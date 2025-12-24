<?php
/**
 * WPExams Migration Functions
 * Handles migration from old QB plugin to WPExams
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if migration is needed
 *
 * @return bool
 */
function wpexams_needs_migration() {
    global $wpdb;
    
    // Check if migration has already been completed
    if (get_option('wpexams_migration_completed')) {
        return false;
    }
    
    // Check if there's any old QB data to migrate
    $old_posts = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} 
         WHERE post_type IN ('qb_questions', 'qb_exams')"
    );
    
    $old_options = get_option('qb_general_options') || get_option('qb_colors_options');
    
    return ($old_posts > 0 || $old_options);
}

/**
 * Get migration statistics
 *
 * @return array
 */
function wpexams_get_migration_stats() {
    global $wpdb;
    
    $stats = array(
        'questions' => 0,
        'exams' => 0,
        'results' => 0,
        'settings' => 0
    );
    
    // Count old questions
    $stats['questions'] = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'qb_questions'"
    );
    
    // Count old exams
    $stats['exams'] = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'qb_exams'"
    );
    
    // Count old results (posts with result meta)
    $stats['results'] = (int) $wpdb->get_var(
        "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
         WHERE meta_key = 'qb_subscriber_exam_result_meta_key'"
    );
    
    // Check if old settings exist
    if (get_option('qb_general_options') || get_option('qb_colors_options')) {
        $stats['settings'] = 1;
    }
    
    return $stats;
}

/**
 * Run database migration
 *
 * @return array Migration result
 */
function wpexams_migrate_database() {
    global $wpdb;
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        $results = array(
            'questions' => 0,
            'exams' => 0,
            'meta_keys' => 0,
            'settings' => 0,
            'errors' => array()
        );
        
        // 1. Migrate Questions
        $questions_updated = $wpdb->query(
            "UPDATE {$wpdb->posts} 
             SET post_type = 'wpexams_question' 
             WHERE post_type = 'qb_questions'"
        );
        $results['questions'] = $questions_updated ? $questions_updated : 0;
        
        // 2. Migrate Exams
        $exams_updated = $wpdb->query(
            "UPDATE {$wpdb->posts} 
             SET post_type = 'wpexams_exam' 
             WHERE post_type = 'qb_exams'"
        );
        $results['exams'] = $exams_updated ? $exams_updated : 0;
        
        // 3. Migrate Results
        $results_updated = $wpdb->query(
            "UPDATE {$wpdb->posts} 
             SET post_type = 'wpexams_result' 
             WHERE post_type = 'qb_results'"
        );
        
        // 4. Migrate Meta Keys
        $meta_key_mappings = array(
            'qb_question_fields_meta_key' => '_wpexams_question_fields',
            'qb_subscriber_exam_result_meta_key' => '_wpexams_exam_result',
            'qb_subscriber_exam_detail_meta_key' => '_wpexams_exam_detail',
            'qb__status_colmun' => '_wpexams_exam_status',
            'qb_question_type' => '_wpexams_question_type',
            'qb_options' => '_wpexams_options',
            'qb_correct_answer' => '_wpexams_correct_answer',
            'qb_explanation' => '_wpexams_explanation',
            'qb_exam_id' => '_wpexams_exam_id',
            'qb_time_limit' => '_wpexams_time_limit',
            'qb_passing_score' => '_wpexams_passing_score',
            'qb_show_answers' => '_wpexams_show_answers',
            'qb_user_id' => '_wpexams_user_id',
            'qb_score' => '_wpexams_score',
            'qb_total_questions' => '_wpexams_total_questions',
            'qb_correct_answers' => '_wpexams_correct_answers',
            'qb_time_taken' => '_wpexams_time_taken',
            'qb_user_answers' => '_wpexams_user_answers'
        );
        
        $meta_updated = 0;
        foreach ($meta_key_mappings as $old_key => $new_key) {
            $updated = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->postmeta} 
                     SET meta_key = %s 
                     WHERE meta_key = %s",
                    $new_key,
                    $old_key
                )
            );
            if ($updated) {
                $meta_updated += $updated;
            }
        }
        $results['meta_keys'] = $meta_updated;
        
        // 5. Migrate General Settings
        $general = get_option('qb_general_options');
        if ($general) {
            $new_general = array(
                'default_question_options' => isset($general['qb_default_question_options_option']) ? $general['qb_default_question_options_option'] : 4,
                'show_profile_username' => isset($general['qb_profile_username_option']) ? $general['qb_profile_username_option'] : '1',
                'show_progressbar' => isset($general['qb_progressbar_option']) ? $general['qb_progressbar_option'] : '1',
                'question_time_seconds' => isset($general['qb_set_time_option']) ? $general['qb_set_time_option'] : 82,
            );
            update_option('wpexams_general_settings', $new_general);
            delete_option('qb_general_options');
            $results['settings']++;
        }
        
        // 6. Migrate Color Settings
        $colors = get_option('qb_colors_options');
        if ($colors) {
            $new_colors = array(
                'button_bg_color' => isset($colors['qb_button_background_color_option']) ? $colors['qb_button_background_color_option'] : '#000000',
                'button_text_color' => isset($colors['qb_button_text_color_option']) ? $colors['qb_button_text_color_option'] : '#ffffff',
                'progressbar_bg_color' => isset($colors['qb_progressbar_background_color_option']) ? $colors['qb_progressbar_background_color_option'] : '#000000',
                'progressbar_text_color' => isset($colors['qb_progressbar_text_color_option']) ? $colors['qb_progressbar_text_color_option'] : '#ffffff',
            );
            update_option('wpexams_color_settings', $new_colors);
            delete_option('qb_colors_options');
            $results['settings']++;
        }
        
        // Commit transaction
        $wpdb->query('COMMIT');
        
        // Mark migration as completed
        update_option('wpexams_migration_completed', true);
        update_option('wpexams_migration_date', current_time('mysql'));
        update_option('wpexams_migration_version', WPEXAMS_VERSION);
        
        // Log migration
        wpexams_log_migration($results);
        
        return array(
            'success' => true,
            'message' => 'Migration completed successfully!',
            'results' => $results
        );
        
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        
        return array(
            'success' => false,
            'message' => 'Migration failed: ' . $e->getMessage(),
            'results' => $results
        );
    }
}

