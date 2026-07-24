<?php
/**
 * Plugin Name: Ramirez PayPal Booking Gateway
 * Plugin URI: https://ramirezrentacar.com/
 * Description: Pasarela de PayPal para el sistema de reservas de Ramírez Rent A Car, con depósito de reserva configurable, conexión Sandbox/Live, webhooks verificados, pagos, reembolsos, conciliación, notificaciones y administración intuitiva.
 * Version: 1.0.0
 * Author: Break The Mold
 * Text Domain: ramirez-paypal-booking-gateway
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'RRC_PAYPAY_GATEWAY_VERSION', '1.0.0' );
define( 'RRC_PAYPAY_GATEWAY_PATH', plugin_dir_path( __FILE__ ) );
define( 'RRC_PAYPAY_GATEWAY_URL', plugin_dir_url( __FILE__ ) );
define( 'RRC_PAYPAY_GATEWAY_BASENAME', plugin_basename( __FILE__ ) );

// Load Autoloader and initialize early so that namespace loader is available during activation/deactivation.
require_once RRC_PAYPAY_GATEWAY_PATH . 'includes/Core/Plugin.php';
\BreakTheMold\RamirezPayPal\Core\Plugin::init();

// Register activation and deactivation hooks
register_activation_hook( __FILE__, [ 'BreakTheMold\\RamirezPayPal\\Core\\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'BreakTheMold\\RamirezPayPal\\Core\\Deactivator', 'deactivate' ] );
