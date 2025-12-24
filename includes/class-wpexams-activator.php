<?php
/**
 * Fired during plugin activation
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin activation class
 */
class WPExams_Activator {

	/**
	 * Activation tasks
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Set default options
		self::set_default_options();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Set activation timestamp
		update_option( 'wpexams_activated_time', time() );
		update_option( 'wpexams_version', WPEXAMS_VERSION );
	}

	/**
	 * Set default plugin options
	 *
	 * @since 1.0.0
	 */
	private static function set_default_options() {
		// General settings
		$general_defaults = array(
			'default_question_options' => 4,
			'show_profile_username'    => '1',
			'show_progressbar'         => '1',
			'question_time_seconds'    => 82,
		);

		if ( ! get_option( 'wpexams_general_settings' ) ) {
			add_option( 'wpexams_general_settings', $general_defaults );
		}

		// Color settings
		$color_defaults = array(
			'button_bg_color'      => '#000000',
			'button_text_color'    => '#ffffff',
			'progressbar_bg_color' => '#000000',
			'progressbar_text_color' => '#ffffff',
		);

		if ( ! get_option( 'wpexams_color_settings' ) ) {
			add_option( 'wpexams_color_settings', $color_defaults );
		}
	}
}