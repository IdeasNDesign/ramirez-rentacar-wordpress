<?php
/**
 * Author: Break The Mold
 */

namespace BreakTheMold\RamirezPayPal\Database;

class Schema {
	public static function get_tables() {
		global $wpdb;
		$prefix = $wpdb->prefix . 'rrc_';
		$charset_collate = $wpdb->get_charset_collate();

		return [
			'rrc_payments' => "CREATE TABLE `{$prefix}payments` (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				reservation_id bigint(20) unsigned NOT NULL,
				provider varchar(50) NOT NULL,
				environment varchar(20) NOT NULL DEFAULT 'sandbox',
				provider_order_id varchar(100) DEFAULT NULL,
				provider_capture_id varchar(100) DEFAULT NULL,
				provider_transaction_id varchar(100) DEFAULT NULL,
				idempotency_key varchar(100) DEFAULT NULL,
				payment_purpose varchar(50) NOT NULL DEFAULT 'security_deposit',
				currency varchar(10) NOT NULL DEFAULT 'USD',
				amount decimal(10,2) NOT NULL,
				expected_amount decimal(10,2) DEFAULT NULL,
				status varchar(50) NOT NULL DEFAULT 'pending',
				request_snapshot_json text DEFAULT NULL,
				response_snapshot_json text DEFAULT NULL,
				paid_at datetime DEFAULT NULL,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY idempotency_key (idempotency_key)
			) $charset_collate;",

			'rrc_refunds' => "CREATE TABLE `{$prefix}refunds` (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				payment_id bigint(20) unsigned NOT NULL,
				reservation_id bigint(20) unsigned NOT NULL,
				provider_refund_id varchar(100) DEFAULT NULL,
				amount decimal(10,2) NOT NULL,
				currency varchar(10) NOT NULL DEFAULT 'USD',
				reason text DEFAULT NULL,
				policy_result_json text DEFAULT NULL,
				status varchar(50) NOT NULL DEFAULT 'pending',
				requested_by bigint(20) unsigned DEFAULT NULL,
				approved_by bigint(20) unsigned DEFAULT NULL,
				processed_at datetime DEFAULT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;",

			'rrc_webhook_events' => "CREATE TABLE `{$prefix}webhook_events` (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				provider varchar(50) NOT NULL,
				external_event_id varchar(100) NOT NULL,
				event_type varchar(100) NOT NULL,
				signature_verified tinyint(1) DEFAULT 0,
				payload_hash varchar(64) NOT NULL,
				payload_json text DEFAULT NULL,
				processing_status varchar(50) NOT NULL DEFAULT 'pending',
				processed_at datetime DEFAULT NULL,
				error_message text DEFAULT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY unique_event (provider, external_event_id)
			) $charset_collate;"
		];
	}
}
