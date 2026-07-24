<?php
namespace RamirezRentACar\AI\Orchestrator;

class AgentRegistry {
	private static $agents = [];

	public static function register(string $key, string $className) {
		self::$agents[$key] = $className;
	}

	public static function get(string $key) {
		if ( isset(self::$agents[$key]) ) {
			$class = self::$agents[$key];
			return new $class();
		}
		return null;
	}

	public static function list() {
		return self::$agents;
	}
}
