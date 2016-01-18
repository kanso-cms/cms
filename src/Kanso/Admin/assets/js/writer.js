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

// ##############################################################################
// FILE: Libs/Writer/initialize.js
// ##############################################################################

/*-------------------------------------------------------------
**  Writer application core
--------------------------------------------------------------*/
var KansoWriter = function() {

    this.version = "1.0.0";
    this.author = "Joe Howard";
    this.copyright = "Kanso 2015";
    this.writer = null;
    this.saveTimer = null;
    this.hasSaved = false;

    // Markdown 2 HTML
    this.markdown = window.markdownit({
        html: true,
        xhtmlOut: false,
        breaks: true,
        langPrefix: '',
        linkify: false,
        highlight: this.highlightCode,
    });

    this.articleID = null;
    this.ajaxType = 'writer_save_new_article';

    // Initialize the application
    this.initialize();
    this.initCodeMirror();
    this.initWindowResize();
    this.initHeroDZ();
    this.initInputChanges();
    this.initFooter();
}

// reset the prototype
KansoWriter.prototype = {};


/*-------------------------------------------------------------
**  Initialize the application
--------------------------------------------------------------*/
KansoWriter.prototype.initialize = function() {

    var self = this;

    // Set the post id if it exists
    var postID = writerTextArea.dataset.id;
    if (postID && postID !== '') {
        this.articleID = parseInt(postID);
        this.ajaxType = 'writer_save_existing_article';
    }

    // Add listener for save
    saveBtn.addEventListener('click', function(e) {
        self.saveArticle(e, self);
    });

    // Add listener for publish
    publishBtn.addEventListener('click', function(e) {
        self.publishArticle(e, self);
    });

    this.initToggleHeader();

}

/*-------------------------------------------------------------
**  Code highlight function for markdown converter
--------------------------------------------------------------*/
KansoWriter.prototype.highlightCode = function(str, syntax) {

    if (syntax === '') return str;
    try {
        return hljs.highlight(syntax, str).value;
    } catch (e) {
        return str;
    }

}

// ##############################################################################
// FILE: Libs/Writer/header.js
// ##############################################################################

/*-------------------------------------------------------------
**  Toggle multiple headers
--------------------------------------------------------------*/
KansoWriter.prototype.initToggleHeader = function() {

    var header = $('.header');
    var toggleUp = $('.js-show-header');
    var toggleDown = $('.js-hide-header');

    toggleUp.addEventListener('click', function() {
        event.preventDefault();
        removeClass(header, 'active');
    });

    toggleDown.addEventListener('click', function() {
        event.preventDefault();
        addClass(header, 'active');
    });

    this.initHeaderMouseListener();
}

/*-------------------------------------------------------------
**  Initialize the mouse timer on the toggle button for the header
--------------------------------------------------------------*/
KansoWriter.prototype.initHeaderMouseListener = function() {
    var toggleUp = $('.js-show-header');
    var self = this;
    window.addEventListener("mousemove", function() {
        var fromTop = event.clientY;
        if (fromTop < 40) {
            clearTimeout(headerTimer);
            toggleUp.style.opacity = "1";
            headerTimer = setTimeout(function() {
                toggleUp.style.opacity = "0";
            }, 80000);
        }
    });

}

// ##############################################################################
// FILE: Libs/Writer/footer.js
// ##############################################################################

