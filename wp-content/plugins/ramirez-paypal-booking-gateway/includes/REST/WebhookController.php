<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\REST;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;
use BreakTheMold\RamirezPayPal\Application\DepositCalculator;
use WP_REST_Response;

class WebhookController {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function execute( $request ) {
		$body = $request->get_body();
		$headers = $request->get_headers();

		$payload = json_decode( $body, true );
		if ( empty( $payload ) || empty( $payload['id'] ) || empty( $payload['event_type'] ) ) {
			return new WP_REST_Response( [ 'message' => 'Payload inválido' ], 400 );
		}

		$event_id   = sanitize_text_field( $payload['id'] );
		$event_type = sanitize_text_field( $payload['event_type'] );

		$settings = $this->container->get( 'settings' );
		$webhook_id = $settings->get_webhook_id();

		// Verify signature if webhook ID is configured
		if ( ! empty( $webhook_id ) ) {
			$verifier = $this->container->get( 'webhook_verifier' );
			$oauth_provider = $this->container->get( 'oauth_provider' );
			$token = $oauth_provider->get_token();
			
			if ( ! is_wp_error( $token ) ) {
				$verified = $verifier->verify( $token, $webhook_id, $headers, $body );
				if ( ! $verified ) {
					return new WP_REST_Response( [ 'message' => 'Firma inválida' ], 401 );
				}
			}
		}

		// Save & Deduplicate Webhook
		$webhook_repo = $this->container->get( 'webhook_repo' );
		$existing = $webhook_repo->get_by_event_id( $event_id );
		if ( $existing ) {
			return new WP_REST_Response( [ 'message' => 'Evento ya procesado' ], 200 );
		}

		$webhook_db_id = $webhook_repo->create( [
			'provider'          => 'paypal',
			'external_event_id' => $event_id,
			'event_type'        => $event_type,
			'signature_verified'=> 1,
			'payload_hash'      => hash( 'sha256', $body ),
			'payload_json'      => $body,
			'processing_status' => 'pending'
		] );

		// Process specific events
		if ( $event_type === 'PAYMENT.CAPTURE.COMPLETED' ) {
			$resource = $payload['resource'] ?? [];
			$custom_id = $resource['custom_id'] ?? '';
			$order_id = $resource['supplementary_data']['related_ids']['order_id'] ?? '';

			if ( ! empty( $custom_id ) ) {
				$this->process_completed_payment_from_webhook( intval( $custom_id ), $order_id, $resource );
			}
		}

		$webhook_repo->update( $webhook_db_id, [
			'processing_status' => 'processed',
			'processed_at'      => current_time( 'mysql' )
		] );

		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	private function process_completed_payment_from_webhook( $reservation_id, $order_id, $resource ) {
		$booking_adapter = $this->container->get( 'booking_adapter' );
		$payment_repo    = $this->container->get( 'payment_repo' );
		$audit_logger    = $this->container->get( 'audit_logger' );

		$res = $booking_adapter->get_reservation( $reservation_id );
		if ( ! $res || in_array( $res->reservation_status, [ 'DEPOSIT_PAID', 'CONFIRMED' ] ) ) {
			return; // Already processed
		}

		$settings = $this->container->get( 'settings' );
		$pct = $settings->get_deposit_percentage();
		$calc = DepositCalculator::calculate( (float) $res->total_amount, $pct );

		$capture_id = $resource['id'] ?? '';

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
				'response_snapshot_json'=> json_encode( $resource ),
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
				'response_snapshot_json'=> json_encode( $resource ),
				'paid_at'               => current_time( 'mysql' )
			] );
		}

		// Block vehicle unit
		if ( ! empty( $res->assigned_unit_id ) ) {
			$booking_adapter->lock_vehicle_unit( $res->id, $res->assigned_unit_id, $res->pickup_at, $res->return_at );
		}

		// Audit Log
		$audit_logger->log( 'PAYPAL_DEPOSIT_COMPLETED_WEBHOOK', 'reservation', $res->id, [ 'old_status' => $res->reservation_status ], [ 'new_status' => 'CONFIRMED' ] );

		// Email confirmation to Customer
		if ( class_exists( 'RamirezRentACar\\Infrastructure\\Notifications\\EmailNotificationService' ) ) {
			\RamirezRentACar\Infrastructure\Notifications\EmailNotificationService::send_reservation_confirmation( $res->id );
		}

		// Email notification to Staff/Admin
		$staff_notification = new \BreakTheMold\RamirezPayPal\Notifications\StaffReservationConfirmed( $this->container );
		$staff_notification->send( $res->id, $order_id, $capture_id );
	}
}
