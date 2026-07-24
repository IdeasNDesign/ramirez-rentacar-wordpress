<?php
namespace RamirezRentACar\REST;

use WP_REST_Server;

class Routes {
	public static function register() {
		$namespace = 'ramirez-rent-a-car/v1';

		register_rest_route( $namespace, '/vehicles', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_vehicles' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/locations', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ __CLASS__, 'get_locations' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/availability/search', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'search_availability' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/quotes', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'create_quote' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/reservations', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'create_reservation' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/reservation-lookup', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'lookup_reservation' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/reservations/(?P<token>[a-zA-Z0-9]+)/paypal/order', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'create_paypal_order' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/reservations/(?P<token>[a-zA-Z0-9]+)/paypal/capture', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'capture_paypal_order' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/webhooks/paypal', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'handle_paypal_webhook' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $namespace, '/newsletter/subscribe', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ __CLASS__, 'newsletter_subscribe' ],
			'permission_callback' => '__return_true'
		] );
	}

	public static function get_vehicles( $request ) {
		global $wpdb;
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$results = $wpdb->get_results( "SELECT * FROM $models_table WHERE status = 'publish' AND deleted_at IS NULL" );
		return rest_ensure_response( $results );
	}

	public static function get_locations( $request ) {
		global $wpdb;
		$locations_table = $wpdb->prefix . 'rrc_locations';
		$results = $wpdb->get_results( "SELECT * FROM $locations_table WHERE is_active = 1" );
		return rest_ensure_response( $results );
	}

	public static function search_availability( $request ) {
		$params    = $request->get_params();
		$pickup    = isset( $params['pickup_at'] ) ? sanitize_text_field( $params['pickup_at'] ) : '';
		$return    = isset( $params['return_at'] ) ? sanitize_text_field( $params['return_at'] ) : '';
		$context   = isset( $params['booking_context'] ) ? sanitize_text_field( $params['booking_context'] ) : 'standard';

		global $wpdb;
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$models = $wpdb->get_results( "SELECT * FROM $models_table WHERE status = 'publish' AND deleted_at IS NULL" );

		$available_models = [];
		$days = \RamirezRentACar\Domain\Rates\PackageRateEngine::calculate_days( $pickup, $return );

		foreach ( $models as $model ) {
			$units = \RamirezRentACar\Domain\Availability\AvailabilityService::check_availability( $model->id, $pickup, $return );
			if ( ! empty( $units ) ) {
				$rate = \RamirezRentACar\Domain\Rates\PackageRateEngine::resolve_rate( $model->id, $context, $days );
				$model->available_units_count = count( $units );
				$model->calculated_days = $days;
				$model->rate = $rate;

				// Fetch image url and permalink
				$img_url = get_post_meta( $model->post_id, '_rrc_image_url', true );
				if ( empty( $img_url ) ) {
					$img_url = 'https://img.freepik.com/vectores-premium/icono-coche-gris-silueta-coche-ilustracion-vectorial_755519-158.jpg';
				}
				$model->image_url = $img_url;
				$model->permalink = get_permalink( $model->post_id );

				$available_models[] = $model;
			}
		}

		return rest_ensure_response( $available_models );
	}

	public static function create_quote( $request ) {
		$params    = $request->get_params();
		$model_id  = intval( $params['vehicle_model_id'] );
		$pickup    = sanitize_text_field( $params['pickup_at'] );
		$return    = sanitize_text_field( $params['return_at'] );
		$context   = sanitize_text_field( $params['booking_context'] );
		$p_loc     = intval( $params['pickup_location_id'] );
		$r_loc     = intval( $params['return_location_id'] );

		$days = \RamirezRentACar\Domain\Rates\PackageRateEngine::calculate_days( $pickup, $return );
		$rate = \RamirezRentACar\Domain\Rates\PackageRateEngine::resolve_rate( $model_id, $context, $days );

		if ( $rate['requires_manual_quote'] ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => isset( $rate['error'] ) ? $rate['error'] : 'Requires manual quote.'
			], 200 );
		}

		global $wpdb;
		$quotes_table = $wpdb->prefix . 'rrc_quotes';
		$token = wp_generate_password( 32, false );
		$token_hash = hash( 'sha256', $token );
		$quote_num  = 'Q-' . strtoupper( wp_generate_password( 8, false ) );

		$inserted = $wpdb->insert( $quotes_table, [
			'public_token_hash'  => $token_hash,
			'quote_number'       => $quote_num,
			'pickup_location_id' => $p_loc,
			'return_location_id' => $r_loc,
			'pickup_at'          => $pickup,
			'return_at'          => $return,
			'chargeable_days'    => $days,
			'booking_context'    => $context,
			'subtotal'           => $rate['total_amount'],
			'total_amount'       => $rate['total_amount'],
			'pricing_snapshot_json' => json_encode( $rate ),
			'expires_at'         => date( 'Y-m-d H:i:s', strtotime( '+24 hours' ) ),
			'status'             => 'active',
			'created_at'         => current_time( 'mysql' ),
			'updated_at'         => current_time( 'mysql' )
		] );

		if ( $inserted ) {
			return rest_ensure_response( [
				'success'      => true,
				'quote_number' => $quote_num,
				'token'        => $token,
				'total_amount' => $rate['total_amount']
			] );
		}

		return new \WP_REST_Response( [ 'success' => false, 'message' => 'Failed to create quote.' ], 500 );
	}

	public static function create_reservation( $request ) {
		$params    = $request->get_params();
		$model_id  = intval( $params['vehicle_model_id'] );
		$pickup    = sanitize_text_field( $params['pickup_at'] );
		$return    = sanitize_text_field( $params['return_at'] );
		$context   = sanitize_text_field( $params['booking_context'] );
		$p_loc     = intval( $params['pickup_location_id'] );
		$r_loc     = intval( $params['return_location_id'] );
		$cust_name = sanitize_text_field( $params['first_name'] );
		$cust_last = sanitize_text_field( $params['last_name'] );
		$cust_email= sanitize_email( $params['email'] );
		$cust_phone= sanitize_text_field( $params['phone'] );
		$country   = sanitize_text_field( $params['country'] );

		// 1. Resolve rate
		$days = \RamirezRentACar\Domain\Rates\PackageRateEngine::calculate_days( $pickup, $return );
		$rate = \RamirezRentACar\Domain\Rates\PackageRateEngine::resolve_rate( $model_id, $context, $days );

		if ( $rate['requires_manual_quote'] ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Manual Quote required' ], 400 );
		}

		// 2. Try lock/hold unit
		$held_unit_id = \RamirezRentACar\Domain\Availability\AvailabilityService::acquire_hold( $model_id, $pickup, $return );
		if ( ! $held_unit_id ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'No units available for selected model and dates' ], 400 );
		}

		global $wpdb;

		// 3. Create customer
		$cust_table = $wpdb->prefix . 'rrc_customers';
		$cust_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $cust_table WHERE email = %s", $cust_email ) );
		if ( ! $cust_id ) {
			$wpdb->insert( $cust_table, [
				'first_name' => $cust_name,
				'last_name'  => $cust_last,
				'email'      => $cust_email,
				'phone'      => $cust_phone,
				'country'    => $country,
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' )
			] );
			$cust_id = $wpdb->insert_id;
		}

		// 4. Calculate 10% deposit and remaining balance
		$total_amount      = (float) $rate['total_amount'];
		$deposit_percentage= 10.00;
		$deposit_amount    = round( $total_amount * 0.10, 2 );
		$remaining_balance = round( $total_amount - $deposit_amount, 2 );

		// 5. Create reservation
		$res_table = $wpdb->prefix . 'rrc_reservations';
		$ref = 'RRC-' . strtoupper( wp_generate_password( 8, false ) );
		$lookup_token = wp_generate_password( 32, false );
		$lookup_token_hash = hash( 'sha256', $lookup_token );

		$wpdb->insert( $res_table, [
			'public_reference'         => $ref,
			'secure_lookup_token_hash' => $lookup_token_hash,
			'customer_id'              => $cust_id,
			'vehicle_model_id'         => $model_id,
			'assigned_unit_id'         => $held_unit_id,
			'pickup_location_id'       => $p_loc,
			'return_location_id'       => $r_loc,
			'pickup_at'                => $pickup,
			'return_at'                => $return,
			'chargeable_days'          => $days,
			'booking_context'          => $context,
			'reservation_status'       => 'PENDING_PAYMENT',
			'payment_status'           => 'NOT_CREATED',
			'deposit_type'             => 'percentage',
			'deposit_percentage'       => $deposit_percentage,
			'deposit_amount'           => $deposit_amount,
			'deposit_paid_amount'      => 0.00,
			'remaining_balance'        => $remaining_balance,
			'remaining_balance_status' => 'pending_at_pickup',
			'subtotal'                 => $total_amount,
			'total_amount'             => $total_amount,
			'pricing_snapshot_json'    => json_encode( $rate ),
			'created_at'               => current_time( 'mysql' ),
			'updated_at'               => current_time( 'mysql' )
		] );

		$res_id = $wpdb->insert_id;

		// Link locks to the reservation
		$locks_table = $wpdb->prefix . 'rrc_unit_day_locks';
		$wpdb->update(
			$locks_table,
			[ 'reservation_id' => $res_id ],
			[ 'vehicle_unit_id' => $held_unit_id, 'reservation_id' => null, 'lock_type' => 'booking_hold' ]
		);

		return rest_ensure_response( [
			'success'           => true,
			'reservation_id'    => $res_id,
			'public_reference'  => $ref,
			'lookup_token'      => $lookup_token,
			'total_amount'      => $total_amount,
			'deposit_amount'    => $deposit_amount,
			'remaining_balance' => $remaining_balance,
			'currency'          => 'USD'
		] );
	}

	public static function lookup_reservation( $request ) {
		$params = $request->get_params();
		$ref    = sanitize_text_field( $params['reference'] );
		$email  = sanitize_email( $params['email'] );

		global $wpdb;
		$res_table  = $wpdb->prefix . 'rrc_reservations';
		$cust_table = $wpdb->prefix . 'rrc_customers';

		$res = $wpdb->get_row( $wpdb->prepare(
			"SELECT r.* FROM $res_table r 
			 JOIN $cust_table c ON r.customer_id = c.id 
			 WHERE r.public_reference = %s AND c.email = %s",
			$ref, $email
		) );

		if ( ! $res ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Reservation not found' ], 404 );
		}

		return rest_ensure_response( [
			'success'            => true,
			'reference'          => $res->public_reference,
			'reservation_status' => $res->reservation_status,
			'payment_status'     => $res->payment_status,
			'total_amount'       => $res->total_amount,
			'pickup_at'          => $res->pickup_at,
			'return_at'          => $res->return_at
		] );
	}

	public static function create_paypal_order( $request ) {
		$token = sanitize_text_field( $request->get_param( 'token' ) );
		global $wpdb;
		$res_table = $wpdb->prefix . 'rrc_reservations';
		$token_hash = hash( 'sha256', $token );

		$res = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $res_table WHERE secure_lookup_token_hash = %s", $token_hash ) );

		if ( ! $res ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Reserva no encontrada.' ], 404 );
		}

		// Recalculate 10% deposit dynamically on server
		$total_amount   = (float) $res->total_amount;
		$deposit_amount = round( $total_amount * 0.10, 2 );

		$paypal_gateway = new \RamirezRentACar\Infrastructure\Payments\PayPalGateway();
		$order = $paypal_gateway->create_order( $res->id, $res->public_reference, $deposit_amount, $res->currency );

		if ( is_wp_error( $order ) || empty( $order['id'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => is_wp_error( $order ) ? $order->get_error_message() : 'Error creando orden PayPal.'
			], 500 );
		}

		// Record payment attempt
		$payments_table = $wpdb->prefix . 'rrc_payments';
		$wpdb->insert( $payments_table, [
			'reservation_id'       => $res->id,
			'provider'             => 'paypal',
			'environment'          => get_option( 'rrc_paypal_mode', 'sandbox' ),
			'provider_order_id'    => $order['id'],
			'payment_purpose'      => 'security_deposit',
			'currency'             => $res->currency,
			'amount'               => $deposit_amount,
			'expected_amount'      => $deposit_amount,
			'status'               => 'ORDER_CREATED',
			'request_snapshot_json'=> json_encode( [ 'deposit_amount' => $deposit_amount ] ),
			'created_at'           => current_time( 'mysql' ),
			'updated_at'           => current_time( 'mysql' )
		] );

		$wpdb->update( $res_table, [
			'payment_status' => 'ORDER_CREATED',
			'updated_at'     => current_time( 'mysql' )
		], [ 'id' => $res->id ] );

		return rest_ensure_response( [
			'success'           => true,
			'order_id'          => $order['id'],
			'currency'          => $res->currency,
			'deposit_amount'    => number_format( $deposit_amount, 2, '.', '' ),
			'total_amount'      => number_format( $total_amount, 2, '.', '' ),
			'remaining_balance' => number_format( $res->remaining_balance, 2, '.', '' )
		] );
	}

	public static function capture_paypal_order( $request ) {
		$token = sanitize_text_field( $request->get_param( 'token' ) );
		$params = $request->get_params();
		$order_id = sanitize_text_field( $params['order_id'] ?? '' );

		global $wpdb;
		$res_table = $wpdb->prefix . 'rrc_reservations';
		$token_hash = hash( 'sha256', $token );

		$res = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $res_table WHERE secure_lookup_token_hash = %s", $token_hash ) );
		if ( ! $res ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Reserva no encontrada.' ], 404 );
		}

		$paypal_gateway = new \RamirezRentACar\Infrastructure\Payments\PayPalGateway();
		$capture = $paypal_gateway->capture_order( $order_id );

		if ( is_wp_error( $capture ) ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => $capture->get_error_message() ], 500 );
		}

		$status = $capture['status'] ?? 'UNKNOWN';

		if ( $status === 'COMPLETED' ) {
			self::process_completed_deposit( $res->id, $order_id, $capture );

			return rest_ensure_response( [
				'success'           => true,
				'message'           => 'Depósito del 10% capturado exitosamente. Reserva confirmada.',
				'reservation_status'=> 'DEPOSIT_PAID',
				'deposit_paid'      => $res->deposit_amount,
				'remaining_balance' => $res->remaining_balance
			] );
		}

		return new \WP_REST_Response( [
			'success' => false,
			'message' => 'La captura del pago no fue completada. Estado: ' . $status
		], 400 );
	}

	public static function handle_paypal_webhook( $request ) {
		$body = $request->get_body();
		$headers = $request->get_headers();

		$payload = json_decode( $body, true );
		if ( empty( $payload ) || empty( $payload['event_type'] ) ) {
			return new \WP_REST_Response( [ 'message' => 'Payload inválido' ], 400 );
		}

		$event_id   = sanitize_text_field( $payload['id'] ?? '' );
		$event_type = sanitize_text_field( $payload['event_type'] );

		global $wpdb;
		$webhooks_table = $wpdb->prefix . 'rrc_webhook_events';

		// Deduplication
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $webhooks_table WHERE external_event_id = %s", $event_id ) );
		if ( $exists ) {
			return rest_ensure_response( [ 'success' => true, 'message' => 'Evento ya procesado' ] );
		}

		// Record Webhook
		$wpdb->insert( $webhooks_table, [
			'provider'          => 'paypal',
			'external_event_id' => $event_id,
			'event_type'        => $event_type,
			'signature_verified'=> 1,
			'payload_hash'      => hash( 'sha256', $body ),
			'payload_json'      => $body,
			'processing_status' => 'pending',
			'created_at'        => current_time( 'mysql' )
		] );
		$webhook_record_id = $wpdb->insert_id;

		if ( $event_type === 'PAYMENT.CAPTURE.COMPLETED' ) {
			$custom_id = $payload['resource']['custom_id'] ?? null;
			$order_id  = $payload['resource']['supplementary_data']['related_ids']['order_id'] ?? '';
			if ( $custom_id ) {
				self::process_completed_deposit( intval( $custom_id ), $order_id, $payload['resource'] );
			}
		}

		$wpdb->update( $webhooks_table, [ 'processing_status' => 'processed', 'processed_at' => current_time( 'mysql' ) ], [ 'id' => $webhook_record_id ] );

		return rest_ensure_response( [ 'success' => true ] );
	}

	private static function process_completed_deposit( $reservation_id, $order_id, $data ) {
		global $wpdb;
		$res_table = $wpdb->prefix . 'rrc_reservations';
		$res = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $res_table WHERE id = %d", $reservation_id ) );

		if ( ! $res || $res->reservation_status === 'DEPOSIT_PAID' || $res->reservation_status === 'CONFIRMED' ) {
			return; // Already processed
		}

		$wpdb->update( $res_table, [
			'reservation_status'  => 'DEPOSIT_PAID',
			'payment_status'      => 'COMPLETED',
			'deposit_paid_amount' => $res->deposit_amount,
			'amount_paid'         => $res->deposit_amount,
			'confirmed_at'        => current_time( 'mysql' ),
			'updated_at'          => current_time( 'mysql' )
		], [ 'id' => $reservation_id ] );

		// Update payments record
		$payments_table = $wpdb->prefix . 'rrc_payments';
		$wpdb->update( $payments_table, [
			'status'              => 'COMPLETED',
			'provider_order_id'   => $order_id,
			'provider_capture_id' => $data['id'] ?? '',
			'paid_at'             => current_time( 'mysql' )
		], [ 'reservation_id' => $reservation_id ] );

		// Send email notification to customer with 10% deposit and remaining 90% balance breakdown
		\RamirezRentACar\Infrastructure\Notifications\EmailNotificationService::send_reservation_confirmation( $reservation_id );
	}
}
