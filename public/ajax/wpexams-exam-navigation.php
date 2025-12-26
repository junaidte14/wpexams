<?php
/**
 * Exam navigation AJAX handler - FIXED VERSION (Issue #4 - No Duplicates)
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

    // Get exam data
    $exam_data = wpexams_get_post_data( $exam_id );

    if ( empty( $exam_data->exam_detail ) ) {
        wp_send_json_error(
            array(
                'message' => __( 'Exam data not found.', 'wpexams' ),
            )
        );
    }

    // FIXED: For predefined (admin_defined) exams, ensure user exam instance exists
    // This is done ONCE at the start, not on every question submission
    if ( isset( $exam_data->exam_detail['role'] ) && 'admin_defined' === $exam_data->exam_detail['role'] ) {
        // Check if this is the original predefined exam or already a user instance
        $is_original = ! get_post_meta( $exam_id, 'wpexams_original_exam_id', true );
        
        if ( $is_original ) {
            // This is the original predefined exam, create user instance
            $exam_id = wpexams_ensure_user_exam_instance( $exam_id, get_current_user_id() );
            
            // Refresh exam data with user instance
            $exam_data = wpexams_get_post_data( $exam_id );
        }
    }

    // Verify user can access this exam
    if ( ! wpexams_user_can_take_exam( $exam_id ) ) {
        wp_send_json_error(
            array(
                'message' => __( 'You do not have permission to access this exam.', 'wpexams' ),
            )
        );
    }

    // Handle exit action
    if ( 'exit' === $action_type ) {
        wpexams_handle_exam_exit( $exam_id, $question_id, $exam_time, $question_time, $user_answer );
        return;
    }

    // 1. Save current answer before moving to the next question
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

    // 2. Determine the Next/Prev ID using the helper function
    $next_question_info = wpexams_get_next_question_id(
        $question_id,
        $exam_data->exam_detail,
        $action_type
    );

    // 3. Logic for handling end-of-exam or beginning-of-exam
    if ( ! $next_question_info ) {
        if ( 'next' === $action_type ) {
            // No more questions forward: trigger result screen
            $action_type = 'show_result'; 
        } elseif ( 'prev' === $action_type ) {
            // No more questions backward
            wp_send_json_error( array( 'message' => __( 'You are at the beginning.', 'wpexams' ) ) );
        } else {
            // General failure
            wp_send_json_error( array( 'message' => __( 'Unable to determine next question.', 'wpexams' ) ) );
        }
    }

    // 4. Handle Result Screen (triggered by action_type or finishing last question)
    if ( 'show_result' === $action_type || wpexams_is_exam_complete( $exam_id, $question_id, $exam_data->exam_detail ) ) {
        $result_data = wpexams_generate_exam_result( $exam_id, $exam_time );
        wp_send_json_success( array(
            'action'  => 'show_result',
            'result'  => $result_data,
            'exam_id' => $exam_id,
        ) );
    }

    // 5. Get the Target Question Data
    $target_question_id = $next_question_info['next_id'];
    $next_question_data = wpexams_get_post_data( $target_question_id );

    if ( empty( $next_question_data->question_fields ) ) {
        wp_send_json_error(
            array(
                'message' => __( 'Question data not found.', 'wpexams' ),
            )
        );
    }

    // 6. Calculate progress percentage
    $progress_percent = wpexams_calculate_progress( $target_question_id, $exam_data->exam_detail );

    // 7. Prepare response
    $response = array(
        'action'            => 'show_question',
        'question_id'       => $target_question_id,
        'current_id'        => $target_question_id,
        'question_title'    => get_the_title( $target_question_id ),
        'question_options'  => $next_question_data->question_fields['options'],
        'correct_option'    => $next_question_data->question_fields['correct_option'],
        'description'       => $next_question_data->question_fields['description'],
        'all_question_ids'  => $exam_data->exam_detail['filtered_questions'],
        'show_prev'         => wpexams_should_show_prev( $target_question_id, $exam_data->exam_detail ),
        'show_next'         => wpexams_should_show_next( $target_question_id, $exam_data->exam_detail ),
        'progress_percent'  => $progress_percent,
    );

    /**
     * Filter navigation response
     */
    $response = apply_filters( 'wpexams_navigation_response', $response, $exam_id );

    wp_send_json_success( $response );
}
add_action( 'wp_ajax_wpexams_exam_navigation', 'wpexams_ajax_exam_navigation' );
add_action( 'wp_ajax_nopriv_wpexams_exam_navigation', 'wpexams_ajax_exam_navigation' );

/**
 * FIXED: Ensure user has their own exam instance for predefined exams (NO DUPLICATES)
 *
 * @since 1.0.0
 * @param int $original_exam_id Original predefined exam ID.
 * @param int $user_id          User ID.
 * @return int User's exam instance ID.
 */
