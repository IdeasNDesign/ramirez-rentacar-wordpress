<?php
namespace RamirezRentACar\Admin;

/**
 * Controller for managing Vehicles & Rates in the WordPress admin panel.
 * Author: Break The Mold
 */
class VehiclesController {
	public static function handle_actions() {
		global $wpdb;
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$plans_table = $wpdb->prefix . 'rrc_rate_plans';
		$packages_table = $wpdb->prefix . 'rrc_rate_packages';
		$units_table = $wpdb->prefix . 'rrc_vehicle_units';

		// --- 1. HANDLE ADD VEHICLE ---
		if ( isset( $_POST['rrc_add_new_vehicle'] ) && check_admin_referer( 'rrc_add_vehicle_nonce' ) ) {
			$public_name = sanitize_text_field( $_POST['public_name'] );
			$category = sanitize_text_field( $_POST['category'] );
			$year = intval( $_POST['year'] );
			$image_url = esc_url_raw( $_POST['image_url'] );
			$desc = sanitize_textarea_field( $_POST['description'] );
			$base_price = floatval( $_POST['base_price'] );

			if ( empty( $public_name ) || empty( $category ) || $base_price <= 0 ) {
				echo '<div class="notice notice-error is-dismissible"><p>Error: El nombre, la categoría y el precio base son obligatorios.</p></div>';
				return;
			}

			$internal_code = sanitize_title( $public_name );

			// Check if vehicle code already exists
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $models_table WHERE internal_code = %s AND deleted_at IS NULL", $internal_code ) );
			if ( $exists ) {
				$internal_code .= '-' . time();
			}

			// A. Create CPT Post
			$post_id = wp_insert_post( [
				'post_title'   => $public_name,
				'post_name'    => $internal_code,
				'post_status'  => 'publish',
				'post_type'    => 'rrc_vehicle',
				'post_content' => $desc,
			] );

			if ( is_wp_error( $post_id ) ) {
				echo '<div class="notice notice-error is-dismissible"><p>Error al crear el post del vehículo: ' . esc_html( $post_id->get_error_message() ) . '</p></div>';
				return;
			}

			// Set image meta
			update_post_meta( $post_id, '_rrc_image_url', $image_url );

			// B. Insert into custom table
			$wpdb->insert( $models_table, [
				'post_id'            => $post_id,
				'internal_code'      => $internal_code,
				'public_name'        => $public_name,
				'category'           => $category,
				'year'               => $year,
				'status'             => 'publish',
				'passenger_capacity' => 5,
				'created_at'         => current_time( 'mysql' ),
				'updated_at'         => current_time( 'mysql' )
			] );
			$model_id = $wpdb->insert_id;

			// C. Create standard rate plan
			$wpdb->insert( $plans_table, [
				'vehicle_model_id' => $model_id,
				'name'             => $public_name . ' Standard Rate Plan',
				'booking_context'  => 'standard',
				'currency'         => 'USD',
				'active'           => 1,
				'effective_from'   => current_time( 'mysql' ),
				'version'          => 1,
				'created_at'       => current_time( 'mysql' ),
				'updated_at'       => current_time( 'mysql' )
			] );
			$plan_id = $wpdb->insert_id;

			// D. Create standard package rate multipliers
			$standard_packages = [
				[ 'unit' => 'day', 'value' => 1, 'mult' => 1.0 ],
				[ 'unit' => 'day', 'value' => 2, 'mult' => 1.833 ], // e.g. slight discount
				[ 'unit' => 'day', 'value' => 3, 'mult' => 2.75 ],
				[ 'unit' => 'day', 'value' => 4, 'mult' => 3.666 ],
				[ 'unit' => 'day', 'value' => 5, 'mult' => 4.583 ],
				[ 'unit' => 'day', 'value' => 6, 'mult' => 5.5 ],
				[ 'unit' => 'week', 'value' => 1, 'mult' => 6.166 ],
				[ 'unit' => 'week', 'value' => 2, 'mult' => 11.5 ],
				[ 'unit' => 'week', 'value' => 3, 'mult' => 15.416 ],
				[ 'unit' => 'month', 'value' => 1, 'mult' => 17.5 ],
				[ 'unit' => 'month', 'value' => 2, 'mult' => 35.0 ],
				[ 'unit' => 'month', 'value' => 3, 'mult' => 52.5 ],
				[ 'unit' => 'month', 'value' => 4, 'mult' => 70.0 ],
				[ 'unit' => 'month', 'value' => 5, 'mult' => 87.5 ],
				[ 'unit' => 'month', 'value' => 6, 'mult' => 105.0 ],
			];

			foreach ( $standard_packages as $pkg ) {
				$norm_days = $pkg['value'];
				if ( $pkg['unit'] === 'week' ) {
					$norm_days = $pkg['value'] * 7;
				} elseif ( $pkg['unit'] === 'month' ) {
					$norm_days = $pkg['value'] * 30;
				}

				$wpdb->insert( $packages_table, [
					'rate_plan_id'   => $plan_id,
					'duration_unit'  => $pkg['unit'],
					'duration_value' => $pkg['value'],
					'normalized_days'=> $norm_days,
					'total_amount'   => round( $base_price * $pkg['mult'], 2 ),
					'stackable'      => 1,
					'guide_included' => 0,
					'created_at'     => current_time( 'mysql' ),
					'updated_at'     => current_time( 'mysql' )
				] );
			}

			// E. Create standard physical units
			for ( $i = 1; $i <= 5; $i++ ) {
				$wpdb->insert( $units_table, [
					'vehicle_model_id' => $model_id,
					'unit_code'        => strtoupper( $internal_code ) . '-' . str_pad( $i, 2, '0', STR_PAD_LEFT ),
					'license_plate'    => 'P-' . strtoupper( wp_generate_password( 6, false ) ),
					'color'            => 'Gris',
					'status'           => 'available',
					'service_status'   => 'available',
					'created_at'       => current_time( 'mysql' ),
					'updated_at'       => current_time( 'mysql' )
				] );
			}

			echo '<div class="notice notice-success is-dismissible"><p>Vehículo nuevo agregado con éxito al catálogo (incluyendo tarifas y unidades).</p></div>';
		}

		// --- 2. HANDLE SAVE VEHICLE ---
		if ( isset( $_POST['rrc_save_vehicle_settings'] ) && check_admin_referer( 'rrc_save_vehicle_nonce' ) ) {
			$model_id = intval( $_POST['vehicle_model_id'] );
			$public_name = sanitize_text_field( $_POST['public_name'] );
			$category = sanitize_text_field( $_POST['category'] );
			$year = intval( $_POST['year'] );
			$desc = sanitize_textarea_field( $_POST['description'] );
			$image_url = esc_url_raw( $_POST['image_url'] );

			$wpdb->update( $models_table, [
				'public_name' => $public_name,
				'category'    => $category,
				'year'        => $year
			], [ 'id' => $model_id ] );

			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $models_table WHERE id = %d", $model_id ) );
			if ( $post_id ) {
				wp_update_post( [
					'ID'           => $post_id,
					'post_title'   => $public_name,
					'post_content' => $desc
				] );
				update_post_meta( $post_id, '_rrc_image_url', $image_url );
			}

			if ( isset( $_POST['rates'] ) && is_array( $_POST['rates'] ) ) {
				$plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $plans_table WHERE vehicle_model_id = %d AND booking_context = 'standard' LIMIT 1", $model_id ) );
				if ( $plan_id ) {
					foreach ( $_POST['rates'] as $pkg_id => $amount ) {
						$wpdb->update( $packages_table, [
							'total_amount' => floatval( $amount )
						], [ 'id' => intval( $pkg_id ), 'rate_plan_id' => $plan_id ] );
					}
				}
			}

			echo '<div class="notice notice-success is-dismissible"><p>Vehículo y tarifas actualizadas con éxito.</p></div>';
		}

		// --- 3. HANDLE DELETE VEHICLE ---
		if ( isset( $_POST['rrc_delete_vehicle'] ) && check_admin_referer( 'rrc_delete_vehicle_nonce' ) ) {
			$model_id = intval( $_POST['vehicle_model_id'] );

			// Mark deleted in custom table
			$wpdb->update( $models_table, [ 'deleted_at' => current_time( 'mysql' ) ], [ 'id' => $model_id ] );

			// Trash associated CPT post
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $models_table WHERE id = %d", $model_id ) );
			if ( $post_id ) {
				wp_trash_post( $post_id );
			}

			echo '<div class="notice notice-success is-dismissible"><p>Vehículo eliminado del catálogo.</p></div>';
		}
	}

