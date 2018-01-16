/**
 * Kanso Writer Application
 * 
 */
(function()
{
    /**
     * JS Helper library
     * 
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * Ajax Provider
     * 
     * @var obj
     */
    var Ajax = Modules.require('Ajax');

    /**
     * Markdown library
     * 
     * @var obj
     */
    var Markdown;

    /**
     * Ajax URL to save to
     * 
     * @var string
     */
    var _ajaxURL = window.location.href.replace(/admin(.+)/, 'admin/writer/');

    /**
     * Ajax access token
     * 
     * @var string
     */
    var _accessToken = Helper.$('.js-access-token').value;

    /**
     * Writer textarea element
     * 
     * @var node
     */
    var _writerTextAreaEl = Helper.$('.js-writer-textarea');

    /**
     * Codemirror element
     * 
     * @var node
     */
    var _CodeMirrorEl;

    /**
     * Save trigger
     * 
     * @var node
     */
    var _saveTriggerEl = Helper.$('.js-writer-footer .js-save-post');

    /**
     * Publish trigger
     * 
     * @var node
     */
    var _publishTriggerEl = Helper.$('.js-review-wrap .js-writer-form button[type=submit]');

    /**
     * Writer panel container element
     * 
     * @var node
     */
    var _writerContainerEl = Helper.$('.js-writer-wrap');

    /**
     * Reader panel container element
     * 
     * @var node
     */
    var _readerContainerEl = Helper.$('.js-reader-wrap');

    /**
     * Reviewer container element
     * 
     * @var node
     */
    var _reviewerContainerEl = Helper.$('.js-review-wrap');

    /**
     * Array of view container elements
     * 
     * @var array
     */
    var _viewContainerEls = [_writerContainerEl, _readerContainerEl, _reviewerContainerEl];

    /**
     * Reader scroll Y position (last saved before view change)
     * 
     * @var int
     */
    var _readScrollPos   = 0;

    /**
     * Writer scroll Y position (last saved before view change)
     * 
     * @var int
     */
    var _writeScrollPos  = 0;

    /**
     * Review scroll Y position (last saved before view change)
     * 
     * @var int
     */
    var _reviewScrollPos = 0;

    /**
     * Writer footer container element
     * 
     * @var node
     */
    var _writerFooterEl = Helper.$('.js-writer-footer');

    /**
     * Trigger element to switch to writer view
     * 
     * @var node
     */
    var _writerViewTriggerEl = Helper.$('.js-writer-footer .js-raw');

    /**
     * Trigger element to switch to reader view
     * 
     * @var node
     */
    var _readerViewTriggerEl = Helper.$('.js-writer-footer .js-html');

    /**
     * Trigger element to switch to reviewer view
     * 
     * @var node
     */
    var _reviewViewTriggerEl = Helper.$('.js-writer-footer .js-pre-publish');

    /**
     * Array of all view trigger elements
     * 
     * @var array
     */
    var _changeViewTriggerEls = [_writerViewTriggerEl, _readerViewTriggerEl, _reviewViewTriggerEl];

    /**
     * Sidebar Element
     * 
     * @var node
     */
    var _sidebarEl = Helper.$('.js-sidebar');

    /**
     * Context menu element
     * 
     * @var node
     */
    var _contextMenuEl = Helper.$('.js-writer-context-menu');

    /**
     * Context menu element
     * 
     * @var string
     */
    var _lastClipboardText;

    /**
     * Footer hide/show timer
     * 
     * @var setTimeout
     */
    var _footerTimer;

    /**
     * Sidebar hide/show timer
     * 
     * @var setTimeout
     */
    var _sbTimer;

    /**
     * Autosave timer
     * 
     * @var setTimeout
     */
    var _autoSaveTimer;

    /**
     * Get closest number from an array of numbers
     * 
     * @param  array e Numbers index
     * @param  int   t Target number
     * @return int
     */
    function closest_number(e,t){for(var n=e[0],r=0;r<e.length;r++){var i=e[r];Math.abs(t-i)<Math.abs(t-n)&&(n=i)}return n;}

    /**
     * Remove class from node list with exception
     * 
     * @param  array  e Node list
     * @param  string t Class name
     * @param  node   n Exception to skip
     */
    function removeClassNodeList(e, t, n) {[].forEach.call(e, function(e) { "undefined" == typeof n ? e.classList.remove(t) : e.classList[e == n ? "add" : "remove"](t)});}

    /**
     * Convert string to sentence case
     * 
     * @param  string e String to convert
     * @return string
     */
    function toSentenceCase(e){e="."+e;var r="";if(0==e.length)return r;for(var t=!1,a=[".","?","!"],n=0;n<e.length;n++){var f=e.charAt(n);if(t)if(" "==f)r+=f;else{var o=f.toUpperCase();r+=o,t=!1}else{var s=f.toLowerCase();r+=s}for(var i=0;i<a.length;i++)if(f==a[i]){t=!0;break}}return r=r.substring(1,r.length-1)}

    /**
     * Convert string to title case
     * 
     * @param  string str String to convert
     * @return string
     */
    function toTitleCase(str)
    {
        return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    }

    /**
     * Get the window height
     * 
     * return int
     */
    function windowHeight(){var e=window,n=document,t=n.documentElement,i=n.getElementsByTagName("body")[0];return e.innerHeight||t.clientHeight||i.clientHeight}

/**
 * Writer Application Core
 * 
 * @var object
 */
var KansoWriter = function()
{
    this.version   = "2.0.0";

    this.author    = "Joe Howard";

    this.copyright = "Kanso 2017";

    this._boot();
};

/**
 * Reset the application prototype
 * 
 * @var object
 */
KansoWriter.prototype = {};

/**
 * API object
 * 
 * @var object
 */
KansoWriter.prototype.api =
{
    code_editor  : null,
    has_saved    : false,
    new_article  : true,
    article_id   : null,
    is_published : false,
    is_saving    : false,
    connected    : true,
};

/**
 * Boot the application
 * 
 */
KansoWriter.prototype._boot = function()
{
    // Add document classes to HTML and body
    this._addBodyClasses();

    // Add document classes to HTML and body
    this._getPostId();

    // Bind save article trigger
    this._bindSaveTrigger();

    // Bind publish article trigger
    this._bindPublishTrigger();

    // Bind the hide/show sidebar timer
    this._bindSidebarTimer();

    // Initialize codemirror
    this._initCodeMirror();

    // Initialise auto saver
    this._startAutoSaver();

    // Bind thumbnail chooser
    this._bindThumbnailChooser();

    // Bind window resize
    this._bindWindowResize();

    // Bind input changes
    this._bindInputChanges();

    // Bind post meta triggers
    this._bindPostMetaTriggers();

    // Bind footer buttons
    this._bindFotterTriggers();

    // Bind context menu
    this._bindContextMenu();

    // Init offline js
    this._initOfflineJs();
}

/**
 * Add the initial body/html elements classes 
 * 
 */
KansoWriter.prototype._addBodyClasses = function()
{
    document.getElementsByTagName('html')[0].className = 'writer-html';

    document.body.className = 'writing markdown';
}

/**
 * Get the current post id and save it to the API
 * 
 */
KansoWriter.prototype._getPostId = function()
{
    var postID = _writerTextAreaEl.dataset.id;

    if (postID && postID !== '')
    {
        this.api.article_id = parseInt(postID);

        this.api.new_article = false;
    }
}

/**
 * Bind save/publish triggers
 * 
 */
KansoWriter.prototype._bindSaveTrigger = function()
{
    var _this = this;

    Helper.addEventListener(_saveTriggerEl, 'click', function(e)
    {
        e = e || window.event;

        e.preventDefault();

        _this._saveArticle(true, false);
    });
}

/**
 * Bind save/publish triggers
 * 
 */
KansoWriter.prototype._bindPublishTrigger = function()
{
    var _this = this;

    Helper.addEventListener(_publishTriggerEl, 'click', function(e)
    {
        e = e || window.event;

        e.preventDefault();

        _this._saveArticle(true, true);
    });
}

/**
 * Bind hide/show sidebar
 * 
 */
KansoWriter.prototype._bindSidebarTimer = function()
{
    Helper.addEventListener(window, 'mousemove', this._sidebarVisibilityHandler);

    Helper.addEventListener(window, 'resize', this._hideSidebar);
}

/**
 * Handle sidebar visibility on mousemove
 *
 * @param event e JavaScript mousemove event
 */
KansoWriter.prototype._sidebarVisibilityHandler = function(e)
{
    clearTimeout(_sbTimer);

    e = e || window.event;

    var _this =  Modules.get('KansoWriter');

    var fromSide = event.clientX;

    if (fromSide < 70)
    {
        _this._showSidebar();
    }

    _sbTimer = setTimeout(function()
    {
        _this._hideSidebar();

    }, 3000);
}

/**
 * Hides the sidebar
 *
 */
KansoWriter.prototype._hideSidebar = function()
{
    clearTimeout(_sbTimer);

    _sidebarEl.style.opacity = "0";
}

/**
 * Shows the sidebar
 *
 */
KansoWriter.prototype._showSidebar = function()
{
    clearTimeout(_sbTimer);

    _sidebarEl.style.opacity = '1';
}

/**
 * Initialize codemirror
 *
 */
KansoWriter.prototype._initCodeMirror = function()
{
    var _this = this;

    var content = this._getInitialWriterContent();

    Markdown = window.markdownit({
        html: true,
        xhtmlOut: false,
        breaks: true,
        langPrefix: '',
        linkify: false,
        highlight: _this._highlightCode,
    });

    CodeMirrorSpellChecker(
    {
        codeMirrorInstance: CodeMirror,
    });

    this.api.code_editor = CodeMirror(
        function(editor)
        {
            _writerTextAreaEl.parentNode.replaceChild(editor, _writerTextAreaEl);
        },
        {
            value          : content,
            mode           : 'spell-checker',
            backdrop       : 'markdown',
            lineWrapping   : true,
            lineNumbers    : false,
            dragDrop       : false,
            theme          : 'base16-light',
            scrollbarStyle : 'overlay',
            extraKeys:
            {
                'Enter': 'newlineAndIndentContinueMarkdownList'
            }
        }
    );

    _CodeMirrorEl = Helper.$('.CodeMirror');

    this._bindCodeMirrorEvents();
}

/**
 * Gets the initial writer content from the textarea when
 * application first boots
 *
 * @return string
 */
KansoWriter.prototype._getInitialWriterContent = function()
{
    // Writer content
    var content = _writerTextAreaEl.innerHTML.trim();

    content = Helper.ltrim(content, '<!--[CDATA[');
    content = content.replace(/\]\]\-\-\>$/, '');
    content = content.trim();

    return content;
}

