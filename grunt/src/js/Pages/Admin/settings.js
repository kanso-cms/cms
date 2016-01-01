// ##############################################################################
// FILE: Pages/Admin/settings.js
// ##############################################################################

// ##############################################################################
// CHECKBOX CLICKERS 
// ##############################################################################


(function() {

    // Query all the checkbox inputs
    var checkBoxs = $All('.admin-settings input[type=checkbox');

    // If a checkbox exists initialize the listeners
    if (nodeExists($('.admin-settings input[type=checkbox'))) initCheckBoxListeners();

    /**
     * Loop check boxes and add change listeners
     * 
     * @param {string} title - The title of the book.
     * @param {string} author - The author of the book.
     */
    function initCheckBoxListeners() {
        for (var i = 0; i < checkBoxs.length; i++) {
            checkBoxs[i].addEventListener('change', toggleCheck);
        }
    }

    function toggleCheck() {
        var checkbox = event.target;
        var checked = checkbox.checked;
        var div = checkbox.parentNode.nextSibling;
        while (div && div.tagName !== 'DIV') {
            div = div.nextSibling;
        }
        if (checked) {
            addClass(div, 'active');
        } else {
            removeClass(div, 'active');
        }

    }

}());

// ##############################################################################
// INPUT MASKERS
// ##############################################################################
(function() {
    var numberInputs = $All('.js-input-mask-number');

    if (nodeExists($('.js-input-mask-number'))) {
        for (var i = 0; i < numberInputs.length; i++) {
            VMasker(numberInputs[i]).maskNumber();
        }
    }

}());


// ##############################################################################
// UPDATE ADMIN SETTINGS
// ##############################################################################
(function() {

    var adminSettingsForm = $('form.js-update-admin-form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(adminSettingsForm)) {
        inputs = $All('input', adminSettingsForm);
        submitBtn = $('.submit', adminSettingsForm)
        submitBtn.addEventListener('click', submitAdminSettings);
    }

    function submitAdminSettings() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(adminSettingsForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_update_settings');

        if (!validator.validForm) {
            showAjaxInputErrors(validator.invalids, adminSettingsForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {

                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        removeClass(submitBtn, 'active');
                        pushNotification('success', 'Your settings were successfully updated!');
                        return;
                    }
                    removeClass(submitBtn, 'active');
                    pushNotification('error', 'There was an error updating your settings!');
                },
                function(error) {
                    removeClass(submitBtn, 'active');
                    pushNotification('error', 'There was an error updating your settings!');
                    return;
                });
        } else {
            removeClass(submitBtn, 'active');
            pushNotification('error', 'There was an error updating your settings!');
        }

    }

}());


// ##############################################################################
// UPDATE AUTHOR SETTINGS
// ##############################################################################
(function() {

    var authorSettingsForm = $('form.js-author-settings-form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(authorSettingsForm)) {
        inputs = $All('input, textarea', authorSettingsForm);
        submitBtn = $('.submit', authorSettingsForm)
        submitBtn.addEventListener('click', submitauthorSettings);
    }

    function submitauthorSettings() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(authorSettingsForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_update_author');

        if (!validator.validForm) {
            showAjaxInputErrors(validator.invalids, authorSettingsForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        removeClass(submitBtn, 'active');
                        pushNotification('success', 'Your settings were successfully updated!');
                    } else {
                        removeClass(submitBtn, 'active');
                        pushNotification('error', 'There was an error updating your settings!');
                        return;
                    }
                },
                function(error) {
                    removeClass(submitBtn, 'active');
                    pushNotification('error', 'There was an error updating your settings!');
                    return;
                });
        } else {
            removeClass(submitBtn, 'active');
            pushNotification('error', 'There was an error updating your settings!');
        }

    }

}());


// ##############################################################################
// UPLOAD AUTHOR IMAGE
// ##############################################################################

