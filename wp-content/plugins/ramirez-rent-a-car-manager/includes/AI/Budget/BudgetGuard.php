<?php
namespace RamirezRentACar\AI\Budget;

class BudgetGuard {
	public static function checkAllowed(string $agentKey): bool {
		// Leer el presupuesto acumulado del mes
		global $wpdb;
		$usage_table = $wpdb->prefix . 'rrc_ai_usage';
		$startOfMonth = date('Y-m-01 00:00:00');

		// Calcular costo estimado en dólares acumulado del mes
		$estimated_cost = $wpdb->get_var($wpdb->prepare(
			"SELECT SUM(estimated_cost) FROM $usage_table WHERE created_at >= %s",
			$startOfMonth
		));
		$estimated_cost = floatval($estimated_cost);

		$max_budget = floatval(getenv('RRC_AI_MONTHLY_BUDGET_USD') ?: (defined('RRC_AI_MONTHLY_BUDGET_USD') ? RRC_AI_MONTHLY_BUDGET_USD : 5.00));

		// Detener procesamiento de IA no crítica a partir del 75%
		if ( $estimated_cost >= $max_budget ) {
			return false; // IA completamente suspendida
		}

		if ( $estimated_cost >= ($max_budget * 0.75) ) {
			// Desactivar agentes no críticos (ej. marketing, reputación, analytics)
			$non_critical = ['marketing', 'reputation', 'analytics'];
			if ( in_array($agentKey, $non_critical) ) {
				return false;
			}
		}

		return true;
	}

	public static function trackUsage(string $provider, string $model, string $agentKey, int $inputTokens, int $outputTokens, int $cachedTokens) {
		global $wpdb;
		$table = $wpdb->prefix . 'rrc_ai_usage';

		// Tasa de precios estimativa de Groq (Llama 3 70B: ~$0.59 por millón de tokens entrada, ~$0.79 salida)
		$input_rate = 0.59 / 1000000;
		$output_rate = 0.79 / 1000000;
		$cached_rate = 0.10 / 1000000;

		$cost = ($inputTokens * $input_rate) + ($outputTokens * $output_rate) + ($cachedTokens * $cached_rate);

		$wpdb->insert($table, [
			'provider' => $provider,
			'model' => $model,
			'agent_key' => $agentKey,
			'input_tokens' => $inputTokens,
			'output_tokens' => $outputTokens,
			'cached_tokens' => $cachedTokens,
			'estimated_cost' => $cost,
			'latency_ms' => 500, // Latencia simulada estándar
			'created_at' => current_time('mysql')
		]);
	}
}
