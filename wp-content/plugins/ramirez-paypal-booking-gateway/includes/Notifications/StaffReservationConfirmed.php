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
		<html>
		<head>
			<meta charset="UTF-8">
			<title>Nueva Reserva Confirmada</title>
		</head>
		<body style="font-family: sans-serif; color: #334155; line-height: 1.6; background-color: #f8fafc; padding: 20px;">
			<div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
				<h2 style="color: #E8272C; margin-top: 0; border-bottom: 2px solid #E8272C; padding-bottom: 10px;">Nueva Reserva Confirmada</h2>
				<p>Se ha recibido y capturado con éxito el depósito del 10% mediante PayPal para la reserva <strong>#<?php echo esc_html( $res->public_reference ); ?></strong>.</p>
				
				<h3 style="color: #0f172a; margin-top: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Detalles del Cliente</h3>
				<table style="width: 100%; border-collapse: collapse;">
					<tr><td style="padding: 6px 0; font-weight: bold; width: 35%;">Cliente:</td><td><?php echo $client_name; ?></td></tr>
					<tr><td style="padding: 6px 0; font-weight: bold;">Teléfono:</td><td><?php echo $phone; ?></td></tr>
					<tr><td style="padding: 6px 0; font-weight: bold;">Email:</td><td><?php echo $email; ?></td></tr>
				</table>

				<h3 style="color: #0f172a; margin-top: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Detalles del Alquiler</h3>
				<table style="width: 100%; border-collapse: collapse;">
					<tr><td style="padding: 6px 0; font-weight: bold; width: 35%;">Vehículo:</td><td><?php echo $vehicle; ?> (<?php echo $passengers; ?> Pasajeros)</td></tr>
					<tr><td style="padding: 6px 0; font-weight: bold;">Fechas:</td><td>Desde <?php echo $pickup_date; ?> hasta <?php echo $return_date; ?></td></tr>
					<tr><td style="padding: 6px 0; font-weight: bold;">Ubicación Retiro:</td><td><?php echo $pickup_loc; ?></td></tr>
					<tr><td style="padding: 6px 0; font-weight: bold;">Ubicación Devolución:</td><td><?php echo $return_loc; ?></td></tr>
				</table>

				<h3 style="color: #0f172a; margin-top: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Información de Pago (10% Depósito)</h3>
				<table style="width: 100%; border-collapse: collapse; background-color: #f1f5f9; padding: 15px; border-radius: 6px; margin-top: 10px;">
					<tr><td style="padding: 8px; font-weight: bold; width: 35%;">Total Alquiler:</td><td style="padding: 8px;">$<?php echo $total; ?> USD</td></tr>
					<tr><td style="padding: 8px; font-weight: bold; color: #16a34a;">Depósito Pagado:</td><td style="padding: 8px; font-weight: bold; color: #16a34a;">$<?php echo $deposit; ?> USD</td></tr>
					<tr><td style="padding: 8px; font-weight: bold; color: #dc2626;">Saldo Restante:</td><td style="padding: 8px; font-weight: bold; color: #dc2626;">$<?php echo $balance; ?> USD</td></tr>
					<tr><td style="padding: 8px; font-weight: bold;">PayPal Order ID:</td><td style="padding: 8px;"><code><?php echo esc_html( $order_id ); ?></code></td></tr>
					<tr><td style="padding: 8px; font-weight: bold;">PayPal Capture ID:</td><td style="padding: 8px;"><code><?php echo esc_html( $capture_id ); ?></code></td></tr>
				</table>

				<h3 style="color: #0f172a; margin-top: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">Acciones Recomendadas</h3>
				<div style="margin-top: 15px; display: flex; gap: 10px;">
					<a href="<?php echo esc_url( $admin_url ); ?>" style="background: #E8272C; color: #ffffff; padding: 10px 18px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 13px;">Abrir Reserva en WordPress</a>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}
