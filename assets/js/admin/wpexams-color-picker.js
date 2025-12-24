/**
 * WP Exams - Color Picker Initialization
 *
 * Initializes WordPress color picker for settings
 *
 * @package WPExams
 * @since 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize color pickers
        $('.wpexams-color-picker').wpColorPicker();
        
        // Fallback for individual color pickers
        $('#wpexams_button_bg_color').wpColorPicker();
        $('#wpexams_button_text_color').wpColorPicker();
        $('#wpexams_progressbar_bg_color').wpColorPicker();
        $('#wpexams_progressbar_text_color').wpColorPicker();
    });

})(jQuery);