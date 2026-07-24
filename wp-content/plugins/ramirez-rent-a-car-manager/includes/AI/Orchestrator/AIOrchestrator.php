<?php
namespace RamirezRentACar\AI\Orchestrator;

use RamirezRentACar\AI\AIServiceProvider;
use RamirezRentACar\AI\Rules\LocalRuleEngine;
use RamirezRentACar\AI\Cache\ExactResponseCache;
use RamirezRentACar\AI\Cache\SemanticCache;
use RamirezRentACar\AI\Budget\BudgetGuard;
use RamirezRentACar\AI\Privacy\PIIMasker;

class AIOrchestrator {
	public static function handleEvent(string $eventType, array $payload, array $options = []): array {
		// Validar e inicializar identificadores únicos de procesamiento
		$dedupKey = hash('sha256', $eventType . '|' . wp_json_encode($payload));
		
		// 1. Control de duplicados en caliente
		global $wpdb;
		$jobs_table = $wpdb->prefix . 'rrc_ai_jobs';
		$existing_job = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $jobs_table WHERE input_hash = %s AND status = 'completed' LIMIT 1",
			$dedupKey
		));
		if ( $existing_job ) {
			return [
				'success' => true,
				'source' => 'deduplication',
				'data' => json_decode($existing_job->output_json, true)
			];
		}

		// Enmascarar PII antes de cualquier procesamiento local o externo
		$maskedPayload = PIIMasker::mask($payload);

		// 2. Nivel 0: Regla determinista
		$ruleResult = LocalRuleEngine::evaluate($eventType, $maskedPayload);
		if ( $ruleResult['success'] && $ruleResult['handled'] ) {
			return [
				'success' => true,
				'source' => 'deterministic_rules',
				'data' => $ruleResult['result']
			];
		}

		// 3. Nivel 1: Caché exacta por hash
		$exactCache = new ExactResponseCache();
		$cachedExact = $exactCache->get($eventType, wp_json_encode($maskedPayload), 'v1');
		if ( $cachedExact ) {
			return [
				'success' => true,
				'source' => 'exact_cache',
				'data' => $cachedExact
			];
		}

		// 4. Nivel 2: Caché semántica local
		$cachedSemantic = SemanticCache::search($eventType, wp_json_encode($maskedPayload));
		if ( $cachedSemantic ) {
			return [
				'success' => true,
				'source' => 'semantic_cache',
				'data' => $cachedSemantic
			];
		}

		// 5. Verificar presupuesto mensual a través de BudgetGuard
		if ( ! BudgetGuard::checkAllowed($eventType) ) {
			return [
				'success' => false,
				'message' => 'Límite de presupuesto mensual de IA alcanzado. Procesamiento suspendido.'
			];
		}

		// 6. Nivel 3: Llamada pequeña e inteligente al proveedor configurado
		$provider = AIServiceProvider::getProvider();
		$agent = AgentRegistry::get($eventType);
		
		if ( ! $agent ) {
			return [
				'success' => false,
				'message' => 'Agente no registrado para el evento: ' . $eventType
			];
		}

		// Generación de prompt minimizado
		$systemPrompt = "Actúas como el agente de Ramírez Rent A Car. Trabajas únicamente con los datos suministrados. No inventes precios, disponibilidad ni políticas. Devuelve exclusivamente JSON válido.";
		$userPrompt = wp_json_encode($maskedPayload);
		$schema = []; // Se valida en el agente

		$response = $provider->generateStructuredResponse($systemPrompt, $userPrompt, $schema, $options);

		if ( $response['success'] ) {
			// Registrar logs de consumo y presupuesto
			$usage = $response['usage'];
			BudgetGuard::trackUsage(
				'groqcloud',
				'llama-3.1-70b-versatile',
				$eventType,
				$usage['prompt_tokens'],
				$usage['completion_tokens'],
				0
			);

			// Guardar el resultado en caché para futuras consultas
			$exactCache->set($eventType, wp_json_encode($maskedPayload), 'v1', $response['data'], 0.95);

			// Guardar el trabajo completado en la tabla de jobs para deduplicación posterior
			$wpdb->insert($jobs_table, [
				'job_type' => $eventType,
				'entity_type' => 'event',
				'entity_id' => 0,
				'provider' => 'groqcloud',
				'model_identifier' => 'llama-3.1-70b-versatile',
				'input_hash' => $dedupKey,
				'sanitized_input_json' => wp_json_encode($maskedPayload),
				'output_json' => wp_json_encode($response['data']),
				'confidence' => 0.95,
				'status' => 'completed',
				'created_at' => current_time('mysql'),
				'completed_at' => current_time('mysql')
			]);

			return [
				'success' => true,
				'source' => 'groq_ai',
				'data' => $response['data']
			];
		}

		return [
			'success' => false,
			'message' => $response['message']
		];
	}
}
