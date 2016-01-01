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
