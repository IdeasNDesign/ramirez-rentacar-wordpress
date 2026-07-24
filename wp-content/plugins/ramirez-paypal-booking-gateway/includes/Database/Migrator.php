<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Database;

class Migrator {
	public static function run() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$tables = Schema::get_tables();
		foreach ( $tables as $table_name => $sql ) {
			dbDelta( $sql );
		}
	}
}
