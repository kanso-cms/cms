// ##############################################################################
// FILE: Pages/Account/forgotUsername.js
// ##############################################################################

(function() {


    var forgotUsernameForm = $('.forgot-username.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(forgotUsernameForm)) {
        inputs = $All('input', forgotUsernameForm);
        submitBtn = $('.submit', forgotUsernameForm)
        submitBtn.addEventListener('click', submitForgotPassword);
    }

    function submitForgotPassword() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(forgotUsernameForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_forgot_username');

        if (!validator.validForm) {
            showAjaxInputErrors(inputs, forgotUsernameForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    showAjaxFormResult(forgotUsernameForm, 'info');
                },
                function(error) {
                    showAjaxFormResult(forgotUsernameForm, 'info');
                    return;
                });
        } else {
            showAjaxFormResult(forgotUsernameForm, 'info');
        }

    }

}());
