<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Infrastructure\PayPal;

class OrdersApi {
	private $client;

	public function __construct( PayPalClient $client ) {
		$this->client = $client;
	}

	public function create( $token, array $payload, $idempotency_key = null ) {
		$headers = [
			'Authorization' => 'Bearer ' . $token,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		];

		if ( $idempotency_key ) {
			$headers['PayPal-Request-Id'] = $idempotency_key;
		}

		return $this->client->request( 'POST', '/v2/checkout/orders', [
			'headers' => $headers,
			'body'    => json_encode( $payload )
		] );
	}

	public function get( $token, $order_id ) {
		return $this->client->request( 'GET', "/v2/checkout/orders/{$order_id}", [
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			]
		] );
	}
}
