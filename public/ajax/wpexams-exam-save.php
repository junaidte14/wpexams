<?php
/**
 * Save new user-defined exam
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler for saving new exam
 */
function wpexams_ajax_save_exam() {
	// Verify nonce
	check_ajax_referer( 'wpexams_nonce', 'nonce' );

	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message' => __( 'You must be logged in.', 'wpexams' ),
			)
		);
	}

	// Validate exam data
	if ( empty( $_POST['exam_data'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Missing exam data.', 'wpexams' ),
			)
		);
	}

	// Sanitize exam data
	$exam_data = wp_unslash( $_POST['exam_data'] );

	// Validate required fields
	if ( empty( $exam_data['category_field'] ) || empty( $exam_data['question_count'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Please select categories and number of questions.', 'wpexams' ),
			)
		);
	}

	// Sanitize data
	$category_ids    = array_map( 'absint', (array) $exam_data['category_field'] );
	$question_count  = absint( $exam_data['question_count'] );
	$is_timed        = isset( $exam_data['is_timed'] ) ? '1' : '0';

	// Get all questions in categories
	$question_ids = get_posts(
		array(
			'post_type'      => 'wpexams_question',
			'category'       => $category_ids,
			'posts_per_page' => $question_count,
			'fields'         => 'ids',
			'post_status'    => 'publish',
			'orderby'        => 'rand',
		)
	);

	if ( empty( $question_ids ) || count( $question_ids ) < $question_count ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
					/* translators: %d: number of questions available */
					__( 'Not enough questions available. Only %d questions found in selected categories.', 'wpexams' ),
					count( $question_ids )
				),
			)
		);
	}

	// Limit to requested count
	$question_ids = array_slice( $question_ids, 0, $question_count );

	// Get user's exam count for numbering
	$user_exams = get_posts(
		array(
			'post_type'      => 'wpexams_exam',
			'author'         => get_current_user_id(),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'publish',
		)
	);
	$exam_number = count( $user_exams ) + 1;

	// Create exam post
	$exam_id = wp_insert_post(
		array(
			'post_title'  => sprintf(
				/* translators: %d: exam number */
				__( 'Exam #%d', 'wpexams' ),
				$exam_number
			),
			'post_type'   => 'wpexams_exam',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
		)
	);

	if ( is_wp_error( $exam_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Failed to create exam.', 'wpexams' ),
			)
		);
	}

	// Prepare exam detail
	$exam_detail = array(
		'category_field'            => $category_ids,
		'question_count'            => $question_count,
		'is_timed'                  => $is_timed,
		'role'                      => 'user_defined',
		'user_id'                   => get_current_user_id(),
		'filtered_questions'        => $question_ids,
	);

	// Save exam detail
	update_post_meta( $exam_id, 'wpexams_exam_detail', $exam_detail );
	
	do_action( 'wpexams_user_exam_created', $exam_id, $exam_detail );
	
	wp_send_json_success( array(
		'exam_id' => $exam_id,
		'message' => __( 'Exam created successfully!', 'wpexams' ),
	) );
}
add_action( 'wp_ajax_wpexams_save_exam', 'wpexams_ajax_save_exam' );