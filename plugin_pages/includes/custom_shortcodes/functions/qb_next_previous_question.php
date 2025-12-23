<?php
add_action( 'wp_ajax_qb_next_prev_question', 'qb_next_prev_question' ) ;
add_action( 'wp_ajax_nopriv_qb_next_prev_question', 'qb_next_prev_question' ) ;

/**
 * NEXT QUESTION
 * PREVIOUS QUESTION
 * SHOW RESULT
 * FILTER SOLVED QUESTIONS
 * SHOW RESULT AT THE END
 */

function qb_next_prev_question () {
   
    if($_REQUEST['currentQuestionID'] && $_REQUEST['btnAction'] && $_REQUEST['examPostID'] && $_REQUEST['question_time']) {

        $currentQuestionID = $_REQUEST['currentQuestionID'] ;
        $exam_time = $_REQUEST['exam_time'] ;
        $question_time = $_REQUEST['question_time'] ;
        $examPostID = $_REQUEST['examPostID'] ;

         // GET POST META => get_post_metas.php
        $qb_get_post_meta = new QB_Get_Post_Meta($examPostID) ;
        $qb_detail = $qb_get_post_meta->qb_detail ;
        $qb_result = $qb_get_post_meta->qb_result ;

        $qb_get_post_meta_o2 = new QB_Get_Post_Meta($currentQuestionID) ;
        $qb_q_fields = $qb_get_post_meta_o2->qb_q_fields ;

        if($qb_detail) {
            if(array_key_exists("filteredPosts" , $qb_detail)) { 
                $current = array_search( intval( $currentQuestionID ), $qb_detail['filteredPosts'] );
            } 
        } else {
            echo wp_json_encode( array("error" => "Posts not exist!") ) ;
            exit ;
        }  

        $new_q_ID = qb_next_question_ID($currentQuestionID , $current , $qb_detail , $_REQUEST['btnAction']) ;
       
        if(empty($new_q_ID['nextID'])) {

            $new_q_ID['nextID'] = $currentQuestionID ;
            $new_q_ID['currentId'] = $currentQuestionID ;
            
        }        

        if(!empty($new_q_ID['nextID'])) {

            $qb_get_post_meta_o3 = new QB_Get_Post_Meta($new_q_ID['nextID']) ;
            $qb_q_fields_03 = $qb_get_post_meta_o3->qb_q_fields ;
            $nextPost = get_post( $new_q_ID['nextID'] ) ;

            if($qb_q_fields_03 && $nextPost) {
                if($qb_detail && $qb_detail['qb_answer_show_immed_field'] == "1") {
                    $immediately = true ;
                } 
                
                $qb_question_IDS_or_number = array_key_exists("qb_q_numbers_field" , $qb_detail) ? intval($qb_detail['qb_q_numbers_field']) : count($qb_detail['qb_q_IDS']) ;
                if($_REQUEST['immediately'] == "0") {
                    // sava the subscriber result 
                
                    $result = array() ;
                    
                    $result['filteredPosts'] = $qb_detail['filteredPosts'] ;
                    $result['qb_user_id'] = get_current_user_id() ;
                    
                    $result['exam_time'] = $exam_time ;
                    $result['total_questions'] = $qb_question_IDS_or_number ;

                    // WHEN SUBSCRIBER CLICK ON EXIT EXAM BUTTON
                    if($_REQUEST['btnAction'] == "exitExam") {
                        if( $_REQUEST['subscriberAnswer'] == "null") {
                            $result['exitQuestion'] = $currentQuestionID ;
                        }
                    }
                    
                    if($qb_result) {
                        if(!array_key_exists("filteredPosts" , $qb_result)) {
                            $result['exam_status'] = "pending" ;
                        } else { 
                            // SAVE EXAM STATUS 
                            if(intval($new_q_ID['currentId']) == (array_key_exists("filteredPosts" , $qb_result) && $_REQUEST['subscriberAnswer'] != "null" ? $qb_result['filteredPosts'][sizeof($qb_result['filteredPosts']) - 1] : null)) { 
                                $result['exam_status'] = "completed" ;
                            } else {
                                $result['exam_status'] = "pending" ;
                            }
                        }

                        if(is_array($qb_result['correct_sub_opt'])) {

                            // UPDATE QUESTION TIME WHEN QUESTION_TIME EXIST IN RESULT
                            if(array_key_exists("question_time" , $qb_result)) {
                                $result['question_time'] = qb_update_question_time($qb_result , $currentQuestionID , $question_time) ; // function
                            } 

                            if(substr($qb_q_fields['qb_correct_field'], -1) == $_REQUEST['subscriberAnswer']) {

                                $new_correct = qb_add_new_correct_question($currentQuestionID , $qb_result , $_REQUEST['subscriberAnswer']) ; // common function
                                $result['wrong_sub_opt'] = $new_correct['wrong_sub_opt'];
                                $result['correct_sub_opt'] = $new_correct['correct_sub_opt'];
                                $result['solved_questions'] = $new_correct['solved_questions'];
                                $result['used_questions'] = $new_correct['used_questions'];

                            } else {

                                $new_wrong = qb_add_new_wrong_question($currentQuestionID , $qb_result , $_REQUEST['subscriberAnswer']) ; // common function
                                $result['wrong_sub_opt'] = $new_wrong['wrong_sub_opt'];
                                $result['correct_sub_opt'] = $new_wrong['correct_sub_opt'];
                                $result['solved_questions'] = $new_wrong['solved_questions'];
                                $result['used_questions'] = $new_wrong['used_questions'];

                            }

                        } else {
                            // DEFAULTS
                            $result['question_time'] = array(array("id" => $currentQuestionID , "time" => $question_time )) ;
                            $default_q = qb_add_default_correct_wrong_q($qb_q_fields ,  $_REQUEST['subscriberAnswer'] , $currentQuestionID) ; // common function
                            $result['wrong_sub_opt'] = $default_q['wrong_sub_opt'];
                            $result['correct_sub_opt'] = $default_q['correct_sub_opt'];
                            $result['solved_questions'] = $default_q['solved_questions'];
                            $result['used_questions'] = $default_q['used_questions'];
                        }
                    } else {
                        
                        $result['question_time'] = array(array("id" => $currentQuestionID , "time" => $question_time )) ;
                        if($_REQUEST['subscriberAnswer'] == "null") {
                            $result['exam_status'] = "pending" ;
                        } else {
                            if(intval($new_q_ID['currentId']) == (array_key_exists("filteredPosts" , $qb_detail)  ? $qb_detail['filteredPosts'][sizeof($qb_detail['filteredPosts']) - 1] : null)) { 
                                $result['exam_status'] = "completed" ;
                            } else{
                                $result['exam_status'] = "pending" ;
                            }
                        }

                        // DEFAULTS
                        $default_q = qb_add_default_correct_wrong_q($qb_q_fields ,  $_REQUEST['subscriberAnswer'] , $currentQuestionID) ; // common function
                        $result['wrong_sub_opt'] = $default_q['wrong_sub_opt'];
                        $result['correct_sub_opt'] = $default_q['correct_sub_opt'];
                        $result['solved_questions'] = $default_q['solved_questions'];
                        $result['used_questions'] = $default_q['used_questions'];

                    }
                    
                    update_post_meta( $examPostID , 'qb_subscriber_exam_result_meta_key', $result ) ;
                    
                    $immediately = false ;
                } 

                if($_REQUEST['immediately'] == "1" && $_REQUEST['subscriberAnswer'] == "null") {
                    qb_subscriber_answer_null ($_REQUEST['subscriberAnswer'] , $new_q_ID , $qb_result , $qb_question_IDS_or_number , $currentQuestionID , $exam_time , $_REQUEST['btnAction'] , $examPostID) ; // function
                }

                if($_REQUEST['btnAction'] == "exitExam") {
                    echo wp_json_encode( array("exit_exam" => "exit") ) ;
                    exit ;
                }

                if($qb_detail['qb_role'] != "admin_defined" && $qb_result && array_key_exists("solved_questions" , $qb_result) && intval($new_q_ID['currentId']) != (is_array($qb_detail['filteredPosts']) ? $qb_detail['filteredPosts'][sizeof($qb_detail['filteredPosts']) - 1] : "")) {
                    
                    if(in_array(strval($new_q_ID['nextID']) , $qb_result['solved_questions']) && $qb_result['qb_user_id'] == get_current_user_id()) {
                        // function
                        qb_question_solved ($_REQUEST['btnAction'] , $qb_result , $exam_time , $new_q_ID , $examPostID , $qb_q_fields_03 , $nextPost) ; // function
                    }
                }
                
                // Result 
                if( intval($new_q_ID['currentId']) == (array_key_exists("filteredPosts" , $qb_detail) ? $qb_detail['filteredPosts'][sizeof($qb_detail['filteredPosts']) - 1] : null )) {
                    $qb_get_post_meta_f = new QB_Get_Post_Meta($examPostID) ;
                    $qb_final_result = $qb_get_post_meta_f->qb_result ;
                    qb_show_question_result($qb_final_result , $exam_time , $new_q_ID , $examPostID) ; // function

                } else { // NEXT QUESTION
                    qb_show_next_question($qb_detail , $_REQUEST['btnAction'] , $nextPost , $new_q_ID , $qb_q_fields_03) ; // function
                } 

            } else {
                echo wp_json_encode( array(
                    "error" => "Internal server error!" 
                 )) ;
            }
        }
    }
    wp_die() ;
}

