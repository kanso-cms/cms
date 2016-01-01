// ##############################################################################
// FILE: Libs/Writer/header.js
// ##############################################################################

/*-------------------------------------------------------------
**  Toggle multiple headers
--------------------------------------------------------------*/
KansoWriter.prototype.initToggleHeader = function() {

    var header = $('.header');
    var toggleUp = $('.js-show-header');
    var toggleDown = $('.js-hide-header');

    toggleUp.addEventListener('click', function() {
        event.preventDefault();
        removeClass(header, 'active');
    });

    toggleDown.addEventListener('click', function() {
        event.preventDefault();
        addClass(header, 'active');
    });

    this.initHeaderMouseListener();
}

/*-------------------------------------------------------------
**  Initialize the mouse timer on the toggle button for the header
--------------------------------------------------------------*/
KansoWriter.prototype.initHeaderMouseListener = function() {
    var toggleUp = $('.js-show-header');
    var self = this;
    window.addEventListener("mousemove", function() {
        var fromTop = event.clientY;
        if (fromTop < 40) {
            clearTimeout(headerTimer);
            toggleUp.style.opacity = "1";
            headerTimer = setTimeout(function() {
                toggleUp.style.opacity = "0";
            }, 80000);
        }
    });

}
