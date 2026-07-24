<?php
namespace BreakTheMold\RamirezAIAssistant\Sales;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VehicleEligibilityService {
	public static function evaluate( $vehicle, array $requirements ): array {
		$failures = [];
		$warnings = [];

		$passengers = isset( $requirements['passengers'] ) ? intval( $requirements['passengers'] ) : 1;
		$large_luggage = isset( $requirements['large_luggage'] ) ? intval( $requirements['large_luggage'] ) : 0;
		$small_luggage = isset( $requirements['small_luggage'] ) ? intval( $requirements['small_luggage'] ) : 0;
		$needs_4x4 = ! empty( $requirements['needs_4x4'] );
		$transmission = $requirements['transmission'] ?? null;

		// 1. Capacity Verification Check
		$verified = ! empty( $vehicle->capacity_verified );
		if ( ! $verified ) {
			$failures[] = 'CAPACITY_UNVERIFIED';
		}

		// 2. Legal Passenger Capacity Check
		$legal_capacity = isset( $vehicle->passenger_capacity ) ? intval( $vehicle->passenger_capacity ) : 0;
		if ( $legal_capacity < $passengers ) {
			$failures[] = 'CAPACITY_INSUFFICIENT';
		}

		// 3. Luggage Capacity Penalties / Warnings
		$large_cap = isset( $vehicle->luggage_capacity_large ) ? intval( $vehicle->luggage_capacity_large ) : 0;
		$small_cap = isset( $vehicle->luggage_capacity_small ) ? intval( $vehicle->luggage_capacity_small ) : 0;
		if ( $large_cap < $large_luggage || $small_cap < $small_luggage ) {
			$warnings[] = 'LUGGAGE_SPACE_LIMITED';
		}

		// 4. Transmission Mismatch
		if ( $transmission && ! empty( $vehicle->transmission ) ) {
			if ( strtolower( $vehicle->transmission ) !== strtolower( $transmission ) ) {
				$failures[] = 'TRANSMISSION_MISMATCH';
			}
		}

		// 5. 4x4 Requirement Mismatch
		if ( $needs_4x4 && empty( $vehicle->drive_type ) ) {
			$failures[] = 'REQUIRED_FEATURE_MISSING';
		} elseif ( $needs_4x4 && stripos( $vehicle->drive_type, '4' ) === false && stripos( $vehicle->drive_type, 'awd' ) === false ) {
			$failures[] = 'REQUIRED_FEATURE_MISSING';
		}

		// Calculate scores
		$passenger_fit = ($legal_capacity > 0) ? min( 1.0, $legal_capacity / ($passengers ?: 1) ) : 0.0;
		$luggage_fit = 1.0;
		if ( ($large_luggage + $small_luggage) > 0 ) {
			$total_cap = $large_cap + $small_cap;
			$total_req = $large_luggage + $small_luggage;
			$luggage_fit = ($total_cap > 0) ? min( 1.0, $total_cap / $total_req ) : 0.0;
		}

		return [
			'eligible'      => empty( $failures ),
			'hard_failures' => $failures,
			'warnings'      => $warnings,
			'score'         => ($passenger_fit * 0.35) + ($luggage_fit * 0.25)
		];
	}
}
