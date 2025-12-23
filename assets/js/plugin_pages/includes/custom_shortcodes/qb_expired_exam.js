function examExpired() {
    const urlParams = new URLSearchParams(window.location.search);
    const examPostID = urlParams.get('qb_subscriber_exam_ID');
    jQuery.ajax({
        method: 'POST',
        url: qb_ajax_url.ajax_url,
        dataType: 'json',
        data: {
            action: 'qb_exam_expired',
            examPostID: examPostID
        },
        success: function (response) {
            if (response.exam_time == "Expired") {
                exitExamFun();
            }
        }
        
    })

    return false ;
}