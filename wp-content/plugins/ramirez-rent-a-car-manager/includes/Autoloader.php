<?php
namespace RamirezRentACar;

class Autoloader {
	public static function register() {
		spl_autoload_register( function ( $class ) {
			$prefix = 'RamirezRentACar\\';
			$len    = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}
			$relative_class = substr( $class, $len );
			$file = RRC_PATH . 'includes/' . str_replace( '\\', '/', $relative_class ) . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		} );
	}
}

Autoloader::register();
