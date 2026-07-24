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

		register_rest_route( $namespace, '/tracker', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'track_reservation' ],
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

		register_rest_route( $namespace, '/admin/reservations/(?P<id>\d+)/send-message', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'send_customer_message' ],
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
			'reservation_status' => 'checked_out',
			'payment_status'     => 'paid',
			'remaining_balance'  => 0.00,
			'amount_paid'        => $res->total_amount
		], [ 'id' => $id ] );
		
		// Log audit
		$wpdb->insert( $wpdb->prefix . 'rrc_audit_log', [
			'actor_user_id'   => get_current_user_id(),
			'actor_type'      => 'user',
			'action'          => 'DELIVER_VEHICLE',
			'entity_type'     => 'reservation',
			'entity_id'       => $id,
			'old_values_json' => json_encode( [ 
				'checked_out_at'     => $res->checked_out_at, 
				'reservation_status' => $res->reservation_status,
				'payment_status'     => $res->payment_status,
				'remaining_balance'  => $res->remaining_balance,
				'amount_paid'        => $res->amount_paid
			] ),
			'new_values_json' => json_encode( [ 
				'checked_out_at'     => current_time( 'mysql' ), 
				'reservation_status' => 'checked_out',
				'payment_status'     => 'paid',
				'remaining_balance'  => 0.00,
				'amount_paid'        => $res->total_amount
			] ),
			'created_at'      => current_time( 'mysql' )
		] );

		// Send friendly return reminder email to customer
		if ( class_exists( '\\BreakTheMold\\RamirezPayPal\\Notifications\\CustomerVehicleDelivered' ) ) {
			\BreakTheMold\RamirezPayPal\Notifications\CustomerVehicleDelivered::send( $id );
		}

		// Schedule friendly email reminder for 6:00 AM on the day of return (Honduras local time)
		$return_date = date( 'Y-m-d', strtotime( $res->return_at ) );
		$reminder_time = strtotime( $return_date . ' 06:00:00' );

		// If 6:00 AM on the return day is in the future, schedule the event
		if ( $reminder_time > current_time( 'timestamp' ) ) {
			wp_clear_scheduled_hook( 'rrc_send_return_day_reminder', [ $id ] );
			wp_schedule_single_event( $reminder_time, 'rrc_send_return_day_reminder', [ $id ] );
		}
		
		return rest_ensure_response( [ 'success' => true, 'message' => 'Vehículo marcado como entregado.' ] );
	}

	public function track_reservation( $request ) {
		$reference = sanitize_text_field( $request->get_param( 'reference' ) );

		// Strip '#' from reference if present
		$reference = ltrim( $reference, '#' );

		if ( empty( $reference ) ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'El código de reserva es obligatorio.' ], 400 );
		}

		global $wpdb;
		$res_table   = $wpdb->prefix . 'rrc_reservations';
		$cust_table  = $wpdb->prefix . 'rrc_customers';
		$model_table = $wpdb->prefix . 'rrc_vehicle_models';
		$loc_table   = $wpdb->prefix . 'rrc_locations';

		$res = $wpdb->get_row( $wpdb->prepare(
			"SELECT r.*, 
			        c.first_name, c.last_name,
			        m.public_name AS vehicle_name, m.post_id,
			        pl.name AS pickup_location_name,
			        rl.name AS return_location_name
			 FROM $res_table r
			 JOIN $cust_table c ON r.customer_id = c.id
			 LEFT JOIN $model_table m ON r.vehicle_model_id = m.id
			 LEFT JOIN $loc_table pl ON r.pickup_location_id = pl.id
			 LEFT JOIN $loc_table rl ON r.return_location_id = rl.id
			 WHERE r.public_reference = %s",
			$reference
		) );

		if ( ! $res ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Reserva no encontrada.' ], 404 );
		}

		// Resolve vehicle image
		$vehicle_img = '';
		if ( ! empty( $res->post_id ) ) {
			$vehicle_img = get_post_meta( $res->post_id, '_rrc_image_url', true );
		}

		$dep = (float) ($res->deposit_paid_amount > 0 ? $res->deposit_paid_amount : $res->deposit_amount);
		$rem = (float) $res->remaining_balance;

		return rest_ensure_response( [
			'success' => true,
			'reservation' => [
				'reference'          => $res->public_reference,
				'status'             => $res->reservation_status,
				'payment_status'     => $res->payment_status,
				'customer_name'      => $res->first_name . ' ' . $res->last_name,
				'vehicle_name'       => $res->vehicle_name,
				'vehicle_image'      => $vehicle_img,
				'pickup_location'    => $res->pickup_location_name,
				'return_location'    => $res->return_location_name,
				'pickup_at'          => $res->pickup_at,
				'return_at'          => $res->return_at,
				'total_amount'       => (float) $res->total_amount,
				'deposit_paid'       => $dep,
				'remaining_balance'  => $rem,
				'checked_out_at'     => $res->checked_out_at,
				'returned_at'        => $res->returned_at,
				'created_at'         => $res->created_at
			]
		] );
	}

	public function send_customer_message( $request ) {
		$id      = intval( $request->get_param( 'id' ) );
		$message = sanitize_textarea_field( $request->get_param( 'message' ) );

		if ( empty( $message ) ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'El mensaje no puede estar vacío.' ], 400 );
		}

		global $wpdb;
		$res_table  = $wpdb->prefix . 'rrc_reservations';
		$cust_table = $wpdb->prefix . 'rrc_customers';

		$res = $wpdb->get_row( $wpdb->prepare(
			"SELECT r.*, c.first_name, c.last_name, c.email AS cust_email
			 FROM $res_table r
			 JOIN $cust_table c ON r.customer_id = c.id
			 WHERE r.id = %d",
			$id
		) );

		if ( ! $res || empty( $res->cust_email ) ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Reserva o correo de cliente no encontrado.' ], 404 );
		}

		$to = $res->cust_email;
		$subject = '🚗 Mensaje sobre tu Reserva - Ramirez Rent a Car';
		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: Ramirez Rent a Car <' . get_option( 'admin_email' ) . '>',
		];

		// Build beautiful branded email template
		$logo_url = 'https://ramirezrentacar.com/wp-content/uploads/2026/R-Rent-a-car-logo-app.png';
		$formatted_message = nl2br( esc_html( $message ) );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title>Mensaje de Ramirez Rent a Car</title>
		</head>
		<body style="font-family: sans-serif; color: #334155; line-height: 1.6; background-color: #f8fafc; padding: 20px;">
			<div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
				
				<!-- LOGO HEADER -->
				<div style="text-align: center; margin-bottom: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="Ramirez Rent a Car" style="max-height: 55px; display: inline-block;">
				</div>

				<h3 style="color: #E8272C; margin-top: 0; font-size: 18px;">Estimado(a) <?php echo esc_html( $res->first_name ); ?>,</h3>
				
				<div style="font-size: 15px; color: #334155; margin-bottom: 25px; background-color: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #f1f5f9; line-height: 1.7;">
					<?php echo $formatted_message; ?>
				</div>

				<p style="font-size: 13px; color: #94a3b8; text-align: center;">
					Detalle de referencia de reserva: <strong>#<?php echo esc_html( $res->public_reference ); ?></strong>
				</p>

				<div style="text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 12px; color: #94a3b8; margin-top: 25px;">
					<p>Atentamente,</p>
					<p style="font-weight: bold; margin-top: 5px; color: #0f172a;">Equipo Ramirez Rent a Car</p>
					<p style="margin-top: 15px;">© <?php echo date('Y'); ?> Ramirez Rent a Car. Todos los derechos reservados.</p>
				</div>

			</div>
		</body>
		</html>
		<?php
		$body = ob_get_clean();

		$sent = wp_mail( $to, $subject, $body, $headers );

		if ( $sent ) {
			// Log audit log
			$wpdb->insert( $wpdb->prefix . 'rrc_audit_log', [
				'actor_user_id'   => get_current_user_id(),
				'actor_type'      => 'user',
				'action'          => 'SEND_CUSTOMER_EMAIL_MESSAGE',
				'entity_type'     => 'reservation',
				'entity_id'       => $id,
				'new_values_json' => json_encode( [ 'message_length' => strlen( $message ) ] ),
				'created_at'      => current_time( 'mysql' )
			] );

			return rest_ensure_response( [ 'success' => true, 'message' => 'Mensaje enviado correctamente al correo del cliente.' ] );
		}

		return new \WP_REST_Response( [ 'success' => false, 'message' => 'El servidor no pudo procesar el envío del correo.' ], 500 );
	}
}