/**
 * Log migration results
 *
 * @param array $results Migration results
 */
function wpexams_log_migration($results) {
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'version' => WPEXAMS_VERSION,
        'results' => $results,
        'user_id' => get_current_user_id()
    );
    
    $log = get_option('wpexams_migration_log', array());
    $log[] = $log_entry;
    update_option('wpexams_migration_log', $log);
}

/**
 * Reset migration (for testing purposes)
 */
function wpexams_reset_migration() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        delete_option('wpexams_migration_completed');
        delete_option('wpexams_migration_date');
        delete_option('wpexams_migration_version');
        return true;
    }
    return false;
}

/**
 * AJAX handler for running migration
 */
function wpexams_ajax_run_migration() {
    // Check nonce
    check_ajax_referer('wpexams_migration', 'nonce');
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'You do not have permission to run migrations.'
        ));
    }
    
    // Run migration
    $result = wpexams_migrate_database();
    
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_wpexams_run_migration', 'wpexams_ajax_run_migration');

/**
 * AJAX handler for getting migration stats
 */
function wpexams_ajax_get_migration_stats() {
    check_ajax_referer('wpexams_migration', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array(
            'message' => 'You do not have permission to view migration stats.'
        ));
    }
    
    $stats = wpexams_get_migration_stats();
    
    wp_send_json_success(array(
        'stats' => $stats,
        'needs_migration' => wpexams_needs_migration()
    ));
}
add_action('wp_ajax_wpexams_get_migration_stats', 'wpexams_ajax_get_migration_stats');