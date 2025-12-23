jQuery(document).ready(function ($) { 
    $("#qb_add_option").on("click" , function () {
        
        let qb_options_num = $("span[class=qb_opt_num]") ;
        let qb_opts_number = parseInt(qb_options_num[qb_options_num.length - 2].innerText) ;
        let increament_opt_num = qb_opts_number + 1 ;
        let qb_new_opt_number = qb_options_num[qb_options_num.length - 1] ;
        
        qb_new_opt_number.innerText = increament_opt_num
        var row = $(".empty_question_optiion_field.screen-reader-text").clone(true) ;
        row.children("input")
        
        row.removeClass('empty_question_optiion_field screen-reader-text');
        row.insertAfter('#qb-question-row .question_col:last');

        //APPEND OPTIONS
        let options_select = $("select[name=qb_correct_field]") ;
        let new_option = document.createElement("option") ;
        let new_option_text = document.createTextNode(`Option ${increament_opt_num}`) ;
        new_option.appendChild(new_option_text)
        new_option.setAttribute("value", `qb_c_option_${increament_opt_num}`) ;
        options_select.append(new_option) ;

        // SET ATTRIBUTES
        let qb_all_options = $("div[class=question_col]") ;
        let new_opt_input = qb_all_options[qb_all_options.length - 1].querySelector("input") ;
        let new_opt_label = qb_all_options[qb_all_options.length - 1].querySelector("label") ;

        new_opt_input.setAttribute("class", `qb_question__field`) ;
        new_opt_input.setAttribute("id", `qb_question_${increament_opt_num}_field`) ;
        new_opt_label.setAttribute("for", `qb_question_${increament_opt_num}_field`) ;

        return false ;
    })

    $('.qb-remove-question-option-row').on('click', function () {

        let qb_options_num = $(this).parents(".question_col").children("label").children(".qb_opt_num");
        let qb_options_input_val = $(this).parents(".question_col").children("div").children(".qb_question__field")[0].value;
        let options_select = document.querySelectorAll("select[name=qb_correct_field] option") ;

        options_select.forEach(element => {
            if (element.value == `qb_c_option_${qb_options_num[0].innerText}`) {
               return element.remove() ;
            } else if (element.value == `qb_c_option_${qb_options_input_val}`) {
                return element.remove();
            }
        });

        $(this).parents(".question_col").remove();
        return false;
    });

})