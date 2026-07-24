<?php
/**
 * Jobs View.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$prefix = $wpdb->prefix . BTMAT_PREFIX;
$rows   = $wpdb->get_results( "SELECT * FROM {$prefix}jobs ORDER BY id DESC LIMIT 50", ARRAY_A );

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Background Jobs Queue', 'break-the-mold-ai-translator' ); ?></h1>
	<p><?php esc_html_e( 'Developed by Break The Mold', 'break-the-mold-ai-translator' ); ?></p>
	<hr />

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Job ID', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Type', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Priority', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Status', 'break-the-mold-ai-translator' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr>
					<td colspan="4"><?php esc_html_e( 'No records found.', 'break-the-mold-ai-translator' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['id'] ); ?></td>
						<td><?php echo esc_html( $row['job_type'] ); ?></td>
						<td><?php echo esc_html( $row['priority'] ); ?></td>
						<td><?php echo esc_html( $row['status'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
