<?php
/**
 * Server Side Translator — provides static helper methods to translate texts.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ServerSideTranslator {

	/**
	 * Translate a text block on the server side using the FrontendController dictionary.
	 *
	 * @param  string $text
	 * @return string
	 */
	public static function translate( string $text ): string {
		return FrontendController::translate_text( $text );
	}

	/**
	 * Translate HTML on the server side using the FrontendController dictionary.
	 *
	 * @param  string $html
	 * @return string
	 */
	public static function translate_html( string $html ): string {
		return FrontendController::translate_html_block( $html );
	}
}