(function() {

    var dropwrap = $('.js-author-hero-drop');
    var progressBar = $('.js-author-hero-drop .upload-bar .progress');
    var authorDZ;

    var sendTimer;
    var errorTimer;
    var sendFiles = true;
    var droppedFiles = 0;

    if (nodeExists(dropwrap)) {
        var options = {
            url: window.location.href.replace(/admin(.+)/, 'admin/'),
            maxFilesize: 5,
            parallelUploads: 1,
            uploadMultiple: false,
            clickable: true,
            createImageThumbnails: true,
            maxFiles: null,
            acceptedFiles: ".jpg,.png",
            autoProcessQueue: false,
            maxThumbnailFilesize: 5,
            thumbnailWidth: 150,
            thumbnailHeight: 150,
            resize: resizeDropImage,
            dictInvalidFileType: "Error! Unsupported file or files. You can't upload files of that type.",
            dictFileTooBig: "Error! File or files are too lare. Max upload size is 5mb per file.",
            dictResponseError: "There was an error processing the request. Try again in a few moments.",
            dictMaxFilesExceeded: "Error! Too many uploads at once. Upload limit is 1 file per drop."
        };

        authorDZ = new Dropzone(dropwrap, options);
        initDropEvents();
    }


    function resizeDropImage(file) {
        var w = file.width;
        var h = file.height;
        var imageResize = ImageResizer(w, h, true);
        var resized = imageResize.crop(150, 150);
        return {
            srcX: resized.source_x,
            srcY: resized.source_y,
            srcWidth: resized.source_w,
            srcHeight: resized.source_h,

            trgX: resized.dest_x,
            trgY: resized.dest_y,
            trgWidth: 150,
            trgHeight: 150,
        };
    }



    function initDropEvents() {
        var DZ = authorDZ;

        DZ.on("uploadprogress", function(file, progress) {
            progressBar.style.width = progress + "%";
        });

        DZ.on("sending", function(file, xhr, formdata) {
            formdata.append("ajaxRequest", 'admin_author_image');
            formdata.append('public_key', GLOBAL_PUBLIC_KEY);
        });

        DZ.on("drop", function(file) {
            cleanUpDropZone();
        });

        DZ.on("error", function(file, response, xhr) {
            cleanUpDropZone();
            handleError(file, response, xhr);
        });

        DZ.on("addedfile", function(file) {
            droppedFiles++;
            if (droppedFiles > 1) {
                cleanUpDropZone();
                sendFiles = false;
                clearTimeout(sendTimer);
                errorTimer = setTimeout(showError, 300);
            } else if (droppedFiles === 1) {
                sendTimer = setTimeout(processQueu, 300);
            }

        });

        DZ.on("success", function(files, response) {
            if (typeof response !== 'object') response = isJSON(response);
            if (typeof response === 'object') {
                if (response && response.response && response.response === 'processed' && response.details.indexOf("http://") > -1) {
                    pushNotification('success', 'Your file was successfully uploaded!');
                    return;
                }
            }
            pushNotification('error', 'There was an error processing the request. Try again in a few moments.');
        });

        DZ.on("complete", function(file) {
            sendFiles = true;
            droppedFiles = 0;
            progressBar.style.width = "0%";
        });
    }

    function showError() {
        clearTimeout(errorTimer);
        pushNotification('error', 'Error! Too many uploads at once. Upload limit is 1 file per drop.');
        sendFiles = true;
        droppedFiles = 0;
    }

    function processQueu() {
        clearTimeout(sendTimer);
        if (sendFiles === true) authorDZ.processQueue();
        sendFiles = true;
        droppedFiles = 0;
    }

    function cleanUpDropZone() {
        removeClass(dropwrap, 'dz-started');
        var existingDrop = $('.js-author-hero-drop .dz-preview');
        var allDrops = $All('.js-author-hero-drop .dz-preview');
        if (nodeExists(existingDrop)) {
            for (var i = 0; i < allDrops.length; i++) {
                removeFromDOM(allDrops[i]);
            }
        }
    }

    function handleError(file, response, xhr) {
        pushNotification('alert', response);
    }

}());

