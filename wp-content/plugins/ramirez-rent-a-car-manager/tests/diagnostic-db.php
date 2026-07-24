<?php
require 'd:/XAMPP/htdocs/ramirezrentacar/wp-load.php';
global $wpdb;
$res = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'rrc_vehicle_models');
echo "CANTIDAD_MODELOS_BD: " . count($res) . "\n";
foreach($res as $r) {
	echo "MODELO: " . $r->public_name . " Status: " . $r->status . " Deleted: " . ($r->deleted_at ? 'Yes' : 'No') . "\n";
}
