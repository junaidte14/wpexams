/**
 * WP Exams - Exam Admin JavaScript
 *
 * Handles question selection and management in exam creation
 *
 * @package WPExams
 * @since 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Add new question field
         */
        $('.wpexams-admin-exam-add-new-question').on('click', function() {
            const row = $('.wpexams-add-questions-hidden.screen-reader-text').clone(true);
            row.removeClass('wpexams-add-questions-hidden screen-reader-text');
            row.insertAfter('.wpexams-add-questions:last');
            row.addClass('wpexams-add-questions');
            
            return false;
        });

        /**
         * Delete question field
         */
        $(document).on('click', '.wpexams-admin-question-delete-btn', function() {
            const questionLength = $(this).parents('.wpexams-question-content')
                                         .children('.wpexams-add-questions').length;
            
            if (questionLength > 1) {
                $(this).parents('.wpexams-add-questions').remove();
            }
            
            return false;
        });

        /**
         * Select question from dropdown
         */
        $(document).on('click', '.wpexams-question-options-div', function() {
            const chPostVal = $(this).parents('.wpexams-add-questions')
                                    .children('.wpexams-question-id-rl');
            const questionTitle = $(this).children('span').text();
            const questionValue = $(this).children('input[name=wpexams_question_options]').val();
            
            $(this).parents('.wpexams-add-questions')
                  .children('.wpexams-question-id')
                  .val(questionValue);
            
            chPostVal.val(questionTitle);
            
            // Hide dropdown
            $(this).parents('.wpexams-add-questions')
                  .children('.wpexams-question-search-content')
                  .addClass('wpexams-close-dropdown');
        });

        /**
         * Search questions on focus
         */
        $(document).on('focusin', '.wpexams-question-id-rl', function() {
            const searchInput = $(this);
            const keyword = searchInput.val();
            const dropdown = searchInput.parents('.wpexams-add-questions')
                                       .children('.wpexams-question-search-content');
            
            wpexamsFetchQuestions(keyword).then((data) => {
                dropdown.html(data);
                dropdown.removeClass('wpexams-close-dropdown');
            });
        });

        /**
         * Hide dropdown on focus out
         */
        $(document).on('focusout', '.wpexams-question-id-rl', function() {
            const dropdown = $(this).parents('.wpexams-add-questions')
                                   .children('.wpexams-question-search-content');
            
            setTimeout(() => {
                dropdown.addClass('wpexams-close-dropdown');
            }, 1000);
        });

        /**
         * Search questions on keyup
         */
        $(document).on('keyup', '.wpexams-question-id-rl', function() {
            const searchInput = $(this);
            const keyword = searchInput.val();
            const dropdown = searchInput.parents('.wpexams-add-questions')
                                       .children('.wpexams-question-search-content');
            
            // Add timestamp for unique ID
            const timestamp = Date.now();
            dropdown.attr('id', `search_${timestamp}`);
            
            wpexamsFetchQuestions(keyword).then((data) => {
                const currentDropdown = document.getElementById(`search_${timestamp}`);
                if (currentDropdown) {
                    currentDropdown.innerHTML = data;
                }
                dropdown.removeClass('wpexams-close-dropdown');
            });
        });

        /**
         * Fetch questions via AJAX
         */
        function wpexamsFetchQuestions(keyword) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: wpexamsAdmin.ajaxUrl,
                    type: 'post',
                    data: {
                        action: 'wpexams_search_questions',
                        nonce: wpexamsAdmin.nonce,
                        keyword: keyword
                    },
                    success: function(data) {
                        if (data) {
                            resolve(data);
                        } else {
                            reject('No data received');
                        }
                    },
                    error: function() {
                        reject('AJAX request failed');
                    }
                });
            });
        }

    });

})(jQuery);