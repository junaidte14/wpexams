<?php 

add_action( 'wp_ajax_qb_review_exam', 'qb_review_exam' ) ;
add_action( 'wp_ajax_nopriv_qb_review_exam', 'qb_review_exam' ) ; 

function qb_review_exam() { 

    if($_REQUEST['currentQuestionID'] && $_REQUEST['btnAction'] && $_REQUEST['examPostID']) {

        $currentQuestionID = $_REQUEST['currentQuestionID'] ;
        $btnAction = $_REQUEST['btnAction'] ;
        $examPostID = $_REQUEST['examPostID'] ;

        // GET POST META => get_post_metas.php
        $qb_get_post_meta = new QB_Get_Post_Meta($examPostID) ;
        $qb_result = $qb_get_post_meta->qb_result ;

        if(array_key_exists("filteredPosts" , $qb_result)) { 
            $current = array_search( intval( $currentQuestionID ), $qb_result['filteredPosts'] );
        } 

        if($btnAction == 'next') {
            
            if($qb_result) {

                if(array_key_exists("filteredPosts" , $qb_result)) {

                     $nextID = $qb_result['filteredPosts'][$current+1] ;

                } else {
                    echo wp_json_encode( array("error" => "Posts not exist!") ) ;
                    exit ;
                }
            } 

            
        } else if($btnAction == 'prev') {
            
            if($qb_result) {

                if(array_key_exists("filteredPosts" , $qb_result)) {

                     $nextID = $qb_result['filteredPosts'][$current-1] ;

                } 
        
            } 
            
        }
        
        if(empty($nextID)) {
            $nextID = $currentQuestionID ;
        }

        if(!empty($nextID)) {

            // GET POST META => get_post_metas.php
            $qb_get_post_meta_o2 = new QB_Get_Post_Meta($nextID) ;
            $qb_q_fields = $qb_get_post_meta_o2->qb_q_fields ;

            $newPost = get_post( intval($nextID) ) ;

            $sub_answer = "" ;
            foreach ($qb_result['correct_sub_opt'] as $key_ID) {
                if($nextID == $key_ID['ID']) {
                    $sub_answer = $key_ID['KEY'];
                }
            }
            foreach ($qb_result['wrong_sub_opt'] as $key_ID) {
                if($nextID == $key_ID['ID']) {
                    $sub_answer = $key_ID['KEY'];
                }
            }

            $question_time_find = "" ;
            foreach ($qb_result['question_time'] as $key => $value) {
                if($value['id'] == $nextID) { 
                    $question_time_find = $value['time'] ;
                }
            }

             echo wp_json_encode( array(
                "success" => "success",
                "allPostsID" => $qb_result['filteredPosts'] ,
                "btnAction" => $btnAction ,
                "message" => "solved" ,
                "questionTitle" => $newPost->post_title ,
                "questionID" => $nextID ,
                "sub_answer" => $sub_answer ,
                "question_time" => $question_time_find ,
                "total_questions" => $qb_result['total_questions'],
                "correct_sub_opt" => $qb_result['correct_sub_opt'],
                "wrong_sub_opt" => $qb_result['wrong_sub_opt'],
                "total_questions" => $qb_result['total_questions'],
                "solved_questions" => $qb_result['solved_questions'],
                "used_questions" => $qb_result['used_questions'],
                "qb_question_options_field" => $qb_q_fields['qb_question_options_field'] ,
                "qb_correct_field" => $qb_q_fields['qb_correct_field'],
                "explanation" => $qb_q_fields['qb_question_description_field']
            ) ) ;
            
            exit ;
        }

    } else {
        echo wp_json_encode( array("message" => "Please add valid data!") ) ;
        exit ;
    }
    wp_die() ;
}