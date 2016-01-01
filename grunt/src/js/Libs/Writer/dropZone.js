// ##############################################################################
// FILE: Libs/Writer/dropZone.js
// ##############################################################################

/*-------------------------------------------------------------
**  Initialize the DropZone for images on the writer
--------------------------------------------------------------*/
KansoWriter.prototype.initWriterDZ = function(self) {

    var options = {
        url: GLOBAL_AJAX_URL,
        maxFilesize: 5,
        parallelUploads: 1,
        uploadMultiple: false,
        clickable: false,
        createImageThumbnails: false,
        maxFiles: null,
        acceptedFiles: ".jpg,.png",
        autoProcessQueue: false,
        dictInvalidFileType: "Error! Unsupported file or files. You can't upload files of that type.",
        dictFileTooBig: "Error! File or files are too lare. Max upload size is 5mb per file.",
        dictResponseError: "There was an error processing the request. Try again in a few moments.",
        dictMaxFilesExceeded: "Error! Too many uploads at once. Upload limit is 1 file per drop."
    };
    writerDZ = new Dropzone(CodeMirrorDiv, options);
    self.initWriteDZEvents();

}

/*-------------------------------------------------------------
**  Writer DropZone Core
--------------------------------------------------------------*/
KansoWriter.prototype.initWriteDZEvents = function() {
    var self = this;

    writerDZ.on("drop", function(file) {
        removeClass(CodeMirrorDiv, 'dz-started');
        var existingDrop = $('.dz-preview', CodeMirrorDiv);
        var allDrops = $All('.dz-preview', CodeMirrorDiv);
        if (nodeExists(existingDrop)) {
            for (var i = 0; i < allDrops.length; i++) {
                removeFromDOM(allDrops[i]);
            }
        }
    });

    writerDZ.on("sending", function(file, xhr, formdata) {
        formdata.append("ajaxRequest", 'writer_image_upload');
        formdata.append('public_key', GLOBAL_PUBLIC_KEY);
    });

    writerDZ.on("uploadprogress", function(file, progress) {
        GLOBAL_PROGRESS.style.width = progress + "%";
    });

    writerDZ.on("error", function(file, response, xhr) {
        pushNotification('alert', response);
    });

    writerDZ.on("addedfile", function(file) {
        writerDZ_droppedFiles++;
        if (writerDZ_droppedFiles > 1) {
            writerDZ_sendFiles = false;
            clearTimeout(writerDZ_sendTimer);
            writerDZ_errorTimer = setTimeout(writerDZ_callback_showError, 300);
        } else if (writerDZ_droppedFiles === 1) {
            writerDZ_sendTimer = setTimeout(self.writerDZ_callback_processQueu, 300);
        }

    });

    writerDZ.on("success", function(files, response) {
        if (typeof response !== 'object') response === isJSON(response);
        if (typeof response === 'object') {
            if (response && response['response'] && response['response'] === 'processed') {
                var name = response['details'];
                var toInsert = '![Alt Text](' + encodeURI(name) + ' "Image Title")';
                self.writer.replaceSelection(toInsert, 'start');
                pushNotification('success', 'Your file was successfully uploaded!');
                clearTimeout(self.savePostTimer);
                addClass($('.js-save-post'), 'active');
                return;
            }
        }
        pushNotification('error', 'There was an error processing the request. Try again in a few moments.');
    });

    writerDZ.on("complete", function(file) {
        writerDZ_sendFiles = true;
        writerDZ_droppedFiles = 0;
        GLOBAL_PROGRESS.style.width = "0%";
        writerDZ_imgInserted = [];
    });

    writerDZ.on("dragenter", function(e) {
        if (writerDZ_imgInserted.length > 0) return;
        var toInsert = '![Alt Text](https://example.com/image.jpg "Image Title")';
        var cursorPos = self.writer.getCursor();
        cursorPos = {
            line: cursorPos.line,
            ch: cursorPos.line
        };
        if (self.writer.somethingSelected()) self.writer.setSelection(cursorPos);
        writerDZ_imgInserted.push(cursorPos);
        self.writer.replaceSelection(toInsert, 'around');
    });

    writerDZ.on("dragleave", function(e) {
        if (writerDZ_imgInserted.length > 0) {
            self.writer.replaceSelection("", 'start');
            writerDZ_imgInserted = [];
        }
    });
}

/*-------------------------------------------------------------
**  Callback functions for Writer DropZone Events
--------------------------------------------------------------*/
KansoWriter.prototype.writerDZ_callback_showError = function() {
    clearTimeout(writerDZ_errorTimer);
    pushNotification('error', 'Error! Too many uploads at once. Upload limit is 1 file per drop.');
    writerDZ_sendFiles = true;
    writerDZ_droppedFiles = 0;
    writerDZ_imgInserted = [];
}

KansoWriter.prototype.writerDZ_callback_processQueu = function() {
    clearTimeout(writerDZ_sendTimer);
    if (writerDZ_sendFiles === true) writerDZ.processQueue();
    writerDZ_sendFiles = true;
    writerDZ_droppedFiles = 0;
    writerDZ_imgInserted = [];
}
