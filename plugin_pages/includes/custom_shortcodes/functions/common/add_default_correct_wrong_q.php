<?php 

function qb_add_default_correct_wrong_q($qb_q_fields , $subscriberAnswer , $currentQuestionID) {

    if(substr($qb_q_fields['qb_correct_field'], -1) ==  $subscriberAnswer) { 
        $correct_sub_opt = array(array("ID" => $currentQuestionID , "KEY" =>  $subscriberAnswer)) ;
        $wrong_sub_opt = array() ;

    } else {
        $wrong_sub_opt = array(array("ID" => $currentQuestionID , "KEY" =>  $subscriberAnswer)) ;
        $correct_sub_opt = array() ;
        
    }
    $solved_questions = array($currentQuestionID) ;
    $used_questions = array($currentQuestionID) ;

    return array(
        "correct_sub_opt" => $correct_sub_opt ,
        "wrong_sub_opt" => $wrong_sub_opt ,
        "solved_questions" => $solved_questions,
        "used_questions" => $used_questions,
    ) ;

}