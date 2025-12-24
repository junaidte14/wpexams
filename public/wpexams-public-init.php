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
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-save.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-navigation.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-submit.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-expired.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-review.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-reset.php';