<?php
/**
 * Admin Menu — registers admin menus, submenus, and renders views.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AdminMenu {

	/**
	 * Register the main menu and all 15 submenus.
	 *
	 * @return void
	 */
	public static function register(): void {

		$cap = 'btmat_manage_settings';

		add_menu_page(
			__( 'BTM Translator', 'break-the-mold-ai-translator' ),
			__( 'BTM Translator', 'break-the-mold-ai-translator' ),
			$cap,
			'btmat-dashboard',
			[ __CLASS__, 'render_dashboard' ],
			'dashicons-translation',
			81
		);

		$submenus = [
			'btmat-dashboard' => [ __( 'Dashboard', 'break-the-mold-ai-translator' ), 'render_dashboard' ],
			'btmat-memory'    => [ __( 'Translation Memory', 'break-the-mold-ai-translator' ), 'render_memory' ],
			'btmat-pages'     => [ __( 'Pages', 'break-the-mold-ai-translator' ), 'render_pages' ],
			'btmat-glossary'  => [ __( 'Glossary', 'break-the-mold-ai-translator' ), 'render_glossary' ],
			'btmat-jobs'      => [ __( 'Jobs', 'break-the-mold-ai-translator' ), 'render_jobs' ],
			'btmat-usage'     => [ __( 'Usage', 'break-the-mold-ai-translator' ), 'render_usage' ],
			'btmat-settings'  => [ __( 'Settings', 'break-the-mold-ai-translator' ), 'render_settings' ],
			'btmat-status'    => [ __( 'System Status', 'break-the-mold-ai-translator' ), 'render_status' ],
		];

		foreach ( $submenus as $slug => $data ) {
			add_submenu_page(
				'btmat-dashboard',
				$data[0],
				$data[0],
				$cap,
				$slug,
				[ __CLASS__, $data[1] ]
			);
		}
	}

	public static function render_dashboard(): void {
		self::load_view( 'dashboard' );
	}

	public static function render_memory(): void {
		self::load_view( 'memory' );
	}

	public static function render_pages(): void {
		self::load_view( 'pages' );
	}

	public static function render_glossary(): void {
		self::load_view( 'glossary' );
	}

	public static function render_jobs(): void {
		self::load_view( 'jobs' );
	}

	public static function render_usage(): void {
		self::load_view( 'usage' );
	}

	public static function render_settings(): void {
		self::load_view( 'settings' );
	}

	public static function render_status(): void {
		self::load_view( 'status' );
	}

	private static function load_view( string $name ): void {
		$file = BTMAT_PATH . 'includes/Admin/Views/' . $name . '.php';
		if ( file_exists( $file ) ) {
			include $file;
		} else {
			echo '<div class="wrap"><h2>' . esc_html( ucfirst( $name ) ) . '</h2><p>View file not found.</p></div>';
		}
	}
}
