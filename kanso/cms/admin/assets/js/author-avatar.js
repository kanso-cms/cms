(function()
{
    /**
     * JS Helper module
     *
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * Avatar select wrapper
     *
     * @var node
     */
    var imgWrap = Helper.$('.js-writer-form select[name="type"]');

    console.log(imgWrap);
    
    /**
     *
     * If the wrapper exists add listeners
     */
    if (Helper.nodeExists(imgWrap))
    {
        initAvatarChooser();
    }

    /**
     * Submit event
     *
     */
    function initAvatarChooser()
    {
        var showMediaLibTrigger = Helper.$('.js-select-img-trigger');
        var removeImgTrigger    = Helper.$('.js-remove-img-trigger');
        var setAvatarTrigger    = Helper.$('.js-set-author-avatar');
        var img                 = Helper.$('.js-author-avatar-img img');
        var featureInput        = Helper.$('.js-avatar-id');
        
        Helper.removeClass(Helper.$('.js-update-media'), 'btn-success');
        
        showMediaLibTrigger.addEventListener('click', function(e) {
            e = e || window.event;
            e.preventDefault();
            Helper.addClass(Helper.$('.js-media-library'), 'feature-image');
        });

        setAvatarTrigger.addEventListener('click', function(e) {
            e = e || window.event;
            e.preventDefault();
            Modules.get('MediaLibrary')._hideLibrary();
            featureInput.value = Helper.$('#media_id').value;
            img.src = Helper.$('#media_url').value;
            Helper.addClass(imgWrap, 'active');
        });

        removeImgTrigger.addEventListener('click', function(e) {
            e = e || window.event;
            e.preventDefault();
            featureInput.value = '';
            Helper.removeClass(imgWrap, 'active');
            img.src = '';
        });
    }   

}());