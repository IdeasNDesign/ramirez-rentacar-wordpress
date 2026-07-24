<?php
/**
 * Job Queue — handles storage, priority, and states of translation jobs.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Queue;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class JobQueue {

	/**
	 * Add a job to the queue.
	 *
	 * @param  string $type
	 * @param  string $src
	 * @param  string $target
	 * @param  string $entity_type
	 * @param  int    $entity_id
	 * @param  array  $payload
	 * @param  int    $priority
	 * @return int|false
	 */
	public static function add_job( string $type, string $src, string $target, string $entity_type, int $entity_id, array $payload, int $priority = 50 ) {
		
		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'jobs';

		$payload_json = wp_json_encode( $payload );
		$payload_hash = hash( 'sha256', $payload_json );

		// Check if equivalent job is already pending
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE payload_hash = %s AND status = 'pending' LIMIT 1",
				$payload_hash
			)
		);

		if ( $existing ) {
			return (int) $existing;
		}

		$wpdb->insert( $table, [
			'job_uuid'         => wp_generate_uuid4(),
			'job_type'         => $type,
			'source_language'  => $src,
			'target_language'  => $target,
			'entity_type'      => $entity_type,
			'entity_id'        => $entity_id,
			'priority'         => $priority,
			'payload_hash'     => $payload_hash,
			'payload_json'     => $payload_json,
			'status'           => 'pending',
			'attempts'         => 0,
			'maximum_attempts' => 3,
			'created_at'       => current_time( 'mysql' ),
			'updated_at'       => current_time( 'mysql' ),
		] );

		return $wpdb->insert_id;
	}

	/**
	 * Retrieve pending jobs, ordered by priority.
	 *
	 * @param  int $limit
	 * @return array
	 */
	public static function get_pending_jobs( int $limit = 5 ): array {
		
		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'jobs';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE status = 'pending' AND (scheduled_at IS NULL OR scheduled_at <= NOW()) ORDER BY priority ASC, id ASC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}
}
