<?php
/**
 * Admin initialization
 *
 * Loads all admin-specific files
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load admin components
require_once WPEXAMS_PLUGIN_DIR . 'admin/metaboxes/wpexams-question-metabox.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/metaboxes/wpexams-exam-metabox.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/metaboxes/wpexams-result-metabox.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/columns/wpexams-exam-columns.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/columns/wpexams-exam-filters.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/columns/wpexams-result-columns.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/ajax/wpexams-admin-ajax.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/pages/wpexams-dashboard.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/pages/wpexams-settings-general.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/pages/wpexams-settings-colors.php';
require_once WPEXAMS_PLUGIN_DIR . 'admin/pages/wpexams-settings-about.php';