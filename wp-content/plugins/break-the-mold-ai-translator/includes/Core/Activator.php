<?php
/**
 * Activator — runs on plugin activation.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Core;

use BreakTheMold\AITranslator\Database\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {

	/**
	 * Fired on register_activation_hook.
	 *
	 * @return void
	 */
	public static function activate(): void {

		// ── Minimum requirements check ───────────────────────
		if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
			deactivate_plugins( BTMAT_BASENAME );
			wp_die(
				esc_html__(
					'Break The Mold AI Translator requires PHP 8.0 or higher.',
					'break-the-mold-ai-translator'
				),
				'Plugin Activation Error',
				[ 'back_link' => true ]
			);
		}

		// ── Create database tables ───────────────────────────
		Schema::create_tables();

		// ── Seed default glossary terms ──────────────────────
		Schema::seed_glossary();

		// ── Register custom capabilities ─────────────────────
		self::add_capabilities();

		// ── Default options ──────────────────────────────────
		self::set_default_options();

		// ── Schedule cron ────────────────────────────────────
		if ( ! wp_next_scheduled( 'btmat_process_queue' ) ) {
			wp_schedule_event( time(), 'five_minutes', 'btmat_process_queue' );
		}

		// ── Flush rewrite rules ──────────────────────────────
		flush_rewrite_rules();
	}

	/**
	 * Grant custom capabilities to the administrator role.
	 *
	 * @return void
	 */
	private static function add_capabilities(): void {

		$role = get_role( 'administrator' );
		if ( ! $role ) {
			return;
		}

		$caps = [
			'btmat_manage_settings',
			'btmat_manage_translations',
			'btmat_approve_translations',
			'btmat_manage_glossary',
			'btmat_manage_jobs',
			'btmat_view_usage',
			'btmat_view_logs',
			'btmat_manage_seo',
			'btmat_run_bulk_translation',
		];

		foreach ( $caps as $cap ) {
			$role->add_cap( $cap );
		}
	}

	/**
	 * Set default plugin options (will not overwrite existing).
	 *
	 * @return void
	 */
	private static function set_default_options(): void {

		$defaults = [
			'btmat_active'              => true,
			'btmat_base_language'       => 'es',
			'btmat_alt_language'        => 'en',
			'btmat_auto_detect'         => true,
			'btmat_fallback_language'   => 'en',
			'btmat_cookie_duration'     => 365,
			'btmat_seo_mode'            => 'simple',
			'btmat_groq_model'          => 'llama-3.3-70b-versatile',
			'btmat_groq_timeout'        => 30,
			'btmat_groq_max_tokens'     => 4096,
			'btmat_groq_temperature'    => 0.1,
			'btmat_batch_max_segments'  => 15,
			'btmat_batch_max_chars'     => 6000,
			'btmat_max_concurrent'      => 1,
			'btmat_max_retries'         => 2,
			'btmat_budget_daily_requests'   => 500,
			'btmat_budget_monthly_requests' => 10000,
			'btmat_budget_daily_tokens'     => 500000,
			'btmat_budget_monthly_tokens'   => 5000000,
			'btmat_tol_buttons'         => 0.15,
			'btmat_tol_headings'        => 0.15,
			'btmat_tol_paragraphs'      => 0.30,
			'btmat_min_font_size'       => 12,
			'btmat_max_font_reduction'  => 0.08,
			'btmat_cache_ttl'           => 86400,
			'btmat_debug'               => false,
		];

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				update_option( $key, $value, false ); // autoload = false
			}
		}
	}
}
