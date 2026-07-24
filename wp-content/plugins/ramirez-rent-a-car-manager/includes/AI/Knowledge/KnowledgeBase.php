<?php
namespace RamirezRentACar\AI\Knowledge;

use RamirezRentACar\AI\Contracts\KnowledgeRepositoryInterface;

class KnowledgeBase implements KnowledgeRepositoryInterface {
	public function query(string $text, string $type = '', array $options = []): array {
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_knowledge';
		$normalized = strtolower(trim($text));

		// Búsqueda LIKE ponderada local sobre la tabla de conocimientos inyectada
		if ( ! empty($type) ) {
			$sql = $wpdb->prepare(
				"SELECT * FROM $table WHERE knowledge_type = %s AND (normalized_content LIKE %s OR title LIKE %s) LIMIT 3",
				$type,
				'%' . $wpdb->esc_like($normalized) . '%',
				'%' . $wpdb->esc_like($normalized) . '%'
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT * FROM $table WHERE (normalized_content LIKE %s OR title LIKE %s) LIMIT 3",
				'%' . $wpdb->esc_like($normalized) . '%',
				'%' . $wpdb->esc_like($normalized) . '%'
			);
		}

		$results = $wpdb->get_results($sql, ARRAY_A);
		return $results ?: [];
	}

	public function add(string $title, string $content, string $type, array $keywords = []) {
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_knowledge';

		$wpdb->insert($table, [
			'knowledge_type' => $type,
			'title' => $title,
			'language' => 'es',
			'content' => $content,
			'normalized_content' => strtolower(trim($content)),
			'keywords_json' => wp_json_encode($keywords),
			'version' => 1,
			'approval_status' => 'approved',
			'created_at' => current_time('mysql'),
			'updated_at' => current_time('mysql')
		]);
	}
}
