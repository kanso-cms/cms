// ##############################################################################
// AJAX FROMS
// FILE: Ajax/froms.js
// ##############################################################################

/* 
	Prevennt ajax forms from being submitted 
   	and add active class on click
*/
var GLOBAL_PROGRESS = $('.js-global-progress .progress');

(function() {

    var ajaxSubmitButtons = $All('form.ajax-form button.submit');

    if (nodeExists($('form.ajax-form button.submit'))) {
        for (var i = 0; i < ajaxSubmitButtons.length; i++) {
            preventFormSubmit(i);
        }
    }

    function preventFormSubmit(i) {
        ajaxSubmitButtons[i].addEventListener("click", function(e) {
            e.preventDefault();
        });
    }

}());

/* Show/hide errors on ajax forms */
function clearAjaxInputErrors(form) {
    var inputs = $All('input', form);
    var submitBtn = $('button.submit', form);
    var resultWrap = $('.form-result', form);
    var results = $All('.animated', form);

    hideInputErrors(inputs);
    addClass(submitBtn, 'active');

    if (nodeExists(resultWrap)) resultWrap.className = 'form-result';
    removeClassNodeList(results, 'animated');
}

function showAjaxInputErrors(errorInputs, form) {
    showInputErrors(errorInputs);
    removeClass($('button.submit', form), 'active');
}

function showAjaxFormResult(form, resultClass, message) {
    if (typeof message !== 'undefined') {
        var messageP = $('.form-result .message.' + resultClass + ' .message-body p', form);
        if (nodeExists(messageP)) messageP.innerHTML = message;
    }
    removeClass($('button.submit', form), 'active');
    addClass($('.form-result', form), resultClass);
    addClass($('.form-result .message.' + resultClass, form), 'animated');
}

function showInputErrors(inputs) {
    for (var i = 0; i < inputs.length; i++) {
        addClass(inputs[i].parentNode, 'error');
    }
}

function hideInputErrors(inputs) {
    for (var i = 0; i < inputs.length; i++) {
        removeClass(inputs[i].parentNode, 'error');
    }
}
