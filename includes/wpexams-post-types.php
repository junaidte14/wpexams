<?php
/**
 * Register custom post types
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Questions post type
 */
function wpexams_register_question_post_type() {
	$labels = array(
		'name'               => _x( 'Questions', 'post type general name', 'wpexams' ),
		'singular_name'      => _x( 'Question', 'post type singular name', 'wpexams' ),
		'menu_name'          => _x( 'Questions', 'admin menu', 'wpexams' ),
		'add_new'            => _x( 'Add New', 'question', 'wpexams' ),
		'add_new_item'       => __( 'Add New Question', 'wpexams' ),
		'edit_item'          => __( 'Edit Question', 'wpexams' ),
		'new_item'           => __( 'New Question', 'wpexams' ),
		'view_item'          => __( 'View Question', 'wpexams' ),
		'search_items'       => __( 'Search Questions', 'wpexams' ),
		'not_found'          => __( 'No questions found', 'wpexams' ),
		'not_found_in_trash' => __( 'No questions found in Trash', 'wpexams' ),
		'parent_item_colon'  => __( 'Parent Question:', 'wpexams' ),
		'all_items'          => __( 'All Questions', 'wpexams' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => false, // Custom menu
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'exam-question' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'author' ),
		'taxonomies'         => array( 'category' ),
		'show_in_rest'       => false, // Disable Gutenberg
	);

	/**
	 * Filter question post type args
	 *
	 * @since 1.0.0
	 * @param array $args Post type registration arguments.
	 */
	$args = apply_filters( 'wpexams_question_post_type_args', $args );

	register_post_type( 'wpexams_question', $args );
}
add_action( 'init', 'wpexams_register_question_post_type' );

/**
 * Register Exams post type
 */
function wpexams_register_exam_post_type() {
	$labels = array(
		'name'               => _x( 'Exams', 'post type general name', 'wpexams' ),
		'singular_name'      => _x( 'Exam', 'post type singular name', 'wpexams' ),
		'menu_name'          => _x( 'Exams', 'admin menu', 'wpexams' ),
		'add_new'            => _x( 'Add New', 'exam', 'wpexams' ),
		'add_new_item'       => __( 'Add New Exam', 'wpexams' ),
		'edit_item'          => __( 'Edit Exam', 'wpexams' ),
		'new_item'           => __( 'New Exam', 'wpexams' ),
		'view_item'          => __( 'View Exam', 'wpexams' ),
		'search_items'       => __( 'Search Exams', 'wpexams' ),
		'not_found'          => __( 'No exams found', 'wpexams' ),
		'not_found_in_trash' => __( 'No exams found in Trash', 'wpexams' ),
		'parent_item_colon'  => __( 'Parent Exam:', 'wpexams' ),
		'all_items'          => __( 'All Exams', 'wpexams' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => false, // Custom menu
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'exam' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'author' ),
		'show_in_rest'       => false, // Disable Gutenberg
	);

	/**
	 * Filter exam post type args
	 *
	 * @since 1.0.0
	 * @param array $args Post type registration arguments.
	 */
	$args = apply_filters( 'wpexams_exam_post_type_args', $args );

	register_post_type( 'wpexams_exam', $args );
}
add_action( 'init', 'wpexams_register_exam_post_type' );