/**
 * WP Exams - Exam Functionality
 *
 * @package WPExams
 * @since 1.0.0
 */

(function($) {
    'use strict';
    console.log('file is loaded');

    $(document).on('click', '#wpexamsNextQuestion, #wpexamsPrevQuestion, #wpexamsExitExam', function(e) {
        e.preventDefault();
        const btn = $(this);
        const questionId = btn.data('question');
        const action = btn.data('action');
        const examId = btn.data('exam');
        
        // Call the logic
        window.wpexamsNextQuestion(questionId, action, '0', examId);
    });

    /**
     * Navigate to next/previous question
     */
    window.wpexamsNextQuestion = function(questionId, action, showImmediate, examId) {
        if (!questionId || !action || !examId) {
            alert(wpexamsData.strings.error);
            return;
        }

        const questionTime = document.getElementById("wpexams_question_timer") ? document.getElementById("wpexams_question_timer").innerText : "00:00:00";
        const examTime = document.getElementById("wpexams_exam_timer") ? document.getElementById("wpexams_exam_timer").innerText : "00:00:00";

        // Handle exit action
        if (action === "exit") {
            if (!confirm(wpexamsData.strings.confirmExit)) {
                return;
            }
        }

        // Get user's answer
        let userAnswer = "null";
        const selectedAnswer = document.querySelector("input[name=wpexams_question_options]:checked");
        if (selectedAnswer) {
            userAnswer = selectedAnswer.value;
        }

        // Disable buttons during request
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        if (nextBtn) nextBtn.disabled = true;
        if (prevBtn) prevBtn.disabled = true;

        console.log(wpexamsData.nonce);
        console.log(questionId);
        console.log(action);
        console.log(examId);
        console.log(examTime);
        console.log(questionTime);
        console.log(userAnswer);
        console.log(showImmediate);

        const fd = new FormData();

        // Core required fields
        fd.append('action', 'wpexams_exam_navigation');
        fd.append('nonce', wpexamsData.nonce);
        fd.append('question_id', questionId);
        fd.append('action_type', action);
        fd.append('exam_id', examId);
        fd.append('exam_time', examTime);
        fd.append('question_time', questionTime);
        fd.append('user_answer', userAnswer);
        fd.append('show_immediate', showImmediate);
        
        fetch(wpexamsData.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(response => {
            console.log(response);
            if (response.success) {
                const data = response.data;
                if (data.action === 'show_question') {
                    handleNavigationSuccess(data, showImmediate, examId);
                    // Update the buttons to use the NEW question ID for the next click
                    $('#wpexamsNextQuestion, #wpexamsPrevQuestion, #wpexamsExitExam')
                        .data('question', data.question_id)
                        .attr('data-question', data.question_id); 
                }
            } else {
                alert(response.data.message || wpexamsData.strings.error);
            }
            
            // Re-enable buttons
            if (nextBtn) nextBtn.disabled = false;
            if (prevBtn) prevBtn.disabled = false;
        })
        .catch(error => {
            console.log(error);
            alert(wpexamsData.strings.error);
            if (nextBtn) nextBtn.disabled = false;
            if (prevBtn) prevBtn.disabled = false;
        })
        .finally(() => {
            //$('#wpwa-loading-overlay').fadeOut(200);
        });
    };

    /**
     * Handle successful navigation response
     */
    function handleNavigationSuccess(data, showImmediate, examId) {
        if (data.action === 'exit') {
            window.wpexamsExitExam();
            return;
        }

        if (data.action === 'show_result') {
            displayExamResult(data);
            return;
        }

        if (data.action === 'show_question') {
            displayNextQuestion(data, showImmediate, examId);
        }
    }

    /**
     * Display next question
     */
    function displayNextQuestion(data, showImmediate, examId) {
        // Reset question timer
        wpexamsRestartTimer(0, 0, 0, "wpexamsQuestionTimer");
        wpexamsQuestionCountdownTimer(0, 0, 0, "wpexamsQuestionTimer");

        // Update question title
        const titleElement = document.getElementById("wpexams-exam-question-title");
        if (titleElement) {
            titleElement.innerHTML = data.question_title + ' <span class="wpexams-f-right" id="wpexams_question_timer"></span>';
        }

        // Clear options container
        const container = document.getElementById('wpexams-questions-tbody-container');
        if (!container) return;
        
        container.innerHTML = "";

        // Hide explanation
        const explanation = document.getElementById("wpexams-questions-explanation-immed");
        if (explanation) {
            explanation.classList.add("wpexams-hide");
            explanation.innerHTML = "";
        }

        // Add question options
        if (data.question_options) {
            Object.keys(data.question_options).forEach(function(key) {
                const option = data.question_options[key];
                const optionNum = parseInt(key) + 1;
                
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                const label = document.createElement('label');
                const div = document.createElement('div');
                
                label.setAttribute('for', 'wpexams_question_option' + optionNum);
                
                const span = document.createElement('span');
                span.className = 'wpexams-alpha-options';
                span.textContent = optionNum;
                
                const input = document.createElement('input');
                input.id = 'wpexams_question_option' + optionNum;
                input.type = 'radio';
                input.name = 'wpexams_question_options';
                input.value = key;
                
                const text = document.createTextNode(' ' + option.replace(/_/g, ' '));
                
                div.appendChild(span);
                div.appendChild(input);
                div.appendChild(text);
                label.appendChild(div);
                td.appendChild(label);
                tr.appendChild(td);
                container.appendChild(tr);
            });
        }

        // Update navigation buttons
        updateNavigationButtons(data, showImmediate, examId);

        // Update progress
        if (data.progress_percent !== undefined) {
            updateProgress(data.current_id, data.all_question_ids, data.question_id);
        }
    }

    /**
     * Update navigation buttons
     */
    function updateNavigationButtons(data, showImmediate, examId) {
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const exitBtn = document.getElementById("wpexamsExitExam");
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");

        // Helper to update jQuery data and DOM attributes simultaneously
        const updateBtnData = (el, qId, action) => {
            if (!el) return;
            el.setAttribute('data-question', qId);
            el.setAttribute('data-action', action);
            // Also update jQuery's internal cache if you use $(el).data()
            jQuery(el).data('question', qId);
            jQuery(el).data('action', action);
        };

        // 1. Handle Previous Button
        if (prevBtn) {
            if (data.show_prev) {
                prevBtn.classList.remove("wpexams-hide");
                updateBtnData(prevBtn, data.question_id, 'prev');
            } else {
                prevBtn.classList.add("wpexams-hide");
            }
        }

        // 2. Handle Next / Show Result Button
        if (nextBtn) {
            const isLastQuestion = !data.show_next;
            const nextAction = isLastQuestion ? 'show_result' : 'next';
            
            // Update Text
            nextBtn.textContent = isLastQuestion ? (wpexamsData.strings.showResult || 'Show Result') : (wpexamsData.strings.next || 'Next');
            
            // Update Data Attributes
            updateBtnData(nextBtn, data.question_id, nextAction);

            // Visibility based on immediate feedback setting
            if (showImmediate === '1') {
                nextBtn.classList.add("wpexams-hide");
            } else {
                nextBtn.classList.remove("wpexams-hide");
            }
        }

        // 3. Handle Exit Button
        if (exitBtn) {
            updateBtnData(exitBtn, data.question_id, 'exit');
        }

        // 4. Handle Submit Button (for immediate feedback mode)
        if (submitBtn && showImmediate === '1') {
            submitBtn.classList.remove("wpexams-hide");
            updateBtnData(submitBtn, data.question_id, 'submit');
        }
    }

    /**
     * Submit answer immediately
     */
    window.wpexamsSubmitAnswer = function(questionId, examId) {
        const selectedAnswer = document.querySelector("input[name=wpexams_question_options]:checked");
        
        if (!selectedAnswer) {
            alert('Please select an answer');
            return;
        }

        const questionTime = document.getElementById("wpexams_question_timer").innerText;
        const examTime = document.getElementById("wpexams_exam_timer").innerText;

        $.ajax({
            url: wpexamsData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpexams_submit_answer',
                nonce: wpexamsData.nonce,
                question_id: questionId,
                exam_id: examId,
                user_answer: selectedAnswer.value,
                exam_time: examTime,
                question_time: questionTime
            },
            success: function(response) {
                if (response.success) {
                    showImmediateAnswer(response.data);
                } else {
                    alert(response.data.message || wpexamsData.strings.error);
                }
            },
            error: function() {
                alert(wpexamsData.strings.error);
            }
        });
    };

    /**
     * Show immediate answer feedback
     */
    function showImmediateAnswer(data) {
        // Remove previous selections
        const allOptions = document.querySelectorAll("input[name=wpexams_question_options]");
        allOptions.forEach(function(option) {
            const parent = option.closest('tr');
            if (parent) {
                parent.classList.remove("wpexams-subscriber-answer");
            }
        });

        // Highlight correct/wrong answers
        allOptions.forEach(function(option, index) {
            const parent = option.parentElement;
            const alphaSpan = parent.querySelector('.wpexams-alpha-options');
            
            // Remove existing feedback
            const existingFeedback = parent.querySelectorAll('.wpexams-immed-answer-is-true, .wpexams-immed-answer-is-false');
            existingFeedback.forEach(el => el.remove());
            
            if (data.correct_option == index) {
                // Correct answer
                if (alphaSpan) alphaSpan.classList.add('wpexams-green');
                const checkmark = document.createElement('span');
                checkmark.className = 'wpexams-immed-answer-is-true';
                checkmark.textContent = ' ✓ ';
                parent.appendChild(checkmark);
            } else {
                // Wrong answer
                if (alphaSpan) alphaSpan.classList.add('wpexams-red');
                const cross = document.createElement('span');
                cross.className = 'wpexams-immed-answer-is-false';
                cross.textContent = ' ✗ ';
                parent.appendChild(cross);
            }

            // Highlight user's selection
            if (option.checked) {
                const tr = option.closest('tr');
                if (tr) tr.classList.add("wpexams-subscriber-answer");
            }
        });

        // Show explanation
        const explanation = document.getElementById("wpexams-questions-explanation-immed");
        if (explanation && data.explanation) {
            explanation.innerHTML = '<strong>Explanation:</strong> ' + data.explanation;
            explanation.classList.remove("wpexams-hide");
        }

        // Show next button, hide submit button
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");
        
        if (nextBtn) nextBtn.classList.remove("wpexams-hide");
        if (submitBtn) submitBtn.classList.add("wpexams-hide");
    }

    /**
     * Display exam result
     */
    function displayExamResult(data) {
        // Stop timers
        const startTimer = document.getElementById("wpexams_start_timer");
        if (startTimer) {
            setTimeout(function() {
                startTimer.classList.add("wpexams-d-none");
            }, 1000);
        }

        if (window.wpexamsTimers['wpexamsTimedTimer']) {
            wpexamsPauseTimer("wpexamsTimedTimer", "wpexamsQuestionTimer");
        } else {
            wpexamsPauseTimer("wpexamsUntimedTimer", "wpexamsQuestionTimer");
        }

        // Hide navigation buttons
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        if (nextBtn) nextBtn.remove();
        if (prevBtn) prevBtn.remove();

        // Clear question title
        const titleElement = document.getElementById("wpexams_exam_question_title");
        if (titleElement) titleElement.innerHTML = "";

        // Show result
        const resultContainer = document.getElementById('wpexams-exam-result');
        if (resultContainer) {
            resultContainer.classList.add('wpexams-show');
            resultContainer.classList.remove('wpexams-hide');
            
            // Add score
            const correctCount = data.result.correct_count || 0;
            const totalCount = data.result.total_count || 0;
            
            let html = `<h5>Score ${correctCount}/${totalCount}</h5>`;
            html += `<a class='wpexams-f-right' href='?wpexams_review_id=${data.exam_id}'>Review</a>`;
            
            resultContainer.innerHTML = html;
        }
    }

    /**
     * Update progress bar
     */
    function updateProgress(currentId, allQuestionIds, nextId) {
        const progressContainer = document.querySelector('.wpexams-exam-progress');
        if (!progressContainer) return;

        let currentIndex = allQuestionIds.indexOf(parseInt(currentId));
        let nextIndex = allQuestionIds.indexOf(parseInt(nextId));
        
        if (currentIndex === -1) currentIndex = 0;
        if (nextIndex === -1) nextIndex = currentIndex + 1;

        const percentage = Math.round((nextIndex / allQuestionIds.length) * 100);

        const progressEl = progressContainer.querySelector('.wpexams-progress');
        const percentageEl = progressContainer.querySelector('.wpexams-percentage');
        const progressNb = progressContainer.querySelector('.wpexams-question-progress-nb');

        if (progressEl) progressEl.style.width = percentage + '%';
        if (percentageEl) {
            percentageEl.innerText = percentage + '%';
            percentageEl.style.left = percentage + '%';
        }
        if (progressNb) {
            progressNb.innerText = `${nextIndex}/${allQuestionIds.length}`;
        }
    }

    /**
     * Review exam question navigation
     */
    window.wpexamsReviewQuestion = function(questionId, action, examId) {
        $.ajax({
            url: wpexamsData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpexams_review_exam',
                nonce: wpexamsData.nonce,
                question_id: questionId,
                action_type: action,
                exam_id: examId
            },
            success: function(response) {
                if (response.success) {
                    displayReviewQuestion(response.data, examId);
                } else {
                    alert(response.data.message || wpexamsData.strings.error);
                }
            },
            error: function() {
                alert(wpexamsData.strings.error);
            }
        });
    };

    /**
     * Display review question
     */
    function displayReviewQuestion(data, examId) {
        // Update title
        const titleElement = document.getElementById("wpexams_exam_question_title");
        if (titleElement) {
            titleElement.innerHTML = data.question_title + ' <span class="wpexams-f-right">' + data.question_time + '</span>';
        }

        // Clear container
        const container = document.getElementById('wpexams-questions-tbody-container');
        if (!container) return;
        container.innerHTML = "";

        // Show explanation
        const explanation = document.getElementById("wpexams-questions-explanation-immed");
        if (explanation) {
            explanation.innerHTML = '<strong>Explanation:</strong> ' + data.explanation;
        }

        // Add options
        if (data.question_options) {
            Object.keys(data.question_options).forEach(function(key) {
                const option = data.question_options[key];
                const optionNum = parseInt(key) + 1;
                const isCorrect = data.correct_option === 'wpexams_c_option_' + key;
                const isSelected = data.user_answer == key;
                
                const tr = document.createElement('tr');
                if (isSelected) tr.className = 'wpexams-subscriber-answer-sl';
                
                const td = document.createElement('td');
                td.style.padding = '10px';
                
                const div = document.createElement('div');
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                
                const span = document.createElement('span');
                span.className = 'wpexams-alpha-options ' + (isCorrect ? 'wpexams-green' : 'wpexams-red');
                span.textContent = optionNum;
                
                const textSpan = document.createElement('span');
                textSpan.style.flexGrow = '1';
                textSpan.textContent = ' ' + option.replace(/_/g, ' ');
                
                const feedback = document.createElement('span');
                feedback.className = isCorrect ? 'wpexams-immed-answer-is-true' : 'wpexams-immed-answer-is-false';
                feedback.textContent = isCorrect ? ' ✓ ' : ' ✗ ';
                
                div.appendChild(span);
                div.appendChild(textSpan);
                div.appendChild(feedback);
                td.appendChild(div);
                tr.appendChild(td);
                container.appendChild(tr);
            });
        }

        // Update navigation buttons
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        const nextBtn = document.getElementById("wpexamsNextQuestion");

        const isFirstQuestion = data.all_question_ids && data.question_id === data.all_question_ids[0];
        const isLastQuestion = data.all_question_ids && data.question_id === data.all_question_ids[data.all_question_ids.length - 1];

        if (prevBtn) {
            if (isFirstQuestion) {
                prevBtn.classList.add("wpexams-hide");
            } else {
                prevBtn.classList.remove("wpexams-hide");
                prevBtn.setAttribute("onclick", `wpexamsReviewQuestion('${data.question_id}', 'prev', '${examId}')`);
            }
        }

        if (nextBtn) {
            if (isLastQuestion) {
                nextBtn.classList.add("wpexams-hide");
            } else {
                nextBtn.classList.remove("wpexams-hide");
                nextBtn.setAttribute("onclick", `wpexamsReviewQuestion('${data.question_id}', 'next', '${examId}')`);
            }
        }
    }

    /**
     * Handle exam expiration
     */
    window.wpexamsExamExpired = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const examId = urlParams.get('wpexams_exam_id');
        
        if (!examId) return;

        $.ajax({
            url: wpexamsData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpexams_exam_expired',
                nonce: wpexamsData.nonce,
                exam_id: examId
            },
            success: function(response) {
                if (response.success) {
                    window.wpexamsExitExam();
                }
            }
        });
    };

    /**
     * Reset question bank
     */
    $(document).on('click', '#wpexams-reset-question-bank', function() {
        if (!confirm(wpexamsData.strings.confirmReset)) {
            return;
        }

        $.ajax({
            url: wpexamsData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpexams_reset_question_bank',
                nonce: wpexamsData.nonce
            },
            success: function(response) {
                const messageEl = document.getElementById('wpexams-reset-question-bank-message');
                if (messageEl) {
                    if (response.success) {
                        messageEl.style.color = 'green';
                        messageEl.textContent = response.data.message;
                    } else {
                        messageEl.style.color = 'red';
                        messageEl.textContent = response.data.message || 'Error occurred';
                    }
                }
            },
            error: function() {
                const messageEl = document.getElementById('wpexams-reset-question-bank-message');
                if (messageEl) {
                    messageEl.style.color = 'red';
                    messageEl.textContent = 'Error occurred';
                }
            }
        });
    });

})(jQuery);