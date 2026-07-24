<?php
namespace RamirezRentACar\Admin;

class VehiclesController {
	public static function handle_actions() {
		global $wpdb;
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$plans_table = $wpdb->prefix . 'rrc_rate_plans';
		$packages_table = $wpdb->prefix . 'rrc_rate_packages';

		if ( isset( $_POST['rrc_save_vehicle_settings'] ) && check_admin_referer( 'rrc_save_vehicle_nonce' ) ) {
			$model_id = intval( $_POST['vehicle_model_id'] );
			$public_name = sanitize_text_field( $_POST['public_name'] );
			$category = sanitize_text_field( $_POST['category'] );
			$year = intval( $_POST['year'] );
			$desc = sanitize_textarea_field( $_POST['description'] );
			$image_url = esc_url_raw( $_POST['image_url'] );

			// 1. Update Custom Table
			$wpdb->update( $models_table, [
				'public_name' => $public_name,
				'category'    => $category,
				'year'        => $year
			], [ 'id' => $model_id ] );

			// 2. Update post CPT metadata / content
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $models_table WHERE id = %d", $model_id ) );
			if ( $post_id ) {
				wp_update_post( [
					'ID'           => $post_id,
					'post_title'   => $public_name,
					'post_content' => $desc
				] );
				update_post_meta( $post_id, '_rrc_image_url', $image_url );
			}

			// 3. Save Packages Rates (Days, Weeks, Months)
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
	}

	public static function render_admin_page() {
		global $wpdb;
		self::handle_actions();

		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		$vehicles = $wpdb->get_results( "SELECT * FROM $models_table WHERE deleted_at IS NULL" );

		echo '<div class="wrap" style="font-family: -apple-system, BlinkMacSystemFont, sans-serif;">';
		echo '<h1>Administrar Vehículos, Tarifas e Imágenes</h1>';
		echo '<p>Modifique descripciones, precios por paquetes (días/semanas/meses) e imágenes de muestra directamente.</p>';

		if ( empty( $vehicles ) ) {
			echo '<p>No hay vehículos cargados en el sistema. Ejecute el importador de catálogo en <strong>Tools</strong>.</p>';
			echo '</div>';
			return;
		}

		foreach ( $vehicles as $vehicle ) {
			$post_id = $vehicle->post_id;
			$post = get_post( $post_id );
			$desc = $post ? $post->post_content : '';
			$image_url = $post ? get_post_meta( $post_id, '_rrc_image_url', true ) : '';

			// If no custom image set, use a beautiful unsplash vehicle mock according to category
			if ( empty( $image_url ) ) {
				$mock_images = [
					'Sedan'               => 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=400&q=80',
					'Standard Jeep'       => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=400&q=80',
					'Luxury SUV'          => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=400&q=80',
					'Premium Luxury SUV'  => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=400&q=80',
					'Standard SUV'        => 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=400&q=80',
					'Medium SUV'          => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=400&q=80',
					'ATV'                 => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=400&q=80',
					'4x4 Pickup Truck'    => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=400&q=80'
				];
				$image_url = isset($mock_images[$vehicle->category]) ? $mock_images[$vehicle->category] : 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=400&q=80';
			}

			// Retrieve packages associated to this model's standard rate plan
			$plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}rrc_rate_plans WHERE vehicle_model_id = %d AND booking_context = 'standard' LIMIT 1", $vehicle->id ) );
			$packages = [];
			if ( $plan_id ) {
				$packages = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rrc_rate_packages WHERE rate_plan_id = %d ORDER BY normalized_days ASC", $plan_id ) );
			}

			echo '<div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 6px; margin-bottom: 30px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
			echo '<form method="POST">';
			wp_nonce_field( 'rrc_save_vehicle_nonce' );
			echo '<input type="hidden" name="vehicle_model_id" value="' . intval( $vehicle->id ) . '">';

			echo '<div style="display: flex; gap: 20px; flex-wrap: wrap;">';
			
			// Left side: Mock Image & Upload fields
			echo '<div style="flex: 1; min-width: 250px; text-align: center;">';
			echo '<img src="' . esc_url( $image_url ) . '" style="max-width: 100%; height: 160px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 10px;">';
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

			echo '<input type="submit" name="rrc_save_vehicle_settings" class="button button-primary" value="Guardar Cambios">';
			echo '</div>'; // End right side

			echo '</div>'; // End flex container
			echo '</form>';
			echo '</div>';
		}

		echo '</div>';
	}
}
