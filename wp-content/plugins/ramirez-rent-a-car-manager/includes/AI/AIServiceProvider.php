<?php
namespace RamirezRentACar\AI;

use RamirezRentACar\AI\Providers\GroqCloudProvider;
use RamirezRentACar\AI\Providers\XAIProvider;
use RamirezRentACar\AI\Providers\DisabledAIProvider;
use RamirezRentACar\AI\Providers\FakeAIProvider;

class AIServiceProvider {
	private static $provider = null;

	public static function getProvider() {
		if ( ! is_null( self::$provider ) ) {
			return self::$provider;
		}

		// Leer el proveedor configurado de forma segura
		$provider_key = getenv('RRC_AI_PROVIDER') ?: (defined('RRC_AI_PROVIDER') ? RRC_AI_PROVIDER : 'groqcloud');
		$enabled = getenv('RRC_AI_ENABLED') ?: (defined('RRC_AI_ENABLED') ? RRC_AI_ENABLED : true);

		if ( ! $enabled ) {
			self::$provider = new DisabledAIProvider();
			return self::$provider;
		}

		switch ( $provider_key ) {
			case 'groqcloud':
				self::$provider = new GroqCloudProvider();
				break;
			case 'xai':
				self::$provider = new XAIProvider();
				break;
			case 'fake':
				self::$provider = new FakeAIProvider();
				break;
			default:
				self::$provider = new DisabledAIProvider();
				break;
		}

		return self::$provider;
	}
}
