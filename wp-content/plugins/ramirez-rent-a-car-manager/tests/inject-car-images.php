<?php
/**
 * Script temporal para inyectar URLs de imágenes de muestra realistas (de Unsplash y fuentes abiertas)
 * basadas en el modelo de coche del catálogo.
 */
define( 'WP_USE_THEMES', false );
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

global $wpdb;
$models_table = $wpdb->prefix . 'rrc_vehicle_models';

$car_images = [
	'sedan-4d'           => 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80', // Sedan
	'atv-standard'       => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=600&q=80', // ATV
	'suv-standard'       => 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=600&q=80', // Standard SUV
	'suv-medium-sorento' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=600&q=80', // KIA Sorento / Medium SUV
	'suv-luxury'         => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=600&q=80', // Luxury SUV (Porsche/Porsche-like)
	'suv-premium-prado'  => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80', // Toyota Prado / Premium SUV
	'jeep-standard'      => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80', // Jeep Standard
	'jeep-gladiator'     => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80', // Jeep Gladiator
	'truck-4x4'          => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80', // 4x4 Truck
	'van-7p'             => 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80', // 7 Pass Van
	'van-15p'            => 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80'  // 15 Pass Van
];

foreach ( $car_images as $code => $url ) {
	$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $models_table WHERE internal_code = %s", $code ) );
	if ( $post_id ) {
		update_post_meta( $post_id, '_rrc_image_url', $url );
		echo "IMAGEN ACTUALIZADA: Código: $code -> URL: $url\n";
	}
}

echo "Proceso de inyección de imágenes de muestra realistas finalizado con éxito!\n";
