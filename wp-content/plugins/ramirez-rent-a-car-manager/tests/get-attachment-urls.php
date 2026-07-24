<?php
require 'd:/XAMPP/htdocs/ramirezrentacar/wp-load.php';

$attachments = get_posts(array(
	'post_type' => 'attachment',
	'posts_per_page' => -1
));

foreach ($attachments as $attachment) {
	echo "ID: {$attachment->ID} | Title: {$attachment->post_title} | URL: " . wp_get_attachment_url($attachment->ID) . "\n";
}
