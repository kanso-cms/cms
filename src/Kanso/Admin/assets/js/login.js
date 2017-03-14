(function() {

    /**
     * @var Helper obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * @var Ajax obj
     */
    var Ajax = Modules.get('Ajax');

    /**
     * @var submitBtn node|null
     */
    var formEl = Helper.$('.js-login-form');

    /**
     * @var submitBtn node|null
     */
    var submitBtn;

    /**
     * @var formValidator obj|null
     */
    var formValidator;

    /**
     * If the form exists bind the listener
     */
    if (Helper.nodeExists(formEl)) {
        submitBtn     = Helper.$('button[type=submit]', formEl);
        formValidator = Modules.get('FormValidator', formEl);
        submitBtn.addEventListener('click', formHandler);
    }
   
    /**
     * Handle the submit click event
     *
     * @param e event
     */
    function formHandler(e) {

        // Stop the form from submitting
        e = e || window.event;
        e.preventDefault();

        // Don't submit if the form if it is being submitted
        if (Helper.hasClass(submitBtn, 'active')) return;

        // Validation
        if (formValidator.isValid()) {
            Helper.addClass(submitBtn, 'active');
            submitForm();
        }
        else {
            formValidator.showInvalid();
        }
    }

    /**
     * Submit the form
     *
     */
    function submitForm()
    {
        formValidator.append('access_token', Modules.get('access_token'));
        formValidator.append('ajaxRequest', 'admin_login');
        var formObj = formValidator.form();

        Ajax.post(Modules.get('AJAX_URL'), formObj, 
        function(success_response) {
            var response_obj = Helper.isJSON(success_response);
            if (response_obj) {
                if (response_obj.details === 'valid') {
                    window.location.href = Modules.get('AJAX_URL') + 'articles/';
                }
                else {
                    Helper.removeClass(submitBtn, 'active');
                    formValidator.showResult('danger');
                }
            }
            else {
                Helper.removeClass(submitBtn, 'active');
                formValidator.showResult('danger');
            }
        },
        function(error_response) {
            Helper.removeClass(submitBtn, 'active');
            formValidator.showResult('danger');
        });
    }        
       
}());
