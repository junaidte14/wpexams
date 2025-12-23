<?php 
    /**
     * GET POST METAS
     */
    

    class QB_Get_Post_Meta {

        public $qb_result ;
        public $qb_detail ;
        public $qb_status ;
        public $qb_q_fields ;
        public $post_id ;

        function __construct($post_id) {
            $this->post_id = $post_id ;
            $this->qb_get_post_metas() ;
        }

        function qb_get_post_metas() {
            $this->qb_q_fields = get_post_meta( $this->post_id, 'qb_question_fields_meta_key', true ) ;
            $this->qb_result = get_post_meta( $this->post_id , 'qb_subscriber_exam_result_meta_key', true ) ;
            $this->qb_detail = get_post_meta( $this->post_id , 'qb_subscriber_exam_detail_meta_key', true ) ;
            $this->qb_status = get_post_meta( $this->post_id , 'qb__status_colmun', true ) ;
        }

    }