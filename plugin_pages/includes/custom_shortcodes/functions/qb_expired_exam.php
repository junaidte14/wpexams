<?php 
add_action( 'wp_ajax_qb_exam_expired', 'qb_exam_expired' ) ;
add_action( 'wp_ajax_nopriv_qb_exam_expired', 'qb_exam_expired' ) ;

/**
 * EXAM EXPIRED
 */

function qb_exam_expired() {

    if($_REQUEST['examPostID']) {

        $examPostID = $_REQUEST['examPostID'] ;

         // GET POST META => get_post_metas.php
        $qb_get_post_meta = new QB_Get_Post_Meta($examPostID) ;
        $qb_detail = $qb_get_post_meta->qb_detail ;
        $qb_result = $qb_get_post_meta->qb_result ;

        if($qb_detail && $qb_result) {
            if(array_key_exists("filteredPosts" , $qb_result) && array_key_exists("solved_questions" , $qb_result)) { 
                
                $QuestionsnotSolvedID = array_diff($qb_result['filteredPosts'] , $qb_result['solved_questions']) ;
                $qb_result['exam_time'] = "expired" ;
                $qb_result['exam_status'] = "completed" ;
                $qb_result['correct_sub_opt'] =  $qb_result['correct_sub_opt'];
                for ($i=0; $i < count(array_values($QuestionsnotSolvedID)); $i++) { 
                    array_push($qb_result['wrong_sub_opt'] , array("ID" => array_values($QuestionsnotSolvedID) , "KEY" => "null")) ;
                    array_push($qb_result['solved_questions'] , strval(array_values($QuestionsnotSolvedID)[$i])) ;
                    array_push($qb_result['used_questions'] , strval(array_values($QuestionsnotSolvedID)[$i])) ;
                    array_push($qb_result['question_time'] , array("id" => strval(array_values($QuestionsnotSolvedID)[$i]) , "time" => "expired")) ;
                }
                update_post_meta( $examPostID , 'qb_subscriber_exam_result_meta_key', $qb_result ) ;
                echo wp_json_encode( array("exam_time" => "Expired") ) ;
                exit ;
            } else {

                $qb_result['exam_time'] = "expired" ;
                $qb_result['total_questions'] = count($qb_result['filteredPosts']) ;
                $qb_result['exam_status'] = "completed" ;
                $qb_result['correct_sub_opt'] =  array();
                $wrong_sub_opt = array() ;
                $solved_questions = array() ;
                $used_questions = array() ;
                $question_time = array() ;
                
                for ($i=0; $i < count($qb_result['filteredPosts']); $i++) { 
                    array_push($wrong_sub_opt , array("ID" => $qb_result['filteredPosts'][$i] , "KEY" => "null")) ;
                    array_push($solved_questions , strval($qb_result['filteredPosts'][$i]))  ;
                    array_push($used_questions , strval($qb_result['filteredPosts'][$i]))  ;
                    array_push($question_time , array("id" => strval($qb_result['filteredPosts'][$i]) , "time" => "expired"))  ;
                }
                $qb_result['wrong_sub_opt'] = $wrong_sub_opt ;
                $qb_result['solved_questions'] = $solved_questions ;
                $qb_result['used_questions'] = $used_questions ;
                $qb_result['question_time'] = $question_time ;
                
                update_post_meta( $examPostID , 'qb_subscriber_exam_result_meta_key', $qb_result ) ;
                echo wp_json_encode( array("exam_time" => "Expired") ) ;
                exit ;

            }
            
        }
    }

    wp_die() ;
}