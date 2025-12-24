/**
 * WP Exams Main JavaScript
 *
 * @package WPExams
 * @since 2.0.0
 */

(function($) {
    'use strict';

    // Global timer object
    window.wpexamsTimers = {};

    /**
     * Document ready
     */
    $(document).ready(function() {
        // Initialize question timer
        wpexamsQuestionCountdownTimer(0, 0, 0, "questiontimer");

        // Handle radio button selection
        handleRadioSelection();

        // Handle accordion
        handleAccordion();
    });

    /**
     * Handle radio button selection
     */
    function handleRadioSelection() {
        $(document).on('click', 'input[name=wpexams_question_options]', function() {
            const radioButtons = document.querySelectorAll("input[name=wpexams_question_options]");
            
            radioButtons.forEach(function(radio) {
                const parent = radio.closest('tr') || radio.closest('label').parentElement;
                
                if (radio.checked) {
                    parent.classList.add("wpexams-subscriber-answer");
                } else {
                    parent.classList.remove("wpexams-subscriber-answer");
                }
            });
        });
    }

    /**
     * Handle accordion collapse/expand
     */
    function handleAccordion() {
        $(document).on('click', '#wpexamsAccordion div', function() {
            $("#wpexamsAccordion ul ul").slideUp();
            
            if ($(this).next().is(":hidden")) {
                $(this).next().slideDown();
            }
        });
    }

    /**
     * Exit exam function
     */
    window.wpexamsExitExam = function() {
        const url = new URL(location.href);
        url.searchParams.delete('wpexams_exam_id');
        url.searchParams.delete('wpexams_review_id');
        window.location.href = url.href;
    };

    /**
     * Reload page
     */
    window.wpexamsReloadPage = function() {
        window.location.reload();
    };

    /**
     * Question countdown timer
     */
    window.wpexamsQuestionCountdownTimer = function(hrs, min, sec, timerId) {
        sec++;
        
        if (sec === 60) {
            sec = 0;
            min = min + 1;
        }
        
        if (min === 60) {
            min = 0;
            hrs = hrs + 1;
        }
        
        // Format time
        if (sec <= 9) sec = "0" + sec;
        if (hrs <= 9) hrs = "0" + hrs;
        const time = hrs + ":" + (min <= 9 ? "0" + min : min) + ":" + sec;

        // Update display
        const timerElement = document.getElementById("wpexams_question_timer");
        if (timerElement) {
            timerElement.innerHTML = time;
        }

        // Set timeout for next tick
        window.wpexamsTimers[timerId] = window.setTimeout(function() {
            wpexamsQuestionCountdownTimer(hrs, min, sec, timerId);
        }, 1000);

        // Stop if reached max
        if (hrs === '00' && min === '00' && sec === '00') {
            window.clearTimeout(window.wpexamsTimers[timerId]);
        }
    };

    /**
     * Restart timer
     */
    window.wpexamsRestartTimer = function(hrs, min, sec, timerId) {
        if (window.wpexamsTimers[timerId]) {
            window.clearTimeout(window.wpexamsTimers[timerId]);
        }
    };

    /**
     * Start timer
     */
    window.wpexamsStartTimer = function(examTimerId, questionTimerId) {
        const examTimeElement = document.getElementById("wpexams_exam_timer");
        const questionTimeElement = document.getElementById("wpexams_question_timer");
        
        if (!examTimeElement || !questionTimeElement) return;

        const examTime = examTimeElement.innerText;
        const [examHrs, examMin, examSec] = examTime.split(':');

        const questionTime = questionTimeElement.innerText;
        const [questionHrs, questionMin, questionSec] = questionTime.split(':');

        // Start exam timer
        if (examTimerId === "wpexamsUntimedTimer") {
            wpexamsUntimedQuizCountdownTimer(
                parseInt(examHrs), 
                parseInt(examMin), 
                parseInt(examSec), 
                examTimerId
            );
        } else if (examTimerId === "wpexamsTimedTimer") {
            wpexamsTimedQuizCountdownTimer(
                parseInt(examHrs), 
                parseInt(examMin), 
                parseInt(examSec), 
                examTimerId
            );
        }

        // Start question timer
        wpexamsQuestionCountdownTimer(
            parseInt(questionHrs), 
            parseInt(questionMin), 
            parseInt(questionSec), 
            questionTimerId
        );

        // Update UI
        const startBtn = document.getElementById("wpexams_start_timer");
        const pauseBtn = document.getElementById("wpexams_pause_timer");
        
        if (startBtn) startBtn.classList.add("wpexams-d-none");
        if (pauseBtn) pauseBtn.classList.remove("wpexams-d-none");

        // Show navigation buttons
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        
        if (nextBtn) nextBtn.classList.remove("wpexams-d-none");
        if (prevBtn) prevBtn.classList.remove("wpexams-d-none");
    };

    /**
     * Pause timer
     */
    window.wpexamsPauseTimer = function(examTimerId, questionTimerId) {
        window.clearInterval(window.wpexamsTimers[examTimerId]);
        window.clearInterval(window.wpexamsTimers[questionTimerId]);

        // Update UI
        const startBtn = document.getElementById("wpexams_start_timer");
        const pauseBtn = document.getElementById("wpexams_pause_timer");
        
        if (startBtn) startBtn.classList.remove("wpexams-d-none");
        if (pauseBtn) pauseBtn.classList.add("wpexams-d-none");

        // Hide navigation buttons
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        const prevBtn = document.getElementById("wpexamsPrevQuestion");
        
        if (nextBtn) nextBtn.classList.add("wpexams-d-none");
        if (prevBtn) prevBtn.classList.add("wpexams-d-none");
    };

    /**
     * Convert seconds to time
     */
    window.wpexamsConvertSecondsToHms = function(seconds) {
        const sec = Number(seconds);
        const h = Math.floor(sec / 3600);
        const m = Math.floor(sec % 3600 / 60);
        const s = Math.floor(sec % 3600 % 60);

        return {
            hrs: h,
            min: m,
            sec: s
        };
    };

    /**
     * Convert time to seconds
     */
    window.wpexamsConvertHmsToSeconds = function(time) {
        const parts = time.split(":");
        const sec = parseInt(parts[0]) * 3600 + parseInt(parts[1]) * 60 + parseInt(parts[2]);
        return sec;
    };

    /**
     * Calculate exam time difference
     */
    window.wpexamsExamTimeDiff = function(totalTime, usedTime) {
        const totalSeconds = wpexamsConvertHmsToSeconds(totalTime);
        const usedSeconds = wpexamsConvertHmsToSeconds(usedTime);
        const diffSeconds = parseInt(totalSeconds) - parseInt(usedSeconds);
        
        const diffTime = wpexamsConvertSecondsToHms(diffSeconds);
        
        const hrs = diffTime.hrs < 10 ? "0" + diffTime.hrs : diffTime.hrs;
        const min = diffTime.min < 10 ? "0" + diffTime.min : diffTime.min;
        const sec = diffTime.sec < 10 ? "0" + diffTime.sec : diffTime.sec;

        const examTimerElement = document.getElementById("wpexams_exam_timer");
        if (examTimerElement) {
            examTimerElement.innerText = hrs + ":" + min + ":" + sec;
        }

        const diffTimeElement = document.getElementById("wpexams_exam_diff_time");
        if (diffTimeElement) {
            diffTimeElement.innerText = hrs + ":" + min + ":" + sec;
        }
    };

})(jQuery);