<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Core;

class Requirements {
	public static function check() {
		// Minimum PHP Version
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			add_action( 'admin_notices', [ __CLASS__, 'php_version_notice' ] );
			return false;
		}

		// Minimum WordPress Version
		global $wp_version;
		if ( version_compare( $wp_version, '5.6', '<' ) ) {
			add_action( 'admin_notices', [ __CLASS__, 'wp_version_notice' ] );
			return false;
		}

		return true;
	}

	public static function php_version_notice() {
		echo '<div class="error"><p>' . esc_html__( 'Ramirez PayPal Booking Gateway requiere PHP 7.4 o superior.', 'ramirez-paypal-booking-gateway' ) . '</p></div>';
	}

	public static function wp_version_notice() {
		echo '<div class="error"><p>' . esc_html__( 'Ramirez PayPal Booking Gateway requiere WordPress 5.6 o superior.', 'ramirez-paypal-booking-gateway' ) . '</p></div>';
	}
}
