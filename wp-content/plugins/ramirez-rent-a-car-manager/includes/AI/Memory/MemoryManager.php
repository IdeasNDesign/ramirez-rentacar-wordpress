<?php
namespace RamirezRentACar\AI\Memory;

use RamirezRentACar\AI\Contracts\MemoryRepositoryInterface;

class MemoryManager implements MemoryRepositoryInterface {
	public function get(string $scope, int $scopeId, string $key) {
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_memories';

		$row = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table WHERE memory_scope = %s AND scope_id = %d AND memory_key = %s AND (expires_at IS NULL OR expires_at > %s) LIMIT 1",
			$scope,
			$scopeId,
			$key,
			current_time('mysql')
		));

		if ( $row ) {
			return json_decode($row->value_json, true);
		}

		return null;
	}

	public function set(string $scope, int $scopeId, string $key, $value, int $expirationSeconds = 0) {
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_memories';
		$expires = ($expirationSeconds > 0) ? date('Y-m-d H:i:s', time() + $expirationSeconds) : null;

		$wpdb->replace($table, [
			'memory_scope' => $scope,
			'scope_id' => $scopeId,
			'memory_key' => $key,
			'value_json' => wp_json_encode($value),
			'sensitivity' => 'medium',
			'expires_at' => $expires,
			'created_at' => current_time('mysql'),
			'updated_at' => current_time('mysql')
		]);
	}

	public function delete(string $scope, int $scopeId, string $key) {
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_memories';
		$wpdb->delete($table, [
			'memory_scope' => $scope,
			'scope_id' => $scopeId,
			'memory_key' => $key
		]);
	}
}
