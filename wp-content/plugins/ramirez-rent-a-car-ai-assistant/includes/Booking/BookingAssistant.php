<?php
namespace BreakTheMold\RamirezAIAssistant\Booking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BookingAssistant {
	public static function search_available_vehicles( $pickup_at, $return_at, $context = 'standard' ) {
		global $wpdb;
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$models = $wpdb->get_results( "SELECT * FROM $models_table WHERE status = 'publish' AND deleted_at IS NULL" );

		$available_models = [];
		$days = 1;
		if ( class_exists( 'RamirezRentACar\Domain\Rates\PackageRateEngine' ) ) {
			$days = \RamirezRentACar\Domain\Rates\PackageRateEngine::calculate_days( $pickup_at, $return_at );
		}

		foreach ( $models as $model ) {
			if ( class_exists( 'RamirezRentACar\Domain\Availability\AvailabilityService' ) ) {
				$units = \RamirezRentACar\Domain\Availability\AvailabilityService::check_availability( $model->id, $pickup_at, $return_at );
				if ( ! empty( $units ) ) {
					$rate = [];
					if ( class_exists( 'RamirezRentACar\Domain\Rates\PackageRateEngine' ) ) {
						$rate = \RamirezRentACar\Domain\Rates\PackageRateEngine::resolve_rate( $model->id, $context, $days );
					}
					
					$img_url = get_post_meta( $model->post_id, '_rrc_image_url', true );
					if ( empty( $img_url ) ) {
						$img_url = 'https://img.freepik.com/vectores-premium/icono-coche-gris-silueta-coche-ilustracion-vectorial_755519-158.jpg';
					}

					$available_models[] = [
						'id'               => $model->id,
						'name'             => $model->public_name,
						'category'         => $model->category,
						'passengers'       => $model->passenger_capacity,
						'passenger_capacity' => $model->passenger_capacity,
						'luggage'          => $model->luggage_capacity,
						'transmission'     => $model->transmission,
						'drive_type'       => $model->drive_type,
						'image_url'        => $img_url,
						'rate'             => $rate,
						'days'             => $days,
						'available_count'  => count( $units ),
						'capacity_verified'=> $model->capacity_verified ?? 0,
						'luggage_capacity_large' => $model->luggage_capacity_large ?? 0,
						'luggage_capacity_small' => $model->luggage_capacity_small ?? 0
					];
				}
			}
		}

		return $available_models;
	}

	public static function search_eligible_vehicles( array $requirements ): array {
		$pickup_at = $requirements['pickup_at'] ?? current_time('mysql');
		$return_at = $requirements['return_at'] ?? date('Y-m-d H:i:s', strtotime('+3 days'));
		$context = $requirements['arrival_type'] === 'cruise' ? 'cruise' : 'standard';

		$all_available = self::search_available_vehicles( $pickup_at, $return_at, $context );
		$eligible = [];
		
		$excluded_summary = [
			'capacity_insufficient' => 0,
			'capacity_unverified'   => 0,
			'not_available'         => 0
		];

		foreach ( $all_available as $v ) {
			// Cast model to object
			$v_obj = (object)$v;
			$evaluation = \BreakTheMold\RamirezAIAssistant\Sales\VehicleEligibilityService::evaluate( $v_obj, $requirements );
			
			if ( $evaluation['eligible'] ) {
				$v['fit_score'] = $evaluation['score'];
				$v['warnings'] = $evaluation['warnings'];
				$eligible[] = $v;
			} else {
				if ( in_array( 'CAPACITY_UNVERIFIED', $evaluation['hard_failures'] ) ) {
					$excluded_summary['capacity_unverified']++;
				}
				if ( in_array( 'CAPACITY_INSUFFICIENT', $evaluation['hard_failures'] ) ) {
					$excluded_summary['capacity_insufficient']++;
				}
			}
		}

		// Sort by score descending
		usort( $eligible, function( $a, $b ) {
			return $b['fit_score'] <=> $a['fit_score'];
		});

		return [
			'eligible_vehicles' => $eligible,
			'excluded_summary'  => $excluded_summary,
			'requires_manual_quote' => empty( $eligible ) && ($requirements['passengers'] > 15)
		];
	}

	public static function create_temporary_hold( $model_id, $pickup_at, $return_at, $quote_id ) {
		if ( class_exists( 'RamirezRentACar\Domain\Availability\AvailabilityService' ) ) {
			return \RamirezRentACar\Domain\Availability\AvailabilityService::acquire_hold( $model_id, $pickup_at, $return_at, $quote_id );
		}
		return false;
	}
}
