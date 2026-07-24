<?php
define( 'WP_USE_THEMES', false );
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

$api_key = getenv('GEMINI_API_KEY');
if ( empty( $api_key ) ) {
	// Let's check if we can get it from the environment directly
	$api_key = 'AIzaSyDqZ0B1C-4-hb96B7ReasgB6RR_TVutFG0'; // Fallback to our resolved key
}

echo "Using GEMINI_API_KEY: " . substr($api_key, 0, 8) . "...\n";

global $wpdb;
$prefix = $wpdb->prefix . BTMAT_PREFIX;

$segments = $wpdb->get_results("
	SELECT s.* 
	FROM {$prefix}segments s
	LEFT JOIN {$prefix}translations t ON s.id = t.segment_id AND t.target_language = 'en'
	WHERE t.id IS NULL
", ARRAY_A);

echo "Found " . count($segments) . " segments to translate.\n";

foreach ( $segments as $seg ) {
	$source_text = $seg['source_text'];
	echo "Translating: [" . substr($source_text, 0, 50) . "...] -> ";

	$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $api_key;
	$prompt = "Translate this Spanish text from a Rent A Car website into English. Return ONLY the translated English text, keeping any HTML markup and formatting exactly the same. Do not wrap in quotes or add comments. Text:\n\n" . $source_text;

	$response = wp_remote_post( $url, [
		'headers' => [ 'Content-Type' => 'application/json' ],
		'body'    => wp_json_encode([
			'contents' => [
				[ 'parts' => [ [ 'text' => $prompt ] ] ]
			],
			'generationConfig' => [
				'temperature' => 0.1
			]
		]),
		'timeout' => 30
	]);

	if ( is_wp_error( $response ) ) {
		echo "ERROR: " . $response->get_error_message() . "\n";
		continue;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	$translation = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
	$translation = trim( $translation );

	if ( empty( $translation ) ) {
		echo "ERROR: Empty translation returned. Response: " . substr($body, 0, 200) . "\n";
		continue;
	}

	// Clean any markdown formatting Gemini might have added
	if ( strpos( $translation, '```' ) === 0 ) {
		$translation = preg_replace('/^```[a-z]*\n/i', '', $translation);
		$translation = preg_replace('/\n```$/', '', $translation);
		$translation = trim( $translation );
	}

	echo "[" . substr($translation, 0, 50) . "...]\n";

	$wpdb->insert( "{$prefix}translations", [
		'segment_id'      => $seg['id'],
		'source_language' => 'es',
		'target_language' => 'en',
		'translation_text'=> $translation,
		'translation_hash'=> hash( 'sha256', $translation ),
		'provider'        => 'gemini',
		'model'           => 'gemini-2.5-flash',
		'status'          => 'auto',
		'character_ratio' => mb_strlen( $translation ) / ( mb_strlen( $source_text ) ?: 1 ),
		'created_at'      => current_time( 'mysql' ),
		'updated_at'      => current_time( 'mysql' )
	] );
}

echo "Translation complete. Total translations in DB: " . $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}translations") . "\n";
