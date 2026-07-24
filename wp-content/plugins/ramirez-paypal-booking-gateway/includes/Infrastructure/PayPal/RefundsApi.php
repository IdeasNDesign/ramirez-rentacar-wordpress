<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Infrastructure\PayPal;

class RefundsApi {
	private $client;

	public function __construct( PayPalClient $client ) {
		$this->client = $client;
	}

	public function refund( $token, $capture_id, array $payload, $idempotency_key = null ) {
		$headers = [
			'Authorization' => 'Bearer ' . $token,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		];

		if ( $idempotency_key ) {
			$headers['PayPal-Request-Id'] = $idempotency_key;
		}

		return $this->client->request( 'POST', "/v2/payments/captures/{$capture_id}/refund", [
			'headers' => $headers,
			'body'    => json_encode( $payload )
		] );
	}
}
