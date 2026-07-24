<?php
require 'd:/XAMPP/htdocs/ramirezrentacar/wp-load.php';

$page_title = 'Información útil';
$page_slug = 'informacion-util';
$page_content = '[rrc_useful_info_section]';

// Check if page already exists
$page = get_page_by_path($page_slug);

if (!$page) {
	$new_page = array(
		'post_title'    => $page_title,
		'post_name'     => $page_slug,
		'post_content'  => $page_content,
		'post_status'   => 'publish',
		'post_type'     => 'page',
		'post_author'   => 1,
	);
	
	$page_id = wp_insert_post($new_page);
	if ($page_id) {
		echo "SUCCESS: Created page '{$page_title}' with ID: {$page_id} and slug: {$page_slug}\n";
	} else {
		echo "ERROR: Failed to create page\n";
	}
} else {
	// If it exists in trash, restore it and update it
	if ($page->post_status === 'trash') {
		wp_untrash_post($page->ID);
	}
	// Update content to make sure shortcode is there
	wp_update_post(array(
		'ID'           => $page->ID,
		'post_title'   => $page_title,
		'post_content' => $page_content,
		'post_status'  => 'publish'
	));
	echo "SUCCESS: Page already exists. Restored and updated ID: {$page->ID} and slug: {$page_slug}\n";
}
