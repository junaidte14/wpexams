<?php
/**
 * Admin AJAX handlers
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX: Search questions for exam creation
 */
function wpexams_ajax_search_questions() {
	// Check nonce
	check_ajax_referer( 'wpexams_admin_nonce', 'nonce' );

	// Check capability
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'wpexams' ) );
	}

	// Get search keyword
	$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';

	// Query arguments
	$args = array(
		'post_type'      => 'wpexams_question',
		'post_status'    => 'publish',
		'orderby'        => 'ID',
		'order'          => 'DESC',
	);

	// Add search if keyword provided
	if ( ! empty( $keyword ) ) {
		$args['s']              = $keyword;
		$args['posts_per_page'] = 5;
	}

	// Execute query
	$query = new WP_Query( $args );

	// Output results
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			?>
			<div class='wpexams-question-options-div' data-id="<?php the_ID(); ?>">
				<span><?php echo esc_html( get_the_title() ); ?></span>
				<input type="radio" name='wpexams_question_options' 
					   class='wpexams-question-options' 
					   value='<?php the_ID(); ?>'>
			</div>
			<?php
		}
		wp_reset_postdata();
	} else {
		echo '<h3>' . esc_html__( 'No Results Found', 'wpexams' ) . '</h3>';
	}

	wp_die();
}
add_action( 'wp_ajax_wpexams_search_questions', 'wpexams_ajax_search_questions' );