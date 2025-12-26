/**
 * WP Exams - Exam Functionality (FIXED VERSION - Issues #3 & #4)
 *
 * @package WPExams
 * @since 1.0.0
 */

(function($) {
    'use strict';

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
        const exitBtn = document.getElementById("wpexamsExitExam");
        
        if (nextBtn) nextBtn.disabled = true;
        if (prevBtn) prevBtn.disabled = true;
        if (exitBtn) exitBtn.disabled = true;

        // Make AJAX request
        $.ajax({
            url: wpexamsData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpexams_exam_navigation',
                nonce: wpexamsData.nonce,
                question_id: questionId,
                action_type: action,
                exam_id: examId,
                exam_time: examTime,
                question_time: questionTime,
                user_answer: userAnswer,
                show_immediate: showImmediate
            },
            success: function(response) {
                if (response.success) {
                    handleNavigationSuccess(response.data, showImmediate, examId);
                } else {
                    alert(response.data.message || wpexamsData.strings.error);
                }
                
                // Re-enable buttons
                if (nextBtn) nextBtn.disabled = false;
                if (prevBtn) prevBtn.disabled = false;
                if (exitBtn) exitBtn.disabled = false;
            },
            error: function() {
                alert(wpexamsData.strings.error);
                if (nextBtn) nextBtn.disabled = false;
                if (prevBtn) prevBtn.disabled = false;
                if (exitBtn) exitBtn.disabled = false;
            }
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
            displayExamResult(data, examId);
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
        
        // Hide result first
        const resultContainer = document.getElementById('wpexams-exam-result');
        if (resultContainer) {
            resultContainer.classList.add('wpexams-hide');
            resultContainer.classList.remove('wpexams-show');
        }
        
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

    }

    /**
     * Update navigation buttons
     */
    function updateNavigationButtons(data, showImmediate, examId) {
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const exitBtn = document.getElementById("wpexamsExitExam");
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");

        // Previous button
        if (prevBtn) {
            if (data.show_prev) {
                prevBtn.classList.remove("wpexams-hide");
                prevBtn.setAttribute('onclick', `wpexamsNextQuestion('${data.question_id}', 'prev', '${showImmediate}', '${examId}')`);
            } else {
                prevBtn.classList.add("wpexams-hide");
            }
        }

        // Next button
        if (nextBtn) {
            const isLastQuestion = !data.show_next;
            nextBtn.textContent = isLastQuestion ? (wpexamsData.strings.showResult || 'Show Result') : (wpexamsData.strings.next || 'Next');
            
            const nextAction = isLastQuestion ? 'show_result' : 'next';
            nextBtn.setAttribute('onclick', `wpexamsNextQuestion('${data.question_id}', '${nextAction}', '${showImmediate}', '${examId}')`);

            // Next button visibility depends on show_immediate setting
            if (showImmediate === '1') {
                // If showing answers immediately, next button hidden until submit
                nextBtn.classList.add("wpexams-hide");
            } else {
                // If not showing answers, next button always visible
                nextBtn.classList.remove("wpexams-hide");
            }
        }

        // Exit button
        if (exitBtn) {
            exitBtn.setAttribute('onclick', `wpexamsNextQuestion('${data.question_id}', 'exit', '${showImmediate}', '${examId}')`);
        }

        // Submit button - should be visible
        if (submitBtn && showImmediate === '1') {
            submitBtn.classList.remove("wpexams-hide");
            submitBtn.disabled = false; // CRITICAL: Re-enable button for next question
            submitBtn.setAttribute('onclick', `wpexamsSubmitAnswer('${data.question_id}', '${examId}')`);
        } else if (submitBtn) {
            // If not showing answers immediately, hide submit button
            submitBtn.classList.add("wpexams-hide");
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

        // Disable submit button
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");
        if (submitBtn) submitBtn.disabled = true;

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
                    if (submitBtn) submitBtn.disabled = false;
                }
            },
            error: function() {
                alert(wpexamsData.strings.error);
                if (submitBtn) submitBtn.disabled = false;
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

        // Update progress bar - FIXED: Use progress_percent from response
        if (data.progress_percent !== undefined) {
            updateProgressFromPercent(data.progress_percent, null, null);
        }

        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");
        
        if (nextBtn) {
            nextBtn.classList.remove("wpexams-hide");
            nextBtn.disabled = false; // Ensure next button is enabled
        }
        
        if (submitBtn) {
            submitBtn.classList.add("wpexams-hide");
            submitBtn.disabled = true; // Disable submit for current question
        }
    }

    /**
     * FIXED: Update progress from percentage value
     */
    function updateProgressFromPercent(percentage, currentId, allQuestionIds) {
        const progressContainer = document.querySelector('.wpexams-exam-progress');
        if (!progressContainer) return;

        const progressEl = progressContainer.querySelector('.wpexams-progress');
        const percentageEl = progressContainer.querySelector('.wpexams-percentage');
        const progressNb = progressContainer.querySelector('.wpexams-question-progress-nb');

        if (progressEl) progressEl.style.width = percentage + '%';
        if (percentageEl) {
            percentageEl.innerText = percentage + '%';
        }

        // Update question number if we have the IDs
        if (currentId && allQuestionIds) {
            let currentIndex = allQuestionIds.findIndex(id => parseInt(id) === parseInt(currentId));
            if (currentIndex !== -1 && progressNb) {
                progressNb.innerText = `${currentIndex + 1}/${allQuestionIds.length}`;
            }
        }
    }

    /**
     * Display exam result - FIXED VERSION
     */
    function displayExamResult(data, examId) {
        // Stop timers
        const pauseBtn = document.getElementById("wpexams_pause_timer");
        const startBtn = document.getElementById("wpexams_start_timer");
        if (pauseBtn) pauseBtn.classList.add("wpexams-d-none");
        if (startBtn) startBtn.classList.add("wpexams-d-none");

        if (window.wpexamsTimers['wpexamsTimedTimer']) {
            window.clearInterval(window.wpexamsTimers['wpexamsTimedTimer']);
        }
        if (window.wpexamsTimers['wpexamsUntimedTimer']) {
            window.clearInterval(window.wpexamsTimers['wpexamsUntimedTimer']);
        }
        if (window.wpexamsTimers['wpexamsQuestionTimer']) {
            window.clearInterval(window.wpexamsTimers['wpexamsQuestionTimer']);
        }

        // Hide question title
        const titleElement = document.getElementById("wpexams-exam-question-title");
        if (titleElement) titleElement.innerHTML = "";

        // Clear question container
        const questionContainer = document.getElementById('wpexams-questions-tbody-container');
        if (questionContainer) {
            questionContainer.innerHTML = "";
        }

        // Hide explanation
        const explanation = document.getElementById("wpexams-questions-explanation-immed");
        if (explanation) {
            explanation.classList.add("wpexams-hide");
        }

        // Hide navigation buttons
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        const submitBtn = document.getElementById("wpexamsSubmitQuestion");
        
        if (nextBtn) nextBtn.style.display = 'none';
        if (prevBtn) prevBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'none';

        // Get or create result container
        let resultContainer = document.getElementById('wpexams-exam-result');
        if (!resultContainer) {
            resultContainer = document.createElement('div');
            resultContainer.id = 'wpexams-exam-result';
            resultContainer.className = 'wpexams-exam-result';
            if (questionContainer && questionContainer.parentNode) {
                questionContainer.parentNode.insertBefore(resultContainer, questionContainer);
            }
        }

        // Calculate score
        const correctCount = data.result.correct_count || 0;
        const totalCount = data.result.total_count || 0;
        const percentage = totalCount > 0 ? Math.round((correctCount / totalCount) * 100) : 0;
        const examTime = data.result.exam_time || '00:00:00';

        // Build result HTML
        let html = '<div style="background: #fff; padding: 30px; border: 1px solid #ddd; border-radius: 8px; text-align: center; margin: 20px 0;">';
        html += '<h2 style="color: #333; margin: 0 0 20px 0;">' + (wpexamsData.strings.examCompleted || 'Exam Completed!') + '</h2>';
        html += '<div style="background: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;">';
        html += '<div style="font-size: 48px; font-weight: bold; color: ' + (percentage >= 50 ? '#4caf50' : '#f44336') + ';">' + percentage + '%</div>';
        html += '<div style="font-size: 24px; margin: 10px 0; color: #555;">Score: ' + correctCount + '/' + totalCount + '</div>';
        
        if (examTime !== 'expired') {
            html += '<div style="font-size: 18px; color: #666;">Time: ' + examTime + '</div>';
        } else {
            html += '<div style="font-size: 18px; color: #f44336;">Time Expired</div>';
        }
        
        html += '</div>';
        
        // Action buttons
        html += '<div style="margin-top: 30px;">';
        html += '<a href="?wpexams_review_id=' + examId + '" class="wpexams-button wpexams-exam-button" style="margin: 5px; text-decoration: none; display: inline-block;">';
        html += (wpexamsData.strings.reviewAnswers || 'Review Answers');
        html += '</a>';
        html += '<a href="?wpexams_history" class="wpexams-button wpexams-exam-button" style="margin: 5px; text-decoration: none; display: inline-block;">';
        html += (wpexamsData.strings.viewHistory || 'View History');
        html += '</a>';
        html += '<button onclick="wpexamsExitExam()" class="wpexams-button wpexams-exam-button" style="margin: 5px;">';
        html += (wpexamsData.strings.backToHome || 'Back to Home');
        html += '</button>';
        html += '</div>';
        html += '</div>';

        resultContainer.innerHTML = html;
        resultContainer.classList.add('wpexams-show');
        resultContainer.classList.remove('wpexams-hide');

        // Scroll to result
        resultContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
        const titleElement = document.getElementById("wpexams-exam-question-title");
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
            explanation.classList.remove("wpexams-hide");
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
                const label = document.createElement('label');
                const div = document.createElement('div');
                
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
                td.appendChild(label);
                label.appendChild(div);
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