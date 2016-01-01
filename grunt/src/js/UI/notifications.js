// ##############################################################################
// FILE: UI/notifiactions.js
// ##############################################################################

// ##############################################################################
// Notifications
// ##############################################################################
var activeNotifs = [];

function pushNotification(type, message) {
    var notifWrap = $('.js-nofification-wrap');
    var content = '<div class="message-icon"><svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#' + type + '"></use></svg></div><div class="message-body"><p>' + message + '</p></div>';
    var id = makeid(20);
    var notif = newNode('div', type + ' message flipInX animated', null, content, notifWrap);
    activeNotifs.push({
        node: notif,
        id: id,
        timeout: setTimeout(function() {
            removeNotif(notif);
        }, 6000),
    });
    notif.addEventListener('click', function() {
        removeNotif(notif);
    });
}

function removeNotif(node) {
    for (var i = 0; i < activeNotifs.length; i++) {
        if (node === activeNotifs[i].node) {
            clearTimeout(activeNotifs[i].timeout);
            fadeOutAndRemove(activeNotifs[i].node);
            activeNotifs.splice(i, 1);
        }
    }
}

function pushCallBackNotification(type, message, confirmType, confirmCallback, confirmArgs, cacnelCallback, cancelArgs) {
    var notifWrap = $('.js-nofification-wrap');
    var content = '<div class="message-icon"><svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#' + type + '"></use></svg></div><div class="message-body"><p>' + message + '</p><div class="row"><button class="button cancel small js-cancel">Cancel</button><button class="button small confirm red js-confirm">' + confirmType + '</button></div></div>';
    var notif = newNode('div', type + ' message flipInX animated message-confirm', null, content, notifWrap);
    var cancel = $('.js-cancel', notif);
    var confirm = $('.js-confirm', notif);

    cancel.addEventListener('click', function() {
        if (isCallable(cacnelCallback)) cacnelCallback(cancelArgs);
        event.preventDefault();
        fadeOutAndRemove(notif);
    });

    confirm.addEventListener('click', function() {
        event.preventDefault();
        if (isCallable(confirmCallback)) confirmCallback(confirmArgs);
        fadeOutAndRemove(notif);
    });

}

// ##############################################################################
// GLOBAL SPINNER
// ##############################################################################

function showGlobalSpinner() {
    addClass($('.js-global-spinner'), 'active');
}

function hideGlobalSpinner() {
    removeClass($('.js-global-spinner'), 'active');
}
