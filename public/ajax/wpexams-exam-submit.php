<?php
/**
 * Submit answer immediately AJAX handler - FIXED VERSION (Issue #3)
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle immediate answer submission
 */
function wpexams_ajax_submit_answer() {
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

	// Validate required parameters
	$required = array( 'question_id', 'exam_id', 'user_answer', 'exam_time', 'question_time' );
	foreach ( $required as $param ) {
		if ( ! isset( $_POST[ $param ] ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %s: parameter name */
						__( 'Missing required parameter: %s', 'wpexams' ),
						$param
					),
				)
			);
		}
	}

	// Sanitize inputs
	$question_id   = absint( $_POST['question_id'] );
	$exam_id       = absint( $_POST['exam_id'] );
	$user_answer   = sanitize_key( $_POST['user_answer'] );
	$exam_time     = sanitize_text_field( $_POST['exam_time'] );
	$question_time = sanitize_text_field( $_POST['question_time'] );

	// Verify user can access this exam
	if ( ! wpexams_user_can_take_exam( $exam_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'You do not have permission to access this exam.', 'wpexams' ),
			)
		);
	}

	// Get question data
	$question_data = wpexams_get_post_data( $question_id );

	if ( empty( $question_data->question_fields ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Question not found.', 'wpexams' ),
			)
		);
	}

	// Get exam data
	$exam_data   = wpexams_get_post_data( $exam_id );
	$exam_detail = $exam_data->exam_detail;
	$exam_result = $exam_data->exam_result;

	// Check if answer is correct
	$correct_option = $question_data->question_fields['correct_option'];
	$is_correct     = ( substr( $correct_option, -1 ) === $user_answer );

	// Calculate total questions
	$total_questions = isset( $exam_detail['question_count'] ) ? $exam_detail['question_count'] : count( $exam_detail['filtered_questions'] );

	// Initialize result arrays if not exists
	if ( ! isset( $exam_result['correct_answers'] ) ) {
		$exam_result['correct_answers'] = array();
	}
	if ( ! isset( $exam_result['wrong_answers'] ) ) {
		$exam_result['wrong_answers'] = array();
	}
	if ( ! isset( $exam_result['solved_questions'] ) ) {
		$exam_result['solved_questions'] = array();
	}
	if ( ! isset( $exam_result['used_questions'] ) ) {
		$exam_result['used_questions'] = array();
	}
	if ( ! isset( $exam_result['question_times'] ) ) {
		$exam_result['question_times'] = array();
	}

	// CRITICAL: Check if this question was already answered to prevent duplicates
	$already_answered = in_array( (string) $question_id, $exam_result['solved_questions'], true );
	
	if ( $already_answered ) {
		// Question already answered - don't save again, just return current state
		wp_send_json_success(
			array(
				'is_correct'       => false, // We don't know, but it's already saved
				'correct_option'   => substr( $correct_option, -1 ),
				'explanation'      => $question_data->question_fields['description'],
				'exam_time'        => $exam_time,
				'solved_questions' => array_values( $exam_result['solved_questions'] ),
				'used_questions'   => array_values( $exam_result['used_questions'] ),
				'total_questions'  => $total_questions,
				'current_index'    => array_search( (int) $question_id, $exam_detail['filtered_questions'], true ),
				'progress_percent' => round( ( ( array_search( (int) $question_id, $exam_detail['filtered_questions'], true ) + 1 ) / $total_questions ) * 100 ),
			)
		);
	}

	// Update question time
	$exam_result['question_times'][] = array(
		'question_id' => (string) $question_id,
		'time'        => $question_time,
	);

	// Save answer (only if not already answered)
	if ( $is_correct ) {
		$exam_result['correct_answers'][] = array(
			'question_id' => $question_id,
			'answer'      => $user_answer,
		);
	} else {
		$exam_result['wrong_answers'][] = array(
			'question_id' => $question_id,
			'answer'      => $user_answer,
		);
	}

	// Add to solved questions (should not be duplicate due to check above)
	$exam_result['solved_questions'][] = (string) $question_id;
	$exam_result['used_questions'][]   = (string) $question_id;

	// Remove duplicates
	$exam_result['solved_questions'] = array_unique( $exam_result['solved_questions'] );
	$exam_result['used_questions']   = array_unique( $exam_result['used_questions'] );

	// Update exam time and status
	$exam_result['exam_time']      = $exam_time;
	$exam_result['total_questions'] = $total_questions;

	// Check if exam is complete
	$last_question = end( $exam_detail['filtered_questions'] );
	if ( (int) $question_id === (int) $last_question ) {
		$exam_result['exam_status'] = 'completed';
	} else {
		$exam_result['exam_status'] = 'pending';
	}

	// Save result
	update_post_meta( $exam_id, 'wpexams_exam_result', $exam_result );

	/**
	 * Fires after answer is submitted immediately
	 *
	 * @since 2.0.0
	 * @param int    $question_id Question ID.
	 * @param string $user_answer User's answer.
	 * @param bool   $is_correct  Whether answer is correct.
	 * @param int    $exam_id     Exam ID.
	 */
	do_action( 'wpexams_answer_submitted', $question_id, $user_answer, $is_correct, $exam_id );

	// FIXED: Calculate progress percentage correctly based on current position
	$current_index = array_search( (int) $question_id, $exam_detail['filtered_questions'], true );
	$progress_percent = 0;
	if ( false !== $current_index ) {
		// Calculate percentage: (current_index + 1) / total * 100
		// +1 because we've just answered this question
		$progress_percent = round( ( ( $current_index + 1 ) / $total_questions ) * 100 );
	}

	// Prepare response
	$response = array(
		'is_correct'       => $is_correct,
		'correct_option'   => substr( $correct_option, -1 ),
		'explanation'      => $question_data->question_fields['description'],
		'exam_time'        => $exam_time,
		'solved_questions' => array_values( $exam_result['solved_questions'] ),
		'used_questions'   => array_values( $exam_result['used_questions'] ),
		'total_questions'  => $total_questions,
		'current_index'    => $current_index,
		'progress_percent' => $progress_percent,
	);

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_wpexams_submit_answer', 'wpexams_ajax_submit_answer' );