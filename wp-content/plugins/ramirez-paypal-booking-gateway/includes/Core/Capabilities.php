<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Core;

class Capabilities {
	const MANAGE_PAYPAL = 'manage_ramirez_paypal';
	const REFUND_PAYMENT = 'refund_ramirez_payments';

	public static function add_caps() {
		$roles = [ 'administrator', 'rrc_manager', 'rrc_finance' ];
		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				$role->add_cap( self::MANAGE_PAYPAL );
				$role->add_cap( self::REFUND_PAYMENT );
			}
		}
	}
}
