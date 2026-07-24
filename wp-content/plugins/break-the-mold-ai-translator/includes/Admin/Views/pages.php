<?php
/**
 * Pages View.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$prefix = $wpdb->prefix . BTMAT_PREFIX;
$rows   = $wpdb->get_results( "SELECT * FROM {$prefix}pages ORDER BY id DESC LIMIT 50", ARRAY_A );

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Scanned Pages', 'break-the-mold-ai-translator' ); ?></h1>
	<p><?php esc_html_e( 'Developed by Break The Mold', 'break-the-mold-ai-translator' ); ?></p>
	<hr />

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Post ID', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Canonical URL', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Total Segments', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Scan Status', 'break-the-mold-ai-translator' ); ?></th>
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
						<td><?php echo esc_html( $row['post_id'] ); ?></td>
						<td><a href="<?php echo esc_url( $row['canonical_url'] ); ?>" target="_blank"><?php echo esc_html( $row['canonical_url'] ); ?></a></td>
						<td><?php echo esc_html( $row['total_segments'] ); ?></td>
						<td><?php echo esc_html( $row['scan_status'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
