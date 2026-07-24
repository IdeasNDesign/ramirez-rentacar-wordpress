<?php
namespace RamirezRentACar\Database;

class SeedManager {
	public static function run() {
		global $wpdb;

		// 1. Seed Locations
		$locations_table = $wpdb->prefix . 'rrc_locations';
		$locations = [
			[
				'name'          => 'Ramirez Rent A Car – Roatán Main Office',
				'slug'          => 'roatan-main-office',
				'location_type' => 'office',
				'address'       => 'Coxen Hole, Calle Principal al Aeropuerto, Roatán, Islas de la Bahía, Honduras',
				'city'          => 'Roatan',
				'department'    => 'Islas de la Bahia',
				'country'       => 'Honduras',
				'phone'         => '(504) 24-45-01-58',
				'email'         => 'info@ramirezrentacar.com',
				'is_active'     => 1,
				'is_historical' => 0,
				'free_pickup'   => 1,
				'free_dropoff'  => 1,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' )
			],
			[
				'name'          => 'French Harbor Office',
				'slug'          => 'french-harbor-office',
				'location_type' => 'office',
				'address'       => 'French Harbor, Roatán, Islas de la Bahía, Honduras',
				'city'          => 'Roatan',
				'department'    => 'Islas de la Bahia',
				'country'       => 'Honduras',
				'phone'         => '(504) 99-03-96-16',
				'email'         => 'info@ramirezrentacar.com',
				'is_active'     => 0,
				'is_historical' => 1,
				'free_pickup'   => 1,
				'free_dropoff'  => 1,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' )
			],
			[
				'name'          => 'Ramirez Rent A Car – San Pedro Sula Airport',
				'slug'          => 'san-pedro-sula-airport',
				'location_type' => 'airport',
				'address'       => 'Aeropuerto Internacional Ramón Villeda Morales, San Pedro Sula, Honduras',
				'city'          => 'San Pedro Sula',
				'department'    => 'Cortes',
				'country'       => 'Honduras',
				'phone'         => '(504) 24-45-01-58',
				'email'         => 'info@ramirezrentacar.com',
				'is_active'     => 1,
				'is_historical' => 0,
				'free_pickup'   => 0,
				'free_dropoff'  => 0,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' )
			],
			[
				'name'          => 'Aeropuerto de Roatan',
				'slug'          => 'aeropuerto-de-roatan',
				'location_type' => 'airport',
				'address'       => 'Aeropuerto Internacional Juan Manuel Gálvez, Roatán, Honduras',
				'city'          => 'Roatan',
				'department'    => 'Islas de la Bahia',
				'country'       => 'Honduras',
				'is_active'     => 1,
				'is_historical' => 0,
				'free_pickup'   => 1,
				'free_dropoff'  => 1,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' )
			],
			[
				'name'          => 'Terminal del Ferry',
				'slug'          => 'terminal-del-ferry',
				'location_type' => 'ferry_terminal',
				'address'       => 'Terminal de Ferry de Roatán, Dixon Cove, Roatán, Honduras',
				'city'          => 'Roatan',
				'department'    => 'Islas de la Bahia',
				'country'       => 'Honduras',
				'is_active'     => 1,
				'is_historical' => 0,
				'free_pickup'   => 1,
				'free_dropoff'  => 1,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' )
			],
			[
				'name'          => 'Muelles de Cruceros',
				'slug'          => 'muelles-de-cruceros',
				'location_type' => 'cruise_port',
				'address'       => 'Puerto de Cruceros de Roatán, Coxen Hole & Mahogany Bay, Roatán, Honduras',
				'city'          => 'Roatan',
				'department'    => 'Islas de la Bahia',
				'country'       => 'Honduras',
				'is_active'     => 1,
				'is_historical' => 0,
				'free_pickup'   => 1,
				'free_dropoff'  => 1,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' )
			]
		];

		foreach ( $locations as $loc ) {
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $locations_table WHERE slug = %s", $loc['slug'] ) );
			if ( ! $exists ) {
				$wpdb->insert( $locations_table, $loc );
			}
		}

		// 2. Seed Vehicle Models & Rates
		$models_table   = $wpdb->prefix . 'rrc_vehicle_models';
		$plans_table    = $wpdb->prefix . 'rrc_rate_plans';
		$packages_table = $wpdb->prefix . 'rrc_rate_packages';

		$legacy_catalog = [
			'sedan' => [
				'internal_code' => 'sedan-4d',
				'public_name'   => 'Four-Door Sedan',
				'category'      => 'Sedan',
				'rates'         => [
					'standard' => [
						// Daily
						[ 'unit' => 'day', 'value' => 1, 'amount' => 60.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 110.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 165.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 220.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 275.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 330.00 ],
						// Weekly
						[ 'unit' => 'week', 'value' => 1, 'amount' => 370.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 690.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 925.00 ],
						// Monthly
						[ 'unit' => 'month', 'value' => 1, 'amount' => 1050.00 ],
						[ 'unit' => 'month', 'value' => 2, 'amount' => 2100.00 ],
						[ 'unit' => 'month', 'value' => 3, 'amount' => 3150.00 ],
						[ 'unit' => 'month', 'value' => 4, 'amount' => 4200.00 ],
						[ 'unit' => 'month', 'value' => 5, 'amount' => 5250.00 ],
						[ 'unit' => 'month', 'value' => 6, 'amount' => 6300.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 55.00 ]
					]
				]
			],
			'atv' => [
				'internal_code' => 'atv-standard',
				'public_name'   => 'ATV\'s',
				'category'      => 'ATV',
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 90.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 180.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 270.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 360.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 440.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 530.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 600.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 1200.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 1800.00 ],
					],
					'guided_cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 90.00, 'guide' => 1 ]
					]
				]
			],
			'standard_suv' => [
				'internal_code' => 'suv-standard',
				'public_name'   => 'Standard SUV',
				'category'      => 'Standard SUV',
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 75.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 150.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 225.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 300.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 365.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 420.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 470.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 930.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 1300.00 ],
						[ 'unit' => 'month', 'value' => 1, 'amount' => 1450.00 ],
						[ 'unit' => 'month', 'value' => 2, 'amount' => 2900.00 ],
						[ 'unit' => 'month', 'value' => 3, 'amount' => 4350.00 ],
						[ 'unit' => 'month', 'value' => 4, 'amount' => 5800.00 ],
						[ 'unit' => 'month', 'value' => 5, 'amount' => 7250.00 ],
						[ 'unit' => 'month', 'value' => 6, 'amount' => 8700.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 70.00 ]
					]
				]
			],
			'kia_sorento' => [
				'internal_code' => 'suv-medium-sorento',
				'public_name'   => 'KIA Sorento',
				'category'      => 'Medium SUV',
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 85.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 170.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 255.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 340.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 425.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 500.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 550.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 1100.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 1650.00 ],
						[ 'unit' => 'month', 'value' => 1, 'amount' => 1700.00 ],
						[ 'unit' => 'month', 'value' => 2, 'amount' => 3400.00 ],
						[ 'unit' => 'month', 'value' => 3, 'amount' => 5100.00 ],
						[ 'unit' => 'month', 'value' => 4, 'amount' => 6800.00 ],
						[ 'unit' => 'month', 'value' => 5, 'amount' => 8500.00 ],
						[ 'unit' => 'month', 'value' => 6, 'amount' => 10200.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 80.00 ]
					]
				]
			],
			'luxury_suv' => [
				'internal_code' => 'suv-luxury',
				'public_name'   => 'Luxury SUV',
				'category'      => 'Luxury SUV',
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 130.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 250.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 360.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 490.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 620.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 750.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 800.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 1600.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 2400.00 ],
						[ 'unit' => 'month', 'value' => 1, 'amount' => 2800.00 ],
						[ 'unit' => 'month', 'value' => 2, 'amount' => 5600.00 ],
						[ 'unit' => 'month', 'value' => 3, 'amount' => 8400.00 ],
						[ 'unit' => 'month', 'value' => 4, 'amount' => 10200.00 ], // Alert triggered internally but saved
						[ 'unit' => 'month', 'value' => 5, 'amount' => 13000.00 ],
						[ 'unit' => 'month', 'value' => 6, 'amount' => 15800.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 125.00 ]
					]
				]
			],
			'toyota_prado' => [
				'internal_code' => 'suv-premium-prado',
				'public_name'   => 'Brand New 2025 Luxury Toyota Prado',
				'category'      => 'Premium Luxury SUV',
				'year'          => 2025,
				'make'          => 'Toyota',
				'model'         => 'Prado',
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 170.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 330.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 495.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 660.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 825.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 990.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 1050.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 2100.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 3150.00 ],
						[ 'unit' => 'month', 'value' => 1, 'amount' => 3500.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 170.00 ]
					]
				]
			],
			'jeep' => [
				'internal_code' => 'jeep-standard',
				'public_name'   => 'Jeep',
				'category'      => 'Standard Jeep',
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 100.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 200.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 300.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 400.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 400.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 480.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 540.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 650.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 1300.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 1950.00 ],
						[ 'unit' => 'month', 'value' => 1, 'amount' => 2300.00 ],
						[ 'unit' => 'month', 'value' => 2, 'amount' => 3600.00 ],
						[ 'unit' => 'month', 'value' => 3, 'amount' => 4800.00 ],
						[ 'unit' => 'month', 'value' => 4, 'amount' => 6400.00 ],
						[ 'unit' => 'month', 'value' => 5, 'amount' => 8000.00 ],
						[ 'unit' => 'month', 'value' => 6, 'amount' => 9600.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 95.00 ]
					]
				]
			],
			'gladiator' => [
				'internal_code' => 'jeep-gladiator',
				'public_name'   => 'Jeep Gladiator',
				'category'      => 'Jeep Gladiator',
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 120.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 240.00 ],
					]
				]
			],
			'truck_4x4' => [
				'internal_code' => 'truck-4x4',
				'public_name'   => '4x4 Double-Cab Pickup',
				'category'      => '4x4 Pickup Truck',
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 95.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 190.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 285.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 380.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 475.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 570.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 620.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 1240.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 1860.00 ],
						[ 'unit' => 'month', 'value' => 1, 'amount' => 2000.00 ],
						[ 'unit' => 'month', 'value' => 2, 'amount' => 4000.00 ],
						[ 'unit' => 'month', 'value' => 3, 'amount' => 6000.00 ],
						[ 'unit' => 'month', 'value' => 4, 'amount' => 8000.00 ],
						[ 'unit' => 'month', 'value' => 5, 'amount' => 10000.00 ],
						[ 'unit' => 'month', 'value' => 6, 'amount' => 12000.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 90.00 ]
					]
				]
			],
			'van_7p' => [
				'internal_code' => 'van-7p',
				'public_name'   => '7-Passenger Minivan',
				'category'      => '7-Passenger Minivan',
				'passenger_capacity' => 7,
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 120.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 240.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 360.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 480.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 600.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 720.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 780.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 1560.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 2340.00 ],
						[ 'unit' => 'month', 'value' => 1, 'amount' => 2800.00 ],
						[ 'unit' => 'month', 'value' => 2, 'amount' => 5600.00 ],
						[ 'unit' => 'month', 'value' => 3, 'amount' => 8400.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 115.00 ]
					]
				]
			],
			'van_15p' => [
				'internal_code' => 'van-15p',
				'public_name'   => '15 Pass Van',
				'category'      => '15 Passenger Van',
				'passenger_capacity' => 15,
				'rates'         => [
					'standard' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 130.00 ],
						[ 'unit' => 'day', 'value' => 2, 'amount' => 260.00 ],
						[ 'unit' => 'day', 'value' => 3, 'amount' => 390.00 ],
						[ 'unit' => 'day', 'value' => 4, 'amount' => 520.00 ],
						[ 'unit' => 'day', 'value' => 5, 'amount' => 650.00 ],
						[ 'unit' => 'day', 'value' => 6, 'amount' => 770.00 ],
						[ 'unit' => 'week', 'value' => 1, 'amount' => 800.00 ],
						[ 'unit' => 'week', 'value' => 2, 'amount' => 1600.00 ],
						[ 'unit' => 'week', 'value' => 3, 'amount' => 2400.00 ],
						[ 'unit' => 'month', 'value' => 1, 'amount' => 2500.00 ],
						[ 'unit' => 'month', 'value' => 2, 'amount' => 5000.00 ],
						[ 'unit' => 'month', 'value' => 3, 'amount' => 7500.00 ],
						[ 'unit' => 'month', 'value' => 4, 'amount' => 10000.00 ],
						[ 'unit' => 'month', 'value' => 5, 'amount' => 12500.00 ],
						[ 'unit' => 'month', 'value' => 6, 'amount' => 15000.00 ],
					],
					'cruise' => [
						[ 'unit' => 'day', 'value' => 1, 'amount' => 125.00 ]
					]
				]
			]
		];

		foreach ( $legacy_catalog as $key => $data ) {
			// Find or create CPT vehicle post
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'rrc_vehicle'", $data['internal_code'] ) );
			if ( ! $post_id ) {
				$post_id = wp_insert_post( [
					'post_title'   => $data['public_name'],
					'post_name'    => $data['internal_code'],
					'post_status'  => 'publish',
					'post_type'    => 'rrc_vehicle',
					'post_content' => 'Modelo de vehículo de Ramírez Rent A Car - ' . $data['public_name'],
				] );
			}

			$model_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $models_table WHERE internal_code = %s", $data['internal_code'] ) );
			if ( ! $model_id ) {
				$wpdb->insert( $models_table, [
					'post_id'            => $post_id,
					'internal_code'      => $data['internal_code'],
					'public_name'        => $data['public_name'],
					'category'           => $data['category'],
					'year'               => isset( $data['year'] ) ? $data['year'] : null,
					'make'               => isset( $data['make'] ) ? $data['make'] : null,
					'model'              => isset( $data['model'] ) ? $data['model'] : null,
					'passenger_capacity' => isset( $data['passenger_capacity'] ) ? $data['passenger_capacity'] : null,
					'status'             => 'publish',
					'created_at'         => current_time( 'mysql' ),
					'updated_at'         => current_time( 'mysql' )
				] );
				$model_id = $wpdb->insert_id;
			} else {
				// Ensure post_id is updated
				$wpdb->update( $models_table, [ 'post_id' => $post_id ], [ 'id' => $model_id ] );
			}

			// Generate rate plans & packages
			foreach ( $data['rates'] as $context => $packages ) {
				$plan_slug = $data['internal_code'] . '-' . $context;
				$plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $plans_table WHERE vehicle_model_id = %d AND booking_context = %s", $model_id, $context ) );
				if ( ! $plan_id ) {
					$wpdb->insert( $plans_table, [
						'vehicle_model_id' => $model_id,
						'name'             => $data['public_name'] . ' ' . ucfirst( $context ) . ' Rate Plan',
						'booking_context'  => $context,
						'currency'         => 'USD',
						'active'           => 1,
						'effective_from'   => current_time( 'mysql' ),
						'version'          => 1,
						'created_at'       => current_time( 'mysql' ),
						'updated_at'       => current_time( 'mysql' )
					] );
					$plan_id = $wpdb->insert_id;
				}

				foreach ( $packages as $pkg ) {
					$norm_days = $pkg['value'];
					if ( $pkg['unit'] === 'week' ) {
						$norm_days = $pkg['value'] * 7;
					} elseif ( $pkg['unit'] === 'month' ) {
						$norm_days = $pkg['value'] * 30;
					}

					// Check if package already exists
					$pkg_exists = $wpdb->get_var( $wpdb->prepare(
						"SELECT id FROM $packages_table WHERE rate_plan_id = %d AND duration_unit = %s AND duration_value = %d",
						$plan_id, $pkg['unit'], $pkg['value']
					) );

					if ( ! $pkg_exists ) {
						$wpdb->insert( $packages_table, [
							'rate_plan_id'   => $plan_id,
							'duration_unit'  => $pkg['unit'],
							'duration_value' => $pkg['value'],
							'normalized_days'=> $norm_days,
							'total_amount'   => $pkg['amount'],
							'stackable'      => 1,
							'guide_included' => isset( $pkg['guide'] ) ? 1 : 0,
							'created_at'     => current_time( 'mysql' ),
							'updated_at'     => current_time( 'mysql' )
						] );
					}
				}
			}
		}

		// 3. Seed Physical Vehicle Units if empty
		$units_table = $wpdb->prefix . 'rrc_vehicle_units';
		$units_count = $wpdb->get_var( "SELECT COUNT(*) FROM $units_table" );
		if ( intval( $units_count ) === 0 ) {
			$inserted_models = $wpdb->get_results( "SELECT id, internal_code FROM $models_table" );
			foreach ( $inserted_models as $m ) {
				for ( $i = 1; $i <= 5; $i++ ) {
					$wpdb->insert( $units_table, [
						'vehicle_model_id' => $m->id,
						'unit_code'        => strtoupper( $m->internal_code ) . '-' . str_pad( $i, 2, '0', STR_PAD_LEFT ),
						'license_plate'    => 'P-' . strtoupper( wp_generate_password( 6, false ) ),
						'color'            => 'Gris',
						'status'           => 'available',
						'service_status'   => 'available',
						'created_at'       => current_time( 'mysql' ),
						'updated_at'       => current_time( 'mysql' )
					] );
				}
			}
		}
	}
}
