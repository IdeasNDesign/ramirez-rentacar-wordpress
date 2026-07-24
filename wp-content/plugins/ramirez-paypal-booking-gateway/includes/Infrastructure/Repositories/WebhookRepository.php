<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Infrastructure\Repositories;

class WebhookRepository {
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'rrc_webhook_events';
	}

	public function get_by_event_id( $event_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE external_event_id = %s", $event_id ) );
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
