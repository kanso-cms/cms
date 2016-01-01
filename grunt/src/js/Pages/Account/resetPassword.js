// ##############################################################################
// FILE: Pages/Account/resetPassword.js
// ##############################################################################


(function() {


    var restPasswordForm = $('.reset-password.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(restPasswordForm)) {
        inputs = $All('input', restPasswordForm);
        submitBtn = $('.submit', restPasswordForm)
        submitBtn.addEventListener('click', submitForgotPassword);
    }

    function submitForgotPassword() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(restPasswordForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_reset_password');

        if (!validator.validForm) {
            showAjaxInputErrors(inputs, restPasswordForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        showAjaxFormResult(restPasswordForm, 'success');
                        return;
                    } else {
                        showAjaxFormResult(restPasswordForm, 'error');
                        return;
                    }
                },
                function(error) {
                    showAjaxFormResult(restPasswordForm, 'error');
                    return;
                });
        } else {
            showAjaxFormResult(restPasswordForm, 'error');
        }

    }

}());
