<?php
define( 'WP_USE_THEMES', false );
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

use BreakTheMold\AITranslator\Discovery\ContentScanner;
use BreakTheMold\AITranslator\Discovery\MenuScanner;
use BreakTheMold\AITranslator\Core\Plugin;
use BreakTheMold\AITranslator\Translation\TranslationService;

echo "Resolved API Key: " . (Plugin::resolve_api_key() ? "FOUND" : "NOT FOUND") . "\n";

// Get all published posts and pages
$posts = get_posts([
	'post_type' => ['post', 'page'],
	'post_status' => 'publish',
	'posts_per_page' => -1
]);

echo "Total posts/pages found: " . count($posts) . "\n";

// Scan each post/page
foreach ($posts as $p) {
	$res = ContentScanner::scan_post($p->ID);
	echo "Scanned ID {$p->ID} ({$p->post_name}): total segments = " . ($res['total'] ?? 0) . ", new = " . ($res['new'] ?? 0) . "\n";
}

// Scan menus
MenuScanner::scan_all_menus();
echo "Menus scanned.\n";

// Check segments in DB
global $wpdb;
$prefix = $wpdb->prefix . BTMAT_PREFIX;
$seg_count = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}segments");
$trans_count = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}translations");
echo "Total segments in DB: $seg_count\n";
echo "Total translations in DB: $trans_count\n";