	public static function render_admin_page() {
		global $wpdb;
		self::handle_actions();

		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$vehicles = $wpdb->get_results( "SELECT * FROM $models_table WHERE deleted_at IS NULL ORDER BY id DESC" );

		echo '<div class="wrap" style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; max-width: 1200px;">';
		echo '<h1>Administrar Vehículos, Tarifas e Imágenes</h1>';
		echo '<p>Modifique descripciones, precios por paquetes (días/semanas/meses) e imágenes de muestra directamente.</p>';

		// --- NEW VEHICLE FORM COLLAPSIBLE ---
		?>
		<div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 6px; margin-bottom: 30px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<h2 style="margin-top: 0; cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="document.getElementById('rrc-add-form').style.display = document.getElementById('rrc-add-form').style.display === 'none' ? 'block' : 'none';">
				<span>➕ Agregar Nuevo Vehículo al Catálogo</span>
				<span style="font-size: 14px; color: #72777c;">(Clic para desplegar)</span>
			</h2>
			
			<div id="rrc-add-form" style="display: none; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
				<form method="POST">
					<?php wp_nonce_field( 'rrc_add_vehicle_nonce' ); ?>
					<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px;">
						<div>
							<label style="font-weight:bold; display:block; margin-bottom: 5px;">Nombre Comercial</label>
							<input type="text" name="public_name" placeholder="Ej. Jeep Wrangler Unlimited" style="width: 100%;" required>
						</div>
						<div>
							<label style="font-weight:bold; display:block; margin-bottom: 5px;">Categoría</label>
							<input type="text" name="category" placeholder="Ej. Sedan, Luxury SUV, ATV" style="width: 100%;" required>
						</div>
						<div>
							<label style="font-weight:bold; display:block; margin-bottom: 5px;">Año</label>
							<input type="number" name="year" value="<?php echo date('Y'); ?>" style="width: 100%;">
						</div>
						<div>
							<label style="font-weight:bold; display:block; margin-bottom: 5px;">Precio Base por Día (USD)</label>
							<input type="number" step="0.01" name="base_price" placeholder="Ej. 60.00" style="width: 100%;" required>
						</div>
					</div>

					<div style="margin-bottom: 15px;">
						<label style="font-weight:bold; display:block; margin-bottom: 5px;">URL de Fotografía de Muestra</label>
						<input type="text" name="image_url" placeholder="https://..." style="width: 100%;">
					</div>

					<div style="margin-bottom: 20px;">
						<label style="font-weight:bold; display:block; margin-bottom: 5px;">Descripción de Beneficios y Seguridad (Persuasiva)</label>
						<textarea name="description" rows="3" placeholder="Describe los beneficios y extras del vehículo..." style="width: 100%;"></textarea>
					</div>

					<input type="submit" name="rrc_add_new_vehicle" class="button button-primary" value="Registrar Vehículo">
				</form>
			</div>
		</div>
		<?php

		if ( empty( $vehicles ) ) {
			echo '<p>No hay vehículos cargados en el sistema. Utiliza el formulario de arriba para agregar uno nuevo.</p>';
			echo '</div>';
			return;
		}

		// --- LIST VEHICLES ---
		foreach ( $vehicles as $vehicle ) {
			$post_id = $vehicle->post_id;
			$post = get_post( $post_id );
			$desc = $post ? $post->post_content : '';
			$image_url = $post ? get_post_meta( $post_id, '_rrc_image_url', true ) : '';

			if ( empty( $image_url ) ) {
				$image_url = 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=400&q=80';
			}

			// Retrieve packages associated to this model's standard rate plan
			$plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}rrc_rate_plans WHERE vehicle_model_id = %d AND booking_context = 'standard' LIMIT 1", $vehicle->id ) );
			$packages = [];
			if ( $plan_id ) {
				$packages = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rrc_rate_packages WHERE rate_plan_id = %d ORDER BY normalized_days ASC", $plan_id ) );
			}

			echo '<div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 6px; margin-bottom: 30px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
			
			echo '<div style="display: flex; gap: 20px; flex-wrap: wrap;">';
			
			// Left side: Mock Image & Upload fields
			echo '<div style="flex: 1; min-width: 250px; text-align: center;">';
			echo '<img src="' . esc_url( $image_url ) . '" style="max-width: 100%; height: 160px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 10px;">';
			
			// Edit form left side url input
			echo '<form method="POST">';
			wp_nonce_field( 'rrc_save_vehicle_nonce' );
			echo '<input type="hidden" name="vehicle_model_id" value="' . intval( $vehicle->id ) . '">';
			echo '<div style="text-align: left;">';
			echo '<label style="display:block; font-weight:bold; margin-bottom: 5px;">URL de Fotografía de Muestra</label>';
			echo '<input type="text" name="image_url" value="' . esc_attr( $image_url ) . '" style="width: 100%; padding: 6px; margin-bottom: 10px;">';
			echo '</div>';
			echo '</div>';

			// Right side: General info & description
			echo '<div style="flex: 2; min-width: 300px;">';
			echo '<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px;">';
			echo '<div><label style="font-weight:bold;">Nombre Comercial</label><input type="text" name="public_name" value="' . esc_attr( $vehicle->public_name ) . '" style="width:100%;"></div>';
			echo '<div><label style="font-weight:bold;">Categoría</label><input type="text" name="category" value="' . esc_attr( $vehicle->category ) . '" style="width:100%;"></div>';
			echo '<div><label style="font-weight:bold;">Año</label><input type="number" name="year" value="' . intval( $vehicle->year ) . '" style="width:100%;"></div>';
			echo '</div>';

			echo '<div style="margin-bottom: 15px;">';
			echo '<label style="font-weight:bold; display:block; margin-bottom:5px;">Descripción de Beneficios y Seguridad (Persuasiva)</label>';
			echo '<textarea name="description" rows="4" style="width: 100%;">' . esc_textarea( $desc ) . '</textarea>';
			echo '</div>';

			// Package pricing inputs
			if ( ! empty( $packages ) ) {
				echo '<h4 style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Tarifas por Paquetes de Duración (USD)</h4>';
				echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-bottom: 15px;">';
				foreach ( $packages as $pkg ) {
					$label = $pkg->duration_value . ' ' . ($pkg->duration_unit === 'day' ? 'Día(s)' : ($pkg->duration_unit === 'week' ? 'Semana(s)' : 'Mes(es)'));
					echo '<div>';
					echo '<label style="font-size: 11px; display:block; color:#555;">' . esc_html( $label ) . '</label>';
					echo '<input type="number" step="0.01" name="rates[' . intval( $pkg->id ) . ']" value="' . floatval( $pkg->total_amount ) . '" style="width:100%;">';
					echo '</div>';
				}
				echo '</div>';
			}

			echo '<div style="display: flex; gap: 10px; margin-top: 15px;">';
			echo '<input type="submit" name="rrc_save_vehicle_settings" class="button button-primary" value="Guardar Cambios">';
			echo '</form>'; // End save form

			// Delete form button
			echo '<form method="POST" onsubmit="return confirm(\'¿Estás seguro de que deseas eliminar este vehículo por completo del catálogo?\');">';
			wp_nonce_field( 'rrc_delete_vehicle_nonce' );
			echo '<input type="hidden" name="vehicle_model_id" value="' . intval( $vehicle->id ) . '">';
			echo '<input type="submit" name="rrc_delete_vehicle" class="button button-link-delete" style="color: #a00; border: 1px solid #ccd0d4; padding: 4px 12px; border-radius: 3px; cursor: pointer; line-height: 20px;" value="🗑️ Eliminar Vehículo">';
			echo '</form>';
			echo '</div>';

			echo '</div>'; // End right side

			echo '</div>'; // End flex container
			echo '</div>';
		}

		echo '</div>';
	}
}
