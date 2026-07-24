<?php
/**
 * Plugin Name: Break The Mold AI Translator
 * Plugin URI:
 * Description: Traductor global inteligente español–inglés para WordPress y Elementor con detección del idioma del navegador, memoria de traducciones, glosario, optimización visual de longitud, traducción de contenido dinámico y consumo controlado de GroqCloud.
 * Version: 1.0.0
 * Author: Break The Mold
 * Author URI:
 * Text Domain: break-the-mold-ai-translator
 * Domain Path: /languages
 * Requires PHP: 8.0
 * Requires at least: 6.0
 *
 * Developed by Break The Mold
 *
 * @package BreakTheMold\AITranslator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Plugin Constants ────────────────────────────────────────────────
define( 'BTMAT_VERSION', '1.0.0' );
define( 'BTMAT_PATH', plugin_dir_path( __FILE__ ) );
define( 'BTMAT_URL', plugin_dir_url( __FILE__ ) );
define( 'BTMAT_BASENAME', plugin_basename( __FILE__ ) );
define( 'BTMAT_SLUG', 'break-the-mold-ai-translator' );
define( 'BTMAT_PREFIX', 'btmat_' );

// ─── Autoloader ──────────────────────────────────────────────────────
require_once BTMAT_PATH . 'includes/Core/Autoloader.php';

// ─── Lifecycle Hooks ─────────────────────────────────────────────────
register_activation_hook( __FILE__, [ 'BreakTheMold\\AITranslator\\Core\\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'BreakTheMold\\AITranslator\\Core\\Deactivator', 'deactivate' ] );

// ─── Bootstrap ───────────────────────────────────────────────────────
add_action( 'plugins_loaded', [ 'BreakTheMold\\AITranslator\\Core\\Plugin', 'instance' ] );
