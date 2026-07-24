<?php
namespace RamirezRentACar\AI\Providers;

use RamirezRentACar\AI\Contracts\AIProviderInterface;

class DisabledAIProvider implements AIProviderInterface {
	public function generateStructuredResponse(string $systemPrompt, string $userPrompt, array $jsonSchema, array $options = []): array {
		return ['success' => false, 'message' => 'El asistente de IA se encuentra actualmente desactivado.'];
	}

	public function classify(string $text, array $categories, array $options = []): string {
		return $categories[0];
	}

	public function summarize(string $text, int $maxChars = 700, array $options = []): string {
		return substr($text, 0, $maxChars);
	}

	public function analyzeImage(string $imagePathOrUrl, string $prompt, array $options = []): array {
		return ['success' => false, 'message' => 'Proveedor de IA desactivado.'];
	}

	public function healthCheck(): bool {
		return false;
	}

	public function listModels(): array {
		return [];
	}

	public function getUsage(): array {
		return [];
	}

	public function supportsCapability(string $capability): bool {
		return false;
	}
}
