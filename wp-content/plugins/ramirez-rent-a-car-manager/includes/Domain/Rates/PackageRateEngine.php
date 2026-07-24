<?php
namespace RamirezRentACar\Domain\Rates;

use DateTime;
use DateTimeZone;

class PackageRateEngine {
	public static function calculate_days( $pickup_at, $return_at, $settings = [] ) {
		$tz = new DateTimeZone( 'America/Tegucigalpa' );
		$start = new DateTime( $pickup_at, $tz );
		$end = new DateTime( $return_at, $tz );

		$diff = $start->diff( $end );
		$days = $diff->days;
		$hours = $diff->h;
		$minutes = $diff->i;

		$grace_minutes = isset( $settings['grace_minutes'] ) ? intval( $settings['grace_minutes'] ) : 59;
		$total_extra_minutes = ( $hours * 60 ) + $minutes;

		if ( $total_extra_minutes > $grace_minutes ) {
			$days += 1;
		}

		$min_days = isset( $settings['minimum_chargeable_days'] ) ? intval( $settings['minimum_chargeable_days'] ) : 1;
		if ( $days < $min_days ) {
			$days = $min_days;
		}

		return $days;
	}

	public static function resolve_rate( $model_id, $booking_context, $days ) {
		global $wpdb;
		$plans_table = $wpdb->prefix . 'rrc_rate_plans';
		$packages_table = $wpdb->prefix . 'rrc_rate_packages';

		// Get active rate plan for this model and context
		$plan = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $plans_table WHERE vehicle_model_id = %d AND booking_context = %s AND active = 1 ORDER BY version DESC LIMIT 1",
			$model_id, $booking_context
		) );

		if ( ! $plan ) {
			return [ 'requires_manual_quote' => true, 'error' => 'No active rate plan found.' ];
		}

		// Retrieve all packages for this plan
		$packages = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $packages_table WHERE rate_plan_id = %d ORDER BY normalized_days DESC, total_amount ASC",
			$plan->id
		) );

		if ( empty( $packages ) ) {
			return [ 'requires_manual_quote' => true, 'error' => 'No rate packages defined.' ];
		}

		// 1. Check exact match
		foreach ( $packages as $pkg ) {
			if ( $pkg->normalized_days === $days ) {
				return [
					'requires_manual_quote' => false,
					'total_amount'          => floatval( $pkg->total_amount ),
					'breakdown'             => [
						[
							'label'          => $pkg->label ?: "{$pkg->duration_value} " . ( $pkg->duration_unit === 'day' ? 'day(s)' : ( $pkg->duration_unit === 'week' ? 'week(s)' : 'month(s)' ) ),
							'quantity'       => 1,
							'package_amount' => floatval( $pkg->total_amount ),
							'total'          => floatval( $pkg->total_amount ),
							'days_covered'   => $pkg->normalized_days
						]
					],
					'rate_plan_id'          => $plan->id
				];
			}
		}

		// 2. Dynamic Programming logic to find the cheapest combination of stackable packages
		// DP array holds the min cost to cover `i` days
		// and track decisions
		$dp = array_fill( 0, $days + 1, INF );
		$parent = array_fill( 0, $days + 1, null );
		$dp[0] = 0.0;

		for ( $i = 1; $i <= $days; $i++ ) {
			foreach ( $packages as $pkg ) {
				if ( ! $pkg->stackable ) {
					continue;
				}
				$nd = intval( $pkg->normalized_days );
				if ( $i >= $nd ) {
					$prev_cost = $dp[ $i - $nd ];
					if ( $prev_cost !== INF ) {
						$cost = $prev_cost + floatval( $pkg->total_amount );
						if ( $cost < $dp[$i] ) {
							$dp[$i] = $cost;
							$parent[$i] = [
								'pkg' => $pkg,
								'prev'=> $i - $nd
							];
						}
					}
				}
			}
		}

		// If no combination covers the exact days, try to cover the days by taking a larger package or manual quote
		if ( $dp[$days] === INF ) {
			return [
				'requires_manual_quote' => true,
				'error'                 => 'No authorized package combination covers this period. Please request a manual quote.'
			];
		}

		// Rebuild path
		$breakdown = [];
		$curr = $days;
		$package_counts = [];

		while ( $curr > 0 ) {
			$step = $parent[$curr];
			if ( ! $step ) {
				break;
			}
			$pkg_id = $step['pkg']->id;
			if ( ! isset( $package_counts[$pkg_id] ) ) {
				$package_counts[$pkg_id] = [
					'pkg'   => $step['pkg'],
					'count' => 0
				];
			}
			$package_counts[$pkg_id]['count']++;
			$curr = $step['prev'];
		}

		$total_resolved = 0.0;
		foreach ( $package_counts as $id => $item ) {
			$pkg = $item['pkg'];
			$qty = $item['count'];
			$pkg_total = floatval( $pkg->total_amount ) * $qty;
			$total_resolved += $pkg_total;

			$breakdown[] = [
				'label'          => $pkg->label ?: "{$pkg->duration_value} " . ( $pkg->duration_unit === 'day' ? 'day(s)' : ( $pkg->duration_unit === 'week' ? 'week(s)' : 'month(s)' ) ),
				'quantity'       => $qty,
				'package_amount' => floatval( $pkg->total_amount ),
				'total'          => $pkg_total,
				'days_covered'   => $pkg->normalized_days * $qty
			];
		}

		return [
			'requires_manual_quote' => false,
			'total_amount'          => $total_resolved,
			'breakdown'             => $breakdown,
			'rate_plan_id'          => $plan->id
		];
	}
}
