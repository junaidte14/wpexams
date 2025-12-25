<?php
/**
 * Main exam shortcode
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register exam shortcode
 *
 * @param array $atts Shortcode attributes.
 * @return string Shortcode output.
 */
function wpexams_exam_shortcode( $atts ) {
	// User must be logged in
	if ( ! is_user_logged_in() ) {
		return wpexams_get_login_message();
	}

	ob_start();

	// Get current user ID
	$current_user_id = get_current_user_id();

	// Get settings
	$general_settings = wpexams_get_setting( 'general' );
	$color_settings   = wpexams_get_setting( 'color' );

	// Pass variables to templates
	$question_time_seconds = isset( $general_settings['question_time_seconds'] ) ? $general_settings['question_time_seconds'] : 82;
	$show_profile          = isset( $general_settings['show_profile_username'] ) ? $general_settings['show_profile_username'] : '1';
	$show_progressbar      = isset( $general_settings['show_progressbar'] ) ? $general_settings['show_progressbar'] : '1';

	// Get user profile picture
	$profile_pic = get_avatar_url( $current_user_id );
	$user_data   = get_userdata( $current_user_id );

	// Display header with profile if enabled
	if ( '1' === $show_profile ) {
		?>
		<div class='wpexams-header'>
			<div class='wpexams-header-left'>
				<img src="<?php echo esc_url( $profile_pic ); ?>" alt="<?php esc_attr_e( 'Profile Picture', 'wpexams' ); ?>">
				<?php if ( $user_data ) : ?>
					<p><?php echo esc_html( $user_data->user_login ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	// Display navigation menus
	?>
	<div class='wpexams-head-menus-links'>
		<a class="wpexams-menu-link" href="<?php echo esc_url( get_permalink() ); ?>">
			<?php esc_html_e( 'New Exam', 'wpexams' ); ?>
		</a>
		<a class="wpexams-menu-link" href="?wpexams_history">
			<?php esc_html_e( 'Exam History', 'wpexams' ); ?>
		</a>
		<button type='button' class='wpexams-button wpexams-reset-question-bank' id='wpexams-reset-question-bank'
				title="<?php esc_attr_e( 'Clear questions usage history. So that the already used questions can again be used in a new exam.', 'wpexams' ); ?>">
			<?php esc_html_e( 'Reset Question Bank', 'wpexams' ); ?>
		</button>
	</div>

	<div class='wpexams-main'>
		<div>
			<span id='wpexams-reset-question-bank-message'></span>
		</div>
		<?php

		// Determine which template to load
		if ( isset( $_GET['wpexams_exam_id'] ) ) {
			wpexams_load_template( 'exam-start', compact( 'current_user_id', 'question_time_seconds', 'show_progressbar' ) );
		} elseif ( isset( $_GET['wpexams_review_id'] ) ) {
			wpexams_load_template( 'exam-review', compact( 'current_user_id', 'question_time_seconds' ) );
		} elseif ( isset( $_GET['wpexams_history'] ) ) {
			wpexams_load_template( 'exam-history', compact( 'current_user_id', 'question_time_seconds' ) );
		} else {
			wpexams_load_template( 'exam-list-predefined', compact( 'current_user_id' ) );
		}

		?>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'wpexams', 'wpexams_exam_shortcode' );

/**
 * Load template file
 *
 * @param string $template_name Template name without .php extension.
 * @param array  $args          Arguments to pass to template.
 */
function wpexams_load_template( $template_name, $args = array() ) {
	// Extract args to variables
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args, EXTR_SKIP );
	}

	$template_path = WPEXAMS_PLUGIN_DIR . 'public/templates/' . $template_name . '.php';

	if ( file_exists( $template_path ) ) {
		/**
		 * Fires before template is loaded
		 *
		 * @since 1.0.0
		 * @param string $template_name Template name.
		 * @param array  $args          Template arguments.
		 */
		do_action( 'wpexams_before_template', $template_name, $args );

		include $template_path;

		/**
		 * Fires after template is loaded
		 *
		 * @since 2.0.0
		 * @param string $template_name Template name.
		 * @param array  $args          Template arguments.
		 */
		do_action( 'wpexams_after_template', $template_name, $args );
	}
}

/**
 * Get login message for non-logged-in users
 *
 * @return string Login message HTML.
 */
function wpexams_get_login_message() {
	ob_start();
	?>
	<div class="wpexams-login-message">
		<p><?php esc_html_e( 'You need to login to take exams.', 'wpexams' ); ?></p>
		<a class='wpexams-button' href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
			<?php esc_html_e( 'Login', 'wpexams' ); ?>
		</a>
	</div>
	<?php
	return ob_get_clean();
}