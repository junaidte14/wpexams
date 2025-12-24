<?php
/**
 * Exam navigation AJAX handler
 *
 * Handles next/previous question navigation with full security
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle exam question navigation
 */
function wpexams_ajax_exam_navigation() {
	// Verify nonce
	check_ajax_referer( 'wpexams_nonce', 'nonce' );

	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message' => __( 'You must be logged in to take exams.', 'wpexams' ),
			)
		);
	}

	// Validate required parameters
	$required_params = array( 'question_id', 'action_type', 'exam_id', 'exam_time', 'question_time' );
	foreach ( $required_params as $param ) {
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
	$question_id    = absint( $_POST['question_id'] );
	$action_type    = sanitize_key( $_POST['action_type'] );
	$exam_id        = absint( $_POST['exam_id'] );
	$exam_time      = sanitize_text_field( $_POST['exam_time'] );
	$question_time  = sanitize_text_field( $_POST['question_time'] );
	$user_answer    = isset( $_POST['user_answer'] ) ? sanitize_key( $_POST['user_answer'] ) : 'null';
	$show_immediate = isset( $_POST['show_immediate'] ) ? '1' : '0';

	// Verify user can access this exam
	if ( ! wpexams_user_can_take_exam( $exam_id ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'You do not have permission to access this exam.', 'wpexams' ),
			)
		);
	}

	// Get exam data
	$exam_data = wpexams_get_post_data( $exam_id );

	if ( empty( $exam_data->exam_detail ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Exam data not found.', 'wpexams' ),
			)
		);
	}

	// Handle exit action
	if ( 'exit' === $action_type ) {
		wpexams_handle_exam_exit( $exam_id, $question_id, $exam_time, $question_time, $user_answer );
		return;
	}

	// Get next question ID
	$next_question = wpexams_get_next_question_id(
		$question_id,
		$exam_data->exam_detail,
		$action_type
	);

	if ( ! $next_question ) {
		wp_send_json_error(
			array(
				'message' => __( 'Unable to determine next question.', 'wpexams' ),
			)
		);
	}

	// Save current answer
	if ( 'null' !== $user_answer ) {
		wpexams_save_exam_answer(
			$exam_id,
			$question_id,
			$user_answer,
			$exam_time,
			$question_time,
			$show_immediate
		);
	}

	// Check if exam is complete
	if ( wpexams_is_exam_complete( $exam_id, $next_question['current_id'], $exam_data->exam_detail ) ) {
		$result_data = wpexams_generate_exam_result( $exam_id, $exam_time );
		wp_send_json_success(
			array(
				'action'     => 'show_result',
				'result'     => $result_data,
				'exam_id'    => $exam_id,
			)
		);
	}

	// Get next question data
	$next_question_data = wpexams_get_post_data( $next_question['next_id'] );

	if ( empty( $next_question_data->question_fields ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Question data not found.', 'wpexams' ),
			)
		);
	}

	// Prepare response
	$response = array(
		'action'            => 'show_question',
		'question_id'       => $next_question['next_id'],
		'current_id'        => $next_question['current_id'],
		'question_title'    => get_the_title( $next_question['next_id'] ),
		'question_options'  => $next_question_data->question_fields['options'],
		'correct_option'    => $next_question_data->question_fields['correct_option'],
		'description'       => $next_question_data->question_fields['description'],
		'all_question_ids'  => $exam_data->exam_detail['filtered_questions'],
		'show_prev'         => wpexams_should_show_prev( $next_question['next_id'], $exam_data->exam_detail ),
		'show_next'         => wpexams_should_show_next( $next_question['next_id'], $exam_data->exam_detail ),
		'progress_percent'  => wpexams_calculate_progress( $next_question['current_id'], $exam_data->exam_detail ),
	);

	/**
	 * Filter navigation response
	 *
	 * @since 1.0.0
	 * @param array $response Response data.
	 * @param int   $exam_id  Exam ID.
	 */
	$response = apply_filters( 'wpexams_navigation_response', $response, $exam_id );

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_wpexams_exam_navigation', 'wpexams_ajax_exam_navigation' );

