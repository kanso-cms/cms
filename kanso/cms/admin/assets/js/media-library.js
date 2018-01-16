(function() {
    /********************************************************************************************/
    // VARS
    /********************************************************************************************/
    
    var Helper   = Modules.get('JSHelper');
    var Ajax     = Modules.require('Ajax');
    var ajaxURL  = window.location.href.replace(/admin(.+)/, 'admin/media-library/');
    var imgTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];

    /********************************************************************************************/
    // MODULE OBJECT
    /********************************************************************************************/
    var MediaLibrary = function() {

        this.wrapper = Helper.$('.js-media-library');
        
        // Vars
        this._instantiated   = false;
        this._isBulkSelect   = false;
        this._submitting     = false;
        this._currPage       = 0;
        this._isTriggerable  = false;
        this._maxImages      = false;
        this._noImages       = false;
        this._bulkSelects    = [];
        this._currItem;
        this._listDZ;
        this._dedicatedDZ;
        this._imageTypes     = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];

        // Nodes
        this._showLibraryTrigger;
        this._bulkSlectTrigger        = Helper.$('.js-bulk-select-trigger');
        this._cancelBulkSlectTrigger  = Helper.$('.js-cancel-bulk-select-trigger');
        this._submitBulkDeleteTrigger = Helper.$('.js-bulk-delete-trigger');
        this._globalProgress          = Helper.$('.js-media-progress');
       
        this._detailsForm             = Helper.$('.js-media-details-form');
        this._imgPreviewWrapper       = Helper.$('.js-preivew-wrapper');
        this._imagesContainer         = Helper.$('.js-media-items');

        this._fileURLText             = Helper.$('.js-fileurl');
        this._filePathText            = Helper.$('.js-filepath');
        this._fileNameText            = Helper.$('.js-filename');
        this._fileTypeText            = Helper.$('.js-filetype');
        this._fileDateText            = Helper.$('.js-filedate');
        this._fileSizeText            = Helper.$('.js-filesize');
        this._fileDimsText            = Helper.$('.js-filedimensions');
        this._fileUploaderText        = Helper.$('.js-fileuploader');

        this._fileTitleInput        = Helper.$('#media_title');
        this._fileAltInput          = Helper.$('#media_alt');
        this._fileURLInput          = Helper.$('#media_url');
        this._fileIdInput           = Helper.$('#media_id');
        this._sizeSelect            = Helper.$('#media_size');
        this._linkToSelect          = Helper.$('#media_link_to_select');
        this._linkToInput           = Helper.$('#media_link_to_input');
        this._linkToWrap            = Helper.$('.js-link-to-wrap');


        this._nextImgTrigger          = Helper.$('.js-image-right-trigger');
        this._prevImgTrigger          = Helper.$('.js-image-left-trigger');
        this._closeModalTrigger       = Helper.$('.js-close-preview');
        this._delMediaTrigger         = Helper.$('.js-delete-media');
        this._submitInsertTrigger     = Helper.$('.js-insert-media');
        this._submitUpdateTrigger     = Helper.$('.js-update-media');
        this._accessToken             = Helper.$('.js-access-token');

        // Self initialize
        if (Helper.nodeExists(this.wrapper))
        {
            this.__construct();
        }
    }


    /********************************************************************************************/
    // CONSTRUCTOR AND INITIATION
    /********************************************************************************************/

    /**
     * Initialize the library
     */
    MediaLibrary.prototype.__construct = function() {

        this._accessToken = this._accessToken.value;
        
        // Check if this media library is hidden by default
        if (Helper.hasClass(this.wrapper.parentNode, 'js-triggerable-media')) {
            this._initTrigger();
        }
        else {
            this._requestImages();
        }

        
        this._initImgClick();
        this._initModal();
        this._initBulkSelect();
        this._initDropZones();
        this._initScrollLoad();
        this._initSelectListener();
    }

    /**
     * Initialize and display the library to load when a trigger element is clicked
     */
    MediaLibrary.prototype._initSelectListener = function()
    {
        var _this = this;

        if (Helper.nodeExists(this._linkToSelect))
        {
            Helper.addEventListener(this._linkToSelect, 'change', function(e)
            {
                e = e || window.event;
                var val = Helper.getInputValue(this);

                if (val === 'custom')
                {
                    _this._linkToWrap.style.height = 'auto';
                }
                else
                {
                    _this._linkToWrap.style.height = '0';
                }
            });
        }
    }
    

    /**
     * Initialize and display the library to load when a trigger element is clicked
     */
    MediaLibrary.prototype._initTrigger = function() {
        this._isTriggerable      = true;
        var triggers = Helper.$All('.js-show-media-lib');
        for (var i = 0; i < triggers.length; i++) {
            Helper.addEventListener(triggers[i], 'click',  this._showLibrary);
        }
        this._hideLibraryTrigger = Helper.$('.js-close-media-lib');
        Helper.addEventListener(this._hideLibraryTrigger, 'click',  this._hideLibrary);
        Helper.addEventListener(this._submitInsertTrigger, 'click', this._insertIntoPost);
    }

    /**
     * Display the library
     */
    MediaLibrary.prototype._showLibrary = function(e) {
        var self = Modules.get('MediaLibrary');
        Helper.addClass(self.wrapper.parentNode, 'active');
        Helper.addClass(document.body, 'hide-overflow');

        // If library is not instantiated load first batch of images
        if (!self._instantiated) self._requestImages();
    }    

    /********************************************************************************************/
    // IMAGE CLICKING
    /********************************************************************************************/


    MediaLibrary.prototype._initImgClick = function() {
        Helper.addEventListener(this._imagesContainer, 'click', this._onImageClick);
    }

    MediaLibrary.prototype._onImageClick = function(e) {
        var self = Modules.get('MediaLibrary');
        e = e || window.event;
        var target = e.target;
        var item   = false;
        
        if (Helper.hasClass(target, 'thumbnail')) {
            item = target.parentNode.parentNode;
        }
        else if (Helper.hasClass(target, 'media-item')) {
            item = target;
        }

        if (item) {
            if (self._isBulkSelect) {
                if (Helper.hasClass(item, 'active')) {
                    Helper.removeClass(item, 'active');
                    var i = self._bulkSelects.length;
                    while (i--) {
                        if (self._bulkSelects[i] === item) self._bulkSelects.splice(i, 1);
                    }
                }
                else {
                    Helper.addClass(item, 'active');
                    self._bulkSelects.push(item);
                }
            }
            else {
                self._displayMediaDetails(item);
            }
        } 
    }

    /********************************************************************************************/
    // BULK SELECTIONS
    /********************************************************************************************/

    MediaLibrary.prototype._initBulkSelect = function() {
        var self = this;
        Helper.addEventListener(this._bulkSlectTrigger, 'click', function(e) {
            self._enableBulkSelect();
        });
        Helper.addEventListener(this._cancelBulkSlectTrigger, 'click', function(e) {
            self._disableBulkSelect();
        });
        Helper.addEventListener(this._submitBulkDeleteTrigger, 'click', function(e) {
            self._submitBulkDelete();
        });
    }

    MediaLibrary.prototype._enableBulkSelect = function() {
        Helper.addClass(this.wrapper, 'bulk-selecting');
        this._isBulkSelect = true;
        this._bulkSelects  = [];
    }

    MediaLibrary.prototype._disableBulkSelect = function() {
        Helper.removeClass(this.wrapper, 'bulk-selecting');
        this._isBulkSelect = false;
        for (var i = 0, len = this._bulkSelects.length; i < len; i++) {
            var node = this._bulkSelects[i];
            if (Helper.nodeExists(node)) {
                Helper.removeClass(node, 'active');
            }
        }
        this._bulkSelects = [];
    }

    MediaLibrary.prototype._submitBulkDelete = function() {
        
        if (this._submitting === true) return;
        if (Helper.empty(this._bulkSelects)) return;

        var ids = this._bulkSelectIds();
        var form = {
            'ajax_request' : 'delete_media',
            'access_token' :  this._accessToken,
            'ids'          :  ids,
        };

        this._submitting = true;
        var self = this;
        Ajax.post(ajaxURL, form, function(success) {
            var responseObj = Helper.isJSON(success);
            if (responseObj && responseObj.response === 'valid')
            {
                for (var i = 0, len = self._bulkSelects.length; i < len; i++)
                {
                    Helper.removeFromDOM( self._bulkSelects[i]);
                }
                Helper.triggerEvent(self._cancelBulkSlectTrigger, 'click');
                self._checkIsEmpty();
            }
            self._submitting = false;
        },
        function(error) {
            self._submitting = false;
            Helper.triggerEvent(self._cancelBulkSlectTrigger, 'click');
        });
    }
   
    MediaLibrary.prototype._bulkSelectIds = function() {
        var ids = [];
        for (var i = 0, len = this._bulkSelects.length; i < len; i++) {
            ids.push( this._bulkSelects[i].dataset.id);
        }
        return ids;
    }

    /********************************************************************************************/
    // MODAL FUNCTIONS
    /********************************************************************************************/
      
    /**
     * Bind click event on images
     */
    MediaLibrary.prototype._initModal = function() {
        var self = this;
        Helper.addEventListener(this._closeModalTrigger, 'click', function(e) {
           self._hideMediaDetails();
        });
        Helper.addEventListener(this._nextImgTrigger, 'click', function(e) {
           self._nextImg();
        });
        Helper.addEventListener(this._prevImgTrigger, 'click', function(e) {
           self._prevImg();
        });
        Helper.addEventListener(this._delMediaTrigger, 'click', function(e) {
            e = e || window.event;
            e.preventDefault();
            self._confirmDelAttchment();
        });
        Helper.addEventListener(this._submitUpdateTrigger, 'click', function(e) {
            e = e || window.event;
            e.preventDefault();
            self._submitUpdateInfo();
        });
    }

    /**
     * Confirm delete file
     */
    MediaLibrary.prototype._confirmDelAttchment = function() {

        var self = this;
        var id    = this._fileIdInput.value;
        Modules.require('Modal', {
            type             : 'danger',
            header           : 'danger',
            icon             : 'exclamation',
            title            : 'Please Confirm',
            message          : 'Are you POSITIVE you want to permanently delete this attachment?',
            closeText        : 'Cancel',
            closeClass       : 'btn-default',
            confirmClass     : 'btn-danger',
            confirmText      : 'Delete',
            overlay          : 'dark',
            extras           : '',
            validateConfirm  : function() { return self._submitDeleteFile(id); },

            closeAnywhere    :  false,
            
        });
    }

    /**
     * Submit delete file
     */
    MediaLibrary.prototype._submitUpdateInfo = function() {
        var form = {
            'ajax_request' : 'update_media_info',
            'id'           : this._fileIdInput.value,
            'title'        : this._fileTitleInput.value,
            'alt'          : this._fileAltInput.value,
            'access_token' : this._accessToken,
        };

        var self = this;
        if (this._submitting === true)
        {
            return false;
        }
        this._submitting = true;
        Helper.addClass(this._submitUpdateTrigger, 'active');

        var item = Helper.$('[data-url="'+this._fileURLText.innerHTML.trim()+'"]', this.wrapper);

        Ajax.post(ajaxURL, form, function(success) {
            var responseObj = Helper.isJSON(success);
            if (responseObj && responseObj.response === 'valid')
            {
                self._submitting = false;
                Helper.removeClass(self._submitUpdateTrigger, 'active');
                Modules.require('Notifications', {
                    type : 'success',
                    msg  : 'Media info successfully updated!',
                });

                item.dataset.alt   = form['alt'];
                item.dataset.title = form['title'];
            }
        },
        function(error) {
            self._submitting = false;
            Helper.removeClass(self._submitUpdateTrigger, 'active');
            Modules.require('Notifications', {
                type : 'danger',
                msg  : 'There was an error processing your request.',
            });
        });
        return true;
    }

    /**
     * Submit delete file
     */
    MediaLibrary.prototype._submitDeleteFile = function(id) {
        var form = {
            'ajax_request' : 'delete_media',
            'ids'          : [id],
            'access_token' :  this._accessToken,
        };

        var self = this;
        if (this._submitting === true) return false;
        this._submitting = true;
        Ajax.post(ajaxURL, form, function(success) {
            var responseObj = Helper.isJSON(success);
            if (responseObj && responseObj.response === 'valid')
            {
                self._removeFile(id);
                self._submitting = false;
            }
        },
        function(error) {
            self._submitting = false;
        });
        return true;
    }

    /**
     * Remove deleted file
     */
    MediaLibrary.prototype._removeFile = function(id) {
        var item = Helper.$('.media-item[data-id="'+id+'"]');
        if (Helper.nodeExists(item)) Helper.removeFromDOM(item);
        this._hideMediaDetails();
        this._checkIsEmpty();
    }

    /**
     * Display the modal
     */
    MediaLibrary.prototype._displayMediaDetails = function(item) {
        this._imgPreviewWrapper.innerHTML = '';
        var img = new Image();
        img.src = item.dataset.preview;
        this._imgPreviewWrapper.appendChild(img); 

        // Details
        this._filePathText.innerHTML = item.dataset.path; 
        this._fileURLText.innerHTML  = item.dataset.url; 
        this._fileNameText.innerHTML = item.dataset.name;        
        this._fileTypeText.innerHTML = item.dataset.type;
        this._fileDateText.innerHTML = item.dataset.date;
        this._fileSizeText.innerHTML = item.dataset.size;
        this._fileDimsText.innerHTML = item.dataset.dimensions;
        this._fileUploaderText.innerHTML = item.dataset.user;

        this._fileTitleInput.value = item.dataset.title; 
        this._fileAltInput.value   = item.dataset.alt;
        this._linkToInput.value    = '';
        this._fileURLInput.value   = item.dataset.url; 
        this._fileIdInput.value    = item.dataset.id;

        // Is this an image
        if (Helper.in_array(item.dataset.type, imgTypes)) {
            Helper.addClass(Helper.$('.js-selected-media-container'), 'is-image');
        }
        else {
            Helper.removeClass(Helper.$('.js-selected-media-container'), 'is-image');
        }

        this._detailsForm.dataset.isimage = item.dataset.isimage;

        this._currItem = item;

        // organize the next and prev buttons
        Helper.removeClass(this._nextImgTrigger,  'disabled');
        Helper.removeClass(this._prevImgTrigger,  'disabled');
        var allItems = Helper.$All('.media-item', this.wrapper);
        for (var i = 0, len = allItems.length; i < len; i++) {
            if (item === allItems[i]) {
                if (i === 0) {
                    Helper.addClass(this._prevImgTrigger,  'disabled');
                }
                else if (i === len-1) {
                    Helper.addClass(this._nextImgTrigger,  'disabled');
                }
            }
        }

        // Display the modal
        Helper.addClass(this.wrapper, 'attachment-details');     
    }

    /**
     * Hide the modal
     */
    MediaLibrary.prototype._hideMediaDetails = function() {
        Helper.removeClass(this.wrapper, 'attachment-details');
        this._currItem = null;
    }

    /**
     * Next image
     */
    MediaLibrary.prototype._nextImg = function() {
        if (this._submitting === true) return;
        if (Helper.hasClass(this._nextImgTrigger, 'disabled')) return;
        var allItems = Helper.$All('.media-item', this.wrapper);
        for (var i = 0, len = allItems.length; i < len; i++) {
            if (this._currItem === allItems[i]) {
                this._displayMediaDetails(allItems[i + 1]);
                break;
                
            }
        }
    }

    /**
     * Prev image
     */
    MediaLibrary.prototype._prevImg = function() {
        if (this._submitting === true) return;
        if (Helper.hasClass(this._prevImgTrigger, 'disabled')) return;
        var allItems = Helper.$All('.media-item', this.wrapper);
        for (var i = 0, len = allItems.length; i < len; i++) {
            if (this._currItem === allItems[i]) {
                this._displayMediaDetails(allItems[i - 1]);
                break;
                
            }
        }
    }

    /**
     * Load the next batch of images
     */
    MediaLibrary.prototype._requestImages = function() {

        // If all images have been loaded nothing to do
        if (this._maxImages === true) return;

        // Library is now instantiated
        this._instantiated = true;
        
        var self = this;
        Helper.addClass(self.wrapper, 'loading');
        Ajax.post(ajaxURL, this._imageForm(), function(success) {
            var responseObj = Helper.isJSON(success);
            if (responseObj && responseObj.response)
            {
                self._handleResponse(responseObj.response);
                return;
            }
            self._setAsEmpty();
        },
        function(error) {
            self._setAsEmpty();
        });
    }


    /**
     * Insert the next batch of images or set the library to empty
     */
    MediaLibrary.prototype._handleResponse = function(images) {
        // No more images
        // Or empty images
        if (Helper.empty(images)) {
            if (this._currPage === 0) {
                this._setAsEmpty();
                return;
            }
            else {
                this._setMaxImages();
            }
        }

        this._currPage += 1;

        var self = this;

        // Create and insert the images
        for (var i = 0, len = images.length; i < len; i++) {
            var details = images[i];
            var node    = document.createElement('div');
            var img     = new Image();
            var isLast  = i === images.length -1;
                
            node.className       = 'media-item';
            node.dataset.id      = details.id;
            node.dataset.url     = details.url;
            node.dataset.preview = details.preview;
            node.dataset.path    = details.path;
            node.dataset.date    = details.date;
            node.dataset.alt     = details.alt;
            node.dataset.title   = details.title;
            node.dataset.size    = details.size;
            node.dataset.user    = details.user;
            node.dataset.type    = details.type;
            node.dataset.name    = details.name;
            node.dataset.dimensions = details.dimensions;
            node.dataset.isimage  = Helper.in_array(details.type, this._imageTypes);

            img.setAttribute('alt',   details.alt);
            img.setAttribute('title', details.title);

            img.src = details.preview;

            var center = document.createElement('div');
                center.className = 'center';
                center.appendChild(img);

            var thumb = document.createElement('div');
                thumb.className = 'thumbnail';
                thumb.appendChild(center);

            var preview = document.createElement('div');
                preview.className = 'preview';
                preview.appendChild(thumb);
                preview.innerHTML += '<div class="name">'+details.name+'</div>';

            node.appendChild(preview);
            this._imagesContainer.appendChild(node);

            if (isLast) {
                img.onload = function (e) {
                    e = e || window.event;
                    Helper.removeClass(self.wrapper, 'loading');
                }
            }
        }
    }

    /********************************************************************************************/
    // DROPZONE
    /********************************************************************************************/
    MediaLibrary.prototype._initDropZones = function() {
        var options = {
            url: ajaxURL,
            maxFilesize: 10,
            parallelUploads: 1,
            uploadMultiple: false,
            clickable: true,
            createImageThumbnails: false,
            maxFiles: 100,
            autoProcessQueue: false,
            dictInvalidFileType: "Error! Unsupported file or files. You can't upload files of that type.",
            dictFileTooBig: "Error! File or files are too lare. Max upload size is 5mb per file.",
            dictResponseError: "There was an error processing the request. Try again in a few moments.",
            dictMaxFilesExceeded: "Error! Too many uploads at once. Upload limit is 1 file per drop."
        };
        this._listDZ = new Dropzone(Helper.$('.js-dz', this.wrapper), options);
        this._initDzEvents(this._listDZ);
    }

    MediaLibrary.prototype._initDzEvents = function(DZ) {

        var self          = this;
        var progressEl    = Helper.$('.progress', this._globalProgress);

        DZ.on("addedfile", function(file) {
            var node = document.createElement('div');
            node.className = 'media-item uploading';
            node.innerHTML = '<div class="preview"><svg viewBox="0 0 64 64" class="loading-spinner spinner-primary"><circle class="path" cx="32" cy="32" r="30" fill="none" stroke-width="4"></circle></svg><div class="name">'+file.name+'</div></div>';
            var container = self._imagesContainer;
            if (container.firstChild) {
                container.insertBefore(node, container.firstChild);
            }
            else {
                container.appendChild(node);
            }
            var form = {
                'ajax_request' : 'file_upload',
                'access_token' : self._accessToken,
                'file'         : file,
            };
            
            Ajax.upload(ajaxURL, form, 
                function(success) {
                    var responseObj = Helper.isJSON(success);
                    if (responseObj && responseObj.response) {
                        if (Helper.is_array(responseObj.response) && Helper.isset(responseObj.response[0])) {
                            updateUploadedItem(responseObj.response[0], node);
                            resetProgress();
                            return;
                        }
                    }
                    self._submitting = false;
                    Helper.removeFromDOM(node);
                    resetProgress();
                },
                function(error) {
                    self._submitting = false;
                    Helper.removeFromDOM(node);
                    resetProgress();
                },
                function(start) {
                    self._submitting  = true;
                    Helper.addClass(self._globalProgress, 'active');
                },
                function(progress) {
                    progress = progress || window.event;
                    var percentage = (progress.loaded/  progress.total) * 100;
                    progressEl.style.width = progress + "%";
                },
                function(complete) {
                    self._submitting = false;
                    resetProgress();
            });
        });

        DZ.on("error", function(file, response, xhr) {
            Helper.removeFromDOM(file.previewElement);
        });

        function resetProgress()
        {
            progressEl.style.width = "0%";
            Helper.removeClass(self._globalProgress, 'active');
        }

        function updateUploadedItem(details, item)
        {
            var img     = new Image();
            item.className       = 'media-item';
            item.dataset.id      = details.id;
            item.dataset.url     = details.url;
            item.dataset.preview = details.preview;
            item.dataset.path    = details.path;
            item.dataset.date    = details.date;
            item.dataset.alt     = details.alt;
            item.dataset.title   = details.title;
            item.dataset.size    = details.size;
            item.dataset.user    = details.user;
            item.dataset.type    = details.type;
            item.dataset.name    = details.name;
            item.dataset.dimensions = details.dimensions;
            item.dataset.isimage  = Helper.in_array(details.type, self._imageTypes);

            img.setAttribute('alt',   details.alt);
            img.setAttribute('title', details.title);
            img.src = details.preview;

            var center = document.createElement('div');
                center.className = 'center';
                center.appendChild(img);

            var thumb = document.createElement('div');
                thumb.className = 'thumbnail';
                thumb.appendChild(center);

            var preview = document.createElement('div');
                preview.className = 'preview';
                preview.appendChild(thumb);
                preview.innerHTML += '<div class="name">'+details.name+'</div>';

            item.innerHTML = '';
            item.appendChild(preview);
            self._setAsNotEmpty();
        }
    }

    /********************************************************************************************/
    // SCROLLLOADING
    /********************************************************************************************/

    MediaLibrary.prototype._initScrollLoad = function() {
        var self   = this;
        if (this._isTriggerable) {
            var target = this.wrapper.parentNode;
            Helper.addEventListener(target, 'scroll', function(e) {
                e = e | window.event;
                if ((target.offsetHeight + target.scrollTop) >= target.scrollHeight) {
                    if (!self._maxImages && !self._noImages && !self._submitting) {
                        self._requestImages();
                    }
                }
            });
        }
        else {
            Helper.addEventListener(window, 'scroll', function(e) {
                e = e | window.event;
                if ((window.innerHeight + window.scrollY) >= document.body.scrollHeight) {
                    if (!self._maxImages && !self._noImages && !self._submitting) {
                        self._requestImages();
                    }
                }
            });
        }   
    }

    /********************************************************************************************/
    // WRITER
    /********************************************************************************************/
    MediaLibrary.prototype._insertIntoPost = function() {
        var self   = Modules.get('MediaLibrary');

        var URL    = self._fileURLInput.value;
        var title  = self._fileTitleInput.value;
        var alt    = self._fileAltInput.value;
        var size   = Helper.getInputValue(self._sizeSelect);
        var writer = Modules.get('KansoWriter');
        var ext    = Helper.$('#media_url', self._detailsForm).value;
        var linkTo = Helper.getInputValue(self._linkToSelect);
        var blogLocation = self.wrapper.dataset.blogLocation || false;
        blogLocation = blogLocation === 'null' || blogLocation === 'false' ? false : blogLocation;

        var prefix = '';
        var suffix = '';
        var img    = '';

        ext = ext.split('.');
        ext = ext[ext.length - 1];

        if (self._detailsForm.dataset.isimage !== 'false' && size !== 'origional')
        {
            var split   = URL.split('.');
            var ext     = split.pop();
            var name    = split.join('.');
            URL         = name+'_'+size+'.'+ext;   
        }

        if (linkTo === 'file')
        {
            prefix = '<a href="'+URL+'" title="'+title+'">';
            suffix = '</a>';
        }
        else if (linkTo === 'attachment')
        {
            var attachmentUrl = window.location.origin + '/attachment/' + URL.split('/').pop();
            
            if (blogLocation)
            {
                attachmentUrl = window.location.origin + '/' + blogLocation +  '/attachment/' + URL.split('/').pop();
            }

            prefix = '<a href="'+ attachmentUrl + '" title="' + title + '" rel="attachment">';
            suffix = '</a>';
        }
        else if (linkTo === 'custom')
        {
            prefix = '<a href="' + self._linkToInput.value.trim() + '" title="' + title + '">';
            suffix = '</a>';
        }

        if (ext === 'svg')
        {
            img = '<img src="' + URL + '" alt="' + alt + '" title="' + title + '" width="" height="" />';
        }
        else if (self._detailsForm.dataset.isimage !== 'false')
        {
            var img = '<img src="'+URL+'" alt="'+alt+'" title="'+title+'" width="" height=""/>';
        }

        writer._insertText(prefix+img+suffix, writer);
        self._hideLibrary();
        self._hideMediaDetails();
    }

    /********************************************************************************************/
    // HELPERS
    /********************************************************************************************/

   
    /**
     * Is the library empty
     */
    MediaLibrary.prototype._checkIsEmpty = function() {
        var item = Helper.$('.media-item', this.wrapper);
        if (!Helper.nodeExists(item)) this._setAsEmpty();
    }

    /**
     * Hide the library
     */
    MediaLibrary.prototype._hideLibrary = function(e) {
        var self = Modules.get('MediaLibrary');
        self._hideMediaDetails();
        Helper.removeClass(self.wrapper.parentNode, 'active');
        Helper.removeClass(self.wrapper, 'feature-image');
        Helper.removeClass(document.body, 'hide-overflow');

    }

    /**
     * Set the media library as empty
     */
    MediaLibrary.prototype._setAsEmpty = function() {
        Helper.addClass(this.wrapper, 'empty');
        Helper.removeClass(this.wrapper, 'loading');
        this._maxImages = true;
        this._noImages  = true;
    }

    /**
     * Set the media library as empty
     */
    MediaLibrary.prototype._setAsNotEmpty = function() {
        Helper.removeClass(this.wrapper, 'empty');
        Helper.removeClass(this.wrapper, 'loading');
        this._noImages  = false;
    }

    /**
     * Set the media library as reached max images
     */
    MediaLibrary.prototype._setMaxImages = function() {
        Helper.removeClass(this.wrapper, 'loading');
        this._maxImages = true;
    }
    
    /**
     * Get the image form
     */
    MediaLibrary.prototype._imageForm = function() {
        return {
            'ajax_request' : 'load_media',
            'page'         : this._currPage,
            'access_token' :  this._accessToken,
        };
    }
    
     // Load into container and invoke
    Modules.singleton('MediaLibrary', MediaLibrary).require('MediaLibrary');

}());
