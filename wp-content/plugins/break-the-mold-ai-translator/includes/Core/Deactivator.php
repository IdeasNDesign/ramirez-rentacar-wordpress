<?php
/**
 * Deactivator — runs on plugin deactivation.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Deactivator {

	/**
	 * Fired on register_deactivation_hook.
	 *
	 * @return void
	 */
	public static function deactivate(): void {

		// ── Clear scheduled cron events ──────────────────────
		$timestamp = wp_next_scheduled( 'btmat_process_queue' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'btmat_process_queue' );
		}

		// ── Flush rewrite rules ──────────────────────────────
		flush_rewrite_rules();
	}
}
