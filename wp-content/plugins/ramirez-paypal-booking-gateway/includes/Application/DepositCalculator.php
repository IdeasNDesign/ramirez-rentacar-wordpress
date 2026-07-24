<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Application;

class DepositCalculator {
	/**
	 * Compute the 10% deposit and remaining balance in cents to avoid floats issues.
	 *
	 * @param float $total
	 * @param float $percentage
	 * @return array
	 */
	public static function calculate( $total, $percentage = 10.00 ) {
		$total_minor = round( $total * 100 );
		$deposit_minor = round( $total_minor * ( $percentage / 100 ) );
		$remaining_minor = $total_minor - $deposit_minor;

		return [
			'total_amount'      => $total,
			'deposit_amount'    => $deposit_minor / 100,
			'remaining_balance' => $remaining_minor / 100,
			'total_minor'       => $total_minor,
			'deposit_minor'     => $deposit_minor,
			'remaining_minor'   => $remaining_minor
		];
	}
}
