<?php
require 'd:/XAMPP/htdocs/ramirezrentacar/wp-load.php';
global $wpdb;
$res = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "posts WHERE post_type = 'rrc_vehicle'");
echo "CANTIDAD_POSTS_CPT: " . count($res) . "\n";
foreach($res as $post) {
	echo "POST: ID " . $post->ID . " - Title: " . $post->post_title . " - Status: " . $post->post_status . "\n";
}
