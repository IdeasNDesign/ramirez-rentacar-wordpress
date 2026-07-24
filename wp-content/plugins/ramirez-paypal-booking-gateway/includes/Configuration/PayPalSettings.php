<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Configuration;

class PayPalSettings {
	private $credentials_provider;

	public function __construct( CredentialsProvider $credentials_provider ) {
		$this->credentials_provider = $credentials_provider;
	}

	public function get_client_id() {
		return $this->credentials_provider->get_client_id();
	}

	public function get_client_secret() {
		return $this->credentials_provider->get_client_secret();
	}

	public function get_webhook_id() {
		return $this->credentials_provider->get_webhook_id();
	}

	public function get_environment() {
		return $this->credentials_provider->get_environment();
	}

	public function get( $key, $default = null ) {
		$option_key = 'rrc_paypal_' . $key;
		return get_option( $option_key, $default );
	}

	public function is_deposit_enabled() {
		return (bool) $this->get( 'deposit_enabled', true );
	}

	public function get_deposit_percentage() {
		return (float) $this->get( 'deposit_percentage', 10.00 );
	}

	public function get_currency() {
		return $this->get( 'currency', 'USD' );
	}

	public function get_remaining_balance_due() {
		return $this->get( 'remaining_balance_due', 'at_pickup' );
	}

	public function should_auto_confirm() {
		return (bool) $this->get( 'auto_confirm_reservation', true );
	}

	public function get_hold_duration() {
		return (int) $this->get( 'hold_duration_minutes', 15 );
	}
}
