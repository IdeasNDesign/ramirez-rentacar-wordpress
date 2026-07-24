<?php
require 'd:/XAMPP/htdocs/ramirezrentacar/wp-load.php';

$pages = array(
	array(
		'title'   => 'Alquiler de Coches en el Aeropuerto de Roatán',
		'slug'    => 'alquiler-coches-aeropuerto-roatan',
		'content' => '[rrc_landing_airport_section]'
	),
	array(
		'title'   => 'Alquiler de Coches en Mahogany Bay',
		'slug'    => 'alquiler-coches-mahogany-bay',
		'content' => '[rrc_landing_mahogany_section]'
	),
	array(
		'title'   => 'Alquiler de Coches en el Puerto de Coxen Hole',
		'slug'    => 'alquiler-coches-puerto-coxen-hole',
		'content' => '[rrc_landing_coxen_section]'
	),
	array(
		'title'   => 'Alquiler de Coches en la Terminal de Ferry',
		'slug'    => 'alquiler-coches-ferry-roatan',
		'content' => '[rrc_landing_ferry_section]'
	),
	array(
		'title'   => 'Guía Completa de Seguros y Coberturas de Alquiler',
		'slug'    => 'guia-seguros-alquiler',
		'content' => '[rrc_insurance_guide_section]'
	),
	array(
		'title'   => 'Guía de Conducción Segura en Roatán',
		'slug'    => 'guia-conduccion-roatan',
		'content' => '[rrc_driving_guide_section]'
	),
	array(
		'title'   => 'Ruta Perfecta de 1 Día para Cruceristas en Auto',
		'slug'    => 'ruta-cruceristas-roatan',
		'content' => '[rrc_cruise_route_section]'
	),
	array(
		'title'   => 'Las Mejores Playas de Roatán Accesibles en Coche',
		'slug'    => 'playas-roatan-coche',
		'content' => '[rrc_beaches_route_section]'
	),
	array(
		'title'   => 'Centro de Ayuda y Soporte 24/7',
		'slug'    => 'centro-ayuda-emergencias',
		'content' => '[rrc_help_center_section]'
	),
);

foreach ($pages as $p) {
	$page = get_page_by_path($p['slug']);
	
	if (!$page) {
		$new_page = array(
			'post_title'    => $p['title'],
			'post_name'     => $p['slug'],
			'post_content'  => $p['content'],
			'post_status'   => 'publish',
			'post_type'     => 'page',
			'post_author'   => 1,
		);
		$page_id = wp_insert_post($new_page);
		if ($page_id) {
			echo "CREATED: '{$p['title']}' ID: {$page_id} slug: {$p['slug']}\n";
		} else {
			echo "FAILED to create: '{$p['title']}'\n";
		}
	} else {
		if ($page->post_status === 'trash') {
			wp_untrash_post($page->ID);
		}
		wp_update_post(array(
			'ID'           => $page->ID,
			'post_title'   => $p['title'],
			'post_content' => $p['content'],
			'post_status'  => 'publish'
		));
		echo "RESTORED/UPDATED: '{$p['title']}' ID: {$page->ID} slug: {$p['slug']}\n";
	}
}
