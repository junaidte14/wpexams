function getPercentage(currentID, allPostaID , nextID) {

    if ((allPostaID.includes(parseInt(currentID)) || allPostaID.includes(currentID))) {
        let currentIDNumber;
        let nextIDNumber;
        if (allPostaID.includes(currentID)) {
            currentIDNumber = parseInt(allPostaID.indexOf(currentID)) + 1;
            nextIDNumber = parseInt(allPostaID.indexOf(nextID)) + 1;
        } else {
            currentIDNumber = parseInt(allPostaID.indexOf(parseInt(currentID))) + 1;
            nextIDNumber = parseInt(allPostaID.indexOf(parseInt(nextID))) + 1;
        }

        

        let findPercentage = (100 * currentIDNumber) / allPostaID.length;

        const progressContainer = document.querySelector(".qb_subs_exam_progress");

        const percentage = Math.trunc(findPercentage) + '%';

        const progressEl = progressContainer.querySelector('.progress');
        const percentageEl = progressContainer.querySelector('.percentage');
        const qb_question_progress_nb = progressContainer.querySelector('.qb_question_progress_nb');
        progressEl.style.width = percentage;
        percentageEl.innerText = percentage;
        if (allPostaID.includes(nextID)) {
            qb_question_progress_nb.innerText = `${nextIDNumber}/${allPostaID.length}`;
        }
        percentageEl.style.left = percentage;
    }

}