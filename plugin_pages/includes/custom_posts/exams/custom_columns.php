<?php 
function qb_exams_posts_columns ($columns) {
    
    $columns['qb_taken_by'] = __("Taken By" , "qb") ;
    $columns['qb_status'] = __("Status" , "qb") ;
    return $columns ;
}

add_filter( 'manage_edit-qb_exams_columns', 'qb_exams_posts_columns' );

function qb_show_exams_posts_columns($columns , $post_id) {

    if("qb_taken_by" == $columns) {
        $author_id = get_post_field( 'post_author', $post_id );
        $author_name = get_the_author_meta( 'display_name', $author_id );
        echo $author_name ;
    }

    if("qb_status" == $columns) {

        // GET POST META => get_post_metas.php
        $qb_get_post_meta = new QB_Get_Post_Meta($post_id) ;
        $qb_detail = $qb_get_post_meta->qb_detail ;
        $qb_result = $qb_get_post_meta->qb_result ;
        $qb_status = $qb_get_post_meta->qb_status ;

        if($qb_result && array_key_exists("exam_status" , $qb_result)) {

            if($qb_result['exam_status'] == 'completed') {
                $qb_status ? update_post_meta( $post_id, 'qb__status_colmun', "Completed" , false ) : add_post_meta( $post_id, 'qb__status_colmun', "Completed" , false ) ;
                echo "Completed" ;
            }
            if($qb_result['exam_status'] == 'pending') {
                $qb_status ? update_post_meta( $post_id, 'qb__status_colmun', "Pending" , false ) : add_post_meta( $post_id, 'qb__status_colmun', "Pending" , false ) ;
                echo "Pending" ;
            }

        } else {
            if (is_array($qb_detail)) :
                if($qb_detail['qb_role'] == 'user_defined') {
                    $qb_status ? update_post_meta( $post_id, 'qb__status_colmun', "Useless" , false ) : add_post_meta( $post_id, 'qb__status_colmun', "Useless" , false ) ;
                    echo "Useless" ;
                } else {
                    $qb_status ? update_post_meta( $post_id, 'qb__status_colmun', "Predefined" , false ) : add_post_meta( $post_id, 'qb__status_colmun', "Predefined" , false ) ;
                    echo "Predefined" ;
                }
            else :
                $qb_status ? update_post_meta( $post_id, 'qb__status_colmun', "Useless" , false ) : add_post_meta( $post_id, 'qb__status_colmun', "Useless" , false ) ;
                echo "Useless" ;
            endif ;
        }
    }


}

add_action( 'manage_qb_exams_posts_custom_column' , "qb_show_exams_posts_columns" , 10 , 2) ;
