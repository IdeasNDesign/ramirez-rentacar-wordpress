<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\REST;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;
use WP_REST_Server;

class Routes {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function register() {
		$namespace = 'ramirez-paypal/v1';

		// Public Checkout Endpoints
		register_rest_route( $namespace, '/reservations/(?P<token>[a-zA-Z0-9]+)/order', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'create_order' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/reservations/(?P<token>[a-zA-Z0-9]+)/capture', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'capture_order' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/reservations/(?P<token>[a-zA-Z0-9]+)/status', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_status' ],
			'permission_callback' => '__return_true'
		] );

		// Webhook Endpoint (Public, verified internally)
		register_rest_route( $namespace, '/webhook', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'handle_webhook' ],
			'permission_callback' => '__return_true'
		] );

		// Admin endpoints
		register_rest_route( $namespace, '/admin/payments', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_admin_payments' ],
			'permission_callback' => [ $this, 'check_admin_permission' ]
		] );

		register_rest_route( $namespace, '/admin/payments/(?P<id>\d+)/refund', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'refund_payment' ],
			'permission_callback' => [ $this, 'check_refund_permission' ]
		] );

		register_rest_route( $namespace, '/admin/health', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_health' ],
			'permission_callback' => [ $this, 'check_admin_permission' ]
		] );

		register_rest_route( $namespace, '/admin/reservations/(?P<id>\d+)/deliver', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'deliver_reservation' ],
			'permission_callback' => [ $this, 'check_agent_permission' ]
		] );
	}

	public function create_order( $request ) {
		$controller = new CreateOrderController( $this->container );
		return $controller->execute( $request );
	}

	public function capture_order( $request ) {
		$controller = new CaptureOrderController( $this->container );
		return $controller->execute( $request );
	}

	public function get_status( $request ) {
		$controller = new PaymentStatusController( $this->container );
		return $controller->execute( $request );
	}

	public function handle_webhook( $request ) {
		$controller = new WebhookController( $this->container );
		return $controller->execute( $request );
	}

	public function get_admin_payments( $request ) {
		$controller = new AdminPaymentController( $this->container );
		return $controller->execute( $request );
	}

	public function refund_payment( $request ) {
		$controller = new AdminPaymentController( $this->container );
		return $controller->refund( $request );
	}

	public function get_health( $request ) {
		$controller = new AdminPaymentController( $this->container );
		return $controller->health( $request );
	}

	public function check_admin_permission() {
		return current_user_can( \BreakTheMold\RamirezPayPal\Core\Capabilities::MANAGE_PAYPAL );
	}

	public function check_refund_permission() {
		return current_user_can( \BreakTheMold\RamirezPayPal\Core\Capabilities::REFUND_PAYMENT );
	}

	public function check_agent_permission() {
		return is_user_logged_in();
	}

	public function deliver_reservation( $request ) {
		$id = intval( $request->get_param( 'id' ) );
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_reservations';
		
		$res = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
		if ( ! $res ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Reserva no encontrada.' ], 404 );
		}
		
		$wpdb->update( $table, [
			'checked_out_at'     => current_time( 'mysql' ),
			'reservation_status' => 'checked_out'
		], [ 'id' => $id ] );
		
		// Log audit
		$wpdb->insert( $wpdb->prefix . 'rrc_audit_log', [
			'actor_user_id'   => get_current_user_id(),
			'actor_type'      => 'user',
			'action'          => 'DELIVER_VEHICLE',
			'entity_type'     => 'reservation',
			'entity_id'       => $id,
			'old_values_json' => json_encode( [ 'checked_out_at' => $res->checked_out_at, 'reservation_status' => $res->reservation_status ] ),
			'new_values_json' => json_encode( [ 'checked_out_at' => current_time( 'mysql' ), 'reservation_status' => 'checked_out' ] ),
			'created_at'      => current_time( 'mysql' )
		] );
		
		return rest_ensure_response( [ 'success' => true, 'message' => 'Vehículo marcado como entregado.' ] );
	}
}
