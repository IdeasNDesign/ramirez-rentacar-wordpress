<?php
/**
 * Plugin Name: Ramirez Rent A Car AI Sales Assistant
 * Plugin URI:
 * Description: Asistente comercial inteligente bilingüe ("Sara") conectado al sistema de reservas de Ramírez Rent A Car para cotizaciones, disponibilidad, recomendaciones y reservas en tiempo real.
 * Version: 1.0.0
 * Author: Break The Mold
 * Author URI:
 * Text Domain: ramirez-rent-a-car-ai-assistant
 * Requires PHP: 8.0
 * Requires at least: 6.0
 *
 * @package BreakTheMold\RamirezAIAssistant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'RRCAIA_VERSION', time() );
define( 'RRCAIA_PATH', plugin_dir_path( __FILE__ ) );
define( 'RRCAIA_URL', plugin_dir_url( __FILE__ ) );
define( 'RRCAIA_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader (PSR-4)
spl_autoload_register( function ( $class ) {
	$prefix = 'BreakTheMold\\RamirezAIAssistant\\';
	$base_dir = RRCAIA_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

// Initialize Plugin
add_action( 'plugins_loaded', function() {
	if ( class_exists( 'BreakTheMold\\RamirezAIAssistant\\Core\\Plugin' ) ) {
		\BreakTheMold\RamirezAIAssistant\Core\Plugin::instance();
	}
} );
