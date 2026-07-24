<?php
/**
 * Budget Guard — tracks daily/monthly limits and blocks API requests if exceeded.
 *
 * @package BreakTheMold\AITranslator
 * @author  Break The Mold
 */

namespace BreakTheMold\AITranslator\Budget;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BudgetGuard {

	/**
	 * Check if the API requests / token budget is exceeded.
	 *
	 * @return bool True if budget is exceeded (should block requests).
	 */
	public static function is_exceeded(): bool {
		
		global $wpdb;
		$table = $wpdb->prefix . BTMAT_PREFIX . 'usage';

		// 1. Get configuration limits
		$limit_req_day  = (int) get_option( 'btmat_budget_daily_requests', 500 );
		$limit_tok_day  = (int) get_option( 'btmat_budget_daily_tokens', 500000 );
		$limit_req_month = (int) get_option( 'btmat_budget_monthly_requests', 10000 );
		$limit_tok_month = (int) get_option( 'btmat_budget_monthly_tokens', 5000000 );

		// 2. Query actual usage today
		$today_usage = $wpdb->get_row(
			"SELECT COUNT(*) as reqs, SUM(input_tokens + output_tokens) as tokens 
			 FROM {$table} 
			 WHERE created_at >= DATE(NOW())",
			ARRAY_A
		);

		if ( $today_usage ) {
			if ( $today_usage['reqs'] >= $limit_req_day || $today_usage['tokens'] >= $limit_tok_day ) {
				return true;
			}
		}

		// 3. Query actual usage this month
		$month_usage = $wpdb->get_row(
			"SELECT COUNT(*) as reqs, SUM(input_tokens + output_tokens) as tokens 
			 FROM {$table} 
			 WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')",
			ARRAY_A
		);

		if ( $month_usage ) {
			if ( $month_usage['reqs'] >= $limit_req_month || $month_usage['tokens'] >= $limit_tok_month ) {
				return true;
			}
		}

		return false;
	}
}