/*-------------------------------------------------------------
**  Initialize the footer
--------------------------------------------------------------*/
KansoWriter.prototype.initFooter = function() {
    var self = this;

    // Mouse Listener
    window.addEventListener("mousemove", self.footerMouseListener);

    // Toggle Views
    this.initToggleButtons();

    // Headings listeners
    $('.writer-footer .js-insert-h1').addEventListener('click', function() {
        self.toggleHeading('#', self);
    });
    $('.writer-footer .js-insert-h2').addEventListener('click', function() {
        self.toggleHeading('##', self);
    });
    $('.writer-footer .js-insert-h3').addEventListener('click', function() {
        self.toggleHeading('###', self);
    });
    $('.writer-footer .js-insert-h4').addEventListener('click', function() {
        self.toggleHeading('####', self);
    });
    $('.writer-footer .js-insert-h5').addEventListener('click', function() {
        self.toggleHeading('#####', self);
    });
    $('.writer-footer .js-insert-h6').addEventListener('click', function() {
        self.toggleHeading('######', self);
    });

    // Lists listeners
    $('.writer-footer .js-insert-list-normal').addEventListener('click', function() {
        self.toggleList(self, true);
    });
    $('.writer-footer .js-insert-list-numbered').addEventListener('click', function() {
        self.toggleList(self, false);
    });

    // Text styles
    $('.writer-footer .js-insert-bold').addEventListener('click', function() {
        self.toggleTextStyle('**', self);
    });
    $('.writer-footer .js-insert-italic').addEventListener('click', function() {
        self.toggleTextStyle('_', self);
    });
    $('.writer-footer .js-insert-strike').addEventListener('click', function() {
        self.toggleTextStyle('~~', self);
    });

    // links and images
    $('.writer-footer .js-insert-link').addEventListener('click', function() {
        self.insertWrapText('[', '](href)', '[text](href)', self);
    });
    $('.writer-footer .js-insert-image').addEventListener('click', function() {
        self.insertWrapText('![', '](src)', '![altText](src)', self);
    });

}

/*-------------------------------------------------------------
**  Initialize toggle buttons for views on footer
--------------------------------------------------------------*/
KansoWriter.prototype.initToggleButtons = function() {
    var self = this;
    toggleTriggers = [writeTrigger, readTrigger, reviewTrigger];
    viewWraps = [writerWrap, readWrap, reviewWrap];
    for (var i = 0; i < toggleTriggers.length; i++) {
        toggleTriggers[i].addEventListener('click', function(e) {
            self.toggleView(e, self);
        });
    }

}

/*-------------------------------------------------------------
**  Event function for footer buttons to toggle view 
--------------------------------------------------------------*/
KansoWriter.prototype.toggleView = function(e, self) {

    var trigger = closest(e.target, 'button');
    var wrap;
    var bodyClass = "";
    if (hasClass(trigger, 'active')) return;

    var scrollToPreview = false;
    var scrollToReview = false;
    var scrolltoWrite = false;

    var acitve = $('.writer-footer .view-toggles button.active');

    // save scroll postions
    if (acitve === readTrigger) readScroll = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
    if (acitve === reviewTrigger) reviewScroll = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
    if (acitve === writeTrigger) writeScroll = self.writer.getScrollInfo()['top'];

    var firstTitle = 'Review';
    var baseTitle = document.title.split("|").pop().trim();

    if (trigger === writeTrigger) {
        scrollToWrite = true;
        wrap = writerWrap;
        bodyClass = "writing markdown";
        firstTitle = 'Write';
        document.getElementsByTagName('html')[0].className = 'writer-html';
        self.windowResize();

    } else if (trigger === readTrigger) {
        scrollToPreview = true;
        wrap = readWrap;
        bodyClass = "writing HTML";
        readWrap.innerHTML = self.markdown.render(self.writer.getValue());
        firstTitle = 'Read';
        document.getElementsByTagName('html')[0].className = 'reading';
    } else {
        scrollToReview = true;
        bodyClass = "writing review";
        wrap = reviewWrap;
        document.getElementsByTagName('html')[0].removeAttribute('class');
    }


    if (self.articleID !== null) {
        var inputTitle = $('.js-article-form input[name=title]').value.trim();
        if (inputTitle === '') {
            baseTitle = 'Untitled';
        } else {
            baseTitle = inputTitle;
        }
    }

    removeClassNodeList(viewWraps, 'active', wrap);
    removeClassNodeList(toggleTriggers, 'active', trigger);
    document.body.className = bodyClass;
    if (scrollToPreview) window.scrollTo(0, readScroll);
    if (scrollToReview) window.scrollTo(0, reviewScroll);
    if (scrolltoWrite) self.writer.scrollTo(null, writeScroll);
    document.title = firstTitle + ' | ' + baseTitle;

}


/*-------------------------------------------------------------
** Show the footer
--------------------------------------------------------------*/
KansoWriter.prototype.showFooter = function() {
    clearTimeout(footerTimer);
    addClass(writerFooter, 'active');
    footerTimer = setTimeout(function() {
        removeClass(writerFooter, 'active');
    }, 80000);
}

