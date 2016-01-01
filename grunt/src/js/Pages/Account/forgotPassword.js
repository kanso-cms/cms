// ##############################################################################
// FILE: Pages/Account/forgotPassword.js
// ##############################################################################

(function() {


    var forgotPassForm = $('.forgot-password.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(forgotPassForm)) {
        inputs = $All('input', forgotPassForm);
        submitBtn = $('.submit', forgotPassForm)
        submitBtn.addEventListener('click', submitForgotPassword);
    }

    function submitForgotPassword() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(forgotPassForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_forgot_password');

        if (!validator.validForm) {
            showAjaxInputErrors(inputs, forgotPassForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    showAjaxFormResult(forgotPassForm, 'info');
                },
                function(error) {
                    showAjaxFormResult(forgotPassForm, 'info');
                    return;
                });
        } else {
            showAjaxFormResult(forgotPassForm, 'info');
        }

    }

}());
