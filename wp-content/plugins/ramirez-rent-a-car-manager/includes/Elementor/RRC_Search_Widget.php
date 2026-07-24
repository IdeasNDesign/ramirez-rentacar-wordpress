<?php
namespace RamirezRentACar\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RRC_Search_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'rrc_search_widget';
	}

	public function get_title() {
		return esc_html__( 'RRC Search Form', 'ramirez-rent-a-car' );
	}

	public function get_icon() {
		return 'eicon-search-results';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function render() {
		echo \RamirezRentACar\Elementor\Widgets::render_search_shortcode();
	}
}
