<?php
/**
 * Exam custom columns
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom columns to exams list
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function wpexams_exam_custom_columns( $columns ) {
	$columns['wpexams_taken_by'] = __( 'Taken By', 'wpexams' );
	$columns['wpexams_status']   = __( 'Status', 'wpexams' );
	return $columns;
}
add_filter( 'manage_wpexams_exam_posts_columns', 'wpexams_exam_custom_columns' );

/**
 * Display custom column content
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function wpexams_exam_column_content( $column, $post_id ) {
	if ( 'wpexams_taken_by' === $column ) {
		$author_id = get_post_field( 'post_author', $post_id );
		echo esc_html( get_the_author_meta( 'display_name', $author_id ) );
	}

	if ( 'wpexams_status' === $column ) {
		// Get post data
		$exam_data = wpexams_get_post_data( $post_id );
		$result    = $exam_data->exam_result;
		$detail    = $exam_data->exam_detail;

		$status = 'pending';

		if ( $result && isset( $result['exam_status'] ) ) {
			$status = $result['exam_status'];
			
			// Update status meta for filtering
			update_post_meta( $post_id, 'wpexams_exam_status', ucfirst( $status ) );
		} else {
			if ( is_array( $detail ) ) {
				if ( isset( $detail['role'] ) && 'user_defined' === $detail['role'] ) {
					$status = 'useless';
				} else {
					$status = 'predefined';
				}
				update_post_meta( $post_id, 'wpexams_exam_status', ucfirst( $status ) );
			} else {
				update_post_meta( $post_id, 'wpexams_exam_status', 'Useless' );
			}
		}

		// Display status with color coding
		$status_class = 'wpexams-status-' . esc_attr( $status );
		printf(
			'<span class="%s">%s</span>',
			esc_attr( $status_class ),
			esc_html( ucfirst( $status ) )
		);
	}
}
add_action( 'manage_wpexams_exam_posts_custom_column', 'wpexams_exam_column_content', 10, 2 );

/**
 * Make custom columns sortable
 *
 * @param array $columns Sortable columns.
 * @return array Modified columns.
 */
function wpexams_exam_sortable_columns( $columns ) {
	$columns['wpexams_status'] = 'wpexams_status';
	return $columns;
}
add_filter( 'manage_edit-wpexams_exam_sortable_columns', 'wpexams_exam_sortable_columns' );

/**
 * Handle sorting by custom columns
 *
 * @param WP_Query $query Query object.
 */
function wpexams_exam_column_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( 'wpexams_status' === $orderby ) {
		$query->set( 'meta_key', 'wpexams_exam_status' );
		$query->set( 'orderby', 'meta_value' );
	}
}
add_action( 'pre_get_posts', 'wpexams_exam_column_orderby' );