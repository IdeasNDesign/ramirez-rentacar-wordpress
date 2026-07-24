<?php
require 'd:/XAMPP/htdocs/ramirezrentacar/wp-load.php';
global $wpdb;
// List menu items
$res = $wpdb->get_results("SELECT ID, post_title, post_name, post_excerpt FROM {$wpdb->posts} WHERE post_type = 'nav_menu_item'");
foreach($res as $r) {
	$url = get_post_meta($r->ID, '_menu_item_url', true);
	echo "MENU ITEM ID: {$r->ID} | TITLE: {$r->post_title} | SLUG: {$r->post_name} | URL: {$url}\n";
}
