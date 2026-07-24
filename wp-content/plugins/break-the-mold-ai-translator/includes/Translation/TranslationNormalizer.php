<?php
/**
 * Translation Normalizer — normalizes text segments before hashing and lookup.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Translation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TranslationNormalizer {

	/**
	 * Normalize text for lookup (used as normalized_hash).
	 *
	 * @param  string $text
	 * @return string
	 */
	public static function normalize( string $text ): string {

		// 1. Decode HTML entities.
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// 2. Normalize Unicode (NFC).
		if ( function_exists( 'normalizer_normalize' ) ) {
			$text = \Normalizer::normalize( $text, \Normalizer::FORM_C ) ?: $text;
		}

		// 3. Normalize quotes.
		$text = str_replace( [ "\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}" ], [ '"', '"', "'", "'" ], $text );

		// 4. Collapse multiple spaces (but preserve single newlines).
		$text = preg_replace( '/[ \t]+/', ' ', $text );
		$text = preg_replace( '/\n{3,}/', "\n\n", $text );

		// 5. Trim.
		$text = trim( $text );

		return $text;
	}

	/**
	 * Generate a SHA-256 hash of the raw source text.
	 *
	 * @param  string $text
	 * @return string
	 */
	public static function source_hash( string $text ): string {
		return hash( 'sha256', $text );
	}

	/**
	 * Generate a SHA-256 hash of the normalized text.
	 *
	 * @param  string $text
	 * @return string
	 */
	public static function normalized_hash( string $text ): string {
		return hash( 'sha256', self::normalize( $text ) );
	}

	/**
	 * Check if a text should be excluded from translation.
	 *
	 * @param  string $text
	 * @return bool   True if the text should NOT be translated.
	 */
	public static function should_exclude( string $text ): bool {

		$text = trim( $text );

		// Empty or whitespace only.
		if ( $text === '' ) {
			return true;
		}

		// Only numbers.
		if ( preg_match( '/^\d+$/', $text ) ) {
			return true;
		}

		// Only punctuation / symbols.
		if ( preg_match( '/^[\p{P}\p{S}\s]+$/u', $text ) ) {
			return true;
		}

		// Only emoji.
		if ( preg_match( '/^[\x{1F300}-\x{1FAD6}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}\x{200D}\s]+$/u', $text ) ) {
			return true;
		}

		// URL only.
		if ( filter_var( $text, FILTER_VALIDATE_URL ) ) {
			return true;
		}

		// Email only.
		if ( filter_var( $text, FILTER_VALIDATE_EMAIL ) ) {
			return true;
		}

		// Phone number pattern only.
		if ( preg_match( '/^[\d\s\-\+\(\)\.]+$/', $text ) && strlen( $text ) >= 7 ) {
			return true;
		}

		// Price pattern only (e.g., "$125.00", "125 USD").
		if ( preg_match( '/^[\$€£]\s*[\d,\.]+$/', $text ) || preg_match( '/^[\d,\.]+\s*(USD|EUR|HNL|LPS)$/i', $text ) ) {
			return true;
		}

		// Too short — configurable, default 2 characters.
		$min_length = (int) get_option( 'btmat_min_segment_length', 2 );
		if ( mb_strlen( $text ) < $min_length ) {
			return true;
		}

		return false;
	}

	/**
	 * Count words in a text.
	 *
	 * @param  string $text
	 * @return int
	 */
	public static function word_count( string $text ): int {
		return str_word_count( strip_tags( $text ) );
	}
}