/**
 * Custom highlight codemirror callback for markdown syntax
 *
 * @param  string content codemirror content
 * @param  string syntax  Syntax to highlight
 * @return string
 */
KansoWriter.prototype._highlightCode = function(content, syntax) {

    if (syntax === '')
    {
        return content;
    }
    try
    {
        return hljs.highlight(syntax, content).value;
    }
    catch (e)
    {
        return content;
    }
}

/**
 * Bind codemirror events
 *
 */
KansoWriter.prototype._bindCodeMirrorEvents = function()
{
    var _this = this;

    // update layout when editor changes
    this.api.code_editor.on('change', function()
    {
        _this._onWriterChange();
    });

    // continue lists when enter is pressed
    this.api.code_editor.on('keyup', function()
    {
        if (event.keyCode == 13)
        {
            _this._checkForLists();
        }
    });
}

/**
 * Update layouts when writing
 *
 */
KansoWriter.prototype._onWriterChange = function()
{
    this._windowResizeHandler();

    this._hideSidebar();

    this._hideFooter();

    this._startAutoSaver();
}

/**
 * Bind the footer trigger buttons
 *
 */
KansoWriter.prototype._bindFotterTriggers = function()
{
    var _this = this;

    // Mouse listener
    Helper.addEventListener(window, 'mousemove', this._footerVisibilityHandler);

    // Toggle Views
    this._bindViewTriggers();

    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-h1'), 'click', function()
    {
        _this._toggleHeading('#');
    });
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-h2'), 'click', function()
    {
        _this._toggleHeading('##');
    });
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-h3'), 'click', function()
    {
        _this._toggleHeading('###');
    });
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-h4'), 'click', function()
    {
        _this._toggleHeading('####');
    });
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-h5'), 'click', function()
    {
        _this._toggleHeading('#####');
    });
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-h6'), 'click', function()
    {
        _this._toggleHeading('######');
    });

    // Lists listeners
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-list-normal'), 'click', function()
    {
        _this._toggleList(true);
    });
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-list-numbered'), 'click', function()
    {
        _this._toggleList(false);
    });

    // Table listener
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-table'), 'click', function()
    {
        _this._insertText(_this._tableTemplate());
    });

    // Text styles
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-bold'), 'click', function()
    {
        _this._toggleTextStyle('**');
    });
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-italic'), 'click', function()
    {
        _this._toggleTextStyle('_');
    });
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-strike'), 'click', function()
    {
        _this._toggleTextStyle('~~');
    });

    // links and images
    Helper.addEventListener(Helper.$('.js-writer-footer .js-insert-link'), 'click', function()
    {
        _this._insertWrapText('[', '](href)', '[text](href)');
    });

    Helper.addEventListener(window, 'resize', this._hideFooter);
}

