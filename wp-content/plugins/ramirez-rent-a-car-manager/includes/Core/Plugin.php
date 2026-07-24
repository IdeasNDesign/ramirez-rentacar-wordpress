<?php
namespace RamirezRentACar\Core;

class Plugin {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Register Custom Post Type
		add_action( 'init', [ $this, 'register_cpt' ] );

		// Register Frontend Router
		require_once RRC_PATH . 'includes/Frontend/BookingRouter.php';
		\RamirezRentACar\Frontend\BookingRouter::init();

		// Register REST controllers
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// Register Admin hooks
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
		}

		// Register Elementor hooks
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );
	}

	public function register_cpt() {
		require_once RRC_PATH . 'includes/Core/CPT.php';
		\RamirezRentACar\Core\CPT::register();
	}

	public function register_rest_routes() {
		require_once RRC_PATH . 'includes/REST/Routes.php';
		\RamirezRentACar\REST\Routes::register();

		require_once RRC_PATH . 'includes/REST/AppRoutes.php';
		\RamirezRentACar\REST\AppRoutes::register();
	}

	public function register_admin_menu() {
		require_once RRC_PATH . 'includes/Admin/Menu.php';
		\RamirezRentACar\Admin\Menu::register();
	}

	public function register_elementor_widgets( $widgets_manager ) {
		require_once RRC_PATH . 'includes/Elementor/Widgets.php';
		\RamirezRentACar\Elementor\Widgets::register( $widgets_manager );
	}
}
