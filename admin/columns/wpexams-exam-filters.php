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

	$current_status = isset( $_GET['wpexams_status_filter'] ) ? sanitize_key( $_GET['wpexams_status_filter'] ) : '';

	?>
	<select id="wpexams_status_filter" name="wpexams_status_filter">
		<option value=""><?php esc_html_e( 'Select Status Type', 'wpexams' ); ?></option>
		<option value="Useless" <?php selected( $current_status, 'Useless' ); ?>>
			<?php esc_html_e( 'Useless', 'wpexams' ); ?>
		</option>
		<option value="Completed" <?php selected( $current_status, 'Completed' ); ?>>
			<?php esc_html_e( 'Completed', 'wpexams' ); ?>
		</option>
		<option value="Pending" <?php selected( $current_status, 'Pending' ); ?>>
			<?php esc_html_e( 'Pending', 'wpexams' ); ?>
		</option>
		<option value="Predefined" <?php selected( $current_status, 'Predefined' ); ?>>
			<?php esc_html_e( 'Predefined', 'wpexams' ); ?>
		</option>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'wpexams_exam_custom_filters' );

/**
 * Filter exams by status
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

	if ( ! empty( $_GET['wpexams_status_filter'] ) ) {
		$status = sanitize_key( $_GET['wpexams_status_filter'] );

		$query->set(
			'meta_query',
			array(
				array(
					'key'     => 'wpexams_exam_status',
					'compare' => '=',
					'value'   => $status,
				),
			)
		);
	}
}
add_filter( 'parse_query', 'wpexams_exam_filter_queries' );