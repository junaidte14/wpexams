<?php 

function qb_frontend_ajax_script() {

    wp_enqueue_style('mcqs-enqueue-style' , QUESTIONS_BANK_PLUGIN_URL.'/assets/css/qb-short-code-style.css' ) ;
    wp_enqueue_script( 'qb-main-script', QUESTIONS_BANK_PLUGIN_URL.'/assets/js/qb_main_script.js' , array( 'jquery' ) );
    // register your script location, dependencies and version

    wp_register_script('qb_save_exam_detail',
    QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_save_exam_detail.js' ,
    array('jquery') );
    wp_register_script('qb_reset_question_bank',
    QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_reset_question_bank.js' ,
    array('jquery') );
    wp_register_script('qb_show_imme',
    QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_show_imme.js' ,
    array('jquery') );
    wp_register_script('qb_next_previous_q',
    QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_next_previous_q.js' ,
    array('jquery') );
    wp_register_script('qb_exam_review',
    QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_exam_review.js' ,
    array('jquery') );
    wp_register_script('qb_expired_exam',
    QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_expired_exam.js' ,
    array('jquery') );
    
    // enqueue the script
    wp_localize_script( 'qb_save_exam_detail', 'qb_ajax_url', array('ajax_url' => admin_url( 'admin-ajax.php' )) ) ;
    wp_enqueue_script('qb_save_exam_detail');
    // enqueue the script
    wp_localize_script( 'qb_reset_question_bank', 'qb_ajax_url', array('ajax_url' => admin_url( 'admin-ajax.php' )) ) ;
    wp_enqueue_script('qb_reset_question_bank');
    // enqueue the script
    wp_localize_script( 'qb_show_imme', 'qb_ajax_url', array('ajax_url' => admin_url( 'admin-ajax.php' )) ) ;
    wp_enqueue_script('qb_show_imme');
    // enqueue the script
    wp_localize_script( 'qb_next_previous_q', 'qb_ajax_url', array('ajax_url' => admin_url( 'admin-ajax.php' )) ) ;
    wp_enqueue_script('qb_next_previous_q');
    // enqueue the script
    wp_localize_script( 'qb_exam_review', 'qb_ex_ajax_url', array('ajax_url' => admin_url( 'admin-ajax.php' )) ) ;
    wp_enqueue_script('qb_exam_review');
    // enqueue the script
    wp_localize_script( 'qb_expired_exam', 'qb_ajax_url', array('ajax_url' => admin_url( 'admin-ajax.php' )) ) ;
    wp_enqueue_script('qb_expired_exam');
    // enqueue the script
    wp_enqueue_script( 'qb-get-percentage', QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_get_percentage.js' , array( 'jquery' ) );
    wp_enqueue_script( 'qb-countdown-timer', QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_countdown_timer.js' , array( 'jquery' ) );
    wp_enqueue_script( 'qb-timer-actions', QUESTIONS_BANK_PLUGIN_URL.'/assets/js/plugin_pages/includes/custom_shortcodes/qb_timer_actions.js' , array( 'jquery' ) );

}

add_action( 'wp_enqueue_scripts', 'qb_frontend_ajax_script' );

function questions_bank_home_page() {

    $current_user_id = get_current_user_id(); // if user not logged in this will return 0

    ob_start();

    if($current_user_id != 0){ 

        $profile_pic = get_avatar_url( $current_user_id);

        // General settings
        $qb_general_options = get_option('qb_general_options');

        if($qb_general_options) {
            // GET TIME
            if(array_key_exists('qb_set_time_option', $qb_general_options)){
                $get_time_from_setting = intval($qb_general_options['qb_set_time_option']) ;
            } else {
                $get_time_from_setting = 82 ;
            }
        } else {
            $get_time_from_setting = 82 ;
        }

        // Color settings 
        $qb_colors_options = get_option('qb_colors_options');

        if($qb_colors_options) {

            if(array_key_exists('qb_button_background_color_option', $qb_colors_options)){
                $button_background_color = $qb_colors_options['qb_button_background_color_option'];
            }else{
                $button_background_color = '#000000';
            }

            if(array_key_exists('qb_button_text_color_option', $qb_colors_options)){
                $button_text_color = $qb_colors_options['qb_button_text_color_option'];
            }else{
                $button_text_color = '#ffffff';
            }

            if(array_key_exists('qb_progressbar_background_color_option', $qb_colors_options)){
                $progressbar_background_color = $qb_colors_options['qb_progressbar_background_color_option'];
            }else{
                $progressbar_background_color = '#000000';
            }

            if(array_key_exists('qb_progressbar_text_color_option', $qb_colors_options)){
                $progressbar_text_color = $qb_colors_options['qb_progressbar_text_color_option'];
            }else{
                $progressbar_text_color = '#ffffff';
            }

        } else {
            $button_background_color = '#000000';
            $button_text_color  = '#ffffff';
            $progressbar_background_color = '#000000';
            $progressbar_text_color  = '#ffffff';
        }

        ?> 
        
       <style type="text/css"> 
            .button_background_color    { background-color: <?php echo $button_background_color; ?>; }
            .button_text_color    { color: <?php echo $button_text_color; ?>; }
            .progressbar_background_color    { background-color: <?php echo $progressbar_background_color; ?>; }
            .progressbar_text_color    { color: <?php echo $progressbar_text_color; ?>; }
        </style>
        
        <?php

        if($qb_general_options){
            if(array_key_exists('qb_profile_username_option' , $qb_general_options)) {
                if($qb_general_options['qb_profile_username_option'] == "1") {
                    ?> 
                    <div class='qb_h_header'>
                        <div class='qb_header_lft'>
                            <img src="<?php echo $profile_pic;?>" alt="Profile Pic">
                            <?php 
                            $userdata = get_userdata( $current_user_id ) ;
                            if($userdata) {
                                ?> <p><?php echo $userdata->user_login ; ?></p> <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
            }
        } else {
            ?> 
            <div class='qb_h_header'>
                <div class='qb_header_lft' style="text-align: center">
                    <img src="<?php echo $profile_pic;?>" alt="Profile Pic">
                    <?php 
                    $userdata = get_userdata( $current_user_id ) ;
                    if($userdata) {
                        ?> <p><?php echo $userdata->user_login ; ?></p> <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?> 
        
        <div class='qb_head_menus_links'>
            <a class="menu-link" href="<?php echo get_permalink() ; ?>">New Exam</a>
            <a class="menu-link" href="?qb_exam_history">Exam History</a>
            <button title="Clear questions usage history. So that the already used questions can again be used in a new exam." type='button' class='button_background_color button_text_color qb_reset_qustion_bank' id='qb_reset_qustion_bank'>Reset Question Bank</button>
        </div>

        <div class='qb_main'>
            <div>
                <span id='qb_reset_qustion_bank_message'></span>
            </div>
        <?php

        if(isset($_GET['qb_subscriber_exam_ID'])) {

            include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/exam.php');      

        } else {
            
            ?> 
            
            <div class='qb_content'>

            <?php 
                // EXAM REVIEW
                if(isset($_GET['qb_exam_review_ID'])) { 

                    include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/exam_review.php');

                    // EXAM HISTORY
                } else if(isset($_GET['qb_exam_history'])) {
    
                    include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/exam_history.php');
    
                } else {

                    if( isset($_GET['qb_new_exam']) ) {

                        // NEW EXAM DETAIL 
    
                        include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/exam_detail.php');

                    }else if( isset($_GET['qb_predefined_exam']) ) {
                        // PREDEFINED EXAM DETAIL
                        include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/exam_predefined_detail.php');

                    } else {

                        // Predefined Exams 
                        include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/common/get_all_admins.php');
                        // GET ALL ADMINS
                        $qb_AdminIdArray = qb_admin_user_ids();

                        $allExams = get_posts(array(
                            'post_type' => 'qb_exams' ,
                            'author__in' => $qb_AdminIdArray ,
                            'posts_per_page' => '-1' ,
                            'post_status' => array('publish', 'Predefined') ,
                            'orderby' => 'ID',
                            'order' => 'DESC'
                        ));

                        ?> 
                        
                        <div style="display: flex;align-items: center;">
                            <p style="margin:0px;flex-grow:1;">Explore our collection of predefined exams and test your knowledge!</p>
                            <a href="?qb_new_exam" class='anchor_link_btn button_background_color button_text_color' >Create Your Own Exam</a>
                        </div>
                        <table class='qb_data_tabels' style="margin:20px 0!important;">
                            <thead>
                                <tr>
                                <th>Title</th>
                                <th>Questions</th>
                                <th>Timed</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                            if (!empty($allExams)) {
                                $hasExam = false;
                                foreach ($allExams as $exam) {
                                    
                                    // GET POST META => get_post_metas.php
                                    $qb_get_post_meta = new QB_Get_Post_Meta($exam->ID);
                                    $qb_detail = $qb_get_post_meta->qb_detail;

                                    if ($qb_detail && array_key_exists('qb_role', $qb_detail) && $qb_detail['qb_role'] == 'admin_defined') {
                                        $hasExam = true; 
                                        ?>   
                                        <tr> 
                                            <td style="text-align: center !important;" data-label="Title"><?php echo empty($exam->post_title) ? "No Title" : $exam->post_title; ?></td>
                                            <td style="text-align: center !important;" data-label="Questions"><?php echo array_key_exists("qb_q_IDS", $qb_detail) ? count($qb_detail['qb_q_IDS']) : "0"; ?></td>
                                            <td style="text-align: center !important;" data-label="Timed"><?php echo isset($qb_detail['qb_timed_field']) && $qb_detail['qb_timed_field'] == '1' ? "✔" : "✖"; ?></td>
                                            <td style="text-align: center !important;" data-label="Action"><a href="?qb_subscriber_exam_ID=<?php echo $exam->ID ?>">Take Exam</a></td>
                                        </tr>
                                        <?php
                                    } 
                                }

                                // If no exams match the condition, show a message
                                if (!$hasExam) {
                                    echo "<tr><td colspan='4' style='text-align: center;'>No predefined available</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align: center;'>No predefined available</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>

                        <!-- <div class='qb_d_flex qb_select_type_of_exam'>
                            <a href="?qb_predefined_exam" class='button_background_color button_text_color' >Predefined Exam</a>
                        </div> -->
                        <?php
 
                    }

                    
                }        
                
                ?>
                
            </div>

            <?php
        }
        ?>

    </div>

    <?php 
        
     } else{
        ?> 
        
        <p><?php _e('You need to login to take exams.', 'qb'); ?></p>
        <a class='button' href="<?php echo wp_login_url() ?>">Login</a>
        
        <?php
	}
    return ob_get_clean();

}

add_shortcode( 'qb_home_page', 'questions_bank_home_page' );

/**
 * COMMON FUNCTIONS 
 */

 include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/common/unset_question.php');
 include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/common/add_correct_wrong_q.php');
 include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/common/update_q_time.php');
 include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/common/add_default_correct_wrong_q.php');
 include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/common/unused_questions.php');

/**
* FUNCTIONS
*/

// SAVE SUBSCRIBER EXAM DETAIL

include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/qb_save_exam_detail.php');

// SHOW ANSWER IMMEDIATELY

include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/qb_show_immediately.php');

// NEXT AND PREVIOUS QUESTION

include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/qb_next_previous_question.php');

// RESET QUESTION BANK HISTORY

include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/qb_reset_question_bank.php');

// EXPIRED EXAM 

include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/qb_expired_exam.php');

// REVIEW EXAM
include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/functions/qb_review_exam.php');
