<?php
/**
 * REST API Routes — registers all endpoints.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Routes {

	private const NAMESPACE = 'break-the-mold/v1';

	/**
	 * Register all REST API endpoints.
	 *
	 * @return void
	 */
	public static function register(): void {

		// GET /break-the-mold/v1/dictionary
		register_rest_route( self::NAMESPACE, '/dictionary', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_dictionary' ],
			'permission_callback' => '__return_true',
		] );

		// GET /break-the-mold/v1/health
		register_rest_route( self::NAMESPACE, '/health', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_health' ],
			'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
		] );
	}

	/**
	 * Callback to get the active translation dictionary.
	 *
	 * @param  \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public static function get_dictionary( \WP_REST_Request $request ): \WP_REST_Response {
		$lang = sanitize_text_field( $request->get_param( 'lang' ) ?: 'en' );
		$post_id = intval( $request->get_param( 'post_id' ) );
		
		$dict = \BreakTheMold\AITranslator\Translation\TranslationMemory::build_page_dictionary( $post_id, $lang );
		
		return new \WP_REST_Response( [
			'success'    => true,
			'dictionary' => $dict,
		], 200 );
	}

	/**
	 * Callback for health checks.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_health(): \WP_REST_Response {
		$provider = new \BreakTheMold\AITranslator\Providers\GroqCloudProvider();
		$check    = $provider->healthCheck();

		return new \WP_REST_Response( [
			'success' => true,
			'status'  => $check['available'] ? 'healthy' : 'degraded',
			'details' => $check['message'],
		], 200 );
	}

	/**
	 * Check if request has administrative capabilities.
	 *
	 * @return bool
	 */
	public static function check_admin_permission(): bool {
		return current_user_can( 'btmat_manage_settings' );
	}
}
