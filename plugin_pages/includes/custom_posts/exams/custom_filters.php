<?php 

function qb_exams_custom_filters() {

    global $wpdb ;
    global $post_type ;

    if( $post_type == 'qb_exams' ) {
        
        /* FOR DATABASE */
        // $qb_exams = $wpdb->get_results($wpdb->prepare("SELECT `ID` FROM {$wpdb->posts} WHERE `post_type` = %s " , 'qb_exams'), ARRAY_N) ;
        // $qb_exams_n = array_values(array_map(function ($x) {return $x[0] ;}, $qb_exams)) ;

        $current_status = "" ;
        if( isset( $_GET['qb_status_filter'] ) ) {
            $current_status = $_GET['qb_status_filter'] ; // check if option has been selected 
        }

        echo '<select class="" id="qb_status_filter" name="qb_status_filter">' ;
            ?> 
            <option value="" > Select Status Type </option>
            <option value="Useless" <?php echo $current_status == "Useless" ? "selected" : "" ?>> Useless </option>
            <option value="Completed" <?php echo $current_status == 'Completed' ? "selected" : "" ?>> Completed </option>
            <option value="Pending" <?php echo $current_status == 'Pending' ? "selected" : "" ?>> Pending </option>
            <option value="Predefined" <?php echo $current_status == 'Predefined' ? "selected" : "" ?>> Predefined </option>
            <?php
        echo '</select>' ;
    }

}

add_action( 'restrict_manage_posts' ,  'qb_exams_custom_filters') ;

/**
 * Exams page filter queries
 */

 function qb_exams_filter_queries ($query) {

    if( is_admin() &&  $query->query['post_type'] == 'qb_exams') {
        
        if(!empty($_GET['qb_status_filter'])) {

            $query->set( 'meta_query', array(
                array(
                    'key'     => 'qb__status_colmun',
                    'compare' => '=',
                    'value'   => $_GET['qb_status_filter']
                )
            ) );   
    
        }

    }

 }

 add_filter( 'parse_query' , 'qb_exams_filter_queries' ) ;