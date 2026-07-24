<?php
/**
 * Exclusion Matcher — checks if content or selectors should be skipped.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Discovery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExclusionMatcher {

	/** Tags that should always be ignored. */
	private const DEFAULT_EXCLUDED_TAGS = [
		'script', 'style', 'code', 'pre', 'textarea', 'noscript', 'iframe',
	];

	/**
	 * Check if a DOM element should be excluded based on tag, attributes, classes or custom selectors.
	 *
	 * @param  string $tag           HTML tag name.
	 * @param  array  $attributes    Associative array of attributes.
	 * @param  string $class_string  HTML class attribute value.
	 * @return bool
	 */
	public static function should_exclude_element( string $tag, array $attributes = [], string $class_string = '' ): bool {
		
		$tag = strtolower( trim( $tag ) );

		// 1. Default ignored tags
		if ( in_array( $tag, self::DEFAULT_EXCLUDED_TAGS, true ) ) {
			return true;
		}

		// 2. Hidden/password inputs
		if ( $tag === 'input' ) {
			$type = strtolower( $attributes['type'] ?? '' );
			if ( in_array( $type, [ 'password', 'hidden' ], true ) ) {
				return true;
			}
		}

		// 3. Attributes: translate="no" or data-btm-no-translate
		if ( isset( $attributes['translate'] ) && strtolower( $attributes['translate'] ) === 'no' ) {
			return true;
		}

		if ( isset( $attributes['data-btm-no-translate'] ) ) {
			return true;
		}

		// 4. Classes: notranslate or btm-no-translate
		if ( ! empty( $class_string ) ) {
			$classes = array_map( 'trim', explode( ' ', strtolower( $class_string ) ) );
			if ( in_array( 'notranslate', $classes, true ) || in_array( 'btm-no-translate', $classes, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a post type is excluded.
	 *
	 * @param  string $post_type
	 * @return bool
	 */
	public static function is_post_type_excluded( string $post_type ): bool {
		$excluded = get_option( 'btmat_exclusions_post_types', [] );
		if ( ! is_array( $excluded ) ) {
			$excluded = explode( ',', (string) $excluded );
		}
		$excluded = array_map( 'trim', array_filter( $excluded ) );
		return in_array( $post_type, $excluded, true );
	}

	/**
	 * Check if a specific page ID is excluded.
	 *
	 * @param  int $post_id
	 * @return bool
	 */
	public static function is_page_excluded( int $post_id ): bool {
		$excluded = get_option( 'btmat_exclusions_pages', [] );
		if ( ! is_array( $excluded ) ) {
			$excluded = explode( ',', (string) $excluded );
		}
		$excluded = array_map( 'intval', array_filter( $excluded ) );
		return in_array( $post_id, $excluded, true );
	}
}
