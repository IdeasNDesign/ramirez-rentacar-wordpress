<?php
/**
 * Language Cookie — manages the BTMAT_LANGUAGE cookie.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Language;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LanguageCookie {

	public const NAME = 'BTMAT_LANGUAGE';

	/**
	 * Read the language cookie value.
	 *
	 * @return string|null  'es' or 'en', or null if not set/invalid.
	 */
	public static function get(): ?string {

		if ( ! isset( $_COOKIE[ self::NAME ] ) ) {
			return null;
		}

		$value = sanitize_text_field( wp_unslash( $_COOKIE[ self::NAME ] ) );

		return LanguageDetector::is_supported( $value ) ? strtolower( $value ) : null;
	}

	/**
	 * Set the language cookie.
	 *
	 * @param string $lang  Two-letter language code (es|en).
	 * @return void
	 */
	public static function set( string $lang ): void {

		if ( ! LanguageDetector::is_supported( $lang ) ) {
			return;
		}

		$duration = (int) get_option( 'btmat_cookie_duration', 365 );
		$secure   = is_ssl();

		setcookie( self::NAME, strtolower( $lang ), [
			'expires'  => time() + ( $duration * DAY_IN_SECONDS ),
			'path'     => '/',
			'secure'   => $secure,
			'httponly' => false,   // JS needs read access for switcher
			'samesite' => 'Lax',
		] );

		// Also update the superglobal so the current request can use it.
		$_COOKIE[ self::NAME ] = strtolower( $lang );
	}

	/**
	 * Delete the language cookie.
	 *
	 * @return void
	 */
	public static function clear(): void {

		setcookie( self::NAME, '', [
			'expires'  => time() - 3600,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		] );

		unset( $_COOKIE[ self::NAME ] );
	}
}
