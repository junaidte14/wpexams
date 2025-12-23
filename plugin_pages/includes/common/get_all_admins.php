<?php
//Get all admin user ID's in the DB
function qb_admin_user_ids(){
    //Grab wp DB
    global $wpdb;
    //Get all users in the DB
    $wp_qb_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID");

    //Blank array
    $qbAdminArray = array();
    //Loop through all users
    foreach ( $wp_qb_user_search as $qb_userid ) {
        //Current user ID we are looping through
        $curID = $qb_userid->ID;
        //Grab the user info of current ID
        $curuser = get_userdata($curID);
        //Current user level
        $qb_user_level = $curuser->user_level;
        //Only look for admins
        if($qb_user_level >= 8){//levels 8, 9 and 10 are admin
            //Push user ID into array
            $qbAdminArray[] = $curID;
        }
    }
    return $qbAdminArray;
}

