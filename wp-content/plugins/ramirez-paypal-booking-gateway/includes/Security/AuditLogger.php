<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Security;

class AuditLogger {
	private $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'rrc_audit_log';
	}

	public function log( $action, $entity_type, $entity_id, $old_values = null, $new_values = null, $correlation_id = null ) {
		global $wpdb;
		
		$actor_id = get_current_user_id();
		$actor_type = $actor_id ? 'user' : 'system';

		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

		$wpdb->insert( $this->table, [
			'actor_user_id'   => $actor_id ? $actor_id : null,
			'actor_type'      => $actor_type,
			'action'          => sanitize_text_field( $action ),
			'entity_type'     => sanitize_text_field( $entity_type ),
			'entity_id'       => intval( $entity_id ),
			'old_values_json' => $old_values ? json_encode( $old_values ) : null,
			'new_values_json' => $new_values ? json_encode( $new_values ) : null,
			'ip_hash'         => $ip ? hash( 'sha256', $ip ) : null,
			'user_agent_hash' => $ua ? hash( 'sha256', $ua ) : null,
			'correlation_id'  => $correlation_id ? sanitize_text_field( $correlation_id ) : null,
			'created_at'      => current_time( 'mysql' )
		] );
	}
}
