<?php
namespace BreakTheMold\RamirezAIAssistant\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Controller;
use WP_REST_Server;
use BreakTheMold\RamirezAIAssistant\Chat\SessionManager;
use BreakTheMold\RamirezAIAssistant\AI\PromptBuilder;
use BreakTheMold\RamirezAIAssistant\Booking\BookingAssistant;

class ChatRestController extends WP_REST_Controller {
	protected $namespace = 'ramirez-rent-a-car-ai-assistant/v1';

	public function register_routes() {
		register_rest_route( $this->namespace, '/chat/session', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_session' ],
			'permission_callback' => '__return_true'
		] );

		register_rest_route( $this->namespace, '/chat/message', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'send_message' ],
			'permission_callback' => '__return_true'
		] );
	}

	public function get_session( $request ) {
		$state = SessionManager::get_or_create_state();
		
		// Set initial language if passed
		$params = $request->get_params();
		if ( isset( $params['lang'] ) ) {
			$state->language = sanitize_text_field( $params['lang'] );
		}

		$name = get_option( 'rrc_ai_assistant_name', 'Sara' );

		// Initial greeting
		$greeting = ($state->language === 'en')
			? "Hi! I’m {$name}, your Ramirez Rent A Car assistant. I can help you choose the right vehicle, calculate the complete price and prepare your reservation here. Are you arriving by plane, ferry or cruise ship?"
			: "¡Hola! Soy {$name}, tu asistente de Ramírez Rent A Car. Puedo ayudarte a elegir el vehículo ideal, calcular el precio completo y dejar tu reserva lista desde aquí. ¿Llegas a Roatán por avión, ferry o crucero?";

		if ( empty( $state->history ) ) {
			$state->history[] = [ 'role' => 'assistant', 'content' => $greeting ];
			SessionManager::save_state( $state );
		}

		return rest_ensure_response([
			'success' => true,
			'greeting'=> $greeting,
			'state'   => $state->to_array()
		]);
	}

	public function send_message( $request ) {
		$params = $request->get_params();
		$message = isset( $params['message'] ) ? sanitize_text_field( $params['message'] ) : '';

		if ( empty( $message ) ) {
			return rest_ensure_response([ 'success' => false, 'message' => 'Message is empty.' ]);
		}

		$state = SessionManager::get_or_create_state();

		// Synchronize state language with current UI or detect from message
		if ( isset( $params['lang'] ) ) {
			$state->language = sanitize_text_field( $params['lang'] );
		}
		if ( preg_match( '/\b(hello|hi|hey|rent|car|vehicle|days|people|passenger|pax|flight|airport|ferry|cruise|ship|price|quote|book|reservation)\b/i', $message ) ) {
			$state->language = 'en';
		} elseif ( preg_match( '/\b(hola|rentar|coche|carro|auto|vehiculo|dias|personas|pasajeros|aeropuerto|precio|reserva|cotizar)\b/i', $message ) ) {
			$state->language = 'es';
		}
		
		// Record previous passengers to check for changes
		$prev_passengers = $state->passengers;
		
		$state->history[] = [ 'role' => 'user', 'content' => $message ];

		// Intent & slot extraction locally
		$this->extract_slots_locally( $message, $state );

		// If passenger count changed, invalidate previous recommendations and vehicle selections
		if ( $state->passengers !== $prev_passengers ) {
			$state->selected_vehicle_id = null;
			$state->quote_id = null;
			$state->hold_id = null;
			$state->stage = 'need_gathering';
		}

		// Enforce slots check before transitioning to vehicle_selection
		$has_dates = ! empty( $state->pickup_at ) && ! empty( $state->return_at );
		$has_pax = ! empty( $state->passengers );
		$has_arrival = ! empty( $state->arrival_type );

		if ( $has_dates && $has_pax && $has_arrival ) {
			$state->stage = 'vehicle_selection';
		} else {
			$state->stage = 'need_gathering';
		}

		$eligible_vehicles = [];
		if ( $state->stage === 'vehicle_selection' ) {
			$requirements = [
				'pickup_at'     => $state->pickup_at,
				'return_at'     => $state->return_at,
				'passengers'    => $state->passengers,
				'arrival_type'  => $state->arrival_type
			];
			
			$eligibility_result = BookingAssistant::search_eligible_vehicles( $requirements );
			$eligible_vehicles = $eligibility_result['eligible_vehicles'];

			// Inject eligible vehicles into the state context temporarily for PromptBuilder
			$state->recommended_vehicle_ids = array_column( $eligible_vehicles, 'id' );
		}

		$system_prompt = PromptBuilder::build_system_prompt( $state );
		
		if ( ! empty( $eligible_vehicles ) ) {
			// Add eligible vehicles details directly to the prompt context
			$system_prompt .= "\nEligible vehicles returned by system (ONLY choose from this list):\n";
			foreach ( $eligible_vehicles as $ev ) {
				$system_prompt .= "- ID: {$ev['id']}, Name: {$ev['name']}, Pax Capacity: {$ev['passengers']}\n";
			}
		} else {
			$system_prompt .= "\nNote: Do NOT show or recommend any vehicle cards yet, as we are still gathering travel requirements (dates, luggage, child seats, etc.). Ask the missing questions first.\n";
		}
		
		$reply = PromptBuilder::call_ai( $system_prompt, $message, $state->history );

		// Validate the AI's response if vehicles are being recommended
		if ( ! empty( $eligible_vehicles ) ) {
			$validation = \BreakTheMold\RamirezAIAssistant\Sales\RecommendationResponseValidator::validate( $reply, $eligible_vehicles, $state->to_array() );
			if ( ! $validation['valid'] ) {
				$reply = $validation['fallback_reply'];
			} else {
				$reply = $validation['reply'];
			}
		}

		$state->history[] = [ 'role' => 'assistant', 'content' => $reply ];
		SessionManager::save_state( $state );

		return rest_ensure_response([
			'success'  => true,
			'reply'    => $reply,
			'vehicles' => $eligible_vehicles,
			'state'    => $state->to_array()
		]);
	}

	private function extract_slots_locally( string $message, &$state ) {
		// Detect travel type
		if ( preg_match( '/(avion|vuelo|airport|aeropuerto|rtb)/i', $message ) ) {
			$state->arrival_type = 'flight';
		} elseif ( preg_match( '/(ferry|galaxy|yate|dixon)/i', $message ) ) {
			$state->arrival_type = 'ferry';
		} elseif ( preg_match( '/(crucero|cruise|barco|muelle|mahogany|coxen)/i', $message ) ) {
			$state->arrival_type = 'cruise';
		}

		// Detect passenger count
		if ( preg_match( '/(\d+)\s*(personas|pasajeros|people|pax)/i', $message, $matches ) ) {
			$state->passengers = intval( $matches[1] );
		}

		// Mock dates for demonstration if user specifies days/dates
		if ( preg_match( '/(hoy|mañana|desde|hasta)/i', $message ) ) {
			$state->pickup_at = current_time( 'mysql' );
			$state->return_at = date( 'Y-m-d H:i:s', strtotime( '+3 days' ) );
		}
	}
}
