<?php
namespace RamirezRentACar\AI\Contracts;

interface AIProviderInterface {
	public function generateStructuredResponse(string $systemPrompt, string $userPrompt, array $jsonSchema, array $options = []): array;
	public function classify(string $text, array $categories, array $options = []): string;
	public function summarize(string $text, int $maxChars = 700, array $options = []): string;
	public function analyzeImage(string $imagePathOrUrl, string $prompt, array $options = []): array;
	public function healthCheck(): bool;
	public function listModels(): array;
	public function getUsage(): array;
	public function supportsCapability(string $capability): bool;
}