/**
 * Bind change view trigger buttons
 *
 */
KansoWriter.prototype._bindViewTriggers = function()
{
    for (var i = 0; i < _changeViewTriggerEls.length; i++)
    {
        Helper.addEventListener(_changeViewTriggerEls[i], 'click', this._changeViewHandler);
    }
}

/**
 * Change the view when trigger is clicked
 *
 */
KansoWriter.prototype._changeViewHandler = function(e)
{
    e = e || window.event;

    var _this     = Modules.get('KansoWriter');
    var trigger   = Helper.closest(e.target, 'button');
    var wrap;

    if (Helper.hasClass(trigger, 'active'))
    {
        return;
    }

    var scrollToPreview = false;
    var scrollToReview  = false;
    var scrolltoWrite   = false;
    var baseTitle       = document.title.split("|").pop().trim();
    var firstTitle      = 'Review';

    _this._saveCurrScrollPos();

    if (trigger === _writerViewTriggerEl)
    {
        scrollToWrite = true;
        wrap          = _writerContainerEl;
        firstTitle    = 'Write';
        document.getElementsByTagName('html')[0].className = 'writer-html';
        _this._windowResizeHandler();
    } 
    else if (trigger === _readerViewTriggerEl) 
    {
        scrollToPreview = true;
        wrap = _readerContainerEl;
        _readerContainerEl.innerHTML = Markdown.render(_this.api.code_editor.getValue());
        firstTitle = 'Read';
        document.getElementsByTagName('html')[0].className = 'reading-html';
    }
    else
    {
        scrollToReview = true;
        wrap = _reviewerContainerEl;
        document.getElementsByTagName('html')[0].className = 'review-html';
    }

    if (_this.api.article_id !== null)
    {
        var inputTitle = Helper.$('.js-writer-form input[name=title]').value.trim();

        if (inputTitle === '') {
            baseTitle = 'Untitled';
        } 
        else
        {
            baseTitle = inputTitle;
        }
    }

    removeClassNodeList(_viewContainerEls, 'active', wrap);
    removeClassNodeList(_changeViewTriggerEls, 'active', trigger);

    if (scrollToPreview)
    {
        window.scrollTo(0, _readScrollPos);
    }
    if (scrollToReview)
    {
        window.scrollTo(0, _reviewScrollPos);
    }
    if (scrolltoWrite) 
    {
        _this.api.code_editor.scrollTo(null, _writeScrollPos);
    }

    document.title = firstTitle + ' | ' + baseTitle;
}

/**
 * Save the current scroll positions
 *
 */
