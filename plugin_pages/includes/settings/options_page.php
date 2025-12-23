<?php 

add_action( 'admin_init' , 'qb_settings' ) ;

function qb_settings() {

    /* Profile and usename */
    register_setting( 'qb-settings', 'qb_profile_username', array("sanitize_callback" => "" , 'default' => 'checked') ) ;
    

}

function cd_settings_menu () {
    add_options_page( 'cd-settings-page', 'Courses Settings', 'manage_options', 'cd-courses-settings', array($this , 'cd_courses_settings_func') ) ;
    remove_submenu_page( 'options-general.php' , 'cd-courses-settings' ) ;
}


function usernameProfileHtml() {
    ?>
        <input type="text" value="<?php echo  esc_attr( get_option('qb_profile_username') ) ;?>" name = "qb_profile_username" class="qb_profile_username"  />
    <?php
}

 function qb_save_settings_() {
    ?>
    <div class="wrap">
        <form method="POST" action="options.php">
            <?php 
                settings_fields( 'qb-settings' ) ;
                do_settings_sections( 'qb-settings' ) ;
                submit_button( 'Save Changes' ) ;
            ?>
        </form>

    </div>
    <?php
}