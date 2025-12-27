<?php
/**
 * Plugin settings registration
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register plugin settings
 */
function wpexams_register_settings() {
	register_setting(
		'wpexams_general_settings',
		'wpexams_general_settings',
		'wpexams_sanitize_general_settings'
	);

	register_setting(
		'wpexams_color_settings',
		'wpexams_color_settings',
		'wpexams_sanitize_color_settings'
	);
}
add_action( 'admin_init', 'wpexams_register_settings' );

/**
 * Sanitize general settings
 *
 * @since 1.0.0
 * @param array $input Raw input data.
 * @return array Sanitized data.
 */
function wpexams_sanitize_general_settings( $input ) {
	$sanitized = array();

	// Default question options (2, 3, or 4)
	if ( isset( $input['default_question_options'] ) ) {
		$value                                  = absint( $input['default_question_options'] );
		$sanitized['default_question_options'] = in_array( $value, array( 2, 3, 4 ), true ) ? $value : 4;
	}

	// Show profile and username
	$sanitized['show_profile_username'] = isset( $input['show_profile_username'] ) ? '1' : '0';

	// Show progressbar
	$sanitized['show_progressbar'] = isset( $input['show_progressbar'] ) ? '1' : '0';

	// Question time in seconds
	if ( isset( $input['question_time_seconds'] ) ) {
		$value                               = absint( $input['question_time_seconds'] );
		$sanitized['question_time_seconds'] = $value > 0 ? $value : 60;
	}

	/**
	 * Filter sanitized general settings
	 *
	 * @since 1.0.0
	 * @param array $sanitized Sanitized settings.
	 * @param array $input     Raw input.
	 */
	return apply_filters( 'wpexams_sanitized_general_settings', $sanitized, $input );
}

/**
 * Sanitize color settings
 *
 * @since 1.0.0
 * @param array $input Raw input data.
 * @return array Sanitized data.
 */
function wpexams_sanitize_color_settings( $input ) {
	$sanitized = array();

	// Button background color
	$sanitized['button_bg_color'] = isset( $input['button_bg_color'] ) ? sanitize_hex_color( $input['button_bg_color'] ) : '#000000';

	// Button text color
	$sanitized['button_text_color'] = isset( $input['button_text_color'] ) ? sanitize_hex_color( $input['button_text_color'] ) : '#ffffff';

	// Progressbar background color
	$sanitized['progressbar_bg_color'] = isset( $input['progressbar_bg_color'] ) ? sanitize_hex_color( $input['progressbar_bg_color'] ) : '#000000';

	// Progressbar text color
	$sanitized['progressbar_text_color'] = isset( $input['progressbar_text_color'] ) ? sanitize_hex_color( $input['progressbar_text_color'] ) : '#ffffff';

	/**
	 * Filter sanitized color settings
	 *
	 * @since 1.0.0
	 * @param array $sanitized Sanitized settings.
	 * @param array $input     Raw input.
	 */
	return apply_filters( 'wpexams_sanitized_color_settings', $sanitized, $input );
}