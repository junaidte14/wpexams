<?php 

$allQuestions = get_posts(array(
    'post_type' => 'qb_questions' ,
    'post_status' => 'publish' ,
    'posts_per_page' => '-1' ,
    'orderby' => 'ID',
    'order' => 'DESC',
));


// check questions exist or not
if($allQuestions) {

    // get categories 

    $qb_categories = get_categories( array(
        'orderby' => 'name',
        'order'   => 'ASC',
    ) );

    ?> 
    <form action="javascript:void(0)" id='qb_subscriber_exam_detail'>
        <p>The exam will be created from random questions.</p>

        <div class='qb_category_content' id='qb_category_content'> 

            <p>Category</p>

            <input type="checkbox" checked id='qb_all_1' value="-1">
            <label for="qb_all_1"> All <?php echo "(".count($allQuestions).")" ;?></label><br>

            <?php
            
            foreach ( $qb_categories as $qb_category ) {

                $catQuestions = get_posts(array(
                    'post_type' => 'qb_questions' ,
                    'category' =>  $qb_category->term_id,
					'posts_per_page' => '-1',
                    'post_status' => 'publish' ,
                    'orderby' => 'ID',
                    'order' => 'DESC'
                ));

                
                ?> 
                    <input type="checkbox" id="<?php echo $qb_category->term_id ; ?>" name="<?php echo $qb_category->term_id ; ?>" value="<?php echo $qb_category->term_id ; ?>">
                    <label for="<?php echo $qb_category->term_id ; ?>"> <?php echo $qb_category->name ; ?><?php echo $catQuestions ? " (".count($catQuestions).")" : " (0)" ; ?></label><br>
                <?php 
            }

            ?> 
            

        </div>

        <div class='qb_q_number_content'>

            <p class='qb_m_0'>Number Of Questions *</p>

            <input type="number" name="qb_q_numbers_field" max='44' min='1' class='qb_q_numbers_field' id="qb_q_numbers_field">
        
        </div>

        <div class='qb_timed_content qb_mt_15'>
            <input type="checkbox" value='1' name="qb_timed_field" id="qb_timed_field">
            <label for="qb_timed_field">Timed ?</label>
        </div>

        <div class='qb_answer_show_immed_content qb_mt_15'>
            
            <p class='qb_m_0'>All / Unused Questions *</p>
            <div>
                <input type="radio" checked="checked" name="qb_all_and_unused_field" value="0" /> All Questions
            </div>
            <div>
                <input type="radio" name="qb_all_and_unused_field" value="1" /> Unused Questions
            </div>
        </div>

        <div class='qb_answer_show_immed_content qb_mt_15'>
            
            <p class='qb_m_0'>View answer immediately after each question?</p>
            <div>
                <input type="radio" name="qb_answer_show_immed_field" value="1" /> Yes
            </div>
            <div>
                <input type="radio" checked="checked" name="qb_answer_show_immed_field" value="0" /> No
            </div>

        </div>

        <div><p id='qb_show_error_on_exam_creation'></p></div>

        <button class='qb_start_question_btn button_background_color button_text_color' type='button' data-id="<?php echo $current_user_id ?>" data-nonce="<?php echo wp_create_nonce('qb_save_exam_detail_nonce') ?>" id='qb_start_question_btn' >Start</button>
        
    </form>
    
    <?php 

} else {

    ?> <div><p><?php _e("No questions exist yet." , "qb") ; ?></p></div> <?php

}


