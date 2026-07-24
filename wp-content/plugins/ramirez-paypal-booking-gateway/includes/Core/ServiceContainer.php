<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Core;

class ServiceContainer {
	private $services = [];

	public function set( $name, $service ) {
		$this->services[ $name ] = $service;
	}

	public function get( $name ) {
		return $this->services[ $name ] ?? null;
	}
}
