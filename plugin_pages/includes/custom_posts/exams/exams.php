<?php 

/*create custom post type questions_bank*/
if(!function_exists("create_post_questions_bank_exams")) :
    function create_post_questions_bank_exams() {
        register_post_type( 'qb_exams',
            array(
                'labels' => array(
                    'name' => __('Exams', 'qb'),
                    'singular_name' => __('Exam', 'qb'),
                    'add_new' => __('Add New', 'qb'),
                    'add_new_item' => __('Add New Exam', 'qb'),
                    'edit' => __('Edit', 'qb'),
                    'edit_item' => __('Edit Exam', 'qb'),
                    'new_item' => __('New Exam', 'qb'),
                    'view' => __('View', 'qb'),
                    'view_item' => __('View Exam', 'qb'),
                    'search_items' => __('Search Exams', 'qb'),
                    'not_found' => __('No Exam found', 'qb'),
                    'not_found_in_trash' => __('No Exams found in Trash', 'qb'),
                    'parent' => __('Parent Exam', 'qb')
                ),
    
                'public' => true,
                'supports' => array( 'title'),
                'taxonomies' => array( '' ),
                'has_archive' => true,
                'show_in_menu' => false,
            )
        );
    }

    add_action( 'init', 'create_post_questions_bank_exams' );


    function qb_exam_metaboxes() {

        add_meta_box(
            'qb_exam_detail_box_id',                 // Unique ID
            'Exam Detail',      // Box title
            "qb_exam_detail_box_html",  // Content callback, must be of type callable
            'qb_exams' , 'normal', 'high'                          // Post type
        );

    }

    

    function qb_exam_detail_box_html( $post ) {

        wp_enqueue_style('qb_exams_enqueue_questions_style' ,  QUESTIONS_BANK_PLUGIN_URL.'assets/css/qb-exams-style.css' ,  true);

        $qb_categories = get_categories( array(
            'orderby' => 'name',
            'order'   => 'ASC',
        ) );

        $randomQuestions = get_posts(array(
            'post_type' => 'qb_questions' ,
            'post_status' => 'publish' ,
            'orderby' => 'ID',
            'order' => 'DESC',
        ));

        $allQuestions = get_posts(array(
            'post_type' => 'qb_questions' ,
            'posts_per_page' => '-1' ,
            'post_status' => 'publish' ,
            'orderby' => 'ID',
            'order' => 'DESC'
        ));

        // GET POST META => get_post_metas.php
        $qb_get_post_meta = new QB_Get_Post_Meta($post->ID) ;
        $qb_detail = $qb_get_post_meta->qb_detail ;

        if($qb_detail) {

            // CATEGORY 
            ?> 
            
            <div class='qb_category_content' id='qb_category_content'> 

                <p class='qb_f_bold'>Question Category</p>

                <input type="checkbox" disabled name='qb_category_field[]' <?php echo in_array("-1" , $qb_detail['qb_category_field']) ? "checked" : "" ?> id='qb_all_1' value="-1">
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
                        <input type="checkbox" disabled <?php echo in_array($qb_category->term_id , $qb_detail['qb_category_field']) ? "checked" : "" ?> id="<?php echo $qb_category->term_id ; ?>" name='qb_category_field[]' value="<?php echo $qb_category->term_id ; ?>">
                        <label for="<?php echo $qb_category->term_id ; ?>"> <?php echo $qb_category->name ; ?><?php echo $catQuestions ? " (".count($catQuestions).")" : " (0)" ; ?></label><br>
                    <?php 
                }

                ?> 
                

            </div>
            
            <?php 
            
            if( array_key_exists("qb_q_numbers_field" , $qb_detail) ) {
                ?> 
                
                <!-- Number Of Questions  -->
                <div class='qb_q_number_content qb_mt_15'>

                    <p class='qb_m_0 qb_f_bold'>Number Of Questions *</p>

                    <input type="number" name="qb_q_numbers_field" value='<?php echo $qb_detail['qb_q_numbers_field'] ?>' max='44' min='1' class='qb_q_numbers_field qb_w_100' id="qb_q_numbers_field">
                
                </div>
                
                <?php
            }

            if(array_key_exists("qb_q_IDS" , $qb_detail)) {
                ?> 
                
                <!-- QUESTIONS  -->
                <div class='qb_q_number_content qb_mt_15'>
                    <p class='qb_m_0 qb_f_bold'>Add Questions *</p>
                    <div class='qb_question_content'>
                    <?php
                    foreach ($qb_detail['qb_q_IDS'] as $qb_q_IDS) {
                            $e_post = get_post( $qb_q_IDS ) ;
                            ?>

                            <div class='qb_add_questions qb_mt_5'>
                                <input type="hidden" value='<?php echo $qb_q_IDS ?>' name='qb_q_IDS[]' class='qb_q_IDS' id="qb_q_IDS">
                                <input type="text" value='<?php echo esc_html($e_post ? $e_post->post_title : "", ENT_QUOTES, "UTF-8"); ?>' class='qb_w_100 qb_q_IDS_rl' >
                                <div class='qb_q_search_content qb_close_dropdown' id='qb_q_search_content'>
            
                                </div>
                                <div class='qb_mt_5'>
                                    <a class="button  qb_admin_question_delete_btn" href="javascript:;">Delete</a>
                                </div>
                            </div>
                                
                            <?php
                    }
                    ?>
                    </div>
                </div>
                
                <div style='text-align:right;margin-top:10px;'>
                    <button type='button' class='button qb_admin_admin_exam_add_new_question'> Add New Question</button>
                </div>
                
                <?php

            }
            
            ?>
            
            <!-- TIMED  -->
            <div class='qb_timed_content qb_mt_15'>
                <input type="checkbox" value='1' <?php echo $qb_detail['qb_timed_field'] == "1" ? "checked" : "" ?> name="qb_timed_field" id="qb_timed_field">
                <label for="qb_timed_field qb_f_bold">Timed ?</label>
            </div>

            <!-- USED AND UNUSED -->
            <div class='qb_answer_show_immed_content qb_mt_15'>
            
                <p class='qb_m_0 qb_f_bold'>All / Unused *</p>
                <div>
                    <input type="radio" disabled <?php echo $qb_detail['qb_all_and_unused_field'] == "0" ? "checked" : "" ?> value="0" /> All Questions
                </div>
                <div>
                    <input type="radio" disabled <?php echo $qb_detail['qb_all_and_unused_field'] == "1" ? "checked" : "" ?> value="1" /> Unused Questions
                </div>

            </div>
            
            <!-- ANSWER IMMEDIATELY -->
            <div class='qb_answer_show_immed_content qb_mt_15'>
            
                <p class='qb_m_0 qb_f_bold'>View answer immediately after each question?</p>
                <div>
                    <input type="radio" <?php echo $qb_detail['qb_answer_show_immed_field'] == "1" ? "checked" : "" ?> name="qb_answer_show_immed_field[]" value="1" /> Yes
                </div>
                <div>
                    <input type="radio" <?php echo $qb_detail['qb_answer_show_immed_field'] == "0" ? "checked" : "" ?> name="qb_answer_show_immed_field[]" value="0" /> No
                </div>

            </div>
            <?php
        } else {
           
            if(!is_admin()) {
                return _e("Exam detail not exists!" , "qb") ;
            }
            
            ?> 
            
            <!-- QUESTIONS  -->
            <div class='qb_q_number_content qb_mt_15'>
                <p class='qb_m_0 qb_f_bold'>Add Questions *</p>
                <div class='qb_question_content'>
                    <div class='qb_add_questions qb_mt_5'>
                        <input type="hidden" name='qb_q_IDS[]' class='qb_q_IDS qb_w_100' id="qb_q_IDS">
                        <input type="text" class='qb_w_100 qb_q_IDS_rl' >
                        <div class='qb_q_search_content qb_close_dropdown'>
    
                        </div>
                        <div class='qb_mt_5'>
                            <a class="button  qb_admin_question_delete_btn" href="javascript:;">Delete</a>
                        </div>
                    </div>
                
                </div>
                
            </div>

            <div style='text-align:right;margin-top:10px;'>
                <button type='button' class='button qb_admin_admin_exam_add_new_question'> Add New Question</button>
            </div>
            
            <!-- TIMED  -->
            <div class='qb_timed_content qb_mt_15'>
                <input type="checkbox" value='1' name="qb_timed_field" id="qb_timed_field">
                <label for="qb_timed_field qb_f_bold">Timed ?</label>
            </div>
            
            <!-- ANSWER IMMEDIATELY -->
            <div class='qb_answer_show_immed_content qb_mt_15'>
            
                <p class='qb_m_0 qb_f_bold'>View answer immediately after each question?</p>
                <div>
                    <input type="radio" name="qb_answer_show_immed_field[]" value="1" /> Yes
                </div>
                <div>
                    <input type="radio" checked name="qb_answer_show_immed_field[]" value="0" /> No
                </div>

            </div>
            <?php
        }
        ?> 
        
        <!-- JQUERY HIDDEN  -->
         <div class='qb_add_questions_hidden qb_mt_5 screen-reader-text'>
            <input type="hidden" name='qb_q_IDS[]' class='qb_q_IDS qb_w_100' id="qb_q_IDS">
             <input type="text" class='qb_w_100 qb_q_IDS_rl' >
            <div class='qb_q_search_content qb_close_dropdown'>

            </div>
            <div class='qb_mt_5'>
                <a class="button  qb_admin_question_delete_btn" href="javascript:;">Delete</a>
            </div>
        </div>

        <?php

    }

    add_action( 'add_meta_boxes' , "qb_exam_metaboxes" ) ;

    // SAVE META DATA
    function qb_save_exam_metaboxes_data( $post_id ) {
        // Check if this is an autosave or a revision
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        // Check if this is the correct post type
        if (get_post_type($post_id) !== 'qb_exams') {
            return;
        }
    
        // Ensure we're in an admin context
        if (!is_admin()) {
            return;
        }
    
        // Validate required fields
        if (
            !isset($_POST['qb_answer_show_immed_field']) || empty($_POST['qb_answer_show_immed_field']) ||
            !isset($_POST['qb_q_IDS']) || !is_array($_POST['qb_q_IDS']) || empty($_POST['qb_q_IDS'][0])
        ) {
            // Prevent update and show error message
            add_filter('redirect_post_location', function($location) {
                return add_query_arg('qb_exam_error', '1', $location);
            });
            return;
        }
    
        $qb_new_array = array();
        $filteredPosts = array();
    
        $qb_new_array['qb_category_field'] = ['-1'];
    
        if (isset($_POST['qb_q_numbers_field'])) {
            $qb_new_array['qb_q_numbers_field'] = $_POST['qb_q_numbers_field'];
        }
    
        if (isset($_POST['qb_q_IDS']) && is_array($_POST['qb_q_IDS'])) {
            $array_qb_q_IDS = $_POST['qb_q_IDS'];
            $new_array_contents = array_filter($array_qb_q_IDS, fn($id) => !empty($id));
    
            $qb_new_array['qb_q_IDS'] = array_unique($new_array_contents);
            $filteredPosts['filteredPosts'] = array_unique($new_array_contents);
            $qb_new_array['filteredPosts'] = array_unique($new_array_contents);
        }
    
        sort($qb_new_array['qb_q_IDS']);
        sort($filteredPosts['filteredPosts']);
        sort($qb_new_array['filteredPosts']);
    
        $qb_new_array['qb_timed_field'] = empty($_POST['qb_timed_field']) ? "0" : $_POST['qb_timed_field'];
        $filteredPosts['qb_user_id'] = get_current_user_id();
        $qb_new_array['qb_user_id'] = get_current_user_id();
        $qb_new_array['qb_role'] = "admin_defined";
        $qb_new_array['qb_all_and_unused_field'] = "0";
        $qb_new_array['qb_answer_show_immed_field'] = $_POST['qb_answer_show_immed_field'][0];
    
        if (!empty($qb_new_array)) {
            update_post_meta($post_id, 'qb_subscriber_exam_result_meta_key', $filteredPosts);
            update_post_meta($post_id, 'qb_subscriber_exam_detail_meta_key', $qb_new_array);
        }
    }

    add_action( 'save_post' ,  'qb_save_exam_metaboxes_data') ;

    // Show error message if at least one question ID is not provided
    add_action('admin_notices', function() {
        global $pagenow, $post;
        
        if ($pagenow === 'post.php' && isset($_GET['qb_exam_error']) && $_GET['qb_exam_error'] == '1') {
            if ($post) {
                echo '<div class="notice notice-error"><p><strong>Error:</strong> You must add at least one question before saving the exam.</p></div>';
            }
        }
    });


    /* ADD CUSTOM COLUMNS */
    include( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_posts/exams/custom_columns.php');
    /* ADD CUSTOM FILTERS */
    include( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_posts/exams/custom_filters.php');
    /* SEARCH POSTS */
    include( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/custom_posts/exams/functions/search_posts.php');

endif ;