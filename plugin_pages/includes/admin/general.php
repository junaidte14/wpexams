<h2>Genral Settings</h2>

<div class="wrap">
    <form method="post" action="options.php">
        <?php 
            settings_fields( 'qb_general_settings' ) ;

            $qb_general_options = get_option('qb_general_options'); 

            if(!empty($qb_general_options)){
                if(array_key_exists('qb_default_question_options_option', $qb_general_options)){
                    $default_question_options = $qb_general_options['qb_default_question_options_option'];
                }else{
                    $default_question_options = 4;
                }
                if(array_key_exists('qb_profile_username_option', $qb_general_options)){
                    $profile_username = $qb_general_options['qb_profile_username_option'];
                }else{
                    $profile_username = '';
                }
                if(array_key_exists('qb_progressbar_option', $qb_general_options)){
                    $progressbar_option = $qb_general_options['qb_progressbar_option'];
                }else{
                    $progressbar_option = '';
                }
                if(array_key_exists('qb_set_time_option', $qb_general_options)){
                    $set_time_option = $qb_general_options['qb_set_time_option'];
                }else{
                    $set_time_option = '';
                }
            
            }else{
                $default_question_options = 4;
                $profile_username = '';
                $progressbar_option = '';
                $set_time_option = '';
            }
        ?>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="qb_default_question_options_option">Default Question Options</label></th>
                    <td>
                        <select style='width: 18.6%;' name="qb_general_options[qb_default_question_options_option]" id="qb_default_question_options_option">
                            <option value="2" <?php echo $default_question_options == 2 ? "selected" : "" ?>>2</option>
                            <option value="3" <?php echo $default_question_options == 3 ? "selected" : "" ?>>3</option>
                            <option value="4" <?php echo $default_question_options == 4 ? "selected" : "" ?>>4</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="qb_profile_username_option">Profile and Username</label></th>
                    <td><input class='qb_f_bold' value="1" <?php echo $profile_username == "1" || empty($qb_general_options) ? "checked" : "" ?> type="checkbox" name='qb_general_options[qb_profile_username_option]' id='qb_profile_username_option'></td>
                </tr>
                <tr>
                    <th scope="row"><label for="qb_progressbar_option">Progressbar</label></th>
                    <td><input class='qb_f_bold' value="1" <?php echo $progressbar_option == "1" || empty($qb_general_options) ? "checked" : "" ?> type="checkbox" name='qb_general_options[qb_progressbar_option]' id='qb_progressbar_option'></td>
                </tr>
                <tr>
                    <th scope="row"><label for="qb_set_time_option">Set Time(sec) For Question</label></th>
                    <td><input class='qb_f_bold' value="<?php echo empty($set_time_option) ? "82" : $set_time_option ; ?>" type="number" name='qb_general_options[qb_set_time_option]' id='qb_set_time_option'> seconds</td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button( 'Save Changes' ) ; ?>
    </form>

</div>