<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\REST;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;
use WP_REST_Response;

class AdminPaymentController {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function execute( $request ) {
		global $wpdb;
		$payments_table = $wpdb->prefix . 'rrc_payments';
		
		$payments = $wpdb->get_results( "SELECT * FROM {$payments_table} ORDER BY id DESC LIMIT 50" );
		return new WP_REST_Response( [ 'success' => true, 'payments' => $payments ] );
	}

	public function refund( $request ) {
		$payment_id = intval( $request->get_param( 'id' ) );
		$params = $request->get_params();
		$amount = floatval( $params['amount'] ?? 0.00 );
		$reason = sanitize_text_field( $params['reason'] ?? '' );

		if ( $amount <= 0 ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Monto de reembolso inválido.' ], 400 );
		}

		$payment_repo = $this->container->get( 'payment_repo' );
		$payment = $payment_repo->get( $payment_id );

		if ( ! $payment ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Transacción de pago no encontrada.' ], 404 );
		}

		if ( $amount > (float) $payment->amount ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'El monto del reembolso excede el total pagado.' ], 400 );
		}

		// Perform PayPal Refund call via RefundsApi
		$oauth_provider = $this->container->get( 'oauth_provider' );
		$token = $oauth_provider->get_token();
		if ( is_wp_error( $token ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => $token->get_error_message() ], 500 );
		}

		$refunds_api = $this->container->get( 'refunds_api' );
		$payload = [
			'amount' => [
				'value'         => number_format( $amount, 2, '.', '' ),
				'currency_code' => $payment->currency
			],
			'note_to_payer' => $reason
		];

		$idempotency_key = 'RRC-REF-' . $payment->id . '-' . time();
		$result = $refunds_api->refund( $token, $payment->provider_capture_id, $payload, $idempotency_key );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => $result->get_error_message() ], 500 );
		}

		$refund_status = $result['status'] ?? 'UNKNOWN';

		// Create database refund record
		$refund_repo = $this->container->get( 'refund_repo' );
		$refund_repo->create( [
			'payment_id'         => $payment->id,
			'reservation_id'     => $payment->reservation_id,
			'provider_refund_id' => $result['id'] ?? '',
			'amount'             => $amount,
			'currency'           => $payment->currency,
			'reason'             => $reason,
			'status'             => $refund_status,
			'requested_by'       => get_current_user_id(),
			'approved_by'        => get_current_user_id(),
			'processed_at'       => current_time( 'mysql' )
		] );

		$payment_repo->update( $payment->id, [
			'status' => 'REFUNDED'
		] );

		// Update reservation fields
		$booking_adapter = $this->container->get( 'booking_adapter' );
		$booking_adapter->update_reservation_status( $payment->reservation_id, 'CANCELLED', 'REFUNDED', [
			'amount_refunded' => $amount
		] );

		// Audit Log
		$audit_logger = $this->container->get( 'audit_logger' );
		$audit_logger->log( 'PAYPAL_PAYMENT_REFUNDED', 'payment', $payment->id, [ 'amount' => $payment->amount ], [ 'refunded' => $amount ] );

		return new WP_REST_Response( [
			'success'    => true,
			'message'    => 'Reembolso procesado con éxito.',
			'refund_id'  => $result['id'] ?? '',
			'status'     => $refund_status
		] );
	}

	public function health( $request ) {
		$settings = $this->container->get( 'settings' );
		$token_provider = $this->container->get( 'oauth_provider' );
		$token = $token_provider->get_token();

		return new WP_REST_Response( [
			'success'                 => true,
			'environment'             => $settings->get_environment(),
			'client_id_configured'    => ! empty( $settings->get_client_id() ),
			'client_secret_configured'=> ! empty( $settings->get_client_secret() ),
			'webhook_id_configured'   => ! empty( $settings->get_webhook_id() ),
			'oauth_connection'        => ! is_wp_error( $token ) ? 'SUCCESS' : 'FAILED',
			'oauth_error'             => is_wp_error( $token ) ? $token->get_error_message() : null
		] );
	}
}
