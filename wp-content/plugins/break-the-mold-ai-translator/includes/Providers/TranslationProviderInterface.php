<?php
/**
 * Translation Provider Interface.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface TranslationProviderInterface {

	/**
	 * Translate a batch of segments.
	 *
	 * @param  array  $segments        Array of segment arrays with 'id', 'text', 'context', etc.
	 * @param  string $source_language Source language code.
	 * @param  string $target_language Target language code.
	 * @param  array  $options         Additional options (glossary, prompt_version, etc.).
	 * @return array  Array of translation result arrays.
	 */
	public function translateBatch( array $segments, string $source_language, string $target_language, array $options = [] ): array;

	/**
	 * Check if the provider is available and configured.
	 *
	 * @return array { 'available' => bool, 'message' => string }
	 */
	public function healthCheck(): array;

	/**
	 * Get usage metadata from the last request.
	 *
	 * @return array { 'input_tokens' => int, 'output_tokens' => int, 'cached_tokens' => int, 'latency_ms' => int }
	 */
	public function getUsageMetadata(): array;

	/**
	 * Get the provider name.
	 *
	 * @return string
	 */
	public function getName(): string;
}
