<?php
/**
 * Usage View.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$prefix = $wpdb->prefix . BTMAT_PREFIX;
$rows   = $wpdb->get_results( "SELECT * FROM {$prefix}usage ORDER BY id DESC LIMIT 50", ARRAY_A );

?>
<div class="wrap">
	<h1><?php esc_html_e( 'API Consumption & Usage', 'break-the-mold-ai-translator' ); ?></h1>
	<p><?php esc_html_e( 'Developed by Break The Mold', 'break-the-mold-ai-translator' ); ?></p>
	<hr />

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Date', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Operation', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Input Tokens', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Output Tokens', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Latency (ms)', 'break-the-mold-ai-translator' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr>
					<td colspan="5"><?php esc_html_e( 'No records found.', 'break-the-mold-ai-translator' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['created_at'] ); ?></td>
						<td><?php echo esc_html( $row['operation'] ); ?></td>
						<td><?php echo esc_html( $row['input_tokens'] ); ?></td>
						<td><?php echo esc_html( $row['output_tokens'] ); ?></td>
						<td><?php echo esc_html( $row['latency_ms'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
