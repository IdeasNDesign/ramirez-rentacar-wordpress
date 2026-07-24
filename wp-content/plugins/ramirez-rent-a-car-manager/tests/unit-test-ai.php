<?php
/**
 * Script de testeo y validación de la arquitectura de agentes, reglas deterministas y enmascaramiento
 */
define( 'WP_USE_THEMES', false );
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

// Cargar todas las clases necesarias
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Contracts/AIProviderInterface.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Contracts/AgentInterface.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Contracts/MemoryRepositoryInterface.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Contracts/KnowledgeRepositoryInterface.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Contracts/CacheRepositoryInterface.php';

require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Providers/GroqCloudProvider.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Providers/XAIProvider.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Providers/DisabledAIProvider.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Providers/FakeAIProvider.php';

require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/AIServiceProvider.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Orchestrator/ExecutionContext.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Orchestrator/AgentRegistry.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Rules/LocalRuleEngine.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Cache/ExactResponseCache.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Cache/SemanticCache.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Knowledge/KnowledgeBase.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Memory/MemoryManager.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Privacy/PIIMasker.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Budget/BudgetGuard.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Orchestrator/AIOrchestrator.php';

require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Agents/ReservationAdvisorAgent.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Agents/DocumentAgent.php';
require_once 'd:/XAMPP/htdocs/ramirezrentacar/wp-content/plugins/ramirez-rent-a-car-manager/includes/AI/Agents/CustomerServiceAgent.php';

// TEST 1: Verificar enmascaramiento de datos (PII)
$sensitivePayload = [
	'name' => 'John Doe',
	'passport' => 'E12345678',
	'email' => 'john.doe@example.com',
	'driver_license' => 'HND-999202'
];
$masked = \RamirezRentACar\AI\Privacy\PIIMasker::mask($sensitivePayload);
echo "TEST_1_ENMASCARAMIENTO: " . ($masked['passport'] !== 'E12345678' && strpos($masked['passport'], '****') !== false ? 'OK' : 'FAIL') . "\n";

// TEST 2: Reglas deterministas locales sin consumo de tokens
$ratesResult = \RamirezRentACar\AI\Rules\LocalRuleEngine::evaluate('get_rates', []);
echo "TEST_2_REGLA_TARIFAS_DETERMINISTAS: " . ($ratesResult['requires_ai'] === false ? 'OK' : 'FAIL') . "\n";

// TEST 3: Evitar llamadas recurrentes por caché exacta
$cache = new \RamirezRentACar\AI\Cache\ExactResponseCache();
$cache->set('test_agent', 'pregunta_test', 'v1', ['res' => 'valor_cacheado'], 0.95);
$cachedRes = $cache->get('test_agent', 'pregunta_test', 'v1');
echo "TEST_3_CACHE_EXACTA: " . ($cachedRes['res'] === 'valor_cacheado' ? 'OK' : 'FAIL') . "\n";

// TEST 4: Orquestador con proveedor simulado
putenv('RRC_AI_PROVIDER=fake');
putenv('RRC_AI_ENABLED=1');
$orchResult = \RamirezRentACar\AI\Orchestrator\AIOrchestrator::handleEvent('reservation_advisor', ['passenger_count' => 4]);
echo "TEST_4_ORQUESTADOR_FAKE_PROVIDER: " . ($orchResult['success'] && $orchResult['source'] === 'groq_ai' ? 'OK' : 'FAIL') . "\n";

echo "TODOS_LOS_TESTS_FINALIZADOS\n";
