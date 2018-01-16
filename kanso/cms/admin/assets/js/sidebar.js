(function() {

    /**
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * @var node
     */
    var sbTrigger = Helper.$('.js-toggle-sb');

    /**
     * @var node
     */
    var sidebar = Helper.$('.js-sidebar');

    /**
     * @var node
     */
    var dashWrap = Helper.$('.js-dash-wrap');

    /**
     * @var array
     */
    var toggleLists = Helper.$All('.js-sidebar .js-toggle-down');

    /**
     * If the sb trigger exists bind the listener
     */
    if (Helper.nodeExists(sbTrigger)) {
        sbTrigger.addEventListener('click', toggleSb);
        window.addEventListener('resize', function() {
            var w = window.innerWidth;
            if (w < 1050) {
                if (Helper.hasClass(sbTrigger, 'active')) {
                    Helper.triggerEvent(sbTrigger, 'click');
                }
            }
        });

        initToggles();
    }
   
    /**
     * Toggle the sidebar
     *
     * @param e event
     */
    function toggleSb(e) {

        // Prevent default
        e = e || window.event;
        e.preventDefault();

        // Toggle classes
        if (Helper.hasClass(sbTrigger, 'active')) {
            Helper.removeClass(sbTrigger, 'active');
            Helper.removeClass(sidebar, 'active');
            Helper.animate(sidebar, 'width', '300px', '66px', 350, 'easeOutQuint');
            Helper.animate(dashWrap, 'padding-left', '324px', '90px', 350, 'easeOutQuint');
            sidebar.scrollTop = 0;
        }
        else {
            Helper.addClass(sbTrigger, 'active');
            Helper.addClass(sidebar, 'active');
            Helper.animate(sidebar, 'width', '66px', '300px', 350, 'easeOutQuint');
            Helper.animate(dashWrap, 'padding-left', '90px', '324px', 350, 'easeOutQuint');
            sidebar.scrollTop = 0;
        }   
    }

    function initToggles()
    {
        for (var i = 0; i < toggleLists.length; i++)
        {
            Helper.addEventListener(toggleLists[i], 'click', toggleList);
        }
    }

    function toggleList(e)
    {
        e = e || window.event;
        var li = this.parentNode;
        Helper.toggleClass(li, 'active');
    }

}());
