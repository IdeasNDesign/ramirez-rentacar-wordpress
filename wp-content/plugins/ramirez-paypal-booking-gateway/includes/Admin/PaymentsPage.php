<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Admin;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;

class PaymentsPage {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function render() {
		global $wpdb;
		$payments_table = $wpdb->prefix . 'rrc_payments';
		$res_table = $wpdb->prefix . 'rrc_reservations';
		$cust_table = $wpdb->prefix . 'rrc_customers';

		// Query payments with reservation details
		$query = "SELECT p.*, r.public_reference, r.total_amount as res_total, c.first_name, c.last_name 
		          FROM {$payments_table} p
		          JOIN {$res_table} r ON p.reservation_id = r.id
		          JOIN {$cust_table} c ON r.customer_id = c.id
		          ORDER BY p.id DESC LIMIT 50";

		$payments = $wpdb->get_results( $query );

		?>
		<div class="wrap rrc-paypal-admin-wrap">
			<style>
				.rrc-payments-table {
					width: 100%;
					border-collapse: collapse;
					margin-top: 20px;
					background: #111827;
					color: #f3f4f6;
					border-radius: 8px;
					overflow: hidden;
				}
				.rrc-payments-table th {
					background: #1f2937;
					padding: 12px 16px;
					text-align: left;
					font-weight: 600;
					color: #9ca3af;
					border-bottom: 1px solid #374151;
				}
				.rrc-payments-table td {
					padding: 12px 16px;
					border-bottom: 1px solid #374151;
				}
				.rrc-status-badge {
					display: inline-block;
					padding: 3px 8px;
					border-radius: 4px;
					font-size: 11px;
					font-weight: bold;
					text-transform: uppercase;
				}
				.rrc-status-completed {
					background: rgba(16, 185, 129, 0.2);
					color: #10b981;
				}
				.rrc-status-pending {
					background: rgba(245, 158, 11, 0.2);
					color: #f59e0b;
				}
				.rrc-status-failed {
					background: rgba(239, 68, 68, 0.2);
					color: #ef4444;
				}
			</style>

			<h1><?php echo esc_html__( 'Registro de Pagos PayPal', 'ramirez-paypal-booking-gateway' ); ?></h1>

			<table class="rrc-payments-table">
				<thead>
					<tr>
						<th>ID</th>
						<th>Referencia Reserva</th>
						<th>Cliente</th>
						<th>Orden PayPal</th>
						<th>Depósito Cobrado</th>
						<th>Total Alquiler</th>
						<th>Entorno</th>
						<th>Estado</th>
						<th>Fecha</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $payments ) ) : ?>
						<tr>
							<td colspan="9" style="text-align: center; color: #9ca3af;">No se han registrado pagos aún.</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $payments as $p ) : ?>
							<tr>
								<td><?php echo intval( $p->id ); ?></td>
								<td><strong>#<?php echo esc_html( $p->public_reference ); ?></strong></td>
								<td><?php echo esc_html( $p->first_name . ' ' . $p->last_name ); ?></td>
								<td><code><?php echo esc_html( $p->provider_order_id ); ?></code></td>
								<td><?php echo esc_html( $p->currency . ' ' . number_format( $p->amount, 2 ) ); ?></td>
								<td><?php echo esc_html( $p->currency . ' ' . number_format( $p->res_total, 2 ) ); ?></td>
								<td>
									<span class="rrc-paypal-badge <?php echo $p->environment === 'live' ? 'rrc-paypal-badge-live' : 'rrc-paypal-badge-sandbox'; ?>">
										<?php echo esc_html( $p->environment ); ?>
									</span>
								</td>
								<td>
									<?php 
									$status_class = 'rrc-status-pending';
									if ( $p->status === 'COMPLETED' ) {
										$status_class = 'rrc-status-completed';
									} elseif ( in_array( $p->status, [ 'FAILED', 'DENIED' ] ) ) {
										$status_class = 'rrc-status-failed';
									}
									?>
									<span class="rrc-status-badge <?php echo $status_class; ?>">
										<?php echo esc_html( $p->status ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $p->created_at ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
