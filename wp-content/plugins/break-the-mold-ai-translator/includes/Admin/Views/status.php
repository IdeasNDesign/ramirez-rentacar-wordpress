<?php
/**
 * System Status View.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_version;

// Check API Connection
$provider = new \BreakTheMold\AITranslator\Providers\GroqCloudProvider();
$check    = $provider->healthCheck();
$api_conn = $check['available'] ? 'Connection valid' : 'Connection invalid (' . $check['message'] . ')';

?>
<div class="wrap">
	<h1><?php esc_html_e( 'System Status', 'break-the-mold-ai-translator' ); ?></h1>
	<p><?php esc_html_e( 'Developed by Break The Mold', 'break-the-mold-ai-translator' ); ?></p>
	<hr />

	<div class="card" style="max-width: 600px;">
		<h2><?php esc_html_e( 'Environment Diagnostics', 'break-the-mold-ai-translator' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'WordPress Version:', 'break-the-mold-ai-translator' ); ?></th>
				<td><?php echo esc_html( $wp_version ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'PHP Version:', 'break-the-mold-ai-translator' ); ?></th>
				<td><?php echo esc_html( PHP_VERSION ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Groq Cloud Connection:', 'break-the-mold-ai-translator' ); ?></th>
				<td>
					<span style="color: <?php echo $check['available'] ? 'green' : 'red'; ?>;">
						<strong><?php echo esc_html( $api_conn ); ?></strong>
					</span>
				</td>
			</tr>
		</table>
	</div>
</div>
