<?php
/**
 * Dashboard View.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$prefix      = $wpdb->prefix . BTMAT_PREFIX;
$total_segs  = $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}segments" );
$trans_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}translations" );
$pages_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}pages" );
$jobs_count  = $wpdb->get_var( "SELECT COUNT(*) FROM {$prefix}jobs" );
$api_status  = \BreakTheMold\AITranslator\Core\Plugin::resolve_api_key() ? 'Configured' : 'Not Configured';

?>
<div class="wrap">
	<h1><?php esc_html_e( 'BTM Translator Dashboard', 'break-the-mold-ai-translator' ); ?></h1>
	<p><?php esc_html_e( 'Developed by Break The Mold', 'break-the-mold-ai-translator' ); ?></p>
	<hr />

	<div class="card" style="max-width: 600px; margin-bottom: 20px;">
		<h2><?php esc_html_e( 'Status & Stats', 'break-the-mold-ai-translator' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'API Key Status:', 'break-the-mold-ai-translator' ); ?></th>
				<td><strong><?php echo esc_html( $api_status ); ?></strong></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Total Pages Scanned:', 'break-the-mold-ai-translator' ); ?></th>
				<td><?php echo esc_html( $pages_count ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Total Segments:', 'break-the-mold-ai-translator' ); ?></th>
				<td><?php echo esc_html( $total_segs ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Translated Segments:', 'break-the-mold-ai-translator' ); ?></th>
				<td><?php echo esc_html( $trans_count ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Jobs in Queue:', 'break-the-mold-ai-translator' ); ?></th>
				<td><?php echo esc_html( $jobs_count ); ?></td>
			</tr>
		</table>
	</div>
</div>
