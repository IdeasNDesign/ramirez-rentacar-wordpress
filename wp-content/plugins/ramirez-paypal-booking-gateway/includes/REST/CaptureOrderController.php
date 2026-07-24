<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\REST;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;
use BreakTheMold\RamirezPayPal\Application\DepositCalculator;
use WP_REST_Response;

class CaptureOrderController {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function execute( $request ) {
		$token = sanitize_text_field( $request->get_param( 'token' ) );
		$params = $request->get_params();
		$order_id = sanitize_text_field( $params['order_id'] ?? '' );

		if ( empty( $order_id ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Falta el PayPal Order ID.' ], 400 );
		}

		$booking_adapter = $this->container->get( 'booking_adapter' );
		$res = $booking_adapter->get_reservation_by_token( $token );

		if ( ! $res ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Reserva no encontrada.' ], 404 );
		}

		if ( in_array( $res->reservation_status, [ 'DEPOSIT_PAID', 'CONFIRMED' ] ) ) {
			return new WP_REST_Response( [
				'success'            => true,
				'message'            => 'Pago ya completado anteriormente.',
				'reservation_status' => $res->reservation_status,
				'deposit_paid'       => number_format( (float) $res->deposit_paid_amount, 2, '.', '' ),
				'remaining_balance'  => number_format( (float) $res->remaining_balance, 2, '.', '' )
			] );
		}

		// Concurrency Lock
		$idempotency_service = $this->container->get( 'idempotency_service' );
		if ( ! $idempotency_service->lock( 'cap-' . $res->id ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Procesamiento en progreso. Intente de nuevo.' ], 409 );
		}

		// Get Access Token
		$oauth_provider = $this->container->get( 'oauth_provider' );
		$token_val = $oauth_provider->get_token();
		if ( is_wp_error( $token_val ) ) {
			$idempotency_service->unlock( 'cap-' . $res->id );
			return new WP_REST_Response( [ 'success' => false, 'message' => $token_val->get_error_message() ], 500 );
		}

		// Capture the order
		$captures_api = $this->container->get( 'captures_api' );
		$capture_idempotency_key = 'RRC-CAP-' . $res->id . '-' . time();
		$capture = $captures_api->capture( $token_val, $order_id, $capture_idempotency_key );

		if ( is_wp_error( $capture ) ) {
			$idempotency_service->unlock( 'cap-' . $res->id );
			return new WP_REST_Response( [ 'success' => false, 'message' => $capture->get_error_message() ], 500 );
		}

		$status = $capture['status'] ?? 'UNKNOWN';

		if ( $status === 'COMPLETED' ) {
			// Complete Payment Process
			$this->process_completed_payment( $res, $order_id, $capture );
			$idempotency_service->unlock( 'cap-' . $res->id );

			// Reload updated reservation values
			$updated_res = $booking_adapter->get_reservation( $res->id );

			return new WP_REST_Response( [
				'success'            => true,
				'message'            => 'Pago completado con éxito.',
				'reservation_status' => $updated_res->reservation_status,
				'deposit_paid'       => number_format( (float) $updated_res->deposit_paid_amount, 2, '.', '' ),
				'remaining_balance'  => number_format( (float) $updated_res->remaining_balance, 2, '.', '' )
			] );
		}

		$idempotency_service->unlock( 'cap-' . $res->id );
		return new WP_REST_Response( [
			'success' => false,
			'message' => 'La captura no fue completada. Estado PayPal: ' . $status
		], 400 );
	}

	private function process_completed_payment( $res, $order_id, $capture ) {
		$booking_adapter = $this->container->get( 'booking_adapter' );
		$payment_repo    = $this->container->get( 'payment_repo' );
		$audit_logger    = $this->container->get( 'audit_logger' );

		$settings = $this->container->get( 'settings' );
		$pct = $settings->get_deposit_percentage();
		$calc = DepositCalculator::calculate( (float) $res->total_amount, $pct );

		$capture_id = '';
		if ( ! empty( $capture['purchase_units'][0]['payments']['captures'][0]['id'] ) ) {
			$capture_id = $capture['purchase_units'][0]['payments']['captures'][0]['id'];
		}

		// Update reservation fields
		$booking_adapter->update_reservation_status( $res->id, 'CONFIRMED', 'COMPLETED', [
			'deposit_paid_amount' => $calc['deposit_amount'],
			'amount_paid'         => $calc['deposit_amount'],
			'remaining_balance'   => $calc['remaining_balance'],
			'confirmed_at'        => current_time( 'mysql' )
		] );

		// Update or Create payment record
		$payment_record = $payment_repo->get_by_order_id( $order_id );
		if ( $payment_record ) {
			$payment_repo->update( $payment_record->id, [
				'provider_capture_id'   => $capture_id,
				'status'                => 'COMPLETED',
				'response_snapshot_json'=> json_encode( $capture ),
				'paid_at'               => current_time( 'mysql' )
			] );
		} else {
			$payment_repo->create( [
				'reservation_id'       => $res->id,
				'provider'             => 'paypal',
				'environment'          => $settings->get_environment(),
				'provider_order_id'    => $order_id,
				'provider_capture_id'  => $capture_id,
				'payment_purpose'      => 'security_deposit',
				'currency'             => $res->currency,
				'amount'               => $calc['deposit_amount'],
				'expected_amount'      => $calc['deposit_amount'],
				'status'               => 'COMPLETED',
				'request_snapshot_json'=> null,
				'response_snapshot_json'=> json_encode( $capture ),
				'paid_at'               => current_time( 'mysql' )
			] );
		}

		// Block vehicle units in availability calendars
		if ( ! empty( $res->assigned_unit_id ) ) {
			$booking_adapter->lock_vehicle_unit( $res->id, $res->assigned_unit_id, $res->pickup_at, $res->return_at );
		}

		// Audit Log
		$audit_logger->log( 'PAYPAL_DEPOSIT_COMPLETED', 'reservation', $res->id, [ 'old_status' => $res->reservation_status ], [ 'new_status' => 'CONFIRMED' ] );

		// Email confirmation to Customer
		if ( class_exists( 'RamirezRentACar\\Infrastructure\\Notifications\\EmailNotificationService' ) ) {
			\RamirezRentACar\Infrastructure\Notifications\EmailNotificationService::send_reservation_confirmation( $res->id );
		}

		// Email notification to Staff/Admin
		$staff_notification = new \BreakTheMold\RamirezPayPal\Notifications\StaffReservationConfirmed( $this->container );
		$staff_notification->send( $res->id, $order_id, $capture_id );
	}
}
