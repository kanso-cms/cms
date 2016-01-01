// ##############################################################################
// FILE: Pages/Account/login.js
// ##############################################################################

(function() {

    var loginForm = $('.login.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(loginForm)) {
        inputs = $All('input', loginForm);
        submitBtn = $('.submit', loginForm)
        submitBtn.addEventListener('click', submitLogin);
    }

    function submitLogin() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(loginForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_login');

        if (!validator.validForm) {
            showAjaxInputErrors(inputs, loginForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {

                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        window.location.href = GLOBAL_AJAX_URL + 'articles/';
                    } else {
                        showAjaxFormResult(loginForm, 'error');
                        return;
                    }
                },
                function(error) {
                    showAjaxFormResult(loginForm, 'error');
                    return;
                });
        } else {
            showAjaxFormResult(loginForm, 'error');
        }

    }

}());
