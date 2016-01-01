// ##############################################################################
// FILE: Libs/Writer/variables.js
// ##############################################################################

// ##############################################################################
// KANSO WRITER APPLICATION START
// ##############################################################################
(function() {

        /*-------------------------------------------------------------
	** Global variables for application
	--------------------------------------------------------------*/
        var doc = document.documentElement;
        var ajaxURL = window.location.href.replace(/admin(.+)/, 'admin/');

        // Writer
        var writerTextArea = $('#writer');
        var writerDiv = $('.writer');
        var CodeMirrorDiv;

        // Inputs and buttons
        var saveBtn = $('.writer-footer .js-save-post');
        var publishBtn = $('.reviewer .js-article-form button.submit');
        var articleForm = $('.reviewer .js-article-form');
        var articleInputs = $All('.reviewer .js-article-form input, .reviewer .js-article-form textarea, .reviewer .js-article-form select');

        // Global writer dopzone variables
        var writerDZ;
        var writerDZ_sendTimer;
        var writerDZ_errorTimer;
        var writerDZ_sendFiles = true;
        var writerDZ_droppedFiles = 0;
        var writerDZ_imgInserted = [];
        var thumbnailInput = $('.js-thumbnail');

        // Global hero image dropzone variables
        var heroDZ;
        var heroDZ_dropwrap = $('.js-hero-drop form');
        var heroDZ_progressBar = $('.js-hero-drop .upload-bar .progress');
        var heroDZ_sendTimer;
        var heroDZ_errorTimer;
        var heroDZ_sendFiles = true;
        var heroDZ_droppedFiles = 0;

        // Panels
        var writerWrap = $('.writer');
        var readWrap = $('.reader');
        var reviewWrap = $('.reviewer');
        var viewWraps;

        // Panel scrolls
        var readScroll = 0;
        var writeScroll = 0;
        var reviewScroll = 0;

        // footer
        var writerFooter = $('.writer-footer');
        var footerTimer;

        // footer view togglers
        var writeTrigger = $('.writer-footer .js-raw');
        var readTrigger = $('.writer-footer .js-html');
        var reviewTrigger = $('.writer-footer .js-pre-publish');
        var toggleTriggers;
        var headerTimer;
