<?php
/**
 * Job Processor — runs the translation queue.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Queue;

use BreakTheMold\AITranslator\Translation\TranslationService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class JobProcessor {

	/**
	 * Run the next pending job.
	 *
	 * @return bool True if a job was processed.
	 */
	public static function run_next(): bool {
		
		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'jobs';

		$jobs = JobQueue::get_pending_jobs( 1 );
		if ( empty( $jobs ) ) {
			return false;
		}

		$job = $jobs[0];

		// Lock the job
		$wpdb->update( $table, [
			'status'     => 'processing',
			'started_at' => current_time( 'mysql' ),
		], [ 'id' => $job['id'] ] );

		$payload = json_decode( $job['payload_json'], true );
		$success = false;
		$error   = '';

		try {
			if ( $job['job_type'] === 'translate' ) {
				// Translate batch of segments
				if ( ! empty( $payload['segments'] ) ) {
					$texts = [];
					foreach ( $payload['segments'] as $seg ) {
						$texts[] = [
							'text'    => $seg['text'],
							'context' => $seg['context'] ?? 'generic',
							'post_id' => $job['entity_id'],
						];
					}

					TranslationService::translate_batch( $texts, $job['source_language'], $job['target_language'] );
					$success = true;
				}
			}
		} catch ( \Exception $e ) {
			$error = $e->getMessage();
		}

		if ( $success ) {
			$wpdb->update( $table, [
				'status'       => 'completed',
				'completed_at' => current_time( 'mysql' ),
			], [ 'id' => $job['id'] ] );
		} else {
			$attempts = $job['attempts'] + 1;
			$status   = $attempts >= $job['maximum_attempts'] ? 'dead_letter' : 'pending';

			$wpdb->update( $table, [
				'status'                  => $status,
				'attempts'                => $attempts,
				'error_code'              => 'PROCESS_ERR',
				'error_message_sanitized' => substr( $error ?: 'Unknown error', 0, 500 ),
			], [ 'id' => $job['id'] ] );
		}

		return true;
	}
}
