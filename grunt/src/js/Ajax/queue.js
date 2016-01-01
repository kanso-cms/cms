// ##############################################################################
// FILE: Ajax/queue.js
// ##############################################################################

function queueAjax(url, type, data, success, error) {
    GLOBAL_AJAX_QUEUE.push({
        url: url,
        type: type.toLowerCase(),
        data: data,
        success: success,
        error: error
    });
}

function bufferAjaxQueue() {
    for (var i = 0; i < GLOBAL_AJAX_QUEUE.length; i++) {
        var params = GLOBAL_AJAX_QUEUE[i];
        var type = params['type'];
        params['data']['public_key'] = GLOBAL_PUBLIC_KEY;
        Ajax[type](params['url'], params['data'], params['success'], params['error']);
    }

}

Ajax.post(GLOBAL_AJAX_URL, {
    ajaxRequest: 'public_key'
}, function(success) {

    response = isJSON(success);

    if (response) {
        GLOBAL_AJAX_ENABLED = true;
        GLOBAL_PUBLIC_KEY = decrypt(response.details.k, response.details.s);
        bufferAjaxQueue();
    }
});
