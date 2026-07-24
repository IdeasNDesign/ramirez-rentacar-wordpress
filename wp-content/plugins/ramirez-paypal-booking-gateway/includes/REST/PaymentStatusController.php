<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\REST;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;
use WP_REST_Response;

class PaymentStatusController {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function execute( $request ) {
		$token = sanitize_text_field( $request->get_param( 'token' ) );
		
		$booking_adapter = $this->container->get( 'booking_adapter' );
		$res = $booking_adapter->get_reservation_by_token( $token );

		if ( ! $res ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'Reserva no encontrada.' ], 404 );
		}

		return new WP_REST_Response( [
			'success'            => true,
			'reservation_status' => $res->reservation_status,
			'payment_status'     => $res->payment_status,
			'total_amount'       => number_format( (float) $res->total_amount, 2, '.', '' ),
			'deposit_paid'       => number_format( (float) $res->deposit_paid_amount, 2, '.', '' ),
			'remaining_balance'  => number_format( (float) $res->remaining_balance, 2, '.', '' )
		] );
	}
}
