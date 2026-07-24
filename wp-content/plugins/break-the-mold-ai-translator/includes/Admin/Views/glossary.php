<?php
/**
 * Glossary View.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$prefix = $wpdb->prefix . BTMAT_PREFIX;
$rows   = $wpdb->get_results( "SELECT * FROM {$prefix}glossary ORDER BY id DESC LIMIT 50", ARRAY_A );

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Protected Glossary Terms', 'break-the-mold-ai-translator' ); ?></h1>
	<p><?php esc_html_e( 'Developed by Break The Mold', 'break-the-mold-ai-translator' ); ?></p>
	<hr />

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Term (ES)', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Protection Mode', 'break-the-mold-ai-translator' ); ?></th>
				<th><?php esc_html_e( 'Whole Word', 'break-the-mold-ai-translator' ); ?></th>
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
						<td><?php echo esc_html( $row['source_term'] ); ?></td>
						<td><?php echo esc_html( $row['protection_mode'] ); ?></td>
						<td><?php echo $row['whole_word'] ? 'Yes' : 'No'; ?></td>
						<td><?php echo esc_html( $row['status'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
