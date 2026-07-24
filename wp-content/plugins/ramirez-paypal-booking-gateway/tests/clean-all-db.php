<?php
/**
 * Script to completely empty reservation-related tables for production launch.
 * Author: Break The Mold
 */

require_once __DIR__ . '/../../../../wp-load.php';

// Security check: Must be logged in as administrator to run this script online
if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	if ( PHP_SAPI !== 'cli' ) {
		wp_die( 'Acceso no autorizado. Debes iniciar sesión como administrador de WordPress para ejecutar esta limpieza.' );
	}
}

global $wpdb;

// Define tables to clean
$tables = [
	$wpdb->prefix . 'rrc_reservations',
	$wpdb->prefix . 'rrc_payments',
	$wpdb->prefix . 'rrc_refunds',
	$wpdb->prefix . 'rrc_customers',
	$wpdb->prefix . 'rrc_drivers',
	$wpdb->prefix . 'rrc_documents',
	$wpdb->prefix . 'rrc_unit_day_locks',
	$wpdb->prefix . 'rrc_notifications',
	$wpdb->prefix . 'rrc_audit_log'
];

echo "<h3>Iniciando Limpieza Completa de la Base de Datos</h3>";

// Disable foreign key checks temporarily if any exist
$wpdb->query( "SET FOREIGN_KEY_CHECKS = 0;" );

foreach ( $tables as $table ) {
	// Check if table exists
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
		// Delete all rows
		$wpdb->query( "DELETE FROM `$table`" );
		// Reset auto increment
		$wpdb->query( "ALTER TABLE `$table` AUTO_INCREMENT = 1" );
		echo "✓ Tabla vaciada y reiniciada: <strong>$table</strong><br>";
	}
}

// Re-enable foreign key checks
$wpdb->query( "SET FOREIGN_KEY_CHECKS = 1;" );

echo "<h4 style='color: green;'>¡Limpieza completada con éxito! La base de datos está vacía y lista para recibir reservas reales.</h4>";
