<?php
/**
 * Translation Service — orchestrator for the full translation pipeline.
 *
 * Flow:  normalize → memory lookup → glossary mask → batch to provider → validate → store → unmask
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Translation;

use BreakTheMold\AITranslator\Core\Plugin;
use BreakTheMold\AITranslator\Providers\GroqCloudProvider;
use BreakTheMold\AITranslator\Providers\DisabledProvider;
use BreakTheMold\AITranslator\AI\PromptBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TranslationService {

	/**
	 * Translate a single text segment.
	 *
	 * @param  string      $text            Source text.
	 * @param  string      $source_language Source language.
	 * @param  string      $target_language Target language.
	 * @param  string      $context         Context type.
	 * @param  int|null    $post_id         Post ID if applicable.
	 * @return array       [ 'translation' => string, 'from_memory' => bool, 'segment_id' => int ]
	 */
	public static function translate( string $text, string $source_language, string $target_language, string $context = 'generic', ?int $post_id = null ): array {

		// 1. Should we skip this text?
		if ( TranslationNormalizer::should_exclude( $text ) ) {
			return [ 'translation' => $text, 'from_memory' => false, 'skipped' => true ];
		}

		// 2. Same language → no translation needed.
		if ( $source_language === $target_language ) {
			return [ 'translation' => $text, 'from_memory' => false, 'same_language' => true ];
		}

		// 3. Check memory first.
		$memory_hit = TranslationMemory::find_by_text( $text, $target_language );
		if ( $memory_hit ) {
			return [
				'translation'  => $memory_hit['translation_text'],
				'from_memory'  => true,
				'segment_id'   => (int) $memory_hit['segment_id'],
				'status'       => $memory_hit['status'],
			];
		}

		// 4. Ensure segment exists.
		$segment_id = TranslationMemory::ensure_segment( $text, $source_language, $context, $post_id );

		// 5. Mask glossary terms.
		$masked = GlossaryService::mask_terms( $text );
		$protected_terms = GlossaryService::find_terms_in_text( $text );

		// 6. Get provider.
		if ( \BreakTheMold\AITranslator\Budget\BudgetGuard::is_exceeded() ) {
			return [
				'translation' => $text,
				'from_memory' => false,
				'error'       => 'Budget limit exceeded. Translation paused.',
				'segment_id'  => $segment_id,
			];
		}
		$provider = self::get_provider();

		// 7. Prepare segment for batch.
		$char_count = mb_strlen( $text );
		$tolerance  = self::get_tolerance_for_context( $context );

		$segments = [
			[
				'id'              => (string) $segment_id,
				'text'            => $masked['text'],
				'context'         => $context,
				'min_chars'       => (int) round( $char_count * ( 1 - $tolerance ) ),
				'max_chars'       => (int) round( $char_count * ( 1 + $tolerance ) ),
				'protected_terms' => $protected_terms,
			],
		];

		// 8. Call provider.
		$result = $provider->translateBatch( $segments, $source_language, $target_language );

		// 9. Log usage.
		self::log_usage( $provider, 'translate' );

		// 10. Handle errors.
		if ( ! empty( $result['error'] ) || empty( $result['translations'] ) ) {
			return [
				'translation' => $text,
				'from_memory' => false,
				'error'       => $result['error'] ?? 'No translations returned',
				'segment_id'  => $segment_id,
			];
		}

		// 11. Process the first (only) translation.
		$tr = $result['translations'][0];
		$translated_text = GlossaryService::unmask_terms( $tr['translation'] ?? $text, $masked['map'] );
		$compact         = ! empty( $tr['compact_translation'] )
			? GlossaryService::unmask_terms( $tr['compact_translation'], $masked['map'] )
			: null;

		// 12. Calculate character ratio.
		$ratio = $char_count > 0 ? mb_strlen( $translated_text ) / $char_count : 1.0;

		// 13. Store in memory.
		TranslationMemory::store( $segment_id, $source_language, $target_language, $translated_text, [
			'compact_translation' => $compact,
			'provider'            => $provider->getName(),
			'model'               => $provider->getUsageMetadata()['model'] ?? null,
			'prompt_version'      => PromptBuilder::PROMPT_VERSION,
			'confidence'          => $tr['confidence'] ?? null,
			'character_ratio'     => round( $ratio, 4 ),
			'status'              => ( $tr['requires_review'] ?? false ) ? 'suggested' : 'auto',
		] );

		return [
			'translation' => $translated_text,
			'from_memory' => false,
			'segment_id'  => $segment_id,
			'ratio'       => $ratio,
		];
	}

	/**
	 * Translate multiple texts in a single batch call.
	 *
	 * @param  array  $texts           [ [ 'text' => '...', 'context' => '...', 'post_id' => 123 ], ... ]
	 * @param  string $source_language
	 * @param  string $target_language
	 * @return array  [ index => [ 'translation' => '...', ... ], ... ]
	 */
	public static function translate_batch( array $texts, string $source_language, string $target_language ): array {

		$results        = [];
		$to_translate   = [];
		$segment_map    = [];
		$mask_maps      = [];

		// Phase A: Check memory for each text.
		foreach ( $texts as $i => $item ) {
			$text    = $item['text'];
			$context = $item['context'] ?? 'generic';
			$post_id = $item['post_id'] ?? null;

			if ( TranslationNormalizer::should_exclude( $text ) || $source_language === $target_language ) {
				$results[ $i ] = [ 'translation' => $text, 'from_memory' => false, 'skipped' => true ];
				continue;
			}

			$hit = TranslationMemory::find_by_text( $text, $target_language );
			if ( $hit ) {
				$results[ $i ] = [ 'translation' => $hit['translation_text'], 'from_memory' => true ];
				continue;
			}

			// Needs translation.
			$segment_id = TranslationMemory::ensure_segment( $text, $source_language, $context, $post_id );
			$masked     = GlossaryService::mask_terms( $text );

			$char_count = mb_strlen( $text );
			$tolerance  = self::get_tolerance_for_context( $context );

			$to_translate[] = [
				'id'              => (string) $segment_id,
				'text'            => $masked['text'],
				'context'         => $context,
				'min_chars'       => (int) round( $char_count * ( 1 - $tolerance ) ),
				'max_chars'       => (int) round( $char_count * ( 1 + $tolerance ) ),
				'protected_terms' => GlossaryService::find_terms_in_text( $text ),
			];

			$segment_map[ (string) $segment_id ] = [
				'index'       => $i,
				'original'    => $text,
				'char_count'  => $char_count,
				'context'     => $context,
			];
			$mask_maps[ (string) $segment_id ] = $masked['map'];
		}

		// Phase B: If nothing to translate, return.
		if ( empty( $to_translate ) ) {
			return $results;
		}

		// Phase C: Call provider.
		if ( \BreakTheMold\AITranslator\Budget\BudgetGuard::is_exceeded() ) {
			foreach ( $segment_map as $seg_id => $info ) {
				$results[ $info['index'] ] = [
					'translation' => $info['original'],
					'from_memory' => false,
					'error'       => 'Budget limit exceeded. Translation paused.',
				];
			}
			return $results;
		}
		$provider = self::get_provider();
		$response = $provider->translateBatch( $to_translate, $source_language, $target_language );
		self::log_usage( $provider, 'translate_batch' );

		if ( ! empty( $response['error'] ) || empty( $response['translations'] ) ) {
			// Fill remaining with originals.
			foreach ( $segment_map as $seg_id => $info ) {
				$results[ $info['index'] ] = [
					'translation' => $info['original'],
					'from_memory' => false,
					'error'       => $response['error'] ?? 'No translations',
				];
			}
			return $results;
		}

		// Phase D: Process translations.
		foreach ( $response['translations'] as $tr ) {
			$seg_id = (string) ( $tr['id'] ?? '' );
			if ( ! isset( $segment_map[ $seg_id ] ) ) {
				continue;
			}

			$info  = $segment_map[ $seg_id ];
			$map   = $mask_maps[ $seg_id ] ?? [];
			$translated = GlossaryService::unmask_terms( $tr['translation'] ?? $info['original'], $map );
			$compact    = ! empty( $tr['compact_translation'] )
				? GlossaryService::unmask_terms( $tr['compact_translation'], $map )
				: null;

			$ratio = $info['char_count'] > 0 ? mb_strlen( $translated ) / $info['char_count'] : 1.0;

			TranslationMemory::store( (int) $seg_id, $source_language, $target_language, $translated, [
				'compact_translation' => $compact,
				'provider'            => $provider->getName(),
				'model'               => $provider->getUsageMetadata()['model'] ?? null,
				'prompt_version'      => PromptBuilder::PROMPT_VERSION,
				'confidence'          => $tr['confidence'] ?? null,
				'character_ratio'     => round( $ratio, 4 ),
				'status'              => ( $tr['requires_review'] ?? false ) ? 'suggested' : 'auto',
			] );

			$results[ $info['index'] ] = [
				'translation' => $translated,
				'from_memory' => false,
				'segment_id'  => (int) $seg_id,
				'ratio'       => $ratio,
			];
		}

		// Fill any missing with originals.
		foreach ( $segment_map as $seg_id => $info ) {
			if ( ! isset( $results[ $info['index'] ] ) ) {
				$results[ $info['index'] ] = [
					'translation' => $info['original'],
					'from_memory' => false,
					'error'       => 'Segment not returned by provider',
				];
			}
		}

		ksort( $results );
		return $results;
	}

	/**
	 * Get the appropriate translation provider.
	 *
	 * @return \BreakTheMold\AITranslator\Providers\TranslationProviderInterface
	 */
	private static function get_provider() {
		$key = Plugin::resolve_api_key();
		return $key ? new GroqCloudProvider() : new DisabledProvider();
	}

	/**
	 * Get the length tolerance for a context type.
	 *
	 * @param  string $context
	 * @return float
	 */
	private static function get_tolerance_for_context( string $context ): float {

		return match ( $context ) {
			'button', 'menu', 'label' => (float) get_option( 'btmat_tol_buttons', 0.15 ),
			'heading', 'title'        => (float) get_option( 'btmat_tol_headings', 0.15 ),
			default                   => (float) get_option( 'btmat_tol_paragraphs', 0.30 ),
		};
	}

	/**
	 * Log provider usage to the usage table.
	 *
	 * @param  \BreakTheMold\AITranslator\Providers\TranslationProviderInterface $provider
	 * @param  string $operation
	 * @return void
	 */
	private static function log_usage( $provider, string $operation ): void {

		$meta = $provider->getUsageMetadata();
		if ( empty( $meta ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'usage';

		$wpdb->insert( $table, [
			'provider'      => $provider->getName(),
			'model'         => $meta['model'] ?? null,
			'operation'     => $operation,
			'input_tokens'  => $meta['input_tokens'] ?? 0,
			'output_tokens' => $meta['output_tokens'] ?? 0,
			'cached_tokens' => $meta['cached_tokens'] ?? 0,
			'latency_ms'    => $meta['latency_ms'] ?? 0,
			'success'       => $meta['success'] ?? true ? 1 : 0,
			'error_code'    => $meta['error_code'] ?? null,
		] );
	}
}
