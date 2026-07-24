<?php
namespace RamirezRentACar\AI\Providers;

use RamirezRentACar\AI\Contracts\AIProviderInterface;

class FakeAIProvider implements AIProviderInterface {
	public function generateStructuredResponse(string $systemPrompt, string $userPrompt, array $jsonSchema, array $options = []): array {
		// Simular una respuesta estructurada determinista para testeo unitario y local
		return [
			'success' => true,
			'data' => [
				'intent' => 'search_vehicles',
				'recommended_vehicle_ids' => [1, 2],
				'reasons' => ['Precio económico', 'Tracción 4x4 ideal para Roatán'],
				'missing_information' => [],
				'confidence' => 0.95,
				'requires_human' => false,
				'document_type' => 'driving_license',
				'readable' => true,
				'name_match' => 'match'
			],
			'usage' => [
				'prompt_tokens' => 150,
				'completion_tokens' => 80,
				'total_tokens' => 230
			]
		];
	}

	public function classify(string $text, array $categories, array $options = []): string {
		return $categories[0];
	}

	public function summarize(string $text, int $maxChars = 700, array $options = []): string {
		return substr($text, 0, $maxChars);
	}

	public function analyzeImage(string $imagePathOrUrl, string $prompt, array $options = []): array {
		return ['success' => true, 'data' => ['analysis' => 'Simulación de análisis visual de daños.']];
	}

	public function healthCheck(): bool {
		return true;
	}

	public function listModels(): array {
		return [['id' => 'mock-model-70b']];
	}

	public function getUsage(): array {
		return [];
	}

	public function supportsCapability(string $capability): bool {
		return true;
	}
}
