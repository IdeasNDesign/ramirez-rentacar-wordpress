<?php
namespace RamirezRentACar\AI\Contracts;

interface MemoryRepositoryInterface {
	public function get(string $scope, int $scopeId, string $key);
	public function set(string $scope, int $scopeId, string $key, $value, int $expirationSeconds = 0);
	public function delete(string $scope, int $scopeId, string $key);
}
