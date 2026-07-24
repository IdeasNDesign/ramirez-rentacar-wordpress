<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\REST;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;
use BreakTheMold\RamirezPayPal\Application\DepositCalculator;
use WP_REST_Response;

class CreateOrderController {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function execute( $request ) {
		$token = sanitize_text_field( $request->get_param( 'token' ) );
		
		$booking_adapter = $this->container->get( 'booking_adapter' );
		$res = $booking_adapter->get_reservation_by_token( $token );

		if ( ! $res ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Reserva no encontrada.' ], 404 );
		}

		if ( in_array( $res->reservation_status, [ 'DEPOSIT_PAID', 'CONFIRMED' ] ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Esta reserva ya ha sido confirmada y pagada.' ], 400 );
		}

		// Calculate deposit
		$settings = $this->container->get( 'settings' );
		$pct = $settings->get_deposit_percentage();
		$calc = DepositCalculator::calculate( (float) $res->total_amount, $pct );

		$deposit_amount = $calc['deposit_amount'];
		$deposit_str    = number_format( $deposit_amount, 2, '.', '' );
		$currency       = $res->currency;

		// Generate Idempotency Key
		$idempotency_key = 'RRC-ORD-' . $res->id . '-' . time();

		$payload = [
			'intent'         => 'CAPTURE',
			'purchase_units' => [
				[
					'reference_id' => (string) $res->public_reference,
					'custom_id'    => (string) $res->id,
					'description'  => sprintf( '%d%% reservation deposit for Ramirez Rent A Car booking %s', $pct, $res->public_reference ),
					'amount'       => [
						'currency_code' => $currency,
						'value'         => $deposit_str,
						'breakdown'     => [
							'item_total' => [
								'currency_code' => $currency,
								'value'         => $deposit_str
							]
						]
					],
					'items' => [
						[
							'name'        => sprintf( 'Depósito de Reserva del %d%%', $pct ),
							'description' => sprintf( 'Garantía del %d%% aplicada al valor total de la reserva %s', $pct, $res->public_reference ),
							'quantity'    => '1',
							'unit_amount' => [
								'currency_code' => $currency,
								'value'         => $deposit_str
							],
							'category'    => 'DIGITAL_GOODS'
						]
					]
				]
			]
		];

		// Get Access Token
		$oauth_provider = $this->container->get( 'oauth_provider' );
		$token_val = $oauth_provider->get_token();
		if ( is_wp_error( $token_val ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => $token_val->get_error_message() ], 500 );
		}

		$orders_api = $this->container->get( 'orders_api' );
		$order = $orders_api->create( $token_val, $payload, $idempotency_key );

		if ( is_wp_error( $order ) || empty( $order['id'] ) ) {
			$msg = is_wp_error( $order ) ? $order->get_error_message() : 'Error creando la orden en PayPal.';
			return new WP_REST_Response( [ 'success' => false, 'message' => $msg ], 500 );
		}

		// Save Payment Log
		$payment_repo = $this->container->get( 'payment_repo' );
		$payment_repo->create( [
			'reservation_id'       => $res->id,
			'provider'             => 'paypal',
			'environment'          => $settings->get_environment(),
			'provider_order_id'    => $order['id'],
			'payment_purpose'      => 'security_deposit',
			'currency'             => $currency,
			'amount'               => $deposit_amount,
			'expected_amount'      => $deposit_amount,
			'status'               => 'ORDER_CREATED',
			'idempotency_key'      => $idempotency_key,
			'request_snapshot_json'=> json_encode( $payload ),
			'response_snapshot_json'=> json_encode( $order )
		] );

		// Update reservation payment state
		$booking_adapter->update_reservation_status( $res->id, $res->reservation_status, 'ORDER_CREATED', [
			'deposit_amount'    => $deposit_amount,
			'remaining_balance' => $calc['remaining_balance']
		] );

		return new WP_REST_Response( [
			'success'           => true,
			'order_id'          => $order['id'],
			'currency'          => $currency,
			'deposit_amount'    => $deposit_str,
			'total_amount'      => number_format( (float) $res->total_amount, 2, '.', '' ),
			'remaining_balance' => number_format( $calc['remaining_balance'], 2, '.', '' )
		] );
	}
}