/**
 * Get next question ID based on action
 *
 * @since 1.0.0
 * @param int    $current_id   Current question ID.
 * @param array  $exam_detail  Exam detail data.
 * @param string $action_type  Action type (next/prev).
 * @return array|false Next question data or false on failure.
 */
function wpexams_get_next_question_id( $current_id, $exam_detail, $action_type ) {
	if ( empty( $exam_detail['filtered_questions'] ) ) {
		return false;
	}

	$questions     = $exam_detail['filtered_questions'];
	$current_index = array_search( (int) $current_id, $questions, true );

	if ( false === $current_index ) {
		return false;
	}

	$next_index = $current_index;

	if ( 'next' === $action_type ) {
		$next_index = $current_index + 1;
	} elseif ( 'prev' === $action_type ) {
		$next_index = $current_index - 1;
	}

	// Validate index
	if ( $next_index < 0 || $next_index >= count( $questions ) ) {
		return false;
	}

	return array(
		'next_id'    => $questions[ $next_index ],
		'current_id' => $current_id,
	);
}

/**
 * Save exam answer
 *
 * @since 1.0.0
 * @param int    $exam_id        Exam ID.
 * @param int    $question_id    Question ID.
 * @param string $user_answer    User's answer.
 * @param string $exam_time      Total exam time.
 * @param string $question_time  Question time.
 * @param string $show_immediate Whether to show answer immediately.
 * @return bool True on success.
 */
function wpexams_save_exam_answer( $exam_id, $question_id, $user_answer, $exam_time, $question_time, $show_immediate ) {
	$exam_data = wpexams_get_post_data( $exam_id );

	// Initialize result if not exists
	$result = ! empty( $exam_data->exam_result ) ? $exam_data->exam_result : array(
		'filtered_questions' => array(),
		'solved_questions'   => array(),
		'used_questions'     => array(),
		'correct_answers'    => array(),
		'wrong_answers'      => array(),
		'question_times'     => array(),
		'exam_time'          => '',
		'exam_status'        => 'pending',
	);

	// Get question data
	$question_data = wpexams_get_post_data( $question_id );

	if ( empty( $question_data->question_fields ) ) {
		return false;
	}

	// Determine if answer is correct
	$correct_option = $question_data->question_fields['correct_option'];
	$is_correct     = ( $correct_option === $user_answer );

	// Update result
	$result['solved_questions'][] = (string) $question_id;
	$result['used_questions'][]   = (string) $question_id;
	$result['question_times'][]   = array(
		'question_id' => (string) $question_id,
		'time'        => $question_time,
	);

	if ( $is_correct ) {
		$result['correct_answers'][] = array(
			'question_id' => $question_id,
			'answer'      => $user_answer,
		);
	} else {
		$result['wrong_answers'][] = array(
			'question_id' => $question_id,
			'answer'      => $user_answer,
		);
	}

	$result['exam_time'] = $exam_time;

	// Remove duplicates
	$result['solved_questions'] = array_unique( $result['solved_questions'] );
	$result['used_questions']   = array_unique( $result['used_questions'] );

	/**
	 * Fires after answer is saved
	 *
	 * @since 1.0.0
	 * @param int    $question_id Question ID.
	 * @param string $user_answer User's answer.
	 * @param bool   $is_correct  Whether answer is correct.
	 * @param int    $exam_id     Exam ID.
	 */
	do_action( 'wpexams_answer_saved', $question_id, $user_answer, $is_correct, $exam_id );

	// Save result
	return update_post_meta( $exam_id, 'wpexams_exam_result', $result );
}

/**
 * Check if exam is complete
 *
 * @since 1.0.0
 * @param int   $exam_id     Exam ID.
 * @param int   $question_id Current question ID.
 * @param array $exam_detail Exam detail data.
 * @return bool True if complete.
 */
function wpexams_is_exam_complete( $exam_id, $question_id, $exam_detail ) {
	if ( empty( $exam_detail['filtered_questions'] ) ) {
		return false;
	}

	$last_question = end( $exam_detail['filtered_questions'] );

	return (int) $question_id === (int) $last_question;
}

