<div class="wrap">
    <form method="post" action="options.php">
        <?php 
        settings_fields( 'qb_colors_settings' ) ;
        $qb_colors_options = get_option('qb_colors_options'); 

        if(!empty($qb_colors_options)){
                if(array_key_exists('qb_button_background_color_option', $qb_colors_options)){
                    $button_background_color = $qb_colors_options['qb_button_background_color_option'];
                }else{
                    $button_background_color = '';
                }

                if(array_key_exists('qb_button_text_color_option', $qb_colors_options)){
                    $button_text_color = $qb_colors_options['qb_button_text_color_option'];
                }else{
                    $button_text_color = '';
                }

                if(array_key_exists('qb_progressbar_background_color_option', $qb_colors_options)){
                    $progressbar_background_color = $qb_colors_options['qb_progressbar_background_color_option'];
                }else{
                    $progressbar_background_color = '';
                }

                if(array_key_exists('qb_progressbar_text_color_option', $qb_colors_options)){
                    $progressbar_text_color = $qb_colors_options['qb_progressbar_text_color_option'];
                }else{
                    $progressbar_text_color = '';
                }
            
            }else{
                $button_background_color = '';
                $button_text_color  = '';
                $progressbar_background_color = '';
                $progressbar_text_color  = '';
            }

        ?>
        <h2>Buttons Color</h2>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="qb_button_background_color_option">Background Color</label></th>
                    <td><input class='qb_f_bold' value='<?php echo esc_html( $button_background_color ) ; ?>' data-default-color="#000000" type="text" name='qb_colors_options[qb_button_background_color_option]' id='qb_button_background_color_option'></td>
                </tr>
                <tr>
                    <th scope="row"><label for="qb_button_text_color_option">Text Color</label></th>
                    <td><input class='qb_f_bold' value='<?php echo esc_html( $button_text_color ) ; ?>' data-default-color="#FFFFFF" type="text" name='qb_colors_options[qb_button_text_color_option]' id='qb_button_text_color_option'></td>
                </tr>
            </tbody>
        </table>

        <h2>Progressbar Color</h2>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="qb_progressbar_background_color_option">Background Color</label></th>
                    <td><input class='qb_f_bold' value='<?php echo esc_html( $progressbar_background_color ) ; ?>' data-default-color="#000000" type="text" name='qb_colors_options[qb_progressbar_background_color_option]' id='qb_progressbar_background_color_option'></td>
                </tr>
                <tr>
                    <th scope="row"><label for="qb_progressbar_text_color_option">Text Color</label></th>
                    <td><input class='qb_f_bold' value='<?php echo esc_html( $progressbar_text_color ) ; ?>' data-default-color="#FFFFFF" type="text" name='qb_colors_options[qb_progressbar_text_color_option]' id='qb_progressbar_text_color_option'></td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button( 'Save Changes' ) ; ?>
    </form>

</div>
