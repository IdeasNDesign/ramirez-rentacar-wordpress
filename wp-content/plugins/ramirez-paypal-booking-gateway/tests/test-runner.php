<?php
/**
 * Author: Break The Mold
 */

require_once dirname( __FILE__, 5 ) . '/wp-load.php';

use BreakTheMold\RamirezPayPal\Application\DepositCalculator;

echo "=== INICIANDO PRUEBAS UNITARIAS DE DEPÓSITO Y REDONDEO ===\n";

$tests = [
	[ 'total' => 800.00, 'expected_dep' => 80.00, 'expected_rem' => 720.00 ],
	[ 'total' => 1050.00, 'expected_dep' => 105.00, 'expected_rem' => 945.00 ],
	[ 'total' => 55.00, 'expected_dep' => 5.50, 'expected_rem' => 49.50 ],
	[ 'total' => 99.99, 'expected_dep' => 10.00, 'expected_rem' => 89.99 ] // 10% of 99.99 is 9.999 -> round to 10.00
];

$failed = false;

foreach ( $tests as $t ) {
	$res = DepositCalculator::calculate( $t['total'] );
	$dep = $res['deposit_amount'];
	$rem = $res['remaining_balance'];

	printf( "Total: $%s -> Depósito esperado: $%s (Calculado: $%s), Restante esperado: $%s (Calculado: $%s)\n",
		number_format($t['total'], 2),
		number_format($t['expected_dep'], 2),
		number_format($dep, 2),
		number_format($t['expected_rem'], 2),
		number_format($rem, 2)
	);

	if ( abs( $dep - $t['expected_dep'] ) > 0.0001 || abs( $rem - $t['expected_rem'] ) > 0.0001 ) {
		echo "❌ FALLÓ: El cálculo no coincide.\n";
		$failed = true;
	} else {
		echo "✅ PASÓ\n";
	}
}

if ( $failed ) {
	exit(1);
} else {
	echo "=== TODAS LAS PRUEBAS SE COMPLETARON CON ÉXITO ===\n";
	exit(0);
}
