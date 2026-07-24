<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Infrastructure\PayPal;

class CapturesApi {
	private $client;

	public function __construct( PayPalClient $client ) {
		$this->client = $client;
	}

	public function capture( $token, $order_id, $idempotency_key = null ) {
		$headers = [
			'Authorization' => 'Bearer ' . $token,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		];

		if ( $idempotency_key ) {
			$headers['PayPal-Request-Id'] = $idempotency_key;
		}

		return $this->client->request( 'POST', "/v2/checkout/orders/{$order_id}/capture", [
			'headers' => $headers,
			'body'    => '{}'
		] );
	}
}
