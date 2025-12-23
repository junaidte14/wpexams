jQuery(document).ready(function ($) { 
    
    $('.qb_dropdown_toggle').on('click', function () {
        $(this).parents(".qb_quiz_").children(".qb_qb_quiz_added").toggle().addClass("active");
    })

    $('#add-quiz-row').on('click', function () {
        var row = $('.empty-qb-quiz-main.screen-reader-text').clone(true);
        row.removeClass('empty-qb-quiz-main screen-reader-text section:last');
        row.insertAfter('.qb-quiz-main .qb_quiz_:last');
        return false;
    });
    
    $('.qb_quiz_remove_row').on('click', function () {
        $(this).parents(".qb_quiz_").remove();
        return false;
    });

    $('#qb-quiz-main').sortable({
        opacity: 0.6,
        revert: true,
        cursor: 'move',
        handle: '.sort'
    });
    
 });

 