<?php
namespace RamirezRentACar\Frontend;

class BookingRouter {
	public static function init() {
		// Intercept single template for CPT rrc_vehicle to output a custom high-end layout
		add_filter( 'single_template', [ __CLASS__, 'load_vehicle_single_layout' ] );
	}

	public static function load_vehicle_single_layout( $single_template ) {
		global $post;

		if ( $post && $post->post_type === 'rrc_vehicle' ) {
			// Serve our custom luxury product sheet template
			return RRC_PATH . 'templates/booking/single-vehicle.php';
		}

		return $single_template;
	}
}
