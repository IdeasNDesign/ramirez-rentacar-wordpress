<?php
/**
 * Test script to send a test confirmation email to a specific address.
 * Author: Break The Mold
 */

require_once __DIR__ . '/../../../../wp-load.php';

$target_email = 'doshamkt@gmail.com';

global $wpdb;

$models_table = $wpdb->prefix . 'rrc_vehicle_models';
$loc_table    = $wpdb->prefix . 'rrc_locations';
$cust_table   = $wpdb->prefix . 'rrc_customers';
$res_table    = $wpdb->prefix . 'rrc_reservations';

$model_id = $wpdb->get_var( "SELECT id FROM $models_table LIMIT 1" );
$p_loc    = $wpdb->get_var( "SELECT id FROM $loc_table LIMIT 1" );
$r_loc    = $wpdb->get_var( "SELECT id FROM $loc_table LIMIT 1" );

if ( ! $model_id || ! $p_loc ) {
	echo "ERROR: Locations or Vehicle Models not found in DB.\n";
	exit;
}

// 1. Create or get customer
$cust_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $cust_table WHERE email = %s", $target_email ) );
if ( ! $cust_id ) {
	$wpdb->insert( $cust_table, [
		'first_name' => 'Cliente',
		'last_name'  => 'Prueba',
		'email'      => $target_email,
		'phone'      => '+1 800-555-0199',
		'country'    => 'DO',
		'created_at' => current_time( 'mysql' ),
		'updated_at' => current_time( 'mysql' )
	] );
	$cust_id = $wpdb->insert_id;
}

// 2. Insert test reservation
$ref = 'RRC-' . strtoupper( wp_generate_password( 8, false ) );
$lookup_token = wp_generate_password( 32, false );
$lookup_token_hash = hash( 'sha256', $lookup_token );

$pickup_at = date( 'Y-m-d H:i:s', strtotime( '+2 days 10:00' ) );
$return_at = date( 'Y-m-d H:i:s', strtotime( '+5 days 10:00' ) );

$wpdb->insert( $res_table, [
	'public_reference'         => $ref,
	'secure_lookup_token_hash' => $lookup_token_hash,
	'customer_id'              => $cust_id,
	'vehicle_model_id'         => $model_id,
	'assigned_unit_id'         => 1,
	'pickup_location_id'       => $p_loc,
	'return_location_id'       => $r_loc,
	'pickup_at'                => $pickup_at,
	'return_at'                => $return_at,
	'chargeable_days'          => 3,
	'booking_context'          => 'standard',
	'reservation_status'       => 'confirmed',
	'payment_status'           => 'paid',
	'subtotal'                 => 180.00,
	'total_amount'             => 180.00,
	'pricing_snapshot_json'    => json_encode( ['total_amount' => 180.00] ),
	'created_at'               => current_time( 'mysql' ),
	'updated_at'               => current_time( 'mysql' )
] );

$res_id = $wpdb->insert_id;

echo "Created test reservation #$res_id (Ref: $ref) for customer $target_email.\n";

// 3. Send email
$sent = \RamirezRentACar\Infrastructure\Notifications\EmailNotificationService::send_reservation_confirmation( $res_id );

if ( $sent ) {
	echo "SUCCESS: Email enviado exitosamente a $target_email con el nuevo diseño interactivo!\n";
} else {
	echo "NOTICE: wp_mail() fue ejecutado pero retornó false. Verifica que el servicio de correo/SMTP de WordPress esté configurado en este entorno de XAMPP.\n";
}
