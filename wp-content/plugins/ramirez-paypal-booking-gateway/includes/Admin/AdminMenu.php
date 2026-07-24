<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Admin;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;

class AdminMenu {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function register() {
		add_submenu_page(
			'ramirez-rent-a-car',
			__( 'PayPal Payments', 'ramirez-paypal-booking-gateway' ),
			__( 'PayPal Payments', 'ramirez-paypal-booking-gateway' ),
			\BreakTheMold\RamirezPayPal\Core\Capabilities::MANAGE_PAYPAL,
			'rrc-paypal-payments',
			[ $this, 'render_payments' ]
		);

		add_submenu_page(
			'ramirez-rent-a-car',
			__( 'PayPal Settings', 'ramirez-paypal-booking-gateway' ),
			__( 'PayPal Settings', 'ramirez-paypal-booking-gateway' ),
			\BreakTheMold\RamirezPayPal\Core\Capabilities::MANAGE_PAYPAL,
			'rrc-paypal-settings',
			[ $this, 'render_settings' ]
		);
	}

	public function ajax_check_connection() {
		$token_provider = $this->container->get( 'oauth_provider' );
		$token = $token_provider->get_token();
		if ( is_wp_error( $token ) ) {
			wp_send_json_error( [ 'message' => $token->get_error_message() ] );
		} else {
			$settings = $this->container->get( 'settings' );
			wp_send_json_success( [ 'environment' => $settings->get_environment() ] );
		}
	}

	public function enqueue_assets( $hook ) {
		// Only load assets on our specific admin pages
		if ( strpos( $hook, 'rrc-paypal-' ) === false ) {
			return;
		}

		wp_enqueue_style( 'rrc-paypal-admin-css', RRC_PAYPAY_GATEWAY_URL . 'assets/admin/style.css', [], RRC_PAYPAY_GATEWAY_VERSION );
		wp_enqueue_script( 'rrc-paypal-admin-js', RRC_PAYPAY_GATEWAY_URL . 'assets/admin/script.js', [ 'jquery' ], RRC_PAYPAY_GATEWAY_VERSION, true );
	}

	public function render_payments() {
		$page = new PaymentsPage( $this->container );
		$page->render();
	}

	public function render_settings() {
		$page = new SettingsPage( $this->container );
		$page->render();
	}
}
