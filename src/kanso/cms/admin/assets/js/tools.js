(function() {

    /**
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * @var node
     */
    var trigger = Helper.$('.js-restore-kanso-trigger');

    /**
     * Listener on restore submit
     */
    if (Helper.nodeExists(trigger)) {
        Helper.addEventListener(trigger, 'click', confirmRestore);
    }

    /**
     * Submit event
     *
     */
    function confirmRestore(e)
    {
        // Stop the form from submitting
        e = e || window.event;
        e.preventDefault();
        var form = Helper.closest(this, 'form');

        Modules.get('Modal', {
            type             : 'danger',
            header           : 'danger',
            icon             : 'exclamation-triangle',
            title            : 'Restore Kanso',
            message          : 'Are you POSITIVE you want irrevocably delete all data associated with this Kanso installation? This cannot be undone.',
            closeText        : 'Cancel',
            closeClass       : 'btn-default',
            confirmClass     : 'btn-danger',
            confirmText      : 'Restore Kanso',
            overlay          : 'dark',
            extras           : '',
            validateConfirm  : function() { form.submit(); return true; },

        });
    }

}());
