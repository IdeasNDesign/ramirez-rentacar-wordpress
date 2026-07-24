<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Admin;

use BreakTheMold\RamirezPayPal\Core\ServiceContainer;

class SettingsPage {
	private $container;

	public function __construct( ServiceContainer $container ) {
		$this->container = $container;
	}

	public function render() {
		// Save settings if POSTed
		if ( isset( $_POST['rrc_paypal_save_settings'] ) && check_admin_referer( 'rrc_paypal_settings_nonce' ) ) {
			$this->save_settings();
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configuración guardada.', 'ramirez-paypal-booking-gateway' ) . '</p></div>';
		}

		$settings = $this->container->get( 'settings' );
		$webhook_url = rest_url( 'ramirez-paypal/v1/webhook' );

		// Load settings values
		$deposit_enabled = $settings->is_deposit_enabled();
		$deposit_percentage = $settings->get_deposit_percentage();
		$currency = $settings->get_currency();
		$remaining_balance_due = $settings->get_remaining_balance_due();
		$auto_confirm = $settings->should_auto_confirm();
		$hold_duration = $settings->get_hold_duration();

		$client_id = $settings->get_client_id();
		$client_secret = $settings->get_client_secret();
		$webhook_id = $settings->get_webhook_id();
		$environment = $settings->get_environment();
		?>
		<div class="wrap rrc-paypal-admin-wrap">
			<style>
				.rrc-paypal-card {
					background: #111827;
					color: #f3f4f6;
					border-radius: 12px;
					padding: 30px;
					margin-top: 20px;
					max-width: 900px;
					border: 1px solid #374151;
					box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5);
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				}
				.rrc-paypal-card h2 {
					color: #ffffff;
					font-size: 24px;
					margin-top: 0;
					border-bottom: 2px solid #E8272C;
					padding-bottom: 12px;
				}
				.rrc-paypal-card label {
					display: block;
					font-weight: 600;
					margin-bottom: 8px;
					color: #9ca3af;
				}
				.rrc-paypal-card input[type="text"], 
				.rrc-paypal-card input[type="number"], 
				.rrc-paypal-card select {
					background: #1f2937;
					border: 1px solid #4b5563;
					color: #ffffff;
					padding: 10px 14px;
					border-radius: 6px;
					width: 100%;
					max-width: 450px;
					box-sizing: border-box;
					margin-bottom: 20px;
				}
				.rrc-paypal-card input[type="text"]:focus, 
				.rrc-paypal-card input[type="number"]:focus, 
				.rrc-paypal-card select:focus {
					border-color: #E8272C;
					box-shadow: 0 0 0 2px rgba(232, 39, 44, 0.2);
					outline: none;
				}
				.rrc-paypal-btn {
					background: #E8272C;
					color: #ffffff;
					border: none;
					padding: 12px 24px;
					font-weight: bold;
					border-radius: 6px;
					cursor: pointer;
					transition: background 0.2s;
				}
				.rrc-paypal-btn:hover {
					background: #c51c20;
				}
				.rrc-paypal-btn-secondary {
					background: #374151;
					color: #ffffff;
					border: 1px solid #4b5563;
					padding: 12px 24px;
					border-radius: 6px;
					cursor: pointer;
					font-weight: bold;
				}
				.rrc-paypal-btn-secondary:hover {
					background: #4b5563;
				}
				.rrc-paypal-badge {
					display: inline-block;
					padding: 4px 10px;
					border-radius: 50px;
					font-size: 12px;
					font-weight: bold;
					text-transform: uppercase;
				}
				.rrc-paypal-badge-sandbox {
					background: rgba(245, 158, 11, 0.2);
					color: #f59e0b;
				}
				.rrc-paypal-badge-live {
					background: rgba(16, 185, 129, 0.2);
					color: #10b981;
				}
				.rrc-paypal-wizard-step {
					background: #1f2937;
					border-left: 4px solid #E8272C;
					padding: 15px 20px;
					border-radius: 0 8px 8px 0;
					margin-bottom: 15px;
				}
				.rrc-paypal-webhook-box {
					background: #1f2937;
					padding: 15px;
					border-radius: 6px;
					border: 1px dashed #4b5563;
					display: flex;
					justify-content: space-between;
					align-items: center;
					margin-bottom: 20px;
				}
				.rrc-paypal-webhook-url {
					font-family: monospace;
					color: #60a5fa;
					font-size: 14px;
				}
			</style>

			<h1><?php echo esc_html__( 'Ramirez PayPal Booking Gateway', 'ramirez-paypal-booking-gateway' ); ?></h1>

