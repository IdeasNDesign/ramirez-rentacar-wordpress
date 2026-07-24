<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Security;

class IdempotencyService {
	public function lock( $key, $ttl = 30 ) {
		$lock_key = 'rrc_lock_' . md5( $key );
		if ( get_transient( $lock_key ) ) {
			return false; // Already locked
		}
		set_transient( $lock_key, 'locked', $ttl );
		return true;
	}

	public function unlock( $key ) {
		$lock_key = 'rrc_lock_' . md5( $key );
		delete_transient( $lock_key );
	}
}