KansoWriter.prototype._saveCurrScrollPos = function()
{
    var acitve = Helper.$('.js-writer-footer .view-toggles button.active');

    var doc = document.documentElement;

    // save scroll postions
    if (acitve === _readerViewTriggerEl)
    {
        _readScrollPos = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
    }
    if (acitve === _reviewViewTriggerEl)
    {
        _reviewScrollPos = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
    }
    if (acitve === _writerViewTriggerEl)
    {
        _writeScrollPos = this.api.code_editor.getScrollInfo()['top'];
    }
}

/**
 * Footer visibility handler
 *
 * @param event e JavaScript mousemove event
 */
KansoWriter.prototype._footerVisibilityHandler = function(e)
{
    e = e || window.event;

    var fromBottom = window.innerHeight - event.clientY;

    var _this = Modules.get('KansoWriter');

    clearTimeout(_footerTimer);

    if (fromBottom < 40)
    {
        _this._showFooter();
    }

    _footerTimer = setTimeout(function()
    {
        _this._hideFooter();

    }, 3000);
}

/**
 * Show the footer
 *
 */
KansoWriter.prototype._showFooter = function()
{
    clearTimeout(_footerTimer);

    Helper.addClass(_writerFooterEl, 'active');
}

/**
 * Hide the footer
 *
 */
KansoWriter.prototype._hideFooter = function()
{
    clearTimeout(_footerTimer);
    
    Helper.removeClass(_writerFooterEl, 'active');
}

/**
 * Toggle heading in the writer
 *
 * @param string text Text to toggle into heading
 */
KansoWriter.prototype._toggleHeading = function(text)
{
    var lineNum    = this.api.code_editor.getCursor().line;
    var lineText   = this.api.code_editor.getLine(lineNum);
    var lineLength = lineText.length;

    // headings always start with #
    if (lineText !== '' && lineText[0] && lineText[0] === '#')
    {
        var re  = new RegExp('^' + text + '\\s.+' + '$');
        var _re = new RegExp('^' + text + '$');
        var __re  = new RegExp('^' + text + '\\s+' + '$');
        // if The current line is a heading (eg h1) but the clicked button was different (eg h3),
        // we should replace it to the clicked heading (eg h3), rather than removing the 
        // heading alltogether
        if (!re.test(lineText) && !_re.test(lineText) && !__re.test(lineText))
        {
            lineText = text + ' ' + Helper.ltrim(Helper.ltrim(lineText, ['#', '##', '###', '####', '#####', '#####']));
        } 
        else
        {
            lineText = Helper.ltrim(Helper.ltrim(lineText, ['#', '##', '###', '####', '#####', '#####']));
        }

    } 
    else
    {
        lineText = text + ' ' + Helper.ltrim(lineText);
    }
    this.api.code_editor.replaceRange(lineText,
        {
            line: lineNum,
            ch: 0
        },
        {
            line: lineNum,
            ch: lineLength
        }
    );

    this._showFooter();

    this.api.code_editor.focus();
}

/**
 * Toggle list in the writer
 *
 * @param bool isUnordered Is this an unordered list ?
 */
KansoWriter.prototype._toggleList = function(isUnordered)
{
    var lineNum    = this.api.code_editor.getCursor().line;
    var lineText   = this.api.code_editor.getLine(lineNum);
    var lineLength = lineText.length;
    var toInsert   = '';

    // unordered lists
    if (isUnordered)
    {

        // List is already present
        if ((lineText !== '') && (lineText[0]) && (lineText[0] === '-' || lineText[0] === '+' || lineText[0] === '*') && (lineText[1]) && (lineText[1] === "" || lineText[1] === " "))
        {
            toInsert = Helper.ltrim(Helper.ltrim(lineText, ['-', '+', '*']));
        } 
        else
        {
            toInsert = '- ' + Helper.ltrim(lineText);
        }

    }

    // ordered list
    else
    {
        var re = new RegExp('^\\d+\.\\s+');
        // List is already present
        if (re.test(lineText))
        {
            toInsert = Helper.ltrim(lineText.replace(/\d+\.\s+/, ''));
        } 
        else
        {
            var num = 1;
            // are we already in a list
            if (lineNum > 0)
            {
                var lineBefore = this.api.code_editor.getLine(lineNum - 1);
                if (re.test(lineBefore)) num = parseInt(lineBefore[0]) + 1;
            }

            toInsert = num + '. ' + Helper.ltrim(lineText);
        }
    }

    this.api.code_editor.replaceRange(toInsert,
        {
            line: lineNum,
            ch: 0
        },
        {
            line: lineNum,
            ch: lineLength
        }
    );

    this._showFooter();

    this.api.code_editor.focus();
}

/**
 * Toggle text styles in the writer
 *
 * @param string prefix Text prefix/suffix to wrap selection/line
 */
