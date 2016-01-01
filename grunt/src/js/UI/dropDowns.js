// ##############################################################################
// FILE: UI/dropDowns.js
// ##############################################################################

/* Header dropdown menu */
(function() {

    var trigger = $('.header .drop-down > a');
    if (nodeExists(trigger)) {
        trigger.addEventListener('click', toggleDD);
        document.addEventListener('click', removeDD);
    }

    function toggleDD() {
        event.preventDefault();
        if (hasClass(trigger.parentNode, 'active')) {
            removeClass(trigger.parentNode, 'active');
        } else {
            addClass(trigger.parentNode, 'active');
        }
    }


    function removeDD() {
        if (event.target === trigger || trigger === closest(event.target, 'a')) return;
        removeClass(trigger.parentNode, 'active');
    }

}());

/* Dropdown buttons - generic */
(function() {

    var dropTriggers = $All('.js-button-down .button');

    if (nodeExists($('.js-button-down .button'))) {
        initDropDowns();
        document.addEventListener('click', removeDDS);
    }

    function initDropDowns() {
        for (var i = 0; i < dropTriggers.length; i++) {
            dropTriggers[i].addEventListener('click', toggleDRD);
            initValueChange(dropTriggers[i]);
        }
    }

    function initValueChange(button) {
        var drop = $('.drop div', button.parentNode);
        drop.addEventListener('click', changeValue);
    }

    function changeValue() {
        event.preventDefault();
        var target = closest(event.target, 'a');
        var wrap = parentUntillClass(target, 'js-button-down');
        var btn = $('.button', wrap);
        var icon = $('svg', btn);
        icon = (typeof icon === 'undefined' ? null : icon);
        btn.innerHTML = target.textContent;
        if (icon) btn.appendChild(icon);
    }


    function toggleDRD() {
        event.preventDefault();
        var button = closest(event.target, 'a');
        var wrap = button.parentNode;
        if (hasClass(wrap, 'active')) {
            button.blur();
            removeClass(wrap, 'active');
        } else {
            button.blur();
            addClass(wrap, 'active');
        }
    }

    function removeDDS() {

        var clicked = closest(event.target, 'a');

        if (clicked && hasClass(clicked.parentNode, 'js-button-down')) {
            return;
        }

        for (var i = 0; i < dropTriggers.length; i++) {
            dropTriggers[i].blur();
            removeClass(dropTriggers[i].parentNode, 'active');
        }
    }

}());
