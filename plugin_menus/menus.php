<?php 
if(!defined( 'ABSPATH' )) {
    
    die('Invalid request.') ;
    
}
    
add_action( 'admin_menu' , 'wp_exams_menus' ) ;

    /* All Menus */ 
    
function wp_exams_menus() {
    
    add_menu_page( 'WP Exams', 'WP Exams', 'edit_posts', 'wp_exams' , 'handel_wp_exams_dashboard_setup' , 'dashicons-edit-page' , 30) ;
    
    add_submenu_page( 'wp_exams', 'WP Exams', 'Dashboard', 'edit_posts', 'wp_exams',  'handel_wp_exams_dashboard_setup' );
    
    add_submenu_page( 'wp_exams', 'Add Questions' , 'Questions', 'edit_posts', 'edit.php?post_type=qb_questions') ;
    
    add_submenu_page( 'wp_exams' , 'Add Exams', 'Exams', 'edit_posts', 'edit.php?post_type=qb_exams' ) ;
    
}

// Dashboard HTML 
function handel_wp_exams_dashboard_setup() {
    
    require_once(QUESTIONS_BANK_PLUGIN_PATH . 'plugin_pages/includes/admin/home.php');
    
}

/* Parent Menu Fix */
add_filter( 'parent_file', 'qb__parent_file_std' );
 
/**
 * Fix Parent Admin Menu Item
 */
function qb__parent_file_std( $parent_file ){
 
    /* Get current screen */
    global $current_screen, $self, $questionsBank_plugin_name;
 
    if ( in_array( $current_screen->base, array( 'post', 'edit' ) ) && 
        (
            'qb_questions' == $current_screen->post_type 
        ) 
    ) {
        $parent_file = $questionsBank_plugin_name;
    }
   
    return $parent_file;
}

add_filter( 'submenu_file', 'qb__submenu_file_std' );
 
/**
 * Fix Sub Menu Item Highlights
 */
function qb__submenu_file_std( $submenu_file ){
 
    /* Get current screen */
    global $current_screen, $self;
 
    if ( in_array( $current_screen->base, array( 'post', 'edit' ) ) && 'qb_questions' == $current_screen->post_type ) {
        $submenu_file = 'edit.php?post_type=qb_questions';
    }
    
    // echo $submenu_file ;
    // exit ;
    return $submenu_file;
}

    
