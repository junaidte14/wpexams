<?php 
// edit pending exam 
// GET POST META => get_post_metas.php
$qb_get_post_meta = new QB_Get_Post_Meta($examPost->ID) ;
$qb_result = $qb_get_post_meta->qb_result ;
$qb_detail = $qb_get_post_meta->qb_detail ;

if($qb_result) {
    $qb_question_IDS_or_number = array_key_exists("qb_q_numbers_field" , $qb_detail) ? $qb_detail['qb_q_numbers_field'] : count($qb_detail['qb_q_IDS']) ;
    if($qb_result['exam_status'] == "completed") {
        
         $filterQuestions = array_filter($qb_result['correct_sub_opt'], function ($var) {
            return ($var['KEY'] != 'null');
        });

        $questionsFieldsArr = array() ;
        $question_time_IDS = array() ;
        $question_time_VALUES = array() ;

        // ADD TIME TO QUESTION OPTION FIELDS
        if(array_key_exists("question_time" , $qb_result)) {
            
            foreach ($qb_result['question_time'] as $key => $value) {
                if(in_array($value['id'] , $qb_result['solved_questions'])) {
                    array_push($question_time_IDS , $value['id']) ;
                    array_push($question_time_VALUES , $value['time']) ;
                }
            }

        }        

        ?>  
        <div class='qb_d_flex qb_m_tb_20'>
            <h5 class='qb_m_0'>Score <?php echo $filterQuestions ? count($filterQuestions) : 0  ?>/<?php echo $qb_result['total_questions'] ? $qb_result['total_questions'] : 0 ; ?></h5> 
            <span><?php echo $qb_result['exam_time'] ; ?></span>
        </div>
        <?php
        if($qb_result['solved_questions']) {

            for ($i=0; $i < count($qb_result['solved_questions']); $i++) { 

               $qb_get_post_meta_o2 = new QB_Get_Post_Meta($qb_result['solved_questions'][$i]) ;
               $qb_q_fields = $qb_get_post_meta_o2->qb_q_fields ;

                if(in_array(intval($qb_result['solved_questions'][$i]),$question_time_IDS)) {
                    $qb_q_fields['time'] = $question_time_VALUES[$i] ;
                }

                foreach ($qb_result['correct_sub_opt'] as $key_ID) {
                    if($qb_result['filteredPosts'][$i] == $key_ID['ID']) {
                        $qb_q_fields['sub_answer'] = $key_ID['KEY'];
                    }
                }
                foreach ($qb_result['wrong_sub_opt'] as $key_ID) {
                    if($qb_result['filteredPosts'][$i] == $key_ID['ID']) {
                        $qb_q_fields['sub_answer'] = $key_ID['KEY'];
                    }
                }
                ?> 
        
                <div class="qb_subscriber_exam_result show" id="qb_subscriber_exam_result">
                    <?php 
                    if($qb_q_fields) {
                        ?> 
                        <div id="qbAccordian">
                            <ul class="qb_m_0">
                                <li>
                                    <div class="qb_result_dp_header_stl">
                                        <h3><?php echo $qb_q_fields['qb_question_title'] == "" ? "no title" : esc_html($qb_q_fields['qb_question_title']) ; ?></h3>
                                        <p><span><?php echo array_key_exists("time" , $qb_q_fields) ? $qb_q_fields['time'] : "00:00:00" ; ; ?></span> ⮟ </p>
                                    </div>
                                    <ul class="qb_m_0 qb_d_none">
                                        <li>
                                            <?php 
                                            
                                            foreach ($qb_q_fields['qb_question_options_field'] as $key => $value) {
                                                ?> 
                                                
                                                <a href="javascript:(0)" style="display: flex;align-items: center;" class="<?php if(array_key_exists("sub_answer" , $qb_q_fields)) if ( $qb_q_fields['sub_answer'] == $key ) echo "qb_subscriber_answer_sl" ?>">
                                                    <span class="alphaOptions <?php echo $qb_q_fields['qb_correct_field'] == "qb_c_option_".$key ? 'qb_green' : 'qb_red' ?>"><?php echo intval($key)+1 ?></span><span style="flex-grow:1;"><?php echo preg_replace('/_/', ' ', esc_html($value)) ; ?></span>
                                                    <?php echo $qb_q_fields['qb_correct_field'] == "qb_c_option_".$key ? '<span class="qb_immed_answer_is_true"> ✔ </span>' : '<span class="qb_immed_answer_is_false"> ✖ </span>' ?>
                                                </a>
                                                
                                                <?php
                                            }
                                            
                                            ?>
                                            
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <?php
                    }
                    
                    ?>
                    
                </div>
                
                <?php
            }

        }
    } else {

        // validate subscriber and post meta exists or not
        if($examPost->post_author == $current_user_id && $qb_detail) { 
            $subsicriberQuestions = $qb_result['filteredPosts'] ;
            if(count($subsicriberQuestions) != 0) {
                
                // last post id end($qb_result['solved_questions']) ;
    
                if(array_key_exists("exitQuestion" , $qb_result) ) {
                    $subscriberLastPostID = array_search($qb_result['exitQuestion'] , $qb_result['filteredPosts']) ;
                    $next_question_for_solve = $qb_result['exitQuestion'] ;
                } else {
    
                    $subscriberLastPostID = end($qb_result['solved_questions']) ;
                    
                    $current_array_val = array_search($subscriberLastPostID, $qb_result['filteredPosts']);
                        
                    $next_question_for_solve =  $qb_result['filteredPosts'][$current_array_val+1]; 
                
                }
    
                $subscriberPostForSolve = get_post( $next_question_for_solve ) ;
    
                if($subscriberPostForSolve) {
                    

                    if($qb_detail['qb_timed_field'] == "0") {
                        $exam_time = $qb_result['exam_time'] ;
                        ?> 

                       <script>
                           const time = <?php echo json_encode($exam_time) ?>;
                            const [hrs, min, sec] = time.split(':');
                            UntimedQuizCountDownTimer(hrs,min,sec,"Untimedquiztimer");
                        </script>
                        
                        <?php
                    }
                    if($qb_detail['qb_timed_field'] == "1" && array_key_exists("exam_time" , $qb_result)) {
                        $exam_time = $qb_result['exam_time'] ;

                        ?> 
                        <script>
                            const time = <?php echo json_encode($exam_time) ?>;
                            const [hrs, min, sec] = time.split(':');
                            timedQuizCountDownTimer(hrs,min,sec,"timedquiztimer");
                        </script>
                        <?php
                    }

                    ?>

                    
                     
                    
                    <div class='qb_subs_exam_question_content_main' class='qb_w_100' >
                         <div class='qb_text_center qb_f_bold qb_d_flex'>
                                <h5>Exams - <?php echo $qb_question_IDS_or_number ; ?> Questions</h5>
                                <h5 class='qb_d_flex'>
                                    <span class='qb_d_none qb_c_pointer qb_mr_15' id='qb_startTimer' onclick="startTimer('<?php echo $qb_detail['qb_timed_field'] == '1' ? 'timedquiztimer' : 'Untimedquiztimer' ?>','questiontimer')">▶</span>
                                    <span class='qb_mr_15 qb_c_pointer' id='qb_pauseTimer' onclick="pauseTimer('<?php echo $qb_detail['qb_timed_field'] == '1' ? 'timedquiztimer' : 'Untimedquiztimer' ?>','questiontimer')">❚❚</span>
                                    <div id="qb_exam_timer"></div>
                                </h5>
                        </div>
                        
                        <?php 
                        
                        $qb_general_options = get_option('qb_general_options');
                        if($qb_general_options) {
                            
                            ?> 
                            <div class='qb_subs_exam_progress <?php echo $qb_general_options['qb_progressbar_option'] == "1" ? "".sizeof($subsicriberQuestions) == 1 || $qb_question_IDS_or_number == '1'  ? 'qb_d_none' : 'qb_d_block'."" : "qb_d_none" ?>' >
                                <p class='qb_m_0'><span class='qb_question_progress_nb'>1/<?php echo $qb_question_IDS_or_number ; ?></span> Questions : <span class="percentage">0%</span></p>
                                <div class="progress-container" data-percentage='0'>
                                    <div class="progress progressbar_background_color"></div>
                                </div>
                            </div>
                            <?php
    
                        } else {
                            ?> 
                            <div class='qb_subs_exam_progress <?php echo sizeof($subsicriberQuestions) == 1 || $qb_question_IDS_or_number == '1'  ? 'qb_d_none' : 'qb_d_block' ?>'>
                            <p class='qb_m_0'><span class='qb_question_progress_nb'>1/<?php echo $qb_question_IDS_or_number ; ?></span> Questions : <span class="percentage">0%</span></p>
                                <div class="progress-container" data-percentage='0'>
                                    <div class="progress progressbar_background_color"></div>
                                </div>
                            </div>
                            <?php
                        }
                        
                        ?>
                        <div class='qb_subs_exam_question_content'>
                            <div id='qb_question_head' >
                                <p id='qb_subs_exam_question_title'><?php echo esc_html($subscriberPostForSolve->post_title) ; ?> <span class='qb_f_right'  id="qb_question_timer"></span></p>
                            </div>
    
                             <form action="javascript:void(0)">
    
                                <!-- question options meta key  -->
                                <?php 
                                $qb_get_post_meta_o2 = new QB_Get_Post_Meta($subscriberPostForSolve->ID) ;
                                $qb_q_fields = $qb_get_post_meta_o2->qb_q_fields ;
                                
                                if($qb_q_fields) {
                                    
                                    ?> 
                                    <table class='qb_w_100'>

                                        <tbody id='qb_questions_tbody_container' class='qb_questions_tbody_container'>

                                            <?php include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/exam_result.php'); ?>
                                            
                                             <?php 
                                        
                                            foreach ($qb_q_fields['qb_question_options_field'] as $key => $value) {
                                                ?> 
                                                
                                                <tr>
                                                    <td><label for="qb_question_option<?php echo intval($key)+1; ?>"><div><span class='alphaOptions'><?php echo intval($key)+1; ?></span> <input id='qb_question_option<?php echo intval($key)+1; ?>'  type="radio" name="qb_question_options" value="<?php echo "qb_c_option_".$key ?>" /> <?php echo preg_replace('/_/', ' ', esc_html($value) ) ?></div></lable></td>
                                                </tr>
                                                
                                                <?php
                                            }
                                            

                                            ?>
                                    
                                    
                                        </tbody>
                                    </table>

                                    <div class='hide qb_mb_20' id='qb_questions_explanation_immed' >
                                        Explanation : 
                                        
                                    </div>
                                    <?php
    
    
                                }

                                    if($qb_detail['qb_answer_show_immed_field'] == "1") {
                                        ?> 
                                            <div class='qb_text_left'>
                                                <button id='qbSubmitQuestion' class='button_background_color button_text_color' onclick='subscriber_answer("<?php echo $subscriberPostForSolve->ID ; ?>" , "<?php echo $examPost->ID ; ?>")'>Submit</button>
                                            </div>
                                         <?php
                                    }
                                    
                                    ?>
                                <div class='qb_text_right'>
                                    <button id='qbPrevQuestion' class='button_background_color button_text_color <?php echo $subsicriberQuestions ? "". sizeof($subsicriberQuestions) == 1 || $qb_question_IDS_or_number == '1' ? 'hide' : '' ."" : "hide" ?> ' onclick="nxtQuestion('<?php echo $subscriberPostForSolve->ID ?>' , 'prev' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>' )">Previous</button>
                                    <button id='qbNxtQuestion' class='button_background_color button_text_color <?php echo $qb_detail['qb_answer_show_immed_field'] == "1" ? "hide" : "" ?>' onclick="nxtQuestion('<?php echo $subscriberPostForSolve->ID ?>' , '<?php echo sizeof($subsicriberQuestions) == 1 || $qb_question_IDS_or_number == '1'  ? 'show_result' : 'next' ?>' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>')"><?php echo sizeof($subsicriberQuestions) == 1 || $qb_question_IDS_or_number == '1' || $subscriberPostForSolve->ID == end($qb_result['filteredPosts']) ? 'Show Result' : 'Next' ?></button>
                                    <button id='qbExitExam' class='button_background_color button_text_color' onclick="nxtQuestion('<?php echo $subscriberPostForSolve->ID ?>' , 'exitExam' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>')">Exit</button>
                                </div>
                            </form>
                        </div>
                    </div> 
    
                    <script type="text/javascript">
                        let allPostaID = <?php echo json_encode($subsicriberQuestions) ?>;
                        let currentID = <?php echo json_encode($subscriberLastPostID); ?>;
                        let nextID = <?php echo json_encode($next_question_for_solve); ?>;
                        // get data for calculate percentage
                        getPercentage(currentID, allPostaID , nextID);
                        
                    </script>
    
                    <?php
    
                }
    
    
            }
    
        }
    
    }
   
}

