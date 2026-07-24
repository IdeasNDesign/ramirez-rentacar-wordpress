<?php
/**
 * Prompt Builder — constructs the versionned system/user messages for Groq.
 *
 * Static instructions go first to leverage Groq's prompt cache.
 * Dynamic segment data goes last.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\AI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PromptBuilder {

	public const PROMPT_VERSION = '1.0';

	/**
	 * Build the messages array for the Groq chat completion API.
	 *
	 * @param  array  $segments        Segments to translate.
	 * @param  string $source_language Source language code.
	 * @param  string $target_language Target language code.
	 * @param  array  $options         Additional options.
	 * @return array  Messages array for chat completion.
	 */
	public static function build_translation_prompt( array $segments, string $source_language, string $target_language, array $options = [] ): array {

		$system = self::get_system_prompt();
		$user   = self::build_user_payload( $segments, $source_language, $target_language, $options );

		return [
			[ 'role' => 'system', 'content' => $system ],
			[ 'role' => 'user',   'content' => $user ],
		];
	}

	/**
	 * Get the static system prompt (cached by Groq when unchanged).
	 *
	 * @return string
	 */
	public static function get_system_prompt(): string {

		return <<<'PROMPT'
You are the professional translation engine for Break The Mold AI Translator.

Your task is to translate public website interface and marketing content between Spanish and English.

Follow these rules:

1. Preserve the original meaning, intent, tone and persuasive strength.
2. Do not add facts that are not present.
3. Do not remove important information.
4. Preserve protected terms exactly as provided.
5. Preserve variables, placeholders, numbers, prices, URLs, emails, phone numbers and codes.
6. Adapt grammar naturally to the target language.
7. For buttons, labels, menus and headings, prefer concise language.
8. Produce a compact alternative when the normal translation is likely to exceed the requested visual length.
9. Never include explanations outside the required JSON.
10. Never output HTML unless the input explicitly contains approved inline markup.
11. Do not translate placeholder tokens beginning with __BTM_.
12. Return valid JSON only.
13. Do not expose internal reasoning.
14. If the meaning is ambiguous, mark requires_review as true.
15. Respect the specified target character range whenever possible without damaging meaning.

BUSINESS CONTEXT:
The website belongs to Ramirez Rent A Car, a vehicle rental company serving Roatán and related locations. Preserve brand names and approved location names.

OUTPUT SCHEMA (return this JSON structure only):
{
  "translations": [
    {
      "id": "segment-id",
      "translation": "",
      "compact_translation": "",
      "detected_source_language": "es",
      "character_count": 0,
      "meaning_preserved": true,
      "protected_terms_preserved": true,
      "requires_review": false,
      "confidence": 0.0
    }
  ]
}
PROMPT;
	}

	/**
	 * Build the user message with segment data (dynamic, placed last for cache efficiency).
	 *
	 * @param  array  $segments
	 * @param  string $source_language
	 * @param  string $target_language
	 * @param  array  $options
	 * @return string
	 */
	private static function build_user_payload( array $segments, string $source_language, string $target_language, array $options = [] ): string {

		$payload_segments = [];

		foreach ( $segments as $seg ) {
			$item = [
				'id'      => $seg['id'] ?? '',
				'text'    => $seg['text'] ?? '',
				'context' => $seg['context'] ?? 'generic',
			];

			if ( ! empty( $seg['min_chars'] ) ) {
				$item['minimum_characters'] = (int) $seg['min_chars'];
			}
			if ( ! empty( $seg['max_chars'] ) ) {
				$item['maximum_characters'] = (int) $seg['max_chars'];
			}
			if ( ! empty( $seg['protected_terms'] ) ) {
				$item['protected_terms'] = $seg['protected_terms'];
			}

			$item['tone'] = $seg['tone'] ?? 'professional_persuasive';

			$payload_segments[] = $item;
		}

		$payload = [
			'source_language' => $source_language,
			'target_language' => $target_language,
			'segments'        => $payload_segments,
		];

		return wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	}
}
