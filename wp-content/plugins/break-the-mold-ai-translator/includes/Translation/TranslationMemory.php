<?php
/**
 * Translation Memory — persistent lookup & storage of translations.
 *
 * Priority (spec §12):
 *   1. Locked by admin
 *   2. Human correction approved
 *   3. Approved translation
 *   4. Auto-validated translation
 *   5. Suggested translation
 *   6. Not found
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Translation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TranslationMemory {

	/**
	 * Look up a translation from memory.
	 *
	 * @param  string $source_hash     SHA-256 of normalized source text.
	 * @param  string $target_language Target language code.
	 * @return array|null  Translation row or null.
	 */
	public static function find( string $source_hash, string $target_language ): ?array {

		global $wpdb;
		$seg_table   = $wpdb->prefix . BTMAT_PREFIX . 'segments';
		$trans_table = $wpdb->prefix . BTMAT_PREFIX . 'translations';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT t.* FROM {$trans_table} t
				 INNER JOIN {$seg_table} s ON t.segment_id = s.id
				 WHERE s.normalized_hash = %s
				   AND t.target_language = %s
				   AND t.status IN ('locked', 'approved', 'auto', 'suggested')
				 ORDER BY FIELD(t.status, 'locked', 'approved', 'auto', 'suggested'),
				          t.is_human_edited DESC,
				          t.hit_count DESC
				 LIMIT 1",
				$source_hash,
				$target_language
			),
			ARRAY_A
		);

		if ( $row ) {
			// Increment hit count.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$trans_table} SET hit_count = hit_count + 1, last_used_at = NOW() WHERE id = %d",
					$row['id']
				)
			);
		}

		return $row;
	}

	/**
	 * Find a translation by exact source text (not normalized).
	 *
	 * @param  string $text            Source text.
	 * @param  string $target_language Target language.
	 * @return array|null
	 */
	public static function find_by_text( string $text, string $target_language ): ?array {
		$hash = TranslationNormalizer::normalized_hash( $text );
		return self::find( $hash, $target_language );
	}

	/**
	 * Store a new translation in memory.
	 *
	 * @param  int    $segment_id
	 * @param  string $source_language
	 * @param  string $target_language
	 * @param  string $translation_text
	 * @param  array  $meta  Additional fields.
	 * @return int|false  Inserted ID or false.
	 */
	public static function store( int $segment_id, string $source_language, string $target_language, string $translation_text, array $meta = [] ) {

		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'translations';

		$data = array_merge( [
			'segment_id'          => $segment_id,
			'source_language'     => $source_language,
			'target_language'     => $target_language,
			'translation_text'    => $translation_text,
			'translation_hash'    => hash( 'sha256', $translation_text ),
			'compact_translation' => $meta['compact_translation'] ?? null,
			'provider'            => $meta['provider'] ?? 'groqcloud',
			'model'               => $meta['model'] ?? null,
			'prompt_version'      => $meta['prompt_version'] ?? '1.0',
			'status'              => $meta['status'] ?? 'auto',
			'quality_score'       => $meta['quality_score'] ?? null,
			'semantic_confidence' => $meta['confidence'] ?? null,
			'character_ratio'     => $meta['character_ratio'] ?? null,
			'is_human_edited'     => $meta['is_human_edited'] ?? 0,
			'is_locked'           => $meta['is_locked'] ?? 0,
			'hit_count'           => 0,
		], array_filter( $meta, fn( $k ) => str_starts_with( $k, 'visual_' ), ARRAY_FILTER_USE_KEY ) );

		$result = $wpdb->insert( $table, $data );

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Create or find a segment for the given source text.
	 *
	 * @param  string   $text
	 * @param  string   $source_language
	 * @param  string   $context_type
	 * @param  int|null $post_id
	 * @return int      Segment ID.
	 */
	public static function ensure_segment( string $text, string $source_language = 'es', string $context_type = 'generic', ?int $post_id = null ): int {

		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'segments';

		$source_hash     = TranslationNormalizer::source_hash( $text );
		$normalized_hash = TranslationNormalizer::normalized_hash( $text );

		// Try to find existing.
		$existing_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE source_hash = %s LIMIT 1",
				$source_hash
			)
		);

		if ( $existing_id ) {
			// Update last_seen_at.
			$wpdb->update( $table, [ 'last_seen_at' => current_time( 'mysql' ) ], [ 'id' => $existing_id ] );
			return (int) $existing_id;
		}

		// Create new segment.
		$wpdb->insert( $table, [
			'segment_uuid'         => wp_generate_uuid4(),
			'source_hash'          => $source_hash,
			'normalized_hash'      => $normalized_hash,
			'source_text'          => $text,
			'source_language'      => $source_language,
			'context_type'         => $context_type,
			'post_id'              => $post_id,
			'source_character_count' => mb_strlen( $text ),
			'source_word_count'    => TranslationNormalizer::word_count( $text ),
			'source_version'       => 1,
			'status'               => 'active',
		] );

		return (int) $wpdb->insert_id;
	}

	/**
	 * Build a page dictionary (segment → translation map).
	 *
	 * @param  int    $post_id
	 * @param  string $target_language
	 * @return array  [ 'source_text' => 'translation_text', ... ]
	 */
	public static function build_page_dictionary( int $post_id, string $target_language ): array {

		global $wpdb;
		$seg_table   = $wpdb->prefix . BTMAT_PREFIX . 'segments';
		$trans_table = $wpdb->prefix . BTMAT_PREFIX . 'translations';
		$ps_table    = $wpdb->prefix . BTMAT_PREFIX . 'page_segments';
		$pages_table = $wpdb->prefix . BTMAT_PREFIX . 'pages';

		// Get page record.
		$page_id = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$pages_table} WHERE post_id = %d LIMIT 1", $post_id )
		);

		if ( ! $page_id ) {
			return [];
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.source_text, t.translation_text, t.compact_translation
				 FROM {$ps_table} ps
				 INNER JOIN {$seg_table} s ON ps.segment_id = s.id
				 INNER JOIN {$trans_table} t ON t.segment_id = s.id
				 WHERE ps.page_id = %d
				   AND t.target_language = %s
				   AND t.status IN ('locked', 'approved', 'auto')
				 ORDER BY FIELD(t.status, 'locked', 'approved', 'auto'),
				          t.is_human_edited DESC",
				$page_id,
				$target_language
			),
			ARRAY_A
		);

		$dict = [];
		foreach ( $rows as $row ) {
			$key = $row['source_text'];
			if ( ! isset( $dict[ $key ] ) ) {
				$dict[ $key ] = $row['translation_text'];
			}
		}

		return $dict;
	}
}
