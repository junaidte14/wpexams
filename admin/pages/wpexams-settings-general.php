<?php
/**
 * General settings page
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render general settings page
 */
function wpexams_settings_general_page() {
	// Get settings
	$settings = wpexams_get_setting( 'general' );

	// Set defaults if empty
	$default_options  = isset( $settings['default_question_options'] ) ? $settings['default_question_options'] : 4;
	$show_profile     = isset( $settings['show_profile_username'] ) ? $settings['show_profile_username'] : '1';
	$show_progressbar = isset( $settings['show_progressbar'] ) ? $settings['show_progressbar'] : '1';
	$question_time    = isset( $settings['question_time_seconds'] ) ? $settings['question_time_seconds'] : 82;

	?>
	<h2><?php esc_html_e( 'General Settings', 'wpexams' ); ?></h2>

	<div class="wrap">
		<form method="post" action="options.php">
			<?php settings_fields( 'wpexams_general_settings' ); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="wpexams_default_question_options">
								<?php esc_html_e( 'Default Question Options', 'wpexams' ); ?>
							</label>
						</th>
						<td>
							<select style='width: 18.6%;' 
									name="wpexams_general_settings[default_question_options]" 
									id="wpexams_default_question_options">
								<option value="2" <?php selected( $default_options, 2 ); ?>>2</option>
								<option value="3" <?php selected( $default_options, 3 ); ?>>3</option>
								<option value="4" <?php selected( $default_options, 4 ); ?>>4</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Default number of answer options for new questions.', 'wpexams' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="wpexams_show_profile_username">
								<?php esc_html_e( 'Profile and Username', 'wpexams' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" 
								   name='wpexams_general_settings[show_profile_username]' 
								   id='wpexams_show_profile_username' 
								   value="1" 
								   <?php checked( $show_profile, '1' ); ?> />
							<label for="wpexams_show_profile_username">
								<?php esc_html_e( 'Show user profile picture and username on exam page', 'wpexams' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="wpexams_show_progressbar">
								<?php esc_html_e( 'Progress Bar', 'wpexams' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" 
								   name='wpexams_general_settings[show_progressbar]' 
								   id='wpexams_show_progressbar' 
								   value="1" 
								   <?php checked( $show_progressbar, '1' ); ?> />
							<label for="wpexams_show_progressbar">
								<?php esc_html_e( 'Show progress bar during exam', 'wpexams' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="wpexams_question_time_seconds">
								<?php esc_html_e( 'Question Time (seconds)', 'wpexams' ); ?>
							</label>
						</th>
						<td>
							<input type="number" 
								   name='wpexams_general_settings[question_time_seconds]' 
								   id='wpexams_question_time_seconds' 
								   value="<?php echo esc_attr( $question_time ); ?>" 
								   min="1" 
								   class="regular-text" />
							<p class="description">
								<?php esc_html_e( 'Time allocated per question in timed exams (in seconds).', 'wpexams' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Save Changes', 'wpexams' ) ); ?>
		</form>
	</div>
	<?php
}