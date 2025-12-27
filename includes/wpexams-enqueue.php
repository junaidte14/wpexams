<?php
/**
 * Enqueue scripts and styles - UPDATED VERSION
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue frontend styles
 */
function wpexams_enqueue_frontend_styles() {
	wp_enqueue_style(
		'wpexams-frontend',
		WPEXAMS_PLUGIN_URL . 'assets/css/wpexams-frontend.css',
		array(),
		WPEXAMS_VERSION
	);

	// Dynamic styles from settings
	$color_settings = wpexams_get_setting( 'color' );
	$custom_css     = wpexams_generate_custom_css( $color_settings );

	wp_add_inline_style( 'wpexams-frontend', $custom_css );
}
add_action( 'wp_enqueue_scripts', 'wpexams_enqueue_frontend_styles' );

/**
 * Enqueue frontend scripts
 */
function wpexams_enqueue_frontend_scripts() {
	// Main script
	wp_enqueue_script(
		'wpexams-main',
		WPEXAMS_PLUGIN_URL . 'assets/js/wpexams-main.js',
		array( 'jquery' ),
		WPEXAMS_VERSION,
		true
	);

	// Timer script
	wp_enqueue_script(
		'wpexams-timer',
		WPEXAMS_PLUGIN_URL . 'assets/js/wpexams-timer.js',
		array( 'jquery' ),
		WPEXAMS_VERSION,
		true
	);

	// Exam functionality
	wp_enqueue_script(
		'wpexams-exam',
		WPEXAMS_PLUGIN_URL . 'assets/js/wpexams-exam.js',
		array( 'jquery', 'wpexams-main' ),
		WPEXAMS_VERSION,
		true
	);

	// Localize script with all necessary strings
	wp_localize_script(
		'wpexams-exam',
		'wpexamsData',
		array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'wpexams_nonce' ),
			'strings'       => array(
				'confirmExit'       => __( 'Your exam is not finished. Exit now and complete later?', 'wpexams' ),
				'loading'           => __( 'Loading...', 'wpexams' ),
				'error'             => __( 'An error occurred. Please try again.', 'wpexams' ),
				'timeExpired'       => __( 'Time has expired!', 'wpexams' ),
				'confirmReset'      => __( 'Are you sure you want to reset the question bank? This will clear the history of used questions.', 'wpexams' ),
				'next'              => __( 'Next', 'wpexams' ),
				'showResult'        => __( 'Show Result', 'wpexams' ),
				'previous'          => __( 'Previous', 'wpexams' ),
				'examCompleted'     => __( 'Exam Completed!', 'wpexams' ),
				'viewHistory'       => __( 'View History', 'wpexams' ),
				'reviewExam'       => __( 'Review Exam', 'wpexams' ),
				'backToHome'        => __( 'Back to Home', 'wpexams' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'wpexams_enqueue_frontend_scripts' );

/**
 * Enqueue admin styles
 */
function wpexams_enqueue_admin_styles( $hook ) {
	// Only load on plugin pages
	$screen = get_current_screen();

	if ( ! $screen || ( 'wpexams_question' !== $screen->post_type && 'wpexams_exam' !== $screen->post_type && false === strpos( $hook, 'wpexams' ) ) ) {
		return;
	}

	wp_enqueue_style(
		'wpexams-admin',
		WPEXAMS_PLUGIN_URL . 'assets/css/wpexams-admin.css',
		array(),
		WPEXAMS_VERSION
	);

	// Color picker
	wp_enqueue_style( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'wpexams_enqueue_admin_styles' );

/**
 * Enqueue admin scripts
 */
function wpexams_enqueue_admin_scripts( $hook ) {
	// Only load on plugin pages
	$screen = get_current_screen();

	if ( ! $screen || ( 'wpexams_question' !== $screen->post_type && 'wpexams_exam' !== $screen->post_type && false === strpos( $hook, 'wpexams' ) ) ) {
		return;
	}

	// Question management
	if ( 'wpexams_question' === $screen->post_type ) {
		wp_enqueue_script(
			'wpexams-question-admin',
			WPEXAMS_PLUGIN_URL . 'assets/js/admin/wpexams-question-admin.js',
			array( 'jquery' ),
			WPEXAMS_VERSION,
			true
		);
	}

	// Exam management
	if ( 'wpexams_exam' === $screen->post_type ) {
		wp_enqueue_script(
			'wpexams-exam-admin',
			WPEXAMS_PLUGIN_URL . 'assets/js/admin/wpexams-exam-admin.js',
			array( 'jquery' ),
			WPEXAMS_VERSION,
			true
		);

		// Localize for AJAX search
		wp_localize_script(
			'wpexams-exam-admin',
			'wpexamsAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpexams_admin_nonce' ),
			)
		);
	}

	// Color picker
	wp_enqueue_script(
		'wpexams-color-picker',
		WPEXAMS_PLUGIN_URL . 'assets/js/admin/wpexams-color-picker.js',
		array( 'wp-color-picker' ),
		WPEXAMS_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'wpexams_enqueue_admin_scripts' );

/**
 * Generate custom CSS from color settings
 *
 * @since 1.0.0
 * @param array $settings Color settings.
 * @return string CSS code.
 */
function wpexams_generate_custom_css( $settings ) {
	$button_bg       = isset( $settings['button_bg_color'] ) ? $settings['button_bg_color'] : '#000000';
	$button_text     = isset( $settings['button_text_color'] ) ? $settings['button_text_color'] : '#ffffff';
	$progress_bg     = isset( $settings['progressbar_bg_color'] ) ? $settings['progressbar_bg_color'] : '#000000';
	$progress_text   = isset( $settings['progressbar_text_color'] ) ? $settings['progressbar_text_color'] : '#ffffff';

	$css = "
	.wpexams-button,
	.wpexams-exam-button {
		background-color: {$button_bg};
		color: {$button_text};
	}

	.wpexams-progress {
		background-color: {$progress_bg};
	}

	.wpexams-progress-text {
		color: {$progress_text};
	}
	";

	/**
	 * Filter generated CSS
	 *
	 * @since 1.0.0
	 * @param string $css      CSS code.
	 * @param array  $settings Color settings.
	 */
	return apply_filters( 'wpexams_custom_css', $css, $settings );
}