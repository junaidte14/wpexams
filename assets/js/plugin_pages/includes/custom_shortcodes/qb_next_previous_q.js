function nxtQuestion(currentQuestionID, btnAction, immediately, examPostID) {

    let question_time = document.getElementById("qb_question_timer").innerText;
    let exam_time = document.getElementById("qb_exam_timer").innerText;

    if (exam_time != "" && question_time != "" && currentQuestionID != "" && currentQuestionID != undefined && btnAction != "" && btnAction != undefined && immediately != "" && immediately != undefined && examPostID != "" && examPostID != undefined) {

        if (btnAction == "exitExam") {
            let confirmToExit = confirm("Your exam is not finished, would you like to exit now and complete later?")
            if (!confirmToExit) {
                return;
            }
        }
        // When the subsciber result show at the end then save this answer.

        let subscriberAnswer;

        // when the subscriber select immediately field false 
        subscriber_answer_qb_question_options = document.querySelector("input[name=qb_question_options]:checked");
        if (subscriber_answer_qb_question_options) {
            subscriberAnswer = subscriber_answer_qb_question_options.value.substr(subscriber_answer_qb_question_options.value.length - 1);
        } else {
            subscriberAnswer = "null";
        }

        jQuery.ajax({
            method: 'POST',
            url: qb_ajax_url.ajax_url,
            dataType: 'json',
            data: {
                action: 'qb_next_prev_question',
                exam_time: exam_time,
                question_time: question_time,
                currentQuestionID: currentQuestionID,
                btnAction: btnAction,
                immediately: immediately,
                subscriberAnswer: subscriberAnswer,
                examPostID: examPostID
            },
            success: function (response) {

                // buttons 
                let nextBtn = document.getElementById("qbNxtQuestion");
                let prevBtn = document.getElementById("qbPrevQuestion");
                let exitBtn = document.getElementById("qbExitExam");
                let submitBtn = document.getElementById("qbSubmitQuestion");

                nextBtn.setAttribute("disabled", true) ;
                prevBtn.setAttribute("disabled", false) ;

                if (response.exit_exam == "exit") {
                    let url = new URL(location.href);
                    url.searchParams.delete('qb_subscriber_exam_ID');
                    return window.location.href = url.href;
                }
                if (response.success) {

                    if (response.exam_status == "completed") {
                        let startT = document.getElementById("qb_startTimer");
                        setTimeout(() => {
                            startT.classList.add("qb_d_none");
                        }, 1000);
                        if (qbtimers['timedQuizCountDownTime']) {
                            pauseTimer("timedquiztimer", "questiontimer");
                        } else {
                            pauseTimer("Untimedquiztimer", "questiontimer");
                        }
                    }
                    // get data for calculate percentage
                    getPercentage(response.currentId, response.allPostsID , response.questionID);

                    nextBtn.removeAttribute("disabled");
                    prevBtn.removeAttribute("disabled");

                    let questionContainer = document.getElementById('qb_questions_tbody_container');

                    questionContainer.innerHTML = "";

                    if (response.result) {
                        
                        let qb_questions_explanation_immed = document.getElementById("qb_questions_explanation_immed");
                        if (qb_questions_explanation_immed !== null) {
                            qb_questions_explanation_immed.classList.add("hide");
                        }

                        let questionResultContainer = document.getElementById('qb_subscriber_exam_result');
                        questionResultContainer.classList.add('show');
                        questionResultContainer.classList.remove('hide');

                        nextBtn.remove();
                        prevBtn.remove();

                        document.getElementById("qb_subs_exam_question_title").innerHTML = "";

                        let currectQuestions = response.correct_sub_opt;

                        let filterCurrectOptions = currectQuestions.filter(function (value) {
                            return value['KEY'] != "null";
                        });

                        let headingText = document.createTextNode(`Score ${filterCurrectOptions ? filterCurrectOptions.length : 0}/${response.total_questions}`);
                        let headh5 = document.createElement("h5");

                        let ra = document.createElement("a");
                        ra.setAttribute("class", "qb_f_right");
                        ra.setAttribute("href", `?qb_exam_review_ID=${examPostID}`);
                        let raText = document.createTextNode("Review");
                        ra.appendChild(raText);

                        headh5.appendChild(headingText);
                        headh5.appendChild(ra);
                        questionResultContainer.appendChild(headh5);

                        function appendData(data) {
                            if (data.length != 0) {
                                for (var i = 0; i < data.length; i++) {
                                    let headdiv = document.createElement("div");
                                    let pdownArrow = document.createElement("p");
                                    let div = document.createElement("div");
                                    let ul = document.createElement("ul");
                                    let ul2 = document.createElement("ul");
                                    let li = document.createElement("li");
                                    let li2 = document.createElement("li");
                                    let h3 = document.createElement("h3");



                                    headdiv.setAttribute("class", "qb_result_dp_header_stl")
                                    let downArrow = document.createTextNode(" ⮟ ");
                                    let time = document.createTextNode(data[i].time);
                                    let tSpan = document.createElement("span");
                                    tSpan.appendChild(time);
                                    let h3Test = document.createTextNode(data[i].qb_question_title == "" ? "no title" : data[i].qb_question_title);
                                    h3.appendChild(h3Test);
                                    pdownArrow.appendChild(tSpan);
                                    pdownArrow.appendChild(downArrow);
                                    headdiv.appendChild(h3);
                                    headdiv.appendChild(pdownArrow);

                                    data[i].qb_question_options_field.forEach((element, key) => {
                                        
                                        let aText = document.createTextNode(element.replace(/_/g, " "))
                                        let aSpan = document.createElement('span');
                                        aSpan.style.flexGrow = '1' ;
                                        aSpan.appendChild(aText);
                                        let a = document.createElement("a");
                                        a.style.display = 'flex' ;
                                        a.style.alignItems = 'center' ;
                                        a.setAttribute("href", "javascript:(0)");
                                        let spanAlph = document.createElement("span");
                                        spanAlph.setAttribute("class", "alphaOptions")
                                         
                                        if (data[i].qb_correct_field.substr(data[i].qb_correct_field.length - 1) == key) {
                                            let span = document.createElement('span');
                                            let rightAnswer = document.createTextNode(" ✔ ");
                                            let spanAlphText = document.createTextNode(parseInt(key) + 1);
                                            span.appendChild(rightAnswer);
                                            span.setAttribute("class", "qb_immed_answer_is_true")
                                            spanAlph.setAttribute("class", "qb_green alphaOptions")
                                            spanAlph.appendChild(spanAlphText);

                                            a.setAttribute("class", "qb_a_f_size");
                                            a.appendChild(spanAlph);
                                            a.appendChild(aSpan);
                                            a.appendChild(span);
                                        } else {
                                            let span = document.createElement('span');
                                            let spanAlphText = document.createTextNode(parseInt(key) + 1);
                                            let wrongAnswer = document.createTextNode(" ✖ ");
                                            span.appendChild(wrongAnswer);
                                            span.setAttribute("class", "qb_immed_answer_is_false")
                                            spanAlph.setAttribute("class", "qb_red alphaOptions")
                                            spanAlph.appendChild(spanAlphText);
                                            a.setAttribute("class", "qb_a_f_size");
                                            a.appendChild(spanAlph);
                                            a.appendChild(aSpan);
                                            a.appendChild(span);
                                        }

                                        
                                        if (parseInt(data[i].sub_answer) == key) {
                                            a.setAttribute("class", "qb_subscriber_answer_sl qb_a_f_size")
                                        }

                                        li2.appendChild(a);
                                    });




                                    ul2.appendChild(li2);
                                    ul2.setAttribute("class", "qb_m_auto")
                                    div.setAttribute("id", "qbAccordian");

                                    li.appendChild(headdiv)
                                    li.appendChild(ul2)

                                    ul.setAttribute("class", "qb_m_auto");
                                    ul.appendChild(li);

                                    div.appendChild(ul);

                                    questionResultContainer.appendChild(div);
                                }
                            }
                        }

                        appendData(response.questionsFeilds);

                        if (response.result == "announced") {
                            exitBtn.setAttribute("onclick", "exitExamFun()");
                        }

                        // TIME DIFF
                        if (qbtimers['timedQuizCountDownTime']) {

                            let calculatedExamTime = qbtimers['timedQuizCountDownTime'];
                            let subscriberExamTime = response.exam_time;

                            examTimeDiff(calculatedExamTime, subscriberExamTime);

                        }

                    } else {

                        restartTimer(0, 0, 0, "questiontimer");

                        if (response.message == "solved") {
                            const time = response.question_time;
                            const [hrs, min, sec] = time.split(':');
                            questionsCountDownTimer(hrs, min, sec, "questiontimer");
                        } else {
                            questionsCountDownTimer(0, 0, 0, "questiontimer");
                        }

                        let qb_subs_exam_question_title = document.getElementById("qb_subs_exam_question_title");
                        qb_subs_exam_question_title.innerText = response.questionTitle;
                        let titleSpan = document.createElement("span");
                        titleSpan.setAttribute("class", "qb_f_right");
                        titleSpan.setAttribute("id", "qb_question_timer");
                        qb_subs_exam_question_title.appendChild(titleSpan);

                        // ** //
                        if (immediately == '1') {

                            submitBtn.setAttribute("onclick", `subscriber_answer('${response.questionID}' , '${examPostID}')`);

                            let qb_questions_explanation_immed = document.getElementById("qb_questions_explanation_immed");
                            qb_questions_explanation_immed.classList.add("hide");

                            response.qb_question_options_field.forEach((element, index) => {

                                let trChild = document.createElement('tr');
                                let tdChild = document.createElement("td");
                                let label = document.createElement("label");
                                let tdDiv = document.createElement("div");
                                let input = document.createElement("input");

                                let optSpanText = document.createTextNode(parseInt(index) + 1);
                                let optSpan = document.createElement("span");
                                optSpan.setAttribute("class", "alphaOptions");
                                optSpan.appendChild(optSpanText);

                                input.setAttribute("name", "qb_question_options");
                                input.setAttribute("id", `qb_question_option${parseInt(index) + 1}`);
                                input.setAttribute("type", "radio");

                                input.setAttribute("value", index);

                                label.setAttribute("for", `qb_question_option${parseInt(index) + 1}`);

                                let text = document.createTextNode(` ${element.replace(/_/g, " ")}`)
                                tdDiv.appendChild(optSpan);
                                tdDiv.appendChild(input);
                                tdDiv.appendChild(text);

                                if (response.message == "solved") {
                                    if (response.sub_answer == index ) {
                                        trChild.setAttribute('class', " qb_subscriber_answer");
                                    }
                                }

                                if (response.message == "solved") {

                                    if (index == response.qb_correct_field.substr(response.qb_correct_field.length - 1)) {
                                        optSpan.setAttribute("class", "alphaOptions qb_green");
                                    } else {
                                        optSpan.setAttribute("class", "alphaOptions qb_red");
                                    }

                                } else {
                                    optSpan.setAttribute("class", "alphaOptions");
                                }

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


                                label.appendChild(tdDiv);
                                tdChild.appendChild(label);
                                trChild.appendChild(tdChild);

                                questionContainer.appendChild(trChild)

                            });

                            if (response.message == "solved") {
                                nextBtn.classList.remove("hide");
                            } else {
                                nextBtn.classList.add("hide");
                            }
                            submitBtn.classList.remove("hide");

                        } else {

                            response.qb_question_options_field.forEach((element, index) => {
                                let trChild = document.createElement('tr');
                                let tdChild = document.createElement("td");
                                let label = document.createElement("label");
                                let tdDiv = document.createElement("div");
                                let input = document.createElement("input");

                                let optSpanText = document.createTextNode(parseInt(index) + 1);
                                let optSpan = document.createElement("span");
                                optSpan.setAttribute("class", "alphaOptions");
                                optSpan.appendChild(optSpanText);

                                input.setAttribute("name", "qb_question_options");
                                input.setAttribute("id", `qb_question_option${parseInt(index) + 1}`);
                                input.setAttribute("type", "radio");

                                input.setAttribute("value", index);

                                tdDiv.appendChild(optSpan);
                                tdDiv.appendChild(input);

                                let text = document.createTextNode(`${element.replace(/_/g, " ")}`)
                                tdDiv.appendChild(text);
                                label.setAttribute("for", `qb_question_option${parseInt(index) + 1}`);
                                label.appendChild(tdDiv);
                                tdChild.appendChild(label);
                                trChild.appendChild(tdChild);

                                questionContainer.appendChild(trChild);
                            });


                            if (response.message == "solved") {
                                let qb_question_field_1 = document.getElementById("qb_question_option1");
                                let qb_question_field_2 = document.getElementById("qb_question_option2");
                                let qb_question_field_3 = document.getElementById("qb_question_option3");
                                let qb_question_field_4 = document.getElementById("qb_question_option4");

                                if (response.sub_answer == qb_question_field_1.value ) {
                                    qb_question_field_1.checked = true;
                                    qb_question_field_1.parentNode.parentNode.parentNode.classList.add("qb_subscriber_answer");
                                }
                                if (response.sub_answer == qb_question_field_2.value ) {
                                    qb_question_field_2.checked = true;
                                    qb_question_field_2.parentNode.parentNode.parentNode.classList.add("qb_subscriber_answer");
                                }
                                if (response.sub_answer == qb_question_field_3.value ) {
                                    qb_question_field_3.checked = true;
                                    qb_question_field_3.parentNode.parentNode.parentNode.classList.add("qb_subscriber_answer");
                                }
                                if (response.sub_answer == qb_question_field_4.value ) {
                                    qb_question_field_4.checked = true;
                                    qb_question_field_4.parentNode.parentNode.parentNode.classList.add("qb_subscriber_answer");
                                }

                            }

                        }

                        if (parseInt(response.questionID) != response.allPostsID[0]) {
                            prevBtn.classList.remove("hide");
                        } else {
                            prevBtn.classList.add("hide");
                        }

                        prevBtn.setAttribute("onclick", `nxtQuestion('${response.questionID}' , 'prev' , '${immediately}' , '${examPostID}')`)


                        if (response.questionID == response.allPostsID[response.allPostsID.length - 1]) {

                            nextBtn.innerText = "Show Result";

                            nextBtn.setAttribute("onclick", `nxtQuestion('${response.questionID}' , 'show_result' , '${immediately}' , '${examPostID}')`)


                        } else {

                            nextBtn.innerText = "Next";

                            nextBtn.setAttribute("onclick", `nxtQuestion('${response.questionID}' , 'next' , '${immediately}' , '${examPostID}')`)

                        }

                    }


                }

            }
        })



    }

}