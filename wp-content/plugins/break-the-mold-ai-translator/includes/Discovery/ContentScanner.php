<?php
/**
 * Content Scanner — discovers translatable segments from posts and pages.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Discovery;

use BreakTheMold\AITranslator\Translation\TranslationNormalizer;
use BreakTheMold\AITranslator\Translation\TranslationMemory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ContentScanner {

	/**
	 * Scan a single post/page and register its segments.
	 *
	 * @param  int $post_id
	 * @return array  [ 'total' => int, 'new' => int ]
	 */
	public static function scan_post( int $post_id ): array {

		$post = get_post( $post_id );
		if ( ! $post || $post->post_status !== 'publish' ) {
			return [ 'total' => 0, 'new' => 0 ];
		}

		global $wpdb;
		$pages_table = $wpdb->prefix . BTMAT_PREFIX . 'pages';
		$ps_table    = $wpdb->prefix . BTMAT_PREFIX . 'page_segments';

		$content_hash = hash( 'sha256', $post->post_title . $post->post_content );

		// Check if page already scanned with same content.
		$page_row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$pages_table} WHERE post_id = %d LIMIT 1", $post_id ),
			ARRAY_A
		);

		if ( $page_row && $page_row['content_hash'] === $content_hash ) {
			return [ 'total' => (int) $page_row['total_segments'], 'new' => 0, 'unchanged' => true ];
		}

		// Extract segments from content.
		$segments = [];

		// Title.
		if ( ! TranslationNormalizer::should_exclude( $post->post_title ) ) {
			$segments[] = [ 'text' => $post->post_title, 'context' => 'title' ];
		}

		// Content — extract text blocks.
		$content_texts = self::extract_text_from_html( $post->post_content );
		foreach ( $content_texts as $text ) {
			if ( ! TranslationNormalizer::should_exclude( $text ) ) {
				$segments[] = [ 'text' => $text, 'context' => 'paragraph' ];
			}
		}

		// Elementor data if available.
		$elementor_data = get_post_meta( $post_id, '_elementor_data', true );
		if ( $elementor_data ) {
			$el_segments = ElementorScanner::extract_segments( $elementor_data );
			foreach ( $el_segments as $seg ) {
				if ( ! TranslationNormalizer::should_exclude( $seg['text'] ) ) {
					$segments[] = $seg;
				}
			}
		}

		// Ensure page record.
		if ( ! $page_row ) {
			$wpdb->insert( $pages_table, [
				'post_id'       => $post_id,
				'canonical_url' => get_permalink( $post_id ),
				'content_hash'  => $content_hash,
				'scan_status'   => 'scanned',
			] );
			$page_db_id = $wpdb->insert_id;
		} else {
			$page_db_id = $page_row['id'];
			$wpdb->update( $pages_table, [
				'content_hash'    => $content_hash,
				'scan_status'     => 'scanned',
				'last_scanned_at' => current_time( 'mysql' ),
			], [ 'id' => $page_db_id ] );
		}

		// Register segments.
		$new_count = 0;
		$total     = count( $segments );

		foreach ( $segments as $pos => $seg ) {
			$segment_id = TranslationMemory::ensure_segment(
				$seg['text'],
				get_option( 'btmat_base_language', 'es' ),
				$seg['context'],
				$post_id
			);

			// Link to page.
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$ps_table} WHERE page_id = %d AND segment_id = %d LIMIT 1",
					$page_db_id,
					$segment_id
				)
			);

			if ( ! $exists ) {
				$wpdb->insert( $ps_table, [
					'page_id'          => $page_db_id,
					'segment_id'       => $segment_id,
					'occurrence_count'  => 1,
					'first_position'   => $pos,
				] );
				$new_count++;
			} else {
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$ps_table} SET occurrence_count = occurrence_count + 1 WHERE id = %d",
						$exists
					)
				);
			}
		}

		// Update page stats.
		$wpdb->update( $pages_table, [
			'total_segments'   => $total,
			'last_scanned_at'  => current_time( 'mysql' ),
		], [ 'id' => $page_db_id ] );

		return [ 'total' => $total, 'new' => $new_count ];
	}

	/**
	 * Scan all published pages and posts.
	 *
	 * @return array  [ 'pages_scanned' => int, 'total_segments' => int, 'new_segments' => int ]
	 */
	public static function scan_all(): array {

		$post_types = get_post_types( [ 'public' => true ], 'names' );
		$posts      = get_posts( [
			'post_type'      => array_values( $post_types ),
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		] );

		$pages_scanned  = 0;
		$total_segments = 0;
		$new_segments   = 0;

		foreach ( $posts as $post_id ) {
			$result = self::scan_post( $post_id );
			$pages_scanned++;
			$total_segments += $result['total'];
			$new_segments   += $result['new'];
		}

		// Also scan menus.
		$menu_result = MenuScanner::scan_all_menus();
		$total_segments += $menu_result['total'];
		$new_segments   += $menu_result['new'];

		return [
			'pages_scanned'  => $pages_scanned,
			'total_segments' => $total_segments,
			'new_segments'   => $new_segments,
		];
	}

	/**
	 * Extract visible text nodes from HTML content.
	 *
	 * @param  string $html
	 * @return array
	 */
	private static function extract_text_from_html( string $html ): array {

		if ( empty( $html ) ) {
			return [];
		}

		// Remove script, style, code, pre, noscript, textarea blocks.
		$html = preg_replace( '/<(script|style|code|pre|noscript|textarea)[^>]*>.*?<\/\1>/si', '', $html );

		// Strip HTML tags but keep text.
		$blocks = preg_split( '/<[^>]+>/', $html );
		$texts  = [];

		foreach ( $blocks as $block ) {
			$block = trim( html_entity_decode( $block, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
			if ( $block !== '' && mb_strlen( $block ) >= 2 ) {
				$texts[] = $block;
			}
		}

		return $texts;
	}
}