KansoWriter.prototype._toggleTextStyle = function(prefix)
{

    var cursorPos  = this.api.code_editor.somethingSelected() ? this.api.code_editor.getCursor("to") : this.api.code_editor.getCursor();
    var lineText   = this.api.code_editor.getLine(cursorPos.line);
    var lineLength = lineText.length;
    var styleRgx   = new RegExp(Helper.preg_quote(prefix) + '(.*?)' + Helper.preg_quote(prefix));
    var matchPttrn = Helper.preg_match_all(styleRgx, lineText);
    var toInsert   = '';

    // if the current line contains the (and or multiple) style patterns
    // we need to figure out the closest pattern
    // to the current cursor position. The end
    // of the cursor pattern is used to ensure the right 
    if (matchPttrn)
    {
        var matchIndex = [];
        for (var i = 0; i < matchPttrn.length; i++)
        {
            matchIndex.push(matchPttrn[i].index);
        }
        var closestMatch = closest_number(matchIndex, cursorPos.ch);
        var split = Helper.str_split_index(lineText, closestMatch);
        toInsert = split[0] + Helper.str_replace(prefix, '', split[1], 2);
    } 
    else
    {
        if (this.api.code_editor.somethingSelected())
        {
            toInsert = prefix + this.api.code_editor.getSelection() + prefix;
            this.api.code_editor.replaceSelection(toInsert, 'start');
            this._showFooter();
            this.api.code_editor.focus();
            return;
        } 
        else
        {
            toInsert = prefix + lineText + prefix;
        }

    }
    this.api.code_editor.replaceRange(toInsert,
        {
            line: cursorPos.line,
            ch: 0
        },
        {
            line: cursorPos.line,
            ch: lineLength
        }
    );
    
    this._showFooter();
    
    this.api.code_editor.focus();
}

/**
 * Insert text to wrap around selection or line
 *
 * @param string prefix      Prefix string
 * @param string suffix      Suffix string
 * @param string noSelection Fallback if nothing is selected
 */
KansoWriter.prototype._insertWrapText = function(prefix, suffix, noSelection)
{
    var toInsert = '';
    
    if (this.api.code_editor.somethingSelected())
    {
        toInsert = prefix + this.api.code_editor.getSelection() + suffix;
    } 
    else
    {
        toInsert = noSelection;
    }

    this.api.code_editor.replaceSelection(toInsert, 'start');
    
    this._showFooter();
    
    this.api.code_editor.focus();
}

/**
 * Insert text into the writer
 *
 * @param string text Text to insert
 */
KansoWriter.prototype._insertText = function(text)
{
    var doc    = this.api.code_editor;
    var cursor = doc.getCursor();
    var line   = doc.getLine(cursor.line);
    var pos    =
    {
        line : cursor.line,
        ch   : line.length
    };

    doc.replaceRange(text, pos);

    this._showFooter();
    
    doc.focus();
}

/**
 * Bind post meta key/value handler
 *
 */
KansoWriter.prototype._bindPostMetaTriggers = function() {

    var addTrigger  = Helper.$('.js-add-post-meta-btn');
    var rmvTriggers = Helper.$All('.js-rmv-post-meta-btn');

    for (var i = 0; i < rmvTriggers.length; i++)
    {
        Helper.addEventListener(rmvTriggers[i], 'click', this._removePostMetaHandler);
    }

    Helper.addEventListener(addTrigger, 'click', this._addPostMetaHandler);
}

/**
 * Remove post meta key/value handler
 *
 * @param event e JavaScript click event
 */
KansoWriter.prototype._removePostMetaHandler = function(e)
{
    e = e || window.event;

    e.preventDefault();

    Helper.removeFromDOM(Helper.parentUntillClass(this, 'js-meta-row'));
}

/**
 * Add new post meta key/value handler
 *
 * @param event e JavaScript click event
 */
KansoWriter.prototype._addPostMetaHandler = function(e)
{
    e = e || window.event
    
    e.preventDefault();

    var container = Helper.$('.js-post-meta-container');
    var _this     = Modules.get('KansoWriter');
    var row       = document.createElement('DIV');
    row.className = 'row roof-xs js-meta-row';
    row.innerHTML =
    [
        '<div class="form-field floor-xs">',
            '<label>Key</label>',
            '<input type="text" name="post-meta-keys[]" value="" autocomplete="off" size="20">',
        '</div>',
        '&nbsp;&nbsp;&nbsp;',
        '<div class="form-field floor-xs">',
            '<label>Value</label>',
            '<input type="text" name="post-meta-values[]" value="" autocomplete="off" size="60">',
        '</div>',
        '&nbsp;&nbsp;&nbsp;',
        '<button class="btn btn-danger js-rmv-post-meta-btn" type="button">Remove</button>',
        '<div class="row clearfix"></div>',
    ].join('');
        
    container.appendChild(row);

    Helper.addEventListener(Helper.$('.js-rmv-post-meta-btn', row), 'click', _this._removePostMetaHandler);
}

/**
 * Bind window resize functions
 *
 */
KansoWriter.prototype._bindWindowResize = function()
{
    var _this = this;
    
    _this.api.code_editor.on('viewportChange', _this._windowResizeHandler);
    
    Helper.addEventListener(window, 'resize', _this._windowResizeHandler);

    _this._windowResizeHandler();

    _this.api.code_editor.execCommand('goDocStart');

    _this.api.code_editor.focus();

    _this.api.code_editor.refresh();
}

/**
 * Window resize handler
 *
 */
KansoWriter.prototype._windowResizeHandler = function()
{
    _writerContainerEl.style.height = window.innerHeight + "px";
}

/**
 * Bind the thumbnail chooser
 *
 */
