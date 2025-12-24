/**
 * WP Exams - Question Admin JavaScript
 *
 * Handles adding/removing question options in the admin
 *
 * @package WPExams
 * @since 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Add new question option
         */
        $('#wpexams-add-option').on('click', function() {
            // Get current options count
            const optNums = $('span.wpexams-opt-num');
            const currentNum = parseInt(optNums[optNums.length - 2].innerText);
            const newNum = currentNum + 1;
            
            // Update the hidden option number
            const newOptNumber = optNums[optNums.length - 1];
            newOptNumber.innerText = newNum;
            
            // Clone the hidden template
            const row = $('.wpexams-empty-question-option.screen-reader-text').clone(true);
            row.removeClass('wpexams-empty-question-option screen-reader-text');
            row.insertAfter('#wpexams-question-row .wpexams-question-col:last');

            // Update select dropdown
            const selectField = $('select[name=wpexams_correct_field]');
            const newOption = document.createElement('option');
            const optionText = document.createTextNode(`Option ${newNum}`);
            
            newOption.appendChild(optionText);
            newOption.setAttribute('value', `wpexams_c_option_${newNum}`);
            selectField.append(newOption);

            // Set attributes on new input/label
            const allOptions = $('div.wpexams-question-col');
            const newOptInput = allOptions[allOptions.length - 1].querySelector('input');
            const newOptLabel = allOptions[allOptions.length - 1].querySelector('label');

            newOptInput.setAttribute('class', 'wpexams-question-field');
            newOptInput.setAttribute('id', `wpexams_question_${newNum}_field`);
            newOptLabel.setAttribute('for', `wpexams_question_${newNum}_field`);

            return false;
        });

        /**
         * Remove question option
         */
        $('.wpexams-remove-question-option').on('click', function() {
            const optNum = $(this).parents('.wpexams-question-col')
                                 .find('label .wpexams-opt-num')
                                 .text();
            
            // Remove from select dropdown
            const selectOptions = document.querySelectorAll('select[name=wpexams_correct_field] option');
            selectOptions.forEach(element => {
                if (element.value === `wpexams_c_option_${optNum}`) {
                    element.remove();
                }
            });

            // Remove the row
            $(this).parents('.wpexams-question-col').remove();
            
            return false;
        });

    });

})(jQuery);