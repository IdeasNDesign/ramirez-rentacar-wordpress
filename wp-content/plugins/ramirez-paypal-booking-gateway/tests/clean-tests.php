<?php
/**
 * Author: Break The Mold
 * Database Cleanup Script for Ramirez Rent A Car Test Reservations
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';

if ( ! is_user_logged_in() && PHP_SAPI !== 'cli' ) {
	die( 'Unauthorized Access.' );
}

global $wpdb;

$res_table  = $wpdb->prefix . 'rrc_reservations';
$cust_table = $wpdb->prefix . 'rrc_customers';

// Find test customers
$test_customers = $wpdb->get_results(
	"SELECT id, first_name, last_name, email FROM $cust_table 
	 WHERE first_name LIKE '%Prueba%' 
	    OR last_name LIKE '%Prueba%' 
	    OR email = 'doshamkt@gmail.com'"
);

if ( empty( $test_customers ) ) {
	echo "No test customers found.\n";
	exit;
}

echo "Found " . count( $test_customers ) . " test customers. Cleaning up...\n";

foreach ( $test_customers as $customer ) {
	// Find and delete reservations for this customer
	$reservations = $wpdb->get_results( $wpdb->prepare( "SELECT id, public_reference FROM $res_table WHERE customer_id = %d", $customer->id ) );
	foreach ( $reservations as $res ) {
		// Delete from audit log
		$wpdb->delete( $wpdb->prefix . 'rrc_audit_log', [ 'entity_id' => $res->id, 'entity_type' => 'reservation' ] );
		// Delete reservation
		$wpdb->delete( $res_table, [ 'id' => $res->id ] );
		echo "Deleted Reservation #{$res->public_reference} (ID: {$res->id})\n";
	}
	
	// Delete customer
	$wpdb->delete( $cust_table, [ 'id' => $customer->id ] );
	echo "Deleted Customer: {$customer->first_name} {$customer->last_name} ({$customer->email})\n";
}

echo "Cleanup completed successfully!\n";