KansoWriter.prototype._bindThumbnailChooser = function()
{
    var _this               = this;
    var showMediaLibTrigger = Helper.$('.js-select-img-trigger');
    var removeImgTrigger    = Helper.$('.js-remove-img-trigger');
    var setFeatureTrigger   = Helper.$('.js-set-feature-image');
    
    Helper.addEventListener(showMediaLibTrigger, 'click', function(e)
    {
        e = e || window.event;
        
        e.preventDefault();

        _this._showMediaLibrary();
    });

    Helper.addEventListener(setFeatureTrigger, 'click', function(e)
    {
        e = e || window.event;
        
        e.preventDefault();

        _this._setThumbnailImage();
    });

    Helper.addEventListener(removeImgTrigger, 'click', function(e)
    {
        e = e || window.event;
        
        e.preventDefault();

        _this._clearThumbnailImage();
    });
}

/**
 * Show the media library
 *
 */
KansoWriter.prototype._showMediaLibrary = function(e)
{
    Helper.addClass(Helper.$('.js-media-library'), 'feature-image');
}

/**
 * Set the thumbnail image
 *
 */
KansoWriter.prototype._setThumbnailImage = function()
{
    Modules.get('MediaLibrary')._hideLibrary();
    
    Helper.$('.js-feature-id').value = Helper.$('#media_id').value;
    
    Helper.$('.js-feature-img img').src = Helper.$('#media_url').value;
    
    Helper.addClass(Helper.$('.js-feature-img'), 'active');
}

/**
 * Clear the thumbnail image
 *
 */
KansoWriter.prototype._clearThumbnailImage = function()
{
    Helper.$('.js-feature-id').value = '';

    Helper.removeClass(Helper.$('.js-feature-img'), 'active');

    Helper.$('.js-feature-img img').src = '';
}

/**
 * Bind input change events
 *
 */
KansoWriter.prototype._bindInputChanges = function()
{
    var allInputs = Helper.$All('.js-review-wrap input, .js-review-wrap textarea, .js-review-wrap select');
    
    for (var i = 0; i < allInputs.length; i++)
    {
        Helper.addEventListener(allInputs[i], 'input', this._inputChangeHandler);
    }
}

/**
 * Handle input change events
 *
 * @param event e JavaScript input change event
 */
KansoWriter.prototype._inputChangeHandler = function(e)
{
    var _this = Modules.get('KansoWriter');

    _this._startAutoSaver();
}

/**
 * Handle input change events
 *
 * @param bool showNotification Show a notification after saving
 * @param bool isPublish        Are we publishing the article ?
 */
KansoWriter.prototype._saveArticle = function(showNotification, isPublish)
{
    showNotification = (typeof showNotification === 'undefined' ? false : showNotification);

    isPublish = (typeof isPublish === 'undefined' ? false : isPublish);

    var _this = this;

    this._clearAutoSave();

    if (this.api.is_saving === true || this.api.connected === false)
    {
        return;
    }

    this.api.is_saving = true;

    if (isPublish)
    {
        Helper.addClass(_publishTriggerEl, 'active');
    }
    else
    {
        Helper.addClass(_saveTriggerEl, 'active');
    }

    // validate the form
    var validator = Modules.get('FormValidator', Helper.$('.js-writer-form'));

    validator.append('ajax_request',  this._getAjaxType());
    validator.append('id',            this.api.article_id);
    validator.append('content',       this.api.code_editor.getValue());
    validator.append('access_token',  _accessToken);

    // Validate publish status
    if (!isPublish)
    {
        if (this.api.is_published === true)
        {
            validator.append('status',  'published');
        }
        else if (this._getAjaxType() === 'writer_save_new_article')
        {
            validator.append('status',  'draft');
        }
    }
    else
    {
        this.api.is_published = true;

        validator.append('status',  'published');        
    }

    Ajax.post(_ajaxURL, validator.form(), function(success)
    {
        var responseObj = Helper.isJSON(success);

        if (responseObj && responseObj.response)
        {
            _this._onSaved(responseObj['response']['id'], responseObj['response']['slug'], showNotification, isPublish);
        } 
        else
        {
            Modules.require('Notifications', { type : 'danger', msg : 'The server encountered an error while saving the article.'});
        }

        _this.api.is_saving = false;

        _this._startAutoSaver();
    },
    function(error)
    {
        Modules.require('Notifications', { type : 'danger', msg : 'The server encountered an error while saving the article.'});

        _this.api.is_saving = false;

        _this._startAutoSaver();
    });
    
}

/**
 * Get the ajax type save/publish
 *
 * @return string
 */
KansoWriter.prototype._getAjaxType = function()
{
    if (this.api.new_article === true)
    {
        return 'writer_save_new_article';
    }

    return 'writer_save_existing_article';
}

/**
 * Handle article saved successfully
 *
 * @param int    postId           Post ID after saved
 * @param string postSlug         Post slug after saved
 * @param bool   showNotification Show a notification after saving
 * @param bool   isPublish        Are we publishing the article ?
 */
KansoWriter.prototype._onSaved = function(postId, postSlug, showNotification, isPublish)
{
    if (showNotification && isPublish)
    {
        var slug = location.protocol + "//" + location.host + '/' + postSlug;
        
        Modules.require('Notifications', { type : 'success', msg : 'Your article was successfully published. Click <a href="' + slug + '" target="_blank">here</a> to view live.'});

        this.api.is_published = true;
    }
    else if (showNotification)
    {
        var slug = location.protocol + "//" + location.host + '/' + postSlug;

        Modules.require('Notifications', { type : 'success', msg : 'Your article was successfully saved. Click <a href="' + slug + '" target="_blank">here</a> to view live.'});
    }

    Helper.removeClass(_saveTriggerEl, 'active');

    Helper.removeClass(_publishTriggerEl, 'active');

    this.api.article_id = parseInt(postId);

    this.api.new_article = false;
}

