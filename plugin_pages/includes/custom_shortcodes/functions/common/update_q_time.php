<?php
 /**
* UPDATE QUESTION TIME
*/

  function qb_update_question_time($qb_result , $currentQuestionID , $question_time) {

    array_push($qb_result['question_time'] , array("id" => $currentQuestionID , "time" => $question_time )) ;
    $newQuestionTimeArray = array(); 
    $FilterDuplicateIDs = array(); 
    foreach ( $qb_result['question_time'] as $key => $value ) { 
        if ( !in_array($value['id'], $FilterDuplicateIDs) ) { 
            $FilterDuplicateIDs[] = $value['id']; 
            $newQuestionTimeArray[$key] = $value; 
        }
        
    } 
    // UPDATE QUESTION TIME
    foreach ($newQuestionTimeArray as $key => $value) {
        if($value['id'] == $currentQuestionID) {
            $newQuestionTimeArray[$key]['time'] = $question_time ;
        }
    }
    
    $originalQuestionTimeArray = $newQuestionTimeArray; 
    
    $newQuestionTimeArray = NULL;
    $FilterDuplicateIDs = NULL;
    return $originalQuestionTimeArray ;

  }