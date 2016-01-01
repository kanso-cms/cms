// ##############################################################################
// FILE: UI/messages.js
// ##############################################################################

function insertMessage(type, content, target, size, clearEl) {

    clearEl = (typeof clearEl === 'undefined' ? false : clearEl);
    size = (typeof size === 'undefined' ? '' : size);

    var icon = (type !== 'plain' ? type : 'info');
    var message = document.createElement('div');
    message.className = 'row';
    message.innerHTML = cleanInnerHTML([
        '<div class="message ' + type + ' ' + size + '">',
        '<div class="message-icon">',
        '<svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#' + icon + '"></use></svg>',
        '</div>',
        '<div class="message-body">',
        '<p>' + content + '</p>',
        '</div>',
        '</div>'
    ]);
    if (clearEl) target.innerHTML = '';
    target.appendChild(message);
}