/**
 * Check if a list is present when enter is pressed in code editor
 *
 */
KansoWriter.prototype._checkForLists = function()
{
    var prevLine   = this.api.code_editor.getCursor().line - 1;
    var lineText   = this.api.code_editor.getLine(prevLine);
    var numListRgx = new RegExp('^\\d+\.\\s+');
    var currLine   = prevLine + 1;

    if (lineText === '')
    {
        return;
    }

    // is this an unordered list
    if ((lineText !== '') && (lineText[0]) && (lineText[0] === '-' || lineText[0] === '+' || lineText[0] === '*') && (lineText[1]) && (lineText[1] === "" || lineText[1] === " "))
    {
        toInsert = lineText[0] + ' ';
        this.api.code_editor.replaceRange(toInsert,
            {
                line: currLine,
                ch: 0
            }
        );
    }
    else if (numListRgx.test(lineText))
    {
        num      = parseInt(lineText[0]) + 1;
        toInsert = num + '. ';
        this.api.code_editor.replaceRange(toInsert,
            {
            line: currLine,
            ch: 0
            }
        );
    }
}

/**
 * Start the autosave timer
 *
 */
KansoWriter.prototype._startAutoSaver = function()
{
    var _this = this;

    this._clearAutoSave();

    _autoSaveTimer = setTimeout(function()
    {
        if (_this.api.code_editor.getValue().trim() === '')
        {
            return;
        }

        _this._saveArticle(false);

    }, 5000);
}

/**
 * Clear the autosave timer
 *
 */
KansoWriter.prototype._clearAutoSave = function()
{
    clearTimeout(_autoSaveTimer);
}

/**
 * Default table template to insert into writer
 *
 * @return string
 */
KansoWriter.prototype._tableTemplate = function()
{
     return [
        '\n| Tables        | Are           | Cool  |\n',
        '| ------------- |:-------------:| -----:|\n',
        '| col 3 is      | right-aligned | $1600 |\n',
        '| col 2 is      | centered      |   $12 |\n',
        '| zebra stripes | are neat      |    $1 |\n',].join('');
}

/**
 * Bind context menu click
 *
 */
KansoWriter.prototype._bindContextMenu = function()
{
    var _this  = this;
    var editor = this.api.code_editor;
    var cmds   = CodeMirror.commands;

    // Google search
    Helper.addEventListener(Helper.$('.js-g-search'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            var win = window.open('https://www.google.com.au/search?q='+encodeURIComponent(editor.getSelection().trim()), '_blank');
            
            win.focus();
        }
    });

    // Dictionary search
    Helper.addEventListener(Helper.$('.js-open-dictionary'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            var win = window.open('https://www.google.com.au/search?q=define:'+encodeURIComponent(editor.getSelection().trim()), '_blank');
           
            win.focus();
        }
    });

    // Thesaurus search
    Helper.addEventListener(Helper.$('.js-open-thesaurus'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            var win = window.open('https://en.oxforddictionaries.com/thesaurus/'+encodeURIComponent(editor.getSelection().trim()), '_blank');
            win.focus();
        }
    });

    // Wikipedia search
    Helper.addEventListener(Helper.$('.js-open-wiki'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            var win = window.open('https://en.wikipedia.org/wiki/'+encodeURIComponent(editor.getSelection().trim()), '_blank');
            win.focus();
        }
    });

    // Cut
    Helper.addEventListener(Helper.$('.js-cut'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            var text = editor.getSelection().trim();

            clipboard.writeText(text);

            _lastClipboardText = text;

            editor.replaceSelection('', 'start');
            
            editor.focus();
        }
    });

    // Copy
    Helper.addEventListener(Helper.$('.js-copy'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            _lastClipboardText = editor.getSelection().trim();

            clipboard.writeText(_lastClipboardText);
        }
    });

    // Paste
    Helper.addEventListener(Helper.$('.js-paste'), 'click', function()
    {
        clipboard.readText().then(function(text)
        {
            if (editor.somethingSelected())
            {
                editor.replaceSelection(text, 'start');
            } 
            else
            {
                pos = editor.getCursor();
                editor.setSelection(pos, pos);
                editor.replaceSelection(text, 'start');
            }

            editor.focus();
        },
        function()
        {
            if (_lastClipboardText)
            {
                if (editor.somethingSelected())
                {
                    editor.replaceSelection(_lastClipboardText, 'start');
                } 
                else
                {
                    pos = editor.getCursor();
                    editor.setSelection(pos, pos);
                    editor.replaceSelection(_lastClipboardText, 'start');
                }
            }

            editor.focus();
        });
    });

    // Select all
    Helper.addEventListener(Helper.$('.js-select-all'), 'click', function()
    {
        cmds.selectAll(editor);
    });

    // Select word
    Helper.addEventListener(Helper.$('.js-select-word'), 'click', function()
    {
        cmds.selectNextOccurrence(editor);
    });

    // Convert title case
    Helper.addEventListener(Helper.$('.js-titlecase-trigger'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            var str = editor.getSelection();

            editor.replaceSelection(toTitleCase(editor.getSelection()), 'start');
            
            editor.focus();
        }

    });

    // Convert to uppercase
    Helper.addEventListener(Helper.$('.js-uppercase-trigger'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            editor.replaceSelection(editor.getSelection().toUpperCase(), 'start');
            editor.focus();
        }
    });

    // Convert to lowercase
    Helper.addEventListener(Helper.$('.js-lowercase-trigger'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            editor.replaceSelection(editor.getSelection().toLowerCase(), 'start');
            
            editor.focus();
        }
    });

    // Convert to sentence case
    Helper.addEventListener(Helper.$('.js-sentence-trigger'), 'click', function()
    {
        if (editor.somethingSelected())
        {
            editor.replaceSelection(toSentenceCase(editor.getSelection()), 'start');
            
            editor.focus();
        }
    });

    // Spelling suggestions
    Helper.addEventListener(Helper.$('.js-suggestions div'), 'click', function(e)
    {
        var suggestion = e.target;

        if (Helper.hasClass(suggestion, 'js-suggestion'))
        {
            editor.replaceSelection(suggestion.innerHTML.trim(), 'start');

            editor.focus();
        }
    });

    // Hide/show context menu
    Helper.addEventListener(_writerContainerEl, 'contextmenu', this._handleContextMenuVisibility);

    // English UK dictionary
    $Spelling.DefaultDictionary = "English (UK)";
}

