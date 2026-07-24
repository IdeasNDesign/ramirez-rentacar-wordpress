<?php
/**
 * Author: Break The Mold
 */

namespace RamirezRentACar\REST;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class AppRoutes {

	public static function register() {
		$namespace = 'ramirez-rent-a-car/v1/app';

		// Auth
		register_rest_route( $namespace, '/auth/login', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'login' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/auth/logout', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'logout' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/auth/me', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_current_user' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		// Dashboard
		register_rest_route( $namespace, '/dashboard', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_dashboard_data' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		// Reservations
		register_rest_route( $namespace, '/reservations', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_reservations' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/reservations/(?P<id>\d+)', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_reservation_detail' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		// Decision endpoints
		register_rest_route( $namespace, '/reservations/(?P<id>\d+)/approve', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'approve_reservation' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/reservations/(?P<id>\d+)/hold', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'hold_reservation' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/reservations/(?P<id>\d+)/reject', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'reject_reservation' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/reservations/(?P<id>\d+)/reject-payment', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'reject_reservation_payment' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/reservations/(?P<id>\d+)/confirm', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'confirm_reservation' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/reservations/(?P<id>\d+)/assign-unit', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'assign_unit' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		// Fleet & Availability
		register_rest_route( $namespace, '/fleet', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_fleet' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/fleet/availability', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_fleet_availability' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		// Audit Log
		register_rest_route( $namespace, '/audit', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_audit_log' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		// User Management
		register_rest_route( $namespace, '/users', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_app_users' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );

		register_rest_route( $namespace, '/users', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'create_app_user' ],
			'permission_callback' => [ __CLASS__, 'check_auth' ]
		] );
	}

	public static function check_auth( WP_REST_Request $request ) {
		$auth_header = $request->get_header( 'Authorization' );
		$token = '';

		if ( $auth_header && preg_match( '/Bearer\s+(.*)$/i', $auth_header, $matches ) ) {
			$token = trim( $matches[1] );
		} elseif ( isset( $_COOKIE['rrc_op_token'] ) ) {
			$token = sanitize_text_field( $_COOKIE['rrc_op_token'] );
		}

		if ( empty( $token ) ) {
			return new \WP_Error( 'rest_unauthorized', 'Token de sesión no proporcionado.', [ 'status' => 401 ] );
		}

		global $wpdb;
		$token_hash = hash( 'sha256', $token );
		$sessions_table = $wpdb->prefix . 'rrc_app_sessions';

		$session = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $sessions_table WHERE token_hash = %s AND expires_at > %s",
			$token_hash, current_time( 'mysql' )
		) );

		if ( ! $session ) {
			return new \WP_Error( 'rest_forbidden', 'Sesión inválida o expirada.', [ 'status' => 403 ] );
		}

		// Update activity
		$wpdb->update( $sessions_table, [ 'last_activity_at' => current_time( 'mysql' ) ], [ 'id' => $session->id ] );

		wp_set_current_user( $session->user_id );
		return true;
	}

	public static function login( WP_REST_Request $request ) {
		$params   = $request->get_params();
		$username = sanitize_text_field( isset( $params['username'] ) ? $params['username'] : '' );
		$password = isset( $params['password'] ) ? $params['password'] : '';

		$user = wp_authenticate( $username, $password );

		if ( is_wp_error( $user ) ) {
			return new WP_REST_Response( [
				'success' => false,
				'message' => 'Credenciales inválidas.'
			], 401 );
		}

		// Create session token
		$token = wp_generate_password( 64, false );
		$token_hash = hash( 'sha256', $token );

		global $wpdb;
		$sessions_table = $wpdb->prefix . 'rrc_app_sessions';

		$wpdb->insert( $sessions_table, [
			'user_id'          => $user->ID,
			'token_hash'       => $token_hash,
			'ip_address'       => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent'       => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'expires_at'       => date( 'Y-m-d H:i:s', strtotime( '+24 hours' ) ),
			'created_at'       => current_time( 'mysql' ),
			'last_activity_at' => current_time( 'mysql' )
		] );

		self::log_audit( $user->ID, 'LOGIN', 'user', $user->ID, null, [ 'ip' => $_SERVER['REMOTE_ADDR'] ?? '' ] );

		return rest_ensure_response( [
			'success' => true,
			'token'   => $token,
			'user'    => [
				'id'           => $user->ID,
				'display_name' => $user->display_name,
				'email'        => $user->user_email,
				'roles'        => $user->roles
			]
		] );
	}

	public static function logout( WP_REST_Request $request ) {
		$auth_header = $request->get_header( 'Authorization' );
		if ( $auth_header && preg_match( '/Bearer\s+(.*)$/i', $auth_header, $matches ) ) {
			$token = trim( $matches[1] );
			global $wpdb;
			$token_hash = hash( 'sha256', $token );
			$wpdb->delete( $wpdb->prefix . 'rrc_app_sessions', [ 'token_hash' => $token_hash ] );
		}
		return rest_ensure_response( [ 'success' => true, 'message' => 'Sesión cerrada exitosamente.' ] );
	}

	public static function get_current_user( WP_REST_Request $request ) {
		$user = wp_get_current_user();
		return rest_ensure_response( [
			'success' => true,
			'user'    => [
				'id'           => $user->ID,
				'display_name' => $user->display_name,
				'email'        => $user->user_email,
				'roles'        => $user->roles
			]
		] );
	}

	public static function get_dashboard_data( WP_REST_Request $request ) {
		global $wpdb;
		$res_table = $wpdb->prefix . 'rrc_reservations';
		$units_table = $wpdb->prefix . 'rrc_vehicle_units';

		$stats = [
			'new_requests'        => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $res_table WHERE reservation_status = 'requested'" ) ),
			'pending_approval'    => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $res_table WHERE reservation_status = 'under_review'" ) ),
			'waiting_for_payment' => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $res_table WHERE reservation_status = 'waiting_for_customer' OR payment_status = 'unpaid'" ) ),
			'confirmed_paid'      => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $res_table WHERE reservation_status = 'confirmed' AND payment_status = 'paid'" ) ),
			'available_units'     => intval( $wpdb->get_var( "SELECT COUNT(*) FROM $units_table WHERE status = 'available'" ) ),
			'today_pickups'       => intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $res_table WHERE DATE(pickup_at) = %s", current_time( 'Y-m-d' ) ) ) ),
			'today_returns'       => intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $res_table WHERE DATE(return_at) = %s", current_time( 'Y-m-d' ) ) ) )
		];

		// Action required items
		$action_required = $wpdb->get_results(
			"SELECT r.*, c.first_name, c.last_name, c.email, c.phone, m.public_name AS vehicle_name, m.post_id
			 FROM $res_table r
			 JOIN {$wpdb->prefix}rrc_customers c ON r.customer_id = c.id
			 LEFT JOIN {$wpdb->prefix}rrc_vehicle_models m ON r.vehicle_model_id = m.id
			 WHERE r.reservation_status IN ('requested', 'under_review', 'hold', 'draft')
			 ORDER BY r.created_at DESC LIMIT 10"
		);

		foreach ( $action_required as $item ) {
			$img_url = '';
			if ( ! empty( $item->post_id ) ) {
				$img_url = get_post_meta( $item->post_id, '_rrc_image_url', true );
				if ( empty( $img_url ) && has_post_thumbnail( $item->post_id ) ) {
					$img_url = get_the_post_thumbnail_url( $item->post_id, 'medium' );
				}
			}
			if ( empty( $img_url ) ) {
				$img_url = 'https://img.freepik.com/vectores-premium/icono-coche-gris-silueta-coche-ilustracion-vectorial_755519-158.jpg';
			}
			$item->image_url = $img_url;
		}

		return rest_ensure_response( [
			'success'         => true,
			'stats'           => $stats,
			'action_required' => $action_required
		] );
	}

	public static function get_reservations( WP_REST_Request $request ) {
		global $wpdb;
		$res_table = $wpdb->prefix . 'rrc_reservations';
		$cust_table = $wpdb->prefix . 'rrc_customers';
		$model_table = $wpdb->prefix . 'rrc_vehicle_models';
		$units_table = $wpdb->prefix . 'rrc_vehicle_units';

		$reservations = $wpdb->get_results(
			"SELECT r.*, 
			        c.first_name, c.last_name, c.email, c.phone, c.whatsapp,
			        m.public_name AS vehicle_name, m.post_id,
			        u.unit_code, u.license_plate
			 FROM $res_table r
			 JOIN $cust_table c ON r.customer_id = c.id
			 LEFT JOIN $model_table m ON r.vehicle_model_id = m.id
			 LEFT JOIN $units_table u ON r.assigned_unit_id = u.id
			 ORDER BY r.created_at DESC LIMIT 100"
		);

		foreach ( $reservations as $res ) {
			$img_url = '';
			if ( ! empty( $res->post_id ) ) {
				$img_url = get_post_meta( $res->post_id, '_rrc_image_url', true );
				if ( empty( $img_url ) && has_post_thumbnail( $res->post_id ) ) {
					$img_url = get_the_post_thumbnail_url( $res->post_id, 'medium' );
				}
			}
			if ( empty( $img_url ) ) {
				$img_url = 'https://img.freepik.com/vectores-premium/icono-coche-gris-silueta-coche-ilustracion-vectorial_755519-158.jpg';
			}
			$res->image_url = $img_url;
		}

		return rest_ensure_response( [
			'success'      => true,
			'reservations' => $reservations
		] );
	}

	public static function get_reservation_detail( WP_REST_Request $request ) {
		$id = intval( $request->get_param( 'id' ) );
		global $wpdb;

		$res = $wpdb->get_row( $wpdb->prepare(
			"SELECT r.*, 
			        c.first_name, c.last_name, c.email, c.phone, c.whatsapp, c.country,
			        m.public_name AS vehicle_name, m.category,
			        pl.name AS pickup_location_name,
			        rl.name AS return_location_name,
			        u.unit_code, u.license_plate
			 FROM {$wpdb->prefix}rrc_reservations r
			 JOIN {$wpdb->prefix}rrc_customers c ON r.customer_id = c.id
			 LEFT JOIN {$wpdb->prefix}rrc_vehicle_models m ON r.vehicle_model_id = m.id
			 LEFT JOIN {$wpdb->prefix}rrc_locations pl ON r.pickup_location_id = pl.id
			 LEFT JOIN {$wpdb->prefix}rrc_locations rl ON r.return_location_id = rl.id
			 LEFT JOIN {$wpdb->prefix}rrc_vehicle_units u ON r.assigned_unit_id = u.id
			 WHERE r.id = %d",
			$id
		) );

		if ( ! $res ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Reserva no encontrada.' ], 404 );
		}

		return rest_ensure_response( [
			'success'     => true,
			'reservation' => $res
		] );
	}

	public static function approve_reservation( WP_REST_Request $request ) {
		$id = intval( $request->get_param( 'id' ) );
		$params = $request->get_params();
		$reason = sanitize_text_field( $params['reason'] ?? 'Solicitud aprobada por el asesor' );

		global $wpdb;
		$table = $wpdb->prefix . 'rrc_reservations';
		$old_status = $wpdb->get_var( $wpdb->prepare( "SELECT reservation_status FROM $table WHERE id = %d", $id ) );

		$wpdb->update( $table, [
			'reservation_status' => 'APPROVED',
			'updated_at'         => current_time( 'mysql' )
		], [ 'id' => $id ] );

		self::log_audit( get_current_user_id(), 'APPROVE_RESERVATION', 'reservation', $id, [ 'status' => $old_status ], [ 'status' => 'APPROVED', 'reason' => $reason ] );

		return rest_ensure_response( [ 'success' => true, 'message' => 'Reserva aprobada exitosamente.' ] );
	}

	public static function hold_reservation( WP_REST_Request $request ) {
		$id = intval( $request->get_param( 'id' ) );
		$params = $request->get_params();
		$reason = sanitize_text_field( $params['reason'] ?? 'En espera de pago/cliente' );

		global $wpdb;
		$table = $wpdb->prefix . 'rrc_reservations';
		$old_status = $wpdb->get_var( $wpdb->prepare( "SELECT reservation_status FROM $table WHERE id = %d", $id ) );

		$wpdb->update( $table, [
			'reservation_status' => 'WAITING_FOR_CUSTOMER',
			'updated_at'         => current_time( 'mysql' )
		], [ 'id' => $id ] );

		self::log_audit( get_current_user_id(), 'HOLD_RESERVATION', 'reservation', $id, [ 'status' => $old_status ], [ 'status' => 'WAITING_FOR_CUSTOMER', 'reason' => $reason ] );

		return rest_ensure_response( [ 'success' => true, 'message' => 'Reserva puesta en espera exitosamente.' ] );
	}

	public static function reject_reservation( WP_REST_Request $request ) {
		$id = intval( $request->get_param( 'id' ) );
		$params = $request->get_params();
		$reason = sanitize_text_field( $params['reason'] ?? 'Solicitud rechazada' );

		global $wpdb;
		$table = $wpdb->prefix . 'rrc_reservations';
		$old_status = $wpdb->get_var( $wpdb->prepare( "SELECT reservation_status FROM $table WHERE id = %d", $id ) );

		$wpdb->update( $table, [
			'reservation_status' => 'REJECTED',
			'updated_at'         => current_time( 'mysql' )
		], [ 'id' => $id ] );

		self::log_audit( get_current_user_id(), 'REJECT_RESERVATION', 'reservation', $id, [ 'status' => $old_status ], [ 'status' => 'REJECTED', 'reason' => $reason ] );

		return rest_ensure_response( [ 'success' => true, 'message' => 'Reserva rechazada.' ] );
	}

	public static function reject_reservation_payment( WP_REST_Request $request ) {
		$id = intval( $request->get_param( 'id' ) );
		$params = $request->get_params();
		$reason = sanitize_text_field( $params['reason'] ?? 'Rechazo por pago fallido o no identificado' );

		global $wpdb;
		$table = $wpdb->prefix . 'rrc_reservations';

		$wpdb->update( $table, [
			'reservation_status' => 'REJECTED',
			'payment_status'     => 'PAYMENT_REJECTED',
			'updated_at'         => current_time( 'mysql' )
		], [ 'id' => $id ] );

		self::log_audit( get_current_user_id(), 'REJECT_PAYMENT', 'reservation', $id, null, [ 'reservation_status' => 'REJECTED', 'payment_status' => 'PAYMENT_REJECTED', 'reason' => $reason ] );

		return rest_ensure_response( [ 'success' => true, 'message' => 'Solicitud rechazada por pago.' ] );
	}

	public static function confirm_reservation( WP_REST_Request $request ) {
		$id = intval( $request->get_param( 'id' ) );

		global $wpdb;
		$table = $wpdb->prefix . 'rrc_reservations';

		$wpdb->update( $table, [
			'reservation_status' => 'CONFIRMED',
			'payment_status'     => 'PAID',
			'confirmed_at'       => current_time( 'mysql' ),
			'updated_at'         => current_time( 'mysql' )
		], [ 'id' => $id ] );

		// Send notification email
		\RamirezRentACar\Infrastructure\Notifications\EmailNotificationService::send_reservation_confirmation( $id );

		self::log_audit( get_current_user_id(), 'CONFIRM_RESERVATION', 'reservation', $id, null, [ 'status' => 'CONFIRMED', 'payment_status' => 'PAID' ] );

		return rest_ensure_response( [ 'success' => true, 'message' => 'Reserva confirmada exitosamente y notificación enviada al cliente.' ] );
	}

	public static function assign_unit( WP_REST_Request $request ) {
		$id = intval( $request->get_param( 'id' ) );
		$unit_id = intval( $request->get_param( 'unit_id' ) );

		global $wpdb;
		$table = $wpdb->prefix . 'rrc_reservations';

		$wpdb->update( $table, [
			'assigned_unit_id'   => $unit_id,
			'operational_status' => 'UNIT_ASSIGNED',
			'updated_at'         => current_time( 'mysql' )
		], [ 'id' => $id ] );

		self::log_audit( get_current_user_id(), 'ASSIGN_UNIT', 'reservation', $id, null, [ 'assigned_unit_id' => $unit_id, 'operational_status' => 'UNIT_ASSIGNED' ] );

		return rest_ensure_response( [ 'success' => true, 'message' => 'Vehículo/Unidad asignada exitosamente.' ] );
	}

	public static function get_fleet( WP_REST_Request $request ) {
		global $wpdb;
		$units = $wpdb->get_results(
			"SELECT u.*, m.public_name AS vehicle_name, l.name AS location_name
			 FROM {$wpdb->prefix}rrc_vehicle_units u
			 JOIN {$wpdb->prefix}rrc_vehicle_models m ON u.vehicle_model_id = m.id
			 LEFT JOIN {$wpdb->prefix}rrc_locations l ON u.location_id = l.id
			 ORDER BY u.unit_code ASC"
		);

		return rest_ensure_response( [ 'success' => true, 'units' => $units ] );
	}

	public static function get_fleet_availability( WP_REST_Request $request ) {
		global $wpdb;
		$locks = $wpdb->get_results(
			"SELECT l.*, u.unit_code, m.public_name AS vehicle_name
			 FROM {$wpdb->prefix}rrc_unit_day_locks l
			 JOIN {$wpdb->prefix}rrc_vehicle_units u ON l.vehicle_unit_id = u.id
			 JOIN {$wpdb->prefix}rrc_vehicle_models m ON u.vehicle_model_id = m.id
			 WHERE l.service_date >= CURDATE()
			 ORDER BY l.service_date ASC LIMIT 200"
		);

		return rest_ensure_response( [ 'success' => true, 'availability_locks' => $locks ] );
	}

	public static function get_audit_log( WP_REST_Request $request ) {
		global $wpdb;
		$logs = $wpdb->get_results(
			"SELECT a.*, u.display_name AS user_name
			 FROM {$wpdb->prefix}rrc_audit_log a
			 LEFT JOIN {$wpdb->users} u ON a.actor_user_id = u.ID
			 ORDER BY a.created_at DESC LIMIT 50"
		);

		return rest_ensure_response( [ 'success' => true, 'audit_logs' => $logs ] );
	}

	public static function get_app_users( WP_REST_Request $request ) {
		$users = get_users( [
			'orderby' => 'display_name',
			'order'   => 'ASC'
		] );

		$formatted = [];
		foreach ( $users as $u ) {
			$formatted[] = [
				'id'           => $u->ID,
				'username'     => $u->user_login,
				'display_name' => $u->display_name,
				'email'        => $u->user_email,
				'roles'        => $u->roles
			];
		}

		return rest_ensure_response( [ 'success' => true, 'users' => $formatted ] );
	}

	public static function create_app_user( WP_REST_Request $request ) {
		$params   = $request->get_params();
		$username = sanitize_text_field( $params['username'] ?? '' );
		$email    = sanitize_email( $params['email'] ?? '' );
		$password = $params['password'] ?? '';
		$role     = sanitize_text_field( $params['role'] ?? 'rrc_reservation_agent' );

		if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Por favor complete todos los campos requeridos.' ], 400 );
		}

		if ( username_exists( $username ) || email_exists( $email ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'El usuario o correo electrónico ya está registrado.' ], 400 );
		}

		$user_id = wp_create_user( $username, $password, $email );
		if ( is_wp_error( $user_id ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => $user_id->get_error_message() ], 500 );
		}

		$user = get_user_by( 'id', $user_id );
		$user->set_role( $role );

		self::log_audit( get_current_user_id(), 'CREATE_USER', 'user', $user_id, null, [ 'username' => $username, 'role' => $role ] );

		return rest_ensure_response( [
			'success' => true,
			'message' => 'Usuario creado exitosamente con el rol asignado.',
			'user_id' => $user_id
		] );
	}

	private static function log_audit( $user_id, $action, $entity_type, $entity_id, $old_vals = null, $new_vals = null ) {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix . 'rrc_audit_log', [
			'actor_user_id'  => $user_id,
			'actor_type'     => 'user',
			'action'         => $action,
			'entity_type'    => $entity_type,
			'entity_id'      => $entity_id,
			'old_values_json'=> $old_vals ? json_encode( $old_vals ) : null,
			'new_values_json'=> $new_vals ? json_encode( $new_vals ) : null,
			'ip_hash'        => hash( 'sha256', $_SERVER['REMOTE_ADDR'] ?? '' ),
			'correlation_id' => wp_generate_password( 16, false ),
			'created_at'     => current_time( 'mysql' )
		] );
	}
}
