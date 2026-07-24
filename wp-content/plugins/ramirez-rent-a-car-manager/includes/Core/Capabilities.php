<?php
namespace RamirezRentACar\Core;

class Capabilities {
	public static function get_roles_and_caps() {
		return [
			'rrc_administrator' => [
				'rrc_manage_system', 'rrc_manage_vehicles', 'rrc_manage_fleet', 'rrc_manage_rates',
				'rrc_view_reservations', 'rrc_manage_reservations', 'rrc_assign_units', 'rrc_view_customers',
				'rrc_view_private_documents', 'rrc_manage_documents', 'rrc_manage_payments', 'rrc_issue_refunds',
				'rrc_manage_locations', 'rrc_manage_policies', 'rrc_view_reports', 'rrc_manage_ai',
				'rrc_view_audit_log', 'rrc_manage_settings'
			],
			'rrc_manager' => [
				'rrc_manage_vehicles', 'rrc_manage_fleet', 'rrc_manage_rates', 'rrc_view_reservations',
				'rrc_manage_reservations', 'rrc_assign_units', 'rrc_view_customers', 'rrc_view_private_documents',
				'rrc_manage_documents', 'rrc_manage_payments', 'rrc_manage_locations', 'rrc_view_reports',
				'rrc_manage_ai', 'rrc_view_audit_log'
			],
			'rrc_reservation_agent' => [
				'rrc_view_reservations', 'rrc_manage_reservations', 'rrc_view_customers',
				'rrc_manage_documents', 'rrc_manage_payments'
			],
			'rrc_fleet_agent' => [
				'rrc_manage_fleet', 'rrc_view_reservations', 'rrc_assign_units'
			],
			'rrc_finance' => [
				'rrc_view_reservations', 'rrc_manage_payments', 'rrc_issue_refunds', 'rrc_view_reports'
			],
			'rrc_viewer' => [
				'rrc_view_reservations', 'rrc_view_customers', 'rrc_view_reports'
			]
		];
	}

	public static function add_caps() {
		$roles_data = self::get_roles_and_caps();
		foreach ( $roles_data as $role_slug => $caps ) {
			$role_name = ucwords( str_replace( '_', ' ', $role_slug ) );
			add_role( $role_slug, $role_name );
			$role = get_role( $role_slug );
			if ( $role ) {
				foreach ( $caps as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}

		// Also add all to administrator role
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( $roles_data['rrc_administrator'] as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}
}
