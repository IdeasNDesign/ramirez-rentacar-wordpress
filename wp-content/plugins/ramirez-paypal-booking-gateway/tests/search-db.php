<?php
/**
 * Script to search the entire database for a string.
 * Author: Break The Mold
 */

require_once __DIR__ . '/../../../../wp-load.php';

// Security check: Must be logged in as administrator
if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	if ( PHP_SAPI !== 'cli' ) {
		wp_die( 'Acceso denegado.' );
	}
}

global $wpdb;
$search = '99-03-96-16';

echo "<h3>Buscando '$search' en toda la base de datos...</h3>";

// Get all tables
$tables = $wpdb->get_col( "SHOW TABLES" );

foreach ( $tables as $table ) {
	// Get columns for this table
	$columns = $wpdb->get_col( "DESCRIBE `$table`" );
	
	foreach ( $columns as $column ) {
		// Search in this column
		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM `$table` WHERE `$column` LIKE %s",
			'%' . $wpdb->esc_like( $search ) . '%'
		);
		$count = $wpdb->get_var( $query );
		
		if ( $count > 0 ) {
			echo "🔍 Encontrado en tabla: <strong>$table</strong> $\rightarrow$ columna: <strong>$column</strong> ($count coincidencias)<br>";
			
			// Show a snippet of the matching rows
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `$table` WHERE `$column` LIKE %s LIMIT 3",
				'%' . $wpdb->esc_like( $search ) . '%'
			), ARRAY_A );
			
			foreach ( $rows as $row ) {
				// Find primary key or ID
				$id_field = isset( $row['ID'] ) ? 'ID' : ( isset( $row['id'] ) ? 'id' : key( $row ) );
				$id_val = $row[$id_field] ?? 'N/A';
				echo "&nbsp;&nbsp;&nbsp;&nbsp;↳ Registro ID ($id_field): <strong>$id_val</strong> | Contenido: " . esc_html( substr( serialize( $row ), 0, 200 ) ) . "...<br>";
			}
			echo "<br>";
		}
	}
}

echo "<h4>Búsqueda finalizada.</h4>";
