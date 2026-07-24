<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Infrastructure\PayPal;

use BreakTheMold\RamirezPayPal\Configuration\PayPalSettings;

class OAuthTokenProvider {
	private $settings;

	public function __construct( PayPalSettings $settings ) {
		$this->settings = $settings;
	}

	public function get_token() {
		$client_id = $this->settings->get_client_id();
		$secret    = $this->settings->get_client_secret();

		if ( empty( $client_id ) || empty( $secret ) ) {
			return new \WP_Error( 'missing_credentials', 'PayPal client ID or Secret is not configured.' );
		}

		$cache_key = 'rrc_paypal_token_' . md5( $client_id . $secret );
		$cached = get_transient( $cache_key );
		if ( $cached ) {
			return $cached;
		}

		$api_url = $this->settings->get_environment() === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

		$response = wp_remote_post( $api_url . '/v1/oauth2/token', [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $secret ),
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

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 400 || empty( $body['access_token'] ) ) {
			$msg = $body['error_description'] ?? 'OAuth authentication failed.';
			return new \WP_Error( 'oauth_failed', $msg );
		}

		$token = $body['access_token'];
		$expiry = isset( $body['expires_in'] ) ? (int) $body['expires_in'] - 60 : 3500;
		set_transient( $cache_key, $token, $expiry );

		return $token;
	}
}
