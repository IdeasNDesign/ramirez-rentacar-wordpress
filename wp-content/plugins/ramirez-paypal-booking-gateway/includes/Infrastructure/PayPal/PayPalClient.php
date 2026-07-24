<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Infrastructure\PayPal;

use BreakTheMold\RamirezPayPal\Configuration\PayPalSettings;

class PayPalClient {
	private $settings;

	public function __construct( PayPalSettings $settings ) {
		$this->settings = $settings;
	}

	public function get_api_url() {
		return $this->settings->get_environment() === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
	}

	public function request( $method, $endpoint, array $args = [] ) {
		$url = $this->get_api_url() . $endpoint;
		
		$args['method'] = strtoupper( $method );
		if ( ! isset( $args['headers'] ) ) {
			$args['headers'] = [];
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $body, true );

		if ( $code >= 400 ) {
			$msg = $decoded['message'] ?? ( $decoded['error_description'] ?? 'PayPal API request error.' );
			return new \WP_Error( 'paypal_api_error', $msg, [ 'status' => $code, 'body' => $decoded ] );
		}

		return $decoded;
	}
}