/*-------------------------------------------------------------
**  Footer mouse listener
--------------------------------------------------------------*/
KansoWriter.prototype.footerMouseListener = function() {
    var fromBottom = window.innerHeight - event.clientY;
    if (fromBottom < 40) {
        clearTimeout(footerTimer);
        addClass(writerFooter, 'active');
        footerTimer = setTimeout(function() {
            removeClass(writerFooter, 'active');
        }, 80000);
    }
}

/*-------------------------------------------------------------
**  Insert headings
--------------------------------------------------------------*/
KansoWriter.prototype.toggleHeading = function(text, self) {
    var lineNum = self.writer.getCursor().line;
    var lineText = self.writer.getLine(lineNum);
    var lineLength = lineText.length;

    // headings always start with #
    if (lineText !== '' && lineText[0] && lineText[0] === '#') {

        var re = new RegExp('^' + text + '\\s.+' + '$');
        // if The current line is a heading (eg h1) but the clicked button was different (eg h3),
        // we should replace it to the clicked heading (eg h3), rather than removing the 
        // heading alltogether
        if (!re.test(lineText)) {
            lineText = text + ' ' + ltrim(ltrim(lineText, ['#', '##', '###', '####', '#####', '#####']));
        } else {
            lineText = ltrim(ltrim(lineText, ['#', '##', '###', '####', '#####', '#####']));
        }

    } else {
        lineText = text + ' ' + ltrim(lineText);
    }
    self.writer.replaceRange(lineText, {
        line: lineNum,
        ch: 0
    }, {
        line: lineNum,
        ch: lineLength
    });
    self.showFooter();
    self.writer.focus();

}

/*-------------------------------------------------------------
**  Insert Lists
--------------------------------------------------------------*/
KansoWriter.prototype.toggleList = function(self, isUnordered) {

    var lineNum = self.writer.getCursor().line;
    var lineText = self.writer.getLine(lineNum);
    var lineLength = lineText.length;
    var toInsert = '';

    // unordered lists
    if (isUnordered) {

        // List is already present
        if ((lineText !== '') && (lineText[0]) && (lineText[0] === '-' || lineText[0] === '+' || lineText[0] === '*') && (lineText[1]) && (lineText[1] === "" || lineText[1] === " ")) {
            toInsert = ltrim(ltrim(lineText, ['-', '+', '*']));
        } else {
            toInsert = '- ' + ltrim(lineText);
        }

    }

    // ordered list
    else {

        var re = new RegExp('^\\d+\.\\s+');
        // List is already present
        if (re.test(lineText)) {
            toInsert = ltrim(lineText.replace(/\d+\.\s+/, ''));
        } else {
            var num = 1;
            // are we already in a list
            if (lineNum > 0) {
                var lineBefore = self.writer.getLine(lineNum - 1);
                if (re.test(lineBefore)) num = parseInt(lineBefore[0]) + 1;
            }
            toInsert = num + '. ' + ltrim(lineText);
        }

    }

    self.writer.replaceRange(toInsert, {
        line: lineNum,
        ch: 0
    }, {
        line: lineNum,
        ch: lineLength
    });
    self.showFooter();
    self.writer.focus();

}

/*-------------------------------------------------------------
**  Insert Text styles
--------------------------------------------------------------*/
KansoWriter.prototype.toggleTextStyle = function(prefix, self) {

    var cursorPos = self.writer.somethingSelected() ? self.writer.getCursor("to") : self.writer.getCursor();
    var lineText = self.writer.getLine(cursorPos.line);
    var lineLength = lineText.length;
    var styleRgx = new RegExp(preg_quote(prefix) + '(.*?)' + preg_quote(prefix));
    var matchPttrn = preg_match_all(styleRgx, lineText);
    var toInsert = '';

    // if the current line contains the (and or multiple) style patterns
    // we need to figure out the closest pattern
    // to the current cursor position. The end
    // of the cursor pattern is used to ensure the right 
    if (matchPttrn) {
        var matchIndex = [];
        for (var i = 0; i < matchPttrn.length; i++) {
            matchIndex.push(matchPttrn[i].index);
        }
        var closestMatch = closest_number(matchIndex, cursorPos.ch);
        var split = str_split_index(lineText, closestMatch);
        toInsert = split[0] + str_replace(prefix, '', split[1], 2);
    } else {
        if (self.writer.somethingSelected()) {
            toInsert = prefix + self.writer.getSelection() + prefix;
            self.writer.replaceSelection(toInsert, 'start');
            self.showFooter();
            self.writer.focus();
            return;
        } else {
            toInsert = prefix + lineText + prefix;
        }

    }
    self.writer.replaceRange(toInsert, {
        line: cursorPos.line,
        ch: 0
    }, {
        line: cursorPos.line,
        ch: lineLength
    });
    self.showFooter();
    self.writer.focus();
}

