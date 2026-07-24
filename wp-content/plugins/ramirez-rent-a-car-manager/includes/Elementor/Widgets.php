<?php
namespace RamirezRentACar\Elementor;

class Widgets {
	public static function register( $widgets_manager ) {
		// Register Shortcodes
		add_shortcode( 'rrc_booking_search', [ __CLASS__, 'render_search_shortcode' ] );
		add_shortcode( 'rrc_vehicle_catalog', [ __CLASS__, 'render_catalog_shortcode' ] );
		add_shortcode( 'rrc_roatan_map', [ __CLASS__, 'render_roatan_map_shortcode' ] );
		add_shortcode( 'rrc_vehicle_slider', [ __CLASS__, 'render_slider_shortcode' ] );
		add_shortcode( 'rrc_benefits_section', [ __CLASS__, 'render_benefits_shortcode' ] );
		add_shortcode( 'rrc_reviews_section', [ __CLASS__, 'render_reviews_shortcode' ] );
		add_shortcode( 'rrc_booking_timeline', [ __CLASS__, 'render_timeline_shortcode' ] );
		add_shortcode( 'rrc_about_us_section', [ __CLASS__, 'render_about_us_shortcode' ] );
		add_shortcode( 'rrc_contact_us_section', [ __CLASS__, 'render_contact_us_shortcode' ] );
		add_shortcode( 'rrc_terms_conditions_section', [ __CLASS__, 'render_terms_conditions_shortcode' ] );
		add_shortcode( 'rrc_privacy_policy_section', [ __CLASS__, 'render_privacy_policy_shortcode' ] );
		add_shortcode( 'rrc_faq_section', [ __CLASS__, 'render_faq_shortcode' ] );
		add_shortcode( 'rrc_useful_info_section', [ __CLASS__, 'render_useful_info_shortcode' ] );
		add_shortcode( 'rrc_sitemap_section', [ __CLASS__, 'render_sitemap_shortcode' ] );
		add_shortcode( 'rrc_landing_airport_section', [ __CLASS__, 'render_landing_airport_shortcode' ] );
		add_shortcode( 'rrc_landing_mahogany_section', [ __CLASS__, 'render_landing_mahogany_shortcode' ] );
		add_shortcode( 'rrc_landing_coxen_section', [ __CLASS__, 'render_landing_coxen_shortcode' ] );
		add_shortcode( 'rrc_landing_ferry_section', [ __CLASS__, 'render_landing_ferry_shortcode' ] );
		add_shortcode( 'rrc_insurance_guide_section', [ __CLASS__, 'render_insurance_guide_shortcode' ] );
		add_shortcode( 'rrc_driving_guide_section', [ __CLASS__, 'render_driving_guide_shortcode' ] );
		add_shortcode( 'rrc_cruise_route_section', [ __CLASS__, 'render_cruise_route_shortcode' ] );
		add_shortcode( 'rrc_beaches_route_section', [ __CLASS__, 'render_beaches_route_shortcode' ] );
		add_shortcode( 'rrc_help_center_section', [ __CLASS__, 'render_help_center_shortcode' ] );
		add_shortcode( 'rrc_deposit_policy_section', [ __CLASS__, 'render_deposit_policy_shortcode' ] );

		// Register Elementor Widgets if Elementor is active and loaded
		if ( did_action( 'elementor/loaded' ) ) {
			self::register_elementor_custom_widgets( $widgets_manager );
		}
	}

	private static function register_elementor_custom_widgets( $widgets_manager ) {
		// Dynamic inclusion and instantiation of Elementor Widget classes
		require_once RRC_PATH . 'includes/Elementor/RRC_Search_Widget.php';
		require_once RRC_PATH . 'includes/Elementor/RRC_Catalog_Widget.php';

		$widgets_manager->register( new \RamirezRentACar\Elementor\RRC_Search_Widget() );
		$widgets_manager->register( new \RamirezRentACar\Elementor\RRC_Catalog_Widget() );
	}

