<?php
namespace RamirezRentACar\Core;

class Activator {
	public static function activate() {
		// Run Schema installation
		require_once RRC_PATH . 'includes/Database/Schema.php';
		\RamirezRentACar\Database\Schema::install();

		// Add custom caps
		Capabilities::add_caps();
	}
}
