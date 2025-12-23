<?php 
/*create custom post type questions_bank*/

if(!function_exists("create_post_questions_bank_questions")) :
    function create_post_questions_bank_questions() {
        register_post_type( 'qb_questions',
            array(
                'labels' => array(
                    'name' => __('Questions', 'qb'),
                    'singular_name' => __('Question', 'qb'),
                    'add_new' => __('Add New', 'qb'),
                    'add_new_item' => __('Add New Question', 'qb'),
                    'edit' => __('Edit', 'qb'),
                    'edit_item' => __('Edit Question', 'qb'),
                    'new_item' => __('New Question', 'qb'),
                    'view' => __('View', 'qb'),
                    'view_item' => __('View Question', 'qb'),
                    'search_items' => __('Search Questions', 'qb'),
                    'not_found' => __('No Question found', 'qb'),
                    'not_found_in_trash' => __('No Questions found in Trash', 'qb'),
                    'parent' => __('Parent Question', 'qb')
                ),
    
                'public' => true,
                'supports' => array( 'title' , "author" ),
                'taxonomies' => array( 'category' ),
                'has_archive' => true,
                'show_in_menu' => false,
            )
        );
    }

    add_action( 'init', 'create_post_questions_bank_questions' );

    /* Metabox Function */

    function qb_question_metaboxes() {

        add_meta_box(
            'qb_question_box_id',                 // Unique ID
            'Add Question',      // Box title
            "qb_question_box_html",  // Content callback, must be of type callable
            'qb_questions' , 'normal', 'high'                          // Post type
        );

    }

    function qb_question_box_html( $post ) {

        // GET POST META => get_post_metas.php
        $qb_get_post_meta = new QB_Get_Post_Meta($post->ID) ;
        $qb_q_fields = $qb_get_post_meta->qb_q_fields ;

        wp_enqueue_style('qb_questions_enqueue_questions_style' ,  QUESTIONS_BANK_PLUGIN_URL.'assets/css/qb-question-style.css' ,  true);
        
        ?>
        
        
        <div class="qb-question-main" id="qb-question-main">
            
            <p class="qb_question_question_main" style='display:none;'></p>
            
            <?php
            $qb_general_options = get_option('qb_general_options');
            if($qb_general_options) {
                    if(array_key_exists('qb_default_question_options_option', $qb_general_options)){
                    $default_question_options = $qb_general_options['qb_default_question_options_option'];
                }else{
                    $default_question_options = 4;
                }
            } else {
                $default_question_options = 4;
            }
            
            if ( !empty( $qb_q_fields ) ) {
                

                if(

                    ( array_key_exists('qb_question_options_field' , $qb_q_fields ) )

                ) {

                    ?>
                    
                    <div class="qb_question_question_main">
                        
                        <section class="qb_qb_question_added" style="padding-bottom:20px" >
                                
                                    
                                <div class="qb-question-row" id="qb-question-row">

                                    <?php 
                                    
                                    foreach ($qb_q_fields['qb_question_options_field'] as $key => $value) {
                                        ?> 
                                        
                                            <div class="question_col">
                                        
                                            <label for="qb_question_<?php echo intval($key)+1 ; ?>_field" style="font-weight: bold;">Option <span class='qb_opt_num'><?php echo intval($key)+1 ; ?></span></label>
                                            
                                            <?php 
                                            
                                            if(intval($key)+1 > $default_question_options) {
                                                ?> 
                                                
                                                <div style='display:flex;'>

                                                    <input style='width: 95%;' name="qb_question_options_field[]" class="qb_question__field" id="qb_question_<?php echo intval($key)+1 ; ?>_field" value="<?php echo esc_html( preg_replace('/_/', ' ', $value) ) ; ?>" />
                                                    <a  class="button qb-remove-question-option-row" href="javascript:;"><span style='line-height: 2;' class="dashicons dashicons-trash"></span></a>

                                                </div>
                                                
                                                <?php
                                            } else {
                                                ?> 
                                                
                                                <input name="qb_question_options_field[]" class="qb_question__field" id="qb_question_<?php echo intval($key)+1 ; ?>_field" value="<?php echo esc_html( preg_replace('/_/', ' ', $value) ) ; ?>" />
                                                
                                                <?php
                                            }
                                            
                                            ?>

                                        </div>
                                        
                                        <?php
                                    }
                                    
                                    ?>
                                
                                <!-- ADD OPTION BUTTON  -->
                                <div class='qb_text_right'>
                                    <button id='qb_add_option' type='button' class='button button-primary'>Add Option</button>
                                </div>
                                
                                <div class="qb-question-third-row" id="qb-question-third-row">
                                    
                                    <label for="qb_correct_field" style="font-weight: bold;">Correct Option</label>
                                    
                                    <select name="qb_correct_field" id="qb_correct_field" class="qb_correct_field">
                                        <?php 
                                        
                                        foreach ($qb_q_fields['qb_question_options_field'] as $key => $value) {

                                            ?> <option value="qb_c_option_<?php echo esc_html( $key ) ; ?>" <?php if($qb_q_fields['qb_correct_field'] == "qb_c_option_".$key) {echo 'selected' ;}  ?>>Option <?php echo intval($key)+1 ?></option> <?php
                                                
                                        }
                                        
                                        ?>

                                        
                                    </select>
                                    
                                </div>

                                <div class= "qb-question-description-row" id='qb-question-description-row'>

                                    <label for="qb_question_description_field" style="font-weight: bold;">Description</label>

                                    <textarea name="qb_question_description_field" class="qb_question_description_field" id="qb_question_description_field" cols="6" rows="5"><?php echo esc_html( $qb_q_fields['qb_question_description_field'] ) ; ?></textarea>

                                </div>
                                
                            <hr>
                            
                        </section>
                        
                        </div>
                        
                    <?php
                    
                }
                
                ?>
                
                <?php
                
            } else {

            ?>
                    
            <section class="qb_question_question_main"  style="padding-bottom:20px" >
            
                <div class="qb-question-row" id="qb-question-row">

                    <?php 
                    
                    for ($i=1; $i <= intval($default_question_options) ; $i++) { 
                        ?> 
                        
                        <div class="question_col">
                        
                            <label for="qb_question_<?php echo $i; ?>_field" style="font-weight: bold;">Option <span class='qb_opt_num'><?php echo $i ?></span></label>
                            
                            <input  name="qb_question_options_field[]" class="qb_question__field" id="qb_question_<?php echo $i ?>_field"  />
                            
                        </div>
                        
                        <?php
                    }

                    ?>
                    
                    
                </div>

                <!-- ADD OPTION BUTTON  -->
                <div class='qb_text_right'>
                    <button id='qb_add_option' type='button' class='button button-primary'>Add Option</button>
                </div>
                
                <div class="qb-question-third-row" id="qb-question-third-row">
                    
                    <label for="qb_correct_field" style="font-weight: bold;">Correct Option</label>
                    
                    <select name="qb_correct_field" id="qb_correct_field" class="qb_correct_field">
                        
                        <?php 
                        
                        for ($i=0; $i < intval($default_question_options) ; $i++) {  
                            ?> <option value="qb_c_option_<?php echo $i ; ?>" class='qb_opt_<?php echo $i ?>'>Option <?php echo intval($i) + 1 ?></option> <?php
                        }
                        
                        ?>
                        
                    </select>
                    
                </div>

                <div class= "qb-question-description-row" id='qb-question-description-row'>

                    <label for="qb_question_description_field" style="font-weight: bold;">Description</label>

                    <textarea name="qb_question_description_field" class="qb_question_description_field" id="qb_question_description_field" question_cols="6" rows="5"></textarea>

                </div>
                
            </section>
            
            <?php } ?>
        
            </div>

            <!-- empty hidden one for jQuery -->
            <div class="question_col empty_question_optiion_field screen-reader-text">
                        
                <label style="font-weight: bold;">Option <span class='qb_opt_num'>null</span></label>
                
                <div style='display:flex;'>
                    <input style='width: 95%;' name="qb_question_options_field[]"  />
                    <a  class="button qb-remove-question-option-row" href="javascript:;"><span style='line-height: 2;' class="dashicons dashicons-trash"></span></a>
                </div>
                
            </div>

            <?php 
    }


    add_action( 'add_meta_boxes' , "qb_question_metaboxes" ) ;


        /* Update Qustions fields */
    function qb_question_save_postdata( $post_id ) {

        // Check if this is the correct post type
        if (get_post_type($post_id) !== 'qb_questions') {
            return;
        }

        // Check if required fields exist and are not empty
        if ( 
            !isset($_POST['qb_question_options_field']) || !is_array($_POST['qb_question_options_field']) ||
            empty($_POST['qb_question_options_field'][0]) ||
            empty($_POST['qb_question_options_field'][1]) ||
            empty($_POST['qb_question_options_field'][2]) ||
            empty($_POST['qb_question_options_field'][3])
        ) {
            // Set an error message and return without updating
            add_filter('redirect_post_location', function($location) {
                return add_query_arg('qb_question_options_error', '1', $location);
            });
            return;
        }

        if(
            !isset($_POST['qb_correct_field']) || empty($_POST['qb_correct_field'])
        ) {
            // Set an error message and return without updating
            add_filter('redirect_post_location', function($location) {
                return add_query_arg('qb_question_correct_option_error', '1', $location);
            });
            return;
        }

        if(
            !isset($_POST['qb_question_description_field']) || empty($_POST['qb_question_description_field'])
        ) {
            // Set an error message and return without updating
            add_filter('redirect_post_location', function($location) {
                return add_query_arg('qb_question_description_error', '1', $location);
            });
            return;
        }
    
        // Get current post data
        $postData = get_post( $post_id );
        $new_array_contents = array();
        $new_array_contents['qb_question_title'] = $postData->post_title;
    
        // Process question options
        $array_qb_opts = $_POST['qb_question_options_field'];
        $removeSpacesOptions = array();
    
        foreach ($array_qb_opts as $option) {
            if (!empty($option)) {
                $removeSpacesOptions[] = preg_replace('/\s+/', '_', $option);
            }
        }
    
        $new_array_contents['qb_question_options_field'] = $removeSpacesOptions;
        $new_array_contents['qb_correct_field'] = $_POST['qb_correct_field'];
        $new_array_contents['qb_question_description_field'] = $_POST['qb_question_description_field'];
    
        // Update post meta if all conditions are met
        update_post_meta( $post_id , 'qb_question_fields_meta_key', $new_array_contents );
    }
    
    // Show error message in admin
    add_action('admin_notices', function() {
        if (isset($_GET['qb_question_options_error']) && $_GET['qb_question_options_error'] == '1') {
            echo '<div class="notice notice-error"><p><strong>Error:</strong> Please ensure the first four options are not empty.</p></div>';
        }
        if (isset($_GET['qb_question_correct_option_error']) && $_GET['qb_question_correct_option_error'] == '1') {
            echo '<div class="notice notice-error"><p><strong>Error:</strong> Please ensure the correct option are not empty.</p></div>';
        }
        if (isset($_GET['qb_question_description_error']) && $_GET['qb_question_description_error'] == '1') {
            echo '<div class="notice notice-error"><p><strong>Error:</strong> Please ensure the description are not empty.</p></div>';
        }
        
    });
    

    add_action( 'save_post', "qb_question_save_postdata" );


endif ;