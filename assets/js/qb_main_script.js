jQuery(document).ready(function ($) {
    
    questionsCountDownTimer(0, 0, 0, "questiontimer");

    $(document).on('click', 'input[name=qb_question_options]', function () { 
        let qb_radio_btns = document.querySelectorAll("input[name=qb_question_options]");
        qb_radio_btns.forEach(elm => {
            if (elm.checked) {
                elm.parentNode.parentNode.parentNode.classList.add("qb_subscriber_answer");
            } else {
                elm.parentNode.parentNode.parentNode.classList.remove("qb_subscriber_answer");
            }
        });
     }) 
    
    // collapse 
    $(document).on('click', '#qbAccordian div' , function () {

        $("#qbAccordian ul ul").slideUp();
        if ($(this).next().is(":hidden")) {
            $(this).next().slideDown();
        }

    })
    
}) ; 

function exitExamFun() {
    let url = new URL(location.href);
    url.searchParams.delete('qb_subscriber_exam_ID');
    url.searchParams.delete('qb_exam_review_ID');
    return window.location.href = url.href;
}

var qbtimers = {}; 

function qb_reload_page() {
    window.location.reload();
}