// ##############################################################################
// UPDATE KANSO SETTINGS
// ##############################################################################
(function() {

    var kansoSettingsForm = $('form.js-kanso-settings-form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(kansoSettingsForm)) {
        inputs = getFormInputs(kansoSettingsForm);
        submitBtn = $('button.submit', kansoSettingsForm)
        submitBtn.addEventListener('click', submitKansoSettings);
    }

    function submitKansoSettings() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(kansoSettingsForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_update_kanso');


        if (!validator.validForm) {
            showAjaxInputErrors(validator.invalids, kansoSettingsForm);
            return;
        }

        var form = validator.getForm();


        if (form['use-cache'] === true && form['cache-life'] === '') {
            showAjaxInputErrors([validator.getInput('cache-life')], kansoSettingsForm);
            return;
        }

        if (form['use-CDN'] === true && form['CDN-url'] === '') {
            showAjaxInputErrors([validator.getInput('CDN-url')], kansoSettingsForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {
                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        removeClass(submitBtn, 'active');
                        pushNotification('success', 'Your settings were successfully updated!');
                        return;
                    } else if (responseObj && responseObj.details === 'theme_no_exist') {
                        removeClass(submitBtn, 'active');
                        pushNotification('error', 'The theme you specified does not exists.');
                        return;
                    } else if (responseObj && responseObj.details === 'invalid_permalinks') {
                        removeClass(submitBtn, 'active');
                        pushNotification('error', 'Your permalinks structure is invalid. Please enter a valid permalinks wildcard.');
                        return;
                    } else if (responseObj && responseObj.details === 'invalid_img_quality') {
                        removeClass(submitBtn, 'active');
                        pushNotification('error', 'The thumbnail quality you entered is invalid. Thumbnail quality needs to be between 1-100');
                        return;
                    } else if (responseObj && responseObj.details === 'invalid_cdn_url') {
                        removeClass(submitBtn, 'active');
                        pushNotification('error', 'The CDN url you intered is not a valid url. Please enter a valid url.');
                        return;
                    } else if (responseObj && responseObj.details === 'invalid_cache_life') {
                        removeClass(submitBtn, 'active');
                        pushNotification('error', 'The cache-life you entered is invalid. Please enter a vailid cache-life');
                        return;
                    }
                },
                function(error) {
                    removeClass(submitBtn, 'active');
                    pushNotification('error', 'There was an error updating your settings!');
                    return;
                });
        } else {
            removeClass(submitBtn, 'active');
            pushNotification('error', 'There was an error updating your settings!');
        }

    }


}());


// ##############################################################################
// INVITE NEW USERS
// ##############################################################################
(function() {

    var inviteUserForm = $('form.js-invite-user-form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(inviteUserForm)) {
        inputs = $All('input, select', inviteUserForm);
        submitBtn = $('.submit', inviteUserForm)
        submitBtn.addEventListener('click', submitauthorSettings);
    }

    function submitauthorSettings() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(inviteUserForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_invite_user');

        if (!validator.validForm) {
            showAjaxInputErrors(validator.invalids, inviteUserForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        removeClass(submitBtn, 'active');
                        pushNotification('success', 'The user was successfully sent an invitation to join your website!');
                    }
                    if (responseObj && responseObj.details === 'already_member') {
                        removeClass(submitBtn, 'active');
                        pushNotification('alert', 'Another user is already singed up under that email address.');
                        return;
                    }
                    if (responseObj && responseObj.details === 'no_send') {
                        removeClass(submitBtn, 'active');
                        pushNotification('error', 'There was an error inviting that user. You need to be running Kanso on a live server, with PHP\'s mail() function to send emails.');
                        return;
                    }
                },
                function(error) {
                    removeClass(submitBtn, 'active');
                    pushNotification('error', 'There was an error inviting that user.');
                    return;
                });
        } else {
            removeClass(submitBtn, 'active');
            pushNotification('error', 'There was an error inviting that user.');
            return;
        }

    }

}());

// ##############################################################################
// DELETE USERS
// ##############################################################################

