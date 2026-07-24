<?php
namespace RamirezRentACar\Core;

class Deactivator {
	public static function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
