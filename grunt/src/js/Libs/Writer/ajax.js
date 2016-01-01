// ##############################################################################
// FILE: Libs/Writer/ajax.js
// ##############################################################################

/*-------------------------------------------------------------
**  Reset the save button when any inputs changed
--------------------------------------------------------------*/
KansoWriter.prototype.initInputChanges = function() {
    var self = this;
    var allInputs = $All('.reviewer .input-default');
    for (var i = 0; i < allInputs.length; i++) {
        allInputs[i].addEventListener('input', function() {
            clearTimeout(self.saveTimer);
            self.saveTimer = setTimeout(function() {
                addClass($('.js-save-post'), 'active');
            }, 1500);

        });
    }

}

/*-------------------------------------------------------------
**  Save the article
--------------------------------------------------------------*/
KansoWriter.prototype.saveArticle = function(e, self) {

    // Clear the timout
    e.preventDefault();
    clearTimeout(self.saveTimer);

    // dont submit when loading
    if (hasClass(publishBtn, 'active')) return;


    // validate the form
    var validator = formValidator(articleInputs);

    // append the ajax request and id
    validator.formAppend('ajaxRequest', self.ajaxType);
    validator.formAppend('id', self.articleID);
    validator.formAppend('content', self.writer.getValue());

    if (GLOBAL_AJAX_ENABLED) {

        Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                var responseObj = isJSON(success);

                if (responseObj && responseObj.details) {
                    self.articleID = responseObj['details']['id'];
                    self.ajaxType = 'writer_save_existing_article';
                    pushNotification('success', 'Your article was successfully saved!');
                    clearTimeout(self.saveTimer);
                    removeClass($('.js-save-post'), 'active');
                    return;
                } else {
                    pushNotification('error', 'The server encoutered an error while saving the article.');
                    return;
                }
            },
            function(error) {
                pushNotification('error', 'The server encoutered an error while saving the article.');
                return;
            });
    } else {
        pushNotification('error', 'The server encoutered an error while saving the article.');
        return;
    }
}

/*-------------------------------------------------------------
**  Publish the article
--------------------------------------------------------------*/
KansoWriter.prototype.publishArticle = function(e, self) {

    // Clear the timout
    e.preventDefault();
    clearTimeout(self.saveTimer);

    // dont submit when loading
    if (hasClass(publishBtn, 'active')) return;


    // validate the form
    var validator = formValidator(articleInputs);

    // append the ajax request and id
    validator.formAppend('ajaxRequest', 'writer_publish_article');
    validator.formAppend('id', self.articleID);
    validator.formAppend('content', self.writer.getValue());

    if (GLOBAL_AJAX_ENABLED) {

        Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                var responseObj = isJSON(success);

                if (responseObj && responseObj.details) {
                    self.articleID = responseObj['details']['id'];
                    self.ajaxType = 'writer_save_existing_article';
                    var slug = GLOBAL_AJAX_URL.replace('/admin', '') + responseObj['details']['slug'];
                    pushNotification('success', 'Your article was successfully published. Click <a href="' + slug + '" target="_blank">here</a> to view live.');
                    clearTimeout(self.saveTimer);
                    removeClass($('.js-save-post'), 'active');
                    return;
                } else {
                    pushNotification('error', 'The server encoutered an error while publishing the article.');
                    return;
                }
            },
            function(error) {
                pushNotification('error', 'The server encoutered an error while publishing the article.');
                return;
            });
    } else {
        pushNotification('error', 'The server encoutered an error while publishing the article.');
        return;
    }
}
