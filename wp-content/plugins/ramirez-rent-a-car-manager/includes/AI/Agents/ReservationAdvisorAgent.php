<?php
namespace RamirezRentACar\AI\Agents;

use RamirezRentACar\AI\Contracts\AgentInterface;
use RamirezRentACar\AI\Orchestrator\ExecutionContext;

class ReservationAdvisorAgent implements AgentInterface {
	public function getKey(): string {
		return 'reservation_advisor';
	}

	public function getName(): string {
		return 'Asesor de Reservas';
	}

	public function run(ExecutionContext $context): array {
		$payload = $context->payload;

		// Estructura de salida JSON rígida del agente lógico
		return [
			'intent' => 'recommendation',
			'recommended_vehicle_ids' => [1, 2], // Simulado a nivel de agente, la llamada real de recomendación de IA se realiza por el Orquestador
			'reasons' => ['Opción con tracción 4x4 recomendada para la topografía de Roatán'],
			'missing_information' => [],
			'confidence' => 0.95,
			'requires_human' => false
		];
	}
}
