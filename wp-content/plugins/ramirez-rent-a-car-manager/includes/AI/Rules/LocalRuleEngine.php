<?php
namespace RamirezRentACar\AI\Rules;

class LocalRuleEngine {
	public static function evaluate(string $eventType, array $payload): array {
		// Nivel 0: Reglas deterministas inmediatas
		// Excluir de IA cotizaciones estándar, tarifas, disponibilidad y cancelaciones estándar
		if ( $eventType === 'get_rates' || $eventType === 'get_availability' ) {
			return [
				'success' => true,
				'handled' => true,
				'requires_ai' => false,
				'result' => ['message' => 'Cálculo de tarifas y disponibilidad procesado de forma determinista al 100% sin IA.']
			];
		}

		if ( $eventType === 'payment_received' ) {
			// Conciliación de pagos determinista
			if ( isset($payload['amount_expected'], $payload['amount_paid']) && floatval($payload['amount_expected']) === floatval($payload['amount_paid']) ) {
				return [
					'success' => true,
					'handled' => true,
					'requires_ai' => false,
					'result' => ['payment_status' => 'reconciled', 'notes' => 'Importe exacto coincidente. Conciliación automática por Nivel 0 completada.']
				];
			}
		}

		// Buscar coincidencia en base de datos local para reglas aprendidas
		global $wpdb;
		$rules_table = $wpdb->prefix . 'rrc_ai_learned_rules';
		$learned_rules = $wpdb->get_results($wpdb->prepare("SELECT * FROM $rules_table WHERE status = 'approved' AND agent_key = %s", $eventType));

		if ( ! empty($learned_rules) ) {
			foreach ( $learned_rules as $rule ) {
				$cond = json_decode($rule->condition_json, true);
				$match = true;
				if ( ! empty($cond) ) {
					foreach ( $cond as $k => $v ) {
						if ( ! isset($payload[$k]) || strpos(strtolower($payload[$k]), strtolower($v)) === false ) {
							$match = false;
							break;
						}
					}
				}
				if ( $match ) {
					return [
						'success' => true,
						'handled' => true,
						'requires_ai' => false,
						'result' => json_decode($rule->action_json, true)
					];
				}
			}
		}

		return [
			'success' => true,
			'handled' => false,
			'requires_ai' => true
		];
	}
}
