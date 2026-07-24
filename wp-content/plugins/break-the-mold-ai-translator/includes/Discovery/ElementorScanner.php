<?php
/**
 * Elementor Scanner — recursively parses _elementor_data JSON.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Discovery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ElementorScanner {

	/** Widget types that contain translatable text. */
	private const TEXT_WIDGETS = [
		'heading', 'text-editor', 'button', 'icon-list', 'testimonial',
		'accordion', 'tabs', 'toggle', 'image', 'image-box', 'icon-box',
		'call-to-action', 'form', 'price-table', 'slides', 'shortcode',
	];

	/** Settings keys that typically contain translatable strings. */
	private const TEXT_KEYS = [
		'title', 'editor', 'text', 'description', 'button_text',
		'heading', 'sub_heading', 'testimonial_content', 'testimonial_name',
		'testimonial_job', 'title_text', 'description_text', 'ribbon_title',
		'tab_title', 'tab_content', 'accordion_title', 'accordion_content',
		'alert_title', 'alert_description', 'label', 'placeholder',
		'html', 'before_text', 'highlighted_text', 'after_text',
		'prefix', 'suffix', 'badge_text', 'price', 'item_description',
		'link_text',
	];

	/**
	 * Extract translatable segments from Elementor JSON data.
	 *
	 * @param  string $json  Raw JSON from _elementor_data.
	 * @return array  [ [ 'text' => '...', 'context' => '...', 'elementor_id' => '...' ], ... ]
	 */
	public static function extract_segments( string $json ): array {

		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			return [];
		}

		$segments = [];
		self::walk_elements( $data, $segments );

		return $segments;
	}

	/**
	 * Recursively walk the Elementor element tree.
	 *
	 * @param  array $elements
	 * @param  array &$segments Collected segments (passed by reference).
	 * @return void
	 */
	private static function walk_elements( array $elements, array &$segments ): void {

		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			$widget_type = $element['widgetType'] ?? $element['elType'] ?? '';
			$el_id       = $element['id'] ?? '';
			$settings    = $element['settings'] ?? [];

			// Extract text from known settings keys.
			foreach ( self::TEXT_KEYS as $key ) {
				if ( ! empty( $settings[ $key ] ) && is_string( $settings[ $key ] ) ) {
					$text = self::clean_elementor_text( $settings[ $key ] );
					if ( $text !== '' && mb_strlen( $text ) >= 2 ) {
						$context = self::determine_context( $widget_type, $key );
						$segments[] = [
							'text'         => $text,
							'context'      => $context,
							'elementor_id' => $el_id,
						];
					}
				}
			}

			// Handle repeater fields (tabs, accordion items, icon lists, etc.).
			foreach ( $settings as $setting_key => $setting_value ) {
				if ( is_array( $setting_value ) ) {
					foreach ( $setting_value as $repeater_item ) {
						if ( is_array( $repeater_item ) ) {
							foreach ( self::TEXT_KEYS as $key ) {
								if ( ! empty( $repeater_item[ $key ] ) && is_string( $repeater_item[ $key ] ) ) {
									$text = self::clean_elementor_text( $repeater_item[ $key ] );
									if ( $text !== '' && mb_strlen( $text ) >= 2 ) {
										$segments[] = [
											'text'         => $text,
											'context'      => self::determine_context( $widget_type, $key ),
											'elementor_id' => $el_id,
										];
									}
								}
							}
						}
					}
				}
			}

			// Recurse into children.
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				self::walk_elements( $element['elements'], $segments );
			}
		}
	}

	/**
	 * Clean Elementor text content (strip excessive HTML, decode entities).
	 *
	 * @param  string $text
	 * @return string
	 */
	private static function clean_elementor_text( string $text ): string {
		// Strip HTML tags but keep simple inline markup.
		$text = wp_strip_all_tags( $text );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		return trim( $text );
	}

	/**
	 * Determine the context type based on widget type and setting key.
	 *
	 * @param  string $widget_type
	 * @param  string $key
	 * @return string
	 */
	private static function determine_context( string $widget_type, string $key ): string {

		if ( in_array( $key, [ 'button_text', 'link_text' ], true ) || $widget_type === 'button' ) {
			return 'button';
		}

		if ( in_array( $key, [ 'title', 'heading', 'title_text' ], true ) || $widget_type === 'heading' ) {
			return 'heading';
		}

		if ( in_array( $key, [ 'label', 'placeholder' ], true ) || $widget_type === 'form' ) {
			return 'form_label';
		}

		if ( in_array( $key, [ 'tab_title', 'accordion_title' ], true ) ) {
			return 'heading';
		}

		return 'paragraph';
	}
}
