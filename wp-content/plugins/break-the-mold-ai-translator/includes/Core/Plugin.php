<?php
/**
 * Plugin bootstrap — singleton that wires every module.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {

	/** @var self|null */
	private static ?self $instance = null;

	/** Singleton accessor — called on `plugins_loaded`. */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Wire everything. */
	private function __construct() {
		$this->load_textdomain();
		$this->init_hooks();
	}

	/** Load plugin text domain. */
	private function load_textdomain(): void {
		load_plugin_textdomain(
			'break-the-mold-ai-translator',
			false,
			dirname( BTMAT_BASENAME ) . '/languages'
		);
	}

	/** Register global hooks. */
	private function init_hooks(): void {

		// ── Cron interval ────────────────────────────────────
		add_filter( 'cron_schedules', [ $this, 'add_cron_intervals' ] );

		// ── Admin ────────────────────────────────────────────
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		}

		// ── Frontend ─────────────────────────────────────────
		if ( ! is_admin() || wp_doing_ajax() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
			add_action( 'init', [ $this, 'register_shortcodes' ] );

			// Apply translations via filters and output buffer.
			add_action( 'wp', [ \BreakTheMold\AITranslator\Frontend\FrontendController::class, 'init' ] );

			// Inject alternate language SEO links.
			\BreakTheMold\AITranslator\SEO\HreflangManager::init();
		}

		// ── REST API ─────────────────────────────────────────
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// ── Elementor ────────────────────────────────────────
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );

		// ── Cron (queue processing) ──────────────────────────
		add_action( 'btmat_process_queue', [ $this, 'process_translation_queue' ] );

		// ── SEO Routing ──────────────────────────────────────
		\BreakTheMold\AITranslator\SEO\Router::init();

		// ── Schema Markup ────────────────────────────────────
		\BreakTheMold\AITranslator\SEO\SchemaManager::init();
		// ── Cookie Manager ───────────────────────────────────
		\BreakTheMold\AITranslator\Frontend\CookieManager::init();
	}

	/* ──────────────────────────────────────────────────────────
	 * Stub callbacks — each will delegate to its own module
	 * once the corresponding phase is implemented.
	 * ────────────────────────────────────────────────────────── */

	/**
	 * Register custom cron intervals.
	 *
	 * @param  array $schedules Existing schedules.
	 * @return array
	 */
	public function add_cron_intervals( array $schedules ): array {
		$schedules['five_minutes'] = [
			'interval' => 300,
			'display'  => __( 'Every Five Minutes', 'break-the-mold-ai-translator' ),
		];
		return $schedules;
	}

	public function register_admin_menu(): void {
		\BreakTheMold\AITranslator\Admin\AdminMenu::register();
	}

	public function enqueue_admin_assets( string $hook ): void {
		// Phase 6 — AdminAssets
	}

	public function enqueue_frontend_assets(): void {
		wp_enqueue_style(
			'btm-language-switcher',
			BTMAT_URL . 'assets/frontend/css/language-switcher.css',
			[],
			BTMAT_VERSION
		);

		wp_enqueue_style(
			'btm-translator',
			BTMAT_URL . 'assets/frontend/css/translator.css',
			[],
			BTMAT_VERSION
		);

		wp_enqueue_script(
			'btm-language-switcher',
			BTMAT_URL . 'assets/frontend/js/language-switcher.js',
			[],
			BTMAT_VERSION,
			true
		);

		wp_enqueue_script(
			'btm-translator',
			BTMAT_URL . 'assets/frontend/js/translator.js',
			[],
			BTMAT_VERSION,
			true
		);

		wp_enqueue_script(
			'btm-dynamic-observer',
			BTMAT_URL . 'assets/frontend/js/dynamic-observer.js',
			[ 'btm-translator' ],
			BTMAT_VERSION,
			true
		);

		wp_localize_script( 'btm-language-switcher', 'btmatConfig', [
			'cookieDuration' => get_option( 'btmat_cookie_duration', 365 ),
			'currentLang'    => \BreakTheMold\AITranslator\Language\LanguageResolver::resolve(),
			'restUrl'        => esc_url_raw( rest_url( 'break-the-mold/v1/' ) ),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'dictionary'     => \BreakTheMold\AITranslator\Frontend\FrontendController::get_dictionary(),
		] );
	}

	public function register_shortcodes(): void {
		add_shortcode( 'btm_language_switcher', function ( $atts ) {
			$atts = shortcode_atts( [
				'layout'      => 'buttons',
				'show_labels' => 'yes',
				'show_flags'  => 'yes',
			], $atts, 'btm_language_switcher' );

			return \BreakTheMold\AITranslator\Elementor\LanguageSwitcherWidget::render_switcher( $atts );
		} );
	}

	public function register_rest_routes(): void {
		\BreakTheMold\AITranslator\REST\Routes::register();
	}

	public function register_elementor_widgets( $widgets_manager ): void {
		\BreakTheMold\AITranslator\Elementor\Integration::register_widgets( $widgets_manager );
	}

	public function process_translation_queue(): void {
		\BreakTheMold\AITranslator\Queue\JobProcessor::run_next();
	}

	/* ──────────────────────────────────────────────────────────
	 * Helpers
	 * ────────────────────────────────────────────────────────── */

	/**
	 * Resolve the Groq API key following the documented priority.
	 *
	 * 1. getenv('BTMAT_GROQ_API_KEY')
	 * 2. getenv('GROQ_API_KEY')
	 * 3. PHP constant BTMAT_GROQ_API_KEY
	 * 4. null  → provider disabled
	 *
	 * @return string|null
	 */
	public static function resolve_api_key(): ?string {

		$key = getenv( 'BTMAT_GROQ_API_KEY' );
		if ( $key ) {
			return $key;
		}

		$key = getenv( 'GROQ_API_KEY' );
		if ( $key ) {
			return $key;
		}

		if ( defined( 'BTMAT_GROQ_API_KEY' ) ) {
			return BTMAT_GROQ_API_KEY;
		}

		return null;
	}
}
