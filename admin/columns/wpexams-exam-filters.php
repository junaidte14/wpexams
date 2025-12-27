<?php
/**
 * Exam custom filters
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom filter dropdown to exams list
 */
function wpexams_exam_custom_filters() {
	global $post_type;

	if ( 'wpexams_exam' !== $post_type ) {
		return;
	}

	$current_type = isset( $_GET['wpexams_type_filter'] ) ? sanitize_key( $_GET['wpexams_type_filter'] ) : '';

	?>
	<select id="wpexams_type_filter" name="wpexams_type_filter">
		<option value=""><?php esc_html_e( 'Select Exam Type', 'wpexams' ); ?></option>
		<option value="user_defined" <?php selected( $current_type, 'user_defined' ); ?>>
			<?php esc_html_e( 'User Defined', 'wpexams' ); ?>
		</option>
		<option value="admin_defined" <?php selected( $current_type, 'admin_defined' ); ?>>
			<?php esc_html_e( 'Predefined', 'wpexams' ); ?>
		</option>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'wpexams_exam_custom_filters' );

/**
 * Filter exams by status or type
 *
 * @param WP_Query $query Query object.
 */
function wpexams_exam_filter_queries( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! isset( $query->query['post_type'] ) || 'wpexams_exam' !== $query->query['post_type'] ) {
		return;
	}

	$meta_query = array();

	// Type filter (new)
	if ( ! empty( $_GET['wpexams_type_filter'] ) ) {
		$type = sanitize_key( $_GET['wpexams_type_filter'] );

		$meta_query[] = array(
			'key'     => 'wpexams_exam_detail',
			'value'   => '"role";s:' . strlen( $type ) . ':"' . $type . '"',
			'compare' => 'LIKE',
		);
	}

	if ( ! empty( $meta_query ) ) {
		$meta_query['relation'] = 'AND';
		$query->set( 'meta_query', $meta_query );
	}
}
add_filter( 'parse_query', 'wpexams_exam_filter_queries' );