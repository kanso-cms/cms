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
