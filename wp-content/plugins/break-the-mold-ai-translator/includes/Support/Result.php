<?php
/**
 * Result — simple wrapper object for handling success/error status and data.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Result {

	private bool $success;
	private $value;
	private string $error;

	private function __construct( bool $success, $value = null, string $error = '' ) {
		$this->success = $success;
		$this->value   = $value;
		$this->error   = $error;
	}

	public static function ok( $value = null ): self {
		return new self( true, $value );
	}

	public static function fail( string $error ): self {
		return new self( false, null, $error );
	}

	public function is_success(): bool {
		return $this->success;
	}

	public function is_failure(): bool {
		return ! $this->success;
	}

	public function get_value() {
		return $this->value;
	}

	public function get_error(): string {
		return $this->error;
	}
}
