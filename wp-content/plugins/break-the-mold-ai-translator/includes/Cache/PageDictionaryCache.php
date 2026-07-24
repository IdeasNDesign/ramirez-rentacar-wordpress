<?php
/**
 * Page Dictionary Cache — caches translation dictionaries.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PageDictionaryCache {

	/**
	 * Get cached page dictionary.
	 *
	 * @param  int    $post_id
	 * @param  string $lang
	 * @return array|null
	 */
	public static function get( int $post_id, string $lang ): ?array {
		$key   = self::get_key( $post_id, $lang );
		$value = get_transient( $key );
		return is_array( $value ) ? $value : null;
	}

	/**
	 * Set cached page dictionary.
	 *
	 * @param  int    $post_id
	 * @param  string $lang
	 * @param  array  $dictionary
	 * @return bool
	 */
	public static function set( int $post_id, string $lang, array $dictionary ): bool {
		$key = self::get_key( $post_id, $lang );
		$ttl = (int) get_option( 'btmat_cache_ttl', 86400 );
		return set_transient( $key, $dictionary, $ttl );
	}

	/**
	 * Delete cache for a page.
	 *
	 * @param  int $post_id
	 * @return void
	 */
	public static function delete( int $post_id ): void {
		delete_transient( self::get_key( $post_id, 'es' ) );
		delete_transient( self::get_key( $post_id, 'en' ) );
	}

	/**
	 * Clear all dictionary caches.
	 *
	 * @return void
	 */
	public static function clear_all(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_btmat_dict_%'" );
	}

	/**
	 * Generate transient key.
	 *
	 * @param  int    $post_id
	 * @param  string $lang
	 * @return string
	 */
	private static function get_key( int $post_id, string $lang ): string {
		return 'btmat_dict_' . $post_id . '_' . $lang;
	}
}