(function() {
    var deletTriggers = $All('.js-delete-author');

    if (nodeExists($('.js-delete-author'))) {
        for (var i = 0; i < deletTriggers.length; i++) {
            deletTriggers[i].addEventListener('click', confirmDelete);
        }
    }

    function confirmDelete() {

        event.preventDefault();

        var clicked = closest(event.target, 'a');

        if (hasClass(clicked, 'active')) return;

        pushCallBackNotification('info', 'Are you POSITIVE you want to permanently delete this user?', 'Delete', deleteUser, clicked);
    }

    function deleteUser(clicked) {

        addClass(clicked, 'active');

        var row = closest(clicked, 'tr');

        var form = {
            ajaxRequest: "admin_delete_user",
            id: parseInt(clicked.dataset.authorId),
            public_key: GLOBAL_PUBLIC_KEY,
            referer: window.location.href,
        };

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        removeClass(clicked, 'active');
                        pushNotification('success', 'The user was successfully deleted. Their authorship articles have been transferred to you.');
                        removeFromDOM(row);
                    } else {
                        removeClass(clicked, 'active');
                        pushNotification('error', 'There was an error deleting that user.');
                        return;
                    }
                },
                function(error) {
                    removeClass(clicked, 'active');
                    pushNotification('error', 'There was an error deleting that user.');
                    return;
                });
        } else {
            removeClass(clicked, 'active');
            pushNotification('error', 'There was an error deleting that user.');
            return;
        }
    }


}());

// ##############################################################################
// CHANGE A USE ROLE
// ##############################################################################
(function() {
    var changeRolls = $All('.js-change-role');

    if (nodeExists($('.js-change-role'))) {
        for (var i = 0; i < changeRolls.length; i++) {
            changeRolls[i].addEventListener('change', changeUserRole);
        }
    }

    function changeUserRole() {
        var selector = closest(event.target, 'select');
        var role = selector.options[selector.selectedIndex].value;

        var form = {
            ajaxRequest: "admin_change_user_role",
            role: role,
            id: selector.dataset.authorId,
            public_key: GLOBAL_PUBLIC_KEY,
            referer: window.location.href,
        };

        pushCallBackNotification('info', 'Are you POSITIVE you want to change this this user\'s account role?', 'Change Role', postRoleChange, [form, selector], revertChange, selector);
    }

    function postRoleChange(args) {
        var form = args[0];
        var selector = args[1];

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        pushNotification('success', 'The user\'s account role was successfully changed.');
                    } else {
                        revertChange(selector);
                        pushNotification('error', 'There was an error changing that user\'s account role.');
                        return;
                    }
                },
                function(error) {
                    revertChange(selector);
                    pushNotification('error', 'There was an error changing that user\'s account role.');
                    return;
                });
        } else {
            revertChange(selector);
            pushNotification('error', 'There was an error changing that user\'s account role.');
            return;
        }
    }

    function revertChange(selector) {
        var selectCount = count(selector.options);
        var selectedIndex = selector.selectedIndex;
        var lastSelected = selectedIndex === selectCount ? selectedIndex - 1 : selectedIndex + 1;
        selector.value = selector.options[lastSelected].value;
    }

}());

// ##############################################################################
// CLEAR CACHE
// ##############################################################################

(function() {
    var cacheClearer = $('.js-clear-kanso-cache');
    if (nodeExists(cacheClearer)) cacheClearer.addEventListener('click', clearKansoCache);

    function clearKansoCache() {

        event.preventDefault();

        var clicked = closest(event.target, 'a');

        if (hasClass(clicked, 'active')) return;

        var form = {
            ajaxRequest: "admin_clear_cache",
            public_key: GLOBAL_PUBLIC_KEY,
            referer: window.location.href,
        };

        addClass(clicked, 'active');

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        removeClass(clicked, 'active');
                        pushNotification('success', 'Kanso\'s cache was successfully cleared!');
                    } else {
                        removeClass(clicked, 'active');
                        pushNotification('error', 'There server encountered an error while clearing the cache.');
                        return;
                    }
                },
                function(error) {
                    removeClass(clicked, 'active');
                    pushNotification('error', 'There server encountered an error while clearing the cache.');
                    return;
                });
        } else {
            removeClass(clicked, 'active');
            pushNotification('error', 'There server encountered an error while clearing the cache.');
            return;
        }
    }

}());
