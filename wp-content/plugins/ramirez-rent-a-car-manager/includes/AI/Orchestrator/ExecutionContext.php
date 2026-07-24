<?php
namespace RamirezRentACar\AI\Orchestrator;

class ExecutionContext {
	public $eventId;
	public $eventType;
	public $entityType;
	public $entityId;
	public $payload;
	public $priority;
	public $callsMade = 0;
	public $errors = [];
	public $results = [];
	public $requiresHuman = false;

	public function __construct(array $data) {
		$this->eventId    = isset($data['id']) ? $data['id'] : 0;
		$this->eventType  = isset($data['event_type']) ? $data['event_type'] : '';
		$this->entityType = isset($data['entity_type']) ? $data['entity_type'] : '';
		$this->entityId   = isset($data['entity_id']) ? $data['entity_id'] : 0;
		$this->payload    = isset($data['payload_json']) ? json_decode($data['payload_json'], true) : [];
		$this->priority   = isset($data['priority']) ? $data['priority'] : 3;
	}
}
