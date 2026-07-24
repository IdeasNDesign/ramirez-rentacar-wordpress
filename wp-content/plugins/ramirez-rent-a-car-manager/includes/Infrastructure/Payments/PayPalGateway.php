<?php
/**
 * Author: Break The Mold
 */

namespace RamirezRentACar\Infrastructure\Payments;

class PayPalGateway {
	private $client_id;
	private $secret;
	private $mode;

	public function __construct() {
		$this->client_id = defined( 'RRC_PAYPAL_CLIENT_ID' ) ? RRC_PAYPAL_CLIENT_ID : get_option( 'rrc_paypal_client_id', '' );
		$this->secret    = defined( 'RRC_PAYPAL_SECRET' ) ? RRC_PAYPAL_SECRET : get_option( 'rrc_paypal_secret', '' );
		$this->mode      = get_option( 'rrc_paypal_mode', 'sandbox' );
	}

	private function get_api_url() {
		return $this->mode === 'production' || $this->mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
	}

	private function get_access_token() {
		if ( empty( $this->client_id ) || empty( $this->secret ) ) {
			return new \WP_Error( 'missing_credentials', 'PayPal client ID or Secret is not configured.' );
		}

		$response = wp_remote_post( $this->get_api_url() . '/v1/oauth2/token', [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->secret ),
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/x-www-form-urlencoded;charset=UTF-8'
			],
			'body'    => [
				'grant_type' => 'client_credentials'
			]
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return isset( $body['access_token'] ) ? $body['access_token'] : new \WP_Error( 'auth_failed', 'PayPal authentication failed.' );
	}

	/**
	 * Create PayPal Order specifically for the 10% deposit
	 */
	public function create_order( $reservation_id, $reference, $deposit_amount, $currency = 'USD' ) {
		$token = $this->get_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$deposit_str = number_format( (float) $deposit_amount, 2, '.', '' );
		$idempotency_key = 'RRC-DEP-' . $reservation_id . '-' . time();

		$payload = [
			'intent'         => 'CAPTURE',
			'purchase_units' => [
				[
					'reference_id' => (string) $reference,
					'custom_id'    => (string) $reservation_id,
					'description'  => 'Depósito de reserva (10%) - Ramirez Rent A Car',
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
							'name'        => 'Depósito de Reserva del 10%',
							'description' => 'Garantía del 10% aplicada al valor total de la reserva ' . $reference,
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

		$response = wp_remote_post( $this->get_api_url() . '/v2/checkout/orders', [
			'headers' => [
				'Authorization'   => 'Bearer ' . $token,
				'Content-Type'    => 'application/json',
				'PayPal-Request-Id' => $idempotency_key
			],
			'body'    => json_encode( $payload )
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Capture PayPal Order from Server
	 */
	public function capture_order( $paypal_order_id ) {
		$token = $this->get_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$response = wp_remote_post( $this->get_api_url() . "/v2/checkout/orders/{$paypal_order_id}/capture", [
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json'
			]
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Verify Webhook Signature from PayPal
	 */
	public function verify_webhook_signature( $headers, $body ) {
		$webhook_id = defined( 'RRC_PAYPAL_WEBHOOK_ID' ) ? RRC_PAYPAL_WEBHOOK_ID : get_option( 'rrc_paypal_webhook_id', '' );
		if ( empty( $webhook_id ) ) {
			return false; // Skip verification if webhook ID is missing in dev environment
		}

		$token = $this->get_access_token();
		if ( is_wp_error( $token ) ) {
			return false;
		}

		$verification_payload = [
			'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? $headers['paypal-auth-algo'] ?? '',
			'cert_url'          => $headers['PAYPAL-CERT-URL'] ?? $headers['paypal-cert-url'] ?? '',
			'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? $headers['paypal-transmission-id'] ?? '',
			'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'] ?? $headers['paypal-transmission-sig'] ?? '',
			'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? $headers['paypal-transmission-time'] ?? '',
			'webhook_id'        => $webhook_id,
			'webhook_event'     => json_decode( $body, true )
		];

		$response = wp_remote_post( $this->get_api_url() . '/v1/notifications/verify-webhook-signature', [
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json'
			],
			'body'    => json_encode( $verification_payload )
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$result = json_decode( wp_remote_retrieve_body( $response ), true );
		return isset( $result['verification_status'] ) && $result['verification_status'] === 'SUCCESS';
	}
}
