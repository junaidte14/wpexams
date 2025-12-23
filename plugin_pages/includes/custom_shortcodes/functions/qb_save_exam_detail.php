<?php 

// SAVE EXAM DETAIL
add_action( 'wp_ajax_handle_qb_save_exam_detail', 'handle_qb_save_exam_detail' );
add_action( 'wp_ajax_nopriv_handle_qb_save_exam_detail', 'handle_qb_save_exam_detail' );

/**
 * SUBSCRIBER EXAM START
 * SAVE SUBSCRIBER EXAM DETAIL 
 * WITH ALL REQUIRED CHECKS
 * CREATE EXAM POST
 */


function handle_qb_save_exam_detail(){

	$permission = check_ajax_referer( 'qb_save_exam_detail_nonce', 'nonce', false );

	if( $permission == false ) {
		echo wp_json_encode( array("error" => "error")) ;
	}

	else {

        if($_REQUEST['formData']) {

            if(
                $_REQUEST['formData']['qb_all_and_unused_field'] == "0" ||  $_REQUEST['formData']['qb_all_and_unused_field'] == "1" ||
                $_REQUEST['formData']['qb_answer_show_immed_field'] == "0" || $_REQUEST['formData']['qb_answer_show_immed_field'] == "1"
            ) {

                $exargs = array(
                    'post_type' => 'qb_exams' ,
                    'posts_per_page' => '-1' ,
                    'author' => get_current_user_id() ,
                    'post_status' => 'publish' ,
                ) ;
        
                $totalExams = get_posts( $exargs ) ;
                $totalExamsCount = count($totalExams) ;
        
                $curentExamNumb = intval($totalExamsCount) + 1 ;
        
                $qb_subscriber_new_post = array(
                    'post_title' => 'exam #'.$curentExamNumb.'' ,
                    'post_content'  => '',
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                    'post_type' => 'qb_exams' ,
                    'post_slug' => 'qb-subscriber-exams' ,
                    'post_parent'           => 0,
                    'menu_order'            => 0,
                    'guid'                  => '',
                    'import_id'             => 0,
                );
        
                $randomQuestions = get_posts(array(
                    'post_type' => 'qb_questions' ,
                    'category' => $_REQUEST['formData']['qb_category_field'] ,
                    'post_status' => 'publish' ,
                    'orderby' => 'rand',
                    'order' => 'DESC'
                ));

                $nxargs = array(
                    'post_type' => 'qb_questions' ,
                    'category' => $_REQUEST['formData']['qb_category_field'] ,
                    'orderby' => 'ID',
                    'order' => 'DESC',
                    'posts_per_page' => $_REQUEST['formData']['qb_q_numbers_field'] ,
                    'post_status' => 'publish' ,
                ) ;

                $questions = get_posts( $nxargs ) ;
                $posts = array();

                $exargs = array(
                    'post_type' => 'qb_exams' ,
                    'author' => get_current_user_id() ,
                    'post_status' => 'publish' ,
                ) ;

                $allExams = get_posts( $exargs ) ;
                
                if($_REQUEST['formData']['qb_all_and_unused_field'] == "1") {

                    $filteredQuestionsIds = qb_unused_questions($_REQUEST['formData']['qb_category_field']) ; // common function

                    if(!empty($filteredQuestionsIds)) {
                        foreach ( $filteredQuestionsIds as $filteredQuestionsId ) { 
                            $posts[] += $filteredQuestionsId;
                        }
                    }

                    if(empty($filteredQuestionsIds) || !(intval($_REQUEST['formData']['qb_q_numbers_field']) <= intval(sizeof($filteredQuestionsIds)))) {
                        echo wp_json_encode( array("error" => "Currently, the selected category(s) have ".count($filteredQuestionsIds)." unused questions." )) ;
                        exit() ;
                    } 


                } else {
                    foreach ( $questions as $question ) { 
                        $posts[] += $question->ID;
                    }
                }
        
                
                $_REQUEST['formData']['filteredPosts'] = array_slice($posts, 0, intval($_REQUEST['formData']['qb_q_numbers_field']));
                
                if($randomQuestions && !empty($posts)) {
        
                    if(intval($_REQUEST['formData']['qb_q_numbers_field']) <= intval(count($randomQuestions))) {
        
                        $postId = wp_insert_post( $qb_subscriber_new_post ) ;
            
                        if($postId) {

                            if(get_post_meta( $postId, "qb_subscriber_exam_detail_meta_key", true )) {
                                update_post_meta( $postId, "qb_subscriber_exam_detail_meta_key", $_REQUEST['formData']) ;
                            } else {
                                add_post_meta( $postId, "qb_subscriber_exam_detail_meta_key", $_REQUEST['formData']) ;
                            }

                            add_post_meta( $postId , "qb_subscriber_exam_result_meta_key" , array("filteredPosts" => array_slice($posts, 0, intval($_REQUEST['formData']['qb_q_numbers_field'])) , "qb_user_id" => get_current_user_id()) ) ;

                            echo wp_json_encode( array("success" => "success" , "postID" => $postId)) ;
                        }
        
                    } else {
        
                         echo wp_json_encode( array("error" => "This category do not enough questions. Currently, this category only have ".count($randomQuestions)." questions.")) ;
        
                    }
        
        
                } else {
        
                    echo wp_json_encode( array("error" => "This category do not have enough questions. Currently, this category only has ".count($randomQuestions)." questions.")) ;
        
                }

            } else {
                echo wp_json_encode( array("error" => "error")) ;
            }
        } else {
            echo wp_json_encode( array("error" => "error")) ;
        }
    }

    
    wp_die();

}