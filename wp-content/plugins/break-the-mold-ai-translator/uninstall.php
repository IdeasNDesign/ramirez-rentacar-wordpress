<?php
/**
 * Uninstall — clean up everything when the plugin is deleted.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load the constants we need.
define( 'BTMAT_VERSION', '1.0.0' );
define( 'BTMAT_PREFIX', 'btmat_' );

// Drop tables.
require_once __DIR__ . '/includes/Database/Schema.php';
\BreakTheMold\AITranslator\Database\Schema::drop_tables();

// Remove all options.
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'btmat\_%'" );

// Remove capabilities from admin.
$role = get_role( 'administrator' );
if ( $role ) {
	$caps = [
		'btmat_manage_settings',
		'btmat_manage_translations',
		'btmat_approve_translations',
		'btmat_manage_glossary',
		'btmat_manage_jobs',
		'btmat_view_usage',
		'btmat_view_logs',
		'btmat_manage_seo',
		'btmat_run_bulk_translation',
	];
	foreach ( $caps as $cap ) {
		$role->remove_cap( $cap );
	}
}

// Clear transients.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_btmat\_%' OR option_name LIKE '_transient_timeout_btmat\_%'" );
