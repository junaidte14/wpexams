<?php
/**
 * Question metabox
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register question metabox
 */
function wpexams_register_question_metabox() {
	add_meta_box(
		'wpexams_question_box',
		__( 'Question Details', 'wpexams' ),
		'wpexams_render_question_metabox',
		'wpexams_question',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'wpexams_register_question_metabox' );

/**
 * Render question metabox
 *
 * @param WP_Post $post Post object.
 */
function wpexams_render_question_metabox( $post ) {
	// Add nonce for security
	wp_nonce_field( 'wpexams_save_question', 'wpexams_question_nonce' );

	// Get saved data
	$question_data = wpexams_get_post_data( $post->ID );
	$fields        = $question_data->question_fields;

	// Get default options count from settings
	$general_settings = wpexams_get_setting( 'general' );
	$default_options  = isset( $general_settings['default_question_options'] ) ? $general_settings['default_question_options'] : 4;

	?>
	<div class="wpexams-question-main" id="wpexams-question-main">
		
		<?php if ( ! empty( $fields ) && isset( $fields['options'] ) ) : ?>
			
			<!-- Existing question with saved options -->
			<section class="wpexams-question-added">
				<div class="wpexams-question-row" id="wpexams-question-row">
					<?php foreach ( $fields['options'] as $key => $option ) : ?>
						<div class="wpexams-question-col">
							<label for="wpexams_question_<?php echo intval( $key ) + 1; ?>_field" style="font-weight: bold;">
								<?php
								/* translators: %d: option number */
								printf( esc_html__( 'Option %d', 'wpexams' ), intval( $key ) + 1 );
								?>
								<span class='wpexams-opt-num'><?php echo intval( $key ) + 1; ?></span>
							</label>
							
							<?php if ( intval( $key ) + 1 > $default_options ) : ?>
								<div style='display:flex;'>
									<input style='width: 95%;' 
										   name="wpexams_question_options[]" 
										   class="wpexams-question-field" 
										   id="wpexams_question_<?php echo intval( $key ) + 1; ?>_field" 
										   value="<?php echo esc_attr( str_replace( '_', ' ', $option ) ); ?>" />
									<a class="button wpexams-remove-question-option" href="javascript:;">
										<span style='line-height: 2;' class="dashicons dashicons-trash"></span>
									</a>
								</div>
							<?php else : ?>
								<input name="wpexams_question_options[]" 
									   class="wpexams-question-field" 
									   id="wpexams_question_<?php echo intval( $key ) + 1; ?>_field" 
									   value="<?php echo esc_attr( str_replace( '_', ' ', $option ) ); ?>" />
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<!-- Add Option Button -->
				<div class='wpexams-text-right'>
					<button id='wpexams-add-option' type='button' class='button button-primary'>
						<?php esc_html_e( 'Add Option', 'wpexams' ); ?>
					</button>
				</div>

				<!-- Correct Option Select -->
				<div class="wpexams-question-third-row" id="wpexams-question-third-row">
					<label for="wpexams_correct_field" style="font-weight: bold;">
						<?php esc_html_e( 'Correct Option', 'wpexams' ); ?>
					</label>
					<select name="wpexams_correct_field" id="wpexams_correct_field" class="wpexams-correct-field">
						<?php foreach ( $fields['options'] as $key => $option ) : ?>
							<option value="wpexams_c_option_<?php echo esc_attr( $key ); ?>" 
									<?php selected( $fields['correct_option'], 'wpexams_c_option_' . $key ); ?>>
								<?php
								/* translators: %d: option number */
								printf( esc_html__( 'Option %d', 'wpexams' ), intval( $key ) + 1 );
								?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Description/Explanation -->
				<div class="wpexams-question-description-row" id='wpexams-question-description-row'>
					<label for="wpexams_question_description_field" style="font-weight: bold;">
						<?php esc_html_e( 'Description/Explanation', 'wpexams' ); ?>
					</label>
					<textarea name="wpexams_question_description_field" 
							  class="wpexams-question-description-field" 
							  id="wpexams_question_description_field" 
							  cols="6" rows="5"><?php echo esc_textarea( $fields['description'] ); ?></textarea>
				</div>
			</section>

		<?php else : ?>
			
			<!-- New question - default options -->
			<section class="wpexams-question-main">
				<div class="wpexams-question-row" id="wpexams-question-row">
					<?php for ( $i = 1; $i <= intval( $default_options ); $i++ ) : ?>
						<div class="wpexams-question-col">
							<label for="wpexams_question_<?php echo $i; ?>_field" style="font-weight: bold;">
								<?php
								/* translators: %d: option number */
								printf( esc_html__( 'Option %d', 'wpexams' ), $i );
								?>
								<span class='wpexams-opt-num'><?php echo $i; ?></span>
							</label>
							<input name="wpexams_question_options[]" 
								   class="wpexams-question-field" 
								   id="wpexams_question_<?php echo $i; ?>_field" />
						</div>
					<?php endfor; ?>
				</div>

				<!-- Add Option Button -->
				<div class='wpexams-text-right'>
					<button id='wpexams-add-option' type='button' class='button button-primary'>
						<?php esc_html_e( 'Add Option', 'wpexams' ); ?>
					</button>
				</div>

				<!-- Correct Option Select -->
				<div class="wpexams-question-third-row" id="wpexams-question-third-row">
					<label for="wpexams_correct_field" style="font-weight: bold;">
						<?php esc_html_e( 'Correct Option', 'wpexams' ); ?>
					</label>
					<select name="wpexams_correct_field" id="wpexams_correct_field" class="wpexams-correct-field">
						<?php for ( $i = 0; $i < intval( $default_options ); $i++ ) : ?>
							<option value="wpexams_c_option_<?php echo $i; ?>" class='wpexams-opt-<?php echo $i; ?>'>
								<?php
								/* translators: %d: option number */
								printf( esc_html__( 'Option %d', 'wpexams' ), intval( $i ) + 1 );
								?>
							</option>
						<?php endfor; ?>
					</select>
				</div>

				<!-- Description/Explanation -->
				<div class="wpexams-question-description-row" id='wpexams-question-description-row'>
					<label for="wpexams_question_description_field" style="font-weight: bold;">
						<?php esc_html_e( 'Description/Explanation', 'wpexams' ); ?>
					</label>
					<textarea name="wpexams_question_description_field" 
							  class="wpexams-question-description-field" 
							  id="wpexams_question_description_field" 
							  cols="6" rows="5"></textarea>
				</div>
			</section>

		<?php endif; ?>

		<!-- Hidden template for adding options via JavaScript -->
		<div class="wpexams-question-col wpexams-empty-question-option screen-reader-text">
			<label style="font-weight: bold;">
				<?php esc_html_e( 'Option', 'wpexams' ); ?> <span class='wpexams-opt-num'>null</span>
			</label>
			<div style='display:flex;'>
				<input style='width: 95%;' name="wpexams_question_options[]" />
				<a class="button wpexams-remove-question-option" href="javascript:;">
					<span style='line-height: 2;' class="dashicons dashicons-trash"></span>
				</a>
			</div>
		</div>

	</div>
	<?php
}

/**
 * Save question metabox data
 *
 * @param int $post_id Post ID.
 */
function wpexams_save_question_metabox( $post_id ) {
	// Security checks
	if ( ! isset( $_POST['wpexams_question_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['wpexams_question_nonce'], 'wpexams_save_question' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'wpexams_question' !== get_post_type( $post_id ) ) {
		return;
	}

	// Validate required fields
	if ( ! isset( $_POST['wpexams_question_options'] ) || ! is_array( $_POST['wpexams_question_options'] ) ) {
		add_filter(
			'redirect_post_location',
			function ( $location ) {
				return add_query_arg( 'wpexams_question_error', 'missing_options', $location );
			}
		);
		return;
	}

	// Check that first 4 options are not empty
	$options = $_POST['wpexams_question_options'];
	if ( empty( $options[0] ) || empty( $options[1] ) || empty( $options[2] ) || empty( $options[3] ) ) {
		add_filter(
			'redirect_post_location',
			function ( $location ) {
				return add_query_arg( 'wpexams_question_error', 'empty_options', $location );
			}
		);
		return;
	}

	// Validate correct option
	if ( empty( $_POST['wpexams_correct_field'] ) ) {
		add_filter(
			'redirect_post_location',
			function ( $location ) {
				return add_query_arg( 'wpexams_question_error', 'missing_correct', $location );
			}
		);
		return;
	}

	// Validate description
	if ( empty( $_POST['wpexams_question_description_field'] ) ) {
		add_filter(
			'redirect_post_location',
			function ( $location ) {
				return add_query_arg( 'wpexams_question_error', 'missing_description', $location );
			}
		);
		return;
	}

	// Sanitize and prepare data
	$question_data = array();

	// Get post title
	$post_data                      = get_post( $post_id );
	$question_data['title']         = $post_data->post_title;

	// Process options - replace spaces with underscores
	$sanitized_options = array();
	foreach ( $options as $option ) {
		if ( ! empty( $option ) ) {
			$sanitized_options[] = str_replace( ' ', '_', sanitize_text_field( $option ) );
		}
	}
	$question_data['options'] = $sanitized_options;

	// Correct option
	$question_data['correct_option'] = sanitize_key( $_POST['wpexams_correct_field'] );

	// Description
	$question_data['description'] = wp_kses_post( $_POST['wpexams_question_description_field'] );

	// Save data
	update_post_meta( $post_id, 'wpexams_question_fields', $question_data );

	/**
	 * Fires after question is saved
	 *
	 * @since 2.0.0
	 * @param int   $post_id       Post ID.
	 * @param array $question_data Question data.
	 */
	do_action( 'wpexams_question_saved', $post_id, $question_data );
}
add_action( 'save_post', 'wpexams_save_question_metabox' );

/**
 * Display admin notices for question errors
 */
function wpexams_question_admin_notices() {
	if ( ! isset( $_GET['wpexams_question_error'] ) ) {
		return;
	}

	$error = sanitize_key( $_GET['wpexams_question_error'] );
	$message = '';

	switch ( $error ) {
		case 'missing_options':
			$message = __( 'Please add question options.', 'wpexams' );
			break;
		case 'empty_options':
			$message = __( 'The first four options cannot be empty.', 'wpexams' );
			break;
		case 'missing_correct':
			$message = __( 'Please select the correct option.', 'wpexams' );
			break;
		case 'missing_description':
			$message = __( 'Please add a description/explanation.', 'wpexams' );
			break;
	}

	if ( $message ) {
		printf(
			'<div class="notice notice-error"><p><strong>%s:</strong> %s</p></div>',
			esc_html__( 'Error', 'wpexams' ),
			esc_html( $message )
		);
	}
}
add_action( 'admin_notices', 'wpexams_question_admin_notices' );