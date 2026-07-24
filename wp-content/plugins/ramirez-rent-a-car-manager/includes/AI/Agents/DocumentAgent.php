<?php
namespace RamirezRentACar\AI\Agents;

use RamirezRentACar\AI\Contracts\AgentInterface;
use RamirezRentACar\AI\Orchestrator\ExecutionContext;

class DocumentAgent implements AgentInterface {
	public function getKey(): string {
		return 'document';
	}

	public function getName(): string {
		return 'Gestor de Documentos';
	}

	public function run(ExecutionContext $context): array {
		return [
			'document_type' => 'driving_license',
			'readable' => true,
			'possible_expiration_date' => '2029-12-31',
			'name_match' => 'match',
			'warnings' => [],
			'confidence' => 0.98,
			'requires_human' => false
		];
	}
}
