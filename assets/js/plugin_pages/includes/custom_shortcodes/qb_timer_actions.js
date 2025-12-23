function examTimeDiff(totalTime, usedTime) {

    let calculatedExamTimeSec = convertHmsToSeconds(totalTime);
    let subscriberExamTimeSec = convertHmsToSeconds(usedTime);

    let exam_diff_sec = parseInt(calculatedExamTimeSec) - parseInt(subscriberExamTimeSec);

    let exam_diff_time = convertSecondsToHms(exam_diff_sec);
    if (exam_diff_time['hrs'] < 10) { exam_diff_time['hrs'] = "0" + exam_diff_time['hrs']; }
    if (exam_diff_time['min'] < 10) { exam_diff_time['min'] = "0" + exam_diff_time['min']; }
    if (exam_diff_time['sec'] < 10) { exam_diff_time['sec'] = "0" + exam_diff_time['sec']; }

    if (document.getElementById("qb_exam_timer")) {
        document.getElementById("qb_exam_timer").innerText = `${exam_diff_time['hrs']}:${exam_diff_time['min']}:${exam_diff_time['sec']}`;
    }
    let subscriber__exam_diff_time = document.getElementById("subscriber__exam_diff_time")
    if (subscriber__exam_diff_time) {
        subscriber__exam_diff_time.innerText = `${exam_diff_time['hrs']}:${exam_diff_time['min']}:${exam_diff_time['sec']}`;
    }
}

function convertSecondsToHms(sec) {
    sec = Number(sec);
    var h = Math.floor(sec / 3600);
    var m = Math.floor(sec % 3600 / 60);
    var s = Math.floor(sec % 3600 % 60);

    return { "hrs": h, "min": m, "sec": s };
}

function convertHmsToSeconds(time) {
    let tt = time.split(":");
    let sec = tt[0] * 3600 + tt[1] * 60 + tt[2] * 1;
    return sec;
}

// questionsCountDownTimer(0,0,82,"questiontimer");
// quizCountDownTimer(0,0,0,"quiztimer");

function restartTimer(hrs, min, sec, tid) {
    window.clearTimeout(qbtimers[tid]);
}

function startTimer(exam_tid, question_tid) {
    let exam_time = document.getElementById("qb_exam_timer").innerText;
    const [exam_hrs, exam_min, exam_sec] = exam_time.split(':');

    let question_time = document.getElementById("qb_question_timer").innerText;
    const [question_hrs, question_min, question_sec] = question_time.split(':');


    if (exam_tid == "Untimedquiztimer") {
        UntimedQuizCountDownTimer(parseInt(exam_hrs), parseInt(exam_min), parseInt(exam_sec), exam_tid);
        questionsCountDownTimer(parseInt(question_hrs), parseInt(question_min), parseInt(question_sec), question_tid)
    }
    if (exam_tid == "timedquiztimer") {
        timedQuizCountDownTimer(parseInt(exam_hrs), parseInt(exam_min), parseInt(exam_sec), exam_tid);
        questionsCountDownTimer(parseInt(question_hrs), parseInt(question_min), parseInt(question_sec), question_tid)
    }
    document.getElementById("qb_startTimer").classList.add("qb_d_none");
    document.getElementById("qb_pauseTimer").classList.remove("qb_d_none");

    document.getElementById("qbNxtQuestion").classList.remove("qb_d_none");
    document.getElementById("qbPrevQuestion").classList.remove("qb_d_none");
}

function pauseTimer(exam_tid, question_tid) {
    window.clearInterval(qbtimers[exam_tid]);
    window.clearInterval(qbtimers[question_tid]);
    document.getElementById("qb_startTimer").classList.remove("qb_d_none");
    document.getElementById("qb_pauseTimer").classList.add("qb_d_none");

    document.getElementById("qbNxtQuestion").classList.add("qb_d_none");
    document.getElementById("qbPrevQuestion").classList.add("qb_d_none");
}