<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Infrastructure\PayPal;

class WebhookVerifier {
	private $client;

	public function __construct( PayPalClient $client ) {
		$this->client = $client;
	}

	public function verify( $token, $webhook_id, array $headers, $raw_body ) {
		$payload = [
			'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? ($headers['paypal-auth-algo'] ?? ''),
			'cert_url'          => $headers['PAYPAL-CERT-URL'] ?? ($headers['paypal-cert-url'] ?? ''),
			'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? ($headers['paypal-transmission-id'] ?? ''),
			'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'] ?? ($headers['paypal-transmission-sig'] ?? ''),
			'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? ($headers['paypal-transmission-time'] ?? ''),
			'webhook_id'        => $webhook_id,
			'webhook_event'     => json_decode( $raw_body, true )
		];

		$response = $this->client->request( 'POST', '/v1/notifications/verify-webhook-signature', [
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			],
			'body'    => json_encode( $payload )
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return isset( $response['verification_status'] ) && $response['verification_status'] === 'SUCCESS';
	}
}
