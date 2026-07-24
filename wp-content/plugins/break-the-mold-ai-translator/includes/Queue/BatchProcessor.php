<?php
/**
 * Batch Processor — handles dividing translation lists into queue jobs.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Queue;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BatchProcessor {

	/**
	 * Split segments and queue them as jobs.
	 *
	 * @param  array  $segments        Array of segment data arrays.
	 * @param  string $source_language
	 * @param  string $target_language
	 * @param  string $entity_type
	 * @param  int    $entity_id
	 * @return int  Number of jobs added.
	 */
	public static function queue_segments( array $segments, string $source_language, string $target_language, string $entity_type, int $entity_id ): int {
		
		$max_segments = (int) get_option( 'btmat_batch_max_segments', 15 );
		$chunks       = array_chunk( $segments, $max_segments );
		$jobs_created = 0;

		foreach ( $chunks as $chunk ) {
			$payload = [
				'segments' => array_map( function( $seg ) {
					return [
						'id'      => $seg['id'] ?? '',
						'text'    => $seg['text'],
						'context' => $seg['context'] ?? 'generic',
					];
				}, $chunk ),
			];

			$job_id = JobQueue::add_job(
				'translate',
				$source_language,
				$target_language,
				$entity_type,
				$entity_id,
				$payload,
				50
			);

			if ( $job_id ) {
				$jobs_created++;
			}
		}

		return $jobs_created;
	}
}
