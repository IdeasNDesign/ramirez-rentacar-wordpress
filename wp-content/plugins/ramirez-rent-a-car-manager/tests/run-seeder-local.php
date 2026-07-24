<?php
/**
 * Script de CLI para importar el catálogo heredado directamente sin pasar por WP-Admin
 */
define( 'WP_USE_THEMES', false );
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

if ( ! is_admin() ) {
    // Definir constantes necesarias o forzar login si fuera requerido,
    // pero como es CLI local ejecutado por el sistema, corremos el SeedManager directamente.
}

require_once RRC_PATH . 'includes/Database/SeedManager.php';
\RamirezRentACar\Database\SeedManager::run();

// Flush rewrite rules para asegurar que los enlaces permanentes del CPT rrc_vehicle funcionen
flush_rewrite_rules();

echo "SEEDED: El catálogo heredado de vehículos, tarifas y oficinas se ha importado con éxito en la base de datos de WordPress y se han regenerado los permalinks!\n";
