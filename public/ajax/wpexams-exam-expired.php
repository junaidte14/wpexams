<?php
/**
 * Handle exam expiration AJAX handler
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle exam expiration
 */
function wpexams_ajax_exam_expired() {
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

	// Validate exam ID
	if ( empty( $_POST['exam_id'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Missing exam ID.', 'wpexams' ),
			)
		);
	}

	$exam_id = absint( $_POST['exam_id'] );

	// Verify user can access this exam
	if ( ! wpexams_user_can_take_exam( $exam_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'You do not have permission to access this exam.', 'wpexams' ),
			)
		);
	}

	// Get exam data
	$exam_data   = wpexams_get_post_data( $exam_id );
	$exam_detail = $exam_data->exam_detail;
	$exam_result = $exam_data->exam_result;

	if ( empty( $exam_detail ) || empty( $exam_result ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Exam data not found.', 'wpexams' ),
			)
		);
	}

	// Initialize arrays if not exist
	if ( ! isset( $exam_result['solved_questions'] ) ) {
		$exam_result['solved_questions'] = array();
	}
	if ( ! isset( $exam_result['wrong_answers'] ) ) {
		$exam_result['wrong_answers'] = array();
	}
	if ( ! isset( $exam_result['question_times'] ) ) {
		$exam_result['question_times'] = array();
	}

	// Get unanswered questions
	$filtered_questions = isset( $exam_result['filtered_questions'] ) ? $exam_result['filtered_questions'] : $exam_detail['filtered_questions'];
	$unanswered         = array_diff( $filtered_questions, $exam_result['solved_questions'] );

	// Mark all unanswered questions as wrong with null answer
	foreach ( $unanswered as $question_id ) {
		$exam_result['wrong_answers'][] = array(
			'question_id' => (int) $question_id,
			'answer'      => 'null',
		);

		$exam_result['solved_questions'][] = (string) $question_id;
	
		$exam_result['question_times'][] = array(
			'question_id' => (string) $question_id,
			'time'        => 'expired',
		);
	}

	// Remove duplicates
	$exam_result['solved_questions'] = array_unique( $exam_result['solved_questions'] );
	
	// Mark exam as completed and expired
	$exam_result['exam_status'] = 'completed';
	$exam_result['exam_time']   = 'expired';

	if ( ! isset( $exam_result['total_questions'] ) ) {
		$exam_result['total_questions'] = count( $filtered_questions );
	}

	// Save result
	update_post_meta( $exam_id, 'wpexams_exam_result', $exam_result );

	/**
	 * Fires when exam expires
	 *
	 * @since 2.0.0
	 * @param int $exam_id Exam ID.
	 * @param int $user_id User ID.
	 */
	do_action( 'wpexams_exam_expired', $exam_id, get_current_user_id() );

	wp_send_json_success(
		array(
			'message'   => __( 'Exam time expired.', 'wpexams' ),
			'exam_time' => 'expired',
		)
	);
}
add_action( 'wp_ajax_wpexams_exam_expired', 'wpexams_ajax_exam_expired' );