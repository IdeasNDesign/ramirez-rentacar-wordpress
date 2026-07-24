<?php
namespace RamirezRentACar\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RRC_Catalog_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'rrc_catalog_widget';
	}

	public function get_title() {
		return esc_html__( 'RRC Vehicle Grid', 'ramirez-rent-a-car' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function render() {
		echo \RamirezRentACar\Elementor\Widgets::render_catalog_shortcode();
	}
}
