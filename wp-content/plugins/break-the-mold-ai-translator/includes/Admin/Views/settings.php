<?php
/**
 * Settings View.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Save settings if submitted
if ( isset( $_POST['btmat_save_settings'] ) && check_admin_referer( 'btmat_settings_nonce', 'btmat_nonce' ) ) {
	$options_to_save = [
		'btmat_active'              => isset( $_POST['btmat_active'] ),
		'btmat_base_language'       => isset( $_POST['btmat_base_language'] ) ? sanitize_text_field( $_POST['btmat_base_language'] ) : get_option( 'btmat_base_language', 'es' ),
		'btmat_alt_language'        => isset( $_POST['btmat_alt_language'] ) ? sanitize_text_field( $_POST['btmat_alt_language'] ) : get_option( 'btmat_alt_language', 'en' ),
		'btmat_auto_detect'         => isset( $_POST['btmat_auto_detect'] ),
		'btmat_fallback_language'   => isset( $_POST['btmat_fallback_language'] ) ? sanitize_text_field( $_POST['btmat_fallback_language'] ) : get_option( 'btmat_fallback_language', 'en' ),
		'btmat_cookie_duration'     => isset( $_POST['btmat_cookie_duration'] ) ? intval( $_POST['btmat_cookie_duration'] ) : get_option( 'btmat_cookie_duration', 365 ),
		'btmat_seo_mode'            => isset( $_POST['btmat_seo_mode'] ) ? sanitize_text_field( $_POST['btmat_seo_mode'] ) : get_option( 'btmat_seo_mode', 'simple' ),
		'btmat_groq_model'          => isset( $_POST['btmat_groq_model'] ) ? sanitize_text_field( $_POST['btmat_groq_model'] ) : get_option( 'btmat_groq_model', 'llama-3.3-70b-versatile' ),
		'btmat_groq_timeout'        => isset( $_POST['btmat_groq_timeout'] ) ? intval( $_POST['btmat_groq_timeout'] ) : get_option( 'btmat_groq_timeout', 30 ),
		'btmat_groq_max_tokens'     => isset( $_POST['btmat_groq_max_tokens'] ) ? intval( $_POST['btmat_groq_max_tokens'] ) : get_option( 'btmat_groq_max_tokens', 4096 ),
		'btmat_groq_temperature'    => isset( $_POST['btmat_groq_temperature'] ) ? floatval( $_POST['btmat_groq_temperature'] ) : get_option( 'btmat_groq_temperature', 0.1 ),
		'btmat_batch_max_segments'  => isset( $_POST['btmat_batch_max_segments'] ) ? intval( $_POST['btmat_batch_max_segments'] ) : get_option( 'btmat_batch_max_segments', 15 ),
		'btmat_batch_max_chars'     => isset( $_POST['btmat_batch_max_chars'] ) ? intval( $_POST['btmat_batch_max_chars'] ) : get_option( 'btmat_batch_max_chars', 6000 ),
		'btmat_max_concurrent'      => isset( $_POST['btmat_max_concurrent'] ) ? intval( $_POST['btmat_max_concurrent'] ) : get_option( 'btmat_max_concurrent', 1 ),
		'btmat_max_retries'         => isset( $_POST['btmat_max_retries'] ) ? intval( $_POST['btmat_max_retries'] ) : get_option( 'btmat_max_retries', 2 ),
		'btmat_tol_buttons'         => isset( $_POST['btmat_tol_buttons'] ) ? floatval( $_POST['btmat_tol_buttons'] ) : get_option( 'btmat_tol_buttons', 0.15 ),
		'btmat_tol_headings'        => isset( $_POST['btmat_tol_headings'] ) ? floatval( $_POST['btmat_tol_headings'] ) : get_option( 'btmat_tol_headings', 0.15 ),
		'btmat_tol_paragraphs'      => isset( $_POST['btmat_tol_paragraphs'] ) ? floatval( $_POST['btmat_tol_paragraphs'] ) : get_option( 'btmat_tol_paragraphs', 0.30 ),
		'btmat_min_font_size'       => isset( $_POST['btmat_min_font_size'] ) ? intval( $_POST['btmat_min_font_size'] ) : get_option( 'btmat_min_font_size', 12 ),
		'btmat_max_font_reduction'  => isset( $_POST['btmat_max_font_reduction'] ) ? floatval( $_POST['btmat_max_font_reduction'] ) : get_option( 'btmat_max_font_reduction', 0.08 ),
		'btmat_cache_ttl'           => isset( $_POST['btmat_cache_ttl'] ) ? intval( $_POST['btmat_cache_ttl'] ) : get_option( 'btmat_cache_ttl', 86400 ),
		'btmat_debug'               => isset( $_POST['btmat_debug'] ),
	];

	foreach ( $options_to_save as $key => $val ) {
		update_option( $key, $val );
	}

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configuraciones guardadas.', 'break-the-mold-ai-translator' ) . '</p></div>';
}

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Configuraciones de BTM Translator', 'break-the-mold-ai-translator' ); ?></h1>
	<p><?php esc_html_e( 'Developed by Break The Mold', 'break-the-mold-ai-translator' ); ?></p>
	<hr />

	<form method="post" action="">
		<?php wp_nonce_field( 'btmat_settings_nonce', 'btmat_nonce' ); ?>

		<h2><?php esc_html_e( 'General', 'break-the-mold-ai-translator' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Plugin Activo', 'break-the-mold-ai-translator' ); ?></th>
				<td>
					<input type="checkbox" name="btmat_active" value="1" <?php checked( get_option( 'btmat_active', true ) ); ?> />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Idioma Base', 'break-the-mold-ai-translator' ); ?></th>
				<td>
					<input type="text" name="btmat_base_language" value="<?php echo esc_attr( get_option( 'btmat_base_language', 'es' ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Idioma Alternativo', 'break-the-mold-ai-translator' ); ?></th>
				<td>
					<input type="text" name="btmat_alt_language" value="<?php echo esc_attr( get_option( 'btmat_alt_language', 'en' ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Detección Automática', 'break-the-mold-ai-translator' ); ?></th>
				<td>
					<input type="checkbox" name="btmat_auto_detect" value="1" <?php checked( get_option( 'btmat_auto_detect', true ) ); ?> />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Modo SEO', 'break-the-mold-ai-translator' ); ?></th>
				<td>
					<select name="btmat_seo_mode">
						<option value="simple" <?php selected( get_option( 'btmat_seo_mode', 'simple' ), 'simple' ); ?>>Simple (Cookie/Params)</option>
						<option value="seo" <?php selected( get_option( 'btmat_seo_mode', 'simple' ), 'seo' ); ?>>SEO (/es/, /en/)</option>
					</select>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Groq Cloud', 'break-the-mold-ai-translator' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Modelo Groq', 'break-the-mold-ai-translator' ); ?></th>
				<td>
					<input type="text" name="btmat_groq_model" value="<?php echo esc_attr( get_option( 'btmat_groq_model', 'llama-3.3-70b-versatile' ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Temperatura', 'break-the-mold-ai-translator' ); ?></th>
				<td>
					<input type="number" step="0.1" name="btmat_groq_temperature" value="<?php echo esc_attr( get_option( 'btmat_groq_temperature', 0.1 ) ); ?>" />
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" name="btmat_save_settings" class="button button-primary" value="<?php esc_attr_e( 'Guardar Configuraciones', 'break-the-mold-ai-translator' ); ?>" />
		</p>
	</form>
</div>