			<div class="rrc-paypal-card">
				<h2>PayPal Connection</h2>
				<p>
					Entorno actual: 
					<?php if ( $environment === 'live' ) : ?>
						<span class="rrc-paypal-badge rrc-paypal-badge-live">Live</span>
					<?php else : ?>
						<span class="rrc-paypal-badge rrc-paypal-badge-sandbox">Sandbox</span>
					<?php endif; ?>
				</p>

				<form method="post" action="">
					<?php wp_nonce_field( 'rrc_paypal_settings_nonce' ); ?>

					<label for="rrc_paypal_environment">Entorno</label>
					<select name="rrc_paypal_environment" id="rrc_paypal_environment">
						<option value="sandbox" <?php selected( $environment, 'sandbox' ); ?>>Sandbox</option>
						<option value="live" <?php selected( $environment, 'live' ); ?>>Live</option>
					</select>

					<label for="rrc_paypal_client_id">Client ID</label>
					<input type="text" name="rrc_paypal_client_id" id="rrc_paypal_client_id" value="<?php echo esc_attr( $client_id ); ?>" placeholder="Client ID de PayPal Developer Dashboard" />

					<label for="rrc_paypal_client_secret">Client Secret</label>
					<input type="text" name="rrc_paypal_client_secret" id="rrc_paypal_client_secret" value="<?php echo ! empty( $client_secret ) ? '••••••••••••••••••••••••••••••••' : ''; ?>" placeholder="<?php echo ! empty( $client_secret ) ? 'Cifrado y guardado' : 'Client Secret de PayPal Developer Dashboard'; ?>" />

					<label for="rrc_paypal_webhook_id">Webhook ID</label>
					<input type="text" name="rrc_paypal_webhook_id" id="rrc_paypal_webhook_id" value="<?php echo esc_attr( $webhook_id ); ?>" placeholder="Webhook ID" />

					<hr style="border: 0; border-top: 1px solid #374151; margin: 30px 0;" />

					<h2>Configuración del Depósito de Reserva</h2>

					<label for="rrc_paypal_deposit_enabled">
						<input type="checkbox" name="rrc_paypal_deposit_enabled" id="rrc_paypal_deposit_enabled" value="1" <?php checked( $deposit_enabled, true ); ?> />
						Habilitar Depósito de Reserva
					</label>
					<p class="description" style="color: #9ca3af; margin-bottom: 20px;">
						Si está activo, el cliente solo pagará el depósito configurado mediante PayPal para confirmar la reserva. El saldo restante se pagará al recoger el vehículo.
					</p>

					<label for="rrc_paypal_deposit_percentage">Porcentaje del Depósito (%)</label>
					<input type="number" step="0.01" name="rrc_paypal_deposit_percentage" id="rrc_paypal_deposit_percentage" value="<?php echo esc_attr( $deposit_percentage ); ?>" />

					<label for="rrc_paypal_currency">Moneda</label>
					<select name="rrc_paypal_currency" id="rrc_paypal_currency">
						<option value="USD" <?php selected( $currency, 'USD' ); ?>>USD ($)</option>
						<option value="EUR" <?php selected( $currency, 'EUR' ); ?>>EUR (€)</option>
						<option value="COP" <?php selected( $currency, 'COP' ); ?>>COP ($)</option>
					</select>

					<label for="rrc_paypal_hold_duration_minutes">Duración del bloqueo temporal (Minutos)</label>
					<input type="number" name="rrc_paypal_hold_duration_minutes" id="rrc_paypal_hold_duration_minutes" value="<?php echo esc_attr( $hold_duration ); ?>" />

					<label for="rrc_paypal_auto_confirm_reservation">
						<input type="checkbox" name="rrc_paypal_auto_confirm_reservation" id="rrc_paypal_auto_confirm_reservation" value="1" <?php checked( $auto_confirm, true ); ?> />
						Confirmar reserva automáticamente al recibir el pago del depósito
					</label>

					<div style="margin-top: 30px;">
						<button type="submit" name="rrc_paypal_save_settings" class="rrc-paypal-btn">Guardar Configuración</button>
					</div>
				</form>
			</div>

