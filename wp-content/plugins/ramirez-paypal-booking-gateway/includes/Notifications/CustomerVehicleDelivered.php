<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Notifications;

class CustomerVehicleDelivered {

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
			        pl.name AS pickup_location_name,
			        rl.name AS return_location_name
			 FROM $res_table r
			 JOIN $cust_table c ON r.customer_id = c.id
			 LEFT JOIN $model_table m ON r.vehicle_model_id = m.id
			 LEFT JOIN $loc_table pl ON r.pickup_location_id = pl.id
			 LEFT JOIN $loc_table rl ON r.return_location_id = rl.id
			 WHERE r.id = %d",
			$reservation_id
		) );

		if ( ! $res || empty( $res->cust_email ) ) {
			return false;
		}

		$to = $res->cust_email;
		$subject = '🚗 ¡Que disfrutes tu viaje! - Ramirez Rent a Car';

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
		$return_date = esc_html( date( 'd/m/Y h:i A', strtotime( $res->return_at ) ) );
		$return_loc = esc_html( $res->return_location_name );
		$ref = esc_html( $res->public_reference );

		// Dynamic logo resolution
		$logo_url = 'https://ramirezrentacar.com/wp-content/uploads/2026/R-Rent-a-car-logo-app.png';

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title>¡Buen viaje con Ramirez Rent a Car!</title>
		</head>
		<body style="font-family: sans-serif; color: #334155; line-height: 1.6; background-color: #f8fafc; padding: 20px;">
			<div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
				
				<!-- LOGO HEADER -->
				<div style="text-align: center; margin-bottom: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px;">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="Ramirez Rent a Car" style="max-height: 55px; display: inline-block;">
				</div>

				<h2 style="color: #E8272C; margin-top: 0;">¡Hola, <?php echo $client_name; ?>! 🚗</h2>
				
				<p style="font-size: 15px; margin-bottom: 18px;">
					Queremos darte la bienvenida oficial a bordo y desearte un viaje espectacular por la isla. Esperamos de todo corazón que la pases de maravilla, disfrutes de cada carretera increíble y conozcas bastante de nuestra hermosa zona.
				</p>

				<p style="font-size: 15px; margin-bottom: 24px; font-weight: bold; color: #16a34a;">
					✨ Confirmamos que tu saldo restante ha sido cancelado en su totalidad y tu cuenta está al día ($0.00 USD pendientes). ¡Todo listo!
				</p>

				<!-- RETURN DETAILS CARD -->
				<div style="background-color: #f1f5f9; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
					<h4 style="margin-top: 0; margin-bottom: 12px; color: #0f172a; text-transform: uppercase; font-size: 12px; letter-spacing: 0.8px;">📌 Información de Retorno del Vehículo</h4>
					<table style="width: 100%; border-collapse: collapse; font-size: 14px;">
						<tr>
							<td style="padding: 4px 0; font-weight: bold; width: 35%;">Vehículo:</td>
							<td><?php echo $vehicle; ?></td>
						</tr>
						<tr>
							<td style="padding: 4px 0; font-weight: bold;">Fecha de Entrega:</td>
							<td style="color: #E8272C; font-weight: bold;"><?php echo $return_date; ?></td>
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

				<p style="font-size: 14px; color: #64748b; margin-bottom: 20px; text-align: center;">
					Si necesitas asistencia en el camino o tienes cualquier consulta, puedes escribirnos directamente respondiendo a este correo o por WhatsApp.
				</p>

				<div style="text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 12px; color: #94a3b8;">
					<p>Gracias por confiar en nosotros y preferir a la familia de <strong>Ramirez Rent a Car</strong>.</p>
					<p style="margin-top: 5px;">© <?php echo date('Y'); ?> Ramirez Rent a Car. Todos los derechos reservados.</p>
				</div>

			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}
