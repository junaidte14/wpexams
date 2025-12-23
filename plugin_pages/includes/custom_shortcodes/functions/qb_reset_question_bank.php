<?php 

add_action( 'wp_ajax_qb_reset_question_bank', 'qb_reset_question_bank' ) ;
add_action( 'wp_ajax_nopriv_qb_reset_question_bank', 'qb_reset_question_bank' ) ;

/**
 * RESET SUBSCRIBER QUESTIONS RESULT
 * CLEAR QUESTIONS BANK HISTORY
 */

function qb_reset_question_bank() {

    $exargs = array(
        'post_type' => 'qb_exams' ,
        'author' => get_current_user_id() ,
        'numberposts'=> -1 ,
        'post_status' => 'publish' ,
    ) ;

    $allExams = get_posts( $exargs ) ;

    if($allExams) {

        $deletedExamsID = array() ;

        global $wpdb; // Must have this or else!

        $qb_postmeta_table = $wpdb->postmeta;

        $arr = array() ;

        foreach ($allExams as $exam) { 

            // GET POST META => get_post_metas.php
            $qb_get_post_meta = new QB_Get_Post_Meta($exam->ID) ;
            $qb_detail = $qb_get_post_meta->qb_detail ;
            $qb_result = $qb_get_post_meta->qb_result ;

            if($qb_result) {
                if( array_key_exists('used_questions' , $qb_result) ) {
                    $q_last_ID = current($qb_result['used_questions']) ;
                    $qb_result['used_questions'] = array() ;
                    array_push($arr , $qb_result) ;
                }
                update_post_meta( $exam->ID, 'qb_subscriber_exam_result_meta_key', $qb_result ) ;
            }
            
        }

        wp_reset_postdata();
        echo wp_json_encode( array(
             "success" => "success" ,
              "message" => "The questions usage history is reset successfully." 
        ) ) ;
    }


    wp_die() ;
    
}