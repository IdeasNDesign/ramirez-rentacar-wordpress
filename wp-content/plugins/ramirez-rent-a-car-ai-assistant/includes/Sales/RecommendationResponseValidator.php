<?php
namespace BreakTheMold\RamirezAIAssistant\Sales;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RecommendationResponseValidator {
	public static function validate( string $reply, array $eligible_vehicles, array $state ): array {
		// Parse vehicle recommendations referenced in the response
		// Extract any numbers or codes that look like vehicle IDs or names
		$referenced_ids = [];
		foreach ( $eligible_vehicles as $v ) {
			// Check if name or category is mentioned in response
			if ( stripos( $reply, $v['name'] ) !== false ) {
				$referenced_ids[] = $v['id'];
			}
		}

		// Also check if any model ID is explicitly mentioned in any structured text/JSON blocks in the reply
		preg_match_all( '/"vehicle_id"\s*:\s*(\d+)/i', $reply, $matches );
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $mid ) {
				$referenced_ids[] = intval( $mid );
			}
		}

		$referenced_ids = array_unique( $referenced_ids );

		// If the AI recommended a vehicle that is not in the eligible list: reject!
		$eligible_ids = array_column( $eligible_vehicles, 'id' );
		
		foreach ( $referenced_ids as $ref_id ) {
			if ( ! in_array( $ref_id, $eligible_ids ) ) {
				error_log( "AI_RECOMMENDATION_REJECTED: Recommended vehicle ID {$ref_id} is not in the eligible list." );
				return [
					'valid' => false,
					'reason' => 'AI_RECOMMENDATION_REJECTED',
					'fallback_reply' => self::build_fallback_reply( $eligible_vehicles, $state )
				];
			}
		}

		return [ 'valid' => true, 'reply' => $reply ];
	}

	private static function build_fallback_reply( array $eligible_vehicles, array $state ): string {
		$lang = $state['language'] ?? 'es';
		
		if ( empty( $eligible_vehicles ) ) {
			return ($lang === 'en')
				? "Based on your requirements, we do not currently have a single vehicle available that meets all criteria. We recommend contacting us or checking different dates."
				: "De acuerdo con tus requisitos, actualmente no disponemos de un único vehículo que cumpla con todos los criterios. Te recomendamos contactarnos o verificar otras fechas.";
		}

		$reply = ($lang === 'en')
			? "Here are the best verified options available for your group of {$state['passengers']} passengers:\n\n"
			: "Aquí tienes las mejores opciones verificadas disponibles para tu grupo de {$state['passengers']} personas:\n\n";

		foreach ( array_slice( $eligible_vehicles, 0, 3 ) as $v ) {
			$reply .= "- **{$v['name']}** (" . (($lang === 'en') ? "Recommended" : "Recomendado") . ")\n";
		}

		$reply .= ($lang === 'en')
			? "\nWhich of these options would you prefer to select?"
			: "\n¿Cuál de estas opciones prefieres seleccionar?";

		return $reply;
	}
}
