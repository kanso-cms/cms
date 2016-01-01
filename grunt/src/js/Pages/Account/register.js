// ##############################################################################
// FILE: Pages/Account/register.js
// ##############################################################################

(function() {


    var registerFrom = $('.register.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(registerFrom)) {
        inputs = $All('input', registerFrom);
        submitBtn = $('.submit', registerFrom)
        submitBtn.addEventListener('click', submitLogin);
    }

    function submitLogin() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(registerFrom);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_register');

        if (!validator.validForm) {
            showAjaxInputErrors(validator.invalids, registerFrom);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    var responseObj = isJSON(success);
                    if (responseObj && responseObj.details === 'valid') {
                        window.location.href = GLOBAL_AJAX_URL + 'settings/';
                    } else {
                        showAjaxFormResult(registerFrom, 'error', responseObj.details);
                        return;
                    }
                },
                function(error) {
                    showAjaxFormResult(registerFrom, 'error', 'There was an error processing your request.');
                    return;
                });
        } else {
            showAjaxFormResult(registerFrom, 'error', 'There was an error processing your request.');
        }

    }

}());
