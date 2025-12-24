/**
 * WP Exams - Frontend Exam Functionality
 *
 * Handles exam navigation, answer submission, and interactions
 *
 * @package WPExams
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Handle next/previous question navigation
     */
    window.wpexamsNextQuestion = function(questionId, action, showImmediate, examId) {
        const questionTime = document.getElementById("wpexams_question_timer").innerText;
        const examTime = document.getElementById("wpexams_exam_timer").innerText;

        // Validate parameters
        if (!examTime || !questionTime || !questionId || !action || !showImmediate || !examId) {
            alert(wpexamsData.strings.error);
            return;
        }

        // Handle exit confirmation
        if (action === "exit") {
            if (!confirm(wpexamsData.strings.confirmExit)) {
                return;
            }
        }

        // Get user's answer
        let userAnswer;
        const selectedAnswer = document.querySelector("input[name=wpexams_question_options]:checked");
        
        if (selectedAnswer) {
            userAnswer = selectedAnswer.value;
        } else {
            userAnswer = "null";
        }

        // Disable buttons during request
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        
        if (nextBtn) nextBtn.setAttribute("disabled", true);
        if (prevBtn) prevBtn.setAttribute("disabled", false);

        // Send AJAX request
        $.ajax({
            method: 'POST',
            url: wpexamsData.ajaxUrl,
            dataType: 'json',
            data: {
                action: 'wpexams_exam_navigation',
                nonce: wpexamsData.nonce,
                exam_time: examTime,
                question_time: questionTime,
                question_id: questionId,
                action_type: action,
                show_immediate: showImmediate,
                user_answer: userAnswer,
                exam_id: examId
            },
            success: function(response) {
                if (!response.success) {
                    alert(response.data.message || wpexamsData.strings.error);
                    if (nextBtn) nextBtn.removeAttribute("disabled");
                    return;
                }

                const data = response.data;

                // Handle exit
                if (data.action === 'exit') {
                    wpexamsExitExam();
                    return;
                }

                // Handle result display
                if (data.action === 'show_result') {
                    wpexamsDisplayResult(data.result, examId);
                    return;
                }

                // Handle next question
                if (data.action === 'show_question') {
                    wpexamsDisplayQuestion(data, showImmediate, examId);
                }

                // Re-enable buttons
                if (nextBtn) nextBtn.removeAttribute("disabled");
                if (prevBtn) prevBtn.removeAttribute("disabled");
            },
            error: function() {
                alert(wpexamsData.strings.error);
                if (nextBtn) nextBtn.removeAttribute("disabled");
            }
        });
    };

    /**
     * Display question
     */
    function wpexamsDisplayQuestion(data, showImmediate, examId) {
        // Restart question timer
        wpexamsRestartTimer(0, 0, 0, "wpexamsQuestionTimer");
        wpexamsQuestionCountdownTimer(0, 0, 0, "wpexamsQuestionTimer");

        // Update question title
        const titleElement = document.getElementById("wpexams-exam-question-title");
        if (titleElement) {
            titleElement.innerText = data.question_title;
            
            const timerSpan = document.createElement("span");
            timerSpan.setAttribute("class", "wpexams-f-right");
            timerSpan.setAttribute("id", "wpexams_question_timer");
            titleElement.appendChild(timerSpan);
        }

        // Update question container
        const questionContainer = document.getElementById('wpexams-questions-tbody-container');
        if (!questionContainer) return;

        questionContainer.innerHTML = "";

        // Hide explanation
        const explanation = document.getElementById("wpexams-questions-explanation-immed");
        if (explanation) {
            explanation.classList.add("wpexams-hide");
        }

        // Add options
        data.question_options.forEach((option, index) => {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            const label = document.createElement('label');
            const div = document.createElement('div');
            const input = document.createElement('input');
            
            const optSpan = document.createElement('span');
            optSpan.setAttribute('class', 'wpexams-alpha-options');
            optSpan.textContent = (index + 1);

            input.setAttribute('name', 'wpexams_question_options');
            input.setAttribute('id', `wpexams_question_option${index + 1}`);
            input.setAttribute('type', 'radio');
            input.setAttribute('value', index);

            label.setAttribute('for', `wpexams_question_option${index + 1}`);

            const text = document.createTextNode(` ${option.replace(/_/g, ' ')}`);
            
            div.appendChild(optSpan);
            div.appendChild(input);
            div.appendChild(text);
            label.appendChild(div);
            td.appendChild(label);
            tr.appendChild(td);

            questionContainer.appendChild(tr);
        });

        // Update buttons
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");

        if (prevBtn) {
            if (data.show_prev) {
                prevBtn.classList.remove("wpexams-hide");
                prevBtn.setAttribute("onclick", `wpexamsNextQuestion('${data.question_id}', 'prev', '${showImmediate}', '${examId}')`);
            } else {
                prevBtn.classList.add("wpexams-hide");
            }
        }

        if (nextBtn) {
            if (data.show_next) {
                nextBtn.innerText = wpexamsData.strings.next || 'Next';
                nextBtn.setAttribute("onclick", `wpexamsNextQuestion('${data.question_id}', 'next', '${showImmediate}', '${examId}')`);
            } else {
                nextBtn.innerText = wpexamsData.strings.showResult || 'Show Result';
                nextBtn.setAttribute("onclick", `wpexamsNextQuestion('${data.question_id}', 'show_result', '${showImmediate}', '${examId}')`);
            }

            if (showImmediate === '1') {
                nextBtn.classList.add("wpexams-hide");
            } else {
                nextBtn.classList.remove("wpexams-hide");
            }
        }

        if (submitBtn) {
            if (showImmediate === '1') {
                submitBtn.classList.remove("wpexams-hide");
                submitBtn.setAttribute("onclick", `wpexamsSubmitAnswer('${data.question_id}', '${examId}')`);
            } else {
                submitBtn.classList.add("wpexams-hide");
            }
        }

        // Update progress
        if (data.progress_percent !== undefined) {
            wpexamsUpdateProgress(data.progress_percent);
        }
    }

    /**
     * Submit answer immediately
     */
    window.wpexamsSubmitAnswer = function(questionId, examId) {
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");

        const questionTime = document.getElementById("wpexams_question_timer").innerText;
        const examTime = document.getElementById("wpexams_exam_timer").innerText;

        const selectedAnswer = document.querySelector("input[name=wpexams_question_options]:checked");

        if (!selectedAnswer) {
            alert('Please select an answer');
            return;
        }

        const userAnswer = selectedAnswer.value;

        $.ajax({
            method: 'POST',
            url: wpexamsData.ajaxUrl,
            dataType: 'json',
            data: {
                action: 'wpexams_submit_answer',
                nonce: wpexamsData.nonce,
                exam_time: examTime,
                question_time: questionTime,
                user_answer: userAnswer,
                question_id: questionId,
                exam_id: examId
            },
            success: function(response) {
                if (!response.success) {
                    alert(response.data.message || wpexamsData.strings.error);
                    return;
                }

                const data = response.data;

                // Show correct/incorrect for each option
                const options = document.querySelectorAll("input[name=wpexams_question_options]");
                options.forEach((option, index) => {
                    const parent = option.parentNode;
                    
                    // Add correct/incorrect indicator
                    const indicator = document.createElement('span');
                    if (data.correct_option == index) {
                        indicator.setAttribute('class', 'wpexams-immed-answer-is-true');
                        indicator.textContent = ' ✓';
                        parent.querySelector('.wpexams-alpha-options').classList.add('wpexams-green');
                    } else {
                        indicator.setAttribute('class', 'wpexams-immed-answer-is-false');
                        indicator.textContent = ' ✗';
                        parent.querySelector('.wpexams-alpha-options').classList.add('wpexams-red');
                    }
                    
                    if (!parent.querySelector('.wpexams-immed-answer-is-true, .wpexams-immed-answer-is-false')) {
                        parent.appendChild(indicator);
                    }
                });

                // Show explanation
                const explanation = document.getElementById("wpexams-questions-explanation-immed");
                if (explanation && data.explanation) {
                    explanation.innerHTML = `<strong>${wpexamsData.strings.explanation || 'Explanation'}:</strong> ${data.explanation}`;
                    explanation.classList.remove("wpexams-hide");
                }

                // Show next button, hide submit
                if (nextBtn) nextBtn.classList.remove("wpexams-hide");
                if (submitBtn) submitBtn.classList.add("wpexams-hide");
            },
            error: function() {
                alert(wpexamsData.strings.error);
            }
        });
    };

    /**
     * Display exam result
     */
    function wpexamsDisplayResult(result, examId) {
        const resultContainer = document.getElementById('wpexams-exam-result');
        if (!resultContainer) return;

        resultContainer.classList.add('wpexams-show');
        resultContainer.classList.remove('wpexams-hide');

        // Hide other elements
        const questionContainer = document.getElementById('wpexams-exam-question-title');
        if (questionContainer) questionContainer.innerHTML = "";

        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");
        
        if (nextBtn) nextBtn.remove();
        if (prevBtn) prevBtn.remove();
        if (submitBtn) submitBtn.remove();

        // Calculate score
        const correctCount = result.correct_count || 0;
        const totalCount = result.total_count || 0;

        // Build result HTML
        let html = `<div class="wpexams-result-summary">`;
        html += `<h3>${wpexamsData.strings.score || 'Score'}: ${correctCount}/${totalCount}</h3>`;
        html += `<a href="?wpexams_review_id=${examId}" class="wpexams-button">${wpexamsData.strings.review || 'Review'}</a>`;
        html += `</div>`;

        resultContainer.innerHTML = html;
    }

    /**
     * Update progress bar
     */
    function wpexamsUpdateProgress(percent) {
        const progressContainer = document.querySelector('.wpexams-progress-container');
        if (!progressContainer) return;

        progressContainer.setAttribute('data-percentage', percent);
        
        const progressBar = progressContainer.querySelector('.wpexams-progress');
        const percentageText = progressContainer.querySelector('.wpexams-percentage');
        
        if (progressBar) progressBar.style.width = percent + '%';
        if (percentageText) {
            percentageText.innerText = percent + '%';
            percentageText.style.left = percent + '%';
        }
    }

    /**
     * Review exam navigation
     */
    window.wpexamsReviewQuestion = function(questionId, action, examId) {
        $.ajax({
            method: 'POST',
            url: wpexamsData.ajaxUrl,
            dataType: 'json',
            data: {
                action: 'wpexams_review_exam',
                nonce: wpexamsData.nonce,
                question_id: questionId,
                action_type: action,
                exam_id: examId
            },
            success: function(response) {
                if (!response.success) {
                    alert(response.data.message || wpexamsData.strings.error);
                    return;
                }

                const data = response.data;
                wpexamsDisplayReviewQuestion(data, examId);
            },
            error: function() {
                alert(wpexamsData.strings.error);
            }
        });
    };

    /**
     * Display review question
     */
    function wpexamsDisplayReviewQuestion(data, examId) {
        // Update title
        const titleElement = document.getElementById("wpexams-exam-question-title");
        if (titleElement) {
            titleElement.innerText = data.question_title;
            
            const timeSpan = document.createElement("span");
            timeSpan.setAttribute("class", "wpexams-f-right");
            timeSpan.textContent = data.question_time;
            titleElement.appendChild(timeSpan);
        }

        // Update options
        const questionContainer = document.getElementById('wpexams-questions-tbody-container');
        if (!questionContainer) return;

        questionContainer.innerHTML = "";

        data.question_options.forEach((option, index) => {
            const isCorrect = (data.correct_option === `wpexams_c_option_${index}`);
            const isSelected = (data.user_answer == index);

            const tr = document.createElement('tr');
            if (isSelected) {
                tr.setAttribute('class', 'wpexams-subscriber-answer-sl');
            }

            const td = document.createElement('td');
            const div = document.createElement('div');
            div.style.display = 'flex';
            div.style.alignItems = 'center';

            const optSpan = document.createElement('span');
            optSpan.setAttribute('class', `wpexams-alpha-options ${isCorrect ? 'wpexams-green' : 'wpexams-red'}`);
            optSpan.textContent = (index + 1);

            const textSpan = document.createElement('span');
            textSpan.style.flexGrow = '1';
            textSpan.textContent = ` ${option.replace(/_/g, ' ')}`;

            const indicator = document.createElement('span');
            indicator.setAttribute('class', isCorrect ? 'wpexams-immed-answer-is-true' : 'wpexams-immed-answer-is-false');
            indicator.textContent = isCorrect ? ' ✓' : ' ✗';

            div.appendChild(optSpan);
            div.appendChild(textSpan);
            div.appendChild(indicator);
            td.appendChild(div);
            tr.appendChild(td);

            questionContainer.appendChild(tr);
        });

        // Update explanation
        const explanation = document.getElementById("wpexams-questions-explanation-immed");
        if (explanation && data.explanation) {
            explanation.innerHTML = `<strong>${wpexamsData.strings.explanation || 'Explanation'}:</strong> ${data.explanation}`;
        }

        // Update buttons
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");

        if (prevBtn) {
            const isFirst = (data.question_id === data.all_question_ids[0]);
            if (isFirst) {
                prevBtn.classList.add("wpexams-hide");
            } else {
                prevBtn.classList.remove("wpexams-hide");
                prevBtn.setAttribute("onclick", `wpexamsReviewQuestion('${data.question_id}', 'prev', '${examId}')`);
            }
        }

        if (nextBtn) {
            const isLast = (data.question_id === data.all_question_ids[data.all_question_ids.length - 1]);
            if (isLast) {
                nextBtn.classList.add("wpexams-hide");
            } else {
                nextBtn.classList.remove("wpexams-hide");
                nextBtn.setAttribute("onclick", `wpexamsReviewQuestion('${data.question_id}', 'next', '${examId}')`);
            }
        }
    }

    /**
     * Reset question bank
     */
    $(document).on('click', '#wpexams-reset-question-bank', function() {
        if (!confirm(wpexamsData.strings.confirmReset)) {
            return false;
        }

        const messageEl = document.getElementById('wpexams-reset-question-bank-message');

        $.ajax({
            method: 'POST',
            url: wpexamsData.ajaxUrl,
            dataType: 'json',
            data: {
                action: 'wpexams_reset_question_bank',
                nonce: wpexamsData.nonce
            },
            success: function(response) {
                if (messageEl) {
                    if (response.success) {
                        messageEl.style.color = 'green';
                        messageEl.innerText = response.data.message;
                    } else {
                        messageEl.style.color = 'red';
                        messageEl.innerText = response.data.message || wpexamsData.strings.error;
                    }
                }
            },
            error: function() {
                if (messageEl) {
                    messageEl.style.color = 'red';
                    messageEl.innerText = wpexamsData.strings.error;
                }
            }
        });

        return false;
    });

    /**
     * Handle exam expiration
     */
    window.wpexamsExamExpired = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const examId = urlParams.get('wpexams_exam_id');

        $.ajax({
            method: 'POST',
            url: wpexamsData.ajaxUrl,
            dataType: 'json',
            data: {
                action: 'wpexams_exam_expired',
                nonce: wpexamsData.nonce,
                exam_id: examId
            },
            success: function(response) {
                if (response.data && response.data.exam_time === "expired") {
                    wpexamsExitExam();
                }
            }
        });

        return false;
    };

})(jQuery);