<?php
/**
 * Core plugin functions
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugin settings
 *
 * @since 1.0.0
 * @param string $group Settings group name.
 * @param string $key   Optional. Specific setting key.
 * @return mixed Settings value or array of all settings.
 */
function wpexams_get_setting( $group = 'general', $key = '' ) {
	$option_name = 'wpexams_' . $group . '_settings';
	$settings    = get_option( $option_name, array() );

	if ( ! empty( $key ) ) {
		return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	}

	return $settings;
}

/**
 * Get admin user IDs
 *
 * @since 1.0.0
 * @return array Array of administrator user IDs.
 */
function wpexams_get_admin_ids() {
	static $admin_ids = null;

	if ( null === $admin_ids ) {
		$admin_users = get_users(
			array(
				'role'   => 'administrator',
				'fields' => 'ID',
			)
		);
		$admin_ids   = wp_list_pluck( $admin_users, 'ID' );
	}

	return $admin_ids;
}

/**
 * Get post meta data wrapper
 *
 * @since 1.0.0
 * @param int $post_id Post ID.
 * @return object Object containing question fields, exam result, exam detail.
 */
function wpexams_get_post_data( $post_id ) {
	$data = new stdClass();

	$data->question_fields = get_post_meta( $post_id, 'wpexams_question_fields', true );
	$data->exam_result     = get_post_meta( $post_id, 'wpexams_exam_result', true );
	$data->exam_detail     = get_post_meta( $post_id, 'wpexams_exam_detail', true );
	$data->exam_status     = get_post_meta( $post_id, 'wpexams_exam_status', true );

	/**
	 * Filter post data
	 *
	 * @since 1.0.0
	 * @param object $data    Post data object.
	 * @param int    $post_id Post ID.
	 */
	return apply_filters( 'wpexams_post_data', $data, $post_id );
}

/**
 * Check if user can take exam
 *
 * @since 1.0.0
 * @param int $exam_id Exam post ID.
 * @param int $user_id User ID. Default current user.
 * @return bool True if user can take exam.
 */
function wpexams_user_can_take_exam( $exam_id, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	$exam_author = get_post_field( 'post_author', $exam_id );
	$admin_ids   = wpexams_get_admin_ids();

	// User is exam owner or exam is by admin
	if ( (int) $user_id === (int) $exam_author || in_array( (int) $exam_author, $admin_ids, true ) ) {
		return true;
	}

	/**
	 * Filter user exam access
	 *
	 * @since 1.0.0
	 * @param bool $can_take Whether user can take exam.
	 * @param int  $exam_id  Exam ID.
	 * @param int  $user_id  User ID.
	 */
	return apply_filters( 'wpexams_user_can_take_exam', false, $exam_id, $user_id );
}

/**
 * Sanitize exam detail data
 *
 * @since 1.0.0
 * @param array $data Exam detail data.
 * @return array Sanitized data.
 */
function wpexams_sanitize_exam_detail( $data ) {
	$sanitized = array();

	// Category field
	if ( isset( $data['category_field'] ) && is_array( $data['category_field'] ) ) {
		$sanitized['category_field'] = array_map( 'absint', $data['category_field'] );
	}

	// Question IDs
	if ( isset( $data['question_ids'] ) && is_array( $data['question_ids'] ) ) {
		$sanitized['question_ids'] = array_map( 'absint', $data['question_ids'] );
	}

	// Number of questions
	if ( isset( $data['question_count'] ) ) {
		$sanitized['question_count'] = absint( $data['question_count'] );
	}

	// Boolean fields
	$bool_fields = array( 'is_timed', 'show_answer_immediately', 'unused_questions_only' );
	foreach ( $bool_fields as $field ) {
		if ( isset( $data[ $field ] ) ) {
			$sanitized[ $field ] = '1' === $data[ $field ] || 1 === $data[ $field ] ? '1' : '0';
		}
	}

	// Role
	if ( isset( $data['role'] ) ) {
		$sanitized['role'] = sanitize_key( $data['role'] );
	}

	// User ID
	if ( isset( $data['user_id'] ) ) {
		$sanitized['user_id'] = absint( $data['user_id'] );
	}

	return $sanitized;
}

/**
 * Sanitize question data
 *
 * @since 1.0.0
 * @param array $data Question data.
 * @return array Sanitized data.
 */
function wpexams_sanitize_question_data( $data ) {
	$sanitized = array();

	// Question title
	if ( isset( $data['title'] ) ) {
		$sanitized['title'] = sanitize_text_field( $data['title'] );
	}

	// Question options
	if ( isset( $data['options'] ) && is_array( $data['options'] ) ) {
		$sanitized['options'] = array_map( 'sanitize_text_field', $data['options'] );
	}

	// Correct option
	if ( isset( $data['correct_option'] ) ) {
		$sanitized['correct_option'] = sanitize_key( $data['correct_option'] );
	}

	// Description/explanation
	if ( isset( $data['description'] ) ) {
		$sanitized['description'] = wp_kses_post( $data['description'] );
	}

	return $sanitized;
}

/**
 * Get unused questions for user
 *
 * @since 1.0.0
 * @param array $category_ids Category IDs to filter.
 * @param int   $user_id      User ID. Default current user.
 * @return array Array of unused question IDs.
 */
function wpexams_get_unused_questions( $category_ids, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Get all questions in categories
	$all_questions = get_posts(
		array(
			'post_type'      => 'wpexams_question',
			'category'       => $category_ids,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'publish',
		)
	);

	// Get user's used questions
	$user_exams = get_posts(
		array(
			'post_type'      => 'wpexams_exam',
			'author'         => $user_id,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		)
	);

	$used_questions = array();
	foreach ( $user_exams as $exam ) {
		$exam_data = wpexams_get_post_data( $exam->ID );

		if ( ! empty( $exam_data->exam_result['used_questions'] ) ) {
			$used_questions = array_merge( $used_questions, $exam_data->exam_result['used_questions'] );
		}
	}

	// Return unused questions
	$unused = array_diff( $all_questions, array_unique( $used_questions ) );

	/**
	 * Filter unused questions
	 *
	 * @since 1.0.0
	 * @param array $unused       Unused question IDs.
	 * @param array $category_ids Category IDs.
	 * @param int   $user_id      User ID.
	 */
	return apply_filters( 'wpexams_unused_questions', $unused, $category_ids, $user_id );
}

/**
 * Convert seconds to HH:MM:SS format
 *
 * @since 1.0.0
 * @param int $seconds Total seconds.
 * @return string Time in HH:MM:SS format.
 */
function wpexams_seconds_to_time( $seconds ) {
	$hours   = floor( $seconds / 3600 );
	$minutes = floor( ( $seconds % 3600 ) / 60 );
	$secs    = $seconds % 60;

	return sprintf( '%02d:%02d:%02d', $hours, $minutes, $secs );
}

/**
 * Convert HH:MM:SS to seconds
 *
 * @since 1.0.0
 * @param string $time Time in HH:MM:SS format.
 * @return int Total seconds.
 */
function wpexams_time_to_seconds( $time ) {
	$parts = explode( ':', $time );

	if ( 3 !== count( $parts ) ) {
		return 0;
	}

	return ( (int) $parts[0] * 3600 ) + ( (int) $parts[1] * 60 ) + (int) $parts[2];
}

/**
 * Log debug message
 *
 * @since 1.0.0
 * @param mixed $message Message to log.
 */
function wpexams_log( $message ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true );
		}
		error_log( '[WP Exams] ' . $message );
	}
}