<?php
/**
 * Result custom columns
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom columns to results list
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function wpexams_result_custom_columns( $columns ) {
	$columns['wpexams_exam']      = __( 'Exam', 'wpexams' );
	$columns['wpexams_user']      = __( 'User', 'wpexams' );
    $columns['wpexams_type']      = __( 'Type', 'wpexams' );
	$columns['wpexams_score']     = __( 'Score', 'wpexams' );
	$columns['wpexams_status']    = __( 'Status', 'wpexams' );
	$columns['wpexams_questions'] = __( '#Questions', 'wpexams' );
	return $columns;
}
add_filter( 'manage_wpexams_result_posts_columns', 'wpexams_result_custom_columns' );

/**
 * Display custom column content
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function wpexams_result_column_content( $column, $post_id ) {
	if ( 'wpexams_exam' === $column ) {
		$exam_id = get_post_meta( $post_id, 'wpexams_exam_id', true );
		if ( $exam_id ) {
			$exam = get_post( $exam_id );
			if ( $exam ) {
				echo '<a href="' . esc_url( get_edit_post_link( $exam_id ) ) . '">' . esc_html( $exam->post_title ) . '</a>';
			} else {
				esc_html_e( 'N/A', 'wpexams' );
			}
		} else {
			esc_html_e( 'User Defined', 'wpexams' );
		}
	}

	if ( 'wpexams_user' === $column ) {
		$author_id = get_post_field( 'post_author', $post_id );
		$user = get_userdata( $author_id );
		if ( $user ) {
			echo '<a href="' . esc_url( get_edit_user_link( $author_id ) ) . '">' . esc_html( $user->display_name ) . '</a>';
		}
	}

	if ( 'wpexams_type' === $column ) {
		$result_data = wpexams_get_post_data( $post_id );
		$exam_detail = $result_data->exam_detail;
		
		if ( $exam_detail && isset( $exam_detail['role'] ) ) {
			$type = $exam_detail['role'];
			$label = 'admin_defined' === $type ? __( 'Predefined', 'wpexams' ) : __( 'User Defined', 'wpexams' );
			$color = 'admin_defined' === $type ? '#0073aa' : '#666';
			
			printf(
				'<span style="color: %s; font-weight: 600;">%s</span>',
				esc_attr( $color ),
				esc_html( $label )
			);
		} else {
			echo '—';
		}
	}

	if ( 'wpexams_score' === $column ) {
		$result_data = wpexams_get_post_data( $post_id );
		$result = $result_data->exam_result;
		
		if ( $result && isset( $result['exam_status'] ) && 'completed' === $result['exam_status'] ) {
			// Calculate correct answers
			$correct_count = 0;
			$correct_question_ids = array();
			
			if ( isset( $result['correct_answers'] ) && is_array( $result['correct_answers'] ) ) {
				foreach ( $result['correct_answers'] as $answer ) {
					if ( isset( $answer['question_id'] ) && isset( $answer['answer'] ) && 'null' !== $answer['answer'] ) {
						$correct_question_ids[] = (string) $answer['question_id'];
					}
				}
			}
			
			$correct_question_ids = array_unique( $correct_question_ids );
			$correct_count = count( $correct_question_ids );
			
			$total = isset( $result['total_questions'] ) ? $result['total_questions'] : 0;
			$percentage = $total > 0 ? round( ( $correct_count / $total ) * 100 ) : 0;
			
			printf(
				'<span style="font-weight: 600; color: %s;">%d/%d (%d%%)</span>',
				$percentage >= 50 ? '#4caf50' : '#f44336',
				$correct_count,
				$total,
				$percentage
			);
		} else {
			echo '—';
		}
	}

	if ( 'wpexams_status' === $column ) {
		$result_data = wpexams_get_post_data( $post_id );
		$result = $result_data->exam_result;
		
		if ( $result && isset( $result['exam_status'] ) ) {
			$status = $result['exam_status'];
			$status_class = 'completed' === $status ? 'wpexams-status-completed' : 'wpexams-status-pending';
			
			printf(
				'<span class="%s">%s</span>',
				esc_attr( $status_class ),
				esc_html( ucfirst( $status ) )
			);
		}
	}

	if ( 'wpexams_questions' === $column ) {
		$result_data = wpexams_get_post_data( $post_id );
		$result = $result_data->exam_result;
		
		if ( $result && isset( $result['total_questions'] ) ) {
			echo esc_html( $result['total_questions'] );
		} else {
			echo '—';
		}
	}
}
add_action( 'manage_wpexams_result_posts_custom_column', 'wpexams_result_column_content', 10, 2 );

/**
 * Make custom columns sortable
 *
 * @param array $columns Sortable columns.
 * @return array Modified columns.
 */
function wpexams_result_sortable_columns( $columns ) {
	$columns['wpexams_status'] = 'wpexams_status';
	return $columns;
}
add_filter( 'manage_edit-wpexams_result_sortable_columns', 'wpexams_result_sortable_columns' );

/**
 * Handle sorting by custom columns
 *
 * @param WP_Query $query Query object.
 */
function wpexams_result_column_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( 'wpexams_status' === $orderby ) {
		$query->set( 'meta_key', 'wpexams_exam_status' );
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'wpexams_result_column_orderby' );