// ##############################################################################
// FILE: Libs/Writer/dropZoneHero.js
// ##############################################################################

/*-------------------------------------------------------------
**  Initialize DropZone for Hero Image
--------------------------------------------------------------*/
KansoWriter.prototype.initHeroDZ = function() {

    var options = {
        url: GLOBAL_AJAX_URL,
        maxFilesize: 5,
        parallelUploads: 1,
        uploadMultiple: false,
        clickable: true,
        createImageThumbnails: true,
        maxFiles: null,
        acceptedFiles: ".jpg,.png",
        autoProcessQueue: false,
        maxThumbnailFilesize: 5,
        thumbnailWidth: 800,
        thumbnailHeight: 400,
        resize: this.resizeHeroDropImage,
        dictInvalidFileType: "Error! Unsupported file or files. You can't upload files of that type.",
        dictFileTooBig: "Error! File or files are too lare. Max upload size is 5mb per file.",
        dictResponseError: "There was an error processing the request. Try again in a few moments.",
        dictMaxFilesExceeded: "Error! Too many uploads at once. Upload limit is 1 file per drop."
    };

    heroDZ = new Dropzone(heroDZ_dropwrap, options);

    this.initHeroDZEvents();
}

/*-------------------------------------------------------------
**  Resize CTX hero image properly
--------------------------------------------------------------*/
KansoWriter.prototype.resizeHeroDropImage = function(file) {
    var w = file['width'];
    var h = file['height'];
    var imageResize = ImageResizer(w, h, true);
    var resized = imageResize.crop(800, 400);
    return {
        srcX: resized.source_x,
        srcY: resized.source_y,
        srcWidth: resized.source_w,
        srcHeight: resized.source_h,

        trgX: resized.dest_x,
        trgY: resized.dest_y,
        trgWidth: 800,
        trgHeight: 400,
    };
};

/*-------------------------------------------------------------
**  Initialize events on the hero image DropZone
--------------------------------------------------------------*/
KansoWriter.prototype.initHeroDZEvents = function() {

    var DZ = heroDZ;
    var self = this;

    DZ.on("uploadprogress", function(file, progress) {
        heroDZ_progressBar.style.width = progress + "%";
    });

    DZ.on("sending", function(file, xhr, formdata) {
        formdata.append("ajaxRequest", 'writer_image_upload');
        formdata.append('public_key', GLOBAL_PUBLIC_KEY);
    });

    DZ.on("drop", function(file) {
        self.HeroDZ_cleanUp();
    });

    DZ.on("error", function(file, response, xhr) {
        self.HeroDZ_cleanUp();
        pushNotification('alert', response);
    });

    DZ.on("addedfile", function(file) {
        heroDZ_droppedFiles++;
        if (heroDZ_droppedFiles > 1) {
            self.HeroDZ_cleanUp();
            heroDZ_sendFiles = false;
            clearTimeout(heroDZ_sendTimer);
            heroDZ_errorTimer = setTimeout(self.HeroDZ_showError, 300);
        } else if (heroDZ_droppedFiles === 1) {
            heroDZ_sendTimer = setTimeout(self.HeroDZ_processQueu, 300);
        }

    });

    DZ.on("success", function(files, response) {
        if (typeof response !== 'object') response === isJSON(response);
        if (typeof response === 'object') {
            if (response && response['response'] && response['response'] === 'processed') {
                pushNotification('success', 'Your file was successfully uploaded!');
                self.HeroDZ_updateForm(response['details']);
                clearTimeout(self.savePostTimer);
                addClass($('.js-save-post'), 'active');
                return;
            }
        }
        pushNotification('error', 'There was an error processing the request. Try again in a few moments.');
    });

    DZ.on("complete", function(file) {
        heroDZ_sendFiles = true;
        heroDZ_droppedFiles = 0;
        heroDZ_progressBar.style.width = "0%";
    });

}

/*-------------------------------------------------------------
**  Hero Image DropZone Callbacks
--------------------------------------------------------------*/
KansoWriter.prototype.HeroDZ_showError = function() {
    clearTimeout(heroDZ_errorTimer);
    pushNotification('error', 'Error! Too many uploads at once. Upload limit is 1 file per drop.');
    heroDZ_sendFiles = true;
    heroDZ_droppedFiles = 0;
}

KansoWriter.prototype.HeroDZ_processQueu = function() {
    clearTimeout(heroDZ_sendTimer);
    if (heroDZ_sendFiles === true) heroDZ.processQueue();
    heroDZ_sendFiles = true;
    heroDZ_droppedFiles = 0;
}

KansoWriter.prototype.HeroDZ_cleanUp = function() {
    removeClass(heroDZ_dropwrap, 'dz-started');
    var existingDrop = $('.js-hero-drop .dz-preview');
    var allDrops = $All('.js-hero-drop .dz-preview');
    if (nodeExists(existingDrop)) {
        for (var i = 0; i < allDrops.length; i++) {
            removeFromDOM(allDrops[i]);
        }
    }
}
KansoWriter.prototype.HeroDZ_updateForm = function(imgURL) {
    clearTimeout(self.savePostTimer);
    imgURL = imgURL.substring(imgURL.lastIndexOf('/') + 1);
    thumbnailInput.value = imgURL;
    addClass($('.js-save-post'), 'active');
}
