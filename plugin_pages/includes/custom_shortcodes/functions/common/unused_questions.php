<?php

function qb_unused_questions($category) {

    $randomQuestions = get_posts(array(
        'post_type' => 'qb_questions' ,
        'category' => $category ,
        'post_status' => 'publish' ,
        'orderby' => 'rand',
        'order' => 'DESC'
    ));

    $exargs = array(
        'post_type' => 'qb_exams' ,
        'author' => get_current_user_id() ,
        'post_status' => 'publish' ,
    ) ;

    $allExams = get_posts( $exargs ) ;
    if($allExams) {

        $qb_all_used_questions_arr = array() ;
        foreach ($allExams as $exam) {
            $qb_get_post_meta = new QB_Get_Post_Meta($exam->ID) ;
            $qb_result = $qb_get_post_meta->qb_result ;
            $qb_detail = $qb_get_post_meta->qb_detail ;
            if($qb_result && $qb_detail) {
                if($qb_detail['qb_role'] != "admin_defined") {
                    for ($i=0; $i < count($qb_result['used_questions']) ; $i++) { 
                        array_push($qb_all_used_questions_arr , $qb_result['used_questions'][$i]) ;
                    }
                }

            }

        }

        $all_random_question_ids_arr = array() ;
        foreach ($randomQuestions as $randomQuestion) {
            array_push($all_random_question_ids_arr , $randomQuestion->ID) ;
        }
        $filteredQuestionsIds = array_diff($all_random_question_ids_arr , $qb_all_used_questions_arr) ;
        
        return $filteredQuestionsIds ;

    }
}