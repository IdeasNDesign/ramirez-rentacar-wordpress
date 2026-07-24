<?php
/**
 * Hreflang Manager — injects alternate page lang links into wp_head.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\SEO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class HreflangManager {

	/**
	 * Initialize hreflang tags injection.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_head', [ __CLASS__, 'output_hreflangs' ] );
	}

	/**
	 * Output alternate language links.
	 *
	 * @return void
	 */
	public static function output_hreflangs(): void {
		
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		$current_url = home_url( add_query_arg( [], $GLOBALS['wp']->request ) );
		$is_seo_mode = get_option( 'btmat_seo_mode', 'simple' ) === 'seo';

		if ( $is_seo_mode ) {
			// Construct URLs with language prefixes: e.g. /es/ and /en/
			$es_url = home_url( '/es/' );
			$en_url = home_url( '/en/' );
		} else {
			// Query parameter alternate URLs
			$es_url = add_query_arg( 'lang', 'es', $current_url );
			$en_url = add_query_arg( 'lang', 'en', $current_url );
		}

		echo "\n" . '<!-- BTM AI Translator Hreflang Links -->' . "\n";
		echo '<link rel="alternate" hreflang="es" href="' . esc_url( $es_url ) . '" />' . "\n";
		echo '<link rel="alternate" hreflang="en" href="' . esc_url( $en_url ) . '" />' . "\n";
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $en_url ) . '" />' . "\n";
	}
}
