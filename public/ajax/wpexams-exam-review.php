<?php
/**
 * Exam review navigation AJAX handler
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle exam review navigation
 */
function wpexams_ajax_review_exam() {
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
	$required = array( 'question_id', 'action_type', 'exam_id' );
	foreach ( $required as $param ) {
		if ( empty( $_POST[ $param ] ) ) {
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
	$question_id = absint( $_POST['question_id'] );
	$action_type = sanitize_key( $_POST['action_type'] );
	$exam_id     = absint( $_POST['exam_id'] );

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
	$exam_result = $exam_data->exam_result;

	if ( empty( $exam_result['filtered_questions'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Exam questions not found.', 'wpexams' ),
			)
		);
	}

	// Get current question index
	$current_index = array_search( (int) $question_id, $exam_result['filtered_questions'], true );

	if ( false === $current_index ) {
		wp_send_json_error(
			array(
				'message' => __( 'Question not found in this exam.', 'wpexams' ),
			)
		);
	}

	// Calculate next question index
	if ( 'next' === $action_type ) {
		$next_index = $current_index + 1;
	} elseif ( 'prev' === $action_type ) {
		$next_index = $current_index - 1;
	} else {
		wp_send_json_error(
			array(
				'message' => __( 'Invalid action type.', 'wpexams' ),
			)
		);
	}

	// Validate index
	if ( $next_index < 0 || $next_index >= count( $exam_result['filtered_questions'] ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'No more questions in this direction.', 'wpexams' ),
			)
		);
	}

	// Get next question ID
	$next_question_id = $exam_result['filtered_questions'][ $next_index ];

	// Get question data
	$question_data = wpexams_get_post_data( $next_question_id );

	if ( empty( $question_data->question_fields ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Question data not found.', 'wpexams' ),
			)
		);
	}

	// Get user's answer for this question
	$user_answer = wpexams_get_user_answer( $exam_result, $next_question_id );

	// Get question time
	$question_time = wpexams_get_question_time( $exam_result, $next_question_id );

	// Prepare response
	$response = array(
		'question_id'      => $next_question_id,
		'question_title'   => get_the_title( $next_question_id ),
		'question_options' => $question_data->question_fields['options'],
		'correct_option'   => $question_data->question_fields['correct_option'],
		'user_answer'      => $user_answer,
		'question_time'    => $question_time,
		'explanation'      => $question_data->question_fields['description'],
		'all_question_ids' => $exam_result['filtered_questions'],
		'total_questions'  => $exam_result['total_questions'],
		'action_type'      => $action_type,
	);

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_wpexams_review_exam', 'wpexams_ajax_review_exam' );

/**
 * Get user's answer for a specific question
 *
 * @param array $exam_result Exam result data.
 * @param int   $question_id Question ID.
 * @return string|null User's answer or null if not found.
 */
function wpexams_get_user_answer( $exam_result, $question_id ) {
	// Check in correct answers
	if ( isset( $exam_result['correct_answers'] ) ) {
		foreach ( $exam_result['correct_answers'] as $answer ) {
			if ( (int) $answer['question_id'] === (int) $question_id ) {
				return $answer['answer'];
			}
		}
	}

	// Check in wrong answers
	if ( isset( $exam_result['wrong_answers'] ) ) {
		foreach ( $exam_result['wrong_answers'] as $answer ) {
			if ( (int) $answer['question_id'] === (int) $question_id ) {
				return $answer['answer'];
			}
		}
	}

	return 'null';
}

/**
 * Get time taken for a specific question
 *
 * @param array $exam_result Exam result data.
 * @param int   $question_id Question ID.
 * @return string Question time or default.
 */
function wpexams_get_question_time( $exam_result, $question_id ) {
	if ( isset( $exam_result['question_times'] ) ) {
		foreach ( $exam_result['question_times'] as $time_data ) {
			if ( (int) $time_data['question_id'] === (int) $question_id ) {
				return $time_data['time'];
			}
		}
	}

	return '00:00:00';
}