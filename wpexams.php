<?php
/**
 * Plugin Name:       WP Exams
 * Plugin URI:        https://wpexams.com/
 * Description:       Create and manage online exams with multiple-choice questions. Perfect for educational websites, training platforms, and certification programs.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            WP Exams Team
 * Author URI:        https://wpexams.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wpexams
 * Domain Path:       /languages
 *
 * @package WPExams
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants
 */
define( 'WPEXAMS_VERSION', '1.0.0' );
define( 'WPEXAMS_PLUGIN_FILE', __FILE__ );
define( 'WPEXAMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPEXAMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPEXAMS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load plugin text domain for translations
 */
function wpexams_load_textdomain() {
	load_plugin_textdomain( 'wpexams', false, dirname( WPEXAMS_PLUGIN_BASENAME ) . '/languages' );
}
add_action( 'plugins_loaded', 'wpexams_load_textdomain' );

/**
 * Activation hook
 */
function wpexams_activate() {
	require_once WPEXAMS_PLUGIN_DIR . 'includes/class-wpexams-activator.php';
	WPExams_Activator::activate();
}
register_activation_hook( __FILE__, 'wpexams_activate' );

/**
 * Deactivation hook
 */
function wpexams_deactivate() {
	require_once WPEXAMS_PLUGIN_DIR . 'includes/class-wpexams-deactivator.php';
	WPExams_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'wpexams_deactivate' );

/**
 * Load ajax handlers earlier
 */
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-navigation.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-save.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-submit.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-expired.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-review.php';
require_once WPEXAMS_PLUGIN_DIR . 'public/ajax/wpexams-exam-reset.php';

/**
 * Load core files
 */
require_once WPEXAMS_PLUGIN_DIR . 'includes/wpexams-functions.php';
require_once WPEXAMS_PLUGIN_DIR . 'includes/wpexams-post-types.php';
require_once WPEXAMS_PLUGIN_DIR . 'includes/wpexams-admin-menu.php';
require_once WPEXAMS_PLUGIN_DIR . 'includes/wpexams-settings.php';
require_once WPEXAMS_PLUGIN_DIR . 'includes/wpexams-enqueue.php';

// Migration system
require_once WPEXAMS_PLUGIN_DIR . 'includes/wpexams-migration.php';

// Load admin-specific files
if ( is_admin() ) {
	require_once WPEXAMS_PLUGIN_DIR . 'admin/wpexams-admin-init.php';
}

// Load frontend-specific files
if ( ! is_admin() ) {
	require_once WPEXAMS_PLUGIN_DIR . 'public/wpexams-public-init.php';
}

/**
 * Initialize the plugin
 */
function wpexams_init() {
	/**
	 * Fires after WP Exams is fully loaded
	 *
	 * @since 1.0.0
	 */
	do_action( 'wpexams_loaded' );
}
add_action( 'plugins_loaded', 'wpexams_init', 20 );