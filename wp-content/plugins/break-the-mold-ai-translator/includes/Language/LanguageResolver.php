<?php
/**
 * Language Resolver — resolves the active language using priority chain.
 *
 * Priority order (spec §7):
 *   1. Manual selection (cookie BTMAT_LANGUAGE)
 *   2. URL prefix when SEO mode is active
 *   3. Accept-Language header / navigator.languages (server-side parse)
 *   4. Fallback → 'en'
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Language;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LanguageResolver {

	/** @var string|null Cached result for the request. */
	private static ?string $resolved = null;

	/**
	 * Resolve the active language for the current request.
	 *
	 * @return string  'es' or 'en'
	 */
	public static function resolve(): string {

		if ( null !== self::$resolved ) {
			return self::$resolved;
		}

		// ── 0. GET query parameter override ──────────────────
		if ( isset( $_GET['lang'] ) ) {
			$get_lang = sanitize_text_field( wp_unslash( $_GET['lang'] ) );
			if ( LanguageDetector::is_supported( $get_lang ) ) {
				self::$resolved = strtolower( $get_lang );
				LanguageCookie::set( self::$resolved );
				return apply_filters( 'btmat_resolved_language', self::$resolved );
			}
		}

		// ── 1. Manual selection (cookie) ─────────────────────
		$cookie = LanguageCookie::get();
		if ( $cookie ) {
			self::$resolved = $cookie;
			return apply_filters( 'btmat_resolved_language', self::$resolved );
		}

		// ── 2. URL prefix in SEO mode ────────────────────────
		if ( 'seo' === get_option( 'btmat_seo_mode', 'simple' ) ) {
			$lang = self::from_url_prefix();
			if ( $lang ) {
				self::$resolved = $lang;
				return apply_filters( 'btmat_resolved_language', self::$resolved );
			}
		}

		// ── 3. Accept-Language header ────────────────────────
		if ( get_option( 'btmat_auto_detect', true ) ) {
			$detected = LanguageDetector::from_accept_header();
			if ( $detected ) {
				self::$resolved = $detected;
				return apply_filters( 'btmat_resolved_language', self::$resolved );
			}
		}

		// ── 4. Fallback ──────────────────────────────────────
		$fallback = get_option( 'btmat_fallback_language', 'en' );
		self::$resolved = apply_filters( 'btmat_default_language', $fallback );

		return apply_filters( 'btmat_resolved_language', self::$resolved );
	}

	/**
	 * Get the "other" language (the one that is NOT currently resolved).
	 *
	 * @return string
	 */
	public static function alternate(): string {
		return self::resolve() === 'es' ? 'en' : 'es';
	}

	/**
	 * Get the base (source) language of the site.
	 *
	 * @return string
	 */
	public static function base(): string {
		return get_option( 'btmat_base_language', 'es' );
	}

	/**
	 * Check whether the current language differs from the site base.
	 *
	 * @return bool
	 */
	public static function needs_translation(): bool {
		return self::resolve() !== self::base();
	}

	/**
	 * Reset the cached value — useful in tests or after setting cookie.
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$resolved = null;
	}

	/**
	 * Try to extract a language prefix from the current URL path.
	 *
	 * Expects: /es/... or /en/...
	 *
	 * @return string|null
	 */
	private static function from_url_prefix(): ?string {

		$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
		if ( ! $path ) {
			return null;
		}

		// Remove the site base path.
		$home_path = wp_parse_url( home_url(), PHP_URL_PATH ) ?? '';
		if ( $home_path ) {
			$path = substr( $path, strlen( rtrim( $home_path, '/' ) ) );
		}

		// Match first segment: /es/ or /en/
		if ( preg_match( '#^/([a-z]{2})(?:/|$)#', $path, $m ) ) {
			if ( LanguageDetector::is_supported( $m[1] ) ) {
				return $m[1];
			}
		}

		return null;
	}
}
