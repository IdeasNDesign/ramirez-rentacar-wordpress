<?php
/**
 * Logger — logs events to the database table.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Logger {

	/**
	 * Log an event.
	 *
	 * @param  string $level
	 * @param  string $event
	 * @param  array  $context
	 * @return void
	 */
	public static function log( string $level, string $event, array $context = [] ): void {
		
		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'logs';

		$wpdb->insert( $table, [
			'level'                  => $level,
			'event'                  => $event,
			'context_json_sanitized' => wp_json_encode( $context ),
			'correlation_id'         => wp_generate_uuid4(),
			'created_at'             => current_time( 'mysql' ),
		] );
	}

	public static function info( string $event, array $context = [] ): void {
		self::log( 'info', $event, $context );
	}

	public static function error( string $event, array $context = [] ): void {
		self::log( 'error', $event, $context );
	}
}