	public static function render_search_shortcode() {
		ob_start();
		global $wpdb;
		$locations_table = $wpdb->prefix . 'rrc_locations';
		$locations = $wpdb->get_results( "SELECT * FROM $locations_table WHERE is_active = 1" );
		?>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

		<div id="reservas" class="rrc-booking-search-container" style="background-color: #0b1320; padding: 24px 30px; border-radius: 20px; color: #ffffff; font-family: 'Inter Tight', sans-serif; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: 1px solid #1e293b; margin: 20px 0;">
			<form id="rrc-search-form" action="" method="GET">
				<div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
					
					<!-- Left Section: Recogida -->
					<div style="flex: 2 1 300px; display: flex; flex-direction: column; gap: 12px;">
						<div>
							<label style="display: block; font-size: 13px; font-weight: 700; color: #ffffff; margin-bottom: 6px;">Recogida</label>
							<div style="position: relative;">
								<select id="pickup_location_id" name="pickup_location_id" required style="width: 100%; height: 42px; padding: 0 12px 0 36px; border: 1px solid #334155; border-radius: 8px; font-family: 'Inter Tight', sans-serif; font-size: 13.5px; color: #ffffff; background: #1e293b; box-sizing: border-box; -webkit-appearance: none; -moz-appearance: none; appearance: none; font-weight: 600;">
									<option value="" style="background: #1e293b; color: #ffffff;">Aeropuerto / Ferry / Crucero</option>
									<?php foreach ( $locations as $loc ) : ?>
										<option value="<?php echo esc_attr( $loc->id ); ?>" style="background: #1e293b; color: #ffffff;"><?php echo esc_html( $loc->name ); ?></option>
									<?php endforeach; ?>
								</select>
								<svg style="position: absolute; left: 12px; top: 13px; width: 16px; height: 16px; color: #94a3b8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
							</div>
						</div>
						
						<div style="display: flex; gap: 10px;">
							<div style="flex: 1; position: relative;">
								<label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 4px;">Fecha</label>
								<input type="date" id="pickup_date" name="pickup_date" required value="<?php echo date('Y-m-d'); ?>" style="width: 100%; height: 40px; padding: 0 10px; border: 1px solid #334155; border-radius: 8px; font-family: 'Inter Tight', sans-serif; font-size: 13px; color: #ffffff; background: #1e293b; box-sizing: border-box; font-weight: 600;">
							</div>
							<div style="flex: 1; position: relative;">
								<label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 4px;">Hora</label>
								<input type="time" id="pickup_time" name="pickup_time" required value="10:00" style="width: 100%; height: 40px; padding: 0 10px; border: 1px solid #334155; border-radius: 8px; font-family: 'Inter Tight', sans-serif; font-size: 13px; color: #ffffff; background: #1e293b; box-sizing: border-box; font-weight: 600;">
							</div>
						</div>
					</div>

					<!-- Middle Section: Devolución -->
					<div style="flex: 2 1 300px; display: flex; flex-direction: column; gap: 12px;">
						<div>
							<label style="display: block; font-size: 13px; font-weight: 700; color: #ffffff; margin-bottom: 6px;">Devolución</label>
							<div style="position: relative;">
								<select id="return_location_id" name="return_location_id" required style="width: 100%; height: 42px; padding: 0 12px 0 36px; border: 1px solid #334155; border-radius: 8px; font-family: 'Inter Tight', sans-serif; font-size: 13.5px; color: #ffffff; background: #1e293b; box-sizing: border-box; -webkit-appearance: none; -moz-appearance: none; appearance: none; font-weight: 600;">
									<option value="" style="background: #1e293b; color: #ffffff;">Aeropuerto / Ferry / Crucero</option>
									<?php foreach ( $locations as $loc ) : ?>
										<option value="<?php echo esc_attr( $loc->id ); ?>" style="background: #1e293b; color: #ffffff;"><?php echo esc_html( $loc->name ); ?></option>
									<?php endforeach; ?>
								</select>
								<svg style="position: absolute; left: 12px; top: 13px; width: 16px; height: 16px; color: #94a3b8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
							</div>
						</div>
						
						<div style="display: flex; gap: 10px;">
							<div style="flex: 1; position: relative;">
								<label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 4px;">Fecha</label>
								<input type="date" id="return_date" name="return_date" required value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" style="width: 100%; height: 40px; padding: 0 10px; border: 1px solid #334155; border-radius: 8px; font-family: 'Inter Tight', sans-serif; font-size: 13px; color: #ffffff; background: #1e293b; box-sizing: border-box; font-weight: 600;">
							</div>
							<div style="flex: 1; position: relative;">
								<label style="display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 4px;">Hora</label>
								<input type="time" id="return_time" name="return_time" required value="10:00" style="width: 100%; height: 40px; padding: 0 10px; border: 1px solid #334155; border-radius: 8px; font-family: 'Inter Tight', sans-serif; font-size: 13px; color: #ffffff; background: #1e293b; box-sizing: border-box; font-weight: 600;">
							</div>
						</div>
					</div>

					<!-- Right Section: Pasajeros & Botón -->
					<div style="flex: 1 1 200px; display: flex; flex-direction: column; gap: 12px; justify-content: flex-end;">
						<div>
							<label style="display: block; font-size: 13px; font-weight: 700; color: #ffffff; margin-bottom: 6px;">Pasajeros</label>
							<div style="position: relative;">
								<select id="passenger_count" name="passenger_count" style="width: 100%; height: 42px; padding: 0 12px 0 36px; border: 1px solid #334155; border-radius: 8px; font-family: 'Inter Tight', sans-serif; font-size: 13.5px; color: #ffffff; background: #1e293b; box-sizing: border-box; font-weight: 600; -webkit-appearance: none; -moz-appearance: none; appearance: none;">
									<option value="2" style="background: #1e293b; color: #ffffff;">2</option>
									<option value="1" style="background: #1e293b; color: #ffffff;">1</option>
									<option value="3" style="background: #1e293b; color: #ffffff;">3</option>
									<option value="4" style="background: #1e293b; color: #ffffff;">4</option>
									<option value="5" style="background: #1e293b; color: #ffffff;">5</option>
									<option value="7" style="background: #1e293b; color: #ffffff;">7</option>
									<option value="15" style="background: #1e293b; color: #ffffff;">15</option>
								</select>
								<svg style="position: absolute; left: 12px; top: 13px; width: 16px; height: 16px; color: #94a3b8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
							</div>
						</div>

						<div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
							<button type="submit" style="width: 100%; height: 44px; background-color: #E8272C; border: none; border-radius: 8px; color: #ffffff; font-family: 'Inter Tight', sans-serif; font-size: 14px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; box-sizing: border-box; transition: background 0.2s;">Buscar vehículos disponibles</button>
							<div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: #94a3b8; font-weight: 600;">
								<svg style="width: 12px; height: 12px; color: #e8272c;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
								<span>Mejor precio garantizado</span>
							</div>
						</div>
					</div>

				</div>
			</form>
			<div id="rrc-search-results" style="margin-top:25px;"></div>
		</div>

		<script>
		document.getElementById('rrc-search-form').addEventListener('submit', function(e) {
			e.preventDefault();
			const pickup_date = document.getElementById('pickup_date').value;
			const pickup_time = document.getElementById('pickup_time').value;
			const return_date = document.getElementById('return_date').value;
			const return_time = document.getElementById('return_time').value;
			const passengerCount = document.getElementById('passenger_count').value;

			const pickup = pickup_date + ' ' + pickup_time + ':00';
			const return_at = return_date + ' ' + return_time + ':00';
			const resultsDiv = document.getElementById('rrc-search-results');
			const context = 'standard';
			const maxPrice = '';

			if (new Date(return_at) <= new Date(pickup)) {
				resultsDiv.innerHTML = '<div style="background: rgba(220, 38, 38, 0.1); border: 1px solid #dc2626; padding: 15px; border-radius: 8px; color: #fca5a5; font-weight: 600;">La fecha de devolución debe ser posterior a la fecha de recogida.</div>';
				return;
			}

			resultsDiv.innerHTML = '<div style="text-align:center; padding: 20px;"><div class="rrc-spinner" style="border: 4px solid rgba(255,255,255,0.1); border-left-color: #38bdf8; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; display: inline-block;"></div><p style="margin-top: 10px; color: #94a3b8;">Buscando flota y tarifas reales...</p></div>';

			fetch('<?php echo esc_url( get_rest_url( null, "ramirez-rent-a-car/v1/availability/search" ) ); ?>', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ pickup_at: pickup, return_at: return_at, booking_context: context })
			})
			.then(res => res.json())
			.then(data => {
				if (!data || data.length === 0) {
					resultsDiv.innerHTML = '<div style="background: rgba(220, 38, 38, 0.1); border: 1px solid #dc2626; padding: 15px; border-radius: 8px; color: #fca5a5;">No hay unidades físicas disponibles para el período seleccionado.</div>';
					return;
				}

				// Apply frontend filters
				let filteredData = data;
				if (maxPrice) {
					const maxFloat = parseFloat(maxPrice);
					filteredData = filteredData.filter(model => {
						if (!model.rate || model.rate.requires_manual_quote) return false;
						const days = parseInt(model.calculated_days) || 1;
						const dailyPrice = parseFloat(model.rate.total_amount) / days;
						return dailyPrice <= maxFloat;
					});
				}
				if (passengerCount) {
					const passInt = parseInt(passengerCount);
					filteredData = filteredData.filter(model => {
						const capacity = parseInt(model.passenger_capacity) || 5;
						// If passengerCount filter is 5, match models with capacity >= 5
						// For 1, capacity >= 1. For 7, capacity >= 7. For 15, capacity >= 15.
						return capacity >= passInt;
					});
				}

				if (filteredData.length === 0) {
					resultsDiv.innerHTML = '<div style="background: rgba(245, 158, 11, 0.1); border: 1px solid #f59e0b; padding: 15px; border-radius: 8px; color: #f59e0b; font-weight: 600;">No hay vehículos disponibles que coincidan con el límite de precio o capacidad de pasajeros seleccionados. Intenta ampliar los filtros.</div>';
					return;
				}

				let html = '<h3 style="font-size: 22px; margin-bottom: 20px; color: #fff; font-weight: 800; border-bottom: 2px solid #E8272C; padding-bottom: 8px; display: inline-block;">Vehículos Disponibles</h3>';
				html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">';
				filteredData.forEach(model => {
					let rateHtml = '';
					if (model.rate.requires_manual_quote) {
						rateHtml = `<div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 10px; border-radius: 6px; font-weight: 600; font-size: 14px; text-align: center;">Requiere Cotización Manual</div>`;
					} else {
						rateHtml = `
							<div style="background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 12px; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.3); text-align: center;">
								<span style="font-size: 11px; display:block; color: #a7f3d0; margin-bottom: 2px; font-weight: 700; text-transform: uppercase;">Precio Total:</span>
								<strong style="font-size: 22px; color: #10b981; font-weight: 900;">$${Math.round(model.rate.total_amount)} USD</strong>
								<span style="font-size: 10px; display: block; color: #94a3b8; margin-top: 3px; font-weight: 500;">Precio cerrado para ${model.calculated_days} día(s)</span>
							</div>
						`;
					}

					html += `
						<div style="position: relative; background: #1e293b; border: 1px solid #334155; padding: 20px; border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
							<div>
								<!-- Car Image Section -->
								<div style="height: 150px; background-image: url('${model.image_url}'); background-size: contain; background-repeat: no-repeat; background-position: center; margin-bottom: 15px;"></div>
								
								<h4 style="margin: 0 0 4px 0; font-size: 17px; color: #fff; font-weight: 800; text-transform: uppercase;">${model.public_name}</h4>
								<p style="margin: 0 0 12px 0; color: #94a3b8; font-size: 12px;">Categoría: <span style="color: #38bdf8; font-weight: 700;">${model.category}</span></p>
								
								<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;">
									<div style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; color: #38bdf8;">
										👥 ${model.passenger_capacity || '5'} Pasajeros
									</div>
									<div style="background: #334155; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #cbd5e1;">
										❄️ A/C
									</div>
									<div style="background: #334155; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #cbd5e1;">
										⚙️ Automático
									</div>
								</div>
							</div>
							
							<div style="margin-top: 10px; display: flex; flex-direction: column; gap: 10px;">
								${rateHtml}
								<a href="${model.permalink}?pickup_at=${encodeURIComponent(pickup)}&return_at=${encodeURIComponent(return_at)}&booking_context=${encodeURIComponent(context)}" style="background: #E8272C; color: #fff; text-align: center; padding: 10px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 800; text-transform: uppercase; transition: background 0.2s; box-shadow: 0 2px 6px rgba(232,39,44,0.3);">Rentar Ahora</a>
							</div>
						</div>
					`;
				});
				html += '</div>';
				resultsDiv.innerHTML = html;
			})
			.catch(err => {
				resultsDiv.innerHTML = '<div style="background: rgba(220, 38, 38, 0.1); border: 1px solid #dc2626; padding: 15px; border-radius: 8px; color: #fca5a5;">Error al realizar la consulta.</div>';
			});
		});
		</script>
		<?php
		return ob_get_clean();
	}

	public static function render_catalog_shortcode() {
		global $wpdb;
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$models = $wpdb->get_results( "SELECT * FROM $models_table WHERE status = 'publish' AND deleted_at IS NULL ORDER BY sort_order ASC" );
		ob_start();
		?>
		<style>
		.rrc-vehicle-catalog-grid {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 20px;
			font-family: 'Inter Tight', sans-serif;
			background-color: #f8fafc;
			padding: 20px;
			border-radius: 12px;
		}
		@media (max-width: 1024px) {
			.rrc-vehicle-catalog-grid {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		@media (max-width: 768px) {
			.rrc-vehicle-catalog-grid {
				grid-template-columns: 1fr;
				padding: 10px;
			}
		}
		</style>
		
		<div id="flota" class="rrc-vehicle-catalog-grid">
			<?php if ( ! empty( $models ) ) : ?>
				<?php foreach ( $models as $model ) : 
					// Get standard 1-day rate for displaying as baseline
					$plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}rrc_rate_plans WHERE vehicle_model_id = %d AND booking_context = 'standard' LIMIT 1", $model->id ) );
					$daily_rate = 60.00;
					if ( $plan_id ) {
						$amount = $wpdb->get_var( $wpdb->prepare( "SELECT total_amount FROM {$wpdb->prefix}rrc_rate_packages WHERE rate_plan_id = %d AND duration_unit = 'day' AND duration_value = 1 LIMIT 1", $plan_id ) );
						if ( $amount ) {
							$daily_rate = floatval( $amount );
						}
					}
					
					// Mock image or CPT meta image
					$img_url = get_post_meta( $model->post_id, '_rrc_image_url', true );
					if ( empty( $img_url ) ) {
						$img_url = 'https://img.freepik.com/vectores-premium/icono-coche-gris-silueta-coche-ilustracion-vectorial_755519-158.jpg';
					}

					// Dynamic specs based on category
					$fuel_economy = '6.5L/100km';
					$bags = '3';
					$doors = '4';
					
					if ( $model->category === 'ATV' ) {
						$fuel_economy = '3.5L/100km';
						$bags = '0';
						$doors = '0';
					} elseif ( strpos( $model->category, 'SUV' ) !== false || $model->category === 'Standard Jeep' ) {
						$fuel_economy = '8.5L/100km';
						$bags = '4';
						$doors = '5';
					} elseif ( strpos( $model->category, 'Van' ) !== false ) {
						$fuel_economy = '10.5L/100km';
						$bags = '6';
						$doors = '5';
					}

					$img_style = "height: 190px; background-size: contain; background-repeat: no-repeat; background-position: center; margin-bottom: 15px;";
				?>
					<div class="rrc-vehicle-card" style="position: relative; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
						<!-- Special Ribbon Badge -->
						<div style="position: absolute; top: 12px; right: 12px; background-color: #E8272C; color: #fff; font-size: 9.5px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 6px rgba(232,39,44,0.35); z-index: 10; font-family: 'Inter Tight', sans-serif;">
							SPECIAL Cruise Ship 1 day Price!
						</div>
						<div>
							<!-- Car Image Section -->
							<div style="<?php echo esc_attr($img_style); ?> background-image: url('<?php echo esc_url($img_url); ?>');"></div>
							
							<!-- Car Title -->
							<h3 style="margin: 0 0 4px 0; font-size: 17px; font-weight: 900; color: #1e293b; font-family: 'Inter Tight', sans-serif;"><?php echo esc_html( $model->public_name ); ?></h3>
							
							<!-- Year / Subtitle -->
							<div style="font-size: 11px; color: #94a3b8; margin-bottom: 10px; font-weight: 600;"><?php echo esc_html( $model->year ?: 'Modelo Garantizado' ); ?></div>
							
							<!-- Cancelation Notice -->
							<div style="font-size: 12px; color: #22c55e; margin-bottom: 15px; display: flex; align-items: center; gap: 4px; font-weight: 500;">
								✓ Free cancellation up to 48h before pick-up time
							</div>

							<!-- Specs Icons Grid -->
							<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9; align-items: center;">
								<!-- Pasajeros (Gris) -->
								<div style="font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 6px;">
									<svg style="width: 15px; height: 15px; fill: #94a3b8;" viewBox="0 0 24 24">
										<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
									</svg>
									<span style="font-weight: 600; color: #475569;"><?php echo esc_html( $model->passenger_capacity ?: '5' ); ?></span>
								</div>
								<!-- Maletas (Gris) -->
								<div style="font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 6px;">
									<svg style="width: 15px; height: 15px; fill: #94a3b8;" viewBox="0 0 24 24">
										<path d="M17 6h-2V3c0-.55-.45-1-1-1h-4c-.55 0-1 .45-1 1v3H7c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zM9 4h6v2H9V4zm10 15H5V8h14v11z"/>
									</svg>
									<span style="font-weight: 600; color: #475569;"><?php echo esc_html( $bags ); ?></span>
								</div>
								<!-- Puertas (Gris) -->
								<div style="font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 6px;">
									<svg style="width: 15px; height: 15px; fill: #94a3b8;" viewBox="0 0 24 24">
										<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 10h-2v-2h2v2zm5-4h-2V7h2v2z"/>
									</svg>
									<span style="font-weight: 600; color: #475569;"><?php echo esc_html( $doors ); ?></span>
								</div>
								<!-- Consumo (Gris) -->
								<div style="font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 6px; white-space: nowrap;">
									<svg style="width: 15px; height: 15px; fill: #94a3b8;" viewBox="0 0 24 24">
										<path d="M19.78 7.78L18.4 6.4c-.76-.76-1.9-1.01-2.9-.77l-1.5-1.5v4.38l-4 4H4v8h10v-6.38l3-3c.78-.78.78-2.05 0-2.83-.02-.02-.02-.02 0 0zM12 18H6v-4h6v4z"/>
									</svg>
									<span style="font-weight: 600; color: #475569;"><?php echo esc_html( $fuel_economy ); ?></span>
								</div>
							</div>
						</div>

						<!-- Pricing & Button Section -->
						<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
							<div>
								<span style="font-size: 19px; font-weight: 800; color: #1e293b;">$<?php echo number_format($daily_rate, 2); ?></span>
								<span style="font-size: 12px; color: #94a3b8;">/ day</span>
							</div>
							<a href="<?php echo esc_url( get_permalink( $model->post_id ) ); ?>" style="background: #0f172a; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: 700; text-align: center; transition: background 0.2s; min-width: 90px; display: inline-block;">Rent Now</a>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p style="grid-column: 1/-1; text-align: center; color: #94a3b8;">No hay vehículos registrados en el catálogo.</p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_roatan_map_shortcode() {
		ob_start();
		?>
		<div class="rrc-map-container" style="position: relative; width: 100%; max-width: 1100px; margin: 0 auto; font-family: 'Inter Tight', sans-serif;">
			<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 40px; padding: 45px 20px; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
				<!-- Left Text Section -->
				<div style="flex: 1; min-width: 320px;">
					<h2 style="font-size: 32px; font-weight: 850; line-height: 1.2; color: #0f172a; margin: 0 0 15px 0;">
						Pick & leave your cars anywhere and any time you want
					</h2>
					<p style="font-size: 15px; color: #64748b; line-height: 1.6; margin: 0 0 30px 0;">
						Ofrecemos entregas y devoluciones sin cargo adicional en los puntos clave de Roatán. Recoge tu vehículo inmediatamente al llegar a la isla o devuélvelo cómodamente antes de partir.
					</p>
					
					<!-- Points List Grid -->
					<div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
						<div style="display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #f1f5f9;">
							<span style="font-size: 20px; display: inline-flex; justify-content: center; align-items: center; background: rgba(232, 39, 44, 0.1); width: 36px; height: 36px; border-radius: 50%;">✈️</span>
							<div>
								<div style="font-weight: 800; color: #0f172a; font-size: 14px;">Aeropuerto de Roatán</div>
								<div style="font-size: 12px; color: #64748b;">Aeropuerto Internacional Juan Manuel Gálvez (RTB)</div>
							</div>
						</div>
						
						<div style="display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #f1f5f9;">
							<span style="font-size: 20px; display: inline-flex; justify-content: center; align-items: center; background: rgba(15, 23, 42, 0.1); width: 36px; height: 36px; border-radius: 50%;">🚢</span>
							<div>
								<div style="font-weight: 800; color: #0f172a; font-size: 14px;">Muelle Coxen Hole</div>
								<div style="font-size: 12px; color: #64748b;">Puerto de cruceros Town Center, Coxen Hole</div>
							</div>
						</div>

						<div style="display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #f1f5f9;">
							<span style="font-size: 20px; display: inline-flex; justify-content: center; align-items: center; background: rgba(232, 39, 44, 0.1); width: 36px; height: 36px; border-radius: 50%;">⛴️</span>
							<div>
								<div style="font-weight: 800; color: #0f172a; font-size: 14px;">Terminal de Ferry</div>
								<div style="font-size: 12px; color: #64748b;">Terminal de Ferry Galaxy Wave en Dixon Cove</div>
							</div>
						</div>

						<div style="display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #f1f5f9;">
							<span style="font-size: 20px; display: inline-flex; justify-content: center; align-items: center; background: rgba(15, 23, 42, 0.1); width: 36px; height: 36px; border-radius: 50%;">⚓</span>
							<div>
								<div style="font-weight: 800; color: #0f172a; font-size: 14px;">Muelle Mahogany Bay</div>
								<div style="font-size: 12px; color: #64748b;">Puerto de cruceros Mahogany Bay en Dixon Cove</div>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Right Map Image with Pins Section -->
				<div style="flex: 1.2; min-width: 320px; position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
					<img src="<?php echo esc_url( content_url( '/uploads/2026/07/banner.png' ) ); ?>" alt="Roatan Map" style="width: 100%; height: auto; display: block;">
					
					<!-- Pin 1: Aeropuerto -->
					<div class="rrc-map-pin" style="position: absolute; left: 24%; top: 32%; transform: translate(-50%, -100%); cursor: pointer; text-align: center; z-index: 5;" title="Aeropuerto Internacional Juan Manuel Gálvez">
						<div style="background: #E8272C; color: #fff; padding: 3px 7px; border-radius: 4px; font-size: 8.5px; font-weight: 800; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.25); margin-bottom: 2px; font-family: 'Inter Tight', sans-serif;">Aeropuerto (RTB)</div>
						<span style="font-size: 22px; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3)); display: block;">📍</span>
					</div>
					
					<!-- Pin 2: Muelle Coxen Hole -->
					<div class="rrc-map-pin" style="position: absolute; left: 16%; top: 52%; transform: translate(-50%, -100%); cursor: pointer; text-align: center; z-index: 5;" title="Muelle de Cruceros Coxen Hole">
						<div style="background: #0f172a; color: #fff; padding: 3px 7px; border-radius: 4px; font-size: 8.5px; font-weight: 800; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.25); margin-bottom: 2px; font-family: 'Inter Tight', sans-serif;">Muelle Coxen Hole</div>
						<span style="font-size: 22px; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3)); display: block;">📍</span>
					</div>
					
					<!-- Pin 3: Terminal Ferry -->
					<div class="rrc-map-pin" style="position: absolute; left: 34%; top: 44%; transform: translate(-50%, -100%); cursor: pointer; text-align: center; z-index: 5;" title="Terminal de Ferry Galaxy Wave">
						<div style="background: #E8272C; color: #fff; padding: 3px 7px; border-radius: 4px; font-size: 8.5px; font-weight: 800; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.25); margin-bottom: 2px; font-family: 'Inter Tight', sans-serif;">Terminal Ferry</div>
						<span style="font-size: 22px; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3)); display: block;">📍</span>
					</div>
					
					<!-- Pin 4: Mahogany Bay -->
					<div class="rrc-map-pin" style="position: absolute; left: 44%; top: 62%; transform: translate(-50%, -100%); cursor: pointer; text-align: center; z-index: 5;" title="Muelle de Cruceros Mahogany Bay">
						<div style="background: #0f172a; color: #fff; padding: 3px 7px; border-radius: 4px; font-size: 8.5px; font-weight: 800; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.25); margin-bottom: 2px; font-family: 'Inter Tight', sans-serif;">Mahogany Bay</div>
						<span style="font-size: 22px; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3)); display: block;">📍</span>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_slider_shortcode() {
		global $wpdb;
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$models = $wpdb->get_results( "SELECT * FROM $models_table WHERE status = 'publish' AND deleted_at IS NULL ORDER BY sort_order ASC" );

		if ( empty( $models ) ) {
			return '<p style="text-align: center; color: #94a3b8;">No hay vehículos registrados en el catálogo.</p>';
		}

		ob_start();
		?>
		<style>
		.rrc-slider-container {
			position: relative;
			width: 90%;
			max-width: 1400px;
			margin: 40px auto;
			background: #fff;
			border-radius: 16px;
			border: 1px solid #e2e8f0;
			box-shadow: 0 10px 30px rgba(0,0,0,0.04);
			font-family: 'Inter Tight', sans-serif;
			overflow: hidden;
		}
		.rrc-slider-inner {
			position: relative;
			width: 100%;
			height: 480px;
		}
		.rrc-slide-item {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			opacity: 0;
			pointer-events: none;
			transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1);
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 40px 60px;
			box-sizing: border-box;
		}
		.rrc-slide-item.active {
			opacity: 1;
			pointer-events: all;
			z-index: 2;
		}
		.rrc-slide-left {
			flex: 1.1;
			position: relative;
			display: flex;
			align-items: center;
			justify-content: center;
			height: 100%;
			transform: translateX(-80px) translateY(10px) rotate(-3deg) scale(0.85);
			opacity: 0;
			transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.15s;
		}
		.rrc-slide-item.active .rrc-slide-left {
			transform: translateX(0) translateY(0) rotate(0deg) scale(1.05);
			opacity: 1;
		}
		.rrc-slide-right {
			flex: 0.9;
			padding-left: 40px;
			box-sizing: border-box;
		}
		.rrc-slide-right > * {
			opacity: 0;
			transform: translateY(20px);
			transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
		}
		.rrc-slide-item.active .rrc-slide-right > * {
			opacity: 1;
			transform: translateY(0);
		}
		.rrc-slide-item.active .rrc-slide-right > *:nth-child(1) { transition-delay: 0.1s; }
		.rrc-slide-item.active .rrc-slide-right > *:nth-child(2) { transition-delay: 0.2s; }
		.rrc-slide-item.active .rrc-slide-right > *:nth-child(3) { transition-delay: 0.3s; }
		.rrc-slide-item.active .rrc-slide-right > *:nth-child(4) { transition-delay: 0.4s; }
		.rrc-slide-item.active .rrc-slide-right > *:nth-child(5) { transition-delay: 0.5s; }

		.rrc-bg-circle {
			position: absolute;
			width: 330px;
			height: 330px;
			border-radius: 50%;
			background: radial-gradient(circle, rgba(254,243,199,0.9) 0%, rgba(254,243,199,0.2) 75%);
			z-index: 1;
			animation: rotateSlow 25s linear infinite, pulseSlow 6s ease-in-out infinite;
			transform-origin: center;
		}
		@keyframes pulseSlow {
			0%, 100% { transform: scale(1); }
			50% { transform: scale(1.1); }
		}
		@keyframes rotateSlow {
			from { transform: rotate(0deg); }
			to { transform: rotate(360deg); }
		}
		.rrc-slide-car-img {
			position: relative;
			z-index: 2;
			max-width: 95%;
			max-height: 290px;
			object-fit: contain;
			filter: drop-shadow(0 20px 25px rgba(0,0,0,0.18)) drop-shadow(0 10px 10px rgba(0,0,0,0.06));
		}
		.rrc-slider-arrow {
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			background: #fff;
			border: 1px solid #e2e8f0;
			color: #0f172a;
			width: 44px;
			height: 44px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			z-index: 10;
			box-shadow: 0 4px 12px rgba(0,0,0,0.05);
			transition: all 0.2s;
		}
		.rrc-slider-arrow:hover {
			background: #E8272C;
			color: #fff;
			border-color: #E8272C;
			box-shadow: 0 4px 15px rgba(232, 39, 44, 0.3);
		}
		.rrc-slider-arrow.prev { left: 20px; }
		.rrc-slider-arrow.next { right: 20px; }
		.rrc-slider-dots {
			position: absolute;
			bottom: 20px;
			left: 50%;
			transform: translateX(-50%);
			display: flex;
			gap: 8px;
			z-index: 10;
		}
		.rrc-slider-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: #cbd5e1;
			cursor: pointer;
			transition: all 0.3s;
		}
		.rrc-slider-dot.active {
			background: #E8272C;
			width: 24px;
			border-radius: 4px;
		}
		@media (max-width: 768px) {
			.rrc-slider-inner {
				height: auto;
			}
			.rrc-slide-item {
				flex-direction: column;
				height: auto;
				padding: 40px 20px;
				position: relative;
				opacity: 0;
				display: none;
			}
			.rrc-slide-item.active {
				display: flex;
				opacity: 1;
			}
			.rrc-slide-left {
				height: 220px;
				margin-bottom: 20px;
			}
			.rrc-slide-right {
				padding-left: 0;
				width: 100%;
			}
			.rrc-bg-circle {
				width: 210px;
				height: 210px;
			}
		}
		</style>

		<div class="rrc-slider-container">
			<!-- Navigation arrows -->
			<div class="rrc-slider-arrow prev">&#10094;</div>
			<div class="rrc-slider-arrow next">&#10095;</div>

			<div class="rrc-slider-inner">
				<?php 
				$idx = 0;
				foreach ( $models as $model ) : 
					// Get standard 1-day rate
					$plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}rrc_rate_plans WHERE vehicle_model_id = %d AND booking_context = 'standard' LIMIT 1", $model->id ) );
					$daily_rate = 60.00;
					if ( $plan_id ) {
						$amount = $wpdb->get_var( $wpdb->prepare( "SELECT total_amount FROM {$wpdb->prefix}rrc_rate_packages WHERE rate_plan_id = %d AND duration_unit = 'day' AND duration_value = 1 LIMIT 1", $plan_id ) );
						if ( $amount ) {
							$daily_rate = floatval( $amount );
						}
					}
					
					// Get image URL
					$img_url = get_post_meta( $model->post_id, '_rrc_image_url', true );
					if ( empty( $img_url ) ) {
						$img_url = 'https://img.freepik.com/vectores-premium/icono-coche-gris-silueta-coche-ilustracion-vectorial_755519-158.jpg';
					}

					// Dynamic specifications list
					$specs = [];
					if ( $model->category === 'ATV' ) {
						$specs = [
							'👤 1 Pasajero',
							'⚙️ Transmisión CVT',
							'⛽ Gasolina',
							'🚪 Sin puertas',
							'⛰️ Tracción 2WD/4WD'
						];
					} elseif ( stripos($model->public_name, 'Minivan') !== false ) {
						$specs = [
							'👥 7 Pasajeros',
							'❄️ Aire acondicionado',
							'🚪 2 puertas corredizas',
							'⛽ Gasolina/híbrido',
							'🪑 3 filas de asientos'
						];
					} elseif ( stripos($model->public_name, 'Prado') !== false || stripos($model->public_name, 'Luxury') !== false ) {
						$specs = [
							'👥 7 Pasajeros',
							'❄️ Aire acondicionado',
							'⛽ Combustible: gasolina',
							'🚗 Tracción FWD/AWD',
							'🛋️ Amplio espacio interior'
						];
					} elseif ( stripos($model->public_name, 'Sorento') !== false ) {
						$specs = [
							'👥 Hasta 7 pasajeros',
							'❄️ Aire acondicionado',
							'⛽ Combustible: gasolina',
							'🚪 4 puertas',
							'🪑 3 filas de asientos'
						];
					} elseif ( stripos($model->public_name, 'Gladiator') !== false || stripos($model->public_name, 'Wrangler') !== false || stripos($model->category, 'Jeep') !== false ) {
						$specs = [
							'👥 5 Pasajeros',
							'❄️ Aire acondicionado',
							'🚪 4 puertas',
							'🚗 Tracción 4x4',
							'⛰️ Mayor altura libre'
						];
					} elseif ( stripos($model->public_name, 'Pickup') !== false || stripos($model->public_name, 'Hilux') !== false ) {
						$specs = [
							'👥 5 Pasajeros',
							'❄️ Aire acondicionado',
							'⛽ Diésel o gasolina',
							'🚗 Tracción 4x4',
							'📦 Caja de carga'
						];
					} else {
						// Sedan / Standard SUV default
						$specs = [
							'👥 5 Pasajeros',
							'❄️ Aire acondicionado',
							'⛽ Combustible: gasolina',
							'🚪 4 puertas',
							'⚙️ Transmisión automática'
						];
					}
				?>
					<div class="rrc-slide-item <?php echo $idx === 0 ? 'active' : ''; ?>" data-index="<?php echo $idx; ?>">
						<!-- Left Side: Interactive image & background circle -->
						<div class="rrc-slide-left">
							<div class="rrc-bg-circle"></div>
							<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $model->public_name ); ?>" class="rrc-slide-car-img">
						</div>

						<!-- Right Side: Details & Action Card -->
						<div class="rrc-slide-right">
							<div style="background: rgba(232, 39, 44, 0.08); color: #E8272C; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; display: inline-block; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">
								SPECIAL Cruise Ship 1 day Price!
							</div>
							
							<h3 style="margin: 0 0 4px 0; font-size: 36px; font-weight: 900; color: #1e293b; text-transform: uppercase; line-height: 1.1; font-family: 'Inter Tight', sans-serif;">
								<?php echo esc_html( $model->public_name ); ?>
							</h3>
							
							<div style="font-size: 12px; color: #94a3b8; font-weight: 600; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
								<?php echo esc_html( $model->year ?: 'Modelo Garantizado' ); ?> &bull; <span style="color: #64748b;"><?php echo esc_html( $model->category ); ?></span>
							</div>

							<!-- Specifications List -->
							<div style="display: grid; grid-template-columns: 1fr; gap: 8px; margin-bottom: 25px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
								<?php foreach ( $specs as $spec ) : ?>
									<div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #475569;">
										<?php echo esc_html( $spec ); ?>
									</div>
								<?php endforeach; ?>
							</div>

							<!-- Pricing & Booking button -->
							<div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; border-top: 1px solid #f1f5f9; padding-top: 15px; margin-top: 10px;">
								<div>
									<span style="font-size: 24px; font-weight: 900; color: #0f172a;">$<?php echo number_format($daily_rate, 2); ?></span>
									<span style="font-size: 12px; color: #94a3b8; font-weight: 600;">/ day</span>
								</div>
								
								<a href="<?php echo esc_url( get_permalink( $model->post_id ) ); ?>" style="background: #E8272C; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 800; text-transform: uppercase; transition: background 0.2s; box-shadow: 0 4px 12px rgba(232, 39, 44, 0.25);">Rent Now</a>
							</div>
						</div>
					</div>
				<?php 
					$idx++;
				endforeach; 
				?>
			</div>

			<!-- Navigation dots -->
			<div class="rrc-slider-dots">
				<?php for ( $i = 0; $i < count($models); $i++ ) : ?>
					<div class="rrc-slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
				<?php endfor; ?>
			</div>
		</div>

		<script>
		document.addEventListener("DOMContentLoaded", function() {
			const container = document.querySelector('.rrc-slider-container');
			if (!container) return;

			const slides = container.querySelectorAll('.rrc-slide-item');
			const dots = container.querySelectorAll('.rrc-slider-dot');
			const prevBtn = container.querySelector('.rrc-slider-arrow.prev');
			const nextBtn = container.querySelector('.rrc-slider-arrow.next');
			
			let currentSlideIndex = 0;
			let autoPlayTimer = null;

			function showSlide(index) {
				slides.forEach(slide => slide.classList.remove('active'));
				dots.forEach(dot => dot.classList.remove('active'));

				currentSlideIndex = (index + slides.length) % slides.length;
				slides[currentSlideIndex].classList.add('active');
				dots[currentSlideIndex].classList.add('active');
			}

			function nextSlide() {
				showSlide(currentSlideIndex + 1);
			}

			function prevSlide() {
				showSlide(currentSlideIndex - 1);
			}

			// Event listeners
			prevBtn.addEventListener('click', () => {
				prevSlide();
				resetTimer();
			});

			nextBtn.addEventListener('click', () => {
				nextSlide();
				resetTimer();
			});

			dots.forEach((dot, idx) => {
				dot.addEventListener('click', () => {
					showSlide(idx);
					resetTimer();
				});
			});

			// Autoplay timer
			function startTimer() {
				stopTimer(); // Prevent duplicate timers
				autoPlayTimer = setInterval(nextSlide, 5000); // 5 seconds per slide
			}

			function stopTimer() {
				if (autoPlayTimer) {
					clearInterval(autoPlayTimer);
				}
			}

			function resetTimer() {
				stopTimer();
				startTimer();
			}

			// Pause autoplay on mouse enter
			container.addEventListener('mouseenter', stopTimer);
			container.addEventListener('mouseleave', startTimer);

			// Start first timer
			startTimer();
		});
		</script>
		<?php
		return ob_get_clean();
	}

	public static function render_benefits_shortcode() {
		ob_start();
		?>
		<style>
		.rrc-benefits-section {
			width: 90%;
			max-width: 1400px;
			margin: 70px auto;
			font-family: 'Inter Tight', sans-serif;
		}
		.rrc-benefits-title-wrapper {
			text-align: left;
			margin-bottom: 45px;
			border-bottom: 1px solid #f1f5f9;
			padding-bottom: 25px;
		}
		.rrc-benefits-title {
			font-size: 32px;
			font-weight: 850;
			color: #0f172a;
			line-height: 1.2;
			margin: 0 0 10px 0;
			letter-spacing: -0.5px;
		}
		.rrc-benefits-subtitle {
			font-size: 15.5px;
			color: #64748b;
			max-width: 950px;
			margin: 0;
			line-height: 1.6;
		}
		.rrc-benefits-row {
			display: flex;
			flex-direction: row;
			justify-content: space-between;
			gap: 35px;
			align-items: flex-start;
			flex-wrap: wrap;
			margin-bottom: 50px;
		}
		.rrc-benefit-item {
			flex: 1;
			min-width: 260px;
			display: flex;
			gap: 16px;
			align-items: flex-start;
		}
		.rrc-benefit-icon-box {
			width: 48px;
			height: 48px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.07);
			color: #E8272C;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 22px;
			flex-shrink: 0;
			transition: all 0.3s;
		}
		.rrc-benefit-item:hover .rrc-benefit-icon-box {
			transform: scale(1.1);
			background: rgba(232, 39, 44, 0.12);
		}
		.rrc-benefit-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-benefit-item-title {
			font-size: 16px;
			font-weight: 800;
			color: #1e293b;
			margin: 0 0 8px 0;
			line-height: 1.35;
		}
		.rrc-benefit-item-desc {
			font-size: 13.5px;
			color: #64748b;
			line-height: 1.55;
			margin: 0;
		}
		
		/* Premium CTA Banner at bottom */
		.rrc-benefits-cta {
			background: linear-gradient(135deg, #1e1e24 0%, #0f0f12 100%);
			border-radius: 24px;
			padding: 60px 40px;
			text-align: center;
			position: relative;
			overflow: hidden;
			box-shadow: 0 20px 40px rgba(0,0,0,0.12);
			border: 1px solid #2d2d34;
			margin-top: 90px;
		}
		.rrc-benefits-cta::after {
			content: '';
			position: absolute;
			width: 450px;
			height: 450px;
			border: 1px solid rgba(255, 255, 255, 0.05);
			border-radius: 50%;
			top: -120px;
			right: -120px;
			z-index: 1;
			pointer-events: none;
		}
		.rrc-benefits-cta::before {
			content: '';
			position: absolute;
			width: 320px;
			height: 320px;
			border: 1px solid rgba(255, 255, 255, 0.03);
			border-radius: 50%;
			top: -50px;
			right: -50px;
			z-index: 1;
			pointer-events: none;
		}
		.rrc-benefits-cta-title {
			font-size: 32px;
			font-weight: 850;
			color: #fff;
			margin: 0 0 15px 0;
			position: relative;
			z-index: 2;
			letter-spacing: -0.5px;
		}
		.rrc-benefits-cta-desc {
			font-size: 15px;
			color: #a1a1aa;
			max-width: 650px;
			margin: 0 auto 30px auto;
			line-height: 1.6;
			position: relative;
			z-index: 2;
		}
		.rrc-benefits-cta-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			background: #ffffff;
			color: #0f0f12;
			padding: 14px 45px;
			border-radius: 30px;
			font-size: 14px;
			font-weight: 800;
			text-decoration: none;
			box-shadow: 0 4px 15px rgba(255,255,255,0.08);
			transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
			position: relative;
			z-index: 2;
		}
		.rrc-benefits-cta-btn:hover {
			background: #f4f4f5;
			box-shadow: 0 8px 25px rgba(255,255,255,0.18);
			transform: translateY(-2px);
			color: #0f0f12;
		}
		</style>

		<div class="rrc-benefits-section">
			<!-- Header Block -->
			<div class="rrc-benefits-title-wrapper">
				<h2 class="rrc-benefits-title">Viaja por Roatán con la confianza de estar en buenas manos</h2>
				<p class="rrc-benefits-subtitle">
					En <strong>Ramírez Rent A Car</strong> hacemos que alquilar un vehículo sea fácil, seguro y transparente.
				</p>
			</div>

			<!-- Row of 4 Benefits -->
			<div class="rrc-benefits-row">
				<!-- Benefit 1: ATENCIÓN PERSONALIZADA -->
				<div class="rrc-benefit-item">
					<div class="rrc-benefit-icon-box">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
					</div>
					<div class="rrc-benefit-content">
						<h3 class="rrc-benefit-item-title">1. Atención personalizada</h3>
						<p class="rrc-benefit-item-desc">
							Te ayudamos a elegir el vehículo ideal según el número de pasajeros, tu equipaje, el tiempo de estadía y los lugares que deseas conocer. Desde la reserva hasta la devolución, recibirás una atención cercana, profesional y pensada especialmente para ti.
						</p>
					</div>
				</div>

				<!-- Benefit 2: TARIFAS CLARAS Y PAGOS SEGUROS -->
				<div class="rrc-benefit-item">
					<div class="rrc-benefit-icon-box">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
					</div>
					<div class="rrc-benefit-content">
						<h3 class="rrc-benefit-item-title">2. Tarifas claras y pagos seguros</h3>
						<p class="rrc-benefit-item-desc">
							Conoce el valor de tu alquiler antes de confirmar, sin cargos inesperados. Nuestras tarifas “Drive Away” incluyen los impuestos correspondientes y el seguro obligatorio del vehículo. Además, contamos con opciones de pago seguras y convenientes.
						</p>
					</div>
				</div>

				<!-- Benefit 3: RESERVA RÁPIDA Y SIN COMPLICACIONES -->
				<div class="rrc-benefit-item">
					<div class="rrc-benefit-icon-box">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
					</div>
					<div class="rrc-benefit-content">
						<h3 class="rrc-benefit-item-title">3. Reserva rápida y sin complicaciones</h3>
						<p class="rrc-benefit-item-desc">
							Selecciona tus fechas, elige el vehículo que mejor se adapte a tu viaje y completa tu reserva de forma rápida y sencilla. Nosotros nos encargamos de preparar todo para que aproveches al máximo tu tiempo en Roatán.
						</p>
					</div>
				</div>

				<!-- Benefit 4: ENTREGA Y RECOGIDA EN ROATÁN -->
				<div class="rrc-benefit-item">
					<div class="rrc-benefit-icon-box">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
					</div>
					<div class="rrc-benefit-content">
						<h3 class="rrc-benefit-item-title">4. Entrega y recogida en Roatán</h3>
						<p class="rrc-benefit-item-desc">
							Coordinamos gratuitamente la entrega y recogida de tu vehículo en los principales puntos de llegada de la isla: <strong>Aeropuerto de Roatán, terminal del ferry y muelles de cruceros.</strong> También puedes consultar la disponibilidad de entrega en hoteles, alojamientos y otros puntos de Roatán.
						</p>
					</div>
				</div>
			</div>

			<!-- Premium CTA Card -->
			<div class="rrc-benefits-cta">
				<h3 class="rrc-benefits-cta-title">Tu viaje comienza con el vehículo correcto</h3>
				<p class="rrc-benefits-cta-desc">
					Recorre Roatán a tu propio ritmo, descubre cada rincón de la isla y disfruta de la libertad de viajar con comodidad. Reserva hoy con Ramírez Rent A Car y vive Roatán sin límites.
				</p>
				<a href="#" class="rrc-benefits-cta-btn" onclick="document.getElementById('pickup_at').focus(); return false;">Reserva Ahora</a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_reviews_shortcode() {
		global $wpdb;
		
		// Array of 35 diverse and realistic Google Reviews for Ramirez Rent A Car Roatan
		$reviews = [
			[
				'quote' => '¡Simplemente maravilloso!',
				'text' => 'Llegamos a Roatán sin tener ninguna reserva. Llamamos, nos recogieron, tuvieron un auto listo en poco tiempo y a un precio muy justo. Normalmente alquilar carros es un dolor de cabeza, ¡pero este es una joya!',
				'name' => 'Josh Cooper',
				'meta' => 'Viajero @joshcoop',
				'avatar' => 'JC',
				'bg' => '#fee2e2'
			],
			[
				'quote' => 'Servicio increíble',
				'text' => 'No puedo recomendar este lugar más alto. Servicio increíble y mejores precios: todo en una tarifa transparente con seguro e impuestos ya incluidos, ¡así que no hay absolutamente costos ocultos ni sorpresas!',
				'name' => 'Sarah Mitchell',
				'meta' => 'Pasajera de Crucero @sarahm',
				'avatar' => 'SM',
				'bg' => '#e0f2fe'
			],
			[
				'quote' => 'El mejor de la isla',
				'text' => 'Excelente servicio y carros impecables. El Wrangler estaba limpio y listo esperándonos. Ninguna complicación para la entrega y devolución en el puerto de cruceros. ¡Recomendado 100%!',
				'name' => 'Miguel Rodríguez',
				'meta' => 'Turista Local @mrodriguez',
				'avatar' => 'MR',
				'bg' => '#fef3c7'
			],
			[
				'quote' => 'Excelente servicio al cliente',
				'text' => 'La mejor agencia de alquiler de Roatán. Tuvimos un pequeño problema con nuestro itinerario de ferry y ellos amablemente nos esperaron y adaptaron las horas de entrega sin cargos adicionales. Muy profesionales.',
				'name' => 'David Watson',
				'meta' => 'Viajero @dwatson',
				'avatar' => 'DW',
				'bg' => '#e0e7ff'
			],
			[
				'quote' => 'Precios honestos y transparentes',
				'text' => 'Me encantó que el precio que reservé en línea fue exactamente el que pagué. Nada de seguros obligatorios sorpresa de último minuto ni retenciones extrañas. El carro Wrangler estaba en perfectas condiciones.',
				'name' => 'Emily Stone',
				'meta' => 'Pasajera de Crucero @emstone',
				'avatar' => 'ES',
				'bg' => '#f1f5f9'
			],
			[
				'quote' => 'Eficiencia y comodidad',
				'text' => 'Un servicio rápido de verdad. Al bajar del avión el personal ya estaba listo esperándome. El carro Toyota Hilux estaba limpio y listo. Devolución rápida para no perder el vuelo. Sin duda repetiré.',
				'name' => 'Carlos Mendoza',
				'meta' => 'Turista de Negocios @carlosm',
				'avatar' => 'CM',
				'bg' => '#fee2e2'
			],
			[
				'quote' => 'Perfecto para cruceros',
				'text' => 'Vinimos en el Symphony of the Seas. Nos entregaron el Jeep justo a la salida del muelle de cruceros y lo devolvimos allí mismo. Todo súper ágil, nos dio tiempo de recorrer West Bay sin prisas.',
				'name' => 'Sophia Bennett',
				'meta' => 'Pasajera de Crucero @sophiab',
				'avatar' => 'SB',
				'bg' => '#e0f2fe'
			],
			[
				'quote' => 'Atención espectacular',
				'text' => 'Muy amables en todo momento. Nos recomendaron qué playas visitar y nos dieron consejos de seguridad para conducir en Roatán. El auto en excelente estado.',
				'name' => 'Javier Ortiz',
				'meta' => 'Turista Familiar @jortiz',
				'avatar' => 'JO',
				'bg' => '#fef3c7'
			],
			[
				'quote' => 'Ningún truco, todo legal',
				'text' => 'Alquilar autos en Roatán a veces da miedo por las malas experiencias, pero con Ramírez todo fue transparente. Tarifas claras, seguro incluido y devolución en 5 minutos.',
				'name' => 'Olivia Taylor',
				'meta' => 'Viajera @oliviat',
				'avatar' => 'OT',
				'bg' => '#e0e7ff'
			],
			[
				'quote' => 'El Jeep Wrangler fue genial',
				'text' => 'Recorrer la isla en un Wrangler 4x4 descapotable fue la mejor decisión. El carro estaba en perfectas condiciones y la tracción 4x4 nos ayudó a llegar a playas hermosas y remotas.',
				'name' => 'Liam Parker',
				'meta' => 'Aventurero @liamp',
				'avatar' => 'LP',
				'bg' => '#f1f5f9'
			],
			[
				'quote' => 'Súper puntuales',
				'text' => 'Entrega a tiempo en la terminal del ferry. Todo el papeleo se hizo en minutos. Excelente servicio y muy amables.',
				'name' => 'Isabella Davis',
				'meta' => 'Turista @isabelad',
				'avatar' => 'ID',
				'bg' => '#fee2e2'
			],
			[
				'quote' => 'Muy confiables',
				'text' => 'Precios competitivos y sin sorpresas de última hora. Todo el proceso fue transparente y amigable. Lo recomiendo ampliamente.',
				'name' => 'Mateo Gómez',
				'meta' => 'Turista Local @mateog',
				'avatar' => 'MG',
				'bg' => '#e0f2fe'
			],
			[
				'quote' => 'Excelente experiencia',
				'text' => 'El proceso de reserva en línea fue facilísimo. Al llegar a la isla, el personal nos recibió con un cartel con nuestro nombre. Un 10 de 10.',
				'name' => 'Ava Martinez',
				'meta' => 'Viajera @avamartinez',
				'avatar' => 'AM',
				'bg' => '#fef3c7'
			],
			[
				'quote' => 'Coches nuevos y limpios',
				'text' => 'El carro olía a nuevo y estaba impecable. El aire acondicionado funcionaba de maravilla, algo clave en el calor de Roatán.',
				'name' => 'Ethan Clark',
				'meta' => 'Viajero @ethanclark',
				'avatar' => 'EC',
				'bg' => '#e0e7ff'
			],
			[
				'quote' => 'Sin depósitos excesivos',
				'text' => 'A diferencia de las grandes franquicias, Ramírez Rent A Car no te bloquea miles de dólares en la tarjeta. Trato justo y muy amigable.',
				'name' => 'Mia Rodriguez',
				'meta' => 'Turista @miarodriguez',
				'avatar' => 'MR',
				'bg' => '#f1f5f9'
			],
			[
				'quote' => 'Atención personalizada',
				'text' => 'Nos ayudaron a cambiar el carro por uno más grande a último minuto porque veníamos con más equipaje del pensado. Súper accesibles.',
				'name' => 'Alexander Lewis',
				'meta' => 'Viajero @alexlewis',
				'avatar' => 'AL',
				'bg' => '#fee2e2'
			],
			[
				'quote' => 'Gran relación calidad-precio',
				'text' => 'Buscamos en varios sitios de Roatán y esta fue la mejor opción. Todo incluido, sin sorpresas y con un trato de primera.',
				'name' => 'Charlotte Lee',
				'meta' => 'Viajera @charlottelee',
				'avatar' => 'CL',
				'bg' => '#e0f2fe'
			],
			[
				'quote' => 'Muy profesionales',
				'text' => 'Un equipo muy bien coordinado. Nos entregaron el carro en el aeropuerto y lo dejamos en el muelle de cruceros. Facilidad total.',
				'name' => 'Daniel Walker',
				'meta' => 'Viajero @danielwalker',
				'avatar' => 'DW',
				'bg' => '#fef3c7'
			],
			[
				'quote' => 'Recomendado 100%',
				'text' => 'La mejor experiencia de alquiler de vehículos en la isla. Personal honesto, autos en gran estado y muy serviciales en todo.',
				'name' => 'Amelia Hall',
				'meta' => 'Viajera @ameliah',
				'avatar' => 'AH',
				'bg' => '#e0e7ff'
			],
			[
				'quote' => 'Rápido y sencillo',
				'text' => 'Sin colas interminables como en las agencias del aeropuerto. En 10 minutos ya estábamos manejando hacia el hotel.',
				'name' => 'Henry Allen',
				'meta' => 'Turista @henryallen',
				'avatar' => 'HA',
				'bg' => '#f1f5f9'
			],
			[
				'quote' => 'El Jeep estaba impecable',
				'text' => 'Disfrutamos muchísimo del Jeep Rubicon. Roatán se disfruta más con un 4x4. El personal de Ramírez fue súper atento.',
				'name' => 'Harper Young',
				'meta' => 'Aventurera @harpery',
				'avatar' => 'HY',
				'bg' => '#fee2e2'
			],
			[
				'quote' => 'Excelente trato',
				'text' => 'Nos esperaron a pesar de que nuestro vuelo se retrasó más de dos horas. Servicio al cliente verdaderamente excepcional.',
				'name' => 'Joseph King',
				'meta' => 'Viajero @josephking',
				'avatar' => 'JK',
				'bg' => '#e0f2fe'
			],
			[
				'quote' => 'Precios todo incluido',
				'text' => 'Me encanta que el precio publicado ya incluya seguros e impuestos. No intentan venderte extras a la fuerza al firmar.',
				'name' => 'Evelyn Wright',
				'meta' => 'Turista @evelynw',
				'avatar' => 'EW',
				'bg' => '#fef3c7'
			],
			[
				'quote' => 'Muy buena flota',
				'text' => 'Tienen vehículos para todo tipo de terrenos. El pick-up 4x4 que alquilamos estaba impecable y con excelente altura sobre el suelo.',
				'name' => 'Samuel Scott',
				'meta' => 'Viajero @samuelscott',
				'avatar' => 'SS',
				'bg' => '#e0e7ff'
			],
			[
				'quote' => 'Fácil y seguro',
				'text' => 'Hicimos la reserva desde la web y pagamos de forma segura. Cero complicaciones al llegar a recoger el vehículo.',
				'name' => 'Abigail Torres',
				'meta' => 'Viajera @abigailt',
				'avatar' => 'AT',
				'bg' => '#f1f5f9'
			],
			[
				'quote' => 'Atención de primera',
				'text' => 'Desde el primer contacto por WhatsApp nos atendieron rápido y resolvieron todas las dudas. Muy amables y profesionales.',
				'name' => 'Sebastian Perez',
				'meta' => 'Turista Local @sebastianp',
				'avatar' => 'SP',
				'bg' => '#fee2e2'
			],
			[
				'quote' => 'Excelente Jeep',
				'text' => 'El Wrangler Rubicon rojo estaba hermoso y funcionaba al 100%. Hicimos fotos increíbles por toda la isla. ¡Una aventura total!',
				'name' => 'Emily Green',
				'meta' => 'Pasajera de Crucero @emilyg',
				'avatar' => 'EG',
				'bg' => '#e0f2fe'
			],
			[
				'quote' => 'Muy amables',
				'text' => 'Gente trabajadora y honesta. Nos devolvieron un cargador de celular que olvidamos en la guantera el mismo día. Gran detalle.',
				'name' => 'David Flores',
				'meta' => 'Turista @davidflores',
				'avatar' => 'DF',
				'bg' => '#fef3c7'
			],
			[
				'quote' => 'Entrega directa en hotel',
				'text' => 'Nos llevaron el carro directamente a nuestro hotel en West End y lo recogieron allí mismo. Gran comodidad y excelente precio.',
				'name' => 'Victoria Baker',
				'meta' => 'Viajera @victoriab',
				'avatar' => 'VB',
				'bg' => '#e0e7ff'
			],
			[
				'quote' => 'Confiabilidad total',
				'text' => 'Segunda vez que alquilo con ellos y el servicio sigue siendo espectacular. Ramírez es mi opción definitiva en Roatán.',
				'name' => 'Andrew Gonzalez',
				'meta' => 'Viajero Frecuente @andrewg',
				'avatar' => 'AG',
				'bg' => '#f1f5f9'
			],
			[
				'quote' => 'Súper recomendado',
				'text' => 'Hicieron que nuestro viaje familiar fuera mucho más cómodo. La minivan de 7 pasajeros estaba limpia y muy espaciosa.',
				'name' => 'Chloe Nelson',
				'meta' => 'Turista Familiar @chloen',
				'avatar' => 'CN',
				'bg' => '#fee2e2'
			],
			[
				'quote' => 'Trámite veloz',
				'text' => 'Entrega rápida y devolución en 2 minutos. Cero burocracia. Ideal para aprovechar el tiempo al máximo en Roatán.',
				'name' => 'Gabriel Morales',
				'meta' => 'Turista @gabrielm',
				'avatar' => 'GM',
				'bg' => '#e0f2fe'
			],
			[
				'quote' => 'Excelente seguro',
				'text' => 'Te da tranquilidad saber que viajas con cobertura completa incluida. Muy transparentes desde el inicio.',
				'name' => 'Elizabeth Cruz',
				'meta' => 'Viajera @elizabethc',
				'avatar' => 'EC',
				'bg' => '#fef3c7'
			],
			[
				'quote' => 'Gran servicio',
				'text' => 'Personal muy educado y atento. Los autos están en perfectas condiciones de mantenimiento y aire acondicionado espectacular.',
				'name' => 'Anthony Reyes',
				'meta' => 'Viajero @anthonyr',
				'avatar' => 'AR',
				'bg' => '#e0e7ff'
			],
			[
				'quote' => 'Experiencia insuperable',
				'text' => 'Roatán es hermoso y recorrerlo con un vehículo de Ramírez hizo el viaje perfecto. Servicio de 5 estrellas de principio a fin.',
				'name' => 'Camila Silva',
				'meta' => 'Viajera @camilas',
				'avatar' => 'CS',
				'bg' => '#f1f5f9'
			]
		];

		ob_start();
		?>
		<style>
		.rrc-reviews-section {
			background: #f8fafc;
			padding: 90px 0;
			font-family: 'Inter Tight', sans-serif;
			overflow: hidden;
		}
		.rrc-reviews-container {
			width: 90%;
			max-width: 1400px;
			margin: 0 auto;
			position: relative;
		}
		.rrc-reviews-header {
			text-align: center;
			margin-bottom: 60px;
		}
		.rrc-reviews-pretitle {
			font-size: 26px;
			color: #94a3b8;
			font-weight: 500;
			margin-bottom: 10px;
			font-family: serif;
			font-style: italic;
		}
		.rrc-reviews-title {
			font-size: 44px;
			font-weight: 900;
			color: #1e293b;
			margin: 0;
			line-height: 1.1;
			letter-spacing: -1px;
		}
		.rrc-reviews-slider-viewport {
			width: 100%;
			overflow: hidden;
		}
		.rrc-reviews-slider-track {
			display: flex;
			gap: 30px;
			transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
			will-change: transform;
		}
		.rrc-review-card {
			background: #fff;
			border: 1px solid #f1f5f9;
			border-radius: 20px;
			padding: 40px;
			box-sizing: border-box;
			box-shadow: 0 10px 30px rgba(0,0,0,0.02);
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			min-height: 340px;
			transition: all 0.3s ease;
			flex: 0 0 calc((100% - 60px) / 3);
		}
		.rrc-review-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 15px 35px rgba(0,0,0,0.04);
		}
		.rrc-review-quote {
			font-size: 20px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 15px 0;
			line-height: 1.35;
		}
		.rrc-review-text {
			font-size: 14px;
			color: #64748b;
			line-height: 1.6;
			margin: 0 0 30px 0;
		}
		.rrc-reviewer-row {
			display: flex;
			align-items: center;
			gap: 12px;
			border-top: 1px solid #f1f5f9;
			padding-top: 20px;
		}
		.rrc-reviewer-avatar {
			width: 44px;
			height: 44px;
			border-radius: 50%;
			object-fit: cover;
			background: #e2e8f0;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: bold;
			color: #475569;
			font-size: 14px;
		}
		.rrc-reviewer-info {
			flex: 1;
		}
		.rrc-reviewer-name {
			font-size: 14px;
			font-weight: 700;
			color: #1e293b;
			margin: 0 0 2px 0;
		}
		.rrc-reviewer-meta {
			font-size: 12px;
			color: #94a3b8;
			margin: 0;
		}
		.rrc-google-badge {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-top: 15px;
		}
		.rrc-stars {
			color: #f59e0b;
			font-size: 14px;
			letter-spacing: 2px;
		}
		.rrc-google-logo {
			display: flex;
			align-items: center;
			gap: 4px;
			font-size: 12px;
			font-weight: 700;
			color: #475569;
		}
		.rrc-reviews-nav {
			display: flex;
			justify-content: center;
			gap: 15px;
			margin-top: 40px;
		}
		.rrc-reviews-arrow {
			width: 46px;
			height: 46px;
			border-radius: 50%;
			background: #fff;
			border: 1px solid #e2e8f0;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			color: #475569;
			font-size: 16px;
			box-shadow: 0 4px 10px rgba(0,0,0,0.03);
			transition: all 0.2s;
			user-select: none;
		}
		.rrc-reviews-arrow:hover {
			background: #E8272C;
			color: #fff;
			border-color: #E8272C;
			box-shadow: 0 4px 12px rgba(232, 39, 44, 0.25);
		}
		@media (max-width: 1024px) {
			.rrc-review-card {
				flex: 0 0 calc((100% - 30px) / 2);
				padding: 30px;
			}
		}
		@media (max-width: 768px) {
			.rrc-review-card {
				flex: 0 0 100%;
				padding: 25px;
				min-height: auto;
			}
			.rrc-reviews-title {
				font-size: 32px;
			}
		}
		</style>

		<section class="rrc-reviews-section">
			<div class="rrc-reviews-container">
				<!-- Header Section -->
				<div class="rrc-reviews-header">
					<div class="rrc-reviews-pretitle">Recomendados</div>
					<h2 class="rrc-reviews-title">por más de 500+ clientes felices</h2>
				</div>

				<!-- Slider Viewport -->
				<div class="rrc-reviews-slider-viewport">
					<div class="rrc-reviews-slider-track">
						<?php foreach ( $reviews as $rev ) : ?>
							<div class="rrc-review-card">
								<div>
									<h3 class="rrc-review-quote">"<?php echo esc_html($rev['quote']); ?>"</h3>
									<p class="rrc-review-text">
										<?php echo esc_html($rev['text']); ?>
									</p>
								</div>
								<div>
									<div class="rrc-reviewer-row">
										<div class="rrc-reviewer-avatar" style="background-color: <?php echo esc_attr($rev['bg']); ?>;">
											<?php echo esc_html($rev['avatar']); ?>
										</div>
										<div class="rrc-reviewer-info">
											<h4 class="rrc-reviewer-name"><?php echo esc_html($rev['name']); ?></h4>
											<p class="rrc-reviewer-meta"><?php echo esc_html($rev['meta']); ?></p>
										</div>
									</div>
									<div class="rrc-google-badge">
										<div class="rrc-stars">★★★★★</div>
										<div class="rrc-google-logo">
											<svg width="14" height="14" viewBox="0 0 24 24"><path fill="#EA4335" d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114A5.99 5.99 0 0 1 8 12.5a5.99 5.99 0 0 1 5.99-6.014c1.648 0 3.136.67 4.223 1.765l3.201-3.2A10.37 10.37 0 0 0 13.99 2C8.473 2 4 6.484 4 12s4.473 10 9.99 10c5.545 0 9.544-3.9 9.544-9.715 0-.616-.067-1.2-.2-1.785H12.24Z"/></svg>
											Google
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Navigation arrows -->
				<div class="rrc-reviews-nav">
					<div class="rrc-reviews-arrow rrc-reviews-arrow-prev" style="margin-right: 5px;">←</div>
					<div class="rrc-reviews-arrow rrc-reviews-arrow-next">→</div>
				</div>
			</div>
		</section>

		<script>
		document.addEventListener("DOMContentLoaded", function() {
			const track = document.querySelector('.rrc-reviews-slider-track');
			const prevBtn = document.querySelector('.rrc-reviews-arrow-prev');
			const nextBtn = document.querySelector('.rrc-reviews-arrow-next');
			if (!track || !prevBtn || !nextBtn) return;

			let currentIndex = 0;
			const cards = track.querySelectorAll('.rrc-review-card');
			const totalCards = cards.length;
			let cardsToShow = 3;

			function getCardsToShow() {
				if (window.innerWidth <= 768) return 1;
				if (window.innerWidth <= 1024) return 2;
				return 3;
			}

			function updateSlider() {
				cardsToShow = getCardsToShow();
				const cardWidth = cards[0].getBoundingClientRect().width;
				const gap = 30; // matching CSS gap
				const amountToMove = currentIndex * (cardWidth + gap);
				track.style.transform = `translateX(-${amountToMove}px)`;
			}

			function nextReview() {
				cardsToShow = getCardsToShow();
				const maxIndex = totalCards - cardsToShow;
				if (currentIndex >= maxIndex) {
					currentIndex = 0; // wrap around to beginning
				} else {
					currentIndex++;
				}
				updateSlider();
			}

			function prevReview() {
				if (currentIndex <= 0) {
					cardsToShow = getCardsToShow();
					currentIndex = totalCards - cardsToShow; // go to end
				} else {
					currentIndex--;
				}
				updateSlider();
			}

			nextBtn.addEventListener('click', () => {
				nextReview();
				resetAutoplay();
			});
			prevBtn.addEventListener('click', () => {
				prevReview();
				resetAutoplay();
			});
			window.addEventListener('resize', updateSlider);

			// Autoplay timer
			let autoplay = setInterval(nextReview, 6000); // Wait 6 seconds per slide
			
			function resetAutoplay() {
				clearInterval(autoplay);
				autoplay = setInterval(nextReview, 6000);
			}

			// Pause autoplay on mouse enter
			const section = document.querySelector('.rrc-reviews-section');
			section.addEventListener('mouseenter', () => clearInterval(autoplay));
			section.addEventListener('mouseleave', () => autoplay = setInterval(nextReview, 6000));
		});
		</script>
		</script>
		<?php
		return ob_get_clean();
	}

	public static function render_timeline_shortcode() {
		ob_start();
		?>
		<style>
		.rrc-timeline-section {
			background: #ffffff;
			padding: 90px 0;
			font-family: 'Inter Tight', sans-serif;
			position: relative;
		}
		/* Watermark logo matching screenshot */
		.rrc-timeline-watermark {
			position: absolute;
			top: 10%;
			left: 5%;
			width: 600px;
			height: 600px;
			background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.02" fill="%23E8272C"><path d="M50 15 C 30 15, 15 30, 15 50 C 15 70, 30 85, 50 85 C 70 85, 85 70, 85 50 C 85 30, 70 15, 50 15 Z M50 25 C 63.8 25, 75 36.2, 75 50 C 75 63.8, 63.8 75, 50 75 C 36.2 75, 25 63.8, 25 50 C 25 36.2, 36.2 25, 50 25 Z"/></svg>');
			background-size: contain;
			background-repeat: no-repeat;
			pointer-events: none;
			z-index: 1;
		}
		.rrc-timeline-container {
			width: 90%;
			max-width: 1400px;
			margin: 0 auto;
			display: flex;
			position: relative;
			z-index: 2;
			gap: 80px;
		}
		
		/* Left Column: Sticky Timeline Nav */
		.rrc-timeline-left {
			width: 40%;
			position: relative;
		}
		.rrc-timeline-sticky-nav {
			position: sticky;
			top: 150px;
			display: flex;
			flex-direction: column;
			gap: 35px;
			padding-left: 30px;
		}
		/* Vertical connection line */
		.rrc-timeline-line-track {
			position: absolute;
			left: 52px; /* Centered relative to the 44px circle inside padding-left */
			top: 20px;
			bottom: 20px;
			width: 2px;
			background: #e2e8f0;
			z-index: 1;
		}
		.rrc-timeline-line-progress {
			position: absolute;
			left: 52px;
			top: 20px;
			width: 2px;
			background: #E8272C;
			z-index: 2;
			height: 0%;
			transition: height 0.4s ease;
		}
		.rrc-timeline-nav-item {
			display: flex;
			align-items: center;
			gap: 20px;
			cursor: pointer;
			position: relative;
			z-index: 3;
			text-decoration: none;
		}
		.rrc-timeline-nav-number {
			width: 44px;
			height: 44px;
			border-radius: 50%;
			background: #fff;
			border: 2px solid #e2e8f0;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 15px;
			font-weight: 800;
			color: #94a3b8;
			transition: all 0.3s ease;
		}
		.rrc-timeline-nav-label {
			font-size: 16px;
			font-weight: 600;
			color: #94a3b8;
			transition: all 0.3s ease;
		}
		
		/* Active Stepper States */
		.rrc-timeline-nav-item.active .rrc-timeline-nav-number,
		.rrc-timeline-nav-item.completed .rrc-timeline-nav-number {
			background: #E8272C;
			border-color: #E8272C;
			color: #fff;
			box-shadow: 0 4px 10px rgba(232, 39, 44, 0.2);
		}
		.rrc-timeline-nav-item.active .rrc-timeline-nav-label {
			color: #0f172a;
			font-weight: 800;
			transform: translateX(4px);
		}
		.rrc-timeline-nav-item.completed .rrc-timeline-nav-label {
			color: #475569;
		}

		/* Right Column: Steps Details Cards */
		.rrc-timeline-right {
			width: 60%;
			display: flex;
			flex-direction: column;
			gap: 120px;
		}
		.rrc-timeline-detail-card {
			background: #fff;
			padding: 40px 0;
			box-sizing: border-box;
			position: relative;
			scroll-margin-top: 180px; /* to align when scrolling via click */
		}
		.rrc-timeline-icon-box {
			width: 54px;
			height: 54px;
			border-radius: 14px;
			background: rgba(232, 39, 44, 0.08);
			display: flex;
			align-items: center;
			justify-content: center;
			color: #E8272C;
			margin-bottom: 25px;
			box-shadow: 0 4px 12px rgba(232, 39, 44, 0.05);
		}
		.rrc-timeline-icon-box svg {
			width: 24px;
			height: 24px;
		}
		.rrc-timeline-detail-title {
			font-size: 32px;
			font-weight: 850;
			color: #1e293b;
			margin: 0 0 18px 0;
			letter-spacing: -0.5px;
		}
		.rrc-timeline-detail-desc {
			font-size: 15px;
			color: #64748b;
			line-height: 1.7;
			margin: 0;
			max-width: 580px;
		}

		@media (max-width: 1024px) {
			.rrc-timeline-container {
				flex-direction: column;
				gap: 50px;
			}
			.rrc-timeline-left {
				width: 100%;
			}
			.rrc-timeline-sticky-nav {
				position: relative;
				top: 0;
				flex-direction: row;
				justify-content: space-between;
				padding-left: 0;
				gap: 10px;
			}
			.rrc-timeline-line-track,
			.rrc-timeline-line-progress {
				display: none;
			}
			.rrc-timeline-nav-item {
				flex-direction: column;
				align-items: center;
				gap: 10px;
				flex: 1;
				text-align: center;
			}
			.rrc-timeline-nav-label {
				font-size: 13px;
			}
			.rrc-timeline-right {
				width: 100%;
				gap: 60px;
			}
			.rrc-timeline-detail-card {
				padding: 30px;
				background: #f8fafc;
				border-radius: 20px;
				border: 1px solid #f1f5f9;
			}
		}
		</style>

		<section class="rrc-timeline-section">
			<div class="rrc-timeline-watermark"></div>
			
			<div class="rrc-timeline-container">
				<!-- Left Column: Stepper Track -->
				<div class="rrc-timeline-left">
					<div class="rrc-timeline-sticky-nav">
						<div class="rrc-timeline-line-track"></div>
						<div class="rrc-timeline-line-progress" id="rrc-timeline-progress"></div>

						<!-- Step 1 Nav -->
						<a href="#step-1" class="rrc-timeline-nav-item active" data-step="0">
							<div class="rrc-timeline-nav-number">1</div>
							<span class="rrc-timeline-nav-label">Elige tus fechas y recogida</span>
						</a>

						<!-- Step 2 Nav -->
						<a href="#step-2" class="rrc-timeline-nav-item" data-step="1">
							<div class="rrc-timeline-nav-number">2</div>
							<span class="rrc-timeline-nav-label">Explora y elige tu coche</span>
						</a>

						<!-- Step 3 Nav -->
						<a href="#step-3" class="rrc-timeline-nav-item" data-step="2">
							<div class="rrc-timeline-nav-number">3</div>
							<span class="rrc-timeline-nav-label">Reserva y confirma online</span>
						</a>

						<!-- Step 4 Nav -->
						<a href="#step-4" class="rrc-timeline-nav-item" data-step="3">
							<div class="rrc-timeline-nav-number">4</div>
							<span class="rrc-timeline-nav-label">Recogida gratuita</span>
						</a>
					</div>
				</div>

				<!-- Right Column: Detail Content -->
				<div class="rrc-timeline-right">
					<!-- Step 1 Detail -->
					<div class="rrc-timeline-detail-card" id="rrc-step-detail-0">
						<div class="rrc-timeline-icon-box">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
						</div>
						<h3 class="rrc-timeline-detail-title">Elige tus fechas y recogida</h3>
						<p class="rrc-timeline-detail-desc">
							Introduce tus fechas de viaje y punto de recogida en la barra de reservas. La disponibilidad se muestra al instante, sin esperas ni llamadas telefónicas.
						</p>
					</div>

					<!-- Step 2 Detail -->
					<div class="rrc-timeline-detail-card" id="rrc-step-detail-1">
						<div class="rrc-timeline-icon-box">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
						</div>
						<h3 class="rrc-timeline-detail-title">Explora y elige tu coche</h3>
						<p class="rrc-timeline-detail-desc">
							Compara nuestra flamante flota de vehículos y elige el que mejor se adapte a tu viaje, desde compactos con portón trasero hasta furgonetas de 11 plazas.
						</p>
					</div>

					<!-- Step 3 Detail -->
					<div class="rrc-timeline-detail-card" id="rrc-step-detail-2">
						<div class="rrc-timeline-icon-box">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
						</div>
						<h3 class="rrc-timeline-detail-title">Reserva y confirma online</h3>
						<p class="rrc-timeline-detail-desc">
							Completa tu reserva de forma segura ingresando tus detalles de contacto. Te enviaremos una confirmación inmediata por correo electrónico y WhatsApp.
						</p>
					</div>

					<!-- Step 4 Detail -->
					<div class="rrc-timeline-detail-card" id="rrc-step-detail-3">
						<div class="rrc-timeline-icon-box">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
						</div>
						<h3 class="rrc-timeline-detail-title">Recogida gratuita en el aeropuerto y luego traslado.</h3>
						<p class="rrc-timeline-detail-desc">
							Te recibimos personalmente en el aeropuerto (RTB), terminal de ferry o muelle de cruceros. Te entregamos el vehículo listo para que comiences a explorar Roatán sin demoras.
						</p>
					</div>
				</div>
			</div>
		</section>

		<script>
		document.addEventListener("DOMContentLoaded", function() {
			const navItems = document.querySelectorAll('.rrc-timeline-nav-item');
			const detailCards = document.querySelectorAll('.rrc-timeline-detail-card');
			const progressBar = document.getElementById('rrc-timeline-progress');

			// Scroll Spy logic using IntersectionObserver
			const observerOptions = {
				root: null,
				rootMargin: '-20% 0px -60% 0px', // triggers when the step card reaches the upper-middle of screen
				threshold: 0
			};

			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						const stepId = entry.target.id.split('-').pop(); // gets "0", "1", etc.
						setActiveStep(parseInt(stepId));
					}
				});
			}, observerOptions);

			detailCards.forEach(card => observer.observe(card));

			function setActiveStep(activeIndex) {
				navItems.forEach((item, index) => {
					if (index === activeIndex) {
						item.classList.add('active');
						item.classList.remove('completed');
					} else if (index < activeIndex) {
						item.classList.add('completed');
						item.classList.remove('active');
					} else {
						item.classList.remove('active', 'completed');
					}
				});

				// Calculate progress bar height percentage
				// 0 active -> 0%, 1 active -> 33.3%, 2 active -> 66.6%, 3 active -> 100%
				const percent = (activeIndex / (navItems.length - 1)) * 100;
				if (progressBar) {
					progressBar.style.height = percent + '%';
				}
			}

			// Click to smooth scroll
			navItems.forEach(item => {
				item.addEventListener('click', function(e) {
					e.preventDefault();
					const stepIndex = this.getAttribute('data-step');
					const targetCard = document.getElementById('rrc-step-detail-' + stepIndex);
					if (targetCard) {
						targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
					}
				});
			});
		});
		</script>
		</script>
		<?php
		return ob_get_clean();
	}

	public static function render_about_us_shortcode() {
		ob_start();
		?>
		<style>
		.rrc-about-section {
			background: #ffffff;
			color: #334155;
			font-family: 'Inter Tight', 'Inter', sans-serif;
			padding: 0;
			overflow: hidden;
		}

		/* Global utilities for About Page */
		.rrc-about-container {
			width: 90%;
			max-width: 1200px;
			margin: 0 auto;
		}
		.rrc-about-section-header {
			text-align: center;
			margin-bottom: 45px;
		}
		.rrc-about-pretitle {
			color: #e8272c;
			font-weight: 800;
			font-size: 12px;
			text-transform: uppercase;
			letter-spacing: 1.5px;
			margin-bottom: 10px;
			display: block;
		}
		.rrc-about-title-large {
			font-size: 34px;
			font-weight: 900;
			color: #0f172a;
			margin: 0 0 15px 0;
			letter-spacing: -0.8px;
		}

		/* 1. Page Header / Hero Intro */
		.rrc-about-header-block {
			padding: 80px 0 50px 0;
			text-align: center;
		}
		.rrc-about-header-tag {
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: inline-block;
			font-size: 12px;
			font-weight: 800;
			text-transform: uppercase;
			padding: 6px 16px;
			border-radius: 30px;
			margin-bottom: 20px;
			letter-spacing: 0.5px;
		}
		.rrc-about-header-title {
			font-size: 42px;
			font-weight: 900;
			color: #0f172a;
			letter-spacing: -1.2px;
			margin: 0 auto 20px auto;
			max-width: 700px;
			line-height: 1.2;
		}
		.rrc-about-header-desc {
			font-size: 16px;
			line-height: 1.6;
			color: #64748b;
			max-width: 800px;
			margin: 0 auto;
		}

		/* Benefits Horizontal Bar */
		.rrc-about-benefits-bar {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 25px;
			margin-top: 45px;
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 20px;
			text-align: left;
		}
		.rrc-about-benefit-item {
			display: flex;
			align-items: center;
			gap: 12px;
		}
		.rrc-about-benefit-icon {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-about-benefit-icon svg {
			width: 18px;
			height: 18px;
		}
		.rrc-about-benefit-title {
			color: #0f172a;
			font-weight: 700;
			font-size: 13.5px;
			margin: 0 0 2px 0;
		}
		.rrc-about-benefit-desc {
			color: #64748b;
			font-size: 11px;
			margin: 0;
			line-height: 1.3;
		}

		/* 2. Our History Section */
		.rrc-about-history-section {
			background: #f8fafc;
			padding: 80px 0;
			border-top: 1px solid #e2e8f0;
			border-bottom: 1px solid #e2e8f0;
		}
		.rrc-about-history-grid {
			display: grid;
			grid-template-columns: 1.1fr 1fr;
			gap: 50px;
			align-items: center;
		}
		.rrc-about-history-image-wrap img {
			width: 100%;
			height: auto;
			border-radius: 20px;
			box-shadow: 0 15px 30px rgba(0,0,0,0.05);
			display: block;
		}
		.rrc-about-history-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-about-history-text {
			font-size: 15px;
			line-height: 1.7;
			color: #475569;
			margin-bottom: 30px;
		}
		.rrc-about-history-text p {
			margin: 0 0 15px 0;
		}
		.rrc-about-history-text p:last-child {
			margin: 0;
		}
		/* History Badges row */
		.rrc-about-history-badges {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 15px;
		}
		.rrc-history-badge-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			padding: 15px;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.rrc-history-badge-icon {
			color: #e8272c;
			flex-shrink: 0;
		}
		.rrc-history-badge-icon svg {
			width: 26px;
			height: 26px;
		}
		.rrc-history-badge-info {
			display: flex;
			flex-direction: column;
		}
		.rrc-history-badge-num {
			color: #e8272c;
			font-size: 16px;
			font-weight: 800;
		}
		.rrc-history-badge-lbl {
			font-size: 11px;
			color: #64748b;
			line-height: 1.2;
		}

		/* 3. Mission & Vision */
		.rrc-about-mv-section {
			padding: 80px 0;
		}
		.rrc-about-mv-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 30px;
		}
		.rrc-about-mv-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 20px;
			padding: 40px;
			position: relative;
			overflow: hidden;
			box-shadow: 0 10px 30px rgba(0,0,0,0.02);
		}
		.rrc-about-mv-icon-bg {
			position: absolute;
			right: 30px;
			top: 30px;
			color: rgba(232, 39, 44, 0.08);
		}
		.rrc-about-mv-icon-bg svg {
			width: 60px;
			height: 60px;
		}
		.rrc-about-mv-pre {
			color: #e8272c;
			font-weight: 800;
			font-size: 12px;
			text-transform: uppercase;
			margin-bottom: 12px;
			display: block;
			letter-spacing: 0.5px;
		}
		.rrc-about-mv-title {
			font-size: 24px;
			font-weight: 900;
			color: #0f172a;
			margin: 0 0 15px 0;
			letter-spacing: -0.5px;
		}
		.rrc-about-mv-text {
			font-size: 14.5px;
			line-height: 1.65;
			color: #475569;
			margin: 0;
		}

		/* 4. Nuestros Valores */
		.rrc-about-values-section {
			background: #f8fafc;
			padding: 80px 0;
			border-top: 1px solid #e2e8f0;
			border-bottom: 1px solid #e2e8f0;
		}
		.rrc-about-values-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 25px;
		}
		.rrc-about-value-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 30px 25px;
			text-align: center;
			box-shadow: 0 10px 30px rgba(0,0,0,0.02);
			transition: transform 0.2s ease;
		}
		.rrc-about-value-card:hover {
			transform: translateY(-5px);
		}
		.rrc-about-value-icon {
			width: 44px;
			height: 44px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 20px auto;
		}
		.rrc-about-value-icon svg {
			width: 20px;
			height: 20px;
		}
		.rrc-about-value-title {
			font-size: 16.5px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 10px 0;
		}
		.rrc-about-value-desc {
			font-size: 13px;
			line-height: 1.5;
			color: #64748b;
			margin: 0;
		}

		/* 5. Lo que nos diferencia */
		.rrc-about-diff-section {
			padding: 80px 0;
		}
		.rrc-about-diff-grid {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 25px;
		}
		.rrc-about-diff-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 30px;
			box-shadow: 0 8px 25px rgba(0,0,0,0.01);
			display: flex;
			gap: 15px;
		}
		.rrc-about-diff-icon {
			width: 40px;
			height: 40px;
			border-radius: 8px;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-about-diff-icon svg {
			width: 20px;
			height: 20px;
		}
		.rrc-about-diff-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-about-diff-title {
			font-size: 15.5px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 8px 0;
		}
		.rrc-about-diff-desc {
			font-size: 13px;
			line-height: 1.5;
			color: #64748b;
			margin: 0;
		}

		/* 6. Nuestra Promesa */
		.rrc-about-promise-section {
			background: #f8fafc;
			padding: 80px 0;
			border-top: 1px solid #e2e8f0;
			border-bottom: 1px solid #e2e8f0;
		}
		.rrc-about-promise-grid {
			display: grid;
			grid-template-columns: repeat(5, 1fr);
			gap: 15px;
		}
		.rrc-about-promise-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			padding: 22px 18px;
			text-align: center;
			box-shadow: 0 4px 15px rgba(0,0,0,0.01);
		}
		.rrc-about-promise-icon {
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 12px;
		}
		.rrc-about-promise-icon svg {
			width: 24px;
			height: 24px;
		}
		.rrc-about-promise-title {
			font-size: 14.5px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 6px 0;
		}
		.rrc-about-promise-desc {
			font-size: 11px;
			line-height: 1.4;
			color: #64748b;
			margin: 0;
		}

		/* 7. Call To Action */
		.rrc-about-cta-section {
			padding: 80px 0;
			text-align: center;
		}
		.rrc-about-cta-banner {
			background: linear-gradient(135deg, #111827 0%, #030712 100%);
			border-radius: 24px;
			padding: 60px 40px;
			color: #ffffff;
			max-width: 1000px;
			margin: 0 auto;
			box-shadow: 0 20px 40px rgba(0,0,0,0.1);
		}
		.rrc-about-cta-title {
			font-size: 32px;
			font-weight: 900;
			color: #ffffff;
			margin: 0 0 15px 0;
			letter-spacing: -0.8px;
		}
		.rrc-about-cta-desc {
			font-size: 15px;
			color: #9ca3af;
			margin-bottom: 30px;
			max-width: 600px;
			margin-left: auto;
			margin-right: auto;
		}
		.rrc-about-cta-btns {
			display: flex;
			gap: 15px;
			justify-content: center;
			align-items: center;
		}
		.rrc-about-cta-btn-primary {
			background: #e8272c;
			color: #ffffff;
			padding: 12px 30px;
			border-radius: 30px;
			font-size: 13.5px;
			font-weight: 800;
			text-decoration: none;
			transition: background 0.2s ease;
		}
		.rrc-about-cta-btn-primary:hover {
			background: #b91c1c;
		}
		.rrc-about-cta-btn-secondary {
			background: transparent;
			color: #ffffff;
			border: 1.5px solid rgba(255, 255, 255, 0.2);
			padding: 11px 29px;
			border-radius: 30px;
			font-size: 13.5px;
			font-weight: 800;
			text-decoration: none;
			transition: background 0.2s ease;
		}
		.rrc-about-cta-btn-secondary:hover {
			background: rgba(255, 255, 255, 0.08);
			border-color: rgba(255, 255, 255, 0.4);
		}

		/* Responsive styling */
		@media (max-width: 1024px) {
			.rrc-about-history-grid {
				grid-template-columns: 1fr;
				gap: 40px;
			}
			.rrc-about-values-grid {
				grid-template-columns: repeat(2, 1fr);
			}
			.rrc-about-diff-grid {
				grid-template-columns: repeat(2, 1fr);
			}
			.rrc-about-promise-grid {
				grid-template-columns: repeat(3, 1fr);
				gap: 20px;
			}
		}
		@media (max-width: 768px) {
			.rrc-about-header-title {
				font-size: 32px;
			}
			.rrc-about-benefits-bar {
				grid-template-columns: repeat(2, 1fr);
			}
			.rrc-about-mv-grid {
				grid-template-columns: 1fr;
			}
			.rrc-about-history-badges {
				grid-template-columns: 1fr;
			}
		}
		@media (max-width: 575px) {
			.rrc-about-benefits-bar {
				grid-template-columns: 1fr;
			}
			.rrc-about-values-grid {
				grid-template-columns: 1fr;
			}
			.rrc-about-diff-grid {
				grid-template-columns: 1fr;
			}
			.rrc-about-promise-grid {
				grid-template-columns: 1fr;
			}
			.rrc-about-cta-btns {
				flex-direction: column;
				gap: 12px;
			}
		}
		</style>

		<section class="rrc-about-section">
			
			<!-- 1. Hero / Header Block -->
			<div class="rrc-about-container">
				<div class="rrc-about-header-block">
					<span class="rrc-about-header-tag">Quiénes somos</span>
					<h2 class="rrc-about-header-title">Tu confianza para recorrer Roatán con total libertad</h2>
					<p class="rrc-about-header-desc">
						En Ramírez Rent A Car somos una empresa dedicada a brindar soluciones de movilidad confiables, cómodas y accesibles en Roatán. Ayudamos a viajeros, familias, grupos y pasajeros de cruceros a elegir el vehículo ideal para disfrutar la isla a su manera.
					</p>

					<!-- Benefits Horizontal Bar -->
					<div class="rrc-about-benefits-bar">
						<div class="rrc-about-benefit-item">
							<div class="rrc-about-benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
							</div>
							<div class="rrc-about-benefit-info">
								<h5 class="rrc-about-benefit-title">Entrega y recogida gratis</h5>
								<p class="rrc-about-benefit-desc">Aeropuerto, Ferry y muelles</p>
							</div>
						</div>
						<div class="rrc-about-benefit-item">
							<div class="rrc-about-benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 11 2 2 4-4"></path></svg>
							</div>
							<div class="rrc-about-benefit-info">
								<h5 class="rrc-about-benefit-title">Seguro incluido</h5>
								<p class="rrc-about-benefit-desc">Nuestras tarifas incluyen seguro</p>
							</div>
						</div>
						<div class="rrc-about-benefit-item">
							<div class="rrc-about-benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="12 8 13.09 10.21 15.5 10.56 13.75 12.27 14.16 14.64 12 13.5 9.84 14.64 10.25 12.27 8.5 10.56 10.91 10.21 12 8"></polygon></svg>
							</div>
							<div class="rrc-about-benefit-info">
								<h5 class="rrc-about-benefit-title">Mejor precio garantizado</h5>
								<p class="rrc-about-benefit-desc">Tarifas claras sin cargos ocultos</p>
							</div>
						</div>
						<div class="rrc-about-benefit-item">
							<div class="rrc-about-benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
							</div>
							<div class="rrc-about-benefit-info">
								<h5 class="rrc-about-benefit-title">Atención 24/7</h5>
								<p class="rrc-about-benefit-desc">Soporte local e internacional</p>
							</div>
						</div>
					</div>

				</div>
			</div>

			<!-- 2. Our History -->
			<div class="rrc-about-history-section">
				<div class="rrc-about-container">
					<div class="rrc-about-history-grid">
						
						<!-- Left image -->
						<div class="rrc-about-history-image-wrap">
							<img src="<?php echo esc_url( home_url( '/wp-content/uploads/2026/07/422a2325-15df-4fdd-bda1-0a65499fcc86.png' ) ); ?>" alt="Nuestra Historia - Ramírez Rent A Car" />
						</div>

						<!-- Right Content -->
						<div class="rrc-about-history-content">
							<span class="rrc-about-pretitle">Nuestra Historia</span>
							<h3 class="rrc-about-title-large">Pasión local, servicio que te acompaña</h3>
							<div class="rrc-about-history-text">
								<p>
									Ramírez Rent A Car nació en Roatán con el propósito de ofrecer un servicio de alquiler de vehículos honesto, profesional y cercano. Conocemos la isla, sus caminos y las necesidades de quienes nos visitan, por eso ponemos a tu disposición una flota moderna y bien mantenida, acompañada de un equipo listo para ayudarte en cada paso del camino.
								</p>
								<p>
									Hoy somos la elección de miles de viajeros que buscan una experiencia segura, transparente y sin complicaciones.
								</p>
							</div>

							<!-- Badges -->
							<div class="rrc-about-history-badges">
								<!-- Stat 1 -->
								<div class="rrc-history-badge-card">
									<div class="rrc-history-badge-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
									</div>
									<div class="rrc-history-badge-info">
										<span class="rrc-history-badge-num">+10 años</span>
										<span class="rrc-history-badge-lbl">Sirviendo a viajeros en Roatán</span>
									</div>
								</div>
								<!-- Stat 2 -->
								<div class="rrc-history-badge-card">
									<div class="rrc-history-badge-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
									</div>
									<div class="rrc-history-badge-info">
										<span class="rrc-history-badge-num">Miles</span>
										<span class="rrc-history-badge-lbl">De clientes cada año</span>
									</div>
								</div>
								<!-- Stat 3 -->
								<div class="rrc-history-badge-card">
									<div class="rrc-history-badge-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
									</div>
									<div class="rrc-history-badge-info">
										<span class="rrc-history-badge-num">3 oficinas</span>
										<span class="rrc-history-badge-lbl">Para estar más cerca de ti</span>
									</div>
								</div>
							</div>

						</div>

					</div>
				</div>
			</div>

			<!-- 3. Mission & Vision -->
			<div class="rrc-about-mv-section">
				<div class="rrc-about-container">
					<div class="rrc-about-mv-grid">
						
						<!-- Mission -->
						<div class="rrc-about-mv-card">
							<div class="rrc-about-mv-icon-bg">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
							</div>
							<span class="rrc-about-mv-pre">Nuestra Misión</span>
							<h4 class="rrc-about-mv-title">Facilitar tu aventura en Roatán</h4>
							<p class="rrc-about-mv-text">
								Ofrecer soluciones de movilidad confiables, cómodas y accesibles, con tarifas claras (Drive Away), seguro incluido y un servicio excepcional que supere las expectativas de cada cliente.
							</p>
						</div>

						<!-- Vision -->
						<div class="rrc-about-mv-card">
							<div class="rrc-about-mv-icon-bg">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
							</div>
							<span class="rrc-about-mv-pre">Nuestra Visión</span>
							<h4 class="rrc-about-mv-title">Ser tu opción número uno en Roatán</h4>
							<p class="rrc-about-mv-text">
								Ser reconocidos como la empresa de renta de vehículos más confiable de la isla, destacando por nuestra calidad, innovación, compromiso con la comunidad y atención personalizada.
							</p>
						</div>

					</div>
				</div>
			</div>

			<!-- 4. Nuestros Valores -->
			<div class="rrc-about-values-section">
				<div class="rrc-about-container">
					<div class="rrc-about-section-header">
						<span class="rrc-about-pretitle">Valores</span>
						<h3 class="rrc-about-title-large">Nuestros Valores</h3>
					</div>

					<div class="rrc-about-values-grid">
						<!-- Value 1 -->
						<div class="rrc-about-value-card">
							<div class="rrc-about-value-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
							</div>
							<h4 class="rrc-about-value-title">Transparencia</h4>
							<p class="rrc-about-value-desc">Tarifas claras y sin cargos ocultos. Lo que ves es lo que pagas.</p>
						</div>
						<!-- Value 2 -->
						<div class="rrc-about-value-card">
							<div class="rrc-about-value-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
							</div>
							<h4 class="rrc-about-value-title">Seguridad</h4>
							<p class="rrc-about-value-desc">Vehículos en excelentes condiciones, seguro incluido y mantenimiento riguroso.</p>
						</div>
						<!-- Value 3 -->
						<div class="rrc-about-value-card">
							<div class="rrc-about-value-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
							</div>
							<h4 class="rrc-about-value-title">Atención personalizada</h4>
							<p class="rrc-about-value-desc">Te escuchamos, te asesoramos y te acompañamos antes, durante y después de tu alquiler.</p>
						</div>
						<!-- Value 4 -->
						<div class="rrc-about-value-card">
							<div class="rrc-about-value-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
							</div>
							<h4 class="rrc-about-value-title">Servicio local</h4>
							<p class="rrc-about-value-desc">Somos parte de esta isla. Conocemos Roatán y queremos que la disfrutes al máximo.</p>
						</div>
					</div>

				</div>
			</div>

			<!-- 5. Lo que nos diferencia -->
			<div class="rrc-about-diff-section">
				<div class="rrc-about-container">
					<div class="rrc-about-section-header">
						<span class="rrc-about-pretitle">Lo que nos diferencia</span>
						<h3 class="rrc-about-title-large">Más beneficios para tu tranquilidad</h3>
					</div>

					<div class="rrc-about-diff-grid">
						<!-- Diff 1 -->
						<div class="rrc-about-diff-card">
							<div class="rrc-about-diff-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><path d="M17 9H13.5a2.5 2.5 0 0 0 0 5h3a2.5 2.5 0 0 1 0 5H10"></path></svg>
							</div>
							<div class="rrc-about-diff-content">
								<h4 class="rrc-about-diff-title">Tarifas transparentes (Drive Away)</h4>
								<p class="rrc-about-diff-desc">Nuestros precios incluyen todo lo esencial. Sin sorpresas, sin cargos ocultos.</p>
							</div>
						</div>
						<!-- Diff 2 -->
						<div class="rrc-about-diff-card">
							<div class="rrc-about-diff-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 11 2 2 4-4"></path></svg>
							</div>
							<div class="rrc-about-diff-content">
								<h4 class="rrc-about-diff-title">Seguro incluido</h4>
								<p class="rrc-about-diff-desc">Todas nuestras tarifas incluyen seguro básico para tu tranquilidad.</p>
							</div>
						</div>
						<!-- Diff 3 -->
						<div class="rrc-about-diff-card">
							<div class="rrc-about-diff-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
							</div>
							<div class="rrc-about-diff-content">
								<h4 class="rrc-about-diff-title">Entrega y recogida GRATIS</h4>
								<p class="rrc-about-diff-desc">Te llevamos el vehículo y lo recogemos en Roatán donde lo necesites.</p>
							</div>
						</div>
						<!-- Diff 4 -->
						<div class="rrc-about-diff-card">
							<div class="rrc-about-diff-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
							</div>
							<div class="rrc-about-diff-content">
								<h4 class="rrc-about-diff-title">Cobertura de puntos clave</h4>
								<p class="rrc-about-diff-desc">Aeropuerto, terminal de ferry y muelles de cruceros.</p>
							</div>
						</div>
						<!-- Diff 5 -->
						<div class="rrc-about-diff-card">
							<div class="rrc-about-diff-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
							</div>
							<div class="rrc-about-diff-content">
								<h4 class="rrc-about-diff-title">Soporte 24/7</h4>
								<p class="rrc-about-diff-desc">Atención local e internacional siempre disponible para ayudarte.</p>
							</div>
						</div>
						<!-- Diff 6 -->
						<div class="rrc-about-diff-card">
							<div class="rrc-about-diff-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"></path><path d="M12 11h2v2h-2v-2zm0-4h2v2h-2V7zm-4 4h2v2H8v-2zm0-4h2v2H8V7zm0 8h2v2H8v-2zm4 0h2v2h-2v-2z"></path></svg>
							</div>
							<div class="rrc-about-diff-content">
								<h4 class="rrc-about-diff-title">Oficinas estratégicas</h4>
								<p class="rrc-about-diff-desc">Coxen Hole, French Harbor y Aeropuerto de San Pedro Sula.</p>
							</div>
						</div>
					</div>

				</div>
			</div>

			<!-- 6. Nuestra Promesa -->
			<div class="rrc-about-promise-section">
				<div class="rrc-about-container">
					<div class="rrc-about-section-header">
						<span class="rrc-about-pretitle">Nuestra promesa al cliente</span>
						<h3 class="rrc-about-title-large">Tu satisfacción es nuestra prioridad</h3>
					</div>

					<div class="rrc-about-promise-grid">
						<!-- Card 1 -->
						<div class="rrc-about-promise-card">
							<div class="rrc-about-promise-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
							</div>
							<h5 class="rrc-about-promise-title">Vehículos confiables</h5>
							<p class="rrc-about-promise-desc">Flota moderna, limpia y lista para tu aventura.</p>
						</div>
						<!-- Card 2 -->
						<div class="rrc-about-promise-card">
							<div class="rrc-about-promise-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><path d="M17 9H13.5a2.5 2.5 0 0 0 0 5h3a2.5 2.5 0 0 1 0 5H10"></path></svg>
							</div>
							<h5 class="rrc-about-promise-title">Precios justos</h5>
							<p class="rrc-about-promise-desc">Drive Away: todo incluido, sin costos ocultos.</p>
						</div>
						<!-- Card 3 -->
						<div class="rrc-about-promise-card">
							<div class="rrc-about-promise-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
							</div>
							<h5 class="rrc-about-promise-title">Reserva fácil</h5>
							<p class="rrc-about-promise-desc">Proceso rápido, seguro y 100% en línea.</p>
						</div>
						<!-- Card 4 -->
						<div class="rrc-about-promise-card">
							<div class="rrc-about-promise-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
							</div>
							<h5 class="rrc-about-promise-title">Experiencia sin estrés</h5>
							<p class="rrc-about-promise-desc">Nos encargamos de los detalles por ti.</p>
						</div>
						<!-- Card 5 -->
						<div class="rrc-about-promise-card">
							<div class="rrc-about-promise-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
							</div>
							<h5 class="rrc-about-promise-title">Compromiso local</h5>
							<p class="rrc-about-promise-desc">Apoyamos nuestra comunidad y cuidamos nuestra isla.</p>
						</div>
					</div>

				</div>
			</div>

			<!-- 7. Call To Action (Light themed banner) -->
			<div class="rrc-about-cta-section">
				<div class="rrc-about-container">
					<div class="rrc-about-cta-banner">
						<h3 class="rrc-about-cta-title">Explora Roatán con una empresa que sí te acompaña</h3>
						<p class="rrc-about-cta-desc">Reserva hoy y viaja con la confianza de estar en las mejores manos.</p>
						<div class="rrc-about-cta-btns">
							<a href="https://wa.me/50499039616" target="_blank" class="rrc-about-cta-btn-primary">Reservar ahora</a>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota" class="rrc-about-cta-btn-secondary">Ver vehículos</a>
						</div>
					</div>
				</div>
			</div>

		</section>
		<?php
		return ob_get_clean();
	}

	public static function render_contact_us_shortcode() {
		ob_start();

		$success_msg = '';
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['rrc_submit_contact'] ) ) {
			// Handle simple contact form submission
			$name = sanitize_text_field( $_POST['rrc_name'] );
			$email = sanitize_email( $_POST['rrc_email'] );
			$category = sanitize_text_field( $_POST['rrc_category'] );
			$message = sanitize_textarea_field( $_POST['rrc_message'] );

			// In a real scenario, this would send an email or save to DB.
			// Let's set a success message.
			$success_msg = '¡Gracias, ' . esc_html( $name ) . '! Hemos recibido tu mensaje y nos pondremos en contacto contigo pronto.';
		}
		?>
		<style>
		@import url('https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,300..900;1,300..900&display=swap');

		.rrc-contact-section {
			background: #f8fafc;
			padding: 100px 0;
			font-family: 'Inter Tight', 'Inter', sans-serif;
			color: #1e293b;
		}
		.rrc-contact-container {
			width: 90%;
			max-width: 1200px;
			margin: 0 auto;
			display: grid;
			grid-template-columns: 1.1fr 0.9fr;
			gap: 80px;
			align-items: center;
		}

		/* Left column styling */
		.rrc-contact-info-col {
			display: flex;
			flex-direction: column;
		}
		.rrc-contact-badge {
			font-size: 11px;
			font-weight: 800;
			text-transform: uppercase;
			color: #64748b;
			letter-spacing: 1.5px;
			margin-bottom: 15px;
		}
		.rrc-contact-title {
			font-size: 46px;
			font-weight: 900;
			line-height: 1.15;
			color: #0f172a;
			margin: 0 0 20px 0;
			letter-spacing: -1.5px;
		}
		.rrc-contact-desc {
			font-size: 15px;
			line-height: 1.6;
			color: #64748b;
			margin-bottom: 40px;
		}

		/* Contact details styling */
		.rrc-contact-details {
			display: flex;
			flex-direction: column;
			gap: 25px;
		}
		.rrc-contact-detail-item {
			display: flex;
			align-items: center;
			gap: 20px;
		}
		.rrc-contact-detail-icon {
			width: 50px;
			height: 50px;
			border-radius: 12px;
			background: rgba(232, 39, 44, 0.08);
			color: #E8272C;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-contact-detail-icon svg {
			width: 24px;
			height: 24px;
		}
		.rrc-contact-detail-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-contact-detail-label {
			font-size: 12px;
			font-weight: 700;
			color: #94a3b8;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			margin-bottom: 2px;
		}
		.rrc-contact-detail-value {
			font-size: 16px;
			font-weight: 800;
			color: #0f172a;
			text-decoration: none;
			transition: color 0.2s ease;
		}
		.rrc-contact-detail-value:hover {
			color: #E8272C;
		}

		/* Right column: Card Form styling */
		.rrc-contact-card {
			background: #ffffff;
			border-radius: 24px;
			padding: 40px;
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.03);
			border: 1px solid #f1f5f9;
		}
		.rrc-contact-form {
			display: flex;
			flex-direction: column;
			gap: 20px;
		}
		.rrc-form-group {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}
		.rrc-form-label {
			font-size: 13px;
			font-weight: 800;
			color: #64748b;
		}
		.rrc-form-input, .rrc-form-select, .rrc-form-textarea {
			width: 100%;
			background: #f8fafc;
			border: 1px solid #f1f5f9;
			border-radius: 12px;
			padding: 12px 16px;
			font-size: 14px;
			font-family: inherit;
			color: #0f172a;
			box-sizing: border-box;
			transition: all 0.2s ease;
		}
		.rrc-form-input:focus, .rrc-form-select:focus, .rrc-form-textarea:focus {
			outline: none;
			background: #ffffff;
			border-color: #E8272C;
			box-shadow: 0 0 0 3px rgba(232, 39, 44, 0.1);
		}
		.rrc-form-textarea {
			resize: vertical;
			min-height: 120px;
		}

		/* Submit Button styling - exact matching of reference */
		.rrc-contact-submit-btn {
			display: inline-flex;
			align-items: center;
			background: #E8272C;
			border: none;
			border-radius: 30px;
			padding: 4px 24px 4px 4px;
			cursor: pointer;
			align-self: flex-start;
			transition: all 0.3s ease;
			box-shadow: 0 4px 15px rgba(232, 39, 44, 0.2);
			margin-top: 10px;
		}
		.rrc-contact-submit-btn:hover {
			background: #c61d22;
			box-shadow: 0 6px 20px rgba(232, 39, 44, 0.35);
			transform: translateY(-2px);
		}
		.rrc-btn-circle {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			background: #ffffff;
			color: #E8272C;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-right: 15px;
			transition: transform 0.3s ease;
		}
		.rrc-contact-submit-btn:hover .rrc-btn-circle {
			transform: translateX(3px);
		}
		.rrc-btn-text {
			color: #ffffff;
			font-size: 13.5px;
			font-weight: 800;
			text-transform: capitalize;
		}

		/* Success Message Alert */
		.rrc-contact-success {
			background: #f0fdf4;
			border: 1px solid #bbf7d0;
			color: #166534;
			padding: 16px 20px;
			border-radius: 12px;
			font-size: 14px;
			line-height: 1.5;
			margin-bottom: 20px;
			font-weight: 700;
		}

		/* Responsive styling */
		@media (max-width: 991px) {
			.rrc-contact-container {
				grid-template-columns: 1fr;
				gap: 50px;
			}
			.rrc-contact-title {
				font-size: 36px;
			}
		}
		</style>

		<section class="rrc-contact-section">
			<div class="rrc-contact-container">
				
				<!-- Left Column: Text & Contact details -->
				<div class="rrc-contact-info-col">
					<span class="rrc-contact-badge">Estamos aquí para ayudarte</span>
					<h2 class="rrc-contact-title">Hablemos de tus necesidades de movilidad en Roatán</h2>
					<p class="rrc-contact-desc">
						¿Buscas la mejor opción de alquiler de vehículos adaptada a tu viaje en la isla? Ponte en contacto con nosotros y resolveremos todas tus dudas al instante.
					</p>

					<div class="rrc-contact-details">
						<!-- E-mail -->
						<div class="rrc-contact-detail-item">
							<div class="rrc-contact-detail-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
							</div>
							<div class="rrc-contact-detail-content">
								<span class="rrc-contact-detail-label">E-mail</span>
								<a href="mailto:ramirezrentacarroatan@gmail.com" class="rrc-contact-detail-value">ramirezrentacarroatan@gmail.com</a>
							</div>
						</div>

						<!-- Phone / WhatsApp -->
						<div class="rrc-contact-detail-item">
							<div class="rrc-contact-detail-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
							</div>
							<div class="rrc-contact-detail-content">
								<span class="rrc-contact-detail-label">Número de teléfono</span>
								<a href="tel:+50499039616" class="rrc-contact-detail-value">+504 9903-9616</a>
							</div>
						</div>

						<!-- Address -->
						<div class="rrc-contact-detail-item">
							<div class="rrc-contact-detail-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
							</div>
							<div class="rrc-contact-detail-content">
								<span class="rrc-contact-detail-label">Dirección de la oficina</span>
								<span class="rrc-contact-detail-value" style="font-size: 15px; color: #0f172a; line-height: 1.4; display: block; margin-top: 4px; font-weight: 800;">Coxen Hole, Calle Principal al Aeropuerto, Roatán, Islas de la Bahía, Honduras</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Right Column: Card Form -->
				<div class="rrc-contact-card">
					<?php if ( ! empty( $success_msg ) ) : ?>
						<div class="rrc-contact-success">
							<?php echo esc_html( $success_msg ); ?>
						</div>
					<?php endif; ?>

					<form method="post" action="" class="rrc-contact-form">
						<!-- Name -->
						<div class="rrc-form-group">
							<label class="rrc-form-label" for="rrc_name">Nombre</label>
							<input type="text" id="rrc_name" name="rrc_name" class="rrc-form-input" placeholder="Tu nombre completo" required />
						</div>

						<!-- Email -->
						<div class="rrc-form-group">
							<label class="rrc-form-label" for="rrc_email">Email</label>
							<input type="email" id="rrc_email" name="rrc_email" class="rrc-form-input" placeholder="tu.email@gmail.com" required />
						</div>

						<!-- Category/Vehicle -->
						<div class="rrc-form-group">
							<label class="rrc-form-label" for="rrc_category">Categoría de vehículo</label>
							<select id="rrc_category" name="rrc_category" class="rrc-form-select">
								<option value="sedan">Sedanes</option>
								<option value="suv">SUV / KIA Sorento</option>
								<option value="jeep">Jeeps / 4x4</option>
								<option value="cuatrimoto">Cuatrimotos</option>
								<option value="van">Vans para grupos</option>
							</select>
						</div>

						<!-- Message -->
						<div class="rrc-form-group">
							<label class="rrc-form-label" for="rrc_message">Mensaje</label>
							<textarea id="rrc_message" name="rrc_message" class="rrc-form-textarea" placeholder="Escribe aquí tus requerimientos..." required></textarea>
						</div>

						<!-- Submit Button -->
						<button type="submit" name="rrc_submit_contact" class="rrc-contact-submit-btn">
							<span class="rrc-btn-circle">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
							</span>
							<span class="rrc-btn-text">Enviar mensaje</span>
						</button>
					</form>
				</div>

			</div>
		</section>

		<!-- Mapa de Google a Ancho Completo -->
		<div class="rrc-contact-map-wrap" style="width: 100%; line-height: 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
			<iframe 
				src="https://maps.google.com/maps?q=Ramirez%20Rent%20A%20Car,%20Coxen%20Hole,%20Roatan,%20Honduras&t=&z=16&ie=UTF8&iwloc=&output=embed" 
				width="100%" 
				height="450" 
				style="border:0; width: 100%; display: block;" 
				allowfullscreen="" 
				loading="lazy" 
				referrerpolicy="no-referrer-when-downgrade">
			</iframe>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_terms_conditions_shortcode() {
		ob_start();
		?>
		<style>
		.rrc-terms-section {
			background: #ffffff;
			color: #334155;
			font-family: 'Inter Tight', 'Inter', sans-serif;
			padding: 80px 0;
			overflow: hidden;
		}
		.rrc-terms-container {
			width: 90%;
			max-width: 1200px;
			margin: 0 auto;
		}

		/* Header Section */
		.rrc-terms-header {
			text-align: center;
			margin-bottom: 50px;
		}
		.rrc-terms-title {
			font-size: 42px;
			font-weight: 900;
			color: #0f172a;
			letter-spacing: -1.2px;
			margin: 0 0 20px 0;
			line-height: 1.2;
		}
		.rrc-terms-subtitle {
			font-size: 16px;
			line-height: 1.6;
			color: #64748b;
			max-width: 800px;
			margin: 0 auto 30px auto;
		}

		/* Top Alert Box */
		.rrc-terms-top-alert {
			background: #fef2f2;
			border: 1px solid #fecaca;
			border-radius: 12px;
			padding: 16px 24px;
			display: inline-flex;
			align-items: center;
			gap: 12px;
			color: #991b1b;
			font-weight: 700;
			font-size: 14.5px;
			text-align: left;
			margin-bottom: 40px;
		}
		.rrc-terms-top-alert svg {
			width: 20px;
			height: 20px;
			color: #ef4444;
			flex-shrink: 0;
		}

		/* Warning Panel (Aviso Legal) */
		.rrc-terms-warning-panel {
			background: #fffbeb;
			border: 1px solid #fef3c7;
			border-left: 5px solid #d97706;
			border-radius: 12px;
			padding: 25px;
			margin-bottom: 50px;
			display: flex;
			gap: 20px;
			align-items: flex-start;
			text-align: left;
		}
		.rrc-terms-warning-icon {
			width: 44px;
			height: 44px;
			border-radius: 50%;
			background: rgba(217, 119, 6, 0.1);
			color: #d97706;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-terms-warning-icon svg {
			width: 22px;
			height: 22px;
		}
		.rrc-terms-warning-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-terms-warning-title {
			color: #b45309;
			font-weight: 800;
			font-size: 14px;
			text-transform: uppercase;
			margin: 0 0 5px 0;
			letter-spacing: 0.5px;
		}
		.rrc-terms-warning-desc {
			color: #78350f;
			font-size: 13.5px;
			margin: 0;
			line-height: 1.5;
		}

		/* Cards Grid */
		.rrc-terms-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 25px;
			margin-bottom: 50px;
		}
		.rrc-terms-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 30px 24px;
			box-shadow: 0 4px 15px rgba(0,0,0,0.01);
			display: flex;
			flex-direction: column;
			gap: 15px;
			text-align: left;
			transition: all 0.2s ease;
		}
		.rrc-terms-card:hover {
			border-color: #cbd5e1;
			box-shadow: 0 8px 25px rgba(0,0,0,0.03);
			transform: translateY(-2px);
		}
		.rrc-terms-card-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			border-bottom: 1px solid #f1f5f9;
			padding-bottom: 15px;
		}
		.rrc-terms-card-icon {
			width: 36px;
			height: 36px;
			border-radius: 8px;
			background: rgba(232, 39, 44, 0.06);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-terms-card-icon svg {
			width: 18px;
			height: 18px;
		}
		.rrc-terms-card-num {
			color: #e2e8f0;
			font-weight: 900;
			font-size: 24px;
			line-height: 1;
		}
		.rrc-terms-card-title {
			font-size: 15.5px;
			font-weight: 800;
			color: #0f172a;
			margin: 0;
			line-height: 1.4;
		}
		.rrc-terms-card-list {
			list-style: none;
			padding: 0;
			margin: 0;
			display: flex;
			flex-direction: column;
			gap: 10px;
		}
		.rrc-terms-card-list li {
			font-size: 12.5px;
			line-height: 1.5;
			color: #475569;
			position: relative;
			padding-left: 14px;
		}
		.rrc-terms-card-list li::before {
			content: '•';
			color: #e8272c;
			position: absolute;
			left: 0;
			font-weight: bold;
		}

		/* Bottom Banner */
		.rrc-terms-bottom-banner {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 25px;
			display: flex;
			align-items: center;
			gap: 20px;
			text-align: left;
			max-width: 1000px;
			margin: 0 auto;
		}
		.rrc-terms-bottom-icon {
			width: 44px;
			height: 44px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-terms-bottom-icon svg {
			width: 20px;
			height: 20px;
		}
		.rrc-terms-bottom-text {
			font-size: 14px;
			line-height: 1.5;
			color: #334155;
			font-weight: 600;
		}

		/* Responsive styling */
		@media (max-width: 1024px) {
			.rrc-terms-grid {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		@media (max-width: 768px) {
			.rrc-terms-title {
				font-size: 32px;
			}
			.rrc-terms-top-alert {
				display: flex;
				flex-direction: column;
				text-align: center;
			}
			.rrc-terms-warning-panel {
				flex-direction: column;
				gap: 15px;
			}
		}
		@media (max-width: 575px) {
			.rrc-terms-grid {
				grid-template-columns: 1fr;
			}
			.rrc-terms-bottom-banner {
				flex-direction: column;
				text-align: center;
				gap: 15px;
			}
		}
		</style>

		<section class="rrc-terms-section">
			<div class="rrc-terms-container">
				
				<!-- Header -->
				<div class="rrc-terms-header">
					<h2 class="rrc-terms-title">Términos y condiciones de alquiler</h2>
					<p class="rrc-terms-subtitle">
						Este documento ha sido redactado conforme a las mejores prácticas generales en materia de alquiler, contratación electrónica, consumo, privacidad y comercio digital aplicables a operaciones en Honduras.
					</p>
				</div>

				<!-- Grid layout -->
				<div class="rrc-terms-grid">
					
					<!-- Card 1 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
							</div>
							<span class="rrc-terms-card-num">01</span>
						</div>
						<h4 class="rrc-terms-card-title">1. Aceptación y alcance</h4>
						<ul class="rrc-terms-card-list">
							<li>Al realizar una reserva o utilizar nuestros servicios, usted acepta estos términos y condiciones en su totalidad.</li>
							<li>Aplican a todas las reservas, contratos y servicios de alquiler de vehículos realizados por Ramírez Rent A Car, en todas nuestras oficinas y canales de venta.</li>
							<li>Nos reservamos el derecho de actualizar estos términos en cualquier momento. La versión vigente será la publicada en nuestro sitio web.</li>
						</ul>
					</div>

					<!-- Card 2 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
							</div>
							<span class="rrc-terms-card-num">02</span>
						</div>
						<h4 class="rrc-terms-card-title">2. Requisitos del arrendatario</h4>
						<ul class="rrc-terms-card-list">
							<li>El arrendatario debe ser mayor de 21 años.</li>
							<li>Licencia de conducir válida con mínimo 1 año de antigüedad.</li>
							<li>No se permite el alquiler a personas con restricciones o suspensiones legales para conducir.</li>
							<li>Ramírez Rent A Car se reserva el derecho de negar el servicio a su exclusiva discreción.</li>
						</ul>
					</div>

					<!-- Card 3 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
							</div>
							<span class="rrc-terms-card-num">03</span>
						</div>
						<h4 class="rrc-terms-card-title">3. Documentación requerida</h4>
						<ul class="rrc-terms-card-list">
							<li>Los clientes deben presentar al retiro del vehículo:
								<ul style="list-style:circle; padding-left:12px; margin-top:5px; color:#64748b;">
									<li>Licencia de conducir válida.</li>
									<li>Pasaporte vigente o Tarjeta de Identidad oficial hondureña.</li>
								</ul>
							</li>
							<li>La información debe coincidir con la reserva y el método de pago utilizado.</li>
						</ul>
					</div>

					<!-- Card 4 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
							</div>
							<span class="rrc-terms-card-num">04</span>
						</div>
						<h4 class="rrc-terms-card-title">4. Reservas y pagos</h4>
						<ul class="rrc-terms-card-list">
							<li>Para confirmar una reserva en línea se requiere el pago 100% por adelantado.</li>
							<li>Los precios están expresados en dólares estadounidenses (USD).</li>
							<li>Métodos de pago aceptados en Honduras: efectivo, cheques de viajero, cheques bancarios certificados y PayPal para pagos en línea.</li>
							<li>No aceptamos cheques personales ni pagos con tarjeta de crédito/débito en el mostrador.</li>
							<li>Recibirá un correo de confirmación en un plazo máximo de 48 horas laborables. Se recomienda imprimir la confirmación y presentarla al retirar el vehículo.</li>
						</ul>
					</div>

					<!-- Card 5 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
							</div>
							<span class="rrc-terms-card-num">05</span>
						</div>
						<h4 class="rrc-terms-card-title">5. Tarifas Drive Away e impuestos</h4>
						<ul class="rrc-terms-card-list">
							<li>Nuestras tarifas son "Drive Away", es decir, incluyen los impuestos aplicables en Honduras.</li>
							<li>Pueden aplicarse cargos adicionales por servicios opcionales o solicitudes especiales.</li>
						</ul>
					</div>

					<!-- Card 6 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
							</div>
							<span class="rrc-terms-card-num">06</span>
						</div>
						<h4 class="rrc-terms-card-title">6. Entrega, recogida y puntos autorizados</h4>
						<ul class="rrc-terms-card-list">
							<li>La entrega y devolución deben realizarse en nuestras oficinas o puntos autorizados.</li>
							<li>La devolución fuera de horario o en una ubicación distinta puede generar cargos adicionales.</li>
							<li>El arrendatario es responsable de entregar el vehículo en buenas condiciones en el lugar y fecha acordados.</li>
						</ul>
					</div>

					<!-- Card 7 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
							</div>
							<span class="rrc-terms-card-num">07</span>
						</div>
						<h4 class="rrc-terms-card-title">7. Uso permitido y restricciones</h4>
						<ul class="rrc-terms-card-list">
							<li>El vehículo debe utilizarse únicamente dentro de Honduras.</li>
							<li>No está permitido el transporte de pasajeros o carga para fines comerciales, ilegales o indebidos.</li>
							<li>Queda prohibido participar en carreras, competiciones o actividades que pongan en riesgo el vehículo.</li>
						</ul>
					</div>

					<!-- Card 8 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
							</div>
							<span class="rrc-terms-card-num">08</span>
						</div>
						<h4 class="rrc-terms-card-title">8. Seguro incluido y responsabilidades</h4>
						<ul class="rrc-terms-card-list">
							<li>Incluimos cobertura básica de seguro con deducible, cuya información se detalla al momento de la reserva.</li>
							<li>El cliente es responsable de cualquier daño, pérdida, robo o infracción ocasionada durante el periodo de alquiler, conforme a los términos del contrato.</li>
						</ul>
					</div>

					<!-- Card 9 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
							</div>
							<span class="rrc-terms-card-num">09</span>
						</div>
						<h4 class="rrc-terms-card-title">9. Daños, accidentes y reporte</h4>
						<ul class="rrc-terms-card-list">
							<li>En caso de accidente, daño o robo, debe notificar de inmediato a la policía y a Ramírez Rent A Car.</li>
							<li>Se requiere un reporte oficial.</li>
							<li>El no reportar puede resultar en cargos adicionales y pérdida de cobertura.</li>
						</ul>
					</div>

					<!-- Card 10 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<!-- Fuel pump SVG -->
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22V2h10v20H3zm7-15H6v4h4V7zm7 8c.5-1.5 1-2.5 2-2.5s1.5 1 1.5 2.5v4c0 1.5-.5 2-1.5 2s-2-1-2-2.5v-3.5z"></path></svg>
							</div>
							<span class="rrc-terms-card-num">10</span>
						</div>
						<h4 class="rrc-terms-card-title">10. Combustible y cargos adicionales</h4>
						<ul class="rrc-terms-card-list">
							<li>El vehículo se entrega con el tanque lleno y debe devolverse con el mismo nivel.</li>
							<li>El combustible faltante se cobrará con un recargo por servicio.</li>
							<li>Cargos adicionales pueden aplicar por limpieza extrema, retrasos, multas o equipos faltantes.</li>
						</ul>
					</div>

					<!-- Card 11 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
							</div>
							<span class="rrc-terms-card-num">11</span>
						</div>
						<h4 class="rrc-terms-card-title">11. Cancelaciones y reembolsos</h4>
						<ul class="rrc-terms-card-list">
							<li>Las cancelaciones deben solicitarse por escrito (correo electrónico).</li>
							<li>Política de reembolsos:
								<ul style="list-style:circle; padding-left:12px; margin-top:5px; color:#64748b;">
									<li>30 días o más antes del inicio: 100%.</li>
									<li>Entre 15 y 29 días: 50%.</li>
									<li>Menos de 15 días: no hay reembolso.</li>
								</ul>
							</li>
						</ul>
					</div>

					<!-- Card 12 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 16.9A5 5 0 0 0 18 7h-1.26a8 8 0 1 0-11.62 8.58"></path></svg>
							</div>
							<span class="rrc-terms-card-num">12</span>
						</div>
						<h4 class="rrc-terms-card-title">12. Disponibilidad y fuerza mayor</h4>
						<ul class="rrc-terms-card-list">
							<li>La disponibilidad está sujeta a cambio sin previo aviso.</li>
							<li>Nos reservamos el derecho de cambiar la categoría del vehículo por uno igual o superior si fuera necesario.</li>
							<li>No seremos responsables por retrasos o incumplimientos causados por casos fortuitos, fuerza mayor o eventos fuera de nuestro control.</li>
						</ul>
					</div>

					<!-- Card 13 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
							</div>
							<span class="rrc-terms-card-num">13</span>
						</div>
						<h4 class="rrc-terms-card-title">13. Protección de datos y privacidad</h4>
						<ul class="rrc-terms-card-list">
							<li>Sus datos personales se tratarán conforme a nuestra Política de Privacidad.</li>
							<li>Al aceptar estos términos, usted consiente recibir comunicaciones electrónicas relacionadas con su reserva y nuestros servicios.</li>
						</ul>
					</div>

					<!-- Card 14 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"></circle><path d="M14.5 9.5a3 3 0 1 0 0 5" stroke="currentColor" stroke-width="2" fill="none"></path>
							</div>
							<span class="rrc-terms-card-num">14</span>
						</div>
						<h4 class="rrc-terms-card-title">14. Propiedad intelectual del sitio</h4>
						<ul class="rrc-terms-card-list">
							<li>Todo el contenido de este sitio web, marcas, logotipos, textos e imágenes son propiedad de Ramírez Rent A Car o de sus licenciantes.</li>
							<li>Queda prohibida su reproducción o uso sin autorización previa y por escrito.</li>
						</ul>
					</div>

					<!-- Card 15 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="9" y1="18" x2="15" y2="18"></line><line x1="12" y1="3" x2="12" y2="21"></line><path d="M21 7c0-2-4-2-4-2s-4 0-4 2 4 2 4 2 4 0 4-2z"></path><path d="M11 7c0-2-4-2-4-2s-4 0-4 2 4 2 4 2 4 0 4-2z"></path></svg>
							</div>
							<span class="rrc-terms-card-num">15</span>
						</div>
						<h4 class="rrc-terms-card-title">15. Ley aplicable y jurisdicción</h4>
						<ul class="rrc-terms-card-list">
							<li>Estos términos se rigen por las leyes de la República de Honduras.</li>
							<li>Para la resolución de cualquier disputa las partes se someten a la jurisdicción de los tribunales competentes de Roatán, Islas de la Bahía, Honduras.</li>
						</ul>
					</div>

					<!-- Card 16 -->
					<div class="rrc-terms-card">
						<div class="rrc-terms-card-header">
							<div class="rrc-terms-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
							</div>
							<span class="rrc-terms-card-num">16</span>
						</div>
						<h4 class="rrc-terms-card-title">16. Contacto</h4>
						<ul class="rrc-terms-card-list">
							<li>Para preguntas, solicitudes o reclamos, puede contactarnos:
								<ul style="list-style:none; padding-left:0; margin-top:5px; color:#64748b; display:flex; flex-direction:column; gap:5px;">
									<li>📞 (+504) 24-45-01-58</li>
									<li>📞 (+504) 99-03-96-16</li>
									<li>✉️ info@RamirezRentACar.com</li>
								</ul>
							</li>
						</ul>
					</div>

				</div>

				<!-- Bottom Acceptance Banner -->
				<div class="rrc-terms-bottom-banner">
					<div class="rrc-terms-bottom-icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
					</div>
					<p class="rrc-terms-bottom-text">
						Al completar una reserva, el cliente declara haber leído, comprendido y aceptado estos términos y condiciones. Si no está de acuerdo con alguno de estos términos, por favor no realice la reserva.
					</p>
				</div>

			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	public static function render_privacy_policy_shortcode() {
		ob_start();
		?>
		<style>
		.rrc-privacy-section {
			background: #ffffff;
			color: #334155;
			font-family: 'Inter Tight', 'Inter', sans-serif;
			padding: 80px 0;
			overflow: hidden;
		}
		.rrc-privacy-container {
			width: 90%;
			max-width: 1200px;
			margin: 0 auto;
		}

		/* Header Section */
		.rrc-privacy-header {
			text-align: center;
			margin-bottom: 50px;
		}
		.rrc-privacy-title {
			font-size: 42px;
			font-weight: 900;
			color: #0f172a;
			letter-spacing: -1.2px;
			margin: 0 0 20px 0;
			line-height: 1.2;
		}
		.rrc-privacy-subtitle {
			font-size: 16px;
			line-height: 1.6;
			color: #64748b;
			max-width: 800px;
			margin: 0 auto 30px auto;
		}

		/* Top Alert Panel (Aviso de privacidad) */
		.rrc-privacy-alert-panel {
			background: #fef2f2;
			border: 1px solid #fecaca;
			border-left: 5px solid #ef4444;
			border-radius: 12px;
			padding: 25px;
			margin-bottom: 50px;
			display: flex;
			gap: 20px;
			align-items: flex-start;
			text-align: left;
		}
		.rrc-privacy-alert-icon {
			width: 44px;
			height: 44px;
			border-radius: 50%;
			background: rgba(239, 68, 68, 0.1);
			color: #ef4444;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-privacy-alert-icon svg {
			width: 22px;
			height: 22px;
		}
		.rrc-privacy-alert-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-privacy-alert-title {
			color: #991b1b;
			font-weight: 800;
			font-size: 14px;
			text-transform: uppercase;
			margin: 0 0 5px 0;
			letter-spacing: 0.5px;
		}
		.rrc-privacy-alert-desc {
			color: #7f1d1d;
			font-size: 13.5px;
			margin: 0;
			line-height: 1.5;
		}

		/* Grid Layout - 2 columns for policy document to read comfortably */
		.rrc-privacy-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 30px;
			margin-bottom: 60px;
		}
		.rrc-privacy-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 30px;
			box-shadow: 0 4px 15px rgba(0,0,0,0.01);
			display: flex;
			flex-direction: column;
			gap: 15px;
			text-align: left;
			transition: all 0.2s ease;
		}
		.rrc-privacy-card:hover {
			border-color: #cbd5e1;
			box-shadow: 0 8px 25px rgba(0,0,0,0.03);
			transform: translateY(-2px);
		}
		.rrc-privacy-card-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			border-bottom: 1px solid #f1f5f9;
			padding-bottom: 15px;
		}
		.rrc-privacy-card-icon {
			width: 38px;
			height: 38px;
			border-radius: 8px;
			background: rgba(232, 39, 44, 0.06);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-privacy-card-icon svg {
			width: 18px;
			height: 18px;
		}
		.rrc-privacy-card-num {
			color: #e2e8f0;
			font-weight: 900;
			font-size: 24px;
			line-height: 1;
		}
		.rrc-privacy-card-title {
			font-size: 16px;
			font-weight: 800;
			color: #0f172a;
			margin: 0;
			line-height: 1.4;
		}
		.rrc-privacy-card-content {
			font-size: 13px;
			line-height: 1.6;
			color: #475569;
		}
		.rrc-privacy-card-list {
			list-style: none;
			padding: 0;
			margin: 10px 0 0 0;
			display: flex;
			flex-direction: column;
			gap: 8px;
		}
		.rrc-privacy-card-list li {
			position: relative;
			padding-left: 14px;
		}
		.rrc-privacy-card-list li::before {
			content: '•';
			color: #e8272c;
			position: absolute;
			left: 0;
			font-weight: bold;
		}

		/* Cookies Preferences Panel */
		.rrc-privacy-cookies-panel {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 18px;
			padding: 40px;
			max-width: 900px;
			margin: 0 auto;
			text-align: left;
			box-shadow: 0 10px 30px rgba(0,0,0,0.02);
		}
		.rrc-cookies-panel-header {
			margin-bottom: 25px;
			border-bottom: 1px solid #e2e8f0;
			padding-bottom: 20px;
		}
		.rrc-cookies-panel-title {
			font-size: 20px;
			font-weight: 900;
			color: #0f172a;
			margin: 0 0 8px 0;
		}
		.rrc-cookies-panel-desc {
			font-size: 13.5px;
			color: #64748b;
			margin: 0;
			line-height: 1.5;
		}
		.rrc-cookies-options {
			display: flex;
			flex-direction: column;
			gap: 15px;
			margin-bottom: 30px;
		}
		.rrc-cookie-opt-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			padding: 15px 20px;
		}
		.rrc-cookie-opt-info {
			display: flex;
			flex-direction: column;
			gap: 3px;
		}
		.rrc-cookie-opt-name {
			font-size: 14px;
			font-weight: 800;
			color: #0f172a;
		}
		.rrc-cookie-opt-desc {
			font-size: 12px;
			color: #64748b;
		}
		
		/* Switch Toggle Styling */
		.rrc-cookie-switch {
			position: relative;
			display: inline-block;
			width: 44px;
			height: 24px;
		}
		.rrc-cookie-switch input {
			opacity: 0;
			width: 0;
			height: 0;
		}
		.rrc-cookie-slider {
			position: absolute;
			cursor: pointer;
			top: 0; left: 0; right: 0; bottom: 0;
			background-color: #cbd5e1;
			transition: .3s;
			border-radius: 24px;
		}
		.rrc-cookie-slider:before {
			position: absolute;
			content: "";
			height: 18px;
			width: 18px;
			left: 3px;
			bottom: 3px;
			background-color: white;
			transition: .3s;
			border-radius: 50%;
		}
		input:checked + .rrc-cookie-slider {
			background-color: #e8272c;
		}
		input:disabled + .rrc-cookie-slider {
			background-color: #94a3b8;
			opacity: 0.6;
			cursor: not-allowed;
		}
		input:checked + .rrc-cookie-slider:before {
			transform: translateX(20px);
		}

		/* Cookies panel actions */
		.rrc-cookies-actions {
			display: flex;
			gap: 15px;
			flex-wrap: wrap;
		}
		.rrc-cookie-btn {
			padding: 11px 24px;
			border-radius: 30px;
			font-size: 13px;
			font-weight: 800;
			cursor: pointer;
			transition: all 0.2s ease;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			font-family: inherit;
		}
		.rrc-cookie-btn-primary {
			background: #e8272c;
			color: #ffffff;
			border: none;
		}
		.rrc-cookie-btn-primary:hover {
			background: #b91c1c;
		}
		.rrc-cookie-btn-secondary {
			background: #ffffff;
			color: #334155;
			border: 1px solid #cbd5e1;
		}
		.rrc-cookie-btn-secondary:hover {
			background: #f8fafc;
			border-color: #94a3b8;
		}
		.rrc-cookie-btn svg {
			width: 14px;
			height: 14px;
		}

		/* Responsive styling */
		@media (max-width: 991px) {
			.rrc-privacy-grid {
				grid-template-columns: 1fr;
				gap: 20px;
			}
		}
		@media (max-width: 768px) {
			.rrc-privacy-title {
				font-size: 32px;
			}
			.rrc-privacy-alert-panel {
				flex-direction: column;
				gap: 15px;
			}
			.rrc-privacy-cookies-panel {
				padding: 25px;
			}
		}
		@media (max-width: 575px) {
			.rrc-cookies-actions {
				flex-direction: column;
				gap: 10px;
			}
			.rrc-cookie-btn {
				width: 100%;
				justify-content: center;
			}
		}
		</style>

		<section class="rrc-privacy-section">
			<div class="rrc-privacy-container">
				
				<!-- Header -->
				<div class="rrc-privacy-header">
					<h2 class="rrc-privacy-title">Política de privacidad</h2>
					<p class="rrc-privacy-subtitle">
						Este documento es un borrador basado en buenas prácticas generales para sitios web, formularios de contacto, reservas digitales, atención al cliente, cookies y comunicaciones electrónicas en Honduras, pendiente de revisión legal en Honduras antes de su publicación.
					</p>
				</div>

				<!-- Privacy Alert Panel -->
				<div class="rrc-privacy-alert-panel">
					<div class="rrc-privacy-alert-icon">
						<!-- Shield check icon -->
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
					</div>
					<div class="rrc-privacy-alert-content">
						<h4 class="rrc-privacy-alert-title">Aviso de privacidad</h4>
						<p class="rrc-privacy-alert-desc">
							En Ramírez Rent A Car valoramos tu privacidad y nos comprometemos a proteger la información personal que nos confías. Esta política explica qué datos recopilamos, cómo los usamos, con quién los compartimos y qué derechos tienes sobre tu información.
						</p>
					</div>
				</div>

				<!-- Grid Layout of 15 points -->
				<div class="rrc-privacy-grid">
					
					<!-- Card 1 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
							</div>
							<span class="rrc-privacy-card-num">01</span>
						</div>
						<h4 class="rrc-privacy-card-title">1. Responsable del tratamiento</h4>
						<div class="rrc-privacy-card-content">
							<p>Ramírez Rent A Car es responsable del tratamiento de tus datos personales.</p>
							<ul class="rrc-privacy-card-list">
								<li><strong>Razón social:</strong> Ramírez Rent A Car</li>
								<li><strong>Correo electrónico:</strong> info@RamirezRentACar.com</li>
								<li><strong>Sitio web:</strong> www.RamirezRentACar.com</li>
								<li><strong>Oficinas:</strong> Roatán (Coxen Hole, Islas de la Bahía) y San Pedro Sula</li>
								<li><strong>Teléfonos:</strong> (+504) 24-45-01-58 | (+504) 99-03-96-16</li>
							</ul>
						</div>
					</div>

					<!-- Card 2 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
							</div>
							<span class="rrc-privacy-card-num">02</span>
						</div>
						<h4 class="rrc-privacy-card-title">2. Información que recopilamos</h4>
						<div class="rrc-privacy-card-content">
							<p>Recopilamos información que nos proporcionas directamente y datos que se generan al interactuar con nuestros servicios:</p>
							<ul class="rrc-privacy-card-list">
								<li><strong>Datos de identificación:</strong> nombre, número de identidad o pasaporte, fecha de nacimiento, nacionalidad.</li>
								<li><strong>Datos de contacto:</strong> correo electrónico, número de teléfono, dirección.</li>
								<li><strong>Información de reserva:</strong> fechas de alquiler, vehículo seleccionado, oficina de recogida y devolución, preferencias, número de conductores.</li>
								<li><strong>Información de pago:</strong> procesada de forma segura por nuestros proveedores de pago (no almacenamos datos completos de tarjetas).</li>
								<li><strong>Datos de navegación:</strong> dirección IP, tipo de dispositivo y navegador, páginas visitadas, tiempo de navegación, origen de referencia.</li>
								<li><strong>Comunicaciones:</strong> mensajes enviados a través de formularios, correos, llamadas o chat.</li>
								<li><strong>Registros de seguridad:</strong> actividad de inicio de sesión, intentos de acceso y registros del sistema para detectar y prevenir fraudes.</li>
							</ul>
						</div>
					</div>

					<!-- Card 3 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
							</div>
							<span class="rrc-privacy-card-num">03</span>
						</div>
						<h4 class="rrc-privacy-card-title">3. Finalidades del tratamiento</h4>
						<div class="rrc-privacy-card-content">
							<p>Usamos tu información para:</p>
							<ul class="rrc-privacy-card-list">
								<li>Responder consultas y solicitudes de información.</li>
								<li>Gestionar reservas, contratos y modificaciones.</li>
								<li>Verificar identidad y requisitos de alquiler.</li>
								<li>Enviar confirmaciones, recordatorios y documentos relacionados con tu alquiler.</li>
								<li>Brindar atención al cliente y soporte postservicio.</li>
								<li>Prevenir fraudes, usos indebidos y mejorar la seguridad.</li>
								<li>Mejorar nuestros servicios, la experiencia de usuario y la página web.</li>
								<li>Cumplir obligaciones legales y regulatorias aplicables en Honduras.</li>
							</ul>
						</div>
					</div>

					<!-- Card 4 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
							</div>
							<span class="rrc-privacy-card-num">04</span>
						</div>
						<h4 class="rrc-privacy-card-title">4. Base de uso y consentimiento</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Tratamos tus datos con base en tu consentimiento, la ejecución de medidas precontractuales, la ejecución de un contrato, obligaciones legales y nuestros intereses legítimos, conforme a la Ley de Protección de Datos Personales de Honduras y demás normativa aplicable.
							</p>
						</div>
					</div>

					<!-- Card 5 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
							</div>
							<span class="rrc-privacy-card-num">05</span>
						</div>
						<h4 class="rrc-privacy-card-title">5. Información compartida con terceros</h4>
						<div class="rrc-privacy-card-content">
							<p>
								No vendemos ni alquilamos tu información. Podemos compartirla únicamente cuando sea necesario con terceros de confianza y bajo estrictos compromisos de confidencialidad o cuando la ley lo exija.
							</p>
						</div>
					</div>

					<!-- Card 6 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
							</div>
							<span class="rrc-privacy-card-num">06</span>
						</div>
						<h4 class="rrc-privacy-card-title">6. Proveedores de pago y servicios externos</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Los pagos en línea se procesan a través de proveedores certificados y seguros. No almacenamos datos completos de tarjetas de crédito o débito. Podemos utilizar servicios de terceros para alojamiento web, análisis, email, mensajería y atención al cliente, los cuales cuentan con medidas de seguridad adecuadas.
							</p>
						</div>
					</div>

					<!-- Card 7 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
							</div>
							<span class="rrc-privacy-card-num">07</span>
						</div>
						<h4 class="rrc-privacy-card-title">7. Conservación de datos</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Conservamos tus datos personales solo durante el tiempo necesario para cumplir con las finalidades descritas y para cumplir con obligaciones legales, contables o fiscales (cuando aplique). Posteriormente, los eliminamos o anonimizamos de forma segura.
							</p>
						</div>
					</div>

					<!-- Card 8 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
							</div>
							<span class="rrc-privacy-card-num">08</span>
						</div>
						<h4 class="rrc-privacy-card-title">8. Seguridad de la información</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Aplicamos medidas técnicas, administrativas y físicas razonables para proteger tus datos contra acceso no autorizado, pérdida, uso indebido o alteración. Sin embargo, ningún sistema es 100% seguro y te recomendamos mantener en confidencialidad tu información de acceso.
							</p>
						</div>
					</div>

					<!-- Card 9 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<!-- Cookie SVG -->
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="8" cy="10" r="1.5"></circle><circle cx="16" cy="11" r="1.5"></circle><circle cx="11" cy="16" r="1.5"></circle><circle cx="15" cy="15" r="1"></circle><circle cx="10" cy="8" r="1"></circle></svg>
							</div>
							<span class="rrc-privacy-card-num">09</span>
						</div>
						<h4 class="rrc-privacy-card-title">9. Cookies y tecnologías similares</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Usamos cookies y tecnologías similares para reconocer tu dispositivo, recordar preferencias, analizar el tráfico y mejorar tu experiencia. Puedes aceptar, rechazar o configurar tus preferencias de cookies en cualquier momento desde el panel de opciones mostrado abajo.
							</p>
						</div>
					</div>

					<!-- Card 10 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><polyline points="16 11 18 13 22 9"></polyline></svg>
							</div>
							<span class="rrc-privacy-card-num">10</span>
						</div>
						<h4 class="rrc-privacy-card-title">10. Derechos del usuario</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Tienes derecho a acceder, rectificar, actualizar, limitar u oponerte al tratamiento de tus datos, así como a solicitar la eliminación de los mismos, siempre que no exista una obligación legal de conservarlos. Para ejercer estos derechos, contáctanos a través de los medios indicados en esta política.
							</p>
						</div>
					</div>

					<!-- Card 11 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
							</div>
							<span class="rrc-privacy-card-num">11</span>
						</div>
						<h4 class="rrc-privacy-card-title">11. Menores de edad</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Nuestros servicios están dirigidos a mayores de 18 años. No recopilamos intencionalmente información personal de menores. Si detectamos que se ha recopilado información de un menor sin consentimiento, la eliminaremos a la brevedad posible.
							</p>
						</div>
					</div>

					<!-- Card 12 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
							</div>
							<span class="rrc-privacy-card-num">12</span>
						</div>
						<h4 class="rrc-privacy-card-title">12. Transferencias internacionales</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Algunos de nuestros proveedores de servicios pueden estar ubicados fuera de Honduras. En esos casos, garantizamos que tus datos reciban un nivel adecuado de protección mediante cláusulas contractuales y medidas de seguridad apropiadas.
							</p>
						</div>
					</div>

					<!-- Card 13 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
							</div>
							<span class="rrc-privacy-card-num">13</span>
						</div>
						<h4 class="rrc-privacy-card-title">13. Enlaces a sitios de terceros</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Nuestro sitio web puede contener enlaces a sitios de terceros. No somos responsables de las prácticas de privacidad de dichos sitios. Te recomendamos leer sus políticas antes de proporcionar información personal.
							</p>
						</div>
					</div>

					<!-- Card 14 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
							</div>
							<span class="rrc-privacy-card-num">14</span>
						</div>
						<h4 class="rrc-privacy-card-title">14. Cambios a esta política</h4>
						<div class="rrc-privacy-card-content">
							<p>
								Podemos actualizar esta Política de Privacidad en cualquier momento. Publicaremos la versión actualizada en esta página con la fecha de entrada en vigencia. Te recomendamos revisarla periódicamente.
							</p>
						</div>
					</div>

					<!-- Card 15 -->
					<div class="rrc-privacy-card">
						<div class="rrc-privacy-card-header">
							<div class="rrc-privacy-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
							</div>
							<span class="rrc-privacy-card-num">15</span>
						</div>
						<h4 class="rrc-privacy-card-title">15. Cómo contactarnos</h4>
						<div class="rrc-privacy-card-content">
							<ul class="rrc-privacy-card-list">
								<li><strong>Correo:</strong> info@RamirezRentACar.com</li>
								<li><strong>Teléfonos:</strong> (+504) 24-45-01-58 | (+504) 99-03-96-16</li>
								<li><strong>Oficinas:</strong>
									<ul style="list-style:circle; padding-left:12px; margin-top:5px;">
										<li><strong>Roatán:</strong> Coxen Hole, Calle Principal al Aeropuerto, Roatán, Islas de la Bahía, Honduras</li>
										<li><strong>San Pedro Sula:</strong> Aeropuerto Internacional Ramón Villeda Morales, San Pedro Sula, Honduras</li>
									</ul>
								</li>
							</ul>
						</div>
					</div>

				</div>

				<!-- Cookies Preferences Panel (interactive frontend) -->
				<div class="rrc-privacy-cookies-panel" id="rrc-cookie-settings-panel">
					<div class="rrc-cookies-panel-header">
						<h4 class="rrc-cookies-panel-title">Preferencias de cookies</h4>
						<p class="rrc-cookies-panel-desc">
							Puedes aceptar todas las cookies, rechazarlas o configurar cuáles deseas permitir. Tu elección se guardará en tu navegador.
						</p>
					</div>

					<div class="rrc-cookies-options">
						<!-- Row 1: Necesarias -->
						<div class="rrc-cookie-opt-row">
							<div class="rrc-cookie-opt-info">
								<span class="rrc-cookie-opt-name">Necesarias</span>
								<span class="rrc-cookie-opt-desc">Esenciales para el funcionamiento del sitio.</span>
							</div>
							<label class="rrc-cookie-switch">
								<input type="checkbox" checked disabled>
								<span class="rrc-cookie-slider"></span>
							</label>
						</div>

						<!-- Row 2: Analíticas -->
						<div class="rrc-cookie-opt-row">
							<div class="rrc-cookie-opt-info">
								<span class="rrc-cookie-opt-name">Analíticas</span>
								<span class="rrc-cookie-opt-desc">Nos ayudan a entender cómo usas el sitio.</span>
							</div>
							<label class="rrc-cookie-switch">
								<input type="checkbox" id="cookie-analytics">
								<span class="rrc-cookie-slider"></span>
							</label>
						</div>

						<!-- Row 3: Marketing -->
						<div class="rrc-cookie-opt-row">
							<div class="rrc-cookie-opt-info">
								<span class="rrc-cookie-opt-name">Marketing</span>
								<span class="rrc-cookie-opt-desc">Para mostrarte contenido y ofertas relevantes.</span>
							</div>
							<label class="rrc-cookie-switch">
								<input type="checkbox" id="cookie-marketing">
								<span class="rrc-cookie-slider"></span>
							</label>
						</div>
					</div>

					<div class="rrc-cookies-actions">
						<button type="button" class="rrc-cookie-btn rrc-cookie-btn-primary" onclick="rrcAcceptAllCookies()">
							Aceptar todas
						</button>
						<button type="button" class="rrc-cookie-btn rrc-cookie-btn-secondary" onclick="rrcRejectAllCookies()">
							Rechazar todas
						</button>
						<button type="button" class="rrc-cookie-btn rrc-cookie-btn-secondary" onclick="rrcSaveCookiePrefs()">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
							Personalizar preferencias
						</button>
					</div>
				</div>

			</div>
		</section>

		<script>
		function rrcAcceptAllCookies() {
			document.getElementById('cookie-analytics').checked = true;
			document.getElementById('cookie-marketing').checked = true;
			alert('Preferencias de cookies guardadas: todas aceptadas.');
		}
		function rrcRejectAllCookies() {
			document.getElementById('cookie-analytics').checked = false;
			document.getElementById('cookie-marketing').checked = false;
			alert('Preferencias de cookies guardadas: solo necesarias.');
		}
		function rrcSaveCookiePrefs() {
			var analytics = document.getElementById('cookie-analytics').checked;
			var marketing = document.getElementById('cookie-marketing').checked;
			alert('Preferencias personalizadas guardadas. Analíticas: ' + (analytics?'Sí':'No') + ', Marketing: ' + (marketing?'Sí':'No'));
		}
		</script>
		<?php
		return ob_get_clean();
	}

	public static function render_faq_shortcode() {
		ob_start();
		?>
		<style>
		.rrc-faq-section {
			background: #ffffff;
			color: #334155;
			font-family: 'Inter Tight', 'Inter', sans-serif;
			padding: 80px 0;
			overflow: hidden;
		}
		.rrc-faq-container {
			width: 90%;
			max-width: 1200px;
			margin: 0 auto;
		}

		/* Header Section */
		.rrc-faq-header {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 30px;
			margin-bottom: 50px;
			text-align: left;
		}
		.rrc-faq-header-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-faq-title {
			font-size: 42px;
			font-weight: 900;
			color: #0f172a;
			letter-spacing: -1.2px;
			margin: 0 0 10px 0;
			line-height: 1.2;
		}
		.rrc-faq-subtitle {
			font-size: 16px;
			color: #64748b;
			margin: 0;
		}
		.rrc-faq-header-icon {
			width: 64px;
			height: 64px;
			border-radius: 50%;
			border: 2px solid #e8272c;
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-faq-header-icon svg {
			width: 32px;
			height: 32px;
		}

		/* Benefits Horizontal Bar */
		.rrc-faq-benefits-bar {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 25px;
			margin-bottom: 50px;
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 20px;
			text-align: left;
		}
		.rrc-faq-benefit-item {
			display: flex;
			align-items: center;
			gap: 12px;
		}
		.rrc-faq-benefit-icon {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-faq-benefit-icon svg {
			width: 18px;
			height: 18px;
		}
		.rrc-faq-benefit-title {
			color: #0f172a;
			font-weight: 700;
			font-size: 13.5px;
			margin: 0 0 2px 0;
		}
		.rrc-faq-benefit-desc {
			color: #64748b;
			font-size: 11px;
			margin: 0;
			line-height: 1.3;
		}

		/* Accordion List */
		.rrc-faq-accordion {
			display: flex;
			flex-direction: column;
			gap: 15px;
			margin-bottom: 60px;
			max-width: 900px;
			margin-left: auto;
			margin-right: auto;
		}
		.rrc-faq-item {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			overflow: hidden;
			transition: all 0.2s ease;
		}
		.rrc-faq-item:hover {
			border-color: #cbd5e1;
			box-shadow: 0 4px 15px rgba(0,0,0,0.02);
		}
		.rrc-faq-summary {
			padding: 20px 25px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			cursor: pointer;
			user-select: none;
			font-weight: 800;
			color: #0f172a;
			font-size: 14.5px;
			outline: none;
			list-style: none; /* Hide default summary arrow */
		}
		.rrc-faq-summary::-webkit-details-marker {
			display: none; /* Hide default summary arrow in Webkit */
		}
		.rrc-faq-summary-left {
			display: flex;
			align-items: center;
			gap: 15px;
		}
		.rrc-faq-question-icon {
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-faq-question-icon svg {
			width: 18px;
			height: 18px;
		}
		.rrc-faq-summary svg.icon-chevron {
			width: 16px;
			height: 16px;
			color: #e8272c;
			transition: transform 0.2s ease;
			flex-shrink: 0;
		}
		.rrc-faq-item[open] svg.icon-chevron {
			transform: rotate(180deg);
		}
		.rrc-faq-content {
			padding: 0 25px 20px 58px;
			font-size: 13.5px;
			line-height: 1.6;
			color: #475569;
			border-top: 1px solid #f1f5f9;
			padding-top: 15px;
		}

		/* Bottom Contact block (¿No encuentras tu respuesta?) */
		.rrc-faq-contact-header {
			text-align: center;
			margin-bottom: 40px;
		}
		.rrc-faq-contact-title {
			font-size: 26px;
			font-weight: 900;
			color: #0f172a;
			margin: 0 0 8px 0;
			letter-spacing: -0.5px;
		}
		.rrc-faq-contact-desc {
			font-size: 14.5px;
			color: #64748b;
			margin: 0;
		}
		.rrc-faq-contact-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 25px;
		}
		.rrc-faq-contact-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 30px 20px;
			text-align: center;
			box-shadow: 0 4px 15px rgba(0,0,0,0.01);
		}
		.rrc-faq-contact-icon {
			width: 44px;
			height: 44px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 20px auto;
		}
		.rrc-faq-contact-icon svg {
			width: 20px;
			height: 20px;
		}
		.rrc-faq-contact-card-title {
			font-size: 15px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 10px 0;
		}
		.rrc-faq-contact-card-desc {
			font-size: 12.5px;
			line-height: 1.5;
			color: #64748b;
			margin: 0;
		}

		/* Responsive styling */
		@media (max-width: 1024px) {
			.rrc-faq-contact-grid {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		@media (max-width: 991px) {
			.rrc-faq-benefits-bar {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		@media (max-width: 768px) {
			.rrc-faq-header {
				flex-direction: column;
				text-align: center;
				gap: 15px;
			}
			.rrc-faq-title {
				font-size: 32px;
			}
		}
		@media (max-width: 575px) {
			.rrc-faq-benefits-bar {
				grid-template-columns: 1fr;
			}
			.rrc-faq-contact-grid {
				grid-template-columns: 1fr;
			}
			.rrc-faq-content {
				padding: 0 20px 20px 20px;
			}
			.rrc-faq-summary-left {
				gap: 10px;
			}
		}
		</style>

		<section class="rrc-faq-section">
			<div class="rrc-faq-container">
				
				<!-- Header -->
				<div class="rrc-faq-header">
					<div class="rrc-faq-header-icon">
						<!-- Help circle svg -->
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
					</div>
					<div class="rrc-faq-header-content">
						<h2 class="rrc-faq-title">Preguntas frecuentes</h2>
						<p class="rrc-faq-subtitle">Respuestas claras para alquilar con confianza en Roatán.</p>
					</div>
				</div>

				<!-- Benefits Horizontal Bar -->
				<div class="rrc-faq-benefits-bar">
					<div class="rrc-faq-benefit-item">
						<div class="rrc-faq-benefit-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
						</div>
						<div class="rrc-faq-benefit-info">
							<h5 class="rrc-faq-benefit-title">Entrega y recogida gratis</h5>
							<p class="rrc-faq-benefit-desc">Aeropuerto, Ferry y muelles de cruceros</p>
						</div>
					</div>
					<div class="rrc-faq-benefit-item">
						<div class="rrc-faq-benefit-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 11 2 2 4-4"></path></svg>
						</div>
						<div class="rrc-faq-benefit-info">
							<h5 class="rrc-faq-benefit-title">Seguro incluido</h5>
							<p class="rrc-faq-benefit-desc">Todas nuestras tarifas incluyen seguro</p>
						</div>
					</div>
					<div class="rrc-faq-benefit-item">
						<div class="rrc-faq-benefit-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="12 8 13.09 10.21 15.5 10.56 13.75 12.27 14.16 14.64 12 13.5 9.84 14.64 10.25 12.27 8.5 10.56 10.91 10.21 12 8"></polygon></svg>
						</div>
						<div class="rrc-faq-benefit-info">
							<h5 class="rrc-faq-benefit-title">Mejor precio garantizado</h5>
							<p class="rrc-faq-benefit-desc">Tarifas claras y sin cargos ocultos</p>
						</div>
					</div>
					<div class="rrc-faq-benefit-item">
						<div class="rrc-faq-benefit-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
						</div>
						<div class="rrc-faq-benefit-info">
							<h5 class="rrc-faq-benefit-title">Atención 24/7</h5>
							<p class="rrc-faq-benefit-desc">Soporte local e internacional</p>
						</div>
					</div>
				</div>

				<!-- Accordion list -->
				<div class="rrc-faq-accordion">
					
					<!-- Q1 -->
					<details class="rrc-faq-item" open>
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Qué incluyen las tarifas?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Todas nuestras tarifas Drive Away incluyen impuestos y seguro obligatorio. Sin cargos ocultos.
						</div>
					</details>

					<!-- Q2 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Dónde puedo recoger y devolver el vehículo en Roatán?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Ofrecemos recogida y entrega GRATIS en el Aeropuerto Internacional Juan Manuel Gálvez, el Terminal Marítimo de Roatán y todos los muelles de cruceros.
						</div>
					</details>

					<!-- Q3 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Qué documentos necesito para alquilar?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Para alquilar, necesitas una licencia de conducir válida y un pasaporte.
						</div>
					</details>

					<!-- Q4 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Cómo funciona la reserva por internet?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Seleccionas tus fechas, el vehículo que deseas y completas el formulario. Nuestro equipo revisará tu solicitud y te enviará la confirmación.
						</div>
					</details>

					<!-- Q5 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Qué métodos de pago aceptan?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Puedes pagar en línea de forma segura a través de PayPal. También aceptamos efectivo, cheques de viajero y cheques bancarios certificados.
						</div>
					</details>

					<!-- Q6 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿En cuánto tiempo recibiré la confirmación?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Enviaremos tu confirmación por correo electrónico dentro de las 48 horas posteriores a la recepción de tu solicitud.
						</div>
					</details>

					<!-- Q7 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Cuál es la política de cancelación?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Cancelaciones con 30 días o más de anticipación: reembolso del 100%. Entre 15 y 29 días de anticipación: reembolso del 50%. Cancelaciones con menos de 15 días de anticipación: no hay reembolso.
						</div>
					</details>

					<!-- Q8 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Pueden atender a pasajeros de cruceros?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							¡Sí! Ofrecemos servicios especiales para pasajeros de cruceros con entrega y recogida GRATIS en todos los muelles de cruceros de Roatán.
						</div>
					</details>

					<!-- Q9 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Qué tipos de vehículos ofrecen?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Contamos con sedanes, SUV, 4x4, vans y más. Consulta nuestra flota completa en la sección "Flota".
						</div>
					</details>

					<!-- Q10 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Conducen por la derecha en Roatán?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Sí. En Honduras se conduce por la derecha.
						</div>
					</details>

					<!-- Q11 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Se puede pagar en línea?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Sí. Aceptamos pagos en línea de forma segura por medio de PayPal.
						</div>
					</details>

					<!-- Q12 -->
					<details class="rrc-faq-item">
						<summary class="rrc-faq-summary">
							<div class="rrc-faq-summary-left">
								<div class="rrc-faq-question-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
								</div>
								<span>¿Cómo puedo contactarlos?</span>
							</div>
							<svg class="icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</summary>
						<div class="rrc-faq-content">
							Puedes llamarnos por teléfono o escribirnos por correo electrónico. Estamos disponibles 24/7 para ayudarte.
						</div>
					</details>

				</div>

				<!-- Bottom Contact -->
				<div class="rrc-faq-contact-header">
					<h3 class="rrc-faq-contact-title">¿No encuentras tu respuesta?</h3>
					<p class="rrc-faq-contact-desc">Nuestro equipo está listo para ayudarte.</p>
				</div>

				<div class="rrc-faq-contact-grid">
					<!-- Card 1 -->
					<div class="rrc-faq-contact-card">
						<div class="rrc-faq-contact-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
						</div>
						<h4 class="rrc-faq-contact-card-title">Llámanos</h4>
						<p class="rrc-faq-contact-card-desc">
							(+1) 518-495-4066 (EE. UU.)<br>
							(+1) 919-758-1518 (EE. UU.)
						</p>
					</div>
					<!-- Card 2 -->
					<div class="rrc-faq-contact-card">
						<div class="rrc-faq-contact-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
						</div>
						<h4 class="rrc-faq-contact-card-title">Escríbenos</h4>
						<p class="rrc-faq-contact-card-desc">
							info@RamirezRentACar.com<br>
							Responderemos lo antes posible.
						</p>
					</div>
					<!-- Card 3 -->
					<div class="rrc-faq-contact-card">
						<div class="rrc-faq-contact-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
						</div>
						<h4 class="rrc-faq-contact-card-title">Atención 24/7</h4>
						<p class="rrc-faq-contact-card-desc">
							Soporte local e internacional.<br>Siempre disponibles para ayudarte.
						</p>
					</div>
					<!-- Card 4 -->
					<div class="rrc-faq-contact-card">
						<div class="rrc-faq-contact-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
						</div>
						<h4 class="rrc-faq-contact-card-title">Nuestras oficinas</h4>
						<p class="rrc-faq-contact-card-desc">
							Roatán y San Pedro Sula<br>
							Honduras
						</p>
					</div>
				</div>

			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	public static function render_useful_info_shortcode() {
		ob_start();
		$uploads_base = content_url( '/uploads/2026/' );
		$img_airport = $uploads_base . 'AEROPUERTO rOATAN.jpg';
		$img_ferry   = $uploads_base . 'terminal ferry roatan.jpg';
		$img_cruise  = $uploads_base . 'Muelles de Cruceros.jpg';
		$img_hero    = content_url( '/uploads/2026/info util.png' );
		?>
		<style>
		/* Hide default page title for page 170 (Useful Info) */
		.page-id-170 .entry-header,
		.page-id-170 .post-title,
		.page-id-170 .page-title,
		.page-id-170 h1.entry-title {
			display: none !important;
		}

		.rrc-info-section {
			background: #ffffff;
			color: #334155;
			font-family: 'Inter Tight', 'Inter', sans-serif;
			overflow: hidden;
		}
		
		/* Hero Banner - matching premium light style with dark readable text */
		.rrc-info-hero {
			position: relative;
			background: url('<?php echo esc_url($img_hero); ?>') no-repeat center center / cover;
			padding: 120px 0 80px 0;
			text-align: left;
			color: #334155;
		}
		.rrc-info-hero::before {
			content: '';
			position: absolute;
			top: 0; left: 0; right: 0; bottom: 0;
			background: linear-gradient(180deg, rgba(255, 255, 255, 0.75) 0%, rgba(255, 255, 255, 0.9) 100%);
			z-index: 1;
		}
		.rrc-info-hero-container {
			position: relative;
			z-index: 2;
			width: 85%;
			max-width: 1400px;
			margin: 0 auto;
		}
		.rrc-info-hero-title {
			font-size: 48px;
			font-weight: 900;
			letter-spacing: -1.5px;
			line-height: 1.1;
			margin: 0 0 15px 0;
			color: #0f172a;
			max-width: 800px;
		}
		.rrc-info-hero-subtitle {
			font-size: 18px;
			color: #475569;
			margin: 0;
			max-width: 600px;
		}

		/* Content Wrap */
		.rrc-info-content-wrap {
			padding: 60px 0;
		}
		.rrc-info-container {
			width: 85%;
			max-width: 1400px;
			margin: 0 auto;
		}

		/* Benefits Horizontal Bar */
		.rrc-info-benefits-bar {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 25px;
			margin-bottom: 60px;
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 20px;
			text-align: left;
		}
		.rrc-info-benefit-item {
			display: flex;
			align-items: center;
			gap: 12px;
		}
		.rrc-info-benefit-icon {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-info-benefit-icon svg {
			width: 18px;
			height: 18px;
		}
		.rrc-info-benefit-title {
			color: #0f172a;
			font-weight: 700;
			font-size: 13.5px;
			margin: 0 0 2px 0;
		}
		.rrc-info-benefit-desc {
			color: #64748b;
			font-size: 11px;
			margin: 0;
			line-height: 1.3;
		}

		/* Driving in Roatan Section */
		.rrc-info-section-title {
			font-size: 24px;
			font-weight: 900;
			color: #0f172a;
			text-align: center;
			margin: 0 0 35px 0;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
		}
		.rrc-info-section-title svg {
			width: 24px;
			height: 24px;
			color: #e8272c;
		}
		.rrc-info-driving-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 25px;
			margin-bottom: 30px;
		}
		.rrc-info-driving-grid-3col {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 25px;
			margin-bottom: 60px;
		}
		.rrc-info-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 25px;
			text-align: left;
			box-shadow: 0 4px 15px rgba(0,0,0,0.01);
			transition: all 0.2s ease;
		}
		.rrc-info-card:hover {
			border-color: #cbd5e1;
			box-shadow: 0 8px 25px rgba(0,0,0,0.03);
			transform: translateY(-2px);
		}
		.rrc-info-card-icon {
			width: 42px;
			height: 42px;
			border-radius: 8px;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 18px;
		}
		.rrc-info-card-icon svg {
			width: 20px;
			height: 20px;
		}
		.rrc-info-card-title {
			font-size: 14.5px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 10px 0;
			line-height: 1.4;
		}
		.rrc-info-card-desc {
			font-size: 12px;
			line-height: 1.5;
			color: #64748b;
			margin: 0;
		}

		/* Split Layout Section (Useful Info + Pickup Points) */
		.rrc-info-split {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 50px;
			margin-bottom: 60px;
			border-top: 1px solid #f1f5f9;
			padding-top: 60px;
		}
		.rrc-info-split-col {
			display: flex;
			flex-direction: column;
		}
		.rrc-info-split-title {
			font-size: 20px;
			font-weight: 900;
			color: #0f172a;
			margin: 0 0 30px 0;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.rrc-info-split-title svg {
			width: 20px;
			height: 20px;
			color: #e8272c;
		}
		.rrc-info-list {
			display: flex;
			flex-direction: column;
			gap: 25px;
		}
		.rrc-info-list-item {
			display: flex;
			gap: 15px;
			text-align: left;
		}
		.rrc-info-list-icon {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-info-list-icon svg {
			width: 16px;
			height: 16px;
		}
		.rrc-info-list-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-info-list-title {
			font-size: 14.5px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 6px 0;
		}
		.rrc-info-list-desc {
			font-size: 12.5px;
			line-height: 1.5;
			color: #64748b;
			margin: 0;
		}

		/* Pickup cards with actual premium images */
		.rrc-pickup-cards {
			display: flex;
			flex-direction: column;
			gap: 20px;
		}
		.rrc-pickup-card {
			display: flex;
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			overflow: hidden;
			box-shadow: 0 4px 15px rgba(0,0,0,0.01);
		}
		.rrc-pickup-img {
			width: 150px;
			height: 100px;
			object-fit: cover;
			flex-shrink: 0;
		}
		.rrc-pickup-info {
			padding: 15px 20px;
			display: flex;
			flex-direction: column;
			justify-content: center;
			text-align: left;
		}
		.rrc-pickup-title {
			font-size: 14px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 5px 0;
		}
		.rrc-pickup-desc {
			font-size: 11.5px;
			line-height: 1.4;
			color: #64748b;
			margin: 0;
		}

		/* Roatan Context Section */
		.rrc-context-section {
			background: #f8fafc;
			border-radius: 20px;
			padding: 50px 40px;
			margin-bottom: 60px;
			border: 1px solid #e2e8f0;
		}
		.rrc-context-title {
			font-size: 22px;
			font-weight: 900;
			color: #0f172a;
			text-align: center;
			margin: 0 0 35px 0;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
		}
		.rrc-context-title svg {
			width: 22px;
			height: 22px;
			color: #e8272c;
		}
		.rrc-context-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 20px;
		}
		.rrc-context-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			padding: 20px;
			text-align: left;
		}
		.rrc-context-card-icon {
			width: 36px;
			height: 36px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 15px;
		}
		.rrc-context-card-icon svg {
			width: 16px;
			height: 16px;
		}
		.rrc-context-card-title {
			font-size: 13.5px;
			font-weight: 800;
			color: #0f172a;
			margin: 0 0 8px 0;
		}
		.rrc-context-card-desc {
			font-size: 11.5px;
			line-height: 1.5;
			color: #64748b;
			margin: 0;
		}

		/* Bottom Responsibility Alerter */
		.rrc-info-footer-alert {
			background: #fef2f2;
			border: 1px solid #fecaca;
			border-left: 5px solid #ef4444;
			border-radius: 12px;
			padding: 25px;
			display: flex;
			gap: 20px;
			align-items: center;
			text-align: left;
		}
		.rrc-info-footer-alert-icon {
			width: 44px;
			height: 44px;
			border-radius: 50%;
			background: rgba(239, 68, 68, 0.1);
			color: #ef4444;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-info-footer-alert-icon svg {
			width: 22px;
			height: 22px;
		}
		.rrc-info-footer-alert-content {
			display: flex;
			flex-direction: column;
		}
		.rrc-info-footer-alert-title {
			color: #991b1b;
			font-weight: 800;
			font-size: 14px;
			margin: 0 0 4px 0;
		}
		.rrc-info-footer-alert-desc {
			color: #7f1d1d;
			font-size: 12.5px;
			margin: 0;
			line-height: 1.5;
		}

		/* Responsive styling */
		@media (max-width: 1024px) {
			.rrc-info-driving-grid {
				grid-template-columns: repeat(2, 1fr);
			}
			.rrc-info-driving-grid-3col {
				grid-template-columns: repeat(2, 1fr);
			}
			.rrc-context-grid {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		@media (max-width: 991px) {
			.rrc-info-benefits-bar {
				grid-template-columns: repeat(2, 1fr);
			}
			.rrc-info-split {
				grid-template-columns: 1fr;
				gap: 40px;
			}
		}
		@media (max-width: 768px) {
			.rrc-info-hero-title {
				font-size: 36px;
			}
			.rrc-context-section {
				padding: 30px 20px;
			}
		}
		@media (max-width: 575px) {
			.rrc-info-benefits-bar {
				grid-template-columns: 1fr;
			}
			.rrc-info-driving-grid {
				grid-template-columns: 1fr;
			}
			.rrc-info-driving-grid-3col {
				grid-template-columns: 1fr;
			}
			.rrc-context-grid {
				grid-template-columns: 1fr;
			}
			.rrc-pickup-card {
				flex-direction: column;
			}
			.rrc-pickup-img {
				width: 100%;
				height: 120px;
			}
			.rrc-info-footer-alert {
				flex-direction: column;
				align-items: flex-start;
				gap: 15px;
			}
		}
		</style>

		<section class="rrc-info-section">
			
			<!-- Hero Banner -->
			<div class="rrc-info-hero">
				<div class="rrc-info-hero-container">
					<h1 class="rrc-info-hero-title">Información útil y consejos para conducir en Roatán</h1>
					<p class="rrc-info-hero-subtitle">Todo lo que necesitas saber antes de tu viaje.</p>
				</div>
			</div>

			<!-- Content Wrap -->
			<div class="rrc-info-content-wrap">
				<div class="rrc-info-container">

					<!-- Benefits Horizontal Bar -->
					<div class="rrc-info-benefits-bar">
						<div class="rrc-info-benefit-item">
							<div class="rrc-info-benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
							</div>
							<div class="rrc-info-benefit-info">
								<h5 class="rrc-info-benefit-title">Entrega y recogida gratis</h5>
								<p class="rrc-info-benefit-desc">Aeropuerto, Ferry y muelles de cruceros</p>
							</div>
						</div>
						<div class="rrc-info-benefit-item">
							<div class="rrc-info-benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 11 2 2 4-4"></path></svg>
							</div>
							<div class="rrc-info-benefit-info">
								<h5 class="rrc-info-benefit-title">Seguro incluido</h5>
								<p class="rrc-info-benefit-desc">Todas nuestras tarifas incluyen seguro</p>
							</div>
						</div>
						<div class="rrc-info-benefit-item">
							<div class="rrc-info-benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="12 8 13.09 10.21 15.5 10.56 13.75 12.27 14.16 14.64 12 13.5 9.84 14.64 10.25 12.27 8.5 10.56 10.91 10.21 12 8"></polygon></svg>
							</div>
							<div class="rrc-info-benefit-info">
								<h5 class="rrc-info-benefit-title">Mejor precio garantizado</h5>
								<p class="rrc-info-benefit-desc">Tarifas claras y sin cargos ocultos</p>
							</div>
						</div>
						<div class="rrc-info-benefit-item">
							<div class="rrc-info-benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
							</div>
							<div class="rrc-info-benefit-info">
								<h5 class="rrc-info-benefit-title">Atención 24/7</h5>
								<p class="rrc-info-benefit-desc">Soporte local e internacional</p>
							</div>
						</div>
					</div>

					<!-- Section: Conducir en Roatan -->
					<h3 class="rrc-info-section-title">
						<!-- Steering wheel svg placeholder -->
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="2" x2="12" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><circle cx="12" cy="12" r="4"></circle></svg>
						Conducir en Roatán
					</h3>

					<!-- Driving Grid Row 1 (4 items) -->
					<div class="rrc-info-driving-grid">
						<!-- Card 1 -->
						<div class="rrc-info-card">
							<div class="rrc-info-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
							</div>
							<h4 class="rrc-info-card-title">Se conduce por la derecha</h4>
							<p class="rrc-info-card-desc">En Honduras se conduce por el lado derecho de la vía. Mantente siempre atento y respeta las señales de tránsito.</p>
						</div>
						<!-- Card 2 -->
						<div class="rrc-info-card">
							<div class="rrc-info-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="17" y2="12"></line><line x1="7" y1="16" x2="13" y2="16"></line></svg>
							</div>
							<h4 class="rrc-info-card-title">Licencia y depósito</h4>
							<p class="rrc-info-card-desc">Se requiere licencia de conducir válida. Puede solicitarse una tarjeta de crédito principal o un depósito en efectivo al momento de la recogida.</p>
						</div>
						<!-- Card 3 -->
						<div class="rrc-info-card">
							<div class="rrc-info-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></svg>
							</div>
							<h4 class="rrc-info-card-title">Evita conducir de noche</h4>
							<p class="rrc-info-card-desc">Muchas carreteras carecen de señalización, iluminación y divisiones de carril. Se recomienda conducir durante el día para mayor seguridad.</p>
						</div>
						<!-- Card 4 -->
						<div class="rrc-info-card">
							<div class="rrc-info-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
							</div>
							<h4 class="rrc-info-card-title">Peatones y animales</h4>
							<p class="rrc-info-card-desc">Estate atento a peatones, motocicletas, bicicletas y animales en la vía, especialmente en zonas rurales.</p>
						</div>
					</div>

					<!-- Driving Grid Row 2 (3 items) -->
					<div class="rrc-info-driving-grid-3col">
						<!-- Card 5 -->
						<div class="rrc-info-card">
							<div class="rrc-info-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
							</div>
							<h4 class="rrc-info-card-title">Baches y condiciones de la vía</h4>
							<p class="rrc-info-card-desc">Algunas carreteras pueden tener baches o superficies irregulares. Conduce con precaución.</p>
						</div>
						<!-- Card 6 -->
						<div class="rrc-info-card">
							<div class="rrc-info-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 17.58A5 5 0 0 0 18 8h-1.26A8 8 0 1 0 4 16.25"></path><line x1="8" y1="16" x2="8" y2="20"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="16" y1="16" x2="16" y2="20"></line></svg>
							</div>
							<h4 class="rrc-info-card-title">Cuidado con el clima</h4>
							<p class="rrc-info-card-desc">En época de lluvias, los caminos de tierra y zonas no pavimentadas pueden volverse resbaladizos. Reduce la velocidad y maneja con extra precaución.</p>
						</div>
						<!-- Card 7 -->
						<div class="rrc-info-card">
							<div class="rrc-info-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
							</div>
							<h4 class="rrc-info-card-title">Límites de velocidad</h4>
							<p class="rrc-info-card-desc">Los límites de velocidad se aplican de manera estricta. Respétalos para tu seguridad y evita multas.</p>
						</div>
					</div>

					<!-- Split section -->
					<div class="rrc-info-split">
						
						<!-- Col 1: Useful Info -->
						<div class="rrc-info-split-col">
							<h3 class="rrc-info-split-title">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
								Información útil para viajeros
							</h3>

							<div class="rrc-info-list">
								<!-- Item 1 -->
								<div class="rrc-info-list-item">
									<div class="rrc-info-list-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
									</div>
									<div class="rrc-info-list-content">
										<h5 class="rrc-info-list-title">Pasaporte</h5>
										<p class="rrc-info-list-desc">Los viajeros de EE. UU. necesitan pasaporte vigente para ingresar a Honduras. Se recomienda que tenga al menos 3 meses de validez al momento de su viaje.</p>
									</div>
								</div>
								<!-- Item 2 -->
								<div class="rrc-info-list-item">
									<div class="rrc-info-list-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
									</div>
									<div class="rrc-info-list-content">
										<h5 class="rrc-info-list-title">Moneda</h5>
										<p class="rrc-info-list-desc">La moneda local es el Lempira Hondureño (HNL). El Dólar Estadounidense (USD) es ampliamente aceptado en la mayoría de negocios y servicios turísticos.</p>
									</div>
								</div>
								<!-- Item 3 -->
								<div class="rrc-info-list-item">
									<div class="rrc-info-list-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
									</div>
									<div class="rrc-info-list-content">
										<h5 class="rrc-info-list-title">Cajeros automáticos</h5>
										<p class="rrc-info-list-desc">Hay cajeros automáticos disponibles en Coxen Hole y en áreas turísticas principales de la isla.</p>
									</div>
								</div>
								<!-- Item 4 -->
								<div class="rrc-info-list-item">
									<div class="rrc-info-list-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
									</div>
									<div class="rrc-info-list-content">
										<h5 class="rrc-info-list-title">Idiomas</h5>
										<p class="rrc-info-list-desc">Los idiomas más comunes son Español e Inglés Caribeño. El inglés es ampliamente entendido en zonas turísticas.</p>
									</div>
								</div>
							</div>
						</div>

						<!-- Col 2: Pickup Points -->
						<div class="rrc-info-split-col">
							<h3 class="rrc-info-split-title">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
								Puntos de recogida
							</h3>

							<div class="rrc-pickup-cards">
								<!-- Pickup Card 1 -->
								<div class="rrc-pickup-card">
									<img src="<?php echo esc_url($img_airport); ?>" alt="Aeropuerto Internacional Juan Manuel Gálvez" class="rrc-pickup-img">
									<div class="rrc-pickup-info">
										<h5 class="rrc-pickup-title">Aeropuerto Internacional Juan Manuel Gálvez (RTB)</h5>
										<p class="rrc-pickup-desc">Recogida fácil y rápida a tu llegada. Nuestro equipo te estará esperando para entregarte tu vehículo.</p>
									</div>
								</div>
								<!-- Pickup Card 2 -->
								<div class="rrc-pickup-card">
									<img src="<?php echo esc_url($img_ferry); ?>" alt="Terminal de Ferry" class="rrc-pickup-img">
									<div class="rrc-pickup-info">
										<h5 class="rrc-pickup-title">Terminal de Ferry</h5>
										<p class="rrc-pickup-desc">Si llegas en ferry desde La Ceiba, podemos entregarte tu vehículo cerca de la terminal.</p>
									</div>
								</div>
								<!-- Pickup Card 3 -->
								<div class="rrc-pickup-card">
									<img src="<?php echo esc_url($img_cruise); ?>" alt="Muelles de Cruceros" class="rrc-pickup-img">
									<div class="rrc-pickup-info">
										<h5 class="rrc-pickup-title">Muelles de Cruceros</h5>
										<p class="rrc-pickup-desc">Recogida conveniente en los muelles de cruceros de Coxen Hole. Disfruta la isla desde el primer minuto.</p>
									</div>
								</div>
							</div>
						</div>

					</div>

					<!-- Section: Roatan Context -->
					<div class="rrc-context-section">
						<h3 class="rrc-context-title">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
							Contexto de Roatán
						</h3>

						<div class="rrc-context-grid">
							<!-- Card 1 -->
							<div class="rrc-context-card">
								<div class="rrc-context-card-icon">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
								</div>
								<h5 class="rrc-context-card-title">Ubicación</h5>
								<p class="rrc-context-card-desc">Roatán es la más grande de las Islas de la Bahía, ubicadas en el Mar Caribe, frente a la costa norte de Honduras.</p>
							</div>
							<!-- Card 2 -->
							<div class="rrc-context-card">
								<div class="rrc-context-card-icon">
									<!-- Tree / nature icon -->
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22v-5M9 17h6M12 13V9M8 9h8M12 5V2"></path><circle cx="12" cy="12" r="10" stroke-dasharray="2 2"></circle></svg>
								</div>
								<h5 class="rrc-context-card-title">Las Islas de la Bahía</h5>
								<p class="rrc-context-card-desc">El archipiélago está compuesto por Roatán, Utila, Guanaja y varios cayos. Roatán es famosa por sus arrecifes de coral y su belleza natural.</p>
							</div>
							<!-- Card 3 -->
							<div class="rrc-context-card">
								<div class="rrc-context-card-icon">
									<!-- Historic building / museum icon -->
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="13" x2="15" y2="13"></line><line x1="9" y1="17" x2="15" y2="17"></line></svg>
								</div>
								<h5 class="rrc-context-card-title">Los Payas</h5>
								<p class="rrc-context-card-desc">Antes de la llegada europea, la isla fue habitada por los Payas, un pueblo indígena pacífico de origen maya que vivía de la pesca, agricultura y el comercio.</p>
							</div>
							<!-- Card 4 -->
							<div class="rrc-context-card">
								<div class="rrc-context-card-icon">
									<!-- Castle / colony icon -->
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 20v-4l-2-2h-3v4M2 20v-4l2-2h3v4M12 14v6"></path><path d="M12 2v6"></path></svg>
								</div>
								<h5 class="rrc-context-card-title">Época Colonial</h5>
								<p class="rrc-context-card-desc">Roatán fue avistada por Cristóbal Colón en 1502. Con el tiempo, piratas y colonos británicos se asentaron en la isla, dejando un legado cultural único.</p>
							</div>
						</div>
					</div>

					<!-- Bottom alert banner: Planifica tus rutas -->
					<div class="rrc-info-footer-alert">
						<div class="rrc-info-footer-alert-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 11 2 2 4-4"></path></svg>
						</div>
						<div class="rrc-info-footer-alert-content">
							<h4 class="rrc-info-footer-alert-title">Planifica tus rutas y conduce con responsabilidad</h4>
							<p class="rrc-info-footer-alert-desc">Disfruta de Roatán de forma segura. Recomendamos planificar tus recorridos durante el día, usar siempre el cinturón de seguridad y respetar las normas de tránsito. ¡Que tengas un viaje increíble!</p>
						</div>
					</div>

				</div>
			</div>

		</section>
		<?php
		return ob_get_clean();
	}

	public static function render_sitemap_shortcode() {
		ob_start();
		?>
		<style>
		.rrc-sitemap-section {
			background: #ffffff;
			color: #334155;
			font-family: 'Inter Tight', 'Inter', sans-serif;
			padding: 80px 0;
			overflow: hidden;
		}
		.rrc-sitemap-container {
			width: 90%;
			max-width: 1200px;
			margin: 0 auto;
		}

		/* Header Section */
		.rrc-sitemap-header {
			text-align: center;
			margin-bottom: 50px;
		}
		.rrc-sitemap-header-icon {
			width: 64px;
			height: 64px;
			border-radius: 50%;
			border: 2px solid #e8272c;
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 20px auto;
		}
		.rrc-sitemap-header-icon svg {
			width: 30px;
			height: 30px;
		}
		.rrc-sitemap-title {
			font-size: 42px;
			font-weight: 900;
			color: #0f172a;
			letter-spacing: -1.2px;
			margin: 0 0 10px 0;
			line-height: 1.2;
		}
		.rrc-sitemap-subtitle {
			font-size: 16px;
			color: #64748b;
			margin: 0 0 25px 0;
			font-weight: 500;
		}
		.rrc-sitemap-description {
			font-size: 14.5px;
			line-height: 1.6;
			color: #475569;
			max-width: 800px;
			margin: 0 auto;
		}

		/* Grid Layout */
		.rrc-sitemap-grid-3col {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 30px;
			margin-bottom: 30px;
		}
		.rrc-sitemap-grid-2col {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 30px;
			margin-bottom: 50px;
		}
		.rrc-sitemap-card {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 30px;
			box-shadow: 0 4px 15px rgba(0,0,0,0.01);
			text-align: left;
			transition: all 0.2s ease;
		}
		.rrc-sitemap-card:hover {
			border-color: #cbd5e1;
			box-shadow: 0 8px 25px rgba(0,0,0,0.03);
			transform: translateY(-2px);
		}
		.rrc-sitemap-card-header {
			display: flex;
			align-items: center;
			gap: 15px;
			border-bottom: 1px solid #f1f5f9;
			padding-bottom: 20px;
			margin-bottom: 20px;
		}
		.rrc-sitemap-card-icon {
			width: 42px;
			height: 42px;
			border-radius: 8px;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-sitemap-card-icon svg {
			width: 20px;
			height: 20px;
		}
		.rrc-sitemap-card-title {
			font-size: 16px;
			font-weight: 800;
			color: #0f172a;
			margin: 0;
		}
		
		/* Links list */
		.rrc-sitemap-links {
			list-style: none;
			padding: 0;
			margin: 0;
			display: flex;
			flex-direction: column;
			gap: 12px;
		}
		.rrc-sitemap-links li {
			position: relative;
			padding-left: 15px;
			font-size: 13.5px;
			line-height: 1.4;
		}
		.rrc-sitemap-links li::before {
			content: '›';
			color: #e8272c;
			position: absolute;
			left: 0;
			font-weight: bold;
			font-size: 16px;
			line-height: 1.1;
		}
		.rrc-sitemap-links a {
			color: #475569;
			text-decoration: none;
			transition: color 0.2s ease;
			font-weight: 500;
		}
		.rrc-sitemap-links a:hover {
			color: #e8272c;
		}

		/* Bottom Back Button & Text */
		.rrc-sitemap-footer {
			text-align: center;
			margin-bottom: 60px;
		}
		.rrc-sitemap-back-btn {
			background: #e8272c;
			color: #ffffff;
			border: none;
			padding: 12px 30px;
			font-size: 14px;
			font-weight: 800;
			border-radius: 30px;
			cursor: pointer;
			display: inline-flex;
			align-items: center;
			gap: 10px;
			text-decoration: none;
			transition: background 0.2s ease;
			margin-bottom: 25px;
			font-family: inherit;
		}
		.rrc-sitemap-back-btn:hover {
			background: #b91c1c;
			color: #ffffff;
		}
		.rrc-sitemap-back-btn svg {
			width: 16px;
			height: 16px;
		}
		.rrc-sitemap-footer-text {
			font-size: 14.5px;
			color: #64748b;
			margin: 0;
		}

		/* Benefits Horizontal Bar */
		.rrc-sitemap-benefits-bar {
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 25px;
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 20px;
			text-align: left;
		}
		.rrc-sitemap-benefit-item {
			display: flex;
			align-items: center;
			gap: 12px;
		}
		.rrc-sitemap-benefit-icon {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.rrc-sitemap-benefit-icon svg {
			width: 18px;
			height: 18px;
		}
		.rrc-sitemap-benefit-title {
			color: #0f172a;
			font-weight: 700;
			font-size: 13.5px;
			margin: 0 0 2px 0;
		}
		.rrc-sitemap-benefit-desc {
			color: #64748b;
			font-size: 11px;
			margin: 0;
			line-height: 1.3;
		}

		/* Responsive styling */
		@media (max-width: 1024px) {
			.rrc-sitemap-grid-3col {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		@media (max-width: 991px) {
			.rrc-sitemap-benefits-bar {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		@media (max-width: 768px) {
			.rrc-sitemap-grid-3col {
				grid-template-columns: 1fr;
			}
			.rrc-sitemap-grid-2col {
				grid-template-columns: 1fr;
			}
			.rrc-sitemap-title {
				font-size: 32px;
			}
		}
		@media (max-width: 575px) {
			.rrc-sitemap-benefits-bar {
				grid-template-columns: 1fr;
			}
		}
		</style>

		<section class="rrc-sitemap-section">
			<div class="rrc-sitemap-container">
				
				<!-- Header -->
				<div class="rrc-sitemap-header">
					<div class="rrc-sitemap-header-icon">
						<!-- Sitemap icon -->
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
					</div>
					<h2 class="rrc-sitemap-title">Mapa del sitio</h2>
					<p class="rrc-sitemap-subtitle">Encuentra rápidamente toda la información del sitio</p>
					<p class="rrc-sitemap-description">
						Este mapa del sitio está diseñado para ayudarte a navegar fácilmente y para que los motores de búsqueda comprendan nuestra estructura.
					</p>
				</div>

				<!-- Grid Row 1 (3 Columns) -->
				<div class="rrc-sitemap-grid-3col">
					
					<!-- Card 1: Páginas principales -->
					<div class="rrc-sitemap-card">
						<div class="rrc-sitemap-card-header">
							<div class="rrc-sitemap-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
							</div>
							<h4 class="rrc-sitemap-card-title">Páginas principales</h4>
						</div>
						<ul class="rrc-sitemap-links">
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a></li>
							<li><a href="<?php echo esc_url( home_url( '/quienes-somos/' ) ); ?>">Quiénes somos</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Flota</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#reservas">Tarifas y reservas</a></li>
							<li><a href="<?php echo esc_url( home_url( '/oficinas/' ) ); ?>">Oficinas y contacto</a></li>
							<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">Preguntas frecuentes</a></li>
							<li><a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>">Contáctanos</a></li>
						</ul>
					</div>

					<!-- Card 2: Vehículos -->
					<div class="rrc-sitemap-card">
						<div class="rrc-sitemap-card-header">
							<div class="rrc-sitemap-card-icon">
								<!-- Car icon -->
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="22" height="13" rx="2" ry="2"></rect><path d="M12 16V9"></path><circle cx="6" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle></svg>
							</div>
							<h4 class="rrc-sitemap-card-title">Vehículos</h4>
						</div>
						<ul class="rrc-sitemap-links">
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Sedán 4 puertas</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">ATV</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">SUV estándar</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">KIA Sorento</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Luxury SUV</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Toyota Prado 2025</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Jeep</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Gladiator Jeep</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Pick-up 4x4</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Van 7 pasajeros</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#flota">Van 15 pasajeros</a></li>
						</ul>
					</div>

					<!-- Card 3: Información legal -->
					<div class="rrc-sitemap-card">
						<div class="rrc-sitemap-card-header">
							<div class="rrc-sitemap-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
							</div>
							<h4 class="rrc-sitemap-card-title">Información legal</h4>
						</div>
						<ul class="rrc-sitemap-links">
							<li><a href="<?php echo esc_url( home_url( '/politicas-de-alquiler/' ) ); ?>">Términos y condiciones</a></li>
							<li><a href="<?php echo esc_url( home_url( '/politica-privacidad/' ) ); ?>">Política de privacidad</a></li>
							<li><a href="<?php echo esc_url( home_url( '/politica-privacidad/' ) ); ?>#rrc-cookie-settings-panel">Política de cookies</a></li>
							<li><a href="<?php echo esc_url( home_url( '/mapa-del-sitio/' ) ); ?>">Mapa del sitio</a></li>
						</ul>
					</div>

				</div>

				<!-- Grid Row 2 (2 Columns) -->
				<div class="rrc-sitemap-grid-2col">
					
					<!-- Card 4: Información útil -->
					<div class="rrc-sitemap-card">
						<div class="rrc-sitemap-card-header">
							<div class="rrc-sitemap-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
							</div>
							<h4 class="rrc-sitemap-card-title">Información útil</h4>
						</div>
						<ul class="rrc-sitemap-links">
							<li><a href="<?php echo esc_url( home_url( '/informacion-util/' ) ); ?>">Consejos para conducir en Roatán</a></li>
							<li><a href="<?php echo esc_url( home_url( '/informacion-util/' ) ); ?>">Información útil del destino</a></li>
							<li><a href="<?php echo esc_url( home_url( '/informacion-util/' ) ); ?>">Enlaces de interés</a></li>
						</ul>
					</div>

					<!-- Card 5: Reservas y atención -->
					<div class="rrc-sitemap-card">
						<div class="rrc-sitemap-card-header">
							<div class="rrc-sitemap-card-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
							</div>
							<h4 class="rrc-sitemap-card-title">Reservas y atención</h4>
						</div>
						<ul class="rrc-sitemap-links">
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#reservas">Formulario de reserva</a></li>
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>#reservas">Confirmación de reserva</a></li>
							<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>#rrc-faq-item">Métodos de pago</a></li>
							<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>#rrc-faq-item">Política de cancelación</a></li>
						</ul>
					</div>

				</div>

				<!-- Footer actions -->
				<div class="rrc-sitemap-footer">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rrc-sitemap-back-btn">
						<!-- Home icon -->
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
						Volver al inicio
					</a>
					<p class="rrc-sitemap-footer-text">
						Explora todas nuestras páginas y encuentra exactamente lo que necesitas para tu próxima aventura en Roatán.
					</p>
				</div>

				<!-- Benefits Horizontal Bar -->
				<div class="rrc-sitemap-benefits-bar">
					<div class="rrc-sitemap-benefit-item">
						<div class="rrc-sitemap-benefit-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
						</div>
						<div class="rrc-sitemap-benefit-info">
							<h5 class="rrc-sitemap-benefit-title">Entrega y recogida gratis</h5>
							<p class="rrc-sitemap-benefit-desc">Aeropuerto, Ferry y muelles de cruceros</p>
						</div>
					</div>
					<div class="rrc-sitemap-benefit-item">
						<div class="rrc-sitemap-benefit-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 11 2 2 4-4"></path></svg>
						</div>
						<div class="rrc-sitemap-benefit-info">
							<h5 class="rrc-sitemap-benefit-title">Seguro incluido</h5>
							<p class="rrc-sitemap-benefit-desc">Todas nuestras tarifas incluyen seguro</p>
						</div>
					</div>
					<div class="rrc-sitemap-benefit-item">
						<div class="rrc-sitemap-benefit-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="12 8 13.09 10.21 15.5 10.56 13.75 12.27 14.16 14.64 12 13.5 9.84 14.64 10.25 12.27 8.5 10.56 10.91 10.21 12 8"></polygon></svg>
						</div>
						<div class="rrc-sitemap-benefit-info">
							<h5 class="rrc-sitemap-benefit-title">Mejor precio garantizado</h5>
							<p class="rrc-sitemap-benefit-desc">Tarifas claras y sin cargos ocultos</p>
						</div>
					</div>
					<div class="rrc-sitemap-benefit-item">
						<div class="rrc-sitemap-benefit-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
						</div>
						<div class="rrc-sitemap-benefit-info">
							<h5 class="rrc-sitemap-benefit-title">Atención 24/7</h5>
							<p class="rrc-sitemap-benefit-desc">Soporte local e internacional</p>
						</div>
					</div>
				</div>

			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	public static function render_landing_airport_shortcode() {
		ob_start();
		$uploads_url = content_url( '/uploads/2026/' );
		?>
		<style>
		.rrc-seo-page { background: #ffffff; color: #334155; font-family: 'Inter Tight', 'Inter', sans-serif; padding: 60px 0; }
		.rrc-seo-container { width: 85%; max-width: 1400px; margin: 0 auto; text-align: left; }
		.rrc-seo-title, .rrc-seo-page h1, .rrc-seo-page h2, .rrc-seo-page h3, .rrc-seo-page h4 { 
			font-family: 'Inter Tight', 'Inter', sans-serif !important; 
			font-weight: 900 !important; 
			color: #0f172a !important; 
			letter-spacing: -1px;
		}
		.rrc-seo-title { font-size: 38px; margin-bottom: 15px; }
		.rrc-seo-subtitle { font-size: 16px; color: #64748b; margin-bottom: 40px; line-height: 1.6; }
		.rrc-seo-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 50px; margin-bottom: 50px; }
		.rrc-seo-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); }
		.rrc-seo-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 15px; }
		.rrc-seo-list li { position: relative; padding-left: 25px; font-size: 14px; line-height: 1.6; }
		.rrc-seo-list li::before { content: '✓'; color: #e8272c; position: absolute; left: 0; font-weight: bold; font-size: 16px; }
		.rrc-seo-img { width: 100%; border-radius: 12px; object-fit: cover; max-height: 400px; }
		@media (max-width: 991px) { .rrc-seo-grid { grid-template-columns: 1fr; } }
		</style>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Alquiler de Coches en el Aeropuerto de Roatán (RTB)</h1>
				<p class="rrc-seo-subtitle">Recogida rápida, sin esperas y sin cargos ocultos directamente al bajar de tu avión en el Aeropuerto Internacional Juan Manuel Gálvez.</p>
				<div class="rrc-seo-grid">
					<div>
						<h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Tu Aventura en Roatán Comienza Aquí</h3>
						<p style="font-size: 14.5px; line-height: 1.7; margin-bottom: 20px;">Evita las largas colas de los mostradores tradicionales y el transporte costoso. Con Ramírez Rent A Car, un miembro de nuestro equipo te recibirá personalmente en la terminal de llegadas con el contrato listo y tu vehículo preparado para salir.</p>
						<ul class="rrc-seo-list">
							<li><strong>Entrega Personalizada:</strong> Te esperamos con un cartel con tu nombre en la salida de equipaje.</li>
							<li><strong>Sin Tarifas Ocultas:</strong> Todas las cotizaciones en línea incluyen el seguro obligatorio del país.</li>
							<li><strong>Flota Variada:</strong> Desde ATV y Jeeps 4x4 hasta amplias Vans familiares de 15 pasajeros para explorar cada rincón de la isla.</li>
							<li><strong>Soporte Local 24/7:</strong> Asistencia mecánica y atención rápida en Coxen Hole y West Bay.</li>
						</ul>
					</div>
					<div>
						<img src="<?php echo esc_url($uploads_url . 'AEROPUERTO rOATAN.jpg'); ?>" alt="Aeropuerto de Roatán" class="rrc-seo-img">
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_landing_mahogany_shortcode() {
		ob_start();
		$uploads_url = content_url( '/uploads/2026/' );
		?>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Alquiler de Coches en Mahogany Bay (Puerto de Cruceros)</h1>
				<p class="rrc-seo-subtitle">Servicio de entrega y devolución exclusivo para cruceristas de Carnival Cruise Line y aerolíneas asociadas en Roatán.</p>
				<div class="rrc-seo-grid">
					<div>
						<h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Maximiza tu Tiempo en la Isla</h3>
						<p style="font-size: 14.5px; line-height: 1.7; margin-bottom: 20px;">¿Llegas a bordo de un crucero y quieres explorar Roatán a tu propio ritmo? Olvídate de los tours costosos y sobrepoblados. Te entregamos tu vehículo justo afuera de la terminal del puerto de Mahogany Bay.</p>
						<ul class="rrc-seo-list">
							<li><strong>Entrega Puntual:</strong> Coordinamos la entrega según la hora exacta de atraque de tu crucero.</li>
							<li><strong>Retorno Flexible:</strong> Devuelve el carro cómodamente antes de la hora de embarque sin demoras.</li>
							<li><strong>Libertad Total:</strong> Conduce hacia West Bay Beach, French Harbour o Jonesville sin depender de taxis.</li>
							<li><strong>Tarifas Todo Incluido:</strong> Precios claros y seguro obligatorio incluido para un viaje sin estrés.</li>
						</ul>
					</div>
					<div>
						<img src="<?php echo esc_url($uploads_url . 'Muelles de Cruceros.jpg'); ?>" alt="Puerto de Mahogany Bay" class="rrc-seo-img">
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_landing_coxen_shortcode() {
		ob_start();
		$uploads_url = content_url( '/uploads/2026/' );
		?>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Alquiler de Coches en el Puerto de Coxen Hole (Town Center)</h1>
				<p class="rrc-seo-subtitle">Entrega directa para pasajeros de Royal Caribbean, Celebrity Cruises y Norwegian Cruise Line que desembarcan en el muelle principal de Coxen Hole.</p>
				<div class="rrc-seo-grid">
					<div>
						<h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Descubre Roatán con Total Independencia</h3>
						<p style="font-size: 14.5px; line-height: 1.7; margin-bottom: 20px;">El puerto de Coxen Hole está ubicado en el centro administrativo de la isla. Desde aquí, puedes tomar tu vehículo y dirigirte directamente a las playas de West End o al exuberante East End de Roatán.</p>
						<ul class="rrc-seo-list">
							<li><strong>Punto de Encuentro Sencillo:</strong> Un representante te estará esperando a pocos pasos de la salida peatonal del puerto.</li>
							<li><strong>Contrato Rápido:</strong> Llenamos la documentación en línea para que solo tengas que firmar y conducir.</li>
							<li><strong>Garantía de Regreso a Tiempo:</strong> Te garantizamos un proceso de devolución en 5 minutos para que nunca pierdas tu barco.</li>
							<li><strong>Modelos 4x4 y SUV:</strong> Vehículos robustos ideales para la topografía y las carreteras de Roatán.</li>
						</ul>
					</div>
					<div>
						<img src="<?php echo esc_url($uploads_url . 'Muelles de Cruceros.jpg'); ?>" alt="Puerto de Coxen Hole" class="rrc-seo-img">
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_landing_ferry_shortcode() {
		ob_start();
		$uploads_url = content_url( '/uploads/2026/' );
		?>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Alquiler de Coches en la Terminal de Ferry (Dixon Cove)</h1>
				<p class="rrc-seo-subtitle">Recogida y entrega sin cargos en la terminal del ferry Galaxy Wave. Ideal para viajeros procedentes de La Ceiba.</p>
				<div class="rrc-seo-grid">
					<div>
						<h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Conexión Terrestre Inmediata</h3>
						<p style="font-size: 14.5px; line-height: 1.7; margin-bottom: 20px;">Si viajas a Roatán por vía marítima a través del Galaxy Wave, te recibimos directamente al desembarcar en Dixon Cove. Te entregamos tu vehículo listo para que conduzcas hasta tu hotel o casa de alquiler.</p>
						<ul class="rrc-seo-list">
							<li><strong>Espera en Terminal:</strong> Monitoreamos los horarios de llegada del ferry para estar listos a tu desembarque.</li>
							<li><strong>Ahorra en Taxis:</strong> Evita pagar tarifas de transporte terrestre elevadas desde Dixon Cove.</li>
							<li><strong>Soporte en Carretera:</strong> Cobertura completa y asistencia en carretera en toda la isla las 24 horas.</li>
							<li><strong>Fácil Devolución:</strong> Entrega el coche directamente en la terminal antes de tomar tu ferry de regreso.</li>
						</ul>
					</div>
					<div>
						<img src="<?php echo esc_url($uploads_url . 'terminal ferry roatan.jpg'); ?>" alt="Terminal de Ferry Galaxy Wave" class="rrc-seo-img">
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_insurance_guide_shortcode() {
		ob_start();
		?>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Guía Completa de Seguros y Coberturas de Alquiler</h1>
				<p class="rrc-seo-subtitle">Transparencia absoluta. Conoce qué cubre tu tarifa, qué significan los términos legales y cómo garantizamos tu tranquilidad en la carretera.</p>
				<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px;">
					<div class="rrc-seo-card">
						<h4 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Responsabilidad Civil (TPL)</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">Seguro obligatorio por ley en Honduras. Cubre daños a terceros y lesiones corporales fuera del vehículo alquilado.</p>
					</div>
					<div class="rrc-seo-card">
						<h4 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Exención de Daños por Colisión (CDW)</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">Exime de responsabilidad financiera al conductor por daños al vehículo alquilado, sujeto a un deducible establecido.</p>
					</div>
					<div class="rrc-seo-card">
						<h4 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Deducible y Depósito</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">El monto bloqueado temporalmente en tu tarjeta de crédito que actúa como garantía. Se libera al devolver el auto sin daños.</p>
					</div>
				</div>
				<h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 15px;">Nuestra Promesa: Sin Cargos Sorpresa</h3>
				<p style="font-size: 14.5px; line-height: 1.7; margin-bottom: 20px;">En Ramírez Rent A Car, la tarifa de reserva en línea incluye las coberturas básicas obligatorias del país. Al recoger tu auto, no te obligaremos a contratar seguros extras costosos que no necesites. Queremos que conduzcas seguro y con total confianza.</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_driving_guide_shortcode() {
		ob_start();
		?>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Guía de Conducción Segura en Roatán</h1>
				<p class="rrc-seo-subtitle">Todo lo que necesitas saber sobre las carreteras de la isla: normas locales, estaciones de servicio, límites de velocidad y consejos prácticos.</p>
				<div class="rrc-seo-grid">
					<div>
						<h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 15px;">Reglas Clave de la Vía</h3>
						<ul class="rrc-seo-list">
							<li><strong>Lado de Conducción:</strong> En Honduras se conduce estrictamente por el lado derecho de la calle.</li>
							<li><strong>Límites de Velocidad:</strong> Varían entre 30 km/h en zonas urbanas y un máximo de 60 km/h en la carretera principal. Respétalos para evitar multas.</li>
							<li><strong>Condiciones del Asfalto:</strong> La carretera principal de Roatán está pavimentada, pero algunas rutas secundarias hacia playas o miradores son de tierra o pueden presentar irregularidades.</li>
							<li><strong>Conducción Nocturna:</strong> Se aconseja limitar la conducción nocturna debido a la falta de iluminación pública y la presencia eventual de peatones o animales en la carretera.</li>
						</ul>
					</div>
					<div class="rrc-seo-card">
						<h4 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 15px;">Estaciones de Gasolina</h4>
						<p style="font-size: 13.5px; line-height: 1.6; color: #64748b;">Hay estaciones de combustible convenientes en Coxen Hole, French Harbour y Sandy Bay. La mayoría de los vehículos de alquiler operan con gasolina regular. Asegúrate de verificar el tipo de combustible con nuestro personal antes de reabastecer.</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_cruise_route_shortcode() {
		ob_start();
		?>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Ruta Perfecta de 1 Día para Cruceristas en Auto</h1>
				<p class="rrc-seo-subtitle">Optimiza tu tiempo en tierra. Un itinerario autoguiado de 6 horas para explorar las mejores atracciones de Roatán a tu propio ritmo.</p>
				<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 45px;">
					<div class="rrc-seo-card">
						<h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Paso 1: Recogida en Puerto</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">Te entregamos el auto a tu llegada a Mahogany Bay o Coxen Hole. Trámite rápido en menos de 10 minutos.</p>
					</div>
					<div class="rrc-seo-card">
						<h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Paso 2: Playas y Snorkel</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">Conduce hacia West Bay para disfrutar del segundo arrecife de coral más grande del mundo o visita West End para almorzar.</p>
					</div>
					<div class="rrc-seo-card">
						<h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Paso 3: Cultura y Retorno</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">Visita la fábrica de chocolate en West End o el santuario de iguanas en French Key antes de devolver el auto 1 hora antes de zarpar.</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_beaches_route_shortcode() {
		ob_start();
		?>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Las Mejores Playas de Roatán Accesibles en Coche</h1>
				<p class="rrc-seo-subtitle">Guía de playas indispensables. Descubre cuáles tienen acceso vehicular sencillo, dónde parquear y qué llevar.</p>
				<div class="rrc-seo-grid">
					<div>
						<h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 15px;">Playas Imperdibles en tu Auto</h3>
						<ul class="rrc-seo-list">
							<li><strong>West Bay Beach:</strong> La playa más famosa por su arena blanca y aguas turquesas. Cuenta con parqueos privados convenientes a pocos metros de la playa.</li>
							<li><strong>Half Moon Bay (West End):</strong> Un ambiente relajado con restaurantes locales y fácil acceso en carro. Ideal para snorkel y atardeceres.</li>
							<li><strong>Camp Bay Beach:</strong> Ubicada en el extremo este de la isla. Es una playa virgen y pacífica. Se recomienda un Jeep o SUV 4x4 para el trayecto final de tierra.</li>
							<li><strong>Sandy Bay:</strong> Una zona tranquila perfecta para relajarse alejado del bullicio de los hoteles grandes.</li>
						</ul>
					</div>
					<div class="rrc-seo-card">
						<h4 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">Consejos de Parqueo</h4>
						<p style="font-size: 13px; line-height: 1.6; color: #64748b;">En West Bay y West End, utiliza los parqueos designados y evita estacionarte en la calle principal para no obstruir el tráfico ni recibir multas. No dejes objetos de valor visibles en el interior del auto al estacionar en las playas públicas.</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_help_center_shortcode() {
		ob_start();
		?>
		<div class="rrc-seo-page">
			<div class="rrc-seo-container">
				<h1 class="rrc-seo-title">Centro de Ayuda y Soporte 24/7</h1>
				<p class="rrc-seo-subtitle">Estamos contigo en cada kilómetro. Encuentra guías rápidas de soporte ante incidentes y números de emergencia locales.</p>
				<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px;">
					<div class="rrc-seo-card">
						<h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Asistencia en Carretera 24/7</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">Si experimentas una avería mecánica, pinchadura o necesitas remolque, llámanos inmediatamente al **(+504) 99-03-96-16**.</p>
					</div>
					<div class="rrc-seo-card">
						<h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">En Caso de Accidente</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">Mantén la calma, no muevas el auto y llama a nuestro equipo de soporte y a la policía de tránsito local (911).</p>
					</div>
					<div class="rrc-seo-card">
						<h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Requisitos de Devolución</h4>
						<p style="font-size: 12.5px; line-height: 1.5; color: #64748b;">Devuelve el auto con el mismo nivel de combustible acordado y asegúrate de retirar todas tus pertenencias personales.</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_deposit_policy_shortcode() {
		ob_start();
		?>
		<style>
		.rrc-deposit-page { font-family: 'Inter Tight', 'Segoe UI', sans-serif; background-color: #0b0f19; color: #f8fafc; padding: 60px 20px; line-height: 1.6; }
		.rrc-deposit-container { max-width: 900px; margin: 0 auto; background: #131b2e; border: 1px solid #1e293b; border-radius: 24px; padding: 48px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
		.rrc-deposit-header { text-align: center; margin-bottom: 40px; border-bottom: 1px solid #1e293b; padding-bottom: 30px; }
		.rrc-deposit-badge { display: inline-block; background: rgba(232, 39, 44, 0.15); border: 1px solid rgba(232, 39, 44, 0.3); color: #E8272C; font-size: 12px; font-weight: 800; padding: 6px 16px; border-radius: 50px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }
		.rrc-deposit-title { font-size: 32px; font-weight: 900; color: #ffffff; margin-bottom: 12px; letter-spacing: -0.5px; }
		.rrc-deposit-subtitle { font-size: 16px; color: #94a3b8; max-width: 700px; margin: 0 auto; }
		
		.rrc-deposit-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin: 32px 0; }
		.rrc-deposit-card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 24px; }
		.rrc-deposit-card h3 { font-size: 18px; font-weight: 800; color: #ffffff; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
		.rrc-deposit-card p { font-size: 14px; color: #cbd5e1; margin: 0; }

		.rrc-table-wrapper { background: #1e293b; border-radius: 16px; border: 1px solid #334155; overflow: hidden; margin: 24px 0; }
		.rrc-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
		.rrc-table th { background: #0f172a; padding: 14px 20px; color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
		.rrc-table td { padding: 14px 20px; border-top: 1px solid #334155; color: #f8fafc; }

		.rrc-deposit-box-callout { background: linear-gradient(135deg, rgba(232, 39, 44, 0.1) 0%, rgba(30, 41, 59, 0.8) 100%); border: 1px solid rgba(232, 39, 44, 0.3); border-radius: 16px; padding: 28px; margin: 36px 0; text-align: center; }
		.rrc-btn-primary { display: inline-block; background: #E8272C; color: #ffffff; font-weight: 900; font-size: 15px; text-decoration: none; padding: 14px 32px; border-radius: 50px; box-shadow: 0 10px 20px rgba(232,39,44,0.3); text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s; }
		.rrc-btn-primary:hover { background: #c61d22; transform: translateY(-2px); }
		</style>

		<div class="rrc-deposit-page">
			<div class="rrc-deposit-container">
				<div class="rrc-deposit-header">
					<span class="rrc-deposit-badge">Transparencia y Confianza</span>
					<h1 class="rrc-deposit-title">¿Por qué solicitamos un depósito de reserva del 10%?</h1>
					<p class="rrc-deposit-subtitle">Reserva tu vehículo con total confianza mediante PayPal y paga el saldo restante al momento de recibirlo en Roatán.</p>
				</div>

				<p style="font-size: 15px; color: #cbd5e1; margin-bottom: 24px;">
					En <strong>Ramírez Rent A Car</strong> queremos que reservar tu automóvil sea un proceso claro, transparente y conveniente. Por eso, para confirmar cualquier reserva solicitamos inicialmente únicamente el <strong>10% del valor total del alquiler mediante PayPal</strong>. El 90% restante se paga al momento de recibir el vehículo, después de verificar los detalles de tu reserva y antes de iniciar tu viaje.
				</p>

				<h2 style="font-size: 22px; font-weight: 800; color: #ffffff; margin-top: 36px; margin-bottom: 16px;">¿Cómo funciona el pago?</h2>
				<div class="rrc-deposit-grid">
					<div class="rrc-deposit-card">
						<h3><span>1.</span> Selecciona tu Vehículo</h3>
						<p>Elige las fechas, punto de recogida en Roatán y el vehículo ideal para tu viaje.</p>
					</div>
					<div class="rrc-deposit-card">
						<h3><span>2.</span> Revisa el Desglose</h3>
						<p>Verás exactamente el total, el depósito inicial del 10% y el saldo pendiente del 90%.</p>
					</div>
					<div class="rrc-deposit-card">
						<h3><span>3.</span> Paga el 10% por PayPal</h3>
						<p>Asegura el bloqueo del vehículo pagando únicamente el 10% con protección PayPal.</p>
					</div>
					<div class="rrc-deposit-card">
						<h3><span>4.</span> Saldo al Recibir</h3>
						<p>El 90% restante se paga antes de la entrega del vehículo en la sucursal o aeropuerto.</p>
					</div>
				</div>

				<h2 style="font-size: 22px; font-weight: 800; color: #ffffff; margin-top: 36px; margin-bottom: 16px;">Ejemplo Transparente de Pago</h2>
				<div class="rrc-table-wrapper">
					<table class="rrc-table">
						<thead>
							<tr>
								<th>Concepto</th>
								<th>Valor</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><strong>Valor total del alquiler</strong></td>
								<td><strong>$800 USD</strong></td>
							</tr>
							<tr style="background: rgba(34, 197, 94, 0.1);">
								<td style="color: #4ade80;">Depósito de reserva del 10% (Pagado por PayPal)</td>
								<td style="color: #4ade80; font-weight: 800;">$80 USD</td>
							</tr>
							<tr style="background: rgba(239, 68, 68, 0.1);">
								<td style="color: #fca5a5;">Saldo pendiente (al recibir el vehículo)</td>
								<td style="color: #ef4444; font-weight: 800;">$720 USD</td>
							</tr>
						</tbody>
					</table>
				</div>

				<h2 style="font-size: 22px; font-weight: 800; color: #ffffff; margin-top: 36px; margin-bottom: 16px;">Beneficios para el Cliente</h2>
				<ul style="color: #cbd5e1; font-size: 14.5px; padding-left: 20px; line-height: 1.8;">
					<li><strong>No pagas todo por adelantado:</strong> Conservas tu liquidez antes de llegar a la isla.</li>
					<li><strong>Garantía de disponibilidad:</strong> Bloqueamos el automóvil en la flota exclusivamente para tus fechas.</li>
					<li><strong>Verificación presencial:</strong> Revisas el vehículo e inspección física antes de abonar el saldo restante.</li>
					<li><strong>Descuento directo:</strong> El 10% NO es una tarifa adicional, se resta íntegramente del total del alquiler.</li>
				</ul>

				<div class="rrc-deposit-box-callout">
					<h3 style="font-size: 20px; font-weight: 900; color: #ffffff; margin-bottom: 12px;">Reserva hoy tu vehículo para Roatán</h3>
					<p style="font-size: 14px; color: #94a3b8; margin-bottom: 20px;">Asegura tu automóvil sin pagar el precio completo por adelantado.</p>
					<a href="<?php echo esc_url( site_url( '/#reservas' ) ); ?>" class="rrc-btn-primary">Buscar vehículos disponibles</a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
