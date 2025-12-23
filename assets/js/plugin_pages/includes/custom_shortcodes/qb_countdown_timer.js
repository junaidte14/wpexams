function timedQuizCountDownTimer(hrs, min, sec, tid) {
    sec--;
    if (sec == -01) {
        sec = 59;
        min = min - 1;
    } else {
        min = min;
    }
    if (min == -01) {
        min = 59;
        hrs = hrs - 1;
    } else {
        hrs = hrs;
    }
    if (sec <= 9) {
        sec = "0" + sec;
    }
    if (hrs <= 9) {
        hrs = "0" + hrs;
    }
    let time = hrs + ":" + (min <= 9 ? "0" + min : min) + ":" + sec + "";
    // let time = sec + "";
    if (!qbtimers['timedQuizCountDownTime']) {

        qbtimers['timedQuizCountDownTime'] = time;

    }

    if (document.getElementById("qb_exam_timer")) {
        document.getElementById("qb_exam_timer").innerHTML = time;
    }

    qbtimers[tid] = window.setTimeout("timedQuizCountDownTimer(" + hrs + "," + min + "," + sec + ",'" + tid + "');", 1000);

    if (hrs == '00' && min == '00' && sec == '00') {
        sec = "00";
        let nextBtn = document.getElementById("qbNxtQuestion");
        nextBtn.setAttribute("onclick", "examExpired()");
        setTimeout(() => {
            nextBtn.click()
        }, 1000);
        window.clearTimeout(qbtimers[tid]);
    }
}

function UntimedQuizCountDownTimer(hrs, min, sec, tid) {
    sec++;
    if (sec == 60) {
        sec = 00;
        min = min + 1;
    } else {
        min = min;
    }
    if (min == 60) {
        min = 00;
        hrs = hrs + 1;
    } else {
        hrs = hrs;
    }
    if (sec <= 9) {
        sec = "0" + sec;
    }
    if (hrs <= 9) {
        hrs = "0" + hrs;
    }
    let time = hrs + ":" + (min <= 9 ? "0" + min : min) + ":" + sec + "";

    if (document.getElementById("qb_exam_timer")) {
        document.getElementById("qb_exam_timer").innerHTML = time;
    }

    qbtimers[tid] = window.setTimeout("UntimedQuizCountDownTimer(" + hrs + "," + min + "," + sec + ",'" + tid + "');", 1000);
    if (hrs == '00' && min == '00' && sec == '00') {
        sec = "00";
        window.clearTimeout(qbtimers[tid]);
    }
}

function questionsCountDownTimer(hrs, min, sec, tid) {

    sec++;
    if (sec == 60) {
        sec = 00;
        min = min + 1;
    } else {
        min = min;
    }
    if (min == 60) {
        min = 00;
        hrs = hrs + 1;
    } else {
        hrs = hrs;
    }
    if (sec <= 9) {
        sec = "0" + sec;
    }
    if (hrs <= 9) {
        hrs = "0" + hrs;
    }
    let time = hrs + ":" + (min <= 9 ? "0" + min : min) + ":" + sec + "";

    if (document.getElementById("qb_question_timer")) {
        document.getElementById("qb_question_timer").innerHTML = time;
    }

    qbtimers[tid] = window.setTimeout("questionsCountDownTimer(" + hrs + "," + min + "," + sec + ",'" + tid + "');", 1000);
    if (hrs == '00' && min == '00' && sec == '00') {
        sec = "00";
        window.clearTimeout(qbtimers[tid]);
    }


}