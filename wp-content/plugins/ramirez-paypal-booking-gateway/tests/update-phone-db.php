<?php
/**
 * Script to update the office phone number in the database.
 * Author: Break The Mold
 */

require_once __DIR__ . '/../../../../wp-load.php';

// Security check: Must be logged in as administrator to run this script online
if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	if ( PHP_SAPI !== 'cli' ) {
		wp_die( 'Acceso no autorizado. Debes iniciar sesión como administrador de WordPress para ejecutar esta actualización.' );
	}
}

global $wpdb;
$table = $wpdb->prefix . 'rrc_locations';

// Check if table exists
if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
	$updated = $wpdb->query( "UPDATE `$table` SET phone = '(504) 99-03-96-16' WHERE phone = '(504) 24-45-01-58' OR phone = '24-45-01-58'" );
	echo "<h3>Actualización de Teléfono en la Base de Datos</h3>";
	echo "✓ Registros de sucursales actualizados: <strong>$updated</strong><br>";
	echo "<h4 style='color: green;'>¡Listo! El teléfono de contacto ha sido actualizado con éxito al móvil (+504) 99-03-96-16.</h4>";
} else {
	echo "La tabla de sucursales ($table) no existe en esta base de datos.";
}