/**
 * GET NEXT QUESTION ID
 */

function qb_next_question_ID ($currentQuestionID , $current , $qb_detail , $btn_action) {
    if($btn_action == 'next') {
        if($qb_detail) {
            if(array_key_exists("filteredPosts" , $qb_detail)) {
                return array(
                    "nextID" => $qb_detail['filteredPosts'][intval($current)+1],
                    "currentId" => $currentQuestionID
                ) ;
            } else {
                return array("error" => "error") ;
            }
        }  
            
    } else if($btn_action == 'prev') {
        if($qb_detail) {
            if(array_key_exists("filteredPosts" , $qb_detail)) {
                    return array(
                    "nextID" => $qb_detail['filteredPosts'][intval($current)-1] ,
                    "currentId" => $qb_detail['filteredPosts'][intval($current)-1]
                    ) ;
            } else {
                return array("error" => "error") ;
            }
        } 
    }
}

/**
 * SOLVED QUESTION
 */

 function qb_question_solved ($btn_action , $qb_result , $exam_time , $new_q_ID , $examPostID , $qb_q_fields_03 , $nextPost) {

    if($btn_action == "exitExam") {
        $qb_result['exam_time'] = $exam_time ;
        update_post_meta( $examPostID , 'qb_subscriber_exam_result_meta_key', $qb_result ) ;
        echo wp_json_encode( array("exit_exam" => "exit" ) ) ;
        exit ;
    }

    $question_sub_answer = array() ;

    $sub_answer = "" ;
    foreach ($qb_result['wrong_sub_opt'] as $key_ID) {
        if($key_ID['ID'] == $new_q_ID['nextID'] ) {
            $sub_answer = $key_ID['KEY'];
        }
    }
    foreach ($qb_result['correct_sub_opt'] as $key_ID) {
        if($key_ID['ID'] == $new_q_ID['nextID'] ) {
            $sub_answer = $key_ID['KEY'];
        }
    }

    $question_time_find = "" ;
    foreach ($qb_result['question_time'] as $key => $value) {
        if(in_array($value['id'] , $qb_result['solved_questions'])) { 
            $question_time_find = $value['time'] ;
        }
    }
    
    echo wp_json_encode( array(
        "success" => "success",
        "allPostsID" => array_key_exists("filteredPosts" , $qb_result) ? $qb_result['filteredPosts'] : "posts not exist!" ,
        "btnAction" => $btn_action ,
        "sub_answer" => $sub_answer ,
        "message" => "solved" ,
        "questionTitle" => $nextPost->post_title ,
        "currentId" => $new_q_ID['currentId'] ,
        "questionID" => $new_q_ID['nextID'] ,
        "question_time" => $question_time_find ,
        "correct_sub_opt" => $qb_result['correct_sub_opt'] ,
        "wrong_sub_opt" => $qb_result['wrong_sub_opt'] ,
        "total_questions" => $qb_result['total_questions'],
        "solved_questions" => $qb_result['solved_questions'],
        "used_questions" => $qb_result['used_questions'],
        "qb_question_options_field" => $qb_q_fields_03['qb_question_options_field'] ,
        "qb_correct_field" => $qb_q_fields_03['qb_correct_field'],
        "explanation" => $qb_q_fields_03['qb_question_description_field']
    ) ) ;
    exit ;
 }

  /**
   * SHOW RESULT
   */

    function qb_show_question_result($qb_final_result , $exam_time , $new_q_ID , $examPostID) {
        $questionsFieldsArr = array() ;
        $question_time_IDS = array() ;
        $question_time_VALUES = array() ;

        // ADD TIME TO QUESTION OPTION FIELDS
        foreach ($qb_final_result['question_time'] as $key => $value) {
            if(in_array($value['id'] , $qb_final_result['filteredPosts'])) {
                array_push($question_time_IDS , $value['id']) ;
                array_push($question_time_VALUES , $value['time']) ;
            }
        }

        for ($i=0; $i < count($qb_final_result['filteredPosts']) ; $i++) { 

            $qb_get_post_meta_o4 = new QB_Get_Post_Meta($qb_final_result['filteredPosts'][$i]) ;
            $qb_q_fields_04 = $qb_get_post_meta_o4->qb_q_fields ;
            
            if(in_array(intval($qb_final_result['filteredPosts'][$i]),$question_time_IDS)) {
                $qb_q_fields_04['time'] = $question_time_VALUES[$i] ;
            }

            foreach ($qb_final_result['wrong_sub_opt'] as $key_ID) {
                if($qb_final_result['filteredPosts'][$i] == $key_ID['ID']) {
                    $qb_q_fields_04['sub_answer'] = $key_ID['KEY'];
                }
            }
            foreach ($qb_final_result['correct_sub_opt'] as $key_ID) {
                if($qb_final_result['filteredPosts'][$i] == $key_ID['ID']) {
                    $qb_q_fields_04['sub_answer'] = $key_ID['KEY'];
                }
            }
            
            array_push($questionsFieldsArr , $qb_q_fields_04) ;
        }

        $qb_final_result['exam_time'] = $exam_time ;
        update_post_meta( $examPostID , 'qb_subscriber_exam_result_meta_key', $qb_final_result ) ; 
        echo wp_json_encode( array(
            "success" => "success" ,
            "result"  => "announced" ,
            "exam_status" => $qb_final_result['exam_status'] ,
            "exam_time" => $qb_final_result['exam_time'] ,
            "question_time" => $qb_final_result['question_time'] ,
            "currentId" => $new_q_ID['currentId'] ,
            "allPostsID" => $qb_final_result['filteredPosts'] ? $qb_final_result['filteredPosts'] : "posts not exist!" ,
            "questionsFeilds" => $questionsFieldsArr ,
            "correct_sub_opt" => $qb_final_result['correct_sub_opt'] ,
            "wrong_sub_opt" => $qb_final_result['wrong_sub_opt'] ,
            "total_questions" => $qb_final_result['total_questions'],
            "solved_questions" => $qb_final_result['solved_questions'],
            "used_questions" => $qb_final_result['used_questions'],
        )) ;

        exit ;
   }

   /**
    * SHOW NEXT QUESTION
    */

    function qb_show_next_question($qb_detail , $btnAction , $nextPost , $new_q_ID , $qb_q_fields_03) {

         echo wp_json_encode( array(
            "success" => "success",
            "allPostsID" => $qb_detail['filteredPosts'] ,
            "btnAction" => $btnAction ,
            "questionTitle" => $nextPost->post_title ,
            "currentId" => $new_q_ID['currentId'] ,
            "questionID" => $new_q_ID['nextID'] ,
            "qb_question_options_field" => $qb_q_fields_03['qb_question_options_field'] ,
            "qb_correct_field" => $qb_q_fields_03['qb_correct_field'],
            "explanation" => $qb_q_fields_03['qb_question_description_field']
        ) ) ;

    }

    /**
     * WHEN SUBSCRIBER ANSWER IS NULL
     */
    
    function qb_subscriber_answer_null ($subscriberAnswer , $new_q_ID , $qb_result , $qb_question_IDS_or_number , $currentQuestionID , $exam_time , $btnAction , $examPostID) {
        $result = array() ;
                    
        $result['total_questions'] = $qb_question_IDS_or_number;
        $result['filteredPosts'] = $qb_result['filteredPosts'] ;
        $result['exam_time'] = $exam_time ;

        if($btnAction == "exitExam") {
            if( $subscriberAnswer == "null") {
                $result['exitQuestion'] = $currentQuestionID ;
            }
        }
        
        if($qb_result && $btnAction != "exitExam") { 

            if(array_key_exists("question_time" , $qb_result)) {
                array_push($qb_result['question_time'] , array("id" => "null" , "time" => "null" )) ;
                $result['question_time'] = array_unique($qb_result['question_time']) ;
            }

            array_push($qb_result['wrong_sub_opt'] , array("ID" => $currentQuestionID , "KEY" => "null")) ;
            $result['wrong_sub_opt'] = $qb_result['wrong_sub_opt'] ;
            array_push($qb_result['correct_sub_opt'] , array("ID" => $currentQuestionID , "KEY" => "null")) ;
            $result['correct_sub_opt'] = $qb_result['correct_sub_opt'] ;
            array_push($qb_result['solved_questions'] , $currentQuestionID) ;    
            $result['solved_questions'] =array_unique($qb_result['solved_questions']) ;
            $result['used_questions'] =array_unique($qb_result['used_questions']) ;

            if(intval($new_q_ID['currentId']) == (array_key_exists("filteredPosts" , $qb_result)? $qb_result['filteredPosts'][sizeof($qb_result['filteredPosts']) - 1] : null)) { 
                $result['exam_status'] = "completed" ;
            } else {
                $result['exam_status'] = "pending" ;
            } 
            if(!array_search(strval($currentQuestionID) , $qb_result['solved_questions'])) {
                update_post_meta( $examPostID , 'qb_subscriber_exam_result_meta_key', $result ) ;
            }

        } else {

            $result['wrong_sub_opt'] = array(array("ID" => $currentQuestionID , "KEY" => "null"));
            $result['correct_sub_opt'] = array() ;
            $result['question_time'] = array(array("id" => "null" , "time" => "null" )) ;
            $result['solved_questions'] = array($currentQuestionID) ;
            $result['used_questions'] = array($currentQuestionID) ;
            $result['exam_status'] = "pending" ;
            update_post_meta( $examPostID , 'qb_subscriber_exam_result_meta_key', $result ) ;

        } 
    }

    