<?php
/**
 * Public/Frontend initialization
 *
 * Loads all frontend-specific files
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load public components
require_once WPEXAMS_PLUGIN_DIR . 'public/shortcodes/wpexams-shortcode-exam.php';