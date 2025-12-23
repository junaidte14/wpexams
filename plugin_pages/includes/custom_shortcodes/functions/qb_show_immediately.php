<?php 


add_action( 'wp_ajax_handle_qb_answer_show_immed', 'handle_qb_answer_show_immed' ) ;
add_action( 'wp_ajax_nopriv_handle_qb_answer_show_immed', 'handle_qb_answer_show_immed' ) ;

/**
 * SHOW RESULT IMMEDIATELY
 * FILTER SOLVED QUESTIONS
 */


function handle_qb_answer_show_immed() {

    $subsAnswer = $_REQUEST['subscriber_answer'] ;
    $questionID = $_REQUEST['question_ID'] ;
    $examPostID = $_REQUEST['examPostID'] ;
    $exam_time = $_REQUEST['exam_time'] ;
    $question_time = $_REQUEST['question_time'] ;

    // GET POST META => get_post_metas.php
    $qb_get_post_meta = new QB_Get_Post_Meta($examPostID) ;
    $qb_result = $qb_get_post_meta->qb_result ;
    $qb_detail = $qb_get_post_meta->qb_detail ;
    
    $qb_get_post_meta_o2 = new QB_Get_Post_Meta($questionID) ;
    $qb_q_fields = $qb_get_post_meta_o2->qb_q_fields ;
    
    $qb_question_IDS_or_number = array_key_exists("qb_q_numbers_field" , $qb_detail) ? $qb_detail['qb_q_numbers_field'] : count($qb_detail['qb_q_IDS']) ;
   
    if( $qb_q_fields &&  $qb_q_fields['qb_correct_field'] ) {

        // save the subscriber result 
        $result = array() ;

        $result['total_questions'] = $qb_question_IDS_or_number ;
        $result['exam_time'] = $exam_time ;
        $result['filteredPosts'] = $qb_detail['filteredPosts'] ;
        $result['qb_user_id'] = get_current_user_id() ;

        if($qb_result) {

            if(array_key_exists("question_time" , $qb_result)) {

                $result['question_time'] = qb_update_question_time($qb_result , $questionID , $question_time) ; // function
            
            } else {
                $result['question_time'] = array(array("id" => $questionID , "time" => $question_time)) ;
            }

            if(intval($questionID) == (array_key_exists("filteredPosts" , $qb_result) ? intval($qb_result['filteredPosts'][sizeof($qb_result['filteredPosts']) - 1]) : null)) { 
               
                $result['exam_status'] = "completed" ;

            } else {
                $result['exam_status'] = "pending" ;
            }

            if(is_array($qb_result['correct_sub_opt'])) { 

                if($subsAnswer ==  substr($qb_q_fields['qb_correct_field'], -1)) {
                    $new_correct = qb_add_new_correct_question($questionID , $qb_result , $subsAnswer) ; // common function
                    $result['wrong_sub_opt'] = $new_correct['wrong_sub_opt'];
                    $result['correct_sub_opt'] = $new_correct['correct_sub_opt'];
                    $result['solved_questions'] = $new_correct['solved_questions'];
                    $result['used_questions'] = $new_correct['used_questions'];

                } else {

                    $new_wrong = qb_add_new_wrong_question($questionID , $qb_result , $subsAnswer) ; // common function
                    $result['wrong_sub_opt'] = $new_wrong['wrong_sub_opt'];
                    $result['correct_sub_opt'] = $new_wrong['correct_sub_opt'];
                    $result['solved_questions'] = $new_wrong['solved_questions'];
                    $result['used_questions'] = $new_wrong['used_questions'];

                }
                
            } else {
                if( substr($qb_q_fields['qb_correct_field'], -1) == $subsAnswer) { 
                    $result['correct_sub_opt'] = array(array("ID" => $questionID , "KEY" => $subsAnswer)) ;
                    $result['wrong_sub_opt'] = array() ;
                    $result['solved_questions'] = array($questionID) ;
                    $result['used_questions'] = array($questionID) ;
                } else {
                    $result['solved_questions'] = array($questionID) ;
                    $result['used_questions'] = array($questionID) ;
                    $result['wrong_sub_opt'] = array(array("ID" => $questionID , "KEY" => $subsAnswer)) ;
                    $result['correct_sub_opt'] = array() ;
                }
            }

        } else {

            // DEFAULTS
            $result['question_time'] = array(array("id" => $questionID , "time" => $question_time)) ;
            if(intval($questionID) == (array_key_exists("filteredPosts" , $qb_detail) ? intval($qb_detail['filteredPosts'][sizeof($qb_detail['filteredPosts']) - 1]) : null)) { 
                $result['exam_status'] = "completed" ;
            } else {
                $result['exam_status'] = "pending" ;
            }
            
            $default_q = qb_add_default_correct_wrong_q($qb_q_fields ,  $subsAnswer , $questionID ) ; // common function
            $result['wrong_sub_opt'] = $default_q['wrong_sub_opt'];
            $result['correct_sub_opt'] = $default_q['correct_sub_opt'];
            $result['solved_questions'] = $default_q['solved_questions'];
            $result['used_questions'] = $default_q['used_questions'];
            
        }
        
        // UPDATE
        echo wp_json_encode( array(
            "exam_time" => $result['exam_time'] ,
            "question_correct_answer" =>  substr($qb_q_fields['qb_correct_field'], -1) ,
            "correct_sub_opt" => $result['correct_sub_opt'],
            "wrong_sub_opt" => $result['wrong_sub_opt'],
            "subscriber_total_questions" => $result['total_questions'],
            "subscriber_solved_questions" => array_values(array_unique($result['solved_questions'])),
            "subscriber_used_questions" => array_values(array_unique($result['used_questions'])),
            "explanation" =>  $qb_q_fields['qb_question_description_field']
        ) ) ;

        update_post_meta( $examPostID , 'qb_subscriber_exam_result_meta_key', $result ) ;
        exit ;

    } else {
        echo wp_json_encode( array("message" => "Question not exists.") ) ;
    }

    wp_die() ;
}