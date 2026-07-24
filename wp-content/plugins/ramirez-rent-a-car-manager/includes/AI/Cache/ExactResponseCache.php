<?php
namespace RamirezRentACar\AI\Cache;

use RamirezRentACar\AI\Contracts\CacheRepositoryInterface;

class ExactResponseCache implements CacheRepositoryInterface {
	public function get(string $agentKey, string $normalizedQuery, string $promptVersion) {
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_cache';
		$inputHash = hash('sha256', $agentKey . '|' . $normalizedQuery . '|' . $promptVersion);

		$cached = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table WHERE input_hash = %s AND expires_at > %s LIMIT 1",
			$inputHash,
			current_time('mysql')
		));

		if ( $cached ) {
			// Aumentar contador de hits de caché
			$wpdb->update(
				$table,
				['hit_count' => intval($cached->hit_count) + 1, 'last_used_at' => current_time('mysql')],
				['id' => $cached->id]
			);
			return json_decode($cached->response_json, true);
		}

		return null;
	}

	public function set(string $agentKey, string $normalizedQuery, string $promptVersion, array $response, float $confidence, int $expirationSeconds = 86400) {
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_cache';
		$inputHash = hash('sha256', $agentKey . '|' . $normalizedQuery . '|' . $promptVersion);
		$cacheKey = 'cache_' . uniqid();

		$wpdb->replace($table, [
			'cache_key' => $cacheKey,
			'cache_type' => 'exact',
			'input_hash' => $inputHash,
			'agent_key' => $agentKey,
			'prompt_version' => $promptVersion,
			'normalized_query' => $normalizedQuery,
			'response_json' => wp_json_encode($response),
			'confidence' => $confidence,
			'hit_count' => 0,
			'expires_at' => date('Y-m-d H:i:s', time() + $expirationSeconds),
			'created_at' => current_time('mysql'),
			'last_used_at' => current_time('mysql')
		]);
	}
}
