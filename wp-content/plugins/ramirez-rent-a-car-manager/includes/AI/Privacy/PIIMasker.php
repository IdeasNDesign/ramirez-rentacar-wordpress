<?php
namespace RamirezRentACar\AI\Privacy;

class PIIMasker {
	public static function mask(array $payload): array {
		$keysToMask = [
			'passport', 'passport_number', 'passport_code',
			'driver_license', 'license_number', 'license_code',
			'card_number', 'cvv', 'cvc', 'expiration_date',
			'phone', 'phone_number', 'email', 'customer_email',
			'address', 'postal_code', 'national_id', 'ssn'
		];

		foreach ( $payload as $key => &$value ) {
			if ( is_array($value) ) {
				$value = self::mask($value);
			} elseif ( is_string($value) ) {
				if ( in_array(strtolower($key), $keysToMask) ) {
					// Guardar solo los últimos 4 caracteres de datos sensibles para auditoría u operar
					$length = strlen($value);
					if ( $length > 4 ) {
						$value = str_repeat('*', $length - 4) . substr($value, -4);
					} else {
						$value = '****';
					}
				}
			}
		}

		return $payload;
	}
}
