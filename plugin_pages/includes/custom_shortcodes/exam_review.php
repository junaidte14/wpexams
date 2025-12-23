<?php

if(isset($_GET['qb_exam_review_ID'])) {
   $examID = $_GET['qb_exam_review_ID'] ;
    // GET POST META => get_post_metas.php
    $qb_get_post_meta = new QB_Get_Post_Meta($examID) ;
    $qb_result = $qb_get_post_meta->qb_result ;
    $qb_detail = $qb_get_post_meta->qb_detail ;

    if($qb_result && $qb_detail) { 
        $qb_question_IDS_or_number = array_key_exists("qb_q_numbers_field" , $qb_detail) ? $qb_detail['qb_q_numbers_field'] : count($qb_detail['qb_q_IDS']) ;
        if($qb_result['exam_status'] == "completed") { 
            
             $filterCorrectQuestions = array_filter($qb_result['correct_sub_opt'], function ($var) {
                 return ($var['KEY'] != 'null');
            });

            $questionsFieldsArr = array() ;
            $question_time_IDS = array() ;
            $question_time_VALUES = array() ;
            
            if(array_key_exists("question_time" , $qb_result)) {
                foreach ($qb_result['question_time'] as $key => $value) {
                    if(in_array($value['id'] , $qb_result['filteredPosts'])) {
                        array_push($question_time_IDS , $value['id']) ;
                        array_push($question_time_VALUES , $value['time']) ;
                    }
                }

            }

             ?>
            <div class='qb_d_flex qb_m_tb_20'>
                <h5 class='qb_m_0'>Score <?php echo $filterCorrectQuestions ? count($filterCorrectQuestions) : 0  ?>/<?php echo $qb_result['total_questions'] ? $qb_result['total_questions'] : 0 ; ?></h5> 
                <span id='subscriber__exam_diff_time' class='<?php echo $qb_result['exam_time'] == "expired" ? "qb_d_none" : "" ?>'><?php echo $qb_detail['qb_timed_field'] == "1" ? "" : $qb_result['exam_time'] ; ?></span>
            </div>
            <?php
            if($qb_detail['qb_timed_field'] == "1" ) { 
                $caluculateTime = $get_time_from_setting * intval($qb_question_IDS_or_number) ;
            ?>  
            <script>
                
                
                let hrs = parseInt(convertSecondsToHms(<?php echo json_encode($caluculateTime) ?>)['hrs']) ;
                let min = parseInt(convertSecondsToHms(<?php echo json_encode($caluculateTime) ?>)['min']) ;
                let sec = parseInt(convertSecondsToHms(<?php echo json_encode($caluculateTime) ?>)['sec']) ;
                
                let time = (hrs<=9 ? "0" + hrs : hrs) + ":" + (min<=9 ? "0" + min : min) + ":" + (sec<=9 ? "0" + sec : sec) + "";
                
                
                let calculatedExamTime = time ;
                let subscriberExamTime = <?php echo json_encode($qb_result['exam_time']) ; ?> ;
                // console.log(subscriberExamTime) ;

                examTimeDiff(calculatedExamTime, subscriberExamTime) ;

            </script>
            <?php

             }

            if(array_key_exists("filteredPosts" , $qb_result)) {

                $qb_get_post_meta_o2 = new QB_Get_Post_Meta($qb_result['filteredPosts'][0]) ;
                $qb_q_fields = $qb_get_post_meta_o2->qb_q_fields ;

                $qb_q_fields = get_post_meta( $qb_result['filteredPosts'][0] , 'qb_question_fields_meta_key', true ) ;
                if($qb_q_fields) {

                    if(in_array(intval($qb_result['filteredPosts'][0]),$question_time_IDS)) {
                        $qb_q_fields['time'] = $question_time_VALUES[0] ;
                    }
                    foreach ($qb_result['correct_sub_opt'] as $key_ID) {
                        if($qb_result['filteredPosts'][0] == $key_ID['ID']) {
                            $qb_q_fields['sub_answer'] = $key_ID['KEY'];
                        }
                    }
                    foreach ($qb_result['wrong_sub_opt'] as $key_ID) {
                        if($qb_result['filteredPosts'][0] == $key_ID['ID']) {
                            $qb_q_fields['sub_answer'] = $key_ID['KEY'];
                        }
                    }

                    $post = get_post( $qb_result['filteredPosts'][0] ) ;
                    ?> 
                    
                    <div class='qb_subs_exam_question_content'>
                        <div id='qb_question_head'>
                            <p id='qb_subs_exam_question_title'><?php echo esc_html($post->post_title) ; ?> <span class='qb_f_right'><?php echo $qb_q_fields['time'] ?></span></p>
                        </div>

                            <table class='qb_w_100'>

                                <tbody id='qb_questions_tbody_container' class='qb_questions_tbody_container'>

                                    <?php 
                                            
                                    foreach ($qb_q_fields['qb_question_options_field'] as $key => $value) {
                                        
                                        ?> 
                                        
                                        <tr class='qb_question_field_show_immed' id='qb_question_field_<?php echo intval($key)+1 ?>'  >
                                            <td class='<?php if ($key == $qb_q_fields['sub_answer'] ) echo "qb_subscriber_answer_sl" ?>'>
                                                <div style="display: flex;align-items: center;">
                                                <span class='alphaOptions <?php echo $qb_q_fields['qb_correct_field'] == "qb_c_option_".$key ? 'qb_green' : 'qb_red' ?>'><?php echo intval($key)+1 ?></span> <span style="flex-grow:1;"><?php echo preg_replace('/_/', ' ', esc_html($value) ) ; ?></span> 
                                                <?php echo $qb_q_fields['qb_correct_field'] == "qb_c_option_".$key ? '<span class="qb_immed_answer_is_true"> ✔ </span>' : '<span class="qb_immed_answer_is_false"> ✖ </span>' ?>
                                            </div></td>
                                        </tr>
                                        
                                        <?php
                                    }
                                    
                                    ?>

                                    
                            
                                </tbody>
                            </table>

                            <div class='qb_mb_20' id='qb_questions_explanation_immed' >
                                Explanation : 
                                <?php echo esc_html($qb_q_fields['qb_question_description_field']) ; ?>
                            </div>

                            <div class='qb_text_center'>
                                <?php 
                                
                                if(sizeof($qb_result['filteredPosts']) > 1) {
                                    ?> 
                                    
                                    <button id='qbPrevQuestion' class='button_background_color button_text_color <?php echo !$post ? "". sizeof($qb_result['solved_questions']) == 1 || $qb_question_IDS_or_number == '1' ? 'hide' : '' ."" : "hide" ?>' onclick="nxtrvwQuestion('<?php echo $post->ID ?>','prev','<?php echo $qb_detail['qb_answer_show_immed_field'] ?>','<?php echo $examID ?>')" >Previous</button>
                                    <button id='qbNxtQuestion' class='button_background_color button_text_color' onclick="nxtrvwQuestion('<?php echo $post->ID ?>' , '<?php echo sizeof($qb_result['solved_questions']) == 1 || $qb_question_IDS_or_number == '1'  ? 'show_result' : 'next' ?>' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examID ?>')">Next</button>

                                    <?php
                                }
                                
                                ?>
                                
                                <button id='qbExitExam' class='button_background_color button_text_color' onclick="exitExamFun()">Exit</button>
                            </div>

                    </div>
                    <?php

                }
                

            }
        } else {
            echo _e( "exam not completed.", "qb" ) ;
        }
    }

}