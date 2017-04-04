(function() {

    /**
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * @var node
     */
    var forms = Helper.$All('.js-validation-form');

    /**
     * If any form validations exist stop them from submitting until valid
     */
    if (!Helper.empty(forms)) {
        for (var i = 0; i < forms.length; i++) {
            initValidations(forms[i]);
        }
    }

    /**
     * Submit event
     *
     */
    function initValidations(form)
    {
        var formValidator = Modules.get('FormValidator', form);
        var submitBtn     = Helper.$('button[type=submit]', form);
        submitBtn.addEventListener('click', function(e) {
            // Stop the form from submitting
            e = e || window.event;
            e.preventDefault();
            // Validation
            if (formValidator.isValid()) {
                form.submit();
            }
            else {
                formValidator.showInvalid();
            }
        });
    }

}());
