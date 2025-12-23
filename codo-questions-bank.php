<?php 
/**
 * Plugin Name:       WP Exams
 * Plugin URI:        http://codoplex.com/
 * Description:       Allow the subscribers to take online exams from the wp exams.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Codoplex
 * Author URI:        http://codoplex.com/
 * License:           GPL v2 or later
 * License URI:       http://codoplex.com/
 */


if(!defined( 'ABSPATH' )) {
     die('Invalid request.') ;
}

global $questionsBank_plugin_name;
$questionsBank_plugin_name = 'codo-questions-bank';
    
// defined constants
define('QUESTIONS_BANK_PLUGIN_FILE' , __FILE__) ;
define('QUESTIONS_BANK_PLUGIN_PATH' , plugin_dir_path( __FILE__)) ;
define('QUESTIONS_BANK_PLUGIN_URL' , plugin_dir_url( __FILE__)) ;



add_action( 'init', 'qb_enqueue_custom_main_style' ); 

function qb_enqueue_custom_main_style() {
     wp_enqueue_style('qb_custom_main_style' , plugin_dir_url( __FILE__ ).'assets/css/main.css' ) ;
}


/* Add Color Script */

add_action( 'admin_enqueue_scripts' , 'qb_admin_enqueue_script' ) ;

function qb_admin_enqueue_script () {

     wp_enqueue_script('qb_repeatable_option',
          plugin_dir_url(__FILE__).'assets/js/qb-repeatable-questions-option.js',
          array('jquery') 
     );

     wp_enqueue_script('qb_handel_exam_post',
          plugin_dir_url(__FILE__).'assets/js/plugin_pages/includes/custom_posts/exam.js',
          array('jquery') 
     );

     wp_enqueue_style( 'wp-color-picker' );
     wp_enqueue_script( 'qb-color-picker-script-handle', plugins_url('assets/js/qb-color-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

/* Register Settings */ 

function qb_general_options_validate_ ($input) {

     $qb_general_options = get_option('qb_general_options'); 

     $default_question_options = sanitize_text_field( $input['qb_default_question_options_option'] ) ;
     $qb_general_options['qb_default_question_options_option'] = $default_question_options ;

     $profile_username_option = sanitize_text_field( $input['qb_profile_username_option'] ) ;
     $qb_general_options['qb_profile_username_option'] = $profile_username_option ;

     $qb_progressbar_option = sanitize_text_field( $input['qb_progressbar_option'] ) ;
     $qb_general_options['qb_progressbar_option'] = $qb_progressbar_option ;

     $qb_set_time_option = sanitize_text_field( $input['qb_set_time_option'] ) ;
     $qb_general_options['qb_set_time_option'] = $qb_set_time_option ;

     return $qb_general_options ;

}

function qb_colors_options_validate_ ($input) {

     $qb_colors_options = get_option( 'qb_colors_options' ) ;
     // Buttons Backgorund color
     $qb_button_background_color_option = sanitize_text_field( $input['qb_button_background_color_option'] ) ;
     $qb_colors_options['qb_button_background_color_option'] = $qb_button_background_color_option ;

     // Buttons Text Color
     $qb_button_text_color_option = sanitize_text_field( $input['qb_button_text_color_option'] ) ;
     $qb_colors_options['qb_button_text_color_option'] = $qb_button_text_color_option ;

     // Progressbar Backgorund color
     $qb_progressbar_background_color_option = sanitize_text_field( $input['qb_progressbar_background_color_option'] ) ;
     $qb_colors_options['qb_progressbar_background_color_option'] = $qb_progressbar_background_color_option ;

     // Progressbar Text Color
     $qb_progressbar_text_color_option = sanitize_text_field( $input['qb_progressbar_text_color_option'] ) ;
     $qb_colors_options['qb_progressbar_text_color_option'] = $qb_progressbar_text_color_option ;
     
     return $qb_colors_options ;

}

function register_qb_settings() { 

     register_setting( 'qb_general_settings', 'qb_general_options' , 'qb_general_options_validate_') ;
     register_setting( 'qb_colors_settings', 'qb_colors_options' , 'qb_colors_options_validate_') ;

}

add_action( 'admin_init' , 'register_qb_settings' ) ;


/* All Menus File */
require_once( plugin_dir_path( __FILE__ ) . 'plugin_menus/menus.php');

/* Custom Posts */
require_once( plugin_dir_path( __FILE__ ) . 'plugin_pages/includes/custom_posts/questions/questions.php');
require_once( plugin_dir_path( __FILE__ ) . 'plugin_pages/includes/custom_posts/exams/exams.php');

/* Custom Shortcodes */
require_once( plugin_dir_path( __FILE__ ) . 'plugin_pages/includes/custom_shortcodes/home.php');

/* Get post metas */
require_once( plugin_dir_path( __FILE__ ) . 'plugin_pages/get_post_metas.php');
