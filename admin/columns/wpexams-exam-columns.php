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
	$columns['wpexams_type'] = __( 'Type', 'wpexams' );
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
	if ( 'wpexams_type' === $column ) {
		$exam_data = wpexams_get_post_data( $post_id );
		$detail = $exam_data->exam_detail;
		
		if ( $detail && isset( $detail['role'] ) ) {
			$role = $detail['role'];
			$label = 'admin_defined' === $role ? __( 'Predefined', 'wpexams' ) : __( 'User Defined', 'wpexams' );
			$color = 'admin_defined' === $role ? '#0073aa' : '#666';
			
			printf(
				'<span style="color: %s; font-weight: 600;">%s</span>',
				esc_attr( $color ),
				esc_html( $label )
			);
		}
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