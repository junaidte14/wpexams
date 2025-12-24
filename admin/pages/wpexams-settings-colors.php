<?php
/**
 * Color settings page
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render color settings page
 */
function wpexams_settings_colors_page() {
	// Get settings
	$settings = wpexams_get_setting( 'color' );

	// Set defaults if empty
	$button_bg        = isset( $settings['button_bg_color'] ) ? $settings['button_bg_color'] : '#000000';
	$button_text      = isset( $settings['button_text_color'] ) ? $settings['button_text_color'] : '#ffffff';
	$progressbar_bg   = isset( $settings['progressbar_bg_color'] ) ? $settings['progressbar_bg_color'] : '#000000';
	$progressbar_text = isset( $settings['progressbar_text_color'] ) ? $settings['progressbar_text_color'] : '#ffffff';

	?>
	<h2><?php esc_html_e( 'Color Settings', 'wpexams' ); ?></h2>

	<div class="wrap">
		<form method="post" action="options.php">
			<?php settings_fields( 'wpexams_color_settings' ); ?>

			<h3><?php esc_html_e( 'Button Colors', 'wpexams' ); ?></h3>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="wpexams_button_bg_color">
								<?php esc_html_e( 'Background Color', 'wpexams' ); ?>
							</label>
						</th>
						<td>
							<input type="text" 
								   name='wpexams_color_settings[button_bg_color]' 
								   id='wpexams_button_bg_color' 
								   value='<?php echo esc_attr( $button_bg ); ?>' 
								   data-default-color="#000000" 
								   class="wpexams-color-picker" />
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="wpexams_button_text_color">
								<?php esc_html_e( 'Text Color', 'wpexams' ); ?>
							</label>
						</th>
						<td>
							<input type="text" 
								   name='wpexams_color_settings[button_text_color]' 
								   id='wpexams_button_text_color' 
								   value='<?php echo esc_attr( $button_text ); ?>' 
								   data-default-color="#FFFFFF" 
								   class="wpexams-color-picker" />
						</td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Progress Bar Colors', 'wpexams' ); ?></h3>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="wpexams_progressbar_bg_color">
								<?php esc_html_e( 'Background Color', 'wpexams' ); ?>
							</label>
						</th>
						<td>
							<input type="text" 
								   name='wpexams_color_settings[progressbar_bg_color]' 
								   id='wpexams_progressbar_bg_color' 
								   value='<?php echo esc_attr( $progressbar_bg ); ?>' 
								   data-default-color="#000000" 
								   class="wpexams-color-picker" />
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="wpexams_progressbar_text_color">
								<?php esc_html_e( 'Text Color', 'wpexams' ); ?>
							</label>
						</th>
						<td>
							<input type="text" 
								   name='wpexams_color_settings[progressbar_text_color]' 
								   id='wpexams_progressbar_text_color' 
								   value='<?php echo esc_attr( $progressbar_text ); ?>' 
								   data-default-color="#FFFFFF" 
								   class="wpexams-color-picker" />
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Save Changes', 'wpexams' ) ); ?>
		</form>
	</div>
	<?php
}