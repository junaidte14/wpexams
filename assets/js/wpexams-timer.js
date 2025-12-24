/**
 * WP Exams Timer Functions
 *
 * @package WPExams
 * @since 1.0.0
 */

/**
 * Timed quiz countdown timer (counts down from given time)
 */
function wpexamsTimedQuizCountdownTimer(hrs, min, sec, timerId) {
    sec--;
    
    if (sec === -1) {
        sec = 59;
        min = min - 1;
    }
    
    if (min === -1) {
        min = 59;
        hrs = hrs - 1;
    }
    
    // Format time
    if (sec <= 9) sec = "0" + sec;
    if (hrs <= 9) hrs = "0" + hrs;
    const time = hrs + ":" + (min <= 9 ? "0" + min : min) + ":" + sec;

    // Store initial time
    if (!window.wpexamsTimers['timedQuizCountDownTime']) {
        window.wpexamsTimers['timedQuizCountDownTime'] = time;
    }

    // Update display
    const timerElement = document.getElementById("wpexams_exam_timer");
    if (timerElement) {
        timerElement.innerHTML = time;
    }

    // Set timeout for next tick
    window.wpexamsTimers[timerId] = window.setTimeout(function() {
        wpexamsTimedQuizCountdownTimer(hrs, min, sec, timerId);
    }, 1000);

    // Handle expiration
    if (hrs === '00' && min === '00' && sec === '00') {
        const nextBtn = document.getElementById("wpexamsNextQuestion");
        if (nextBtn) {
            nextBtn.setAttribute("onclick", "wpexamsExamExpired()");
            setTimeout(function() {
                nextBtn.click();
            }, 1000);
        }
        window.clearTimeout(window.wpexamsTimers[timerId]);
    }
}

/**
 * Untimed quiz countdown timer (counts up from zero)
 */
function wpexamsUntimedQuizCountdownTimer(hrs, min, sec, timerId) {
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
    const timerElement = document.getElementById("wpexams_exam_timer");
    if (timerElement) {
        timerElement.innerHTML = time;
    }

    // Set timeout for next tick
    window.wpexamsTimers[timerId] = window.setTimeout(function() {
        wpexamsUntimedQuizCountdownTimer(hrs, min, sec, timerId);
    }, 1000);

    // Stop at 99:59:59 to prevent overflow
    if (hrs === 99 && min === 59 && sec === 59) {
        window.clearTimeout(window.wpexamsTimers[timerId]);
    }
}

/**
 * Question countdown timer (tracks time per question)
 */
function wpexamsQuestionCountdownTimer(hrs, min, sec, timerId) {
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

    // Stop at 99:59:59 to prevent overflow
    if (hrs === 99 && min === 59 && sec === 59) {
        window.clearTimeout(window.wpexamsTimers[timerId]);
    }
}