<?php
namespace RamirezRentACar\AI\Contracts;

interface AgentInterface {
	public function getKey(): string;
	public function getName(): string;
	public function run(\RamirezRentACar\AI\Orchestrator\ExecutionContext $context): array;
}
