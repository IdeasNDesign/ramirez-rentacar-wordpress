<?php
namespace BreakTheMold\RamirezAIAssistant\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIWidget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'ramirez_ai_sales_assistant';
	}

	public function get_title() {
		return 'Ramirez AI Sales Assistant';
	}

	public function get_icon() {
		return 'eicon-chat';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => 'Settings',
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'assistant_name',
			[
				'label' => 'Assistant Name',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'Sara',
			]
		);

		$this->add_control(
			'assistant_color',
			[
				'label' => 'Primary Color',
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#E8272C',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		// Update configuration options based on Elementor controls
		if ( ! empty( $settings['assistant_name'] ) ) {
			update_option( 'rrc_ai_assistant_name', sanitize_text_field( $settings['assistant_name'] ) );
		}
		
		echo do_shortcode( '[ramirez_ai_assistant]' );
	}
}
