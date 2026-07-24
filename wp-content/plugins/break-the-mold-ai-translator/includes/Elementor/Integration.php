<?php
/**
 * Elementor Integration — registers widget category and widgets.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Integration {

	/**
	 * Register the custom widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 * @return void
	 */
	public static function register_category( $elements_manager ): void {
		$elements_manager->add_category( 'break-the-mold', [
			'title' => __( 'Break The Mold', 'break-the-mold-ai-translator' ),
			'icon'  => 'eicon-globe',
		] );
	}

	/**
	 * Register Elementor widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 * @return void
	 */
	public static function register_widgets( $widgets_manager ): void {

		// Make sure Elementor is loaded.
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		require_once BTMAT_PATH . 'includes/Elementor/LanguageSwitcherWidget.php';
		$widgets_manager->register( new LanguageSwitcherWidget() );
	}
}
