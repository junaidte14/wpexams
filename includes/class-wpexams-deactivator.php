<?php
/**
 * Fired during plugin deactivation
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin deactivation class
 */
class WPExams_Deactivator {

	/**
	 * Deactivation tasks
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();

		/**
		 * Fires after plugin deactivation
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpexams_deactivated' );
	}
}