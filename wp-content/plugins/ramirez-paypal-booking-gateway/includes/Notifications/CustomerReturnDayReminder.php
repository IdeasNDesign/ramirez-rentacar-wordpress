<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Notifications;

class CustomerReturnDayReminder {

	public static function send( $reservation_id ) {
		global $wpdb;

		$res_table   = $wpdb->prefix . 'rrc_reservations';
		$cust_table  = $wpdb->prefix . 'rrc_customers';
		$model_table = $wpdb->prefix . 'rrc_vehicle_models';
		$loc_table   = $wpdb->prefix . 'rrc_locations';

		$res = $wpdb->get_row( $wpdb->prepare(
			"SELECT r.*, 
			        c.first_name, c.last_name, c.email AS cust_email,
			        m.public_name AS vehicle_name,
			        rl.name AS return_location_name
			 FROM $res_table r
			 JOIN $cust_table c ON r.customer_id = c.id
			 LEFT JOIN $model_table m ON r.vehicle_model_id = m.id
			 LEFT JOIN $loc_table rl ON r.return_location_id = rl.id
			 WHERE r.id = %d AND r.reservation_status = 'checked_out'",
			$reservation_id
		) );

		// Only send if the reservation is still checked out (the client has the car and needs to return it)
		if ( ! $res || empty( $res->cust_email ) ) {
			return false;
		}

		$to = $res->cust_email;
		$subject = '🚗 Recordatorio: Hoy entregas tu vehículo - Ramirez Rent a Car';

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: Ramirez Rent a Car <' . get_option( 'admin_email' ) . '>',
		];

		$message = self::build_message_html( $res );

		return wp_mail( $to, $subject, $message, $headers );
	}

	private static function build_message_html( $res ) {
		$client_name = esc_html( $res->first_name );
		$vehicle = esc_html( $res->vehicle_name );
		$return_time = esc_html( date( 'h:i A', strtotime( $res->return_at ) ) );
		$return_loc = esc_html( $res->return_location_name );
		$ref = esc_html( $res->public_reference );

		$logo_url = 'https://ramirezrentacar.com/wp-content/uploads/2026/R-Rent-a-car-logo-app.png';

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title>Hoy entregas tu vehículo - Ramirez Rent a Car</title>
		</head>
		<body style="font-family: sans-serif; color: #334155; line-height: 1.6; background-color: #f8fafc; padding: 20px;">
			<div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
				
				<!-- LOGO HEADER -->
				<div style="text-align: center; margin-bottom: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="Ramirez Rent a Car" style="max-height: 55px; display: inline-block;">
				</div>

				<h2 style="color: #E8272C; margin-top: 0;">¡Hola, <?php echo $client_name; ?>! 😊</h2>
				
				<p style="font-size: 15px; margin-bottom: 18px;">
					Esperamos de todo corazón que hayas disfrutado al máximo conociendo cada rincón de la isla a bordo de nuestro vehículo. ¡Ha sido un honor acompañarte en esta aventura!
				</p>

				<p style="font-size: 15px; margin-bottom: 20px;">
					Te escribimos este correo amistoso para recordarte con mucho cariño que <strong>hoy vence el plazo de alquiler de tu vehículo</strong>. Aquí tienes los detalles para la devolución:
				</p>

				<!-- RETURN CARD -->
				<div style="background-color: #fffbeb; padding: 20px; border-radius: 8px; border: 1px solid #fef3c7; margin-bottom: 25px;">
					<h4 style="margin-top: 0; margin-bottom: 12px; color: #b45309; text-transform: uppercase; font-size: 12px; letter-spacing: 0.8px;">⏰ Recordatorio de Devolución de Hoy</h4>
					<table style="width: 100%; border-collapse: collapse; font-size: 14px;">
						<tr>
							<td style="padding: 4px 0; font-weight: bold; width: 35%;">Vehículo:</td>
							<td><?php echo $vehicle; ?></td>
						</tr>
						<tr>
							<td style="padding: 4px 0; font-weight: bold; color: #b45309;">Hora de Entrega:</td>
							<td style="color: #b45309; font-weight: bold;"><?php echo $return_time; ?> (Hoy)</td>
						</tr>
						<tr>
							<td style="padding: 4px 0; font-weight: bold;">Lugar de Devolución:</td>
							<td><?php echo $return_loc; ?></td>
						</tr>
						<tr>
							<td style="padding: 4px 0; font-weight: bold;">Código de Reserva:</td>
							<td><code>#<?php echo $ref; ?></code></td>
						</tr>
					</table>
				</div>

				<p style="font-size: 15px; margin-bottom: 20px;">
					Queremos darte las **gracias infinitas** por haber confiado en nosotros. Esperamos que hayas coleccionado momentos inolvidables y que muy pronto nos volvamos a encontrar en tu próximo viaje.
				</p>

				<p style="font-size: 14px; color: #64748b; margin-bottom: 20px; text-align: center;">
					Si tienes cualquier eventualidad con la hora de entrega, avísanos con confianza por WhatsApp.
				</p>

				<div style="text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 12px; color: #94a3b8;">
					<p>¡Buen viaje de regreso a casa! Con cariño,</p>
					<p style="font-weight: bold; margin-top: 5px; color: #0f172a;">La familia de Ramirez Rent a Car</p>
				</div>

			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}
