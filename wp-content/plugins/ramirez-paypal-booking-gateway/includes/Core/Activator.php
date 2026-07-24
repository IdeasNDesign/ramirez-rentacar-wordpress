<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Core;

class Activator {
	public static function activate() {
		// Run Database Schema and check setup
		require_once RRC_PAYPAY_GATEWAY_PATH . 'includes/Database/Schema.php';
		require_once RRC_PAYPAY_GATEWAY_PATH . 'includes/Database/Migrator.php';
		\BreakTheMold\RamirezPayPal\Database\Migrator::run();

		// Add custom caps
		require_once RRC_PAYPAY_GATEWAY_PATH . 'includes/Core/Capabilities.php';
		Capabilities::add_caps();
	}
}
