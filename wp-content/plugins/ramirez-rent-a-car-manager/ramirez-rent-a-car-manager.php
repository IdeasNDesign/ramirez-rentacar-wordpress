<?php
/**
 * Plugin Name: Ramirez Rent A Car Manager
 * Description: Business logic, rate engine, bookings, physical units, private documents, PayPal and AI Assistant for Ramirez Rent A Car.
 * Version: 1.0.0
 * Author: Break The Mold
 * Text Domain: ramirez-rent-a-car
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Plugin Constants
define( 'RRC_VERSION', '1.0.0' );
define( 'RRC_PATH', plugin_dir_path( __FILE__ ) );
define( 'RRC_URL', plugin_dir_url( __FILE__ ) );
define( 'RRC_BASENAME', plugin_basename( __FILE__ ) );

// Load Autoloader
require_once RRC_PATH . 'includes/Autoloader.php';

// Initialize Plugin
register_activation_hook( __FILE__, [ 'RamirezRentACar\\Core\\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'RamirezRentACar\\Core\\Deactivator', 'deactivate' ] );

add_action( 'plugins_loaded', [ 'RamirezRentACar\\Core\\Plugin', 'instance' ] );
