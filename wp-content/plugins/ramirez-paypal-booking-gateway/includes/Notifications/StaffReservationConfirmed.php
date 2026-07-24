<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Notifications;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;

class StaffReservationConfirmed {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function send( $reservation_id, $order_id, $capture_id ) {
		global $wpdb;

		$res_table   = $wpdb->prefix . 'rrc_reservations';
		$cust_table  = $wpdb->prefix . 'rrc_customers';
		$model_table = $wpdb->prefix . 'rrc_vehicle_models';
		$loc_table   = $wpdb->prefix . 'rrc_locations';

		$res = $wpdb->get_row( $wpdb->prepare(
			"SELECT r.*, 
			        c.first_name, c.last_name, c.email AS cust_email, c.phone AS cust_phone,
			        m.public_name AS vehicle_name, m.passenger_capacity,
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

		if ( ! $res ) {
			return false;
		}

		$admin_email = get_option( 'admin_email' );
		$subject = sprintf( '🔔 Nueva reserva confirmada – %s', $res->public_reference );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: Ramirez Rent a Car <' . $admin_email . '>',
		];

		// Build team notification email message
		$message = $this->build_message_html( $res, $order_id, $capture_id );

		// Send email
		return wp_mail( $admin_email, $subject, $message, $headers );
	}

	private function build_message_html( $res, $order_id, $capture_id ) {
		$client_name = esc_html( $res->first_name . ' ' . $res->last_name );
		$phone = esc_html( $res->cust_phone );
		$email = esc_html( $res->cust_email );
		$vehicle = esc_html( $res->vehicle_name );
		$passengers = intval( $res->passenger_capacity );
		$pickup_date = esc_html( date( 'd/m/Y H:i A', strtotime( $res->pickup_at ) ) );
		$return_date = esc_html( date( 'd/m/Y H:i A', strtotime( $res->return_at ) ) );
		$pickup_loc = esc_html( $res->pickup_location_name );
		$return_loc = esc_html( $res->return_location_name );

		$total = number_format( (float) $res->total_amount, 2 );
		$deposit = number_format( (float) $res->deposit_paid_amount, 2 );
		$balance = number_format( (float) $res->remaining_balance, 2 );

		$admin_url = admin_url( 'admin.php?page=rrc-reservations' );

		ob_start();
		?>
		<!DOCTYPE html>
		<html lang="es">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Nueva Reserva Confirmada</title>
		</head>
		<body style="margin: 0; padding: 0; background-color: #0b0f19; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #1e293b;">
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0b0f19; padding: 40px 10px;">
				<tr>
					<td align="center">
						<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
							
							<!-- HEADER BAR WITH LOGO -->
							<tr>
								<td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 36px 40px; text-align: center; border-bottom: 3px solid #E8272C;">
									<img src="https://ramirezrentacar.com/wp-content/uploads/2026/R-Rent-a-car-logo-app.png" alt="Ramirez Rent a Car" style="max-height: 50px; margin-bottom: 16px; border: 0; display: inline-block;">
									<div>
										<span style="display: inline-block; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; padding: 6px 16px; border-radius: 50px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
											🔔 Nueva Reserva Confirmada
										</span>
									</div>
								</td>
							</tr>

							<!-- BODY CONTENT -->
							<tr>
								<td style="padding: 40px 40px 30px 40px;">
									<p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6; color: #475569;">
										Se ha recibido y capturado con éxito el depósito del 10% mediante PayPal para la reserva <strong>#<?php echo esc_html( $res->public_reference ); ?></strong>.
									</p>

									<!-- CLIENT SECTION -->
									<h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #E8272C;">Detalles del Cliente</h3>
									<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 28px; border-collapse: collapse;">
										<tr>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #64748b; font-weight: 500; width: 35%;">Cliente:</td>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700;"><?php echo $client_name; ?></td>
										</tr>
										<tr>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #64748b; font-weight: 500;">Teléfono:</td>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700;"><?php echo $phone; ?></td>
										</tr>
										<tr>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #64748b; font-weight: 500;">Email:</td>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700;"><?php echo $email; ?></td>
										</tr>
									</table>

									<!-- VEHICLE SECTION -->
									<h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #E8272C;">Detalles del Alquiler</h3>
									<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 28px; border-collapse: collapse;">
										<tr>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #64748b; font-weight: 500; width: 35%;">Vehículo:</td>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700;"><?php echo $vehicle; ?> (<?php echo $passengers; ?> Pasajeros)</td>
										</tr>
										<tr>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #64748b; font-weight: 500;">Fechas:</td>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700;">Desde <?php echo $pickup_date; ?> hasta <?php echo $return_date; ?></td>
										</tr>
										<tr>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #64748b; font-weight: 500;">Origen (Retiro):</td>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700;"><?php echo $pickup_loc; ?></td>
										</tr>
										<tr>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #64748b; font-weight: 500;">Destino (Devolución):</td>
											<td style="padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #0f172a; font-weight: 700;"><?php echo $return_loc; ?></td>
										</tr>
									</table>

									<!-- PAYMENT CARD -->
									<h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #E8272C;">Información de Pago (10% Depósito)</h3>
									<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; border-collapse: separate; overflow: hidden; margin-bottom: 28px;">
										<tr>
											<td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #475569;">Total Alquiler:</td>
											<td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #0f172a; font-weight: 700; text-align: right;">$<?php echo $total; ?> USD</td>
										</tr>
										<tr>
											<td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #16a34a; font-weight: 600;">Depósito Cobrado (10%):</td>
											<td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #16a34a; font-weight: 800; text-align: right;">$<?php echo $deposit; ?> USD</td>
										</tr>
										<tr>
											<td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #dc2626; font-weight: 600;">Saldo Pendiente (90%):</td>
											<td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #dc2626; font-weight: 800; text-align: right;">$<?php echo $balance; ?> USD</td>
										</tr>
										<tr>
											<td style="padding: 10px 20px; font-size: 12px; color: #64748b;">PayPal Order ID:</td>
											<td style="padding: 10px 20px; font-size: 12px; color: #0f172a; font-weight: 600; text-align: right;"><code><?php echo esc_html( $order_id ); ?></code></td>
										</tr>
										<tr>
											<td style="padding: 10px 20px; font-size: 12px; color: #64748b;">PayPal Capture ID:</td>
											<td style="padding: 10px 20px; font-size: 12px; color: #0f172a; font-weight: 600; text-align: right;"><code><?php echo esc_html( $capture_id ); ?></code></td>
										</tr>
									</table>

									<!-- ACTIONS -->
									<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td align="center" style="padding-top: 10px;">
												<a href="<?php echo esc_url( $admin_url ); ?>" style="display: inline-block; background-color: #E8272C; color: #ffffff; padding: 14px 30px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 6px 18px rgba(232, 39, 44, 0.3);">
													Ver en Administrador
												</a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							
							<!-- FOOTER -->
							<tr>
								<td style="background-color: #f8fafc; padding: 24px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;">
									<p style="margin: 0 0 4px 0;">Este es un correo automático generado por tu plataforma.</p>
									<p style="margin: 0;">© <?php echo date('Y'); ?> Ramirez Rent a Car. Todos los derechos reservados.</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}
