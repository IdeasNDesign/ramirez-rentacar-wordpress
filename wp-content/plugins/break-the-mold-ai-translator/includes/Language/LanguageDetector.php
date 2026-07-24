<?php
/**
 * Language Detector — determines user language from browser/headers.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Language;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LanguageDetector {

	private const SUPPORTED = [ 'es', 'en' ];

	/**
	 * Parse the Accept-Language header and return the first supported language.
	 *
	 * @return string|null
	 */
	public static function from_accept_header(): ?string {

		$header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
		if ( empty( $header ) ) {
			return null;
		}

		// Parse "es-HN,es;q=0.9,en-US;q=0.8,en;q=0.7"
		$langs = [];
		foreach ( explode( ',', $header ) as $part ) {
			$parts = explode( ';q=', trim( $part ) );
			$code  = strtolower( trim( $parts[0] ) );
			$q     = isset( $parts[1] ) ? (float) $parts[1] : 1.0;
			$langs[ $code ] = $q;
		}

		// Sort by quality descending.
		arsort( $langs );

		foreach ( $langs as $code => $q ) {
			$primary = substr( $code, 0, 2 );
			if ( in_array( $primary, self::SUPPORTED, true ) ) {
				return $primary;
			}
		}

		return null;
	}

	/**
	 * Determine whether a two-letter code is supported.
	 *
	 * @param  string $code
	 * @return bool
	 */
	public static function is_supported( string $code ): bool {
		return in_array( strtolower( $code ), self::SUPPORTED, true );
	}

	/**
	 * Get the list of supported language codes.
	 *
	 * @return array
	 */
	public static function supported(): array {
		return apply_filters( 'btmat_supported_languages', self::SUPPORTED );
	}
}
