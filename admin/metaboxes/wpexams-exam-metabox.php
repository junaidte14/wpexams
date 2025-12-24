<?php
/**
 * Exam metabox
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register exam metabox
 */
function wpexams_register_exam_metabox() {
	add_meta_box(
		'wpexams_exam_detail_box',
		__( 'Exam Details', 'wpexams' ),
		'wpexams_render_exam_metabox',
		'wpexams_exam',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'wpexams_register_exam_metabox' );

/**
 * Render exam metabox
 *
 * @param WP_Post $post Post object.
 */
function wpexams_render_exam_metabox( $post ) {
	// Add nonce for security
	wp_nonce_field( 'wpexams_save_exam', 'wpexams_exam_nonce' );

	// Enqueue exam styles
	wp_enqueue_style( 'wpexams-exam-admin', WPEXAMS_PLUGIN_URL . 'assets/css/wpexams-admin.css', array(), WPEXAMS_VERSION );

	// Get saved data
	$exam_data   = wpexams_get_post_data( $post->ID );
	$exam_detail = $exam_data->exam_detail;

	// Get all categories
	$categories = get_categories(
		array(
			'orderby' => 'name',
			'order'   => 'ASC',
		)
	);

	// Get all questions for display
	$all_questions = get_posts(
		array(
			'post_type'      => 'wpexams_question',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'DESC',
		)
	);

	?>

	<?php if ( $exam_detail ) : ?>
		
		<!-- Categories (disabled for admin-defined exams) -->
		<div class='wpexams-category-content' id='wpexams-category-content'>
			<p class='wpexams-bold'><?php esc_html_e( 'Question Category', 'wpexams' ); ?></p>

			<input type="checkbox" disabled name='wpexams_category_field[]' 
				   <?php checked( in_array( '-1', $exam_detail['category_field'], true ) ); ?>
				   id='wpexams-all-1' value="-1">
			<label for="wpexams-all-1">
				<?php
				/* translators: %d: number of questions */
				printf( esc_html__( 'All (%d)', 'wpexams' ), count( $all_questions ) );
				?>
			</label><br>

			<?php foreach ( $categories as $category ) : ?>
				<?php
				$cat_questions = get_posts(
					array(
						'post_type'      => 'wpexams_question',
						'category'       => $category->term_id,
						'posts_per_page' => -1,
						'post_status'    => 'publish',
						'orderby'        => 'ID',
						'order'          => 'DESC',
					)
				);
				?>
				<input type="checkbox" disabled 
					   <?php checked( in_array( (string) $category->term_id, $exam_detail['category_field'], true ) ); ?>
					   id="<?php echo esc_attr( $category->term_id ); ?>" 
					   name='wpexams_category_field[]' 
					   value="<?php echo esc_attr( $category->term_id ); ?>">
				<label for="<?php echo esc_attr( $category->term_id ); ?>">
					<?php
					echo esc_html( $category->name );
					/* translators: %d: number of questions */
					printf( esc_html__( ' (%d)', 'wpexams' ), count( $cat_questions ) );
					?>
				</label><br>
			<?php endforeach; ?>
		</div>

		<!-- Questions List -->
		<div class='wpexams-q-number-content wpexams-mt-15'>
			<p class='wpexams-m-0 wpexams-bold'><?php esc_html_e( 'Add Questions', 'wpexams' ); ?> *</p>
			<div class='wpexams-question-content'>
				<?php if ( isset( $exam_detail['question_ids'] ) ) : ?>
					<?php foreach ( $exam_detail['question_ids'] as $question_id ) : ?>
						<?php $question_post = get_post( $question_id ); ?>
						<div class='wpexams-add-questions wpexams-mt-5'>
							<input type="hidden" value='<?php echo esc_attr( $question_id ); ?>' 
								   name='wpexams_question_ids[]' class='wpexams-question-id' />
							<input type="text" 
								   value='<?php echo $question_post ? esc_attr( $question_post->post_title ) : ''; ?>' 
								   class='wpexams-w-100 wpexams-question-id-rl' >
							<div class='wpexams-question-search-content wpexams-close-dropdown'></div>
							<div class='wpexams-mt-5'>
								<a class="button wpexams-admin-question-delete-btn" href="javascript:;">
									<?php esc_html_e( 'Delete', 'wpexams' ); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<div style='text-align:right;margin-top:10px;'>
			<button type='button' class='button wpexams-admin-exam-add-new-question'>
				<?php esc_html_e( 'Add New Question', 'wpexams' ); ?>
			</button>
		</div>

		<!-- Timed Exam -->
		<div class='wpexams-timed-content wpexams-mt-15'>
			<input type="checkbox" value='1' 
				   <?php checked( isset( $exam_detail['is_timed'] ) ? $exam_detail['is_timed'] : '0', '1' ); ?>
				   name="wpexams_timed_field" id="wpexams_timed_field">
			<label for="wpexams_timed_field" class="wpexams-bold">
				<?php esc_html_e( 'Timed?', 'wpexams' ); ?>
			</label>
		</div>

		<!-- Show Answer Immediately -->
		<div class='wpexams-answer-show-immed-content wpexams-mt-15'>
			<p class='wpexams-m-0 wpexams-bold'>
				<?php esc_html_e( 'View answer immediately after each question?', 'wpexams' ); ?>
			</p>
			<div>
				<input type="radio" 
					   <?php checked( isset( $exam_detail['show_answer_immediately'] ) ? $exam_detail['show_answer_immediately'] : '0', '1' ); ?>
					   name="wpexams_answer_show_immed_field[]" value="1" />
				<?php esc_html_e( 'Yes', 'wpexams' ); ?>
			</div>
			<div>
				<input type="radio" 
					   <?php checked( isset( $exam_detail['show_answer_immediately'] ) ? $exam_detail['show_answer_immediately'] : '0', '0' ); ?>
					   name="wpexams_answer_show_immed_field[]" value="0" />
				<?php esc_html_e( 'No', 'wpexams' ); ?>
			</div>
		</div>

	<?php else : ?>
		
		<!-- New Exam - Add Questions -->
		<div class='wpexams-q-number-content wpexams-mt-15'>
			<p class='wpexams-m-0 wpexams-bold'><?php esc_html_e( 'Add Questions', 'wpexams' ); ?> *</p>
			<div class='wpexams-question-content'>
				<div class='wpexams-add-questions wpexams-mt-5'>
					<input type="hidden" name='wpexams_question_ids[]' class='wpexams-question-id wpexams-w-100' />
					<input type="text" class='wpexams-w-100 wpexams-question-id-rl' 
						   placeholder="<?php esc_attr_e( 'Search questions...', 'wpexams' ); ?>">
					<div class='wpexams-question-search-content wpexams-close-dropdown'></div>
					<div class='wpexams-mt-5'>
						<a class="button wpexams-admin-question-delete-btn" href="javascript:;">
							<?php esc_html_e( 'Delete', 'wpexams' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div style='text-align:right;margin-top:10px;'>
			<button type='button' class='button wpexams-admin-exam-add-new-question'>
				<?php esc_html_e( 'Add New Question', 'wpexams' ); ?>
			</button>
		</div>

		<!-- Timed Exam -->
		<div class='wpexams-timed-content wpexams-mt-15'>
			<input type="checkbox" value='1' name="wpexams_timed_field" id="wpexams_timed_field">
			<label for="wpexams_timed_field" class="wpexams-bold">
				<?php esc_html_e( 'Timed?', 'wpexams' ); ?>
			</label>
		</div>

		<!-- Show Answer Immediately -->
		<div class='wpexams-answer-show-immed-content wpexams-mt-15'>
			<p class='wpexams-m-0 wpexams-bold'>
				<?php esc_html_e( 'View answer immediately after each question?', 'wpexams' ); ?>
			</p>
			<div>
				<input type="radio" name="wpexams_answer_show_immed_field[]" value="1" />
				<?php esc_html_e( 'Yes', 'wpexams' ); ?>
			</div>
			<div>
				<input type="radio" checked name="wpexams_answer_show_immed_field[]" value="0" />
				<?php esc_html_e( 'No', 'wpexams' ); ?>
			</div>
		</div>

	<?php endif; ?>

	<!-- Hidden template for jQuery -->
	<div class='wpexams-add-questions-hidden wpexams-mt-5 screen-reader-text'>
		<input type="hidden" name='wpexams_question_ids[]' class='wpexams-question-id wpexams-w-100' />
		<input type="text" class='wpexams-w-100 wpexams-question-id-rl' 
			   placeholder="<?php esc_attr_e( 'Search questions...', 'wpexams' ); ?>">
		<div class='wpexams-question-search-content wpexams-close-dropdown'></div>
		<div class='wpexams-mt-5'>
			<a class="button wpexams-admin-question-delete-btn" href="javascript:;">
				<?php esc_html_e( 'Delete', 'wpexams' ); ?>
			</a>
		</div>
	</div>

	<?php
}

/**
 * Save exam metabox data
 *
 * @param int $post_id Post ID.
 */
function wpexams_save_exam_metabox( $post_id ) {
	// Security checks
	if ( ! isset( $_POST['wpexams_exam_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['wpexams_exam_nonce'], 'wpexams_save_exam' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'wpexams_exam' !== get_post_type( $post_id ) ) {
		return;
	}

	// Validate required fields
	if ( ! isset( $_POST['wpexams_answer_show_immed_field'] ) || empty( $_POST['wpexams_answer_show_immed_field'] ) ) {
		add_filter(
			'redirect_post_location',
			function ( $location ) {
				return add_query_arg( 'wpexams_exam_error', 'missing_answer_setting', $location );
			}
		);
		return;
	}

	if ( ! isset( $_POST['wpexams_question_ids'] ) || ! is_array( $_POST['wpexams_question_ids'] ) || empty( $_POST['wpexams_question_ids'][0] ) ) {
		add_filter(
			'redirect_post_location',
			function ( $location ) {
				return add_query_arg( 'wpexams_exam_error', 'missing_questions', $location );
			}
		);
		return;
	}

	// Prepare exam detail data
	$exam_detail = array();

	// Category field (default to all for admin-defined)
	$exam_detail['category_field'] = array( '-1' );

	// Question IDs
	$question_ids = array_map( 'absint', $_POST['wpexams_question_ids'] );
	$question_ids = array_filter( $question_ids );
	$question_ids = array_unique( $question_ids );
	sort( $question_ids );

	$exam_detail['question_ids']       = $question_ids;
	$exam_detail['filtered_questions'] = $question_ids;

	// Timed field
	$exam_detail['is_timed'] = isset( $_POST['wpexams_timed_field'] ) ? '1' : '0';

	// Show answer immediately
	$exam_detail['show_answer_immediately'] = sanitize_key( $_POST['wpexams_answer_show_immed_field'][0] );

	// Role (admin-defined)
	$exam_detail['role'] = 'admin_defined';

	// All/unused questions (default to all for admin-defined)
	$exam_detail['unused_questions_only'] = '0';

	// User ID
	$exam_detail['user_id'] = get_current_user_id();

	// Save exam detail
	update_post_meta( $post_id, 'wpexams_exam_detail', $exam_detail );

	// Initialize exam result
	$exam_result = array(
		'filtered_questions' => $question_ids,
		'user_id'            => get_current_user_id(),
	);
	update_post_meta( $post_id, 'wpexams_exam_result', $exam_result );

	/**
	 * Fires after exam is saved
	 *
	 * @since 2.0.0
	 * @param int   $post_id     Post ID.
	 * @param array $exam_detail Exam detail data.
	 */
	do_action( 'wpexams_exam_saved', $post_id, $exam_detail );
}
add_action( 'save_post', 'wpexams_save_exam_metabox' );

/**
 * Display admin notices for exam errors
 */
function wpexams_exam_admin_notices() {
	if ( ! isset( $_GET['wpexams_exam_error'] ) ) {
		return;
	}

	$error   = sanitize_key( $_GET['wpexams_exam_error'] );
	$message = '';

	switch ( $error ) {
		case 'missing_answer_setting':
			$message = __( 'Please select whether to show answers immediately.', 'wpexams' );
			break;
		case 'missing_questions':
			$message = __( 'You must add at least one question before saving the exam.', 'wpexams' );
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
add_action( 'admin_notices', 'wpexams_exam_admin_notices' );