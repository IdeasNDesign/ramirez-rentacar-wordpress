<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Frontend;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;

class CheckoutAssets {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function enqueue() {
		// Only enqueue on rental single vehicle details or checkout template pages
		if ( ! is_singular( 'rrc_vehicle' ) && ! is_page( 'checkout' ) ) {
			// In our case we load on specific pages or single layouts
		}

		wp_register_style( 'rrc-paypal-checkout-css', RRC_PAYPAY_GATEWAY_URL . 'assets/frontend/checkout.css', [], RRC_PAYPAY_GATEWAY_VERSION );
		wp_register_script( 'rrc-paypal-checkout-js', RRC_PAYPAY_GATEWAY_URL . 'assets/frontend/checkout.js', [], RRC_PAYPAY_GATEWAY_VERSION, true );
	}
}
