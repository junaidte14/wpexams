<?php
/**
 * Reset question bank AJAX handler
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reset question bank (clear used questions history)
 */
function wpexams_ajax_reset_question_bank() {
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

	// Get all user's exams
	$exams = get_posts(
		array(
			'post_type'      => 'wpexams_exam',
			'author'         => get_current_user_id(),
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		)
	);

	if ( empty( $exams ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'No exam history found.', 'wpexams' ),
			)
		);
	}

	$reset_count = 0;

	// Clear used_questions from each exam
	foreach ( $exams as $exam ) {
		$exam_result = get_post_meta( $exam->ID, 'wpexams_exam_result', true );

		if ( is_array( $exam_result ) && isset( $exam_result['used_questions'] ) ) {
			// Clear the used questions array
			$exam_result['used_questions'] = array();

			// Update the meta
			update_post_meta( $exam->ID, 'wpexams_exam_result', $exam_result );
			$reset_count++;
		}
	}

	/**
	 * Fires after question bank is reset
	 *
	 * @since 2.0.0
	 * @param int $user_id      User ID.
	 * @param int $reset_count  Number of exams reset.
	 */
	do_action( 'wpexams_question_bank_reset', get_current_user_id(), $reset_count );

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %d: number of exams */
				_n(
					'Question bank reset successfully. %d exam updated.',
					'Question bank reset successfully. %d exams updated.',
					$reset_count,
					'wpexams'
				),
				$reset_count
			),
			'reset_count' => $reset_count,
		)
	);
}
add_action( 'wp_ajax_wpexams_reset_question_bank', 'wpexams_ajax_reset_question_bank' );