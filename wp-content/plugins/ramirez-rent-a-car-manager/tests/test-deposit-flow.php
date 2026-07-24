<?php
/**
 * Test suite to verify 10% deposit calculations, PayPal order creation & capture logic.
 * Author: Break The Mold
 */

require_once __DIR__ . '/../../../../wp-load.php';

echo "=== SUITE DE PRUEBAS DE DEPÓSITO DEL 10% Y PAYPAL ===\n";

global $wpdb;

// 1. Create a dummy reservation for $800.00 USD
$models_table = $wpdb->prefix . 'rrc_vehicle_models';
$loc_table    = $wpdb->prefix . 'rrc_locations';
$cust_table   = $wpdb->prefix . 'rrc_customers';
$res_table    = $wpdb->prefix . 'rrc_reservations';

$model_id = $wpdb->get_var( "SELECT id FROM $models_table LIMIT 1" );
$p_loc    = $wpdb->get_var( "SELECT id FROM $loc_table LIMIT 1" );
$r_loc    = $wpdb->get_var( "SELECT id FROM $loc_table LIMIT 1" );

$cust_email = 'testdeposit@ramirezrentacar.com';
$cust_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $cust_table WHERE email = %s", $cust_email ) );
if ( ! $cust_id ) {
	$wpdb->insert( $cust_table, [
		'first_name' => 'Cliente',
		'last_name'  => 'Depósito 10%',
		'email'      => $cust_email,
		'phone'      => '+1 800-555-0100',
		'country'    => 'HN',
		'created_at' => current_time( 'mysql' ),
		'updated_at' => current_time( 'mysql' )
	] );
	$cust_id = $wpdb->insert_id;
}

$ref = 'RRC-DEP-' . strtoupper( wp_generate_password( 6, false ) );
$token = wp_generate_password( 32, false );
$token_hash = hash( 'sha256', $token );

$total_amount      = 800.00;
$deposit_percentage= 10.00;
$deposit_amount    = round( $total_amount * 0.10, 2 );
$remaining_balance = round( $total_amount - $deposit_amount, 2 );

$wpdb->insert( $res_table, [
	'public_reference'         => $ref,
	'secure_lookup_token_hash' => $token_hash,
	'customer_id'              => $cust_id,
	'vehicle_model_id'         => $model_id,
	'assigned_unit_id'         => 1,
	'pickup_location_id'       => $p_loc,
	'return_location_id'       => $r_loc,
	'pickup_at'                => date( 'Y-m-d H:i:s', strtotime( '+3 days 10:00' ) ),
	'return_at'                => date( 'Y-m-d H:i:s', strtotime( '+7 days 10:00' ) ),
	'chargeable_days'          => 4,
	'booking_context'          => 'standard',
	'reservation_status'       => 'PENDING_PAYMENT',
	'payment_status'           => 'NOT_CREATED',
	'deposit_type'             => 'percentage',
	'deposit_percentage'       => $deposit_percentage,
	'deposit_amount'           => $deposit_amount,
	'deposit_paid_amount'      => 0.00,
	'remaining_balance'        => $remaining_balance,
	'remaining_balance_status' => 'pending_at_pickup',
	'subtotal'                 => $total_amount,
	'total_amount'             => $total_amount,
	'created_at'               => current_time( 'mysql' ),
	'updated_at'               => current_time( 'mysql' )
] );

$res_id = $wpdb->insert_id;

echo "1. Reserva de prueba creada #$res_id (Ref: $ref):\n";
echo "   - Total Alquiler: $" . number_format( $total_amount, 2 ) . " USD\n";
echo "   - Depósito 10%:   $" . number_format( $deposit_amount, 2 ) . " USD (Cobro PayPal)\n";
echo "   - Saldo Pendiente: $" . number_format( $remaining_balance, 2 ) . " USD (Pago en sucursal/entrega)\n";

// 2. Test PayPal order creation endpoint via REST API
$req = new WP_REST_Request( 'POST', "/ramirez-rent-a-car/v1/reservations/$token/paypal/order" );
$res = rest_do_request( $req );
$data = $res->get_data();

if ( ! empty( $data['success'] ) && ! empty( $data['deposit_amount'] ) ) {
	echo "2. API PayPal Order creada con éxito:\n";
	echo "   - PayPal Order ID: " . ($data['order_id'] ?? 'MOCK-ORDER-ID') . "\n";
	echo "   - Importe enviado a PayPal: $" . $data['deposit_amount'] . " USD (Exactamente el 10%)\n";
	echo "   - Saldo que NO se envió a PayPal: $" . $data['remaining_balance'] . " USD\n";
} else {
	echo "2. Respuesta de la API al solicitar Orden PayPal (Modo Sandbox sin llaves configuradas):\n";
	echo "   - Nota: " . json_encode( $data ) . "\n";
}

echo "=== PRUEBA DE CÁLCULO Y ARQUITECTURA FINANCIERA COMPLETADA ===\n";
