<?php
/**
 * Glossary Service — manages protected terms and placeholder substitution.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Translation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GlossaryService {

	/** @var array|null Cached glossary terms. */
	private static ?array $terms = null;

	/**
	 * Load all active glossary terms.
	 *
	 * @return array
	 */
	public static function get_terms(): array {

		if ( null !== self::$terms ) {
			return self::$terms;
		}

		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'glossary';

		self::$terms = $wpdb->get_results(
			"SELECT * FROM {$table} WHERE status = 'active' ORDER BY priority DESC, CHAR_LENGTH(source_term) DESC",
			ARRAY_A
		);

		return self::$terms;
	}

	/**
	 * Replace protected terms with safe placeholders before sending to AI.
	 *
	 * @param  string $text
	 * @return array  [ 'text' => masked_text, 'map' => [ placeholder => original_term ] ]
	 */
	public static function mask_terms( string $text ): array {

		$terms = self::get_terms();
		$map   = [];
		$index = 1;

		foreach ( $terms as $term ) {
			if ( $term['protection_mode'] !== 'no_translate' ) {
				continue;
			}

			$pattern = self::build_pattern( $term );
			$placeholder = '__BTM_TERM_' . str_pad( (string) $index, 3, '0', STR_PAD_LEFT ) . '__';

			$count = 0;
			$text  = preg_replace( $pattern, $placeholder, $text, -1, $count );

			if ( $count > 0 ) {
				$map[ $placeholder ] = $term['source_term'];
				$index++;
			}
		}

		return [ 'text' => $text, 'map' => $map ];
	}

	/**
	 * Restore original terms from placeholders in a translated text.
	 *
	 * @param  string $text Translated text with placeholders.
	 * @param  array  $map  [ placeholder => original_term ]
	 * @return string
	 */
	public static function unmask_terms( string $text, array $map ): string {

		foreach ( $map as $placeholder => $original ) {
			$text = str_replace( $placeholder, $original, $text );
		}

		return $text;
	}

	/**
	 * Get the protected terms list for a segment (to send to the AI prompt).
	 *
	 * @param  string $text
	 * @return array  List of term strings found in the text.
	 */
	public static function find_terms_in_text( string $text ): array {

		$terms = self::get_terms();
		$found = [];

		foreach ( $terms as $term ) {
			$pattern = self::build_pattern( $term );
			if ( preg_match( $pattern, $text ) ) {
				$found[] = $term['source_term'];
			}
		}

		return array_unique( $found );
	}

	/**
	 * Build a regex pattern for a glossary term.
	 *
	 * @param  array $term Glossary row.
	 * @return string
	 */
	private static function build_pattern( array $term ): string {

		$escaped = preg_quote( $term['source_term'], '/' );
		$flags   = $term['case_sensitive'] ? '' : 'i';

		if ( $term['whole_word'] ) {
			return '/\b' . $escaped . '\b/u' . $flags;
		}

		return '/' . $escaped . '/u' . $flags;
	}

	/**
	 * Reset the cached terms (useful after adding/editing terms).
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$terms = null;
	}
}
