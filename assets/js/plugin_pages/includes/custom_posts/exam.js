jQuery(document).ready(function ($) { 

    // ADD NEW QUESTION 
    $('.qb_admin_admin_exam_add_new_question').on('click', function () {
        let row = $('.qb_add_questions_hidden.screen-reader-text').clone(true);
        row.removeClass('qb_add_questions_hidden screen-reader-text');
        row.insertAfter('.qb_add_questions:last');
        row.addClass("qb_add_questions")
    })

    // DELETE QUESTION 
    $(document).on('click', '.qb_admin_question_delete_btn', function () {
        let question_length = $(this).parents(".qb_q_number_content").children(".qb_question_content").children(".qb_add_questions").length;
        if (question_length > 1) {
            $(this).parents(".qb_add_questions").remove();
        }
        return false;
    });

    /* DEFUALT POSTS * SEARCH INPUT */

    $(document).on('click', '.qb_q_options_div', function () {

        let ch_post_val = $(this).parents(".qb_add_questions").children(".qb_q_IDS_rl");
        let qb_posts_options_html = $(this).children("span").text();
        let qb_posts_options_value = $(this).children("input[name=qb_q_options]").val();
       
        $(this).parents(".qb_add_questions").children(".qb_q_IDS").val(qb_posts_options_value);

        ch_post_val.val(qb_posts_options_html)
    })

})