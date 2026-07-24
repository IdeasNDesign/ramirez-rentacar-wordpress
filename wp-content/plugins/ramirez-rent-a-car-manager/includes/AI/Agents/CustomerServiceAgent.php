<?php
namespace RamirezRentACar\AI\Agents;

use RamirezRentACar\AI\Contracts\AgentInterface;
use RamirezRentACar\AI\Orchestrator\ExecutionContext;

class CustomerServiceAgent implements AgentInterface {
	public function getKey(): string {
		return 'customer_service';
	}

	public function getName(): string {
		return 'Atención al Cliente';
	}

	public function run(ExecutionContext $context): array {
		return [
			'response_text' => 'Hola, con gusto le asisto. Para cotizar, indíqueme su fecha de viaje.',
			'current_intent' => 'query_faq',
			'confirmed_facts' => [],
			'pending_questions' => ['fecha_viaje'],
			'requires_human' => false
		];
	}
}
// Registrar los agentes lógicos iniciales en el AgentRegistry para su acceso en el orquestador
\RamirezRentACar\AI\Orchestrator\AgentRegistry::register('reservation_advisor', \RamirezRentACar\AI\Agents\ReservationAdvisorAgent::class);
\RamirezRentACar\AI\Orchestrator\AgentRegistry::register('document', \RamirezRentACar\AI\Agents\DocumentAgent::class);
\RamirezRentACar\AI\Orchestrator\AgentRegistry::register('customer_service', \RamirezRentACar\AI\Agents\CustomerServiceAgent::class);
