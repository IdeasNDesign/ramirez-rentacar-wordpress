<?php
namespace RamirezRentACar\Domain\Availability;

use DateTime;
use DateTimeZone;

class AvailabilityService {
	public static function check_availability( $model_id, $pickup_at, $return_at, $buffer_hours = 2 ) {
		global $wpdb;
		$units_table = $wpdb->prefix . 'rrc_vehicle_units';
		$locks_table = $wpdb->prefix . 'rrc_unit_day_locks';

		// Get all physical active units for this model
		$units = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $units_table WHERE vehicle_model_id = %d AND status = 'available' AND deleted_at IS NULL",
			$model_id
		) );

		if ( empty( $units ) ) {
			return [];
		}

		$available_units = [];

		$tz = new DateTimeZone( 'America/Tegucigalpa' );
		$start_dt = new DateTime( $pickup_at, $tz );
		$end_dt = new DateTime( $return_at, $tz );

		// Adjust with buffers
		if ( $buffer_hours > 0 ) {
			$start_dt->modify( "-{$buffer_hours} hours" );
			$end_dt->modify( "+{$buffer_hours} hours" );
		}

		// Generate list of dates to check
		$interval = new \DateInterval( 'P1D' );
		$period = new \DatePeriod(
			new DateTime( $start_dt->format( 'Y-m-d' ) ),
			$interval,
			( new DateTime( $end_dt->format( 'Y-m-d' ) ) )->modify( '+1 day' )
		);

		$dates_to_check = [];
		foreach ( $period as $dt ) {
			$dates_to_check[] = $dt->format( 'Y-m-d' );
		}

		if ( empty( $dates_to_check ) ) {
			return $units;
		}

		foreach ( $units as $unit ) {
			// Check locks on these dates
			$placeholders = implode( ',', array_fill( 0, count( $dates_to_check ), '%s' ) );
			$query = $wpdb->prepare(
				"SELECT COUNT(*) FROM $locks_table WHERE vehicle_unit_id = %d AND service_date IN ($placeholders) AND status = 'active'",
				array_merge( [ $unit->id ], $dates_to_check )
			);

			$lock_count = $wpdb->get_var( $query );
			if ( intval( $lock_count ) === 0 ) {
				$available_units[] = $unit;
			}
		}

		return $available_units;
	}

	public static function acquire_hold( $model_id, $pickup_at, $return_at, $quote_id = null, $reservation_id = null ) {
		global $wpdb;
		$locks_table = $wpdb->prefix . 'rrc_unit_day_locks';

		// Find candidate units
		$available_units = self::check_availability( $model_id, $pickup_at, $return_at );
		if ( empty( $available_units ) ) {
			return false;
		}

		$tz = new DateTimeZone( 'America/Tegucigalpa' );
		$start_dt = new DateTime( $pickup_at, $tz );
		$end_dt = new DateTime( $return_at, $tz );

		$interval = new \DateInterval( 'P1D' );
		$period = new \DatePeriod(
			new DateTime( $start_dt->format( 'Y-m-d' ) ),
			$interval,
			( new DateTime( $end_dt->format( 'Y-m-d' ) ) )->modify( '+1 day' )
		);

		$dates_to_lock = [];
		foreach ( $period as $dt ) {
			$dates_to_lock[] = $dt->format( 'Y-m-d' );
		}

		// Try to lock a candidate unit
		foreach ( $available_units as $unit ) {
			$wpdb->query( 'START TRANSACTION' );
			$failed = false;

			foreach ( $dates_to_lock as $date ) {
				$inserted = $wpdb->insert( $locks_table, [
					'vehicle_unit_id' => $unit->id,
					'service_date'    => $date,
					'quote_id'        => $quote_id,
					'reservation_id'  => $reservation_id,
					'lock_type'       => 'booking_hold',
					'status'          => 'active',
					'expires_at'      => ( new DateTime( 'now', $tz ) )->modify( '+15 minutes' )->format( 'Y-m-d H:i:s' ),
					'created_at'      => current_time( 'mysql' )
				] );

				if ( $inserted === false ) {
					$failed = true;
					break;
				}
			}

			if ( $failed ) {
				$wpdb->query( 'ROLLBACK' );
			} else {
				$wpdb->query( 'COMMIT' );
				return $unit->id; // Successfully held this unit
			}
		}

		return false;
	}
}
