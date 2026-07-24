<?php
namespace BreakTheMold\RamirezAIAssistant\Chat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SessionManager {
	private static $session_token_cookie = 'rrc_ai_sess_token';

	public static function get_or_create_state(): ConversationState {
		global $wpdb;
		$table_name = $wpdb->prefix . 'rrc_ai_sessions';

		$token = self::get_cookie_token();
		$state = new ConversationState();

		if ( $token ) {
			$row = $wpdb->get_row( $wpdb->prepare(
				"SELECT state_json FROM $table_name WHERE session_token = %s LIMIT 1",
				$token
			), ARRAY_A );

			if ( $row ) {
				$decoded = json_decode( $row['state_json'], true );
				if ( is_array( $decoded ) ) {
					$state->from_array( $decoded );
					return $state;
				}
			}
		}

		// Otherwise create new session
		$token = wp_generate_password( 32, false );
		self::set_cookie_token( $token );

		$wpdb->insert( $table_name, [
			'session_token' => $token,
			'state_json'    => json_encode( $state->to_array() ),
			'created_at'    => current_time( 'mysql' ),
			'updated_at'    => current_time( 'mysql' )
		] );

		return $state;
	}

	public static function save_state( ConversationState $state ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'rrc_ai_sessions';
		$token = self::get_cookie_token();

		if ( ! $token ) {
			return;
		}

		$wpdb->update( $table_name, [
			'state_json' => json_encode( $state->to_array() ),
			'updated_at' => current_time( 'mysql' )
		], [ 'session_token' => $token ] );
	}

	private static function get_cookie_token() {
		return isset( $_COOKIE[ self::$session_token_cookie ] ) ? sanitize_text_field( $_COOKIE[ self::$session_token_cookie ] ) : null;
	}

	private static function set_cookie_token( $token ) {
		setcookie( self::$session_token_cookie, $token, time() + 86400, '/' );
		$_COOKIE[ self::$session_token_cookie ] = $token;
	}
}
