<?php
define( 'WP_USE_THEMES', false );
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

$api_key = getenv('GEMINI_API_KEY');
if ( empty( $api_key ) ) {
	$api_key = 'AIzaSyDqZ0B1C-4-hb96B7ReasgB6RR_TVutFG0';
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $api_key;
$response = wp_remote_get( $url );
$body = wp_remote_retrieve_body( $response );
$data = json_decode($body, true);
foreach ($data['models'] as $m) {
	if (in_array('generateContent', $m['supportedGenerationMethods'])) {
		echo $m['name'] . "\n";
	}
}