/**
 * Handle context menu click
 *
 * @param event e JavaScript contextmenu click on writer
 */
KansoWriter.prototype._handleContextMenuVisibility = function(e)
{
    e = e || window.event;

    e.preventDefault();

    var _this = Modules.get('KansoWriter');

    _this._styleContextMenu(e);

    Helper.addClass(_contextMenuEl, 'active');
    
    Helper.addClass(document.body, 'context-menu-active');

    window.addEventListener('click', _this._hideContextMenu);

    if (_this.api.code_editor.somethingSelected())
    {
        _this._contextMenuSuggestions();
    }
}

/**
 * Position the context menu
 *
 * @param event e JavaScript contextmenu click on writer
 */
KansoWriter.prototype._styleContextMenu = function(e)
{   
    if (this.api.code_editor.somethingSelected())
    {
        Helper.addClass(_contextMenuEl, 'something-selected');
    }
    else
    {
        Helper.removeClass(_contextMenuEl, 'something-selected');
    }

    if (windowHeight() - e.clientY < 377)
    {
        _contextMenuEl.style.top = (e.clientY - 377) + 'px';
    }
    else
    {
        _contextMenuEl.style.top  = e.clientY + 'px';
    }

    if (windowHeight() - e.clientY < 440)
    {
        Helper.$('.js-suggestions > div').style.top = 'initial';
        Helper.$('.js-suggestions > div').style.bottom = '0';

        Helper.$('.js-context-menu-casings > div').style.top = 'initial';
        Helper.$('.js-context-menu-casings > div').style.bottom = '0';
    }
    else
    {
        Helper.$('.js-suggestions > div').style.top    = '0';
        Helper.$('.js-suggestions > div').style.bottom = 'initial';

        Helper.$('.js-context-menu-casings > div').style.top    = '0';
        Helper.$('.js-context-menu-casings > div').style.bottom = 'initial';
    }

    _contextMenuEl.style.left = e.clientX + 'px';
}


/**
 * Hide the context menu
 *
 */
KansoWriter.prototype._hideContextMenu = function(e)
{
    Helper.removeClass(_contextMenuEl, 'active');
    Helper.removeClass(document.body, 'context-menu-active');
    window.removeEventListener('click', Modules.get('KansoWriter')._hideContextMenu);
}

/**
 * Handle context menu suggestions
 *
 */
KansoWriter.prototype._contextMenuSuggestions = function()
{
    var list       = Helper.$('.js-suggestions ul');
    var selection  = this.api.code_editor.getSelection().trim();
    list.innerHTML = '';

    if (Helper.count(selection.split(" ")) > 1)
    {
        list.innerHTML = '<li style="opacity:0.3">No Suggestions</li>';
        
        return;
    }

    if (selection !== '')
    {
        var suggestions = $Spelling.SpellCheckSuggest(selection);

        if (Helper.empty(suggestions))
        {
            list.innerHTML = '<li style="opacity:0.3">No Suggestions</li>';
            return;
        }

        if (!Helper.isset(suggestions[1]))
        {
            suggestions = suggestions[0];
        }

        for (var i = 0; i < suggestions.length; i++)
        {
            list.innerHTML += '<li class="js-suggestion">'+suggestions[i]+'</li>';
        }
    }
}

/**
 * Initialize Offline.js
 *
 */
KansoWriter.prototype._initOfflineJs = function()
{   
    var _this = this;
    var offlineOverlay = Helper.$('.js-offline-overlay');

    Offline.options =
    {
        checkOnLoad: false,
        interceptRequests: false,
        checks: {
            xhr: {
                url:  window.location.href.replace(/admin(.+)/, 'kanso/cms/admin/assets/js/check-connection.js')
            }
        },
        requests: false,
        game: false
    };

    Offline.on("down", disconnected);

    function disconnected()
    {
        Helper.addClass(offlineOverlay, 'active');
        Offline.on("up", connected);
        _this.api.connected = false;
        _this._clearAutoSave();
    }

    function connected()
    {
        Helper.removeClass(offlineOverlay, 'active');
        Offline.off("up", connected);
        _this.api.connected = true;
        _this._startAutoSaver();
    }
};

/**
 * Instantiate and boot
 *
 */
Modules.singleton('KansoWriter', KansoWriter).get('KansoWriter')

})();
