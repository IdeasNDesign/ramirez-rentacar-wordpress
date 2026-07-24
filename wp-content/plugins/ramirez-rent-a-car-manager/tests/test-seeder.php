<?php
/**
 * Test de inicialización de Base de Datos y Creación de Posts del Catálogo
 */
define( 'ABSPATH', true );
define( 'RRC_PATH', dirname( __DIR__ ) . '/' );

// Stub WordPress database & functions for CLI testing
class WPDB_Stub {
	public $prefix = 'wp_';
	public $posts = 'wp_posts';
	public function get_charset_collate() { return 'DEFAULT CHARSET=utf8'; }
	public function get_var($query) { return null; }
	public function insert($table, $data) { return true; }
	public function update($table, $data, $where) { return true; }
	public function prepare($query, ...$args) { return $query; }
}
$wpdb = new WPDB_Stub();

function current_time($type) { return date('Y-m-d H:i:s'); }
function wp_generate_password($l, $s) { return 'pass1234'; }
function wp_insert_post($args) { return 99; }
function register_post_type($type, $args) {}
function register_taxonomy($tax, $type, $args) {}

require_once RRC_PATH . 'includes/Autoloader.php';

// Cargar y testear Seeder
require_once RRC_PATH . 'includes/Database/SeedManager.php';
\RamirezRentACar\Database\SeedManager::run();

echo "Ejecución simulada del seeder completada sin errores PHP!\n";
