jQuery(document).ready(function ($) { 

    $(document).on('click', '.qb_reset_qustion_bank', function () {

        if (confirm("Are you sure?")) {

            var data = {
                action: 'qb_reset_question_bank',
                id: "2",
            }

            jQuery.post(qb_ajax_url.ajax_url, data, function (response) {
                let resJson = JSON.parse(response);
                let qbRestMessage = document.getElementById("qb_reset_qustion_bank_message");
                if (resJson && resJson.success) {
                    qbRestMessage.style.color = "green";
                    qbRestMessage.innerText = resJson.message;
                } else {
                    qbRestMessage.style.color = "red";
                    qbRestMessage.innerText = 'Exam history not exists';
                }

            });

        }
        return false;
    })

})