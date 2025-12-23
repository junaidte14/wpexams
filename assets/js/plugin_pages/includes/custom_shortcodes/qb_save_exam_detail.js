jQuery(document).ready(function ($) { 

    // request for save subscriber question detail
    $(document).on('click', '.qb_start_question_btn', function () {
        var id = $(this).data('id');
        var nonce = $(this).data('nonce');

        if (confirm("Are you sure?")) {

            let qb_category_field_arr = document.querySelectorAll("#qb_category_content input[type=checkbox]:checked");
            let qb_q_numbers_field = $('.qb_q_numbers_field').val();
            let qb_timed_field = document.querySelector('.qb_timed_content input[type=checkbox]:checked');
            let qb_all_and_unused_field = $('input[name=qb_all_and_unused_field]:checked').val();
            let qb_answer_show_immed_field = $('input[name=qb_answer_show_immed_field]:checked').val();

            let qb_category_field_values_arr = [];
            qb_category_field_arr.forEach((e) => {
                qb_category_field_values_arr.push(e.value);
            })

            var qb_timed_field_value;
            if (!qb_timed_field) {
                qb_timed_field_value = "0";
            } else {
                qb_timed_field_value = "1";
            }

            let formData = {
                "qb_role": "user_defined",
                "qb_category_field": qb_category_field_values_arr,
                "qb_q_numbers_field": qb_q_numbers_field,
                "qb_timed_field": qb_timed_field_value,
                "qb_all_and_unused_field": qb_all_and_unused_field,
                "qb_answer_show_immed_field": qb_answer_show_immed_field
            }

            if (
                formData.qb_category_field.length != 0 && formData.qb_category_field != undefined &&
                formData.qb_q_numbers_field != "" && formData.qb_q_numbers_field != undefined &&
                formData.qb_all_and_unused_field != "" && formData.qb_all_and_unused_field != undefined &&
                formData.qb_answer_show_immed_field != "" && formData.qb_answer_show_immed_field != undefined
            ) {

                var data = {
                    action: 'handle_qb_save_exam_detail',
                    nonce: nonce,
                    id: id,
                    formData: formData
                }


                jQuery.post(qb_ajax_url.ajax_url, data, function (response) {
                    let examContainer = document.getElementById("qb_subscriber_exam_detail");
                    let div = document.createElement('div');
                    let p = document.createElement('p');
                    let button = document.createElement('button');

                    if (response) {

                        if (JSON.parse(response)) {
                            let jsonReps = JSON.parse(response);
                            if (jsonReps.success) {

                                let subscriberExamStartParam = new URLSearchParams();
                                subscriberExamStartParam.set("qb_subscriber_exam_ID", jsonReps.postID);

                                window.location.search = subscriberExamStartParam;
                            }

                            if (jsonReps.error) {
                                examContainer.innerHTML = "";
                                let test = document.createTextNode(jsonReps.error);
                                p.appendChild(test);
                                let buttonText = document.createTextNode("Try Again");
                                button.appendChild(buttonText);

                                button.setAttribute("onclick", "qb_reload_page()")
                                button.setAttribute("class", "button_background_color button_text_color")
                                div.appendChild(p);
                                div.appendChild(button);
                                examContainer.appendChild(div);

                            }

                        } else {
                            examContainer.innerHTML = "";
                            let test = document.createTextNode("Unable to start your exam!");
                            p.appendChild(test);
                            let buttonText = document.createTextNode("Try Again");
                            button.appendChild(buttonText);
                            button.setAttribute("onclick", "qb_reload_page()")
                            button.setAttribute("class", "button_background_color button_text_color")
                            div.appendChild(p);
                            div.appendChild(button);
                            examContainer.appendChild(div);
                        }

                    } else {
                        examContainer.innerHTML = "";
                        let test = document.createTextNode("Internal server error!");
                        p.appendChild(test);
                        let buttonText = document.createTextNode("Try Again");
                        button.appendChild(buttonText);
                        button.setAttribute("onclick", "qb_reload_page()")
                        div.appendChild(p);
                        div.appendChild(button);
                        examContainer.appendChild(div);
                    }

                });

            }

        }

        return false;
    })

}) ;