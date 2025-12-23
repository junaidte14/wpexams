<?php 

if(isset($_GET['id']) && isset($_GET['qb_exam_history'])) {

    $examID = $_GET['id'] ;

    // GET POST META => get_post_metas.php
    $qb_get_post_meta = new QB_Get_Post_Meta($examID) ;
    $qb_result = $qb_get_post_meta->qb_result ;
    $qb_detail = $qb_get_post_meta->qb_detail ;

    if($qb_result && $qb_detail) {

        $qb_question_IDS_or_number = array_key_exists("qb_q_numbers_field" , $qb_detail) ? $qb_detail['qb_q_numbers_field'] : count($qb_detail['qb_q_IDS']) ;

         $filterQuestions = array_filter($qb_result['correct_sub_opt'], function ($var) {
            return ($var['KEY'] != 'null');
        });


        $questionsFieldsArr = array() ;
        $question_time_IDS = array() ;
        $question_time_VALUES = array() ;
        if(array_key_exists('question_time', $qb_result)){
            foreach ($qb_result['question_time'] as $key => $value) {
                if(in_array($value['id'] , $qb_result['filteredPosts'])) {
                    
                    array_push($question_time_IDS , $value['id']) ;
                    array_push($question_time_VALUES , $value['time']) ;
    
                }
            }
        }

        ?>  
        <div class='qb_d_flex qb_m_tb_20' >
            <h5 class='qb_m_0'>Score <?php echo $filterQuestions ? count($filterQuestions) : 0  ?>/<?php echo $qb_result['total_questions'] ? $qb_result['total_questions'] : 0 ; ?></h5> 
            <span id='subscriber__exam_diff_time' class='<?php echo $qb_result['exam_time'] == "expired" ? "qb_d_none" : "" ?>'><?php echo $qb_detail['qb_timed_field'] == "1" ? "" : $qb_result['exam_time'] ; ?></span>
        </div>
          <?php

          if($qb_detail['qb_timed_field'] == "1" ) { 
                $caluculateTime = $get_time_from_setting * intval($qb_question_IDS_or_number) ;
            ?>  
            <script>


                let hrs = parseInt(convertSecondsToHms('<?php echo json_encode($caluculateTime) ?>')['hrs']) ;
                let min = parseInt(convertSecondsToHms('<?php echo json_encode($caluculateTime) ?>')['min']) ;
                let sec = parseInt(convertSecondsToHms('<?php echo json_encode($caluculateTime) ?>')['sec']) ;

                let time = (hrs<=9 ? "0" + hrs : hrs) + ":" + (min<=9 ? "0" + min : min) + ":" + (sec<=9 ? "0" + sec : sec) + "";
                // console.log(time) ;
                

                let calculatedExamTime = time ;
                let subscriberExamTime = <?php echo json_encode($qb_result['exam_time']) ; ?> ;

                examTimeDiff(calculatedExamTime, subscriberExamTime) ;

            </script>
            <?php

        }

        if($qb_result['filteredPosts']) {

            for ($i=0; $i < count($qb_result['filteredPosts']); $i++) { 

                $qb_get_post_meta_o2 = new QB_Get_Post_Meta($qb_result['filteredPosts'][$i]) ;
                $qb_q_fields = $qb_get_post_meta_o2->qb_q_fields ;

                $qb_q_fields = get_post_meta( $qb_result['filteredPosts'][$i] , 'qb_question_fields_meta_key', true ) ;
                // GET QUESTION TIME
                if(in_array(intval($qb_result['filteredPosts'][$i]),$question_time_IDS)) {
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
                                        <h3 class='qb_f_bold'><?php echo $qb_q_fields['qb_question_title'] == "" ? "no title" : esc_html($qb_q_fields['qb_question_title']) ; ?></h3>
                                        <p><span><?php echo $qb_q_fields['time'] ; ?></span> ⮟ </p>
                                    </div>
                                    <ul class="qb_m_0 qb_d_none">
                                        <li>
                                            <?php 
                                            foreach ($qb_q_fields['qb_question_options_field'] as $key => $value) {
                                                ?> 
                                                <a href="javascript:(0)" style="display: flex;align-items: center;" class="<?php if(array_key_exists("sub_answer" , $qb_q_fields)) if ( $qb_q_fields['sub_answer'] == $key ) echo "qb_subscriber_answer_sl" ?>">
                                                    <span class="alphaOptions <?php echo $qb_q_fields['qb_correct_field'] == "qb_c_option_".$key ? 'qb_green' : 'qb_red' ?>"><?php echo intval($key)+1 ?></span><span  style="flex-grow:1;"><?php echo preg_replace('/_/', ' ', esc_html($value)) ; ?></span>
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
        ?> 
        <div><?php echo _e("Exam history not exists." , "qb") ; ?></div>
        <?php
    }

} else {
	
	?>
		<p>
			History of exams that you have created.
		</p>
	<?php

    $ourCurrentHistoryPage = get_query_var( 'paged' ) ;
    $exargs = array(
        'post_type' => 'qb_exams' ,
        'author' => get_current_user_id() ,
        'meta_key' => 'qb_subscriber_exam_result_meta_key' ,
        'post_status' => 'publish' ,
    ) ;
    $allExams = new WP_Query( $exargs ) ;
    
    if($allExams->have_posts()) :
        ?>
    	
        <table class='qb_data_tabels'>
    
            <thead>
                <tr>
                    <th>Date</th>
                    <th>#Questions</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Score</th>
                </tr>
            </thead>
    
            <tbody> <?php

        while ($allExams->have_posts()) :
            $allExams->the_post() ;
            // GET POST META => get_post_metas.php
            $qb_get_post_meta = new QB_Get_Post_Meta(get_the_ID()) ;
            $qb_result = $qb_get_post_meta->qb_result ;
            $qb_detail = $qb_get_post_meta->qb_detail ;

            if($qb_result && array_key_exists("exam_status" , $qb_result) && $qb_detail) {
                if($qb_detail['qb_role'] == 'user_defined') {
					?>
                <tr>
                    <td data-label="Date"><?php echo get_the_date( 'Y-m-d' ) ; echo "/" ; the_time( 'H:i:s' ); ?></td>
                    <td data-label="#Questions"><?php echo array_key_exists("total_questions" , $qb_result) ? $qb_result['total_questions'] : "0"; ?></td>
                    <td data-label="Type"><?php echo $qb_detail['qb_role'] == 'admin_defined' ? 'Predefined' : "User Defined"; ?></td>
                    <td data-label="Status"><?php echo array_key_exists("exam_status" , $qb_result) ? $qb_result['exam_status'] : "" ; ?></td>
                    <?php 
                    
                    if(array_key_exists("correct_sub_opt" , $qb_result)) {

                        $filterQuestions = array_filter($qb_result['correct_sub_opt'], function ($var) {
                            return ($var != 'null');
                        });
                        
                    }
                    
                    ?>
                    <td data-label="Score" class='qb_text_center'>
                        <?php 
                        
                        if(array_key_exists("exam_status" , $qb_result) && $qb_result['exam_status'] == "pending") {
                            ?> 
                                <a href='?qb_subscriber_exam_ID=<?php echo get_the_ID() ?>' >Continue </a>
                            <?php
                        } else {
                            ?>

                                <a href='?qb_exam_history&id=<?php echo get_the_ID() ?>' >Score <?php echo array_key_exists("correct_sub_opt" , $qb_result) ? $filterQuestions ? count($filterQuestions) : 0 : 0 ; ?>/<?php echo  array_key_exists("total_questions" , $qb_result) ? $qb_result['total_questions'] : 0 ; ?></a>
                                <a href='?qb_exam_review_ID=<?php echo get_the_ID() ?>' >Review</a>
                            <?php
                        }
                        
                        ?>
                </td>
                </tr>
                <?php
	
				}
            }
        endwhile ;

        ?> </tbody>
    
        </table> <?php
    else :
         ?> <div class='qb_m_10'><p><?php _e("No exam history exists yet.") ; ?></p></div> <?php
    endif ;

}

?>