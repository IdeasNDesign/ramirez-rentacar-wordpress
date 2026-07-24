<?php
/**
 * Database schema — 10 tables created via dbDelta on activation.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Schema {

	/**
	 * Create all plugin tables using dbDelta.
	 *
	 * @return void
	 */
	public static function create_tables(): void {

		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		$prefix  = $wpdb->prefix . BTMAT_PREFIX;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// ── 1. btmat_segments ────────────────────────────────
		$sql = "CREATE TABLE {$prefix}segments (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			segment_uuid CHAR(36) NOT NULL,
			source_hash CHAR(64) NOT NULL,
			normalized_hash CHAR(64) NOT NULL,
			source_text LONGTEXT NOT NULL,
			source_language VARCHAR(10) NOT NULL DEFAULT 'es',
			context_type VARCHAR(50) NOT NULL DEFAULT 'generic',
			context_key VARCHAR(191) NULL,
			element_selector TEXT NULL,
			post_id BIGINT UNSIGNED NULL,
			elementor_element_id VARCHAR(100) NULL,
			attribute_name VARCHAR(50) NULL,
			source_character_count INT UNSIGNED NOT NULL DEFAULT 0,
			source_word_count INT UNSIGNED NOT NULL DEFAULT 0,
			source_version INT UNSIGNED NOT NULL DEFAULT 1,
			status VARCHAR(30) NOT NULL DEFAULT 'active',
			first_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY segment_uuid (segment_uuid),
			KEY source_hash (source_hash),
			KEY normalized_hash (normalized_hash),
			KEY post_id (post_id),
			KEY status (status),
			KEY context_type (context_type)
		) $charset;";
		dbDelta( $sql );

		// ── 2. btmat_translations ────────────────────────────
		$sql = "CREATE TABLE {$prefix}translations (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			segment_id BIGINT UNSIGNED NOT NULL,
			source_language VARCHAR(10) NOT NULL DEFAULT 'es',
			target_language VARCHAR(10) NOT NULL DEFAULT 'en',
			translation_text LONGTEXT NOT NULL,
			compact_translation LONGTEXT NULL,
			translation_hash CHAR(64) NOT NULL,
			provider VARCHAR(50) NOT NULL DEFAULT 'groqcloud',
			model VARCHAR(100) NULL,
			prompt_version VARCHAR(30) NOT NULL DEFAULT '1.0',
			status VARCHAR(30) NOT NULL DEFAULT 'auto',
			quality_score DECIMAL(5,4) NULL,
			semantic_confidence DECIMAL(5,4) NULL,
			character_ratio DECIMAL(7,4) NULL,
			visual_width_ratio DECIMAL(7,4) NULL,
			is_human_edited TINYINT(1) NOT NULL DEFAULT 0,
			is_locked TINYINT(1) NOT NULL DEFAULT 0,
			approved_by BIGINT UNSIGNED NULL,
			approved_at DATETIME NULL,
			hit_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
			last_used_at DATETIME NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY segment_id (segment_id),
			KEY target_language (target_language),
			KEY status (status),
			KEY translation_hash (translation_hash),
			KEY is_locked (is_locked)
		) $charset;";
		dbDelta( $sql );

		// ── 3. btmat_glossary ────────────────────────────────
		$sql = "CREATE TABLE {$prefix}glossary (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			source_term VARCHAR(255) NOT NULL,
			source_language VARCHAR(10) NOT NULL DEFAULT 'es',
			target_language VARCHAR(10) NOT NULL DEFAULT 'en',
			required_translation VARCHAR(255) NOT NULL,
			protection_mode VARCHAR(30) NOT NULL DEFAULT 'no_translate',
			case_sensitive TINYINT(1) NOT NULL DEFAULT 0,
			whole_word TINYINT(1) NOT NULL DEFAULT 1,
			context_type VARCHAR(50) NULL,
			priority INT UNSIGNED NOT NULL DEFAULT 10,
			status VARCHAR(30) NOT NULL DEFAULT 'active',
			created_by BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY source_term (source_term),
			KEY source_language (source_language),
			KEY status (status)
		) $charset;";
		dbDelta( $sql );

		// ── 4. btmat_pages ───────────────────────────────────
		$sql = "CREATE TABLE {$prefix}pages (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT UNSIGNED NOT NULL,
			canonical_url VARCHAR(500) NULL,
			content_hash CHAR(64) NULL,
			scan_status VARCHAR(30) NOT NULL DEFAULT 'pending',
			translation_status_es VARCHAR(30) NOT NULL DEFAULT 'pending',
			translation_status_en VARCHAR(30) NOT NULL DEFAULT 'pending',
			total_segments INT UNSIGNED NOT NULL DEFAULT 0,
			translated_segments INT UNSIGNED NOT NULL DEFAULT 0,
			pending_segments INT UNSIGNED NOT NULL DEFAULT 0,
			failed_segments INT UNSIGNED NOT NULL DEFAULT 0,
			last_scanned_at DATETIME NULL,
			last_translated_at DATETIME NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY post_id (post_id),
			KEY scan_status (scan_status)
		) $charset;";
		dbDelta( $sql );

		// ── 5. btmat_page_segments ───────────────────────────
		$sql = "CREATE TABLE {$prefix}page_segments (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			page_id BIGINT UNSIGNED NOT NULL,
			segment_id BIGINT UNSIGNED NOT NULL,
			occurrence_count INT UNSIGNED NOT NULL DEFAULT 1,
			first_position INT UNSIGNED NULL,
			context_json TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY page_id (page_id),
			KEY segment_id (segment_id),
			UNIQUE KEY page_segment (page_id, segment_id)
		) $charset;";
		dbDelta( $sql );

		// ── 6. btmat_jobs ────────────────────────────────────
		$sql = "CREATE TABLE {$prefix}jobs (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			job_uuid CHAR(36) NOT NULL,
			job_type VARCHAR(50) NOT NULL DEFAULT 'translate',
			source_language VARCHAR(10) NOT NULL DEFAULT 'es',
			target_language VARCHAR(10) NOT NULL DEFAULT 'en',
			entity_type VARCHAR(50) NULL,
			entity_id BIGINT UNSIGNED NULL,
			priority INT UNSIGNED NOT NULL DEFAULT 50,
			payload_hash CHAR(64) NULL,
			payload_json LONGTEXT NULL,
			status VARCHAR(30) NOT NULL DEFAULT 'pending',
			attempts INT UNSIGNED NOT NULL DEFAULT 0,
			maximum_attempts INT UNSIGNED NOT NULL DEFAULT 3,
			scheduled_at DATETIME NULL,
			started_at DATETIME NULL,
			completed_at DATETIME NULL,
			error_code VARCHAR(50) NULL,
			error_message_sanitized TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY job_uuid (job_uuid),
			KEY status (status),
			KEY priority (priority),
			KEY job_type (job_type),
			KEY payload_hash (payload_hash)
		) $charset;";
		dbDelta( $sql );

		// ── 7. btmat_usage ───────────────────────────────────
		$sql = "CREATE TABLE {$prefix}usage (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			provider VARCHAR(50) NOT NULL DEFAULT 'groqcloud',
			model VARCHAR(100) NULL,
			operation VARCHAR(50) NOT NULL DEFAULT 'translate',
			job_id BIGINT UNSIGNED NULL,
			input_tokens INT UNSIGNED NOT NULL DEFAULT 0,
			output_tokens INT UNSIGNED NOT NULL DEFAULT 0,
			cached_tokens INT UNSIGNED NOT NULL DEFAULT 0,
			estimated_cost DECIMAL(10,6) NOT NULL DEFAULT 0,
			latency_ms INT UNSIGNED NOT NULL DEFAULT 0,
			success TINYINT(1) NOT NULL DEFAULT 1,
			error_code VARCHAR(50) NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY provider (provider),
			KEY created_at (created_at),
			KEY operation (operation)
		) $charset;";
		dbDelta( $sql );

		// ── 8. btmat_feedback ────────────────────────────────
		$sql = "CREATE TABLE {$prefix}feedback (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			translation_id BIGINT UNSIGNED NOT NULL,
			old_translation LONGTEXT NULL,
			corrected_translation LONGTEXT NOT NULL,
			feedback_type VARCHAR(30) NOT NULL DEFAULT 'correction',
			reason TEXT NULL,
			user_id BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY translation_id (translation_id),
			KEY user_id (user_id)
		) $charset;";
		dbDelta( $sql );

		// ── 9. btmat_logs ────────────────────────────────────
		$sql = "CREATE TABLE {$prefix}logs (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			level VARCHAR(20) NOT NULL DEFAULT 'info',
			event VARCHAR(100) NOT NULL,
			context_json_sanitized TEXT NULL,
			correlation_id CHAR(36) NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY level (level),
			KEY event (event),
			KEY created_at (created_at)
		) $charset;";
		dbDelta( $sql );

		// ── 10. btmat_prompt_versions ────────────────────────
		$sql = "CREATE TABLE {$prefix}prompt_versions (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			prompt_key VARCHAR(100) NOT NULL,
			version VARCHAR(30) NOT NULL,
			system_prompt LONGTEXT NOT NULL,
			response_schema_json LONGTEXT NULL,
			status VARCHAR(30) NOT NULL DEFAULT 'active',
			created_by BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY prompt_key_version (prompt_key, version),
			KEY status (status)
		) $charset;";
		dbDelta( $sql );

		// ── Save schema version ──────────────────────────────
		update_option( 'btmat_db_version', BTMAT_VERSION );
	}

	/**
	 * Drop all plugin tables — used by uninstall.php.
	 *
	 * @return void
	 */
	public static function drop_tables(): void {

		global $wpdb;
		$prefix = $wpdb->prefix . BTMAT_PREFIX;

		$tables = [
			'page_segments',
			'translations',
			'segments',
			'pages',
			'jobs',
			'usage',
			'feedback',
			'logs',
			'prompt_versions',
			'glossary',
		];

		foreach ( $tables as $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			$wpdb->query( "DROP TABLE IF EXISTS {$prefix}{$table}" );
		}

		delete_option( 'btmat_db_version' );
	}

	/**
	 * Seed the glossary with default protected terms.
	 *
	 * @return void
	 */
	public static function seed_glossary(): void {

		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'glossary';

		$terms = [
			[ 'Ramírez Rent A Car',  'no_translate', 0, 1 ],
			[ 'Ramirez Rent A Car',  'no_translate', 0, 1 ],
			[ 'Break The Mold',      'no_translate', 0, 1 ],
			[ 'Roatán',              'no_translate', 0, 1 ],
			[ 'Roatan',              'no_translate', 0, 1 ],
			[ 'Coxen Hole',          'no_translate', 0, 1 ],
			[ 'French Harbor',       'no_translate', 0, 1 ],
			[ 'PayPal',              'no_translate', 0, 1 ],
			[ 'Toyota',              'no_translate', 0, 1 ],
			[ 'KIA',                 'no_translate', 1, 1 ],
			[ 'Sorento',             'no_translate', 0, 1 ],
			[ 'Prado',               'no_translate', 0, 1 ],
			[ 'Jeep',                'no_translate', 0, 1 ],
			[ 'Gladiator',           'no_translate', 0, 1 ],
			[ 'Drive Away',          'no_translate', 0, 1 ],
			[ 'USD',                 'no_translate', 1, 1 ],
		];

		foreach ( $terms as $term ) {
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$table} WHERE source_term = %s LIMIT 1",
					$term[0]
				)
			);

			if ( ! $exists ) {
				$wpdb->insert( $table, [
					'source_term'          => $term[0],
					'source_language'      => 'es',
					'target_language'      => 'en',
					'required_translation' => $term[0],
					'protection_mode'      => $term[1],
					'case_sensitive'       => $term[2],
					'whole_word'           => $term[3],
					'priority'             => 100,
					'status'               => 'active',
				] );
			}
		}
	}
}
