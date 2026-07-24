<?php
/**
 * Length Optimizer — applies style adjustments if translation is too long.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Translation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LengthOptimizer {

	/**
	 * Compute layout class adjustments based on visual width ratio.
	 *
	 * @param  float $ratio Width or character length ratio.
	 * @return string CSS class name to apply.
	 */
	public static function get_fit_class( float $ratio ): string {
		if ( $ratio > 1.25 ) {
			return 'btm-fit-very-tight';
		}
		if ( $ratio > 1.10 ) {
			return 'btm-fit-tight';
		}
		return '';
	}
}
