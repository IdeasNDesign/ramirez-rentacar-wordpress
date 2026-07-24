<?php
namespace BreakTheMold\RamirezAIAssistant\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Run table migration on activation
		register_activation_hook( RRCAIA_BASENAME, [ $this, 'activate' ] );

		// Load REST routes
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Render chat widget footer injection if active
		add_action( 'wp_footer', [ $this, 'render_chat_widget_html' ] );

		// Register shortcode
		add_shortcode( 'ramirez_ai_assistant', [ $this, 'render_shortcode' ] );
		add_shortcode( 'rrc_cruise_banner', [ $this, 'render_cruise_banner' ] );

		// Elementor Integration
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widget' ] );

		// Admin menu
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
	}

	public function activate() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'rrc_ai_sessions';

		$sql = "CREATE TABLE `$table_name` (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_token varchar(64) NOT NULL,
			state_json text NOT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY session_token (session_token)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Update vehicle models table schema
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';
		
		$columns_to_add = [
			'recommended_passenger_capacity' => 'int(3) DEFAULT NULL',
			'luggage_capacity_large'         => 'int(3) DEFAULT NULL',
			'luggage_capacity_small'         => 'int(3) DEFAULT NULL',
			'cargo_notes'                    => 'text DEFAULT NULL',
			'child_seat_capacity'            => 'int(3) DEFAULT NULL',
			'maximum_combined_occupancy'     => 'int(3) DEFAULT NULL',
			'capacity_verified'              => 'tinyint(1) DEFAULT 0',
			'capacity_verified_at'           => 'datetime DEFAULT NULL',
			'capacity_verified_by'           => 'bigint(20) DEFAULT NULL'
		];

		foreach ( $columns_to_add as $col => $type ) {
			$col_check = $wpdb->get_results( "SHOW COLUMNS FROM `$models_table` LIKE '$col'" );
			if ( empty( $col_check ) ) {
				$wpdb->query( "ALTER TABLE `$models_table` ADD `$col` $type" );
			}
		}

		// Seed/Update capacities
		$capacities = [
			'sedan-4d'           => [ 'p' => 5, 'rp' => 4, 'l' => 2, 's' => 2, 'v' => 1 ],
			'atv-standard'       => [ 'p' => 2, 'rp' => 2, 'l' => 0, 's' => 0, 'v' => 1 ],
			'suv-standard'       => [ 'p' => 5, 'rp' => 4, 'l' => 3, 's' => 2, 'v' => 1 ],
			'suv-medium-sorento' => [ 'p' => 7, 'rp' => 6, 'l' => 2, 's' => 2, 'v' => 1 ],
			'suv-luxury'         => [ 'p' => 7, 'rp' => 6, 'l' => 3, 's' => 3, 'v' => 1 ],
			'suv-premium-prado'  => [ 'p' => 7, 'rp' => 6, 'l' => 3, 's' => 2, 'v' => 1 ],
			'jeep-standard'      => [ 'p' => 5, 'rp' => 4, 'l' => 2, 's' => 2, 'v' => 1 ],
			'jeep-gladiator'     => [ 'p' => 5, 'rp' => 4, 'l' => 2, 's' => 2, 'v' => 1 ],
			'truck-4x4'          => [ 'p' => 5, 'rp' => 4, 'l' => 4, 's' => 2, 'v' => 1 ],
			'van-7p'             => [ 'p' => 7, 'rp' => 6, 'l' => 3, 's' => 2, 'v' => 1 ],
			'van-15p'            => [ 'p' => 15, 'rp' => 12, 'l' => 6, 's' => 4, 'v' => 1 ]
		];

		foreach ( $capacities as $code => $data ) {
			$wpdb->update( $models_table, [
				'passenger_capacity'             => $data['p'],
				'recommended_passenger_capacity' => $data['rp'],
				'luggage_capacity_large'         => $data['l'],
				'luggage_capacity_small'         => $data['s'],
				'capacity_verified'              => $data['v'],
				'capacity_verified_at'           => current_time( 'mysql' )
			], [ 'internal_code' => $code ] );
		}
	}

	public function register_rest_routes() {
		$controller = new \BreakTheMold\RamirezAIAssistant\REST\ChatRestController();
		$controller->register_routes();
	}

	public function enqueue_assets() {
		// Register CSS & JS
		wp_enqueue_style( 'rrc-ai-chat-css', RRCAIA_URL . 'assets/chat-widget.css', [], RRCAIA_VERSION );
		wp_enqueue_script( 'rrc-ai-chat-js', RRCAIA_URL . 'assets/chat-widget.js', [ 'jquery' ], RRCAIA_VERSION, true );

		// Localize values for JS
		wp_localize_script( 'rrc-ai-chat-js', 'RrcAiAssistant', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'rest_url' => esc_url_raw( rest_url( 'ramirez-rent-a-car-ai-assistant/v1' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'current_lang' => ( function_exists( 'BreakTheMold\AITranslator\Language\LanguageResolver::resolve' ) )
				? \BreakTheMold\AITranslator\Language\LanguageResolver::resolve()
				: 'es'
		] );
	}

	public function render_chat_widget_html() {
		// Auto render if active globally, or let Elementor handle it
		if ( get_option( 'rrc_ai_assistant_active', true ) ) {
			echo do_shortcode( '[ramirez_ai_assistant]' );
		}
	}

	public function render_shortcode() {
		ob_start();
		$avatar = get_option( 'rrc_ai_assistant_avatar', content_url( '/uploads/2026/asistente virtual.webp' ) );
		$name = get_option( 'rrc_ai_assistant_name', 'Sara' );
		?>
		<div id="rrc-ai-chat-root" class="rrc-ai-chat-widget collapsed">
			<div class="rrc-ai-chat-trigger" onclick="toggleRrcChat()">
				<div class="rrc-ai-chat-tooltip">¿Hablamos? 👋</div>
				<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="rrc-ai-chat-trigger-avatar" />
				<span class="rrc-ai-chat-trigger-badge"></span>
			</div>
			
			<div class="rrc-ai-chat-window">
				<div class="rrc-ai-chat-header">
					<div class="rrc-ai-chat-header-info">
						<img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="rrc-ai-chat-avatar" />
						<div>
							<h4 class="rrc-ai-chat-name"><?php echo esc_html( $name ); ?></h4>
							<span class="rrc-ai-chat-status">Asistente en línea</span>
						</div>
					</div>
					<button class="rrc-ai-chat-close" onclick="toggleRrcChat()">&times;</button>
				</div>
				<div class="rrc-ai-chat-messages" id="rrc-ai-chat-messages-container">
					<!-- Messages load dynamically -->
				</div>
				<div class="rrc-ai-chat-input-area">
					<div class="rrc-ai-chat-quick-buttons" id="rrc-ai-chat-quick-buttons-container">
						<!-- Contextual buttons -->
					</div>
					<form id="rrc-ai-chat-form" onsubmit="sendRrcChatMessage(event)">
						<input type="text" id="rrc-ai-chat-input" placeholder="Escribe tu mensaje aquí..." required autocomplete="off" />
						<button type="submit" class="rrc-ai-chat-send-btn">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
						</button>
					</form>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function register_elementor_widget( $widgets_manager ) {
		if ( class_exists( '\Elementor\Widget_Base' ) ) {
			// Register Widget (will implement the Elementor widget file)
			require_once RRCAIA_PATH . 'includes/Elementor/AIWidget.php';
			$widgets_manager->register( new \BreakTheMold\RamirezAIAssistant\Elementor\AIWidget() );
		}
	}

	public function register_admin_menu() {
		add_submenu_page(
			'ramirez-rent-a-car',
			'AI Vehicle Knowledge',
			'AI Vehicle Knowledge',
			'manage_options',
			'rrc-ai-vehicle-knowledge',
			[ $this, 'render_vehicle_knowledge_page' ]
		);
	}

	public function render_vehicle_knowledge_page() {
		global $wpdb;
		$models_table = $wpdb->prefix . 'rrc_vehicle_models';

		// Handle updates
		if ( isset( $_POST['save_vehicle_ai_knowledge'] ) && check_admin_referer( 'rrc_save_vehicle_ai_knowledge' ) ) {
			$model_id = intval( $_POST['model_id'] );
			$wpdb->update( $models_table, [
				'passenger_capacity'             => intval( $_POST['passenger_capacity'] ),
				'recommended_passenger_capacity' => intval( $_POST['recommended_passenger_capacity'] ),
				'luggage_capacity_large'         => intval( $_POST['luggage_capacity_large'] ),
				'luggage_capacity_small'         => intval( $_POST['luggage_capacity_small'] ),
				'capacity_verified'              => isset( $_POST['capacity_verified'] ) ? 1 : 0,
				'capacity_verified_at'           => current_time( 'mysql' ),
				'capacity_verified_by'           => get_current_user_id()
			], [ 'id' => $model_id ] );
			echo '<div class="notice notice-success is-dismissible"><p>Conocimiento del vehículo actualizado correctamente.</p></div>';
		}

		$vehicles = $wpdb->get_results( "SELECT * FROM $models_table" );
		?>
		<div class="wrap" style="font-family: 'Inter Tight', sans-serif;">
			<h1>Ramirez Rent A Car > AI Assistant > Vehicle Knowledge</h1>
			<p>Gestione el conocimiento estructurado que utiliza el Asistente IA para validar capacidades de pasajeros y equipaje.</p>
			
			<table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
				<thead>
					<tr>
						<th><strong>Vehículo</strong></th>
						<th><strong>Capacidad Legal</strong></th>
						<th><strong>Capacidad Recomendada</strong></th>
						<th><strong>Maletas Grandes</strong></th>
						<th><strong>Maletas Pequeñas</strong></th>
						<th><strong>Estado Verificación</strong></th>
						<th><strong>Exclusión</strong></th>
						<th><strong>Acción</strong></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $vehicles as $v ) : 
						$missing = [];
						if ( empty( $v->passenger_capacity ) ) $missing[] = 'Capacidad';
						if ( empty( $v->luggage_capacity_large ) ) $missing[] = 'Maletas';
						
						$verified = ! empty( $v->capacity_verified );
						$available_rec = $verified && $v->passenger_capacity > 0;
						?>
						<tr>
							<td><strong><?php echo esc_html( $v->public_name ); ?></strong><br><small>Código: <code><?php echo esc_html( $v->internal_code ); ?></code></small></td>
							<td><?php echo esc_html( $v->passenger_capacity ?: 'Por confirmar' ); ?></td>
							<td><?php echo esc_html( $v->recommended_passenger_capacity ?: 'Por confirmar' ); ?></td>
							<td><?php echo esc_html( $v->luggage_capacity_large ?: '0' ); ?></td>
							<td><?php echo esc_html( $v->luggage_capacity_small ?: '0' ); ?></td>
							<td>
								<?php if ( $verified ) : ?>
									<span style="color:#10b981; font-weight:bold;">✓ Verificado</span><br>
									<small><?php echo esc_html( $v->capacity_verified_at ); ?></small>
								<?php else : ?>
									<span style="color:#ef4444; font-weight:bold;">✗ CAPACITY_UNVERIFIED</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $available_rec ) : ?>
									<span style="color:#10b981; font-weight:bold;">Elegible</span>
								<?php else : ?>
									<span style="color:#ef4444; font-weight:bold;">Excluido</span><br>
									<small style="color:#dc2626;">Falta verificación de capacidad</small>
								<?php endif; ?>
							</td>
							<td>
								<button class="button" onclick="jQuery('#form-<?php echo $v->id; ?>').toggle()">Editar</button>
							</td>
						</tr>
						<tr id="form-<?php echo $v->id; ?>" style="display:none; background:#f8fafc;">
							<td colspan="8" style="padding:15px;">
								<form method="POST">
									<?php wp_nonce_field( 'rrc_save_vehicle_ai_knowledge' ); ?>
									<input type="hidden" name="model_id" value="<?php echo $v->id; ?>" />
									<div style="display:flex; gap:15px; flex-wrap:wrap;">
										<label>Capacidad Legal: <input type="number" name="passenger_capacity" value="<?php echo esc_attr( $v->passenger_capacity ); ?>" style="width:70px;" /></label>
										<label>Capacidad Recomendada: <input type="number" name="recommended_passenger_capacity" value="<?php echo esc_attr( $v->recommended_passenger_capacity ); ?>" style="width:70px;" /></label>
										<label>Maletas Grandes: <input type="number" name="luggage_capacity_large" value="<?php echo esc_attr( $v->luggage_capacity_large ); ?>" style="width:70px;" /></label>
										<label>Maletas Pequeñas: <input type="number" name="luggage_capacity_small" value="<?php echo esc_attr( $v->luggage_capacity_small ); ?>" style="width:70px;" /></label>
										<label><input type="checkbox" name="capacity_verified" value="1" <?php checked( $v->capacity_verified, 1 ); ?> /> Capacidad Verificada</label>
										<input type="submit" name="save_vehicle_ai_knowledge" class="button button-primary" value="Guardar" />
									</div>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function render_cruise_banner() {
		ob_start();
		$img_url = content_url( '/uploads/2026/Muelles de Cruceros.jpg' );
		?>
		<div class="rrc-cruise-banner-card">
			<div class="rrc-cruise-banner-flex">
				<!-- Image Block -->
				<div class="rrc-cruise-banner-img-col">
					<img src="<?php echo esc_url( $img_url ); ?>" alt="Crucero en Roatán" />
					<div class="rrc-img-overlay"></div>
				</div>
				
				<!-- Description Block -->
				<div class="rrc-cruise-banner-desc-col">
					<span class="rrc-badge-promo">PROMO EXCLUSIVA</span>
					<h3 class="rrc-cruise-banner-title">Tarifas especiales para cruceros</h3>
					<p class="rrc-cruise-banner-subtitle">Tarifa de 1 día ideal para pasajeros de cruceros.</p>
					<ul class="rrc-cruise-banner-bullets">
						<li>
							<span class="rrc-checkmark-badge">✓</span>
							<span class="rrc-bullet-text">Recogida y entrega en los puertos de cruceros de Roatán.</span>
						</li>
						<li>
							<span class="rrc-checkmark-badge">✓</span>
							<span class="rrc-bullet-text">Precios especiales por un día (aplican restricciones).</span>
						</li>
						<li>
							<span class="rrc-checkmark-badge">✓</span>
							<span class="rrc-bullet-text">Servicio rápido para que aproveches al máximo tu tiempo en la isla.</span>
						</li>
					</ul>
				</div>
				
				<!-- Pricing Block -->
				<div class="rrc-cruise-banner-price-col">
					<div class="rrc-cruise-price-box">
						<span class="rrc-price-label">Tarifa 1 día desde</span>
						<div class="rrc-price-amount">
							<span class="rrc-price-symbol">$</span>
							<span class="rrc-price-val">55</span>
							<span class="rrc-price-currency">USD</span>
						</div>
						<span class="rrc-price-tax-note">Incluye seguro e impuestos</span>
					</div>
					
					<!-- Bottom locations indicators -->
					<div class="rrc-cruise-locations-indicators">
						<span class="rrc-loc-label">Recogida en:</span>
						<div class="rrc-loc-item">
							<svg class="rrc-loc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20h20M5 17h14M9 10l3-3 3 3M12 7v10"/></svg>
							<span>Terminal de Ferry</span>
						</div>
						<div class="rrc-loc-item">
							<svg class="rrc-loc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10v6M12 2v20M2 10h20"/></svg>
							<span>Puertos de Cruceros</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<style>
		.rrc-cruise-banner-card {
			background: #ffffff;
			border-radius: 24px;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.01);
			border: 1px solid #f1f5f9;
			overflow: hidden;
			margin: 25px 0;
			font-family: 'Inter Tight', 'Inter', sans-serif;
			transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
		}
		.rrc-cruise-banner-card:hover {
			transform: translateY(-6px);
			box-shadow: 0 20px 40px rgba(0, 0, 0, 0.07);
		}
		.rrc-cruise-banner-flex {
			display: flex;
			align-items: stretch;
			flex-wrap: wrap;
		}
		.rrc-cruise-banner-img-col {
			flex: 1 1 28%;
			min-width: 260px;
			position: relative;
			overflow: hidden;
		}
		.rrc-cruise-banner-img-col img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: block;
			transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
		}
		.rrc-cruise-banner-card:hover .rrc-cruise-banner-img-col img {
			transform: scale(1.06);
		}
		.rrc-img-overlay {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: linear-gradient(to right, rgba(0,0,0,0.1), transparent);
			pointer-events: none;
		}
		.rrc-cruise-banner-desc-col {
			flex: 2 1 42%;
			padding: 30px;
			display: flex;
			flex-direction: column;
			justify-content: center;
		}
		.rrc-badge-promo {
			align-self: flex-start;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			font-size: 10px;
			font-weight: 800;
			padding: 4px 8px;
			border-radius: 6px;
			letter-spacing: 1px;
			margin-bottom: 12px;
		}
		.rrc-cruise-banner-title {
			font-size: 24px;
			font-weight: 900;
			color: #0f172a;
			margin: 0 0 8px 0;
			font-family: 'Inter Tight', sans-serif !important;
			letter-spacing: -0.5px;
		}
		.rrc-cruise-banner-subtitle {
			font-size: 14px;
			color: #475569;
			margin: 0 0 20px 0;
			font-weight: 500;
		}
		.rrc-cruise-banner-bullets {
			list-style: none;
			padding: 0;
			margin: 0;
			display: flex;
			flex-direction: column;
			gap: 12px;
		}
		.rrc-cruise-banner-bullets li {
			display: flex;
			align-items: center;
			gap: 12px;
			font-size: 13px;
			color: #334155;
			font-weight: 600;
		}
		.rrc-checkmark-badge {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 20px;
			height: 20px;
			border-radius: 50%;
			background: rgba(232, 39, 44, 0.08);
			color: #e8272c;
			font-size: 11px;
			font-weight: bold;
			flex-shrink: 0;
		}
		.rrc-cruise-banner-price-col {
			flex: 1 1 30%;
			padding: 30px;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			border-left: 1px solid #f1f5f9;
			background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
		}
		.rrc-cruise-price-box {
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 16px;
			padding: 18px;
			text-align: left;
			margin-bottom: 16px;
			box-shadow: 0 4px 15px rgba(0,0,0,0.02);
		}
		.rrc-price-label {
			font-size: 10px;
			font-weight: 800;
			color: #64748b;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		.rrc-price-amount {
			display: flex;
			align-items: baseline;
			gap: 4px;
			margin: 6px 0;
		}
		.rrc-price-symbol {
			font-size: 22px;
			font-weight: 900;
			color: #e8272c;
		}
		.rrc-price-val {
			font-size: 42px;
			font-weight: 900;
			color: #e8272c;
			line-height: 1;
			letter-spacing: -1px;
		}
		.rrc-price-currency {
			font-size: 13px;
			font-weight: 900;
			color: #e8272c;
		}
		.rrc-price-tax-note {
			font-size: 11.5px;
			color: #64748b;
			font-weight: 500;
		}
		.rrc-cruise-locations-indicators {
			display: flex;
			align-items: center;
			gap: 12px;
			font-size: 10px;
			font-weight: 700;
			color: #475569;
			flex-wrap: wrap;
		}
		.rrc-loc-label {
			color: #64748b;
		}
		.rrc-loc-item {
			display: flex;
			align-items: center;
			gap: 4px;
			background: #ffffff;
			padding: 4px 8px;
			border-radius: 6px;
			border: 1px solid #e2e8f0;
		}
		.rrc-loc-icon {
			width: 14px;
			height: 14px;
			color: #e8272c;
		}
		@media (max-width: 768px) {
			.rrc-cruise-banner-price-col {
				border-left: none;
				border-top: 1px solid #f1f5f9;
			}
			.rrc-cruise-banner-img-col {
				max-height: 220px;
			}
		}
		</style>
		<?php
		return ob_get_clean();
	}
}
