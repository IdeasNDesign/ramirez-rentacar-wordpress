<?php
/**
 * Disabled Provider — fallback when no API key is configured.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DisabledProvider implements TranslationProviderInterface {

	public function getName(): string {
		return 'disabled';
	}

	public function translateBatch( array $segments, string $source_language, string $target_language, array $options = [] ): array {
		return [
			'error'        => 'Translation provider is disabled. Configure an API key to enable translations.',
			'translations' => [],
		];
	}

	public function healthCheck(): array {
		return [ 'available' => false, 'message' => 'Provider disabled — no API key configured' ];
	}

	public function getUsageMetadata(): array {
		return [ 'input_tokens' => 0, 'output_tokens' => 0, 'cached_tokens' => 0, 'latency_ms' => 0 ];
	}
}
