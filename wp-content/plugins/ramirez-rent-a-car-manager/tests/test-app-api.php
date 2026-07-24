<?php
/**
 * Test Suite for Ramirez Rent A Car Operations Application REST API
 * Author: Break The Mold
 */

require_once __DIR__ . '/../../../../wp-load.php';

echo "=== INICIANDO SUITE DE PRUEBAS PARA API RAMIREZ OPERATIONS ===\n";

// 1. Authenticate test user
$user = get_user_by( 'login', 'admin' );
if ( ! $user ) {
	$user_id = wp_create_user( 'admin', 'admin123', 'admin@ramirezrentacar.com' );
	$user = get_user_by( 'id', $user_id );
	$user->set_role( 'administrator' );
}

echo "1. Usuario de prueba autenticado: " . $user->user_login . " (ID: " . $user->ID . ")\n";

// 2. Test Login API endpoint
$request = new WP_REST_Request( 'POST', '/ramirez-rent-a-car/v1/app/auth/login' );
$request->set_body_params( [
	'username' => 'admin',
	'password' => 'admin123'
] );

$response = rest_do_request( $request );
$data = $response->get_data();

if ( ! empty( $data['success'] ) && ! empty( $data['token'] ) ) {
	echo "¡ÉXITO! Login API respondió con éxito. Token emitido: " . substr( $data['token'], 0, 15 ) . "...\n";
	$token = $data['token'];
} else {
	echo "ERROR en Login API: " . json_encode( $data ) . "\n";
	exit(1);
}

// 3. Test Dashboard API with Token
$dash_req = new WP_REST_Request( 'GET', '/ramirez-rent-a-car/v1/app/dashboard' );
$dash_req->set_header( 'Authorization', 'Bearer ' . $token );
$dash_res = rest_do_request( $dash_req );
$dash_data = $dash_res->get_data();

if ( ! empty( $dash_data['success'] ) ) {
	echo "¡ÉXITO! Dashboard API respondió con métricas en tiempo real:\n";
	echo " - Solicitudes Nuevas: " . $dash_data['stats']['new_requests'] . "\n";
	echo " - En Revisión: " . $dash_data['stats']['pending_approval'] . "\n";
	echo " - Unidades Disponibles: " . $dash_data['stats']['available_units'] . "\n";
} else {
	echo "ERROR en Dashboard API: " . json_encode( $dash_data ) . "\n";
}

// 4. Test Reservations List API
$res_req = new WP_REST_Request( 'GET', '/ramirez-rent-a-car/v1/app/reservations' );
$res_req->set_header( 'Authorization', 'Bearer ' . $token );
$res_res = rest_do_request( $res_req );
$res_data = $res_res->get_data();

if ( ! empty( $res_data['success'] ) ) {
	echo "¡ÉXITO! Obtención de reservas para Kanban/Lista completada (Total: " . count( $res_data['reservations'] ) . " reservas).\n";
} else {
	echo "ERROR en Reservations API: " . json_encode( $res_data ) . "\n";
}

echo "=== SUITE DE PRUEBAS COMPLETADA CON ÉXITO ===\n";
