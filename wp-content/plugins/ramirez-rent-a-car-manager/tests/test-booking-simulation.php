<?php
/**
 * Test de simulación de Reserva y Disponibilidad en Ramirez Rent A Car Manager
 */
define( 'ABSPATH', true );
define( 'RRC_PATH', dirname( __DIR__ ) . '/' );

// Requerir el cargador automático
require_once RRC_PATH . 'includes/Autoloader.php';

// Stub de WordPress para ejecutarlo en línea de comandos (CLI) sin inicializar todo el CMS
class WPDB_Stub {
	public $prefix = 'wp_';
	public function get_results( $query ) {
		// Simular unidades físicas disponibles para el ID de modelo 1 (Sedan)
		if ( strpos( $query, 'rrc_vehicle_units' ) !== false ) {
			return [
				(object) [ 'id' => 10, 'vehicle_model_id' => 1, 'unit_code' => 'UNIT-SEDAN-01', 'status' => 'available' ]
			];
		}
		// Simular que el plan de tarifas activo existe
		if ( strpos( $query, 'rrc_rate_plans' ) !== false ) {
			return (object) [ 'id' => 100, 'vehicle_model_id' => 1, 'booking_context' => 'standard', 'active' => 1, 'version' => 1 ];
		}
		// Simular el paquete de tarifas
		if ( strpos( $query, 'rrc_rate_packages' ) !== false ) {
			return [
				(object) [ 'id' => 200, 'rate_plan_id' => 100, 'duration_unit' => 'day', 'duration_value' => 1, 'normalized_days' => 1, 'total_amount' => '60.00', 'stackable' => 1, 'label' => '1 Day Package' ],
				(object) [ 'id' => 201, 'rate_plan_id' => 100, 'duration_unit' => 'day', 'duration_value' => 2, 'normalized_days' => 2, 'total_amount' => '110.00', 'stackable' => 1, 'label' => '2 Day Package' ]
			];
		}
		return [];
	}
	public function get_row( $query ) {
		return (object) [ 'id' => 100, 'vehicle_model_id' => 1, 'booking_context' => 'standard', 'active' => 1, 'version' => 1 ];
	}
	public function get_var( $query ) {
		// Simular que no hay bloqueos de fechas activos (retorna 0 bloqueos)
		if ( strpos( $query, 'rrc_unit_day_locks' ) !== false ) {
			return 0;
		}
		return null;
	}
	public function prepare( $query, ...$args ) {
		return $query;
	}
}

global $wpdb;
$wpdb = new WPDB_Stub();

use RamirezRentACar\Domain\Availability\AvailabilityService;
use RamirezRentACar\Domain\Rates\PackageRateEngine;

echo "=== INICIANDO SIMULACIÓN DE PRUEBA ===\n";

// Datos de consulta de entrada
$pickup = '2026-07-20 10:00:00';
$return = '2026-07-22 10:00:00'; // 2 días exactos
$model_id = 1; // Sedan
$context = 'standard';

echo "Buscando disponibilidad para Sedan desde $pickup hasta $return...\n";
$available_units = AvailabilityService::check_availability( $model_id, $pickup, $return );

if ( ! empty( $available_units ) ) {
	echo "¡ÉXITO! Unidades físicas disponibles encontradas: " . count( $available_units ) . " (" . $available_units[0]->unit_code . ")\n";
	
	// Calcular precio en base a tarifas
	$days = PackageRateEngine::calculate_days( $pickup, $return );
	echo "Días de alquiler calculados: $days día(s)\n";
	
	$rate_resolved = PackageRateEngine::resolve_rate( $model_id, $context, $days );
	if ( ! $rate_resolved['requires_manual_quote'] ) {
		echo "Tarifa resuelta con éxito:\n";
		echo " - Total a pagar: $" . $rate_resolved['total_amount'] . " USD\n";
		echo " - Detalle de paquetes: " . $rate_resolved['breakdown'][0]['label'] . " (x" . $rate_resolved['breakdown'][0]['quantity'] . ")\n";
	} else {
		echo "La tarifa requiere cotización manual.\n";
	}
} else {
	echo "Sin disponibilidad de vehículos para las fechas seleccionadas.\n";
}

echo "=== FIN DE LA SIMULACIÓN ===\n";
