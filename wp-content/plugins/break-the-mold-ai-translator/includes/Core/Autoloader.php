<?php
/**
 * PSR-4 Autoloader for BreakTheMold\AITranslator namespace.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register( function ( string $class ) {

	$prefix    = 'BreakTheMold\\AITranslator\\';
	$base_dir  = __DIR__ . '/../';          // → includes/

	// Only handle classes within our namespace.
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	// Build the file path from the remaining class name.
	$relative_class = substr( $class, $len );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );
