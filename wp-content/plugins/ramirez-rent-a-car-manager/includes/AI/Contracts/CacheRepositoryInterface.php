<?php
namespace RamirezRentACar\AI\Contracts;

interface CacheRepositoryInterface {
	public function get(string $agentKey, string $normalizedQuery, string $promptVersion);
	public function set(string $agentKey, string $normalizedQuery, string $promptVersion, array $response, float $confidence, int $expirationSeconds = 86400);
}
