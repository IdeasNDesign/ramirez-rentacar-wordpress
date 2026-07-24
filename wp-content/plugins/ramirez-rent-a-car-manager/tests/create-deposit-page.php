<?php
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-load.php';

$slug = 'deposito-de-reserva-10';
$page = get_page_by_path( $slug );

if ( ! $page ) {
	$id = wp_insert_post( [
		'post_title'   => '¿Por qué solicitamos un depósito del 10%?',
		'post_name'    => $slug,
		'post_content' => '[rrc_deposit_policy_section]',
		'post_status'  => 'publish',
		'post_type'    => 'page'
	] );
	echo "Page created successfully! URL: " . get_permalink( $id );
} else {
	wp_update_post( [
		'ID'           => $page->ID,
		'post_content' => '[rrc_deposit_policy_section]'
	] );
	echo "Page updated successfully! URL: " . get_permalink( $page->ID );
}
