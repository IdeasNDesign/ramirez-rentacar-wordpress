<?php
/**
 * Author: Break The Mold
 */

namespace RamirezRentACar\Infrastructure\Notifications;

class EmailNotificationService {

	/**
	 * Send confirmation email to the customer for a given reservation.
	 *
	 * @param int $reservation_id
	 * @return bool
	 */
	public static function send_reservation_confirmation( $reservation_id ) {
		global $wpdb;

		$res_table   = $wpdb->prefix . 'rrc_reservations';
		$cust_table  = $wpdb->prefix . 'rrc_customers';
		$model_table = $wpdb->prefix . 'rrc_vehicle_models';
		$loc_table   = $wpdb->prefix . 'rrc_locations';

		$reservation = $wpdb->get_row( $wpdb->prepare(
			"SELECT r.*, 
			        c.first_name, c.last_name, c.email, c.phone,
			        m.public_name AS vehicle_name, m.post_id,
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

		if ( ! $reservation || empty( $reservation->email ) ) {
			return false;
		}

		$to = $reservation->email;
		$subject = sprintf( '🚗 ¡Depósito Confirmado! Reserva #%s - Ramirez Rent a Car', $reservation->public_reference );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: Ramirez Rent a Car <' . get_option( 'admin_email' ) . '>',
		);

		$message = self::build_email_html( $reservation );

		$sent = wp_mail( $to, $subject, $message, $headers );

		if ( ! $sent ) {
			$header_str = implode( "\r\n", $headers );
			$sent = @mail( $to, $subject, $message, $header_str );
		}

		return $sent;
	}

	/**
	 * Build HTML email template for confirmation.
	 *
	 * @param object $res
	 * @return string
	 */
	private static function build_email_html( $res ) {
		$customer_name = esc_html( trim( $res->first_name . ' ' . $res->last_name ) );
		$reference     = esc_html( $res->public_reference );
		$vehicle       = esc_html( $res->vehicle_name ? $res->vehicle_name : 'Vehículo Seleccionado' );
		$pickup_loc    = esc_html( $res->pickup_location_name ? $res->pickup_location_name : 'Sucursal de Origen' );
		$return_loc    = esc_html( $res->return_location_name ? $res->return_location_name : 'Sucursal de Destino' );
		$pickup_at     = esc_html( date( 'd/m/Y H:i A', strtotime( $res->pickup_at ) ) );
		$return_at     = esc_html( date( 'd/m/Y H:i A', strtotime( $res->return_at ) ) );
		$days          = intval( $res->chargeable_days );
		$total         = number_format( (float) $res->total_amount, 2 );
		$deposit       = number_format( (float) ($res->deposit_paid_amount > 0 ? $res->deposit_paid_amount : $res->deposit_amount), 2 );
		$balance       = number_format( (float) $res->remaining_balance, 2 );
		$site_url      = esc_url( site_url() );

		// Logo URL resolution
		$logo_url = 'https://ramirezrentacar.com/wp-content/uploads/2026/R-Rent-a-car-logo-app.png';

		// Vehicle image
		$vehicle_img = '';
		if ( ! empty( $res->post_id ) ) {
			$vehicle_img = get_post_meta( $res->post_id, '_rrc_image_url', true );
		}

		ob_start();
		?>
		<!DOCTYPE html>
		<html lang="es">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Confirmación de Reserva</title>
		</head>
		<body style="margin: 0; padding: 0; background-color: #0b0f19; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #1e293b;">
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0b0f19; padding: 40px 10px;">
				<tr>
					<td align="center">
						<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 620px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
							
							<!-- HEADER BAR WITH LOGO & STATUS -->
							<tr>
								<td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 36px 40px; text-align: center; border-bottom: 3px solid #E8272C;">
									<?php if ( $logo_url ) : ?>
										<img src="<?php echo esc_url( $logo_url ); ?>" alt="Ramirez Rent a Car" style="max-height: 50px; margin-bottom: 16px; border: 0; display: inline-block;">
									<?php else : ?>
										<h1 style="margin: 0 0 10px 0; color: #ffffff; font-size: 26px; font-weight: 800; tracking: -0.5px; text-transform: uppercase;">
											RAMIREZ <span style="color: #E8272C;">RENT A CAR</span>
										</h1>
									<?php endif; ?>
									<div>
										<span style="display: inline-block; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #4ade80; padding: 6px 16px; border-radius: 50px; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
											✓ Depósito del 10% Recibido
										</span>
									</div>
								</td>
							</tr>

							<!-- HERO CONFIRMATION TITLE -->
							<tr>
								<td style="padding: 40px 40px 20px 40px; text-align: center;">
									<h2 style="margin: 0 0 10px 0; color: #0f172a; font-size: 24px; font-weight: 700;">¡Depósito Confirmado, <?php echo $customer_name; ?>!</h2>
									<p style="margin: 0; color: #64748b; font-size: 15px; line-height: 1.6;">
										Hemos recibido exitosamente el depósito del 10% mediante PayPal. Tu vehículo ha sido bloqueado y reservado para tus fechas.
									</p>
								</td>
							</tr>

							<!-- CODE REFERENCE BOX -->
							<tr>
								<td style="padding: 0 40px 24px 40px;">
									<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 14px; text-align: center; padding: 18px;">
										<tr>
											<td>
												<span style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Código de Reserva</span>
												<div style="font-size: 28px; font-weight: 900; color: #E8272C; letter-spacing: 2px; margin-top: 4px;">
													#<?php echo $reference; ?>
												</div>
											</td>
										</tr>
									</table>
								</td>
							</tr>

							<!-- VEHICLE DETAILS CARD -->
							<?php if ( $vehicle_img ) : ?>
							<tr>
								<td style="padding: 0 40px 24px 40px; text-align: center;">
									<img src="<?php echo esc_url( $vehicle_img ); ?>" alt="<?php echo $vehicle; ?>" style="max-width: 100%; height: auto; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.08);">
								</td>
							</tr>
							<?php endif; ?>

							<!-- SUMMARY DETAILS TABLE WITH 10% DEPOSIT & 90% BALANCE -->
							<tr>
								<td style="padding: 0 40px 30px 40px;">
									<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;">
										<tr style="background: #f1f5f9;">
											<th colspan="2" style="padding: 14px 20px; text-align: left; font-size: 14px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
												Desglose Financiero del Alquiler
											</th>
										</tr>
										<tr>
											<td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px; font-weight: 600; width: 45%;">Vehículo:</td>
											<td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 14px; font-weight: 700; width: 55%;"><?php echo $vehicle; ?></td>
										</tr>
										<tr>
											<td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px; font-weight: 600;">Retiro:</td>
											<td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 14px; line-height: 1.4;">
												<strong><?php echo $pickup_loc; ?></strong><br>
												<span style="color: #64748b; font-size: 13px;"><?php echo $pickup_at; ?></span>
											</td>
										</tr>
										<tr>
											<td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px; font-weight: 600;">Devolución:</td>
											<td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 14px; line-height: 1.4;">
												<strong><?php echo $return_loc; ?></strong><br>
												<span style="color: #64748b; font-size: 13px;"><?php echo $return_at; ?></span>
											</td>
										</tr>
										<tr>
											<td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 14px; font-weight: 600;">Valor Total Alquiler:</td>
											<td style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; color: #0f172a; font-size: 15px; font-weight: 700;">$<?php echo $total; ?> USD</td>
										</tr>
										<tr style="background: #f0fdf4;">
											<td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; color: #166534; font-size: 14px; font-weight: 700;">Depósito Pagado (10% PayPal):</td>
											<td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; color: #15803d; font-size: 16px; font-weight: 800;">$<?php echo $deposit; ?> USD</td>
										</tr>
										<tr style="background: #fef2f2;">
											<td style="padding: 16px 20px; color: #991b1b; font-size: 15px; font-weight: 800;">Saldo Pendiente (al retirar):</td>
											<td style="padding: 16px 20px; color: #dc2626; font-size: 20px; font-weight: 900;">$<?php echo $balance; ?> USD</td>
										</tr>
									</table>
								</td>
							</tr>

							<!-- TRUST & BADGES SECTION -->
							<tr>
								<td style="padding: 0 40px 30px 40px;">
									<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border-radius: 14px; padding: 20px;">
										<tr>
											<td align="center">
												<h4 style="margin: 0 0 16px 0; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.8px;">
													🛡️ Tu Tranquilidad Es Nuestra Prioridad
												</h4>
											</td>
										</tr>
										<tr>
											<td>
												<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
													<tr>
														<td width="33.33%" align="center" style="padding: 5px;">
															<div style="font-size: 20px; margin-bottom: 4px;">🚘</div>
															<div style="font-size: 12px; font-weight: 700; color: #1e293b;">Vehículos Limpios</div>
															<div style="font-size: 11px; color: #64748b; margin-top: 2px;">Desinfectados e inspeccionados</div>
														</td>
														<td width="33.33%" align="center" style="padding: 5px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
															<div style="font-size: 20px; margin-bottom: 4px;">🤝</div>
															<div style="font-size: 12px; font-weight: 700; color: #1e293b;">Sin Tarifas Ocultas</div>
															<div style="font-size: 11px; color: #64748b; margin-top: 2px;">Precio final garantizado</div>
														</td>
														<td width="33.33%" align="center" style="padding: 5px;">
															<div style="font-size: 20px; margin-bottom: 4px;">📞</div>
															<div style="font-size: 12px; font-weight: 700; color: #1e293b;">Asistencia 24/7</div>
															<div style="font-size: 11px; color: #64748b; margin-top: 2px;">Siempre disponibles</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>

							<!-- CALL TO ACTION BUTTON -->
							<tr>
								<td align="center" style="padding: 0 40px 40px 40px;">
									<a href="<?php echo esc_url( $site_url . '/tracker-app/?code=' . $reference ); ?>" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #E8272C 0%, #b91c1c 100%); color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 700; padding: 16px 36px; border-radius: 50px; box-shadow: 0 10px 20px rgba(232, 39, 44, 0.3); transition: all 0.3s ease;">
										Ver / Gestionar mi Reserva →
									</a>
								</td>
							</tr>

							<!-- FOOTER -->
							<tr>
								<td style="background-color: #0f172a; padding: 30px 40px; text-align: center; color: #94a3b8; font-size: 13px; line-height: 1.6;">
									<p style="margin: 0 0 10px 0; font-weight: 600; color: #cbd5e1;">Ramirez Rent a Car</p>
									<p style="margin: 0 0 16px 0;">Tu mejor experiencia de alquiler sobre ruedas.</p>
									<div style="border-top: 1px solid #1e293b; padding-top: 16px; font-size: 11px; color: #64748b;">
										&copy; <?php echo date('Y'); ?> Ramirez Rent a Car. Todos los derechos reservados.
									</div>
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
