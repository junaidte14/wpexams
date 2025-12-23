<?php
/**
* ADD NEW CORRECT QUESTION INTO RESULT
*/

function qb_add_new_correct_question($currentQuestionID , $qb_result , $subscriberAnswer) {

    $wrong_sub_opt_key = array_search($currentQuestionID , array_values(array_column($qb_result['wrong_sub_opt'] ,"ID"))) ;
    $correct_sub_opt_key = array_search($currentQuestionID , array_values(array_column($qb_result['correct_sub_opt'] ,"ID"))) ;

    $wrong_sub_opt = qb_unset_questions($wrong_sub_opt_key , $qb_result['wrong_sub_opt']) ;

    if($correct_sub_opt_key === false ) {
        array_push($qb_result['correct_sub_opt'] , array("ID" => $currentQuestionID , "KEY" => $subscriberAnswer)) ;
    }

    foreach ($qb_result['correct_sub_opt'] as $opt_key => $opt_value) {
        if($opt_value['ID'] == $currentQuestionID) {
            $qb_result['correct_sub_opt'][$opt_key]['KEY'] = $subscriberAnswer ;
        }
    }

    $correct_sub_opt = $qb_result['correct_sub_opt'] ;
    array_push($qb_result['solved_questions'] , $currentQuestionID) ;
    array_push($qb_result['used_questions'] , $currentQuestionID) ;
    $solved_questions = array_unique($qb_result['solved_questions']) ;
    $used_questions = array_unique($qb_result['used_questions']) ;

    return array(
        "wrong_sub_opt" => $wrong_sub_opt ,
        "correct_sub_opt" => $correct_sub_opt ,
        "solved_questions" => $solved_questions,
        "used_questions" => $used_questions,
    ) ;

}

/**
 * ADD NEW WRONG QUESTION 
 */

    function qb_add_new_wrong_question($currentQuestionID , $qb_result , $subscriberAnswer) {
    // check if wrong answer exist in correct opt answers array then unset from there
    $correct_sub_opt_key = array_search($currentQuestionID , array_values(array_column($qb_result['correct_sub_opt'] ,"ID"))) ;
    $wrong_sub_opt_key = array_search($currentQuestionID , array_values(array_column($qb_result['wrong_sub_opt'] ,"ID"))) ;

    $correct_sub_opt = qb_unset_questions($correct_sub_opt_key , $qb_result['correct_sub_opt']) ;

    if($wrong_sub_opt_key === false) {
        array_push($qb_result['wrong_sub_opt'] , array("ID" => $currentQuestionID , "KEY" => $subscriberAnswer)) ;
    }

    foreach ($qb_result['wrong_sub_opt'] as $opt_key => $opt_value) {
        if($opt_value['ID'] == $currentQuestionID) {
            $qb_result['wrong_sub_opt'][$opt_key]['KEY'] = $subscriberAnswer ;
        }
    }
    
    $wrong_sub_opt = $qb_result['wrong_sub_opt'] ;
    // push all solved questions ids
    array_push($qb_result['solved_questions'] , $currentQuestionID) ; 
    array_push($qb_result['used_questions'] , $currentQuestionID) ; 
    $solved_questions = array_unique($qb_result['solved_questions']) ;
    $used_questions = array_unique($qb_result['used_questions']) ;

    return array(
        "correct_sub_opt" => $correct_sub_opt ,
        "wrong_sub_opt" => $wrong_sub_opt ,
        "solved_questions" => $solved_questions,
        "used_questions" => $used_questions,
    ) ;
    }