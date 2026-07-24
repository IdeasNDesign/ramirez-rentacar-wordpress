<?php
namespace RamirezRentACar\AI\Providers;

use RamirezRentACar\AI\Contracts\AIProviderInterface;

class XAIProvider implements AIProviderInterface {
	protected $apiKey;
	protected $baseUrl;
	protected $defaultModel;

	public function __construct() {
		$this->apiKey = getenv('XAI_API_KEY') ?: (defined('RRC_XAI_API_KEY') ? RRC_XAI_API_KEY : '');
		$this->baseUrl = 'https://api.x.ai/v1';
		$this->defaultModel = 'grok-beta';
	}

	public function generateStructuredResponse(string $systemPrompt, string $userPrompt, array $jsonSchema, array $options = []): array {
		if ( empty($this->apiKey) ) {
			return ['success' => false, 'message' => 'xAI API Key no configurada.'];
		}

		$model = isset($options['model']) ? $options['model'] : $this->defaultModel;
		$temperature = isset($options['temperature']) ? $options['temperature'] : 0.1;
		$maxTokens = isset($options['max_tokens']) ? $options['max_tokens'] : 250;

		$payload = [
			'model' => $model,
			'messages' => [
				['role' => 'system', 'content' => $systemPrompt],
				['role' => 'user', 'content' => $userPrompt]
			],
			'temperature' => $temperature,
			'max_tokens' => $maxTokens,
			'response_format' => ['type' => 'json_object']
		];

		$response = wp_remote_post($this->baseUrl . '/chat/completions', [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->apiKey,
				'Content-Type' => 'application/json'
			],
			'body' => wp_json_encode($payload),
			'timeout' => 15
		]);

		if ( is_wp_error($response) ) {
			return ['success' => false, 'message' => 'Error de conexión: ' . $response->get_error_message()];
		}

		$code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if ( $code !== 200 || empty($data['choices'][0]['message']['content']) ) {
			$errorMsg = isset($data['error']['message']) ? $data['error']['message'] : 'Respuesta de xAI inválida';
			return ['success' => false, 'message' => $errorMsg];
		}

		$content = $data['choices'][0]['message']['content'];
		$result = json_decode($content, true);
		$usage = isset($data['usage']) ? $data['usage'] : [];

		return [
			'success' => true,
			'data' => $result,
			'usage' => [
				'prompt_tokens' => isset($usage['prompt_tokens']) ? $usage['prompt_tokens'] : 0,
				'completion_tokens' => isset($usage['completion_tokens']) ? $usage['completion_tokens'] : 0,
				'total_tokens' => isset($usage['total_tokens']) ? $usage['total_tokens'] : 0
			]
		];
	}

	public function classify(string $text, array $categories, array $options = []): string {
		$system = "Clasifica en: " . implode(', ', $categories) . ". Responde únicamente en formato JSON con la propiedad 'category'.";
		$schema = [
			'type' => 'object',
			'properties' => [
				'category' => ['type' => 'string']
			],
			'required' => ['category']
		];
		$res = $this->generateStructuredResponse($system, $text, $schema, $options);
		if ( $res['success'] && isset($res['data']['category']) ) {
			return $res['data']['category'];
		}
		return $categories[0];
	}

	public function summarize(string $text, int $maxChars = 700, array $options = []): string {
		$system = "Resume a máximo {$maxChars} caracteres. Responde únicamente en formato JSON con la propiedad 'summary'.";
		$schema = [
			'type' => 'object',
			'properties' => [
				'summary' => ['type' => 'string']
			],
			'required' => ['summary']
		];
		$res = $this->generateStructuredResponse($system, $text, $schema, $options);
		if ( $res['success'] && isset($res['data']['summary']) ) {
			return $res['data']['summary'];
		}
		return substr($text, 0, $maxChars);
	}

	public function analyzeImage(string $imagePathOrUrl, string $prompt, array $options = []): array {
		return ['success' => false, 'message' => 'Vision capability not implemented.'];
	}

	public function healthCheck(): bool {
		if ( empty($this->apiKey) ) {
			return false;
		}
		$response = wp_remote_get($this->baseUrl . '/models', [
			'headers' => [
				'Authorization' => 'Bearer ' . $this->apiKey
			],
			'timeout' => 5
		]);
		return wp_remote_retrieve_response_code($response) === 200;
	}

	public function listModels(): array {
		return [['id' => 'grok-beta'], ['id' => 'grok-vision-beta']];
	}

	public function getUsage(): array {
		return [];
	}

	public function supportsCapability(string $capability): bool {
		$capabilities = ['text', 'json_schema'];
		return in_array($capability, $capabilities);
	}
}
