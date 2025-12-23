<?php

$postID = $_GET['qb_subscriber_exam_ID'] ;

// Exam Post

$examPost = get_post( $postID ) ;

// check if post exists or not
if($examPost) {
    include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/common/get_all_admins.php');
    // GET ALL ADMINS
    $qb_AdminIdArray = qb_admin_user_ids();

    // GET POST META => get_post_metas.php
    $qb_get_post_meta = new QB_Get_Post_Meta($examPost->ID) ;
    $qb_result = $qb_get_post_meta->qb_result ;
    $qb_detail = $qb_get_post_meta->qb_detail ;

    // if exam result pending then continue exam otherwise start new exam
    if($qb_result && array_key_exists("solved_questions" , $qb_result) && $qb_detail['qb_role'] != "admin_defined" && $qb_result['qb_user_id'] == get_current_user_id()) {
        include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_shortcodes/exam_edit.php');
    } else {
        $qb_question_IDS_or_number = array_key_exists("qb_q_numbers_field" , $qb_detail) ? $qb_detail['qb_q_numbers_field'] : count($qb_detail['qb_q_IDS']) ;
        // validate subscriber and post meta exists or not
        if(($examPost->post_author == $current_user_id || in_array($examPost->post_author , $qb_AdminIdArray)) && $qb_detail && array_key_exists("qb_role" , $qb_detail)) {
            
            if( sizeof($qb_detail['filteredPosts']) == 0 && $qb_detail['qb_all_and_unused_field'] == "1") {
                return _e("No unused questions exist." , "qb") ;
            }
            
            // Subscriber exam start
            if( $qb_detail['qb_role'] == 'user_defined' ) {

                $qb_questions = get_posts(array(
                    'post_type' => 'qb_questions' ,
                    'category' => $qb_detail['qb_category_field'] ,
                    'post_status' => 'publish' ,
                    'post_par_page' => $qb_question_IDS_or_number ,
                    'orderby' => 'ID',
                    'order' => 'DESC'
                ));

            } 

            if( $qb_detail['qb_role'] == 'admin_defined' ) {

                $qb_questions = get_posts(array(
                    'post_type' => 'qb_questions' ,
                    'post__in' => $qb_detail['qb_q_IDS'], // array
                    'orderby' => 'ID',
                    'order' => 'ASC'
                ));
                
            }
                
            if($qb_detail['qb_timed_field'] == "0") {
                ?> 
                <script>
                    UntimedQuizCountDownTimer(0,0,0,"Untimedquiztimer");
                </script>
                <?php
            
            } 
            if($qb_detail['qb_timed_field'] == "1" ) {
                $caluculateTime = $get_time_from_setting * intval($qb_question_IDS_or_number) ;

                ?> 
                <script>
                    let hrs = parseInt(convertSecondsToHms(<?php echo json_encode($caluculateTime) ?>)['hrs']) ;
                    let min = parseInt(convertSecondsToHms(<?php echo json_encode($caluculateTime) ?>)['min']) ;
                    let sec = parseInt(convertSecondsToHms(<?php echo json_encode($caluculateTime) ?>)['sec']) ;
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
            if ($qb_questions) {
                // filtered unused questions
                if($qb_detail['qb_all_and_unused_field'] == "1") {

                    $filteredQuestionsPost = get_post( $qb_detail['filteredPosts'][0] ) ;
                            
                    $qb_general_options = get_option('qb_general_options');
                    if($qb_general_options) {
                        
                        ?> 
                        <div class='qb_subs_exam_progress <?php echo $qb_general_options['qb_progressbar_option'] == "1" ? "".sizeof($qb_detail['filteredPosts']) == 1 || $qb_question_IDS_or_number == '1'  ? 'qb_d_none' : 'qb_d_block'."" : "qb_d_none" ?>' >
                            <p class='qb_m_0'><span class='qb_question_progress_nb'>1/<?php echo $qb_question_IDS_or_number ; ?></span> Questions : <span class="percentage">0%</span></p>
                            <div class="progress-container" data-percentage='0'>
                                <div class="progress progressbar_background_color"></div>
                            </div>
                        </div>
                        <?php

                    } else {
                        ?> 
                        <div class='qb_subs_exam_progress <?php echo sizeof($qb_detail['filteredPosts']) == 1 || $qb_question_IDS_or_number == '1'  ? 'qb_d_none' : 'qb_d_block' ?>'>
                        <p class='qb_m_0'><span class='qb_question_progress_nb'>1/<?php echo $qb_question_IDS_or_number ; ?></span> Questions : <span class="percentage">0%</span></p>
                            <div class="progress-container" data-percentage='0'>
                                <div class="progress progressbar_background_color"></div>
                            </div>
                        </div>
                        <?php
                    }
                    
                    ?>
            
                    <div class='qb_subs_exam_question_content'>
                        <div id='qb_question_head'>
                            <p id='qb_subs_exam_question_title'><?php echo esc_html($filteredQuestionsPost->post_title); ?> <span class='qb_f_right'  id="qb_question_timer"></span></p>
                        </div>

                        <form action="javascript:void(0)">

                            <!-- question options meta key  -->
                            <?php 
                            
                            $qb_get_post_meta_o2 = new QB_Get_Post_Meta($filteredQuestionsPost->ID) ;
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

                            ?>
                            <?php 
                                
                            if($qb_detail['qb_answer_show_immed_field'] == "1") {
                                ?> 
                                <div class='qb_text_right'>
                                    <button id='qbSubmitQuestion' class='button_background_color button_text_color' onclick='subscriber_answer("<?php echo $filteredQuestionsPost->ID ; ?>" , "<?php echo $examPost->ID ; ?>")'>Submit</button>
                                </div>
                                <?php
                            }
                                
                            ?>
                            
                            <div class='qb_text_center'>
                                <button id='qbPrevQuestion' class='button_background_color button_text_color <?php echo !$qb_detail['filteredPosts'] ? "". sizeof($qb_detail['filteredPosts']) == 1 || $qb_question_IDS_or_number == '1' ? 'hide' : '' ."" : "hide" ?> ' onclick="nxtQuestion('<?php echo $filteredQuestionsPost->ID ?>' , 'prev' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>' )">Previous</button>
                                <button id='qbNxtQuestion' class='button_background_color button_text_color <?php echo $qb_detail['qb_answer_show_immed_field'] == "1" ? "hide" : "" ?>' onclick="nxtQuestion('<?php echo $filteredQuestionsPost->ID ?>' , '<?php echo sizeof($qb_detail['filteredPosts']) == 1 || $qb_question_IDS_or_number == '1'  ? 'show_result' : 'next' ?>' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>')"><?php echo sizeof($qb_detail['filteredPosts']) == 1 || $qb_question_IDS_or_number == '1' ? 'Show Result' : 'Next' ?></button>
                                <button id='qbExitExam' class='button_background_color button_text_color' onclick="nxtQuestion('<?php echo $filteredQuestionsPost->ID ?>' , 'exitExam' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>')">Exit</button>
                            </div>
                        </form>

                    </div>
                    
                    <?php



                } else {

                    $qb_general_options = get_option('qb_general_options');
                    if($qb_general_options) {

                        ?> 
                        <div class='qb_subs_exam_progress <?php echo $qb_general_options['qb_progressbar_option'] == "1" ? "".sizeof($qb_questions) == 1 || $qb_question_IDS_or_number == '1'  ? 'qb_d_none' : 'qb_d_block'."" : "qb_d_none" ?>' >
                            <p class='qb_m_0'><span class='qb_question_progress_nb'>1/<?php echo $qb_question_IDS_or_number ; ?></span> Questions : <span class="percentage">0%</span></p>
                            <div class="progress-container" data-percentage='0'>
                                <div class="progress progressbar_background_color"></div>
                            </div>
                        </div>
                        <?php
                        
                    } else {
                        ?> 
                        <div class='qb_subs_exam_progress <?php echo sizeof($qb_questions) == 1 || $qb_question_IDS_or_number == '1'  ? 'qb_d_none' : 'qb_d_block' ?>'>
                            <p class='qb_m_0'><span class='qb_question_progress_nb'>1/<?php echo $qb_question_IDS_or_number ; ?></span> Questions : <span class="percentage">0%</span></p>
                            <div class="progress-container" data-percentage='0'>
                                <div class="progress progressbar_background_color"></div>
                            </div>
                        </div>
                        <?php
                    }
                    ?> 
                    
                    <div class='qb_subs_exam_question_content'>
                        <div id= 'qb_question_head'>
                            <p id='qb_subs_exam_question_title'><?php echo esc_html($qb_questions[0]->post_title) ; ?> <span class='qb_f_right'  id="qb_question_timer"></span></p>
                        </div>

                        <form action="javascript:void(0)">

                            <!-- question options meta key  -->
                            <?php 

                            $qb_get_post_meta_o2 = new QB_Get_Post_Meta($qb_questions[0]->ID) ;
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

                            ?>
                            <?php 
                            if($qb_detail['qb_answer_show_immed_field'] == "1") {
                                ?>
                                <div class='qb_text_left'> 
                                    <button id='qbSubmitQuestion' class='button_background_color button_text_color' onclick='subscriber_answer("<?php echo $qb_questions[0]->ID ; ?>" , "<?php echo $examPost->ID ; ?>")'>Submit</button>
                                </div>
                                <?php
                            }
                            ?>
                        
                        <div class='qb_text_right'>
                            <button id='qbPrevQuestion' class='button_background_color button_text_color <?php echo !$qb_questions[0] ? "". sizeof($qb_questions) == 1 || $qb_question_IDS_or_number == '1' ? 'hide' : '' ."" : "hide" ?> ' onclick="nxtQuestion('<?php echo $qb_questions[0]->ID ?>' , 'prev' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>' )">Previous</button>
                            <button id='qbNxtQuestion' class='button_background_color button_text_color <?php echo $qb_detail['qb_answer_show_immed_field'] == "1" ? "hide" : "" ?>' onclick="nxtQuestion('<?php echo $qb_questions[0]->ID ?>' , '<?php echo sizeof($qb_questions) == 1 || $qb_question_IDS_or_number == '1'  ? 'show_result' : 'next' ?>' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>')"><?php echo sizeof($qb_questions) == 1 || $qb_question_IDS_or_number == '1'  ?  'Show Result' : 'Next' ?></button>
                            <button id='qbExitExam' class='button_background_color button_text_color' onclick="nxtQuestion('<?php echo $qb_questions[0]->ID ?>' , 'exitExam' , '<?php echo $qb_detail['qb_answer_show_immed_field'] ?>' , '<?php echo $examPost->ID ?>')">Exit</button>
                        </div>

                        </form>

                    </div>
                    
                    <?php

                }


            } else {

                ?> <div class='qb_m_10'><p><?php _e("No questions exist yet." , "qb") ; ?></p></div> <?php

            }

            ?>  
        </div>
        <?php
    }

    ?>
        <script>
            const progressContainer = document.querySelector('.qb_subs_exam_progress');

            // initial call
            setPercentage();

            function setPercentage() {
            const percentage = document.querySelector(".progress-container").getAttribute('data-percentage') + '%';
            
            const progressEl = progressContainer.querySelector('.progress');
            const percentageEl = progressContainer.querySelector('.percentage');
            
            progressEl.style.width = percentage;
            percentageEl.innerText = percentage;
            percentageEl.style.left = percentage;

            }

        </script>

        <?php

    }


} else {
   echo _e("Exam not exists." , "qb") ;
}
