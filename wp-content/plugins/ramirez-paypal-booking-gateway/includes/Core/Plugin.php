<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Core;

class Plugin {
	private static $instance = null;
	private $container;

	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->register_autoloader();
		$this->bootstrap();
	}

	private function register_autoloader() {
		spl_autoload_register( function ( $class ) {
			$prefix = 'BreakTheMold\\RamirezPayPal\\';
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return;
			}
			$relative_class = substr( $class, $len );
			$file = RRC_PAYPAY_GATEWAY_PATH . 'includes/' . str_replace( '\\', '/', $relative_class ) . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		} );
	}

	private function bootstrap() {
		if ( ! Requirements::check() ) {
			return;
		}

		$this->container = new ServiceContainer();
		$this->register_services();

		// Register hooks
		add_action( 'rest_api_init', [ $this->container->get( 'rest_routes' ), 'register' ] );
		add_action( 'rrc_send_return_day_reminder', [ $this, 'send_return_day_reminder' ], 10, 1 );
		
		if ( is_admin() ) {
			add_action( 'admin_menu', [ $this->container->get( 'admin_menu' ), 'register' ], 15 );
			add_action( 'admin_enqueue_scripts', [ $this->container->get( 'admin_menu' ), 'enqueue_assets' ] );
			add_action( 'wp_ajax_rrc_paypal_check_connection', [ $this->container->get( 'admin_menu' ), 'ajax_check_connection' ] );
		}

		// Frontend hooks
		add_action( 'wp_enqueue_scripts', [ $this->container->get( 'frontend_assets' ), 'enqueue' ] );
	}

	private function register_services() {
		// Repositories
		$this->container->set( 'payment_repo', new \BreakTheMold\RamirezPayPal\Infrastructure\Repositories\PaymentRepository() );
		$this->container->set( 'webhook_repo', new \BreakTheMold\RamirezPayPal\Infrastructure\Repositories\WebhookRepository() );
		$this->container->set( 'refund_repo', new \BreakTheMold\RamirezPayPal\Infrastructure\Repositories\RefundRepository() );

		// Core Config
		$this->container->set( 'credentials_provider', new \BreakTheMold\RamirezPayPal\Configuration\CredentialsProvider() );
		$this->container->set( 'settings', new \BreakTheMold\RamirezPayPal\Configuration\PayPalSettings( $this->container->get( 'credentials_provider' ) ) );

		// Security & Auditing
		$this->container->set( 'audit_logger', new \BreakTheMold\RamirezPayPal\Security\AuditLogger() );
		$this->container->set( 'idempotency_service', new \BreakTheMold\RamirezPayPal\Security\IdempotencyService() );

		// PayPal Infrastructure API Clients
		$this->container->set( 'paypal_client', new \BreakTheMold\RamirezPayPal\Infrastructure\PayPal\PayPalClient( $this->container->get( 'settings' ) ) );
		$this->container->set( 'oauth_provider', new \BreakTheMold\RamirezPayPal\Infrastructure\PayPal\OAuthTokenProvider( $this->container->get( 'settings' ) ) );
		$this->container->set( 'orders_api', new \BreakTheMold\RamirezPayPal\Infrastructure\PayPal\OrdersApi( $this->container->get( 'paypal_client' ) ) );
		$this->container->set( 'captures_api', new \BreakTheMold\RamirezPayPal\Infrastructure\PayPal\CapturesApi( $this->container->get( 'paypal_client' ) ) );
		$this->container->set( 'refunds_api', new \BreakTheMold\RamirezPayPal\Infrastructure\PayPal\RefundsApi( $this->container->get( 'paypal_client' ) ) );
		$this->container->set( 'webhook_verifier', new \BreakTheMold\RamirezPayPal\Infrastructure\PayPal\WebhookVerifier( $this->container->get( 'paypal_client' ) ) );

		// Adapters
		$this->container->set( 'booking_adapter', new \BreakTheMold\RamirezPayPal\Booking\BookingGatewayAdapter() );

		// REST Controllers
		$this->container->set( 'rest_routes', new \BreakTheMold\RamirezPayPal\REST\Routes( $this->container ) );

		// Admin Pages
		$this->container->set( 'admin_menu', new \BreakTheMold\RamirezPayPal\Admin\AdminMenu( $this->container ) );

		// Frontend
		$this->container->set( 'frontend_assets', new \BreakTheMold\RamirezPayPal\Frontend\CheckoutAssets( $this->container ) );
	}

	public function get_container() {
		return $this->container;
	}

	public function send_return_day_reminder( $reservation_id ) {
		if ( class_exists( '\\BreakTheMold\\RamirezPayPal\\Notifications\\CustomerReturnDayReminder' ) ) {
			\BreakTheMold\RamirezPayPal\Notifications\CustomerReturnDayReminder::send( $reservation_id );
		}
	}
}
