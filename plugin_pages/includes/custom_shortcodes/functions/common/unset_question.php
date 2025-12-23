<?php 

function qb_unset_questions($key , $array) {

    $arr = $array ;
    if( $key !== false ) {
        unset($arr[$key]) ;
    }
    return array_values($arr) ;
}

