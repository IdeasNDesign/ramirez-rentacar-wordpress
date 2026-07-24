<?php
/**
 * Menu Scanner — discovers translatable text from WordPress menus.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Discovery;

use BreakTheMold\AITranslator\Translation\TranslationNormalizer;
use BreakTheMold\AITranslator\Translation\TranslationMemory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MenuScanner {

	/**
	 * Scan all registered menus.
	 *
	 * @return array  [ 'total' => int, 'new' => int ]
	 */
	public static function scan_all_menus(): array {

		$menus = get_nav_menu_locations();
		$total = 0;
		$new   = 0;

		foreach ( $menus as $location => $menu_id ) {
			if ( ! $menu_id ) {
				continue;
			}

			$items = wp_get_nav_menu_items( $menu_id );
			if ( ! $items ) {
				continue;
			}

			foreach ( $items as $item ) {
				$title = $item->title ?? '';
				if ( $title && ! TranslationNormalizer::should_exclude( $title ) ) {
					TranslationMemory::ensure_segment( $title, get_option( 'btmat_base_language', 'es' ), 'menu' );
					$total++;
				}

				$attr_title = $item->attr_title ?? '';
				if ( $attr_title && $attr_title !== $title && ! TranslationNormalizer::should_exclude( $attr_title ) ) {
					TranslationMemory::ensure_segment( $attr_title, get_option( 'btmat_base_language', 'es' ), 'menu' );
					$total++;
				}
			}
		}

		return [ 'total' => $total, 'new' => $new ];
	}
}
