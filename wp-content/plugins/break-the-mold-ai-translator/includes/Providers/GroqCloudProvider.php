<?php
/**
 * GroqCloud Provider — sends translation requests to Groq API.
 *
 * API key resolution (§3 security rules):
 *   1. getenv('BTMAT_GROQ_API_KEY')
 *   2. getenv('GROQ_API_KEY')
 *   3. constant BTMAT_GROQ_API_KEY
 *   4. provider disabled
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Providers;

use BreakTheMold\AITranslator\Core\Plugin;
use BreakTheMold\AITranslator\AI\PromptBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class GroqCloudProvider implements TranslationProviderInterface {

	private string $base_url;
	private string $model;
	private int    $timeout;
	private int    $max_tokens;
	private float  $temperature;
	private array  $last_usage = [];

	public function __construct() {
		$this->base_url    = 'https://api.groq.com/openai/v1/chat/completions';
		$this->model       = get_option( 'btmat_groq_model', 'llama-3.3-70b-versatile' );
		$this->timeout     = (int) get_option( 'btmat_groq_timeout', 30 );
		$this->max_tokens  = (int) get_option( 'btmat_groq_max_tokens', 4096 );
		$this->temperature = (float) get_option( 'btmat_groq_temperature', 0.1 );
	}

	public function getName(): string {
		return 'groqcloud';
	}

	/**
	 * @inheritDoc
	 */
	public function translateBatch( array $segments, string $source_language, string $target_language, array $options = [] ): array {

		$api_key = Plugin::resolve_api_key();
		if ( ! $api_key ) {
			return [ 'error' => 'API key not configured', 'translations' => [] ];
		}

		// Build the prompt.
		$prompt  = PromptBuilder::build_translation_prompt( $segments, $source_language, $target_language, $options );

		$body = [
			'model'       => $this->model,
			'messages'    => $prompt,
			'temperature' => $this->temperature,
			'max_tokens'  => $this->max_tokens,
			'response_format' => [ 'type' => 'json_object' ],
		];

		$start_time = microtime( true );

		$response = wp_remote_post( $this->base_url, [
			'timeout' => $this->timeout,
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			],
			'body' => wp_json_encode( $body ),
		] );

		$latency = (int) ( ( microtime( true ) - $start_time ) * 1000 );

		// Handle HTTP errors.
		if ( is_wp_error( $response ) ) {
			$this->last_usage = [
				'input_tokens'  => 0,
				'output_tokens' => 0,
				'cached_tokens' => 0,
				'latency_ms'    => $latency,
				'success'       => false,
				'error_code'    => $response->get_error_code(),
			];
			return [ 'error' => $response->get_error_message(), 'translations' => [] ];
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$raw_body    = wp_remote_retrieve_body( $response );
		$data        = json_decode( $raw_body, true );

		// Handle API errors (rate limit, auth, etc.).
		if ( $status_code !== 200 ) {
			$error_msg = $data['error']['message'] ?? "HTTP {$status_code}";

			$this->last_usage = [
				'input_tokens'  => 0,
				'output_tokens' => 0,
				'cached_tokens' => 0,
				'latency_ms'    => $latency,
				'success'       => false,
				'error_code'    => (string) $status_code,
			];

			return [ 'error' => $error_msg, 'translations' => [] ];
		}

		// Parse usage metadata.
		$usage = $data['usage'] ?? [];
		$this->last_usage = [
			'input_tokens'  => (int) ( $usage['prompt_tokens'] ?? 0 ),
			'output_tokens' => (int) ( $usage['completion_tokens'] ?? 0 ),
			'cached_tokens' => (int) ( $usage['prompt_tokens_details']['cached_tokens'] ?? 0 ),
			'latency_ms'    => $latency,
			'success'       => true,
			'error_code'    => null,
			'model'         => $data['model'] ?? $this->model,
		];

		// Extract the JSON from the assistant message.
		$content = $data['choices'][0]['message']['content'] ?? '';
		$parsed  = json_decode( $content, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! isset( $parsed['translations'] ) ) {
			return [
				'error'        => 'Invalid JSON response from model',
				'translations' => [],
				'raw'          => $content,
			];
		}

		return [
			'error'        => null,
			'translations' => $parsed['translations'],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function healthCheck(): array {

		$api_key = Plugin::resolve_api_key();
		if ( ! $api_key ) {
			return [ 'available' => false, 'message' => 'API key not configured' ];
		}

		$response = wp_remote_get( 'https://api.groq.com/openai/v1/models', [
			'timeout' => 10,
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
			],
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'available' => false, 'message' => $response->get_error_message() ];
		}

		$code = wp_remote_retrieve_response_code( $response );

		return [
			'available' => $code === 200,
			'message'   => $code === 200 ? 'Connection valid' : "HTTP {$code}",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getUsageMetadata(): array {
		return $this->last_usage;
	}
}
