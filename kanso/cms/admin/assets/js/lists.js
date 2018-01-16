(function() {

    /**
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * @var node
     */
    var bulkForm = Helper.$('.js-bulk-actions-form');

    /**
     * @var node
     */
    var submitBtn = Helper.$('.js-bulk-actions-form button[type=submit]');

    /**
     * @var node
     */
    var checkAll = Helper.$('.js-list-check-all');

    /**
     * @var node
     */
    var checks = Helper.$All('.js-bulk-action-cb');

     /**
     * @var array
     */
    var deleteTriggers = Helper.$All('.js-confirm-delete');

    /**
     * @var boolean
     */
    var submitting = false;

    // If the sb trigger exists bind the listener
    if (Helper.nodeExists(bulkForm)) {
        submitBtn.addEventListener('click', submitBulkActions);
    }
   
    // If the check all exists and listener
    if (Helper.nodeExists(checkAll)) {
        checkAll.addEventListener('change', toggleCheckAll);
    }

    /**
     * Add Listeners to delete item triggers
     */
    initDeleteTriggers();
    function initDeleteTriggers()
    {
        for (var i = 0; i < deleteTriggers.length; i++) {
            Helper.addEventListener(deleteTriggers[i], 'click', confirmDelete);
        }
    }

    /**
     * Confirm an item delete
     *
     * @param e event
     */
    function confirmDelete(e) {
        e = e || window.event;
        e.preventDefault();
        
        var form = Helper.$('#'+this.dataset.form);
        var item = this.dataset.item;
        
        Modules.require('Notifications', {
            type           : 'default',
            msg            : 'Are you POSITIVE you want to delete this '+item+'?',
            isCallback     :  true,

            cancelText     : 'No',
            cancelClass    : 'btn-default',
            
            confirmText    : 'Yes delete it!',
            confirmClass   : 'btn-danger',
            onConfirm      : function(args) { form.submit(); },
            onConfirmArgs  : [form],
        });

    }

    
    /**
     * Check/uncheck all the list items
     *
     * @param e event
     */
    function toggleCheckAll(e) {
        if (!Helper.empty(checks)) {
            var doCheck = checkAll.checked;
            for (var i = 0; i < checks.length; i++) {
                checks[i].checked = doCheck;
            }
        }
    }

    /**
     * Submit the bulk action form
     *
     * @param e event
     */
    function submitBulkActions(e) {
       
        // Prevent default
        e = e || window.event;
        e.preventDefault();

        // Prevent double clicks
        if (submitting === true) return;
        submitting = true;

        // Find all the checkboxes
        // and append them to the form then submit
        var checkboxes = Helper.$All('.js-bulk-action-cb');
        if (!Helper.empty(checkboxes)) {
            for (var i = 0; i < checkboxes.length; i++) {
                var clone = checkboxes[i].cloneNode();
                clone.style.display = 'none';
                bulkForm.appendChild(clone);
            }
            bulkForm.submit();
        }
        submitting = false;

        
    }

}());
