// ##############################################################################
// FILE: Pages/Admin/tools.js
// ##############################################################################


// ##############################################################################
// BATCH IMPORT ARTICLES
// ##############################################################################
(function() {

    var articleImportInput = $('.js-batch-import input');

    if (nodeExists(articleImportInput)) articleImportInput.addEventListener('change', articleImportAjax);


    function articleImportAjax() {

        // Don't upload when active
        if (hasClass(articleImportInput.parentNode, 'active')) return;

        // Add spinner
        addClass(articleImportInput.parentNode, 'active');

        // Initialize uploader
        var uploader = Uploader(articleImportInput, ['application/json'], 1).init();

        // Validate the mime types
        if (!uploader.validateMime()) {

            articleImportInput.value = ""; // reset the input

            GLOBAL_PROGRESS.style.width = '0px'; // reset progress bar

            removeClass(articleImportInput.parentNode, 'active'); // remove spinner

            pushNotification('error', 'Unsupported file type. You need to upload a valid JSON file.');
            return;
        }

        // Validate the file size
        if (!uploader.validateFileSizes()) {

            articleImportInput.value = ""; // reset the input

            GLOBAL_PROGRESS.style.width = '0px'; // reset progress bar

            removeClass(articleImportInput.parentNode, 'active'); // remove spinner

            pushNotification('error', 'File is too large. Max file size is 5mb.');
            return;
        }

        // Validate the amount of files
        if (!uploader.validateMaxFiles()) {
            articleImportInput.value = ""; // reset the input

            GLOBAL_PROGRESS.style.width = '0px'; // reset progress bar

            removeClass(articleImportInput.parentNode, 'active'); // remove spinner

            pushNotification('error', 'Too many files uploading at once. You can only upload a single file at a time.');
            return;
        }

        // Append validation to form
        uploader.append('ajaxRequest', 'admin_import_articles');
        uploader.append('public_key', GLOBAL_PUBLIC_KEY);
        uploader.append('referer', window.location.href);


        // only send ajax when authentification is valid
        if (GLOBAL_AJAX_ENABLED) {

            // do upload
            uploader.upload(GLOBAL_AJAX_URL,

                // success
                function(success) {

                    articleImportInput.value = ""; // reset the input

                    GLOBAL_PROGRESS.style.width = '0px'; // reset progress bar

                    removeClass(articleImportInput.parentNode, 'active'); // remove spinner

                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        pushNotification('success', 'Your articles were successfully imported!');
                    } else {
                        pushNotification('error', 'Your articles could not be imported. The JSON file you uploaded is invalid for import.');
                    }
                },

                // error 
                function(error) {
                    pushNotification('error', 'The server encoutered an error while processing your request.');

                    articleImportInput.value = ""; // reset the input

                    GLOBAL_PROGRESS.style.width = '0px'; // reset progress bar

                    removeClass(articleImportInput.parentNode, 'active'); // remove spinner

                },

                // progress 
                function(progress) {
                    GLOBAL_PROGRESS.style.width = progress + "%";
                }
            );
        }

        // Ajax is disabled
        else {
            articleImportInput.value = ""; // reset the input

            GLOBAL_PROGRESS.style.width = '0px'; // reset progress bar

            removeClass(articleImportInput.parentNode, 'active'); // remove spinner
        }
    }

}());

// ##############################################################################
// BATCH UPLOAD IMAGES
// ##############################################################################
(function() {

    var imagesUploadInput = $('.js-batch-images input');

    if (nodeExists(imagesUploadInput)) imagesUploadInput.addEventListener('change', batchImageUpload);

    function batchImageUpload() {

        // Don't upload when active
        if (hasClass(imagesUploadInput.parentNode, 'active')) return;

        // Add spinner
        addClass(imagesUploadInput.parentNode, 'active');

        // Initialize the uploader
        var uploader = Uploader(imagesUploadInput, ['image/jpeg', 'image/png', 'image/gif'], 50).init();

        // Validate the mime types
        if (!uploader.validateMime()) {

            imagesUploadInput.value = ""; // reset input

            GLOBAL_PROGRESS.style.width = '0px'; // reset progress

            removeClass(imagesUploadInput.parentNode, 'active'); // remove spinner

            pushNotification('error', 'Unsupported file types were found. You can only upload GIF, PNG and JPG images.');
            return;
        }

        // Validate the file size
        if (!uploader.validateFileSizes()) {

            imagesUploadInput.value = ""; // reset input

            GLOBAL_PROGRESS.style.width = '0px'; // reset progress

            removeClass(imagesUploadInput.parentNode, 'active'); // remove spinner

            pushNotification('error', 'One of the files is too large. Max file size is 5mb.');
            return;
        }

        // Validate the amount of files
        if (!uploader.validateMaxFiles()) {

            imagesUploadInput.value = ""; // reset input

            GLOBAL_PROGRESS.style.width = '0px'; // reset progress

            removeClass(imagesUploadInput.parentNode, 'active'); // remove spinner

            pushNotification('error', 'Too many files uploading at once. You can upload a maximum of 50 files at a time.');
            return;
        }

        // Append validation to form
        uploader.append('ajaxRequest', 'admin_batch_image');
        uploader.append('public_key', GLOBAL_PUBLIC_KEY);
        uploader.append('referer', window.location.href);

        // Only send ajax when authentification is valid
        if (GLOBAL_AJAX_ENABLED) {

            // do upload
            uploader.upload(GLOBAL_AJAX_URL,

                // success
                function(success) {

                    imagesUploadInput.value = ""; // reset input

                    GLOBAL_PROGRESS.style.width = '0px'; // reset progress

                    removeClass(imagesUploadInput.parentNode, 'active'); // remove spinner

                    // Parse the response
                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        pushNotification('success', 'Your images were successfully uploaded!');
                    } else if (responseObj && responseObj.details === 'server_error') {
                        pushNotification('error', 'The server encoutered an error while processing your request.');
                    } else if (responseObj && responseObj.details === 'invalid_size') {
                        pushNotification('error', 'One or more of the files are too large. Max file size is 5mb.');
                    } else if (responseObj && responseObj.details === 'invalid_mime') {
                        pushNotification('error', 'Unsupported file types were found. You can only upload GIF, PNG and JPG images.');
                    }
                },

                // error
                function(error) {

                    imagesUploadInput.value = ""; // reset input

                    GLOBAL_PROGRESS.style.width = '0px'; // reset progress

                    removeClass(imagesUploadInput.parentNode, 'active'); // remove spinner

                    pushNotification('error', 'The server encoutered an error while processing your request.');

                },

                // progress
                function(progress) {
                    GLOBAL_PROGRESS.style.width = progress + "%";
                }
            );
        }

        // Ajax is disabled
        else {
            imagesUploadInput.value = ""; // reset input

            GLOBAL_PROGRESS.style.width = '0px'; // reset progress

            removeClass(imagesUploadInput.parentNode, 'active'); // remove spinner

            pushNotification('error', 'The server encoutered an error while processing your request.');
        }
    }

}());



// ##############################################################################
// RESTORE KANSO
// ##############################################################################
(function() {

    var clearKansoTrigger = $('.js-clear-kanso-database');

    if (nodeExists(clearKansoTrigger)) clearKansoTrigger.addEventListener('click', confirmAction);


    function confirmAction() {
        event.preventDefault();
        pushCallBackNotification('info', 'Are you POSITIVE you want to restor Kanso to its origional settings?', 'Restore Kanso', restoreKanso);

    }

    function restoreKanso() {

        addClass(clearKansoTrigger, 'active');

        var form = {};
        form['ajaxRequest'] = 'admin_restore_kanso';
        form['public_key'] = GLOBAL_PUBLIC_KEY;
        form['referer'] = window.location.href;

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        window.location.href = window.location.href = GLOBAL_AJAX_URL + 'login/';
                    } else {
                        removeClass(clearKansoTrigger, 'active');
                        pushNotification('error', 'There was an error restoring Kanso\'s settings.');
                        return;
                    }
                },
                function(error) {
                    removeClass(clearKansoTrigger, 'active');
                    pushNotification('error', 'The server encoutered an error while processing your request.');
                    return;
                });
        } else {
            removeClass(clearKansoTrigger, 'active');
            pushNotification('error', 'The server encoutered an error while processing your request.');
            return;
        }
    }

}());
