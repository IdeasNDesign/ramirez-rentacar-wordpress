<?php
/**
 * Test Harness for Ramirez Rent A Car Manager
 */
define( 'ABSPATH', true );
define( 'RRC_PATH', dirname( __DIR__ ) . '/' );

require_once RRC_PATH . 'includes/Autoloader.php';

use RamirezRentACar\Domain\Rates\PackageRateEngine;

// Test 1: Day calculation
$pickup = '2026-07-20 10:00:00';
$return = '2026-07-22 10:00:00'; // Exact 2 days
$days = PackageRateEngine::calculate_days( $pickup, $return );
echo "Test 1 (Exact 2 days): Calculated Days = " . $days . " (Expected: 2)\n";

$return_grace = '2026-07-22 10:45:00'; // 2 days + 45 mins (within grace)
$days_grace = PackageRateEngine::calculate_days( $pickup, $return_grace );
echo "Test 2 (Within grace): Calculated Days = " . $days_grace . " (Expected: 2)\n";

$return_extra = '2026-07-22 11:30:00'; // 2 days + 1.5 hours (extra day)
$days_extra = PackageRateEngine::calculate_days( $pickup, $return_extra );
echo "Test 3 (Over grace): Calculated Days = " . $days_extra . " (Expected: 3)\n";

echo "All tests ran!\n";
