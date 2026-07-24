<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Booking;

class BookingGatewayAdapter {
	private $res_table;
	private $lock_table;

	public function __construct() {
		global $wpdb;
		$this->res_table = $wpdb->prefix . 'rrc_reservations';
		$this->lock_table = $wpdb->prefix . 'rrc_unit_day_locks';
	}

	public function get_reservation_by_token( $token ) {
		global $wpdb;
		$token_hash = hash( 'sha256', $token );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->res_table} WHERE secure_lookup_token_hash = %s", $token_hash ) );
	}

	public function get_reservation( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->res_table} WHERE id = %d", $id ) );
	}

	public function update_reservation_status( $id, $res_status, $pay_status, array $extra_fields = [] ) {
		global $wpdb;
		$fields = array_merge( [
			'reservation_status' => $res_status,
			'payment_status'     => $pay_status,
			'updated_at'         => current_time( 'mysql' )
		], $extra_fields );

		return $wpdb->update( $this->res_table, $fields, [ 'id' => $id ] );
	}

	public function lock_vehicle_unit( $reservation_id, $unit_id, $pickup_at, $return_at ) {
		global $wpdb;
		
		$start_date = date( 'Y-m-d', strtotime( $pickup_at ) );
		$end_date   = date( 'Y-m-d', strtotime( $return_at ) );

		$current = new \DateTime( $start_date );
		$end     = new \DateTime( $end_date );

		while ( $current <= $end ) {
			$date_str = $current->format( 'Y-m-d' );
			$wpdb->replace( $this->lock_table, [
				'vehicle_unit_id' => $unit_id,
				'service_date'    => $date_str,
				'reservation_id'  => $reservation_id,
				'lock_type'       => 'reservation',
				'status'          => 'active',
				'created_at'      => current_time( 'mysql' )
			] );
			$current->modify( '+1 day' );
		}
	}
}
