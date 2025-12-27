<?php
/**
 * Register admin menu
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register WP Exams admin menu
 */
function wpexams_register_admin_menu() {
	add_menu_page(
		__( 'WP Exams', 'wpexams' ),
		__( 'WP Exams', 'wpexams' ),
		'edit_posts',
		'wpexams',
		'wpexams_dashboard_page',
		'dashicons-edit-page',
		30
	);

	add_submenu_page(
		'wpexams',
		__( 'Dashboard', 'wpexams' ),
		__( 'Dashboard', 'wpexams' ),
		'edit_posts',
		'wpexams',
		'wpexams_dashboard_page'
	);

	add_submenu_page(
		'wpexams',
		__( 'Questions', 'wpexams' ),
		__( 'Questions', 'wpexams' ),
		'edit_posts',
		'edit.php?post_type=wpexams_question'
	);

	add_submenu_page(
		'wpexams',
		__( 'Exams', 'wpexams' ),
		__( 'Exams', 'wpexams' ),
		'edit_posts',
		'edit.php?post_type=wpexams_exam'
	);

	add_submenu_page(
		'wpexams',
		__( 'Results', 'wpexams' ),
		__( 'Results', 'wpexams' ),
		'edit_posts',
		'edit.php?post_type=wpexams_result'
	);
}
add_action( 'admin_menu', 'wpexams_register_admin_menu' );

/**
 * Fix parent menu highlighting for questions and results
 *
 * @param string $parent_file Parent file.
 * @return string Modified parent file.
 */
function wpexams_fix_parent_menu( $parent_file ) {
	global $current_screen;

	if ( ! $current_screen ) {
		return $parent_file;
	}

	if ( in_array( $current_screen->base, array( 'post', 'edit' ), true ) && 
	     in_array( $current_screen->post_type, array( 'wpexams_question', 'wpexams_result' ), true ) ) {
		$parent_file = 'wpexams';
	}

	return $parent_file;
}
add_filter( 'parent_file', 'wpexams_fix_parent_menu' );

/**
 * Fix submenu highlighting for questions and results
 *
 * @param string $submenu_file Submenu file.
 * @return string Modified submenu file.
 */
function wpexams_fix_submenu_file( $submenu_file ) {
	global $current_screen;

	if ( ! $current_screen ) {
		return $submenu_file;
	}

	if ( in_array( $current_screen->base, array( 'post', 'edit' ), true ) ) {
		if ( 'wpexams_question' === $current_screen->post_type ) {
			$submenu_file = 'edit.php?post_type=wpexams_question';
		} elseif ( 'wpexams_result' === $current_screen->post_type ) {
			$submenu_file = 'edit.php?post_type=wpexams_result';
		}
	}

	return $submenu_file;
}