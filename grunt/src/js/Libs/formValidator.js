// ##############################################################################
// FILE: Libs/formValidator.js
// ##############################################################################

/* 
	Form validator 
*/
var formValidator = function(inputs) {
    if (!(this instanceof formValidator)) {
        return new formValidator(inputs)
    }

    // Save inputs 
    this.inputs = inputs;

    // Validation index
    this.validationIndex = [];

    // Form result
    this.validForm = true;

    // Invalid inputs
    this.invalids = [];

    this.formObj = {};

    this.inputsIndex = {};

    // Index the inputs based on type
    this.indexInputs();

    // generate the form
    this.generateForm();


};

formValidator.prototype = {

    // index the inputs base on validation types
    indexInputs: function() {
        for (var i = 0; i < this.inputs.length; i++) {
            var name = this.inputs[i].name;
            this.inputsIndex[name] = this.inputs[i];
            this.validationIndex.push({
                node: this.inputs[i],
                isRequired: this.inputs[i].dataset.jsRequired || null,
                validationMinLength: this.inputs[i].dataset.jsMinLegnth || null,
                validationMaxLength: this.inputs[i].dataset.jsMaxLegnth || null,
                validationType: this.inputs[i].dataset.jsValidation || null,
                isValid: true,
            });
        }
    },

    validateForm: function() {
        this.invalids = [];
        this.validForm = true;

        for (var i = 0; i < this.validationIndex.length; i++) {
            this.validationIndex[i].isValid = true;

            var pos = this.validationIndex[i];
            var value = getInputValue(pos.node);

            if (!pos.isRequired && value === '') {
                continue;
            } else if (pos.isRequired && !this.validateEmpty(value)) {
                this.devalidate(i);
            } else if (pos.validationMinLength && !this.validateMinLength(value, pos.validationMinLength)) {
                this.devalidate(i);
            } else if (pos.validationMaxLength && !this.validateMaxLength(value, pos.validationMaxLength)) {
                this.devalidate(i);
            } else if (pos.validationType) {
                var isValid = true;
                if (pos.validationType === 'email') isValid = this.validateEmail(value);
                if (pos.validationType === 'name') isValid = this.validateName(value);
                if (pos.validationType === 'password') isValid = this.validatePassword(value);
                if (pos.validationType === 'website') isValid = this.validateWebsite(value);
                if (pos.validationType === 'plain-text') isValid = this.validatePlainText(value);
                if (pos.validationType === 'numbers') isValid = this.validateNumbers(value);
                if (pos.validationType === 'list') isValid = this.validateList(value);
                if (pos.validationType === 'no-spaces-text') isValid = this.validatePlainTextNoSpace(value);
                if (pos.validationType === 'slug') isValid = this.validateSlug(value);
                if (pos.validationType === 'creditcard') isValid = this.validateCreditCard(value);
                if (pos.validationType === 'cvv') isValid = this.validateCVV(value);
                if (pos.validationType === 'permalinks') isValid = this.validatePermalinks(value);
                if (pos.validationType === 'comma-list-numbers') isValid = this.validateCommaListNumbers(value);
                if (pos.validationType === 'url-path') isValid = this.validateURLPath(value);


                if (!isValid) this.devalidate(i);
            }
        }
        return this;
    },

    getInput: function(name) {
        if (name in this.inputsIndex) return this.inputsIndex[name];
        return null;
    },

    generateForm: function() {
        for (var i = 0; i < this.inputs.length; i++) {
            var value = getInputValue(this.inputs[i]);
            if (is_numeric(value)) value = parseInt(value);
            this.formObj[this.inputs[i].name] = value;
        }
        this.formAppend('public_key', GLOBAL_PUBLIC_KEY);
        this.formAppend('referer', window.location.href);
        return this.formObj;
    },

    formAppend: function(key, value) {
        this.formObj[key] = value;
        return this.formObj;
    },

    getForm: function() {
        return this.formObj;
    },

    devalidate: function(i) {
        this.validationIndex[i].isValid = false;
        this.validForm = false;
        this.invalids.push(this.validationIndex[i].node);
    },

    validateEmpty: function(value) {
        value = value.trim();
        var re = /^\s*$/;
        return re.test(value) ? false : true;
    },

    validateEmail: function(value) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(value);
    },

    validateName: function(value) {
        var re = /^[A-z \-]+$/;
        return re.test(value);
    },

    validateNumbers: function(value) {
        var re = /^[\d]+$/;
        return re.test(value);
    },

    validatePassword: function(value) {
        var re = /^(?=.*[^a-zA-Z]).{6,40}$/;
        return re.test(value);
    },

    validateWebsite: function(value) {
        re = /^(www\.|[A-z]|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
        return re.test(value);
    },

    validateMinLength: function(value, min) {
        return value.length >= min;
    },

    validateMaxLength: function(value, max) {
        return value.length < max;
    },

    validatePlainText: function(value) {
        var re = /^[A-z _-]+$/;
        return re.test(value);
    },

    validatePlainTextNoSpace: function(value) {
        var re = /^[A-z_-]+$/;
        return re.test(value);
    },

    validateList: function(value) {
        var re = /^[-\w\s]+(?:,[-\w\s]*)*$/;
        return re.test(value);
    },

    validateSlug: function(value) {
        var re = /^[A-z\/\-\_]+$/;
        return re.test(value);
    },

    validateCreditCard: function(value) {
        value = value.replace(/ /g, "");
        var re = /^[0-9]+$/;
        var check = re.test(value);
        if (check === false) return false;
        if (value.length !== 16) return false;
        return true;
    },

    validateCVV: function(value) {
        if (value.length > 4) return false;
        var re = /^[0-9]+$/;
        return re.test(value);
    },

    validatePermalinks: function(value) {
        var re = /^((year|month|postname|category|author|day|hour|minute|second)\/)+$/;
        return re.test(value);
    },

    validateCommaListNumbers: function(value) {
        var re = /^((\d+),\s)+(\d+)$/;
        return re.test(value);
    },

    validateURLPath: function(value) {
        var re = /[A-z-_ \.\/]+/;
        return re.test(value);
    },

};
