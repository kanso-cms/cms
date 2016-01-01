// ##############################################################################
// FILE: Libs/fileUploader.js
// ##############################################################################

/* Image resizer for CTX - Done right */
var Uploader = function(input, acceptedMime, maxFiles, maxFileSize) {
    if (!(this instanceof Uploader)) {
        return new Uploader(input, acceptedMime, maxFiles, maxFileSize)
    }

    this.files = input.files;
    this.acceptedMime = acceptedMime;
    this.maxFiles = (typeof maxFiles === 'undefined' ? 1 : maxFiles);
    this.formObj = new FormData;
    this.maxFileSize = (typeof maxFileSize === 'undefined' ? 5000000 : maxFileSize);

    return this;
};

Uploader.prototype = {

    init: function() {
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            if (in_array(file.type, this.acceptedMime)) this.formObj.append('file[]', file, file.name);
        }
        return this;
    },

    validateMime: function() {
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            if (!in_array(file.type, this.acceptedMime)) return false;
        }
        return true;
    },

    validateMaxFiles: function() {
        return this.files.length <= this.maxFiles;
    },

    validateFileSizes: function() {
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            if (file.size > this.maxFileSize) return false;
        }
        return true;
    },

    append: function(key, value) {
        this.formObj.append(key, value);
        return this;
    },

    upload: function(url, success, error, onProgress) {

        var self = this;
        var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X_REQUESTED_WITH', 'XMLHttpRequest');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                var percentComplete = (e.loaded / e.total) * 100;
                if (isCallable(onProgress)) onProgress(percentComplete);
            }
        }

        xhr.onload = function() {
            if (xhr.readyState == 4) {

                if (xhr.status >= 200 && xhr.status < 300 || xhr.status === 304) {

                    var data = xhr.responseXML || xhr.responseText;

                    if (isCallable(success)) success(data, xhr);
                } else {
                    if (isCallable(error)) error(xhr, xhr.status);
                }
            }
        }

        xhr.send(this.formObj);
    }
};