/*-------------------------------------------------------------
**  Insert link or image
--------------------------------------------------------------*/
KansoWriter.prototype.insertWrapText = function(prefix, suffix, noSelection, self) {
    var toInsert = '';
    if (self.writer.somethingSelected()) {
        toInsert = prefix + self.writer.getSelection() + suffix;
    } else {
        toInsert = noSelection;
    }

    self.writer.replaceSelection(toInsert, 'start');
    self.showFooter();
    self.writer.focus();
}

// ##############################################################################
// FILE: Libs/Writer/coreMirror.js
// ##############################################################################

/*-------------------------------------------------------------
**  Initialize CodeMirror
--------------------------------------------------------------*/
KansoWriter.prototype.initCodeMirror = function() {

    var self = this;
    this.writer = CodeMirror.fromTextArea(writerTextArea, {
        mode: 'markdown',
        lineWrapping: true,
        lineNumbers: false,
        dragDrop: false,
        theme: "base16-light",
        scrollbarStyle: 'overlay',
        extraKeys: {
            "Enter": "newlineAndIndentContinueMarkdownList"
        }
    });

    CodeMirrorDiv = $('.CodeMirror');

    this.writer.on("change", function() {
        self.updateCodeMirrorLayout(self);
    });

    this.initWriterDZ(this);
    this.initWriterEvents();

}

/*-------------------------------------------------------------
**  Initialize window resize event
--------------------------------------------------------------*/
KansoWriter.prototype.initWindowResize = function() {
    var self = this;
    self.writer.on('viewportChange', self.windowResize);
    window.addEventListener('resize', self.windowResize);
    self.windowResize();
    self.writer.execCommand('goDocStart');
    self.writer.focus();
    self.writer.refresh();
}

KansoWriter.prototype.windowResize = function() {
    writerDiv.style.height = window.innerHeight + "px";
}


/*-------------------------------------------------------------
**  Event function when typing on CodeMirror
--------------------------------------------------------------*/
KansoWriter.prototype.updateCodeMirrorLayout = function(self) {
    self.windowResize();
    clearTimeout(self.saveTimer);
    self.saveTimer = setTimeout(function() {
        addClass($('.js-save-post'), 'active');
    }, 1500);

    clearTimeout(headerTimer);
    $('.js-show-header').style.opacity = "0";

    clearTimeout(footerTimer);
    removeClass(writerFooter, 'active');

}

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

// ##############################################################################
// FILE: Libs/Writer/ajax.js
// ##############################################################################

/*-------------------------------------------------------------
**  Reset the save button when any inputs changed
--------------------------------------------------------------*/
KansoWriter.prototype.initInputChanges = function() {
    var self = this;
    var allInputs = $All('.reviewer .input-default');
    for (var i = 0; i < allInputs.length; i++) {
        allInputs[i].addEventListener('input', function() {
            clearTimeout(self.saveTimer);
            self.saveTimer = setTimeout(function() {
                addClass($('.js-save-post'), 'active');
            }, 1500);

        });
    }

}