function wpexams_ensure_user_exam_instance( $original_exam_id, $user_id ) {
	// Check if user already has ANY instance of this exam (completed or pending)
	$existing_instances = get_posts(
		array(
			'post_type'      => 'wpexams_exam',
			'author'         => $user_id,
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => 'wpexams_original_exam_id',
					'value' => $original_exam_id,
				),
			),
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	// FIXED: If exists and it's NOT completed, return that instance
	if ( ! empty( $existing_instances ) ) {
		$instance_id = $existing_instances[0]->ID;
		$instance_result = get_post_meta( $instance_id, 'wpexams_exam_result', true );
		
		// If exam is not completed, reuse this instance
		if ( isset( $instance_result['exam_status'] ) && 'completed' !== $instance_result['exam_status'] ) {
			return $instance_id;
		}
		
		// If completed, create a new instance (retake)
	}

	// Create a new user instance
	$original_exam = get_post( $original_exam_id );
	$exam_data     = wpexams_get_post_data( $original_exam_id );

	// Count how many times user has taken this exam for numbering
	$user_attempts = get_posts(
		array(
			'post_type'      => 'wpexams_exam',
			'author'         => $user_id,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => 'wpexams_original_exam_id',
					'value' => $original_exam_id,
				),
			),
			'fields'         => 'ids',
		)
	);

	$attempt_number = count( $user_attempts ) + 1;

	$user_exam_id = wp_insert_post(
		array(
			'post_title'  => $original_exam->post_title . ' (Attempt #' . $attempt_number . ')',
			'post_type'   => 'wpexams_exam',
			'post_status' => 'publish',
			'post_author' => $user_id,
		)
	);

	if ( is_wp_error( $user_exam_id ) ) {
		return $original_exam_id; // Fallback to original
	}

	// Copy exam detail
	$exam_detail = $exam_data->exam_detail;
	$exam_detail['original_exam_id'] = $original_exam_id;
	$exam_detail['user_id'] = $user_id;
	
	update_post_meta( $user_exam_id, 'wpexams_exam_detail', $exam_detail );
	update_post_meta( $user_exam_id, 'wpexams_original_exam_id', $original_exam_id );

	// Initialize exam result
	$exam_result = array(
		'filtered_questions' => $exam_data->exam_detail['filtered_questions'],
		'user_id'            => $user_id,
		'exam_status'        => 'pending',
		'solved_questions'   => array(),
		'used_questions'     => array(),
		'correct_answers'    => array(),
		'wrong_answers'      => array(),
		'question_times'     => array(),
		'total_questions'    => isset( $exam_data->exam_detail['question_count'] ) ? $exam_data->exam_detail['question_count'] : count( $exam_data->exam_detail['filtered_questions'] ),
	);
	update_post_meta( $user_exam_id, 'wpexams_exam_result', $exam_result );
	update_post_meta( $user_exam_id, 'wpexams_exam_status', 'Pending' );

	return $user_exam_id;
}

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
	$is_correct     = ( $correct_option === 'wpexams_c_option_' . $user_answer );

	// Check if this question was already answered (prevent duplicates)
	$already_answered = false;
	if ( isset( $result['solved_questions'] ) && is_array( $result['solved_questions'] ) ) {
		$already_answered = in_array( (string) $question_id, $result['solved_questions'], true );
	}

	// Only save if not already answered
	if ( ! $already_answered ) {
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

		// Remove duplicates
		$result['solved_questions'] = array_unique( $result['solved_questions'] );
		$result['used_questions']   = array_unique( $result['used_questions'] );
	}

	$result['exam_time'] = $exam_time;

	// Set total questions if not set
	if ( ! isset( $result['total_questions'] ) ) {
		$exam_detail = wpexams_get_post_data( $exam_id )->exam_detail;
		$result['total_questions'] = isset( $exam_detail['question_count'] ) ? $exam_detail['question_count'] : count( $exam_detail['filtered_questions'] );
	}

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
	$correct_count = 0;
	if ( isset( $result['correct_answers'] ) && is_array( $result['correct_answers'] ) ) {
		foreach ( $result['correct_answers'] as $answer ) {
			if ( isset( $answer['answer'] ) && 'null' !== $answer['answer'] ) {
				$correct_count++;
			}
		}
	}

	$total_count = isset( $result['total_questions'] ) ? $result['total_questions'] : count( $result['filtered_questions'] );
	$percentage  = $total_count > 0 ? ( $correct_count / $total_count ) * 100 : 0;

	$result['score_percentage'] = round( $percentage, 2 );
	$result['correct_count']    = $correct_count;
	$result['total_count']      = $total_count;

	update_post_meta( $exam_id, 'wpexams_exam_result', $result );
	update_post_meta( $exam_id, 'wpexams_exam_status', 'Completed' );

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