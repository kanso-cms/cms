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
