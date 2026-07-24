<?php
/**
 * Frontend Controller — applies translations via WordPress filters.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Frontend;

use BreakTheMold\AITranslator\Language\LanguageResolver;
use BreakTheMold\AITranslator\Translation\TranslationMemory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FrontendController {

	/** @var array Page dictionary cache (source → translation). */
	private static array $dictionary = [];

	/** @var bool Whether we've loaded the dictionary. */
	private static bool $loaded = false;

	/**
	 * Register all translation filters.
	 *
	 * @return void
	 */
	public static function init(): void {

		// Send no-cache headers to prevent browser from showing cached Spanish version
		nocache_headers();

		// Only act when translation is needed.
		if ( ! LanguageResolver::needs_translation() ) {
			return;
		}

		// Set HTML lang attribute.
		add_filter( 'language_attributes', [ __CLASS__, 'filter_language_attributes' ] );

		// Content filters.
		add_filter( 'the_title',   [ __CLASS__, 'translate_text' ], 999 );
		add_filter( 'the_content', [ __CLASS__, 'translate_html_block' ], 999 );
		add_filter( 'widget_text', [ __CLASS__, 'translate_html_block' ], 999 );
		add_filter( 'the_excerpt', [ __CLASS__, 'translate_text' ], 999 );

		// Menu items.
		add_filter( 'nav_menu_item_title', [ __CLASS__, 'translate_text' ], 999 );
		add_filter( 'nav_menu_item_args',  [ __CLASS__, 'translate_menu_item_args' ], 999, 3 );

		// Document title parts.
		add_filter( 'document_title_parts', [ __CLASS__, 'translate_title_parts' ], 999 );

		// SEO meta.
		add_filter( 'wpseo_title',           [ __CLASS__, 'translate_text' ], 999 );
		add_filter( 'wpseo_metadesc',        [ __CLASS__, 'translate_text' ], 999 );
		add_filter( 'rank_math/frontend/title',       [ __CLASS__, 'translate_text' ], 999 );
		add_filter( 'rank_math/frontend/description', [ __CLASS__, 'translate_text' ], 999 );

		// Output buffer for full-page translation as fallback.
		add_action( 'template_redirect', [ __CLASS__, 'start_output_buffer' ], 1 );
	}

	/**
	 * Filter the language_attributes to reflect the active language.
	 *
	 * @param  string $output
	 * @return string
	 */
	public static function filter_language_attributes( string $output ): string {
		$lang = LanguageResolver::resolve();
		$output = preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $lang ) . '"', $output );
		return $output;
	}

	/**
	 * Translate a plain text string by looking it up in the dictionary.
	 *
	 * @param  string $text
	 * @return string
	 */
	public static function translate_text( $text ): string {

		if ( ! is_string( $text ) || empty( trim( $text ) ) ) {
			return $text;
		}

		self::ensure_dictionary();

		$clean = trim( $text );

		// Direct match.
		if ( isset( self::$dictionary[ $clean ] ) ) {
			return self::$dictionary[ $clean ];
		}

		// Try with HTML entities decoded.
		$decoded = html_entity_decode( $clean, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( isset( self::$dictionary[ $decoded ] ) ) {
			return self::$dictionary[ $decoded ];
		}

		return $text;
	}

	/**
	 * Translate text within an HTML block (paragraphs, divs, etc.).
	 *
	 * @param  string $html
	 * @return string
	 */
	public static function translate_html_block( $html ): string {

		if ( ! is_string( $html ) || empty( trim( $html ) ) ) {
			return $html;
		}

		self::ensure_dictionary();

		if ( empty( self::$dictionary ) ) {
			return $html;
		}

		// Replace known text segments within the HTML.
		foreach ( self::$dictionary as $source => $translation ) {
			if ( $source === $translation ) {
				continue;
			}
			// Use str_replace for exact matches within HTML.
			$html = str_replace( $source, $translation, $html );
		}

		return $html;
	}

	/**
	 * Translate document title parts.
	 *
	 * @param  array $parts
	 * @return array
	 */
	public static function translate_title_parts( array $parts ): array {
		foreach ( $parts as $key => $value ) {
			if ( is_string( $value ) ) {
				$parts[ $key ] = self::translate_text( $value );
			}
		}
		return $parts;
	}

	/**
	 * Translate menu item args (for accessibility attributes).
	 *
	 * @param  object $args
	 * @param  object $item
	 * @param  int    $depth
	 * @return object
	 */
	public static function translate_menu_item_args( $args, $item, $depth ) {
		if ( ! empty( $item->attr_title ) ) {
			$item->attr_title = self::translate_text( $item->attr_title );
		}
		return $args;
	}

	/**
	 * Start output buffering for full-page translation.
	 *
	 * @return void
	 */
	public static function start_output_buffer(): void {
		if ( is_admin() || wp_doing_ajax() || defined( 'REST_REQUEST' ) ) {
			return;
		}
		ob_start( [ __CLASS__, 'translate_output_buffer' ] );
	}

	/**
	 * Process the output buffer — translate remaining text.
	 *
	 * @param  string $buffer
	 * @return string
	 */
	public static function translate_output_buffer( string $buffer ): string {

		self::ensure_dictionary();

		if ( empty( self::$dictionary ) ) {
			return $buffer;
		}

		// Apply dictionary translations to the full output.
		foreach ( self::$dictionary as $source => $translation ) {
			if ( $source === $translation || strlen( $source ) < 3 ) {
				continue;
			}
			// 1. Direct replacement (fastest)
			$buffer = str_replace( $source, $translation, $buffer );

			// 2. Flexible whitespace regex replacement to catch multi-line texts with tabs/indentation
			if ( strpos( $source, ' ' ) !== false || strlen( $source ) > 15 ) {
				$escaped = preg_quote( $source, '/' );
				$pattern = '/' . preg_replace( '/\s+/', '\s+', $escaped ) . '/u';
				$buffer  = preg_replace( $pattern, $translation, $buffer );
			}
		}

		// Remove loading class.
		$buffer = str_replace( 'btm-language-loading', '', $buffer );

		$buffer .= '<!-- BTMAT BUFFER ACTIVE - LANG: ' . LanguageResolver::resolve() . ' - DICT: ' . count( self::$dictionary ) . ' -->';

		return $buffer;
	}

	/**
	 * Load the page dictionary from translation memory.
	 *
	 * @return void
	 */
	private static function ensure_dictionary(): void {

		if ( self::$loaded ) {
			return;
		}
		self::$loaded = true;

		$target = LanguageResolver::resolve();
		$post_id = get_queried_object_id();

		if ( $post_id ) {
			self::$dictionary = TranslationMemory::build_page_dictionary( $post_id, $target );
		}

		// Also load menu translations.
		self::load_menu_dictionary( $target );

		// Load global master JSON dictionary for English fallback/override
		if ( 'en' === $target ) {
			$json_file = BTMAT_PATH . 'languages/rrc-translations-es-en.json';
			if ( file_exists( $json_file ) ) {
				$json_data = json_decode( file_get_contents( $json_file ), true );
				if ( is_array( $json_data ) ) {
					self::$dictionary = array_merge( self::$dictionary, $json_data );
				}
			}
		}
	}

	/**
	 * Load menu item translations into the dictionary.
	 *
	 * @param  string $target_language
	 * @return void
	 */
	private static function load_menu_dictionary( string $target_language ): void {

		global $wpdb;
		$seg_table   = $wpdb->prefix . BTMAT_PREFIX . 'segments';
		$trans_table = $wpdb->prefix . BTMAT_PREFIX . 'translations';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.source_text, t.translation_text
				 FROM {$seg_table} s
				 INNER JOIN {$trans_table} t ON t.segment_id = s.id
				 WHERE s.context_type = 'menu'
				   AND t.target_language = %s
				   AND t.status IN ('locked', 'approved', 'auto')
				 ORDER BY FIELD(t.status, 'locked', 'approved', 'auto')
				 LIMIT 200",
				$target_language
			),
			ARRAY_A
		);

		foreach ( $rows as $row ) {
			if ( ! isset( self::$dictionary[ $row['source_text'] ] ) ) {
				self::$dictionary[ $row['source_text'] ] = $row['translation_text'];
			}
		}
	}

	/**
	 * Get the currently loaded dictionary.
	 *
	 * @return array
	 */
	public static function get_dictionary(): array {
		self::ensure_dictionary();
		return self::$dictionary;
	}
}
