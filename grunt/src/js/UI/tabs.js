// ##############################################################################
// FILE: UI/tabs.js
// ##############################################################################

(function() {

    var triggers = $All('.js-tabs-wrap a');

    if (nodeExists($('.js-tabs-wrap a'))) {
        for (var i = 0; i < triggers.length; i++) {
            triggers[i].addEventListener('click', toggleTabs);
        }
    }

    function toggleTabs() {
        event.preventDefault();
        var clicked = event.target;
        if (hasClass(clicked, 'active')) {
            return;
        }
        var tabsWrap = parentUntillClass(clicked, 'js-tabs-wrap');
        var activeTab = $('a.active', tabsWrap);
        var activePanel = $('#' + activeTab.dataset.tab);
        var newPanel = $('#' + clicked.dataset.tab);
        if (nodeExists(activePanel)) {
            removeClass(activePanel, 'active');
        }
        if (nodeExists(newPanel)) {
            addClass(newPanel, 'active');
        }
        removeClass(activeTab, 'active');
        addClass(clicked, 'active');
        if (hasClass(tabsWrap, 'js-url-tabs')) {
            var title = clicked.dataset.tabTitle;
            var slug = clicked.dataset.tabUrl;
            URLtabber(title, slug);
        }
    }

    function URLtabber(title, url) {
        var baseURL = rtrim(window.location.href, '/');
        baseURL = baseURL.split("/");
        baseURL.pop();
        baseURL = baseURL.join('/');

        //prevents browser from storing history with each change:
        if (window.history.replaceState) {
            var statedata = title;
            window.history.replaceState(statedata, title, baseURL + "/" + url + "/");
        }
        document.title = title;
    }

}());
