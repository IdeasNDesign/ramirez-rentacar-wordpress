<?php
/**
 * Router — registers custom rewrite rules for bilingual SEO en/es paths.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\SEO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Router {

	/**
	 * Initialize the rewrite rules and filter.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'template_redirect', [ __CLASS__, 'handle_redirects' ] );

		if ( 'seo' === get_option( 'btmat_seo_mode', 'simple' ) ) {
			add_action( 'init', [ __CLASS__, 'add_rewrite_rules' ], 1 );
			add_filter( 'query_vars', [ __CLASS__, 'add_query_vars' ] );
		}
	}

	/**
	 * Handle 301 redirects for typos and historical URLs.
	 *
	 * @return void
	 */
	public static function handle_redirects(): void {
		$request_uri = $_SERVER['REQUEST_URI'] ?? '';

		if ( false !== strpos( $request_uri, '/contac-us' ) ) {
			$query = wp_parse_url( $request_uri, PHP_URL_QUERY );
			$path  = wp_parse_url( $request_uri, PHP_URL_PATH ) ?? '';

			$lang_prefix = '';
			if ( preg_match( '#/(es|en)/contac-us#', $path, $matches ) ) {
				$lang_prefix = '/' . $matches[1];
			}

			$redirect_url = home_url( $lang_prefix . '/contact-us' );
			if ( $query ) {
				$redirect_url = add_query_arg( [], $redirect_url . '?' . $query );
			}

			wp_safe_redirect( $redirect_url, 301 );
			exit;
		}
	}

	/**
	 * Add rewrite rules for language prefixes.
	 *
	 * @return void
	 */
	public static function add_rewrite_rules(): void {
		add_rewrite_rule( '^(es|en)/?$', 'index.php?btmat_lang=$matches[1]', 'top' );
		add_rewrite_rule( '^(es|en)/([^/]+)/?$', 'index.php?btmat_lang=$matches[1]&name=$matches[2]', 'top' );
		add_rewrite_rule( '^(es|en)/([^/]+)/([^/]+)/?$', 'index.php?btmat_lang=$matches[1]&post_type=$matches[2]&name=$matches[3]', 'top' );
		add_rewrite_rule( '^(es|en)/(.+?)/?$', 'index.php?btmat_lang=$matches[1]&pagename=$matches[2]', 'top' );
	}

	/**
	 * Register btmat_lang query var.
	 *
	 * @param  array $vars
	 * @return array
	 */
	public static function add_query_vars( array $vars ): array {
		$vars[] = 'btmat_lang';
		return $vars;
	}
}
