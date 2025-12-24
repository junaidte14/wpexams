<?php
/**
 * Uninstall WP Exams
 *
 * Deletes all plugin data from the database when the plugin is uninstalled.
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly or not uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all plugin data
/*$exams = get_posts(
	array(
		'post_type'      => 'wpexams_exam',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);

foreach ( $exams as $exam ) {
	wp_delete_post( $exam->ID, true );
}

$questions = get_posts(
	array(
		'post_type'      => 'wpexams_question',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);

foreach ( $questions as $question ) {
	wp_delete_post( $question->ID, true );
}

delete_option( 'wpexams_general_settings' );
delete_option( 'wpexams_color_settings' );
delete_option( 'wpexams_version' );
delete_option( 'wpexams_activated_time' );
delete_option( 'wpexams_migrated_v2' );
delete_option( 'wpexams_migration_date' );
*/
wp_cache_flush();