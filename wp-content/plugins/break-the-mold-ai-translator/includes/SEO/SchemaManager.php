<?php
/**
 * Schema Manager — injects structured data JSON-LD in the page head.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\SEO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SchemaManager {

	/**
	 * Initialize Schema injections.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_head', [ __CLASS__, 'output_schemas' ] );
	}

	/**
	 * Detect page type and output corresponding schemas.
	 *
	 * @return void
	 */
	public static function output_schemas(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		$current_lang = 'es';
		if ( function_exists( 'BreakTheMold\AITranslator\Language\LanguageResolver::resolve' ) ) {
			$current_lang = \BreakTheMold\AITranslator\Language\LanguageResolver::resolve();
		}

		// ── 1. Front Page / Home: AutoRental Schema ─────────
		if ( is_front_page() || is_home() ) {
			self::output_autorental_schema( $current_lang );
		}

		// ── 2. Single Vehicle Post Page: Car/Product Schema ──────────
		if ( is_singular( 'rrc_vehicle' ) ) {
			self::output_vehicle_schema( get_the_ID(), $current_lang );
		}
	}

	/**
	 * Output AutoRental Schema.
	 *
	 * @param  string $lang Current language code.
	 * @return void
	 */
	private static function output_autorental_schema( string $lang ): void {
		$name = 'Ramirez Rent A Car';
		$desc = ( $lang === 'en' ) 
			? 'Enjoy your trip in Roatan with transparent rates, insurance included, and vehicles adapted to your adventure.'
			: 'Disfruta tu viaje por Roatán con tarifas transparentes, seguros incluidos y vehículos adaptados a tu aventura.';

		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'AutoRental',
			'@id'         => home_url( '/' ) . '#autorental',
			'name'        => $name,
			'url'         => home_url( '/' ),
			'logo'        => home_url( '/wp-content/uploads/2026/07/Logo-Ramirez-Renta-car.png' ),
			'image'       => home_url( '/wp-content/uploads/2026/07/Logo-Ramirez-Renta-car.png' ),
			'description' => $desc,
			'address'     => [
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Roatán',
				'addressRegion'   => 'Islas de la Bahía',
				'addressCountry'  => 'HN',
			],
			'geo'         => [
				'@type'     => 'GeoCoordinates',
				'latitude'  => '16.3268',
				'longitude' => '-86.5367',
			],
			'priceRange'  => '$$',
			'areaServed'  => [
				'@type' => 'Place',
				'name'  => 'Roatan Island',
			],
		];

		echo "\n" . '<!-- Ramirez Rent A Car LocalBusiness Schema -->' . "\n";
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "\n";
		echo '</script>' . "\n";
	}

	/**
	 * Output Car Schema for a specific post.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $lang    Language code.
	 * @return void
	 */
	private static function output_vehicle_schema( int $post_id, string $lang ): void {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$title = get_the_title( $post_id );
		$url   = get_permalink( $post_id );
		$desc  = $post->post_excerpt ?: $post->post_title;
		$image = get_the_post_thumbnail_url( $post_id, 'full' ) ?: home_url( '/wp-content/uploads/2026/07/Logo-Ramirez-Renta-car.png' );

		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Car',
			'@id'         => $url . '#car',
			'name'        => $title,
			'url'         => $url,
			'image'       => $image,
			'description' => $desc,
			'brand'       => [
				'@type' => 'Brand',
				'name'  => 'Ramirez Rent A Car',
			],
			'offers'      => [
				'@type'         => 'Offer',
				'url'           => $url,
				'priceCurrency' => 'USD',
				'seller'        => [
					'@type' => 'AutoRental',
					'name'  => 'Ramirez Rent A Car',
					'url'   => home_url( '/' ),
				],
			],
		];

		echo "\n" . '<!-- Ramirez Rent A Car Vehicle Schema -->' . "\n";
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "\n";
		echo '</script>' . "\n";
	}
}
