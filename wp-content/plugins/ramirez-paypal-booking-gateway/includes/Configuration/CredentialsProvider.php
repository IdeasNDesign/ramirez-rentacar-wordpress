<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Configuration;

class CredentialsProvider {
	private $encryption_key;

	public function __construct() {
		// Use standard WordPress salt as base for encryption key
		$this->encryption_key = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : 'default-fallback-salt';
	}

	public function get_client_id() {
		return $this->resolve_credential( 'RRC_PAYPAL_CLIENT_ID', 'PAYPAL_CLIENT_ID', 'rrc_paypal_client_id' );
	}

	public function get_client_secret() {
		return $this->resolve_credential( 'RRC_PAYPAL_CLIENT_SECRET', 'PAYPAL_CLIENT_SECRET', 'rrc_paypal_client_secret', true );
	}

	public function get_webhook_id() {
		return $this->resolve_credential( 'RRC_PAYPAL_WEBHOOK_ID', 'PAYPAL_WEBHOOK_ID', 'rrc_paypal_webhook_id' );
	}

	public function get_environment() {
		$env = $this->resolve_credential( 'RRC_PAYPAL_ENVIRONMENT', 'PAYPAL_ENVIRONMENT', 'rrc_paypal_environment' );
		return in_array( strtolower( $env ), [ 'live', 'production' ] ) ? 'live' : 'sandbox';
	}

	private function resolve_credential( $rrc_var, $general_var, $option_name, $is_encrypted = false ) {
		// 1. Check RRC Environment specific variables
		if ( getenv( $rrc_var ) !== false ) {
			return getenv( $rrc_var );
		}

		// 2. Check general PayPal environment variables
		if ( getenv( $general_var ) !== false ) {
			return getenv( $general_var );
		}

		// 3. Check wp-config constants
		if ( defined( $rrc_var ) ) {
			return constant( $rrc_var );
		}
		if ( defined( $general_var ) ) {
			return constant( $general_var );
		}

		// 4. Fallback to DB Options
		$val = get_option( $option_name, '' );
		if ( $is_encrypted && ! empty( $val ) ) {
			return $this->decrypt( $val );
		}

		return $val;
	}

	public function save_credentials( $client_id, $client_secret, $webhook_id, $environment ) {
		update_option( 'rrc_paypal_client_id', sanitize_text_field( $client_id ) );
		if ( ! empty( $client_secret ) ) {
			update_option( 'rrc_paypal_client_secret', $this->encrypt( sanitize_text_field( $client_secret ) ) );
		}
		update_option( 'rrc_paypal_webhook_id', sanitize_text_field( $webhook_id ) );
		update_option( 'rrc_paypal_environment', sanitize_text_field( $environment ) );
	}

	private function encrypt( $value ) {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return base64_encode( $value );
		}
		$method = 'aes-256-cbc';
		$iv_length = openssl_cipher_iv_length( $method );
		$iv = openssl_random_pseudo_bytes( $iv_length );
		$encrypted = openssl_encrypt( $value, $method, $this->encryption_key, 0, $iv );
		return base64_encode( $iv . $encrypted );
	}

	private function decrypt( $raw ) {
		$decoded = base64_decode( $raw );
		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return $decoded;
		}
		$method = 'aes-256-cbc';
		$iv_length = openssl_cipher_iv_length( $method );
		$iv = substr( $decoded, 0, $iv_length );
		$encrypted = substr( $decoded, $iv_length );
		return openssl_decrypt( $encrypted, $method, $this->encryption_key, 0, $iv );
	}
}