			<div class="rrc-paypal-card" style="margin-top: 30px;">
				<h2>Asistente de Conexión (Wizard)</h2>
				<div class="rrc-paypal-wizard-step">
					<strong>Paso 1: Configurar webhook en el portal de PayPal Developer</strong>
					<p>Copie la siguiente URL de webhook generada dinámicamente y regístrela en su aplicación de PayPal en la consola Developer:</p>
					<div class="rrc-paypal-webhook-box">
						<span class="rrc-paypal-webhook-url" id="rrc-paypal-webhook-url-text"><?php echo esc_url( $webhook_url ); ?></span>
						<button type="button" onclick="navigator.clipboard.writeText(document.getElementById('rrc-paypal-webhook-url-text').innerText); alert('Copiado');" class="rrc-paypal-btn-secondary" style="padding: 6px 12px; font-size: 12px;">Copiar URL</button>
					</div>
					<?php if ( strpos( $webhook_url, 'localhost' ) !== false || strpos( $webhook_url, '127.0.0.1' ) !== false ) : ?>
						<div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 6px; padding: 16px; margin-bottom: 20px; color: #fca5a5; font-size: 13px; line-height: 1.5;">
							<strong>⚠️ Entorno Local Detectado (localhost):</strong><br>
							PayPal no puede enviar notificaciones webhook directas a direcciones locales. Para realizar pruebas en este servidor:
							<ol style="margin-top: 8px; padding-left: 20px; list-style-type: decimal;">
								<li>Inicie un túnel inverso en su terminal con: <code>ngrok http 80</code></li>
								<li>Copie la URL pública HTTPS que le asigne ngrok (ej: <code>https://abc123.ngrok-free.app</code>)</li>
								<li>En su panel de PayPal Developer, registre la URL del webhook sustituyendo el host local, quedando de la siguiente forma:<br>
								<code>https://[su-subdominio].ngrok-free.app/ramirezrentacar/wp-json/ramirez-paypal/v1/webhook</code></li>
							</ol>
						</div>
					<?php endif; ?>
					<p>Asegúrese de seleccionar los siguientes eventos al configurar el webhook:</p>
					<ul style="list-style-type: disc; padding-left: 20px; color: #9ca3af;">
						<li>CHECKOUT.ORDER.APPROVED</li>
						<li>PAYMENT.CAPTURE.COMPLETED</li>
						<li>PAYMENT.CAPTURE.DENIED</li>
						<li>PAYMENT.CAPTURE.REFUNDED</li>
					</ul>
				</div>

				<div style="display: flex; gap: 15px; margin-top: 20px;">
					<button type="button" id="btn-test-connection" class="rrc-paypal-btn-secondary">Comprobar conexión</button>
					<button type="button" id="btn-test-sandbox" class="rrc-paypal-btn-secondary">Ejecutar prueba Sandbox</button>
				</div>
				<div id="connection-test-result" style="margin-top: 15px; font-weight: bold;"></div>
			</div>
			
			<script>
				jQuery(document).ready(function($) {
					$('#btn-test-connection').on('click', function() {
						var $btn = $(this);
						$btn.prop('disabled', true).text('Conectando...');
						$('#connection-test-result').html('<span style="color:#9ca3af;">Verificando credenciales de PayPal...</span>');
						
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'rrc_paypal_check_connection'
							},
							success: function(response) {
								$btn.prop('disabled', false).text('Comprobar conexión');
								if (response.success) {
									$('#connection-test-result').html('<span style="color:#10b981;">● Conectado a ' + response.data.environment + ' (Token OAuth obtenido con éxito)</span>');
								} else {
									$('#connection-test-result').html('<span style="color:#ef4444;">● Error de conexión: ' + response.data.message + '</span>');
								}
							},
							error: function() {
								$btn.prop('disabled', false).text('Comprobar conexión');
								$('#connection-test-result').html('<span style="color:#ef4444;">● Error del servidor al procesar la solicitud AJAX.</span>');
							}
						});
					});
				});
			</script>
		</div>
		<?php
	}

	private function save_settings() {
		$environment = sanitize_text_field( $_POST['rrc_paypal_environment'] ?? 'sandbox' );
		$client_id = sanitize_text_field( $_POST['rrc_paypal_client_id'] ?? '' );
		$client_secret_input = sanitize_text_field( $_POST['rrc_paypal_client_secret'] ?? '' );
		$webhook_id = sanitize_text_field( $_POST['rrc_paypal_webhook_id'] ?? '' );

		$credentials_provider = $this->container->get( 'credentials_provider' );

		// Only save client secret if it changed (not equal to placeholder mask)
		$client_secret = '';
		if ( $client_secret_input !== '••••••••••••••••••••••••••••••••' ) {
			$client_secret = $client_secret_input;
		}

		$credentials_provider->save_credentials( $client_id, $client_secret, $webhook_id, $environment );

		update_option( 'rrc_paypal_deposit_enabled', isset( $_POST['rrc_paypal_deposit_enabled'] ) ? 1 : 0 );
		update_option( 'rrc_paypal_deposit_percentage', floatval( $_POST['rrc_paypal_deposit_percentage'] ?? 10.00 ) );
		update_option( 'rrc_paypal_currency', sanitize_text_field( $_POST['rrc_paypal_currency'] ?? 'USD' ) );
		update_option( 'rrc_paypal_hold_duration_minutes', intval( $_POST['rrc_paypal_hold_duration_minutes'] ?? 15 ) );
		update_option( 'rrc_paypal_auto_confirm_reservation', isset( $_POST['rrc_paypal_auto_confirm_reservation'] ) ? 1 : 0 );
	}
}
