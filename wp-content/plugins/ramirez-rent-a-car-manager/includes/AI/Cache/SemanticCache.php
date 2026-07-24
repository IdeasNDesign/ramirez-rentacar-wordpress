<?php
namespace RamirezRentACar\AI\Cache;

class SemanticCache {
	public static function search(string $agentKey, string $query): ?array {
		// Normalización de texto local, coincidencia por palabras clave y distancia textual Levenshtein
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_cache';
		$normalized = strtolower(trim($query));

		$cached = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $table WHERE agent_key = %s AND expires_at > %s",
			$agentKey,
			current_time('mysql')
		));

		if ( ! empty($cached) ) {
			foreach ( $cached as $item ) {
				$saved = strtolower(trim($item->normalized_query));
				$dist = levenshtein($normalized, $saved);
				$maxLength = max(strlen($normalized), strlen($saved));
				
				// Si la similitud textual es mayor al 92% (distancia muy baja), reutilizar la respuesta semánticamente
				if ( $maxLength > 0 && ($dist / $maxLength) < 0.08 ) {
					$wpdb->update(
						$table,
						['hit_count' => intval($item->hit_count) + 1, 'last_used_at' => current_time('mysql')],
						['id' => $item->id]
					);
					return json_decode($item->response_json, true);
				}
			}
		}

		return null;
	}
}
