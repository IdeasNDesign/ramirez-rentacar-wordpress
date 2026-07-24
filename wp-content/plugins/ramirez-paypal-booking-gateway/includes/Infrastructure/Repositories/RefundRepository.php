<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Infrastructure\Repositories;

class RefundRepository {
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'rrc_refunds';
	}

	public function create( array $data ) {
		global $wpdb;
		$data['created_at'] = current_time( 'mysql' );
		$inserted = $wpdb->insert( $this->table, $data );
		return $inserted ? $wpdb->insert_id : false;
	}

	public function update( $id, array $data ) {
		global $wpdb;
		return $wpdb->update( $this->table, $data, [ 'id' => $id ] );
	}
}
