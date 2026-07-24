<?php
/**
 * Script de inyección de conocimientos y FAQ locales (Roatán, políticas de seguro, cruceros)
 */
define( 'WP_USE_THEMES', false );
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Contracts/KnowledgeRepositoryInterface.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Knowledge/KnowledgeBase.php';

$kb = new \RamirezRentACar\AI\Knowledge\KnowledgeBase();

// Inyectar FAQ 1
$kb->add(
	'¿El seguro está incluido en la tarifa de alquiler?',
	'Sí, todas nuestras tarifas estándar para standard drive away ya contemplan el seguro obligatorio básico de colisión y daños a terceros sin cargos ocultos.',
	'faq',
	['seguro', 'cobertura', 'tarifa', 'incluido', 'insurance']
);

// Inyectar FAQ 2
$kb->add(
	'¿Qué métodos de pago son aceptados?',
	'Aceptamos tarjetas de crédito Visa, Mastercard, pagos seguros en línea mediante PayPal y transferencias bancarias locales previamente confirmadas.',
	'faq',
	['pago', 'tarjeta', 'paypal', 'metodos', 'payment']
);

// Inyectar FAQ 3
$kb->add(
	'¿Hacen entregas en el aeropuerto o terminal de ferry?',
	'Sí, realizamos entregas y devoluciones completamente gratuitas en el Aeropuerto de Roatán (RTB) y la Terminal del Ferry (Galaxy Wave). Solo debe notificar su número de vuelo o ferry.',
	'faq',
	['aeropuerto', 'ferry', 'entrega', 'roatan', 'rtb']
);

echo "CONOCIMIENTO_LOCAL_INJECTADO_CON_EXITO\n";
