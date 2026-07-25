<?php
namespace BreakTheMold\RamirezAIAssistant\AI;

/**
 * Prompt Builder and AI service connector for Groq Cloud.
 * Author: Break The Mold
 */
class PromptBuilder {
	public static function build_system_prompt(): string {
		$name = get_option( 'rrc_ai_assistant_name', 'Sara' );
		
		$prompt  = "You are $name, a helpful and premium virtual assistant for Ramirez Rent A Car in Roatan and Honduras.\n";
		$prompt .= "Your goal is to answer questions about vehicles, locations, rates, and help the user choose the perfect vehicle.\n";
		$prompt .= "Guidelines:\n";
		$prompt .= "- Answer in the same language the customer uses (usually Spanish or English).\n";
		$prompt .= "- Keep your responses concise, friendly, and extremely professional.\n";
		$prompt .= "- Always offer a logical next step (e.g., asking for flight details, rental dates, or preference of automatic vs manual).\n";
		$prompt .= "- Do not use passive or open ending questions like 'Let me know if you need anything else'.\n";

		return $prompt;
	}

	public static function call_ai( string $system_prompt, string $user_message, array $history = [] ): string {
		// 1. Try to get Groq API Key from WordPress constants or options
		$groq_key = defined( 'GROQ_API_KEY' ) ? GROQ_API_KEY : get_option( 'rrc_groq_api_key' );

		if ( empty( $groq_key ) ) {
			// Fallback to Gemini if no Groq key is configured
			return self::call_gemini_fallback( $system_prompt, $user_message, $history );
		}

		$url = "https://api.groq.com/openai/v1/chat/completions";
		
		$messages = [];
		$messages[] = [
			'role'    => 'system',
			'content' => $system_prompt
		];

		// Add history
		foreach ( $history as $msg ) {
			$messages[] = [
				'role'    => ($msg['role'] === 'assistant') ? 'assistant' : 'user',
				'content' => $msg['content']
			];
		}
		
		// Add current message
		$messages[] = [
			'role'    => 'user',
			'content' => $user_message
		];

		$response = wp_remote_post( $url, [
			'headers' => [ 
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $groq_key
			],
			'body'    => wp_json_encode([
				'messages'    => $messages,
				'model'       => 'llama-3.3-70b-specdec', // Fast versatile model on Groq
				'temperature' => 0.2
			]),
			'timeout' => 30
		]);

		if ( is_wp_error( $response ) ) {
			return "Lo siento, estoy teniendo un inconveniente técnico temporal para conectarse: " . esc_html( $response->get_error_message() );
		}

		$body  = wp_remote_retrieve_body( $response );
		$data  = json_decode( $body, true );
		
		// Return API error details directly in chat for diagnostic
		if ( isset( $data['error']['message'] ) ) {
			return "Error de la API de Groq: " . esc_html( $data['error']['message'] ) . " (Tipo: " . esc_html( $data['error']['type'] ?? 'N/A' ) . ")";
		}
		
		$reply = $data['choices'][0]['message']['content'] ?? '';
		
		if ( empty( $reply ) ) {
			return "Respuesta de Groq vacía. Código de respuesta: " . wp_remote_retrieve_response_code( $response ) . ". Datos: " . esc_html( substr( $body, 0, 300 ) );
		}
		
		return trim( $reply );
	}

	private static function call_gemini_fallback( string $system_prompt, string $user_message, array $history = [] ): string {
		$api_key = getenv('GEMINI_API_KEY');
		if ( empty( $api_key ) ) {
			$api_key = 'AIzaSyDqZ0B1C-4-hb96B7ReasgB6RR_TVutFG0'; // Local active key
		}

		$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $api_key;
		
		$contents = [];
		// Add history
		foreach ( $history as $msg ) {
			$contents[] = [
				'role' => ($msg['role'] === 'assistant') ? 'model' : 'user',
				'parts' => [ [ 'text' => $msg['content'] ] ]
			];
		}
		
		// Add current message
		$contents[] = [
			'role' => 'user',
			'parts' => [ [ 'text' => "System Context:\n" . $system_prompt . "\n\nUser Message: " . $user_message ] ]
		];

		$response = wp_remote_post( $url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode([
				'contents' => $contents,
				'generationConfig' => [
					'temperature' => 0.2
				]
			]),
			'timeout' => 30
		]);

		if ( is_wp_error( $response ) ) {
			return "Lo siento, estoy teniendo un inconveniente técnico temporal para conectarme. ¿Podrías intentar de nuevo?";
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
		
		return trim( $reply ) ?: "Lo siento, no pude procesar tu solicitud en este momento.";
	}
}
