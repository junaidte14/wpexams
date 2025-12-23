function nxtrvwQuestion(currentQuestionID, btnAction, immediately, examPostID) { 
    if (currentQuestionID && btnAction && immediately && examPostID ) {


        jQuery.ajax({
            method: 'POST',
            url: qb_ex_ajax_url.ajax_url,
            dataType: 'json',
            data: {
                action: 'qb_review_exam',
                currentQuestionID: currentQuestionID,
                btnAction: btnAction,
                immediately: immediately,
                examPostID: examPostID
            },
            success: function (response) {

                if(response.success) {

                    let questionContainer = document.getElementById('qb_questions_tbody_container');

                    questionContainer.innerHTML = "";

                    let nextBtn = document.getElementById("qbNxtQuestion");
                    let prevBtn = document.getElementById("qbPrevQuestion");
                    let exitBtn = document.getElementById("qbExitExam");

                    let qb_questions_explanation_immed = document.getElementById("qb_questions_explanation_immed");
                    qb_questions_explanation_immed.innerHTML = "" ;

                    let qb_subs_exam_question_title = document.getElementById("qb_subs_exam_question_title");
                    qb_subs_exam_question_title.innerText = response.questionTitle;
                    let timerText = document.createTextNode(response.question_time) ;
                    let titleSpan = document.createElement("span");
                    titleSpan.setAttribute("class", "qb_f_right");
                    titleSpan.appendChild(timerText) ;
                    qb_subs_exam_question_title.appendChild(titleSpan);

                    response.qb_question_options_field.forEach((element, index) => {

                        let trChild = document.createElement('tr');

                        trChild.setAttribute("id", `qb_question_field_${parseInt(index)+1}`)
                        trChild.setAttribute("value", index);

                        let tdChild = document.createElement("td")
                        tdChild.style.padding = '10px';
                        if (response.message == "solved") {
                            if (response.sub_answer == index && response.sub_answer !== "") {
                                tdChild.setAttribute('class', "qb_subscriber_answer_sl");
                            }
                        } 

                        let tdDiv = document.createElement("div");
                        let spanAlphText = document.createTextNode(parseInt(index) + 1);
                        let spanAlph = document.createElement("span");
                        if (response.message == "solved") {

                            if (index == response.qb_correct_field.substr(response.qb_correct_field.length - 1)) {
                                spanAlph.setAttribute("class", "alphaOptions qb_green");
                            } else {
                                spanAlph.setAttribute("class", "alphaOptions qb_red");
                            }

                        } else {
                            spanAlph.setAttribute("class", "alphaOptions");
                        }

                        spanAlph.appendChild(spanAlphText);
                        let opt = document.createTextNode(` ${element.replace(/_/g, " ")}`)
                        let optSpan = document.createElement('span');
                        optSpan.style.flexGrow = '1' ;

                        tdDiv.appendChild(spanAlph);
                        optSpan.appendChild(opt)
                        tdDiv.appendChild(optSpan);
                        tdDiv.style.display = 'flex' ;
                        tdDiv.style.alignItems = 'center' ;
                        if (response.message == "solved") {

                            if (index == response.qb_correct_field.substr(response.qb_correct_field.length - 1)) {
                                let span = document.createElement('span');
                                let rightAnswer = document.createTextNode(" ✔ ");
                                span.appendChild(rightAnswer);
                                span.setAttribute("class", "qb_immed_answer_is_true")
                                tdDiv.appendChild(span);
                            } else {
                                let span = document.createElement('span');
                                let wrongAnswer = document.createTextNode(" ✖ ");
                                span.appendChild(wrongAnswer);
                                span.setAttribute("class", "qb_immed_answer_is_false")
                                tdDiv.appendChild(span);
                            }

                        }
                        tdChild.appendChild(tdDiv)

                        trChild.appendChild(tdChild);
                        questionContainer.appendChild(trChild)

                    });

                    let expText = document.createTextNode(`Explanation : ${response.explanation}`)
                    qb_questions_explanation_immed.appendChild(expText);

                    if (parseInt(response.questionID) != response.allPostsID[0]) {
                        prevBtn.classList.remove("hide");
                    } else {
                        prevBtn.classList.add("hide");
                    }

                    prevBtn.setAttribute("onclick", `nxtrvwQuestion('${response.questionID}' , 'prev' , '${immediately}' , '${examPostID}')`)


                    if (response.questionID == response.allPostsID[response.allPostsID.length - 1]) {

                        nextBtn.classList.add("hide");

                    } else {

                        nextBtn.classList.remove("hide");

                        nextBtn.innerText = "Next";

                        nextBtn.setAttribute("onclick", `nxtrvwQuestion('${response.questionID}' , 'next' , '${immediately}' , '${examPostID}')`)

                    }

                }
            }

        }) ;

    } else {
        alert("Sorry somthing is missing. Try Again")
    }
}
