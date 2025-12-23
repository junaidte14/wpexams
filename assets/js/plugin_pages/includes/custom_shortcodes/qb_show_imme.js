function subscriber_answer(qID, examPostID) {

    let nextBtn = document.getElementById("qbNxtQuestion");
    let submitBtn = document.getElementById("qbSubmitQuestion");

    let question_time = document.getElementById("qb_question_timer").innerText;
    let exam_time = document.getElementById("qb_exam_timer").innerText;

    let subscriber_answer_qb_question_options = document.querySelector("input[name=qb_question_options]:checked");

    if (exam_time != "", question_time != "", subscriber_answer_qb_question_options.value != "" && subscriber_answer_qb_question_options.value != undefined && examPostID != "" && examPostID != undefined && qID != "" && qID != undefined) {
        
        let data = {
            action: 'handle_qb_answer_show_immed',
            exam_time: exam_time,
            question_time: question_time,
            subscriber_answer: subscriber_answer_qb_question_options.value.substr(subscriber_answer_qb_question_options.value.length - 1),
            question_ID: qID,
            examPostID: examPostID
        }

        jQuery.post(qb_ajax_url.ajax_url, data, function (response) {
            if (JSON.parse(response)) {
                let jsonReps = JSON.parse(response);
                let qb_tb_container = document.querySelectorAll(".qb_questions_tbody_container tr") ;
                qb_tb_container.forEach((el) => {
                    if (el.getElementsByClassName("qb_subscriber_answer")) {
                        el.classList.remove("qb_subscriber_answer")
                    }
                })

                let qb_question_field_show_immed = document.querySelectorAll("input[name=qb_question_options]");
                let qb_questions_explanation_immed = document.getElementById("qb_questions_explanation_immed");
                qb_questions_explanation_immed.innerHTML = "";

                qb_question_field_show_immed.forEach((element , ind) => {

                    let val = element.value ;
                    if (jsonReps.question_correct_answer == val.substr(val.length -1)) {
                        let rightAnswer = document.createTextNode(" ✔ ");
                        let span = document.createElement("span");
                        span.setAttribute("class", "qb_immed_answer_is_true");
                        span.appendChild(rightAnswer);
                        element.appendChild(span);
                        
                        element.parentNode.style.color = "green"
                        if (!element.nextSibling.nextSibling) {
                            element.parentNode.appendChild(span);
                        }

                    } else {
                        let wrongAnswer = document.createTextNode(" ✖ ");
                        let span = document.createElement("span");
                        span.setAttribute("class", "qb_immed_answer_is_false");
                        span.appendChild(wrongAnswer);
                        element.appendChild(span);
                        
                        element.parentNode.style.color = "red"
                        if (!element.nextSibling.nextSibling) {
                            element.parentNode.appendChild(span);
                        }
                    }
                    
                    if (jsonReps.question_correct_answer == ind) {
                        element.classList.add("qb_subscriber_answer")
                    }

                });
                let p = document.createElement("p");
                let text = document.createTextNode("Explanation :")
                let expText = document.createTextNode(jsonReps.explanation);
                p.setAttribute("class", "qb_immed_exp_stl");
                p.appendChild(expText);

                qb_questions_explanation_immed.appendChild(text);
                qb_questions_explanation_immed.appendChild(p);
                qb_questions_explanation_immed.classList.remove("hide");

                nextBtn.classList.remove("hide");
                submitBtn.classList.add("hide");

            }
        });

    }


};