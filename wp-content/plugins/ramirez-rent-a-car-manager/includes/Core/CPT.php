<?php
namespace RamirezRentACar\Core;

class CPT {
	public static function register() {
		// Register CPT
		$labels = [
			'name'               => _x( 'Vehicles', 'post type general name', 'ramirez-rent-a-car' ),
			'singular_name'      => _x( 'Vehicle', 'post type singular name', 'ramirez-rent-a-car' ),
			'menu_name'          => _x( 'Vehicles CPT', 'admin menu', 'ramirez-rent-a-car' ),
			'name_admin_bar'     => _x( 'Vehicle', 'add new on admin bar', 'ramirez-rent-a-car' ),
			'add_new'            => _x( 'Add New', 'vehicle', 'ramirez-rent-a-car' ),
			'add_new_item'       => __( 'Add New Vehicle Model', 'ramirez-rent-a-car' ),
			'new_item'           => __( 'New Vehicle', 'ramirez-rent-a-car' ),
			'edit_item'          => __( 'Edit Vehicle', 'ramirez-rent-a-car' ),
			'view_item'          => __( 'View Vehicle', 'ramirez-rent-a-car' ),
			'all_items'          => __( 'All Vehicles CPT', 'ramirez-rent-a-car' ),
			'search_items'       => __( 'Search Vehicles', 'ramirez-rent-a-car' ),
			'parent_item_colon'  => __( 'Parent Vehicles:', 'ramirez-rent-a-car' ),
			'not_found'          => __( 'No vehicles found.', 'ramirez-rent-a-car' ),
			'not_found_in_trash' => __( 'No vehicles found in Trash.', 'ramirez-rent-a-car' )
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'ramirez-rent-a-car', // Under our main admin page
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'rent-a-car' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'rrc_vehicle', $args );

		// Register categories
		register_taxonomy( 'rrc_vehicle_category', 'rrc_vehicle', [
			'label'        => __( 'Categories', 'ramirez-rent-a-car' ),
			'rewrite'      => [ 'slug' => 'vehicle-category' ],
			'hierarchical' => true,
			'show_in_rest' => true
		] );
	}
}
