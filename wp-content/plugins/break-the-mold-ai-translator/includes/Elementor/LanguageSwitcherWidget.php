<?php
/**
 * Elementor Language Switcher Widget — ES | EN toggle.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LanguageSwitcherWidget extends Widget_Base {

	public function get_name(): string {
		return 'btm_language_switcher';
	}

	public function get_title(): string {
		return __( 'BTM Language Switcher', 'break-the-mold-ai-translator' );
	}

	public function get_icon(): string {
		return 'eicon-globe';
	}

	public function get_categories(): array {
		return [ 'break-the-mold', 'general' ];
	}

	public function get_keywords(): array {
		return [ 'language', 'idioma', 'translator', 'switcher', 'es', 'en' ];
	}

	protected function register_controls(): void {

		// ── Content ──────────────────────────────────────────
		$this->start_controls_section( 'section_content', [
			'label' => __( 'Contenido', 'break-the-mold-ai-translator' ),
		] );

		$this->add_control( 'layout', [
			'label'   => __( 'Diseño', 'break-the-mold-ai-translator' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'buttons',
			'options' => [
				'buttons'  => __( 'Botones ES | EN', 'break-the-mold-ai-translator' ),
				'dropdown' => __( 'Desplegable', 'break-the-mold-ai-translator' ),
				'flags'    => __( 'Banderas', 'break-the-mold-ai-translator' ),
				'compact'  => __( 'Compacto', 'break-the-mold-ai-translator' ),
			],
		] );

		$this->add_control( 'show_labels', [
			'label'        => __( 'Mostrar texto', 'break-the-mold-ai-translator' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'label_on'     => __( 'Sí', 'break-the-mold-ai-translator' ),
			'label_off'    => __( 'No', 'break-the-mold-ai-translator' ),
		] );

		$this->add_control( 'show_flags', [
			'label'        => __( 'Mostrar banderas', 'break-the-mold-ai-translator' ),
			'type'         => Controls_Manager::SWITCHER,
			'default'      => 'yes',
		] );

		$this->end_controls_section();

		// ── Style ────────────────────────────────────────────
		$this->start_controls_section( 'section_style', [
			'label' => __( 'Estilo', 'break-the-mold-ai-translator' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'active_color', [
			'label'     => __( 'Color activo', 'break-the-mold-ai-translator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#E8272C',
			'selectors' => [
				'{{WRAPPER}} .btm-lang-btn.active' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'text_color', [
			'label'     => __( 'Color de texto', 'break-the-mold-ai-translator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [
				'{{WRAPPER}} .btm-lang-btn.active' => 'color: {{VALUE}};',
			],
		] );

		$this->add_control( 'inactive_color', [
			'label'     => __( 'Color inactivo', 'break-the-mold-ai-translator' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#f1f5f9',
			'selectors' => [
				'{{WRAPPER}} .btm-lang-btn:not(.active)' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'border_radius', [
			'label'      => __( 'Bordes redondeados', 'break-the-mold-ai-translator' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
			'default'    => [ 'size' => 8, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .btm-lang-switcher' => 'border-radius: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .btm-lang-btn'      => 'border-radius: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		echo self::render_switcher( $settings );
	}

	/**
	 * Static renderer — used by both the widget and the shortcode.
	 *
	 * @param  array $settings  Widget settings or shortcode atts.
	 * @return string
	 */
	public static function render_switcher( array $settings = [] ): string {

		$layout      = $settings['layout'] ?? 'buttons';
		$show_labels = ( $settings['show_labels'] ?? 'yes' ) === 'yes';
		$show_flags  = ( $settings['show_flags'] ?? 'yes' ) === 'yes';

		$current = \BreakTheMold\AITranslator\Language\LanguageResolver::resolve();

		ob_start();
		?>
		<div class="btm-lang-switcher btm-layout-<?php echo esc_attr( $layout ); ?>"
		     role="navigation"
		     aria-label="<?php esc_attr_e( 'Selector de idioma', 'break-the-mold-ai-translator' ); ?>">

			<?php if ( $layout === 'dropdown' ) : ?>
				<select class="btm-lang-select" data-btm-lang-select aria-label="<?php esc_attr_e( 'Idioma', 'break-the-mold-ai-translator' ); ?>">
					<option value="es" <?php selected( $current, 'es' ); ?>>
						<?php echo $show_flags ? '🇪🇸 ' : ''; ?><?php echo $show_labels ? 'Español' : 'ES'; ?>
					</option>
					<option value="en" <?php selected( $current, 'en' ); ?>>
						<?php echo $show_flags ? '🇺🇸 ' : ''; ?><?php echo $show_labels ? 'English' : 'EN'; ?>
					</option>
				</select>

			<?php else : ?>
				<?php foreach ( [ 'es', 'en' ] as $lang ) :
					$is_active = $lang === $current;
					$flag      = $lang === 'es' ? '🇪🇸' : '🇺🇸';
					$label     = $lang === 'es' ? 'Español' : 'English';
				?>
					<button
						type="button"
						class="btm-lang-btn<?php echo $is_active ? ' active' : ''; ?>"
						data-btm-lang="<?php echo esc_attr( $lang ); ?>"
						aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
						aria-label="<?php echo esc_attr( $label ); ?>"
						lang="<?php echo esc_attr( $lang ); ?>"
						hreflang="<?php echo esc_attr( $lang ); ?>">
						<?php if ( $show_flags ) : ?>
							<span class="btm-lang-flag" aria-hidden="true"><?php echo $flag; ?></span>
						<?php endif; ?>
						<?php if ( $show_labels ) : ?>
							<span class="btm-lang-label"><?php echo $layout === 'compact' ? strtoupper( $lang ) : esc_html( $label ); ?></span>
						<?php endif; ?>
						<?php if ( ! $show_labels && ! $show_flags ) : ?>
							<span class="btm-lang-label"><?php echo strtoupper( $lang ); ?></span>
						<?php endif; ?>
					</button>
				<?php endforeach; ?>
			<?php endif; ?>

		</div>
		<?php
		return ob_get_clean();
	}
}
