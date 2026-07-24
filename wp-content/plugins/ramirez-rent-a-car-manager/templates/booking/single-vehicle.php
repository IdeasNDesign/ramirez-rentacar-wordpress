<?php
/**
 * Plantilla de Ficha de Vehículo de Lujo - Estilo BRABUS Bodo V12
 * Author: Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wpdb;
$post_id = get_the_ID();

// Buscar modelo en la tabla transaccional correspondiente a este post
$model = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rrc_vehicle_models WHERE post_id = %d AND deleted_at IS NULL", $post_id ) );

if ( ! $model ) {
	echo '<div class="wrap"><p>No se encontró la configuración técnica de este modelo de vehículo.</p></div>';
	get_footer();
	exit;
}

// Inyección de imagen de fondo o destacada
$img_url = get_post_meta( $post_id, '_rrc_image_url', true );
if ( empty( $img_url ) ) {
	$img_url = 'https://img.freepik.com/vectores-premium/icono-coche-gris-silueta-coche-ilustracion-vectorial_755519-158.jpg';
}

// Buscar tarifa diaria base para mostrar dinámicamente
$rate_plan = $wpdb->get_row( $wpdb->prepare(
	"SELECT * FROM {$wpdb->prefix}rrc_rate_plans WHERE vehicle_model_id = %d AND booking_context = 'standard' AND active = 1 ORDER BY version DESC LIMIT 1",
	$model->id
) );

$base_price_display = '45';
if ( $rate_plan ) {
	$rate_package = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}rrc_rate_packages WHERE rate_plan_id = %d AND duration_unit = 'day' ORDER BY duration_value ASC LIMIT 1",
		$rate_plan->id
	) );
	if ( $rate_package ) {
		$base_price_display = round( $rate_package->total_amount );
	}
}

// Cargar ubicaciones activas
$locations = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rrc_locations WHERE is_active = 1" );

// Características por defecto
$transmission = $model->transmission ?: 'Automatic';
$passengers = $model->passenger_capacity ?: 4;
$ac_status = $model->air_conditioning ? 'Air Conditioner' : 'Manual A/C';
?>

<!-- Importar Google Fonts Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>


/* Lienzo con gradiente amarillo-naranja */
.rrc-profile-canvas {
	background: linear-gradient(135deg, #F3B03C 0%, #E76F51 100%);
	padding: 140px 40px 80px 40px;
	border-radius: 16px;
	margin: 40px auto;
	max-width: 1050px;
	box-sizing: border-box;
	display: flex;
	flex-direction: column;
	align-items: center;
	position: relative;
	font-family: 'Inter', sans-serif;
	box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

/* Tarjeta Principal Blanca */
.rrc-mockup-card {
	background: #ffffff;
	border-radius: 16px;
	box-shadow: 0 30px 60px rgba(0,0,0,0.2);
	width: 100%;
	max-width: 950px;
	padding: 50px;
	box-sizing: border-box;
	position: relative;
	display: grid;
	grid-template-columns: 1.2fr 1.3fr;
	gap: 40px;
}

/* Contenedor Imagen Superpuesta */
.rrc-card-image-box {
	position: absolute;
	top: -140px;
	left: 40px;
	width: 48%;
	pointer-events: none;
	z-index: 10;
}
.rrc-card-image-box img {
	width: 100%;
	height: auto;
	object-fit: contain;
	filter: drop-shadow(0 30px 30px rgba(0,0,0,0.35));
}

/* Columna Izquierda */
.rrc-card-left {
	margin-top: 130px; /* espacio para la imagen */
	text-align: left;
}

.rrc-label-title {
	font-size: 11px;
	font-weight: 800;
	letter-spacing: 1.5px;
	color: #94a3b8;
	text-transform: uppercase;
	margin-bottom: 12px;
	display: block;
}

/* Características Grid (Bottom Left) */
.rrc-features-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 16px 20px;
	background-color: #f8fafc;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	padding: 20px;
}
.rrc-feature-card-item {
	display: flex;
	align-items: center;
	gap: 10px;
	font-size: 13px;
	color: #475569;
	font-weight: 600;
}
.rrc-feature-card-icon {
	font-size: 18px;
}

/* Columna Derecha */
.rrc-card-right {
	text-align: left;
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
}

.rrc-brand-logo-container {
	display: flex;
	flex-direction: column;
	align-items: flex-end;
	margin-bottom: 20px;
	width: 100%;
}
.rrc-brand-model {
	font-size: 56px;
	font-weight: 900;
	letter-spacing: 1px;
	color: #E2E8F0;
	text-transform: uppercase;
	line-height: 1;
	text-align: right;
}

/* Persuasive description */
.rrc-persuasive-description {
	font-size: 13.5px;
	color: #475569;
	line-height: 1.6;
	font-weight: 500;
	text-align: left;
	margin-bottom: 25px;
	background-color: #f8fafc;
	border-left: 3px solid #F3B03C;
	padding: 12px 16px;
	border-radius: 0 8px 8px 0;
}

/* ========================================== */
/* CALCULADORA INLINE MUY MODERNA             */
/* ========================================== --> */
.rrc-inline-calculator {
	background: #ffffff;
	border: 1.5px solid #e2e8f0;
	border-radius: 12px;
	padding: 20px;
	box-shadow: 0 4px 15px rgba(0,0,0,0.02);
	margin-bottom: 20px;
}

/* Tab Selector de Renta */
.rrc-rent-term-select {
	display: flex;
	background: #f1f5f9;
	padding: 4px;
	border-radius: 8px;
	margin-bottom: 15px;
}
.rrc-rent-term-btn {
	flex: 1;
	border: none;
	background: transparent;
	padding: 8px;
	font-size: 12px;
	font-weight: 700;
	border-radius: 6px;
	cursor: pointer;
	color: #64748b;
	transition: all 0.25s ease;
	text-align: center;
}
.rrc-rent-term-btn.active {
	background: #ffffff;
	color: #0f172a;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Inputs de Fecha y Ubicación */
.rrc-calc-grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 12px;
}
.rrc-calc-field {
	display: flex;
	flex-direction: column;
	gap: 4px;
}
.rrc-calc-label {
	font-size: 11px;
	font-weight: 800;
	color: #64748b;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.rrc-calc-input {
	height: 38px;
	border: 1px solid #cbd5e1;
	border-radius: 6px;
	padding: 0 10px;
	font-family: inherit;
	font-size: 13px;
	color: #1e293b;
	outline: none;
	background: #ffffff;
	transition: border-color 0.2s;
}
.rrc-calc-input:focus {
	border-color: #F3B03C;
}

/* Price & Button inline */
.rrc-calc-result-row {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-top: 20px;
	border-top: 1px solid #f1f5f9;
	padding-top: 15px;
}
.rrc-price-display-wrapper {
	display: flex;
	align-items: baseline;
	transition: transform 0.2s ease-in-out;
}
.rrc-price-display-wrapper.pulse {
	transform: scale(1.08);
}
.rrc-calculated-price {
	font-size: 30px;
	font-weight: 900;
	color: #2B7A4B;
	line-height: 1;
}
.rrc-calculated-period {
	font-size: 13px;
	color: #64748b;
	font-weight: 600;
	margin-left: 8px;
}

.rrc-book-btn {
	background-color: #E8272C;
	color: #ffffff;
	font-size: 13px;
	font-weight: 800;
	border: none;
	border-radius: 20px;
	padding: 12px 30px;
	cursor: pointer;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	box-shadow: 0 4px 10px rgba(232,39,44,0.3);
	transition: all 0.2s;
}
.rrc-book-btn:hover {
	background-color: #c61d22;
	box-shadow: 0 6px 15px rgba(232,39,44,0.45);
}

/* ========================================== */
/* CAJÓN DESPLEGABLE DE DATOS DEL CONDUCTOR    */
/* ========================================== --> */
.rrc-driver-drawer {
	max-height: 0;
	opacity: 0;
	overflow: hidden;
	transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease-out;
	border-top: 1px dashed #cbd5e1;
	margin-top: 15px;
	padding-top: 0;
}
.rrc-driver-drawer.open {
	max-height: 1000px;
	opacity: 1;
	padding-top: 15px;
}
.rrc-drawer-title {
	font-size: 14px;
	font-weight: 700;
	color: #334155;
	margin-bottom: 12px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}
.rrc-drawer-form-row {
	display: flex;
	gap: 12px;
	margin-bottom: 12px;
}
.rrc-drawer-input {
	width: 100%;
	height: 36px;
	border: 1px solid #cbd5e1;
	border-radius: 4px;
	padding: 0 10px;
	font-family: inherit;
	font-size: 13px;
	color: #334155;
	outline: none;
	box-sizing: border-box;
}
.rrc-drawer-input:focus {
	border-color: #F3B03C;
}
.rrc-confirm-btn {
	width: 100%;
	height: 38px;
	background-color: #2B7A4B;
	color: #ffffff;
	font-size: 13px;
	font-weight: 800;
	border: none;
	border-radius: 6px;
	cursor: pointer;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	transition: background-color 0.2s;
	margin-top: 5px;
}
.rrc-confirm-btn:hover {
	background-color: #225F3A;
}

/* Mensaje de éxito Inline */
.rrc-inline-success {
	background-color: #d1fae5;
	border: 1px solid #10b981;
	border-radius: 8px;
	padding: 15px;
	color: #065f46;
	font-size: 13px;
	line-height: 1.5;
	margin-top: 15px;
	display: none;
}

/* Footer de Autor */
.rrc-proud-footer {
	margin-top: 30px;
	display: flex;
	align-items: center;
	gap: 10px;
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	color: #ffffff;
	opacity: 0.85;
}
.rrc-proud-footer strong {
	font-size: 14px;
}

@media (max-width: 768px) {
	.rrc-mockup-card {
		grid-template-columns: 1fr;
		padding: 30px;
	}
	.rrc-card-image-box {
		position: relative;
		top: 0;
		left: 0;
		width: 100%;
		margin-bottom: 20px;
	}
	.rrc-card-left {
		margin-top: 0;
	}
}
</style>

<div class="rrc-profile-canvas">
	
	<!-- Botón de Retroceso -->
	<div style="position: absolute; top: 30px; left: 40px;">
		<a href="javascript:history.back()" style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; background-color: rgba(255,255,255,0.2); border-radius: 50%; text-decoration: none; color: #fff; font-size: 20px; font-weight: 900; transition: background-color 0.2s;">←</a>
	</div>

	<!-- TARJETA PRINCIPAL DEL COCHE -->
	<div class="rrc-mockup-card">
		
		<!-- Imagen Superpuesta en Absoluto -->
		<div class="rrc-card-image-box">
			<img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr( $model->public_name ); ?>">
		</div>

		<!-- COLUMNA IZQUIERDA -->
		<div class="rrc-card-left">
			<!-- Características Section -->
			<span class="rrc-label-title">Características</span>
			<div class="rrc-features-grid">
				<?php if ( stripos( $model->public_name, 'Sorento' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span>Aire acondicionado</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Combustible: gasolina</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>4 puertas</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>Hasta 7 pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🪑</span>
						<span>3 filas de asientos</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción FWD o AWD, según la unidad</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">💼</span>
						<span>Espacio para equipaje</span>
					</div>
				<?php elseif ( stripos( $model->public_name, 'ATV' ) !== false || stripos( $model->category, 'ATV' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática CVT</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>1 pasajero</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Combustible: gasolina</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>Sin puertas</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción 2WD/4WD</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🧺</span>
						<span>Parrillas delantera/trasera</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🌴</span>
						<span>Uso recreativo</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🪖</span>
						<span>Casco de seguridad incluido</span>
					</div>
				<?php elseif ( stripos( $model->public_name, 'Gladiator' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>5 pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span>Aire acondicionado</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Combustible: gasolina</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>4 puertas</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción 4x4</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">📦</span>
						<span>Caja de carga trasera</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛰️</span>
						<span>Mayor altura libre al suelo</span>
					</div>
				<?php elseif ( stripos( $model->public_name, 'Truck' ) !== false || stripos( $model->category, 'Truck' ) !== false || stripos( $model->public_name, 'Pickup' ) !== false || stripos( $model->public_name, 'Hilux' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>5 pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span>Aire acondicionado</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Combustible: diésel o gasolina</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>4 puertas</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción 4x4</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">📦</span>
						<span>Caja de carga trasera</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛰️</span>
						<span>Mayor altura libre al suelo</span>
					</div>
				<?php elseif ( stripos( $model->public_name, 'Jeep' ) !== false || stripos( $model->category, 'Jeep' ) !== false || stripos( $model->public_name, 'Wrangler' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>5 pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span>Aire acondicionado</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Gasolina o según disp.</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>4 puertas</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción 4x4</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">💼</span>
						<span>Espacio para equipaje</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛰️</span>
						<span>Mayor altura sobre el terreno</span>
					</div>
				<?php elseif ( stripos( $model->public_name, 'Minivan' ) !== false || stripos( $model->category, 'Minivan' ) !== false || stripos( $model->public_name, 'Sienna' ) !== false || stripos( $model->public_name, '7 Pass' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>7 pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span>Aire acondicionado</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Gasolina/híbrido</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>2 puertas corredizas</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción según disponibilidad</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🪑</span>
						<span>3 filas de asientos</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">💼</span>
						<span>Espacio para equipaje</span>
					</div>
				<?php elseif ( stripos( $model->public_name, 'Luxury SUV' ) !== false || stripos( $model->category, 'Luxury SUV' ) !== false || stripos( $model->public_name, 'Telluride' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>7 pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span>Aire acondicionado</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Combustible: gasolina</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>4 puertas y portón trasero</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción FWD o AWD según disp.</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🛋️</span>
						<span>Amplio espacio interior</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">💼</span>
						<span>Capacidad para equipaje</span>
					</div>
				<?php elseif ( stripos( $model->public_name, 'Standard SUV' ) !== false || stripos( $model->category, 'Standard SUV' ) !== false || stripos( $model->public_name, 'Qashqai' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>5 pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span>Aire acondicionado</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Combustible: gasolina</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>4 puertas locales</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción delantera FWD</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">💼</span>
						<span>Espacio para equipaje</span>
					</div>
				<?php elseif ( stripos( $model->public_name, 'Sedan' ) !== false || stripos( $model->public_name, 'Four-Door' ) !== false ) : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span>Transmisión automática</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span>5 pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span>Aire acondicionado</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Combustible: gasolina</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span>4 puertas</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción delantera FWD</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">💼</span>
						<span>Maletero para equipaje</span>
					</div>
				<?php else : ?>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⚙️</span>
						<span><?php echo $transmission === 'Automatic' ? 'Transmisión automática' : 'Transmisión manual'; ?></span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">👥</span>
						<span><?php echo esc_html($passengers); ?> pasajeros</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">❄️</span>
						<span><?php echo strpos($ac_status, 'Air') !== false ? 'Aire acondicionado' : 'A/C manual'; ?></span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">⛽</span>
						<span>Combustible: <?php echo strtolower($model->fuel_type) === 'gasoline' ? 'gasolina' : 'diésel'; ?></span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚪</span>
						<span><?php echo esc_html($model->doors ?: 4); ?> puertas</span>
					</div>
					<div class="rrc-feature-card-item">
						<span class="rrc-feature-card-icon">🚗</span>
						<span>Tracción <?php echo esc_html($model->drive_type ?: 'FWD'); ?></span>
					</div>
				<?php endif; ?>
			</div>
			<!-- Disclaimer and Important Notice -->
			<div style="font-size: 11px; color: #64748b; line-height: 1.5; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-style: italic;">
				* Imagen de referencia. La marca, modelo, año, color, versión y equipamiento pueden variar según disponibilidad. Se entregará un vehículo de la misma categoría o características equivalentes.<br><br>
				<?php if ( stripos( $model->public_name, 'Gladiator' ) !== false ) : ?>
					* La tracción 4x4 no autoriza la conducción en playas, terrenos privados, caminos cerrados ni zonas no permitidas por la empresa.<br><br>
					* La caja de carga es abierta. Se recomienda proteger adecuadamente maletas y objetos personales contra la lluvia; la caja trasera no garantiza protección contra lluvia ni seguridad para objetos dejados sin supervisión.
				<?php elseif ( stripos( $model->public_name, 'Truck' ) !== false || stripos( $model->category, 'Truck' ) !== false || stripos( $model->public_name, 'Pickup' ) !== false || stripos( $model->public_name, 'Hilux' ) !== false ) : ?>
					* La tracción 4x4 no autoriza la conducción en playas, terrenos privados, caminos cerrados ni zonas no permitidas por la empresa.<br><br>
					* La caja de carga puede estar abierta y no garantiza protección contra lluvia, pérdida, daño o robo de equipaje y objetos personales.
				<?php elseif ( stripos( $model->public_name, 'Jeep' ) !== false || stripos( $model->category, 'Jeep' ) !== false || stripos( $model->public_name, 'Wrangler' ) !== false ) : ?>
					* La tracción 4x4 no autoriza la conducción en playas, terrenos privados, caminos cerrados ni zonas no permitidas por la empresa.
				<?php endif; ?>
			</div>
		</div>

		<!-- COLUMNA DERECHA -->
		<div class="rrc-card-right">
			
			<div class="rrc-brand-logo-container">
				<div class="rrc-brand-model"><?php echo esc_html($model->model ?: $model->public_name); ?></div>
			</div>

			<!-- Persuasive description (ideal for families or couples exploring Roatan) -->
			<div class="rrc-persuasive-description">
				<?php 
				if ( stripos( $model->public_name, 'Sorento' ) !== false ) {
					echo "Un SUV amplio y versátil, ideal para familias y grupos que desean recorrer Roatán con comodidad, seguridad y suficiente espacio para pasajeros y equipaje.<br><br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad máxima:</span> 7 pasajeros<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad recomendada con equipaje:</span> 4 o 5 pasajeros";
				} elseif ( stripos( $model->public_name, 'Gladiator' ) !== false ) {
					echo "Pickup 4x4 resistente, cómoda y versátil, ideal para parejas, familias y grupos pequeños que necesitan mayor altura, espacio interior y capacidad adicional de carga para recorrer las rutas autorizadas de Roatán.<br><br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad máxima:</span> 5 pasajeros<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Equipaje:</span> sujeto al tamaño y cantidad del equipaje; la caja de carga es abierta.";
				} elseif ( stripos( $model->public_name, 'Truck' ) !== false || stripos( $model->category, 'Truck' ) !== false || stripos( $model->public_name, 'Pickup' ) !== false || stripos( $model->public_name, 'Hilux' ) !== false ) {
					echo "Pickup 4x4 resistente, espaciosa y versátil, ideal para familias, grupos pequeños y viajeros que necesitan mayor capacidad de carga para recorrer cómodamente las rutas autorizadas de Roatán.<br><br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad máxima:</span> 5 pasajeros<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad recomendada con equipaje:</span> 4 pasajeros<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Equipaje:</span> sujeto al tamaño y cantidad; la caja de carga puede estar abierta.";
				} elseif ( stripos( $model->public_name, 'Minivan' ) !== false || stripos( $model->category, 'Minivan' ) !== false || stripos( $model->public_name, 'Sienna' ) !== false || stripos( $model->public_name, '7 Pass' ) !== false ) {
					echo "Minivan amplia, cómoda y versátil, ideal para familias y grupos que necesitan tres filas de asientos y espacio para equipaje mientras visitan las playas y principales destinos de Roatán.<br><br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad máxima:</span> 7 pasajeros<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad recomendada con equipaje:</span> 5 pasajeros";
				} elseif ( stripos( $model->public_name, 'ATV' ) !== false || stripos( $model->category, 'ATV' ) !== false ) {
					echo "Una alternativa aventurera para explorar rutas y caminos permitidos de Roatán. Ágil, resistente y diseñada para una experiencia individual al aire libre.<br><br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Edad mínima del conductor:</span> 21 años<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Licencia requerida:</span> Sí<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Peso máximo permitido:</span> 150 kg<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Depósito de seguridad:</span> Sí<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Restricciones de circulación:</span> Caminos permitidos<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Políticas:</span> Casco obligatorio";
				} elseif ( stripos( $model->public_name, 'Jeep' ) !== false || stripos( $model->category, 'Jeep' ) !== false || stripos( $model->public_name, 'Wrangler' ) !== false ) {
					echo "Jeep 4x4 resistente, cómodo y versátil, ideal para parejas, familias y grupos pequeños que desean recorrer las rutas autorizadas de Roatán con mayor altura, espacio y confianza.<br><br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad máxima:</span> 5 pasajeros<br>";
					echo "<span style='font-size:12px; font-weight:700; color:#1e293b;'>Capacidad recomendada con equipaje:</span> 4 pasajeros";
				} elseif ( stripos( $model->public_name, 'Luxury SUV' ) !== false || stripos( $model->category, 'Luxury SUV' ) !== false || stripos( $model->public_name, 'Telluride' ) !== false ) {
					echo "SUV premium, amplio y cómodo, ideal para familias y grupos que desean recorrer Roatán con mayor espacio, comodidad y seguridad. Ofrece tres filas de asientos, aire acondicionado y excelente capacidad para pasajeros y equipaje.";
				} elseif ( stripos( $model->public_name, 'Standard SUV' ) !== false || stripos( $model->category, 'Standard SUV' ) !== false || stripos( $model->public_name, 'Qashqai' ) !== false ) {
					echo "SUV moderno, cómodo y versátil, ideal para familias y grupos pequeños que desean recorrer Roatán con mayor espacio para pasajeros y equipaje.";
				} elseif ($model->passenger_capacity >= 5) {
					echo "Este vehículo es ideal para familias que buscan comodidad, seguridad y espacio de equipaje óptimo mientras recorren las paradisíacas playas de Roatán juntos.";
				} else {
					echo "La elección perfecta para parejas que buscan una aventura dinámica, facilidad para estacionarse y total libertad para descubrir cada rincón secreto de Roatán.";
				}
				?>
			</div>

			<!-- ========================================== -->
			<!-- CALCULADORA INLINE DENTRO DE LA TARJETA     -->
			<!-- ========================================== -->
			<div class="rrc-inline-calculator">
				<form id="rrc-inline-reservation-form">
					<input type="hidden" id="calc_model_id" value="<?php echo intval( $model->id ); ?>">
					
					<!-- Rentar Por Selector (Tabs) -->
					<div class="rrc-rent-term-select">
						<button type="button" class="rrc-rent-term-btn active" data-term="day">Días</button>
						<button type="button" class="rrc-rent-term-btn" data-term="week">Semanas</button>
						<button type="button" class="rrc-rent-term-btn" data-term="month">Meses</button>
					</div>

					<div class="rrc-calc-grid">
						<!-- Pickup Location -->
						<div class="rrc-calc-field">
							<label class="rrc-calc-label">Pickup Location</label>
							<select id="calc_pickup_loc" class="rrc-calc-input">
								<?php foreach ( $locations as $loc ): ?>
									<option value="<?php echo intval( $loc->id ); ?>"><?php echo esc_html( $loc->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<!-- Pickup Date & Time -->
						<div class="rrc-calc-field">
							<label class="rrc-calc-label">Pickup Date & Time</label>
							<input type="datetime-local" id="calc_pickup_at" class="rrc-calc-input" required>
						</div>

						<!-- Contenedores condicionales -->
						<div id="rrc-term-fields-container">
							<!-- Campos para Días -->
							<div id="rrc-fields-day" class="rrc-calc-field">
								<label class="rrc-calc-label">Return Date & Time</label>
								<input type="datetime-local" id="calc_return_at" class="rrc-calc-input" required>
							</div>

							<!-- Campos para Semanas -->
							<div id="rrc-fields-week" class="rrc-calc-field" style="display: none;">
								<label class="rrc-calc-label">Número de Semanas</label>
								<select id="calc_weeks_count" class="rrc-calc-input">
									<?php for ($w = 1; $w <= 10; $w++): ?>
										<option value="<?php echo $w; ?>"><?php echo $w; ?> <?php echo $w === 1 ? 'semana' : 'semanas'; ?></option>
									<?php endfor; ?>
								</select>
							</div>

							<!-- Campos para Meses -->
							<div id="rrc-fields-month" class="rrc-calc-field" style="display: none;">
								<label class="rrc-calc-label">Número de Meses</label>
								<select id="calc_months_count" class="rrc-calc-input">
									<?php for ($m = 1; $m <= 12; $m++): ?>
										<option value="<?php echo $m; ?>"><?php echo $m; ?> <?php echo $m === 1 ? 'mes' : 'meses'; ?></option>
									<?php endfor; ?>
								</select>
							</div>
						</div>
					</div>

					<!-- Price & Book Now button row -->
					<div class="rrc-calc-result-row">
						<div class="rrc-price-display-wrapper" id="rrc-price-wrapper">
							<div class="rrc-calculated-price" id="rrc-calculated-price">$0</div>
							<div class="rrc-calculated-period" id="rrc-calculated-period-label">for 10 days</div>
						</div>
						<button type="button" class="rrc-book-btn" id="rrc-book-now-trigger">Rent Now</button>
					</div>

					<!-- ========================================== -->
					<!-- CAJÓN CONDUCTOR (SLIDES DOWN INTERACTIVAMENTE) -->
					<!-- ========================================== -->
					<div class="rrc-driver-drawer" id="rrc-driver-drawer">
						<div class="rrc-drawer-title">Driver details</div>
						
						<div class="rrc-drawer-form-row">
							<input type="text" id="cust_first_name" placeholder="First name" class="rrc-drawer-input" required>
							<input type="text" id="cust_last_name" placeholder="Last name" class="rrc-drawer-input" required>
						</div>

						<div style="margin-bottom: 12px;">
							<input type="email" id="cust_email" placeholder="Enter your email address" class="rrc-drawer-input" required>
						</div>

						<div style="margin-bottom: 15px;">
							<input type="tel" id="cust_phone" placeholder="Enter phone number" class="rrc-drawer-input" required>
						</div>

						<!-- breakdown box -->
						<div id="rrc-payment-breakdown" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:15px; margin-bottom:15px; font-size:13px; color:#334155;">
							<div style="font-weight:800; color:#0f172a; margin-bottom:8px; border-bottom:1px solid #cbd5e1; padding-bottom:4px; text-transform:uppercase; font-size:11px; letter-spacing:0.5px; display:flex; justify-space-between; align-items:center;">
								<span>Desglose de Pago (PayPal 10%)</span>
								<a href="<?php echo esc_url( site_url( '/deposito-de-reserva-10/' ) ); ?>" target="_blank" style="color:#E8272C; text-decoration:none; font-size:11px; font-weight:700;">¿Por qué el 10%? ℹ️</a>
							</div>
							<div style="display:flex; justify-content:space-between; margin-bottom:4px;">
								<span>Total Alquiler:</span>
								<strong id="rrc-breakdown-total">$0.00 USD</strong>
							</div>
							<div style="display:flex; justify-content:space-between; margin-bottom:4px; color:#166534; font-weight:700;">
								<span>Depósito Requerido (10%):</span>
								<strong id="rrc-breakdown-deposit">$0.00 USD</strong>
							</div>
							<div style="display:flex; justify-content:space-between; color:#dc2626; font-weight:700;">
								<span>Saldo Pendiente (al retirar):</span>
								<strong id="rrc-breakdown-balance">$0.00 USD</strong>
							</div>
						</div>

						<!-- Legal Checkbox -->
						<div style="margin-bottom:15px; font-size:12px; color:#475569; display:flex; gap:8px; align-items:flex-start;">
							<input type="checkbox" id="rrc-deposit-terms" required style="margin-top:2px; cursor:pointer;">
							<label for="rrc-deposit-terms" style="cursor:pointer; line-height:1.4;">
								Entiendo que el pago realizado corresponde únicamente al <strong>depósito de reserva del 10%</strong> y que el 90% restante quedará pendiente para pagarse al momento de retirar el vehículo.
							</label>
						</div>

						<button type="submit" class="rrc-confirm-btn" id="rrc-submit-booking-btn" style="height: 48px; font-size: 14px; background-color: #E8272C;">PAY 10% SECURITY DEPOSIT</button>

						<!-- PayPal Buttons Container -->
						<div id="rrc-paypal-container" style="display:none; margin-top:15px;"></div>
					</div>
				</form>

				<!-- Inline Success Block -->
				<div class="rrc-inline-success" id="rrc-success-msg"></div>
			</div>

		</div>

	</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Elementos
	const modelId = document.getElementById('calc_model_id').value;
	const pickupInput = document.getElementById('calc_pickup_at');
	const returnInput = document.getElementById('calc_return_at');
	const weeksSelect = document.getElementById('calc_weeks_count');
	const monthsSelect = document.getElementById('calc_months_count');
	const pickupLocSelect = document.getElementById('calc_pickup_loc');

	const rentalPeriodText = document.getElementById('rrc-calculated-period-label');
	const calculatedPriceText = document.getElementById('rrc-calculated-price');
	const priceWrapper = document.getElementById('rrc-price-wrapper');

	const bookNowTrigger = document.getElementById('rrc-book-now-trigger');
	const driverDrawer = document.getElementById('rrc-driver-drawer');

	// Tabs term selector
	const termBtns = document.querySelectorAll('.rrc-rent-term-btn');
	let activeTerm = 'day'; // day, week, month

	termBtns.forEach(btn => {
		btn.addEventListener('click', function() {
			termBtns.forEach(b => b.classList.remove('active'));
			this.classList.add('active');
			activeTerm = this.dataset.term;

			document.getElementById('rrc-fields-day').style.display = activeTerm === 'day' ? 'block' : 'none';
			document.getElementById('rrc-fields-week').style.display = activeTerm === 'week' ? 'block' : 'none';
			document.getElementById('rrc-fields-month').style.display = activeTerm === 'month' ? 'block' : 'none';

			updateCalculatedReturnDate();
			updatePrice();
		});
	});

	// Toggle drawer on Book Now click
	bookNowTrigger.addEventListener('click', function() {
		driverDrawer.classList.toggle('open');
		if (driverDrawer.classList.contains('open')) {
			// Scroll suave para revelar datos del conductor si es necesario
			driverDrawer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}
	});

	// Valores por defecto (Recogida mañana a las 12:00, devolución 1 día después)
	const now = new Date();
	const tomorrow = new Date(now.getTime() + (24 * 60 * 60 * 1000));
	tomorrow.setHours(12, 0, 0, 0);

	const returnDate = new Date(tomorrow.getTime() + (1 * 24 * 60 * 60 * 1000));

	pickupInput.value = formatDateForInput(tomorrow);
	returnInput.value = formatDateForInput(returnDate);

	// Eventos
	pickupInput.addEventListener('change', () => {
		updateCalculatedReturnDate();
		updatePrice();
	});
	returnInput.addEventListener('change', updatePrice);
	weeksSelect.addEventListener('change', () => {
		updateCalculatedReturnDate();
		updatePrice();
	});
	monthsSelect.addEventListener('change', () => {
		updateCalculatedReturnDate();
		updatePrice();
	});
	pickupLocSelect.addEventListener('change', updatePrice);

	let baseAmount = 0;
	let currentDays = 1;

	// Cargar precio inicial
	updatePrice();

	function formatDateForInput(date) {
		const pad = num => String(num).padStart(2, '0');
		return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
	}

	function updateCalculatedReturnDate() {
		const pDate = new Date(pickupInput.value);
		if (isNaN(pDate)) return;

		if (activeTerm === 'week') {
			const weeks = parseInt(weeksSelect.value) || 1;
			const rDate = new Date(pDate.getTime() + (weeks * 7 * 24 * 60 * 60 * 1000));
			returnInput.value = formatDateForInput(rDate);
		} else if (activeTerm === 'month') {
			const months = parseInt(monthsSelect.value) || 1;
			const rDate = new Date(pDate.getTime() + (months * 30 * 24 * 60 * 60 * 1000));
			returnInput.value = formatDateForInput(rDate);
		}
	}

	function calculateDays() {
		const pDate = new Date(pickupInput.value);
		const rDate = new Date(returnInput.value);
		if (isNaN(pDate) || isNaN(rDate) || rDate <= pDate) {
			return 1;
		}
		const diffTime = Math.abs(rDate - pDate);
		return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) || 1;
	}

	function updatePrice() {
		const pickup = pickupInput.value;
		const returnAt = returnInput.value;
		const pickupLoc = pickupLocSelect.value;
		currentDays = calculateDays();

		rentalPeriodText.textContent = `for ${currentDays} days`;

		if (!pickup || !returnAt) return;

		fetch('<?php echo esc_url( get_rest_url( null, "ramirez-rent-a-car/v1/quotes" ) ); ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				vehicle_model_id: modelId,
				pickup_at: pickup.replace('T', ' '),
				return_at: returnAt.replace('T', ' '),
				booking_context: 'standard',
				pickup_location_id: pickupLoc,
				return_location_id: pickupLoc
			})
		})
		.then(res => res.json())
		.then(data => {
			if (data.success) {
				baseAmount = parseFloat(data.total_amount);
				
				// Animación de pulso/escalado al actualizar el precio
				priceWrapper.classList.add('pulse');
				calculatedPriceText.textContent = `$${Math.round(baseAmount)}`;
				setTimeout(() => priceWrapper.classList.remove('pulse'), 250);

				// Actualizar desglose de 10% en tiempo real
				updateBreakdownUI(baseAmount);
			} else {
				const fallbackDailyPrice = <?php echo floatval($base_price_display); ?>;
				baseAmount = fallbackDailyPrice * currentDays;
				calculatedPriceText.textContent = `$${Math.round(baseAmount)}`;
				updateBreakdownUI(baseAmount);
			}
		})
		.catch(err => {
			const fallbackDailyPrice = <?php echo floatval($base_price_display); ?>;
			baseAmount = fallbackDailyPrice * currentDays;
			calculatedPriceText.textContent = `$${Math.round(baseAmount)}`;
			updateBreakdownUI(baseAmount);
		});
	}

	function updateBreakdownUI(total) {
		const deposit = (total * 0.10).toFixed(2);
		const balance = (total - deposit).toFixed(2);
		document.getElementById('rrc-breakdown-total').textContent = `$${total.toFixed(2)} USD`;
		document.getElementById('rrc-breakdown-deposit').textContent = `$${deposit} USD`;
		document.getElementById('rrc-breakdown-balance').textContent = `$${balance} USD`;
	}

	let paypalLoaded = false;
	let currentReservationToken = null;

	function loadPayPalSDK(clientId, callback) {
		if (paypalLoaded && window.paypal) {
			callback();
			return;
		}
		const script = document.createElement('script');
		script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=USD&intent=capture`;
		script.onload = () => {
			paypalLoaded = true;
			callback();
		};
		document.head.appendChild(script);
	}

	// Procesar Reserva y habilitar PayPal
	document.getElementById('rrc-inline-reservation-form').addEventListener('submit', function(e) {
		e.preventDefault();

		const pickup = pickupInput.value;
		const returnAt = returnInput.value;
		const pickupLoc = pickupLocSelect.value;

		const firstName = document.getElementById('cust_first_name').value;
		const lastName = document.getElementById('cust_last_name').value;
		const email = document.getElementById('cust_email').value;
		const phone = document.getElementById('cust_phone').value;
		const submitBtn = document.getElementById('rrc-submit-booking-btn');

		const successMsgBox = document.getElementById('rrc-success-msg');
		submitBtn.disabled = true;
		submitBtn.textContent = 'Procesando Reserva...';

		fetch('<?php echo esc_url( get_rest_url( null, "ramirez-rent-a-car/v1/reservations" ) ); ?>', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				vehicle_model_id: modelId,
				pickup_at: pickup.replace('T', ' '),
				return_at: returnAt.replace('T', ' '),
				booking_context: 'standard',
				pickup_location_id: pickupLoc,
				return_location_id: pickupLoc,
				first_name: firstName,
				last_name: lastName,
				email: email,
				phone: phone
			})
		})
		.then(res => res.json())
		.then(data => {
			if (data.success) {
				currentReservationToken = data.lookup_token;
				
				// Ocultar botón y cargar pasarela PayPal
				submitBtn.style.display = 'none';
				const paypalContainer = document.getElementById('rrc-paypal-container');
				paypalContainer.style.display = 'block';
				paypalContainer.innerHTML = '<p style="text-align:center; font-size:12px; color:#64748b;">Cargando PayPal seguro...</p>';

				const paypalClientId = '<?php echo esc_js( defined("RRC_PAYPAL_CLIENT_ID") ? RRC_PAYPAL_CLIENT_ID : get_option("rrc_paypal_client_id", "sb") ); ?>';

				loadPayPalSDK(paypalClientId, () => {
					paypalContainer.innerHTML = '';
					window.paypal.Buttons({
						createOrder: function(data, actions) {
							return fetch(`<?php echo esc_url( get_rest_url( null, "ramirez-rent-a-car/v1/reservations/" ) ); ?>${currentReservationToken}/paypal/order`, {
								method: 'POST',
								headers: { 'Content-Type': 'application/json' }
							})
							.then(res => res.json())
							.then(orderData => {
								if (orderData.order_id) {
									return orderData.order_id;
								} else {
									alert(orderData.message || 'Error al generar la orden de PayPal.');
								}
							});
						},
						onApprove: function(data, actions) {
							paypalContainer.innerHTML = '<p style="text-align:center; font-size:13px; color:#10b981; font-weight:700;">Verificando depósito con el servidor...</p>';
							return fetch(`<?php echo esc_url( get_rest_url( null, "ramirez-rent-a-car/v1/reservations/" ) ); ?>${currentReservationToken}/paypal/capture`, {
								method: 'POST',
								headers: { 'Content-Type': 'application/json' },
								body: JSON.stringify({ order_id: data.orderID })
							})
							.then(res => res.json())
							.then(captureData => {
								if (captureData.success) {
									driverDrawer.classList.remove('open');
									successMsgBox.style.display = 'block';
									successMsgBox.innerHTML = `
										<div style="font-weight: 900; font-size: 16px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; color:#065f46;">
											🎉 ¡Depósito Recibido y Reserva Confirmada!
										</div>
										Tu reserva ha sido asegurada con la referencia <strong>#${data.public_reference || 'Confirmada'}</strong>.<br>
										Depósito pagado (10%): <strong>$${captureData.deposit_paid} USD</strong><br>
										Saldo pendiente a pagar al retirar: <strong>$${captureData.remaining_balance} USD</strong><br><br>
										Hemos enviado el comprobante oficial a tu correo electrónico.
									`;
									successMsgBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
								} else {
									alert('Error al verificar la captura: ' + captureData.message);
								}
							});
						},
						onError: function(err) {
							alert('Ocurrió un inconveniente con la pasarela de PayPal.');
						}
					}).render('#rrc-paypal-container');
				});
			} else {
				alert('Error al registrar la reserva: ' + (data.message || 'Intente de nuevo.'));
				submitBtn.disabled = false;
				submitBtn.textContent = 'PAY 10% SECURITY DEPOSIT';
			}
		})
		.catch(err => {
			alert('Ocurrió un error en el servidor. Intente de nuevo.');
			submitBtn.disabled = false;
			submitBtn.textContent = 'PAY 10% SECURITY DEPOSIT';
		});
	});
});
</script>

<?php
get_footer();
