// ##############################################################################
// FILE: UI/loading.js
// ##############################################################################

/* Insert a loading spinner into a div */
function makeLoading(el, clearEl, height) {

    clearEl = (typeof clearEl === 'undefined' ? false : clearEl);

    height = (typeof height === 'undefined' ? 300 : height);

    var actualHeight = el.style.height || el.clientHeight || el.offsetHeight;

    actualHeight = parseInt(actualHeight);

    height = (height < actualHeight || actualHeight === 0 ? height : actualHeight);

    el.style.height = height + 'px';
    el.style.position = 'relative';
    if (clearEl) {
        el.innerHTML = '<div class="div-spinner active"><span class="spinner1"></span><span class="spinner2"></span><svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo-simple"></use></svg></div>';
    } else {
        var loader = document.createElement('div');
        loader.innerHTML = '<span class="spinner1"></span><span class="spinner2"></span><svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo-simple"></use></svg>';
        loader.className = 'div-spinner active';
        el.appendChild(loader);
    }

}

/* Remove a loading spinner from a dig */
function undoLoading(el, clearEl) {

    clearEl = (typeof clearEl === 'undefined' ? false : clearEl);

    el.style.removeProperty('height');
    el.style.removeProperty('position');

    if (clearEl) {
        el.innerHTML = '';
    } else {
        removeFromDOM($('.div-spinner', el));
    }
}