/*-------------------------------------------------------------
**  Save the article
--------------------------------------------------------------*/
KansoWriter.prototype.saveArticle = function(e, self) {

    // Clear the timout
    e.preventDefault();
    clearTimeout(self.saveTimer);

    // dont submit when loading
    if (hasClass(publishBtn, 'active')) return;


    // validate the form
    var validator = formValidator(articleInputs);

    // append the ajax request and id
    validator.formAppend('ajaxRequest', self.ajaxType);
    validator.formAppend('id', self.articleID);
    validator.formAppend('content', self.writer.getValue());

    if (GLOBAL_AJAX_ENABLED) {

        Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                var responseObj = isJSON(success);

                if (responseObj && responseObj.details) {
                    self.articleID = responseObj['details']['id'];
                    self.ajaxType = 'writer_save_existing_article';
                    pushNotification('success', 'Your article was successfully saved!');
                    clearTimeout(self.saveTimer);
                    removeClass($('.js-save-post'), 'active');
                    return;
                } else {
                    pushNotification('error', 'The server encoutered an error while saving the article.');
                    return;
                }
            },
            function(error) {
                pushNotification('error', 'The server encoutered an error while saving the article.');
                return;
            });
    } else {
        pushNotification('error', 'The server encoutered an error while saving the article.');
        return;
    }
}

/*-------------------------------------------------------------
**  Publish the article
--------------------------------------------------------------*/
KansoWriter.prototype.publishArticle = function(e, self) {

    // Clear the timout
    e.preventDefault();
    clearTimeout(self.saveTimer);

    // dont submit when loading
    if (hasClass(publishBtn, 'active')) return;


    // validate the form
    var validator = formValidator(articleInputs);

    // append the ajax request and id
    validator.formAppend('ajaxRequest', 'writer_publish_article');
    validator.formAppend('id', self.articleID);
    validator.formAppend('content', self.writer.getValue());

    if (GLOBAL_AJAX_ENABLED) {

        Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                var responseObj = isJSON(success);

                if (responseObj && responseObj.details) {
                    self.articleID = responseObj['details']['id'];
                    self.ajaxType = 'writer_save_existing_article';
                    var slug = GLOBAL_AJAX_URL.replace('/admin', '') + responseObj['details']['slug'];
                    pushNotification('success', 'Your article was successfully published. Click <a href="' + slug + '" target="_blank">here</a> to view live.');
                    clearTimeout(self.saveTimer);
                    removeClass($('.js-save-post'), 'active');
                    return;
                } else {
                    pushNotification('error', 'The server encoutered an error while publishing the article.');
                    return;
                }
            },
            function(error) {
                pushNotification('error', 'The server encoutered an error while publishing the article.');
                return;
            });
    } else {
        pushNotification('error', 'The server encoutered an error while publishing the article.');
        return;
    }
}

// ##############################################################################
// FILE: Libs/Writer/writingEvents.js
// ##############################################################################

/*-------------------------------------------------------------
**  Initialize writer events
--------------------------------------------------------------*/
KansoWriter.prototype.initWriterEvents = function() {
    var self = this;

    // continue lists when enter is pressed
    this.writer.on('keyup', function() {
        if (event.keyCode == 13) {
            self.checkForLists(self)
        }
    });
}

/*-------------------------------------------------------------
**  Continue lists when enter is pressed
--------------------------------------------------------------*/
KansoWriter.prototype.checkForLists = function(self) {
    var prevLine = self.writer.getCursor().line - 1;
    var lineText = self.writer.getLine(prevLine);
    var numListRgx = new RegExp('^\\d+\.\\s+');
    var currLine = prevLine + 1;

    if (lineText === '') return;

    // is this an unordered list
    if ((lineText !== '') && (lineText[0]) && (lineText[0] === '-' || lineText[0] === '+' || lineText[0] === '*') && (lineText[1]) && (lineText[1] === "" || lineText[1] === " ")) {
        toInsert = lineText[0] + ' ';
        self.writer.replaceRange(toInsert, {
            line: currLine,
            ch: 0
        });
    } else if (numListRgx.test(lineText)) {
        num = parseInt(lineText[0]) + 1;
        toInsert = num + '. ';
        self.writer.replaceRange(toInsert, {
            line: currLine,
            ch: 0
        });
    }
}

// ##############################################################################
// FILE: Libs/Writer/end.js
// ##############################################################################

/*-------------------------------------------------------------
**  Initialize the editor
--------------------------------------------------------------*/
var bootWriter = new KansoWriter();

})();