/**
 * Generate exam result data
 *
 * @since 1.0.0
 * @param int    $exam_id   Exam ID.
 * @param string $exam_time Total exam time.
 * @return array Result data.
 */
function wpexams_generate_exam_result( $exam_id, $exam_time ) {
	$exam_data = wpexams_get_post_data( $exam_id );
	$result    = $exam_data->exam_result;

	// Mark as complete
	$result['exam_status'] = 'completed';
	$result['exam_time']   = $exam_time;

	// Calculate score
	$correct_count = count( $result['correct_answers'] );
	$total_count   = count( $result['filtered_questions'] );
	$percentage    = $total_count > 0 ? ( $correct_count / $total_count ) * 100 : 0;

	$result['score_percentage'] = round( $percentage, 2 );
	$result['correct_count']    = $correct_count;
	$result['total_count']      = $total_count;

	update_post_meta( $exam_id, 'wpexams_exam_result', $result );

	/**
	 * Fires when exam is completed
	 *
	 * @since 1.0.0
	 * @param int   $exam_id Exam ID.
	 * @param int   $user_id User ID.
	 * @param array $result  Result data.
	 */
	do_action( 'wpexams_exam_completed', $exam_id, get_current_user_id(), $result );

	return $result;
}

/**
 * Handle exam exit
 *
 * @since 1.0.0
 * @param int    $exam_id       Exam ID.
 * @param int    $question_id   Current question ID.
 * @param string $exam_time     Total exam time.
 * @param string $question_time Question time.
 * @param string $user_answer   User's answer.
 */
function wpexams_handle_exam_exit( $exam_id, $question_id, $exam_time, $question_time, $user_answer ) {
	// Save current answer if provided
	if ( 'null' !== $user_answer ) {
		wpexams_save_exam_answer( $exam_id, $question_id, $user_answer, $exam_time, $question_time, '0' );
	}

	// Mark exit question
	$exam_data = wpexams_get_post_data( $exam_id );
	$result    = $exam_data->exam_result;

	$result['exit_question'] = $question_id;
	$result['exam_time']     = $exam_time;

	update_post_meta( $exam_id, 'wpexams_exam_result', $result );

	wp_send_json_success(
		array(
			'action'  => 'exit',
			'message' => __( 'Exam progress saved. You can resume later.', 'wpexams' ),
		)
	);
}

/**
 * Calculate exam progress percentage
 *
 * @since 1.0.0
 * @param int   $current_id  Current question ID.
 * @param array $exam_detail Exam detail data.
 * @return int Progress percentage.
 */
function wpexams_calculate_progress( $current_id, $exam_detail ) {
	if ( empty( $exam_detail['filtered_questions'] ) ) {
		return 0;
	}

	$questions     = $exam_detail['filtered_questions'];
	$current_index = array_search( (int) $current_id, $questions, true );

	if ( false === $current_index ) {
		return 0;
	}

	return round( ( ( $current_index + 1 ) / count( $questions ) ) * 100 );
}

/**
 * Check if previous button should show
 *
 * @since 1.0.0
 * @param int   $question_id Current question ID.
 * @param array $exam_detail Exam detail data.
 * @return bool True if should show.
 */
function wpexams_should_show_prev( $question_id, $exam_detail ) {
	if ( empty( $exam_detail['filtered_questions'] ) ) {
		return false;
	}

	$first_question = reset( $exam_detail['filtered_questions'] );

	return (int) $question_id !== (int) $first_question;
}

/**
 * Check if next button should show
 *
 * @since 1.0.0
 * @param int   $question_id Current question ID.
 * @param array $exam_detail Exam detail data.
 * @return bool True if should show.
 */
function wpexams_should_show_next( $question_id, $exam_detail ) {
	if ( empty( $exam_detail['filtered_questions'] ) ) {
		return false;
	}

	$last_question = end( $exam_detail['filtered_questions'] );

	return (int) $question_id !== (int) $last_question;
}