// ##############################################################################
// FILE: Vendor/CodeMirror/codemirror.js
// ##############################################################################

// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

// This is CodeMirror (http://codemirror.net), a code editor
// implemented in JavaScript on top of the browser's DOM.
//
// You can find some technical background for some of the code below
// at http://marijnhaverbeke.nl/blog/#cm-internals .

(function(mod) {
    if (typeof exports == "object" && typeof module == "object") // CommonJS
        module.exports = mod();
    else if (typeof define == "function" && define.amd) // AMD
        return define([], mod);
    else // Plain browser env
        this.CodeMirror = mod();
})(function() {
    "use strict";

    // BROWSER SNIFFING

    // Kludges for bugs and behavior differences that can't be feature
    // detected are enabled based on userAgent etc sniffing.

    var gecko = /gecko\/\d/i.test(navigator.userAgent);
    var ie_upto10 = /MSIE \d/.test(navigator.userAgent);
    var ie_11up = /Trident\/(?:[7-9]|\d{2,})\..*rv:(\d+)/.exec(navigator.userAgent);
    var ie = ie_upto10 || ie_11up;
    var ie_version = ie && (ie_upto10 ? document.documentMode || 6 : ie_11up[1]);
    var webkit = /WebKit\//.test(navigator.userAgent);
    var qtwebkit = webkit && /Qt\/\d+\.\d+/.test(navigator.userAgent);
    var chrome = /Chrome\//.test(navigator.userAgent);
    var presto = /Opera\//.test(navigator.userAgent);
    var safari = /Apple Computer/.test(navigator.vendor);
    var mac_geMountainLion = /Mac OS X 1\d\D([8-9]|\d\d)\D/.test(navigator.userAgent);
    var phantom = /PhantomJS/.test(navigator.userAgent);

    var ios = /AppleWebKit/.test(navigator.userAgent) && /Mobile\/\w+/.test(navigator.userAgent);
    // This is woefully incomplete. Suggestions for alternative methods welcome.
    var mobile = ios || /Android|webOS|BlackBerry|Opera Mini|Opera Mobi|IEMobile/i.test(navigator.userAgent);
    var mac = ios || /Mac/.test(navigator.platform);
    var windows = /win/i.test(navigator.platform);

    var presto_version = presto && navigator.userAgent.match(/Version\/(\d*\.\d*)/);
    if (presto_version) presto_version = Number(presto_version[1]);
    if (presto_version && presto_version >= 15) {
        presto = false;
        webkit = true;
    }
    // Some browsers use the wrong event properties to signal cmd/ctrl on OS X
    var flipCtrlCmd = mac && (qtwebkit || presto && (presto_version == null || presto_version < 12.11));
    var captureRightClick = gecko || (ie && ie_version >= 9);

    // Optimize some code when these features are not used.
    var sawReadOnlySpans = false,
        sawCollapsedSpans = false;

    // EDITOR CONSTRUCTOR

    // A CodeMirror instance represents an editor. This is the object
    // that user code is usually dealing with.

    function CodeMirror(place, options) {
        if (!(this instanceof CodeMirror)) return new CodeMirror(place, options);

        this.options = options = options ? copyObj(options) : {};
        // Determine effective options based on given values and defaults.
        copyObj(defaults, options, false);
        setGuttersForLineNumbers(options);

        var doc = options.value;
        if (typeof doc == "string") doc = new Doc(doc, options.mode);
        this.doc = doc;

        var input = new CodeMirror.inputStyles[options.inputStyle](this);
        var display = this.display = new Display(place, doc, input);
        display.wrapper.CodeMirror = this;
        updateGutters(this);
        themeChanged(this);
        if (options.lineWrapping)
            this.display.wrapper.className += " CodeMirror-wrap";
        if (options.autofocus && !mobile) display.input.focus();
        initScrollbars(this);

        this.state = {
            keyMaps: [], // stores maps added by addKeyMap
            overlays: [], // highlighting overlays, as added by addOverlay
            modeGen: 0, // bumped when mode/overlay changes, used to invalidate highlighting info
            overwrite: false,
            delayingBlurEvent: false,
            focused: false,
            suppressEdits: false, // used to disable editing during key handlers when in readOnly mode
            pasteIncoming: false,
            cutIncoming: false, // help recognize paste/cut edits in input.poll
            draggingText: false,
            highlight: new Delayed(), // stores highlight worker timeout
            keySeq: null, // Unfinished key sequence
            specialChars: null
        };

        var cm = this;

        // Override magic textarea content restore that IE sometimes does
        // on our hidden textarea on reload
        if (ie && ie_version < 11) setTimeout(function() {
            cm.display.input.reset(true);
        }, 20);

        registerEventHandlers(this);
        ensureGlobalHandlers();

        startOperation(this);
        this.curOp.forceUpdate = true;
        attachDoc(this, doc);

        if ((options.autofocus && !mobile) || cm.hasFocus())
            setTimeout(bind(onFocus, this), 20);
        else
            onBlur(this);

        for (var opt in optionHandlers)
            if (optionHandlers.hasOwnProperty(opt))
                optionHandlers[opt](this, options[opt], Init);
        maybeUpdateLineNumberWidth(this);
        if (options.finishInit) options.finishInit(this);
        for (var i = 0; i < initHooks.length; ++i) initHooks[i](this);
        endOperation(this);
        // Suppress optimizelegibility in Webkit, since it breaks text
        // measuring on line wrapping boundaries.
        if (webkit && options.lineWrapping &&
            getComputedStyle(display.lineDiv).textRendering == "optimizelegibility")
            display.lineDiv.style.textRendering = "auto";
    }

    // DISPLAY CONSTRUCTOR

    // The display handles the DOM integration, both for input reading
    // and content drawing. It holds references to DOM nodes and
    // display-related state.

    function Display(place, doc, input) {
        var d = this;
        this.input = input;

        // Covers bottom-right square when both scrollbars are present.
        d.scrollbarFiller = elt("div", null, "CodeMirror-scrollbar-filler");
        d.scrollbarFiller.setAttribute("cm-not-content", "true");
        // Covers bottom of gutter when coverGutterNextToScrollbar is on
        // and h scrollbar is present.
        d.gutterFiller = elt("div", null, "CodeMirror-gutter-filler");
        d.gutterFiller.setAttribute("cm-not-content", "true");
        // Will contain the actual code, positioned to cover the viewport.
        d.lineDiv = elt("div", null, "CodeMirror-code");
        // Elements are added to these to represent selection and cursors.
        d.selectionDiv = elt("div", null, null, "position: relative; z-index: 1");
        d.cursorDiv = elt("div", null, "CodeMirror-cursors");
        // A visibility: hidden element used to find the size of things.
        d.measure = elt("div", null, "CodeMirror-measure");
        // When lines outside of the viewport are measured, they are drawn in this.
        d.lineMeasure = elt("div", null, "CodeMirror-measure");
        // Wraps everything that needs to exist inside the vertically-padded coordinate system
        d.lineSpace = elt("div", [d.measure, d.lineMeasure, d.selectionDiv, d.cursorDiv, d.lineDiv],
            null, "position: relative; outline: none");
        // Moved around its parent to cover visible view.
        d.mover = elt("div", [elt("div", [d.lineSpace], "CodeMirror-lines")], null, "position: relative");
        // Set to the height of the document, allowing scrolling.
        d.sizer = elt("div", [d.mover], "CodeMirror-sizer");
        d.sizerWidth = null;
        // Behavior of elts with overflow: auto and padding is
        // inconsistent across browsers. This is used to ensure the
        // scrollable area is big enough.
        d.heightForcer = elt("div", null, null, "position: absolute; height: " + scrollerGap + "px; width: 1px;");
        // Will contain the gutters, if any.
        d.gutters = elt("div", null, "CodeMirror-gutters");
        d.lineGutter = null;
        // Actual scrollable element.
        d.scroller = elt("div", [d.sizer, d.heightForcer, d.gutters], "CodeMirror-scroll");
        d.scroller.setAttribute("tabIndex", "-1");
        // The element in which the editor lives.
        d.wrapper = elt("div", [d.scrollbarFiller, d.gutterFiller, d.scroller], "CodeMirror");

        // Work around IE7 z-index bug (not perfect, hence IE7 not really being supported)
        if (ie && ie_version < 8) {
            d.gutters.style.zIndex = -1;
            d.scroller.style.paddingRight = 0;
        }
        if (!webkit && !(gecko && mobile)) d.scroller.draggable = true;

        if (place) {
            if (place.appendChild) place.appendChild(d.wrapper);
            else place(d.wrapper);
        }

        // Current rendered range (may be bigger than the view window).
        d.viewFrom = d.viewTo = doc.first;
        d.reportedViewFrom = d.reportedViewTo = doc.first;
        // Information about the rendered lines.
        d.view = [];
        d.renderedView = null;
        // Holds info about a single rendered line when it was rendered
        // for measurement, while not in view.
        d.externalMeasured = null;
        // Empty space (in pixels) above the view
        d.viewOffset = 0;
        d.lastWrapHeight = d.lastWrapWidth = 0;
        d.updateLineNumbers = null;

        d.nativeBarWidth = d.barHeight = d.barWidth = 0;
        d.scrollbarsClipped = false;

        // Used to only resize the line number gutter when necessary (when
        // the amount of lines crosses a boundary that makes its width change)
        d.lineNumWidth = d.lineNumInnerWidth = d.lineNumChars = null;
        // Set to true when a non-horizontal-scrolling line widget is
        // added. As an optimization, line widget aligning is skipped when
        // this is false.
        d.alignWidgets = false;

        d.cachedCharWidth = d.cachedTextHeight = d.cachedPaddingH = null;

        // Tracks the maximum line length so that the horizontal scrollbar
        // can be kept static when scrolling.
        d.maxLine = null;
        d.maxLineLength = 0;
        d.maxLineChanged = false;

        // Used for measuring wheel scrolling granularity
        d.wheelDX = d.wheelDY = d.wheelStartX = d.wheelStartY = null;

        // True when shift is held down.
        d.shift = false;

        // Used to track whether anything happened since the context menu
        // was opened.
        d.selForContextMenu = null;

        d.activeTouch = null;

        input.init(d);
    }

    // STATE UPDATES

    // Used to get the editor into a consistent state again when options change.

    function loadMode(cm) {
        cm.doc.mode = CodeMirror.getMode(cm.options, cm.doc.modeOption);
        resetModeState(cm);
    }

    function resetModeState(cm) {
        cm.doc.iter(function(line) {
            if (line.stateAfter) line.stateAfter = null;
            if (line.styles) line.styles = null;
        });
        cm.doc.frontier = cm.doc.first;
        startWorker(cm, 100);
        cm.state.modeGen++;
        if (cm.curOp) regChange(cm);
    }

    function wrappingChanged(cm) {
        if (cm.options.lineWrapping) {
            addClass(cm.display.wrapper, "CodeMirror-wrap");
            cm.display.sizer.style.minWidth = "";
            cm.display.sizerWidth = null;
        } else {
            rmClass(cm.display.wrapper, "CodeMirror-wrap");
            findMaxLine(cm);
        }
        estimateLineHeights(cm);
        regChange(cm);
        clearCaches(cm);
        setTimeout(function() {
            updateScrollbars(cm);
        }, 100);
    }

    // Returns a function that estimates the height of a line, to use as
    // first approximation until the line becomes visible (and is thus
    // properly measurable).
    function estimateHeight(cm) {
        var th = textHeight(cm.display),
            wrapping = cm.options.lineWrapping;
        var perLine = wrapping && Math.max(5, cm.display.scroller.clientWidth / charWidth(cm.display) - 3);
        return function(line) {
            if (lineIsHidden(cm.doc, line)) return 0;

            var widgetsHeight = 0;
            if (line.widgets)
                for (var i = 0; i < line.widgets.length; i++) {
                    if (line.widgets[i].height) widgetsHeight += line.widgets[i].height;
                }

            if (wrapping)
                return widgetsHeight + (Math.ceil(line.text.length / perLine) || 1) * th;
            else
                return widgetsHeight + th;
        };
    }

    function estimateLineHeights(cm) {
        var doc = cm.doc,
            est = estimateHeight(cm);
        doc.iter(function(line) {
            var estHeight = est(line);
            if (estHeight != line.height) updateLineHeight(line, estHeight);
        });
    }

    function themeChanged(cm) {
        cm.display.wrapper.className = cm.display.wrapper.className.replace(/\s*cm-s-\S+/g, "") +
            cm.options.theme.replace(/(^|\s)\s*/g, " cm-s-");
        clearCaches(cm);
    }

    function guttersChanged(cm) {
        updateGutters(cm);
        regChange(cm);
        setTimeout(function() {
            alignHorizontally(cm);
        }, 20);
    }

    // Rebuild the gutter elements, ensure the margin to the left of the
    // code matches their width.
    function updateGutters(cm) {
        var gutters = cm.display.gutters,
            specs = cm.options.gutters;
        removeChildren(gutters);
        for (var i = 0; i < specs.length; ++i) {
            var gutterClass = specs[i];
            var gElt = gutters.appendChild(elt("div", null, "CodeMirror-gutter " + gutterClass));
            if (gutterClass == "CodeMirror-linenumbers") {
                cm.display.lineGutter = gElt;
                gElt.style.width = (cm.display.lineNumWidth || 1) + "px";
            }
        }
        gutters.style.display = i ? "" : "none";
        updateGutterSpace(cm);
    }

    function updateGutterSpace(cm) {
        var width = cm.display.gutters.offsetWidth;
        cm.display.sizer.style.marginLeft = width + "px";
    }

    // Compute the character length of a line, taking into account
    // collapsed ranges (see markText) that might hide parts, and join
    // other lines onto it.
    function lineLength(line) {
        if (line.height == 0) return 0;
        var len = line.text.length,
            merged, cur = line;
        while (merged = collapsedSpanAtStart(cur)) {
            var found = merged.find(0, true);
            cur = found.from.line;
            len += found.from.ch - found.to.ch;
        }
        cur = line;
        while (merged = collapsedSpanAtEnd(cur)) {
            var found = merged.find(0, true);
            len -= cur.text.length - found.from.ch;
            cur = found.to.line;
            len += cur.text.length - found.to.ch;
        }
        return len;
    }

    // Find the longest line in the document.
    function findMaxLine(cm) {
        var d = cm.display,
            doc = cm.doc;
        d.maxLine = getLine(doc, doc.first);
        d.maxLineLength = lineLength(d.maxLine);
        d.maxLineChanged = true;
        doc.iter(function(line) {
            var len = lineLength(line);
            if (len > d.maxLineLength) {
                d.maxLineLength = len;
                d.maxLine = line;
            }
        });
    }

    // Make sure the gutters options contains the element
    // "CodeMirror-linenumbers" when the lineNumbers option is true.
    function setGuttersForLineNumbers(options) {
        var found = indexOf(options.gutters, "CodeMirror-linenumbers");
        if (found == -1 && options.lineNumbers) {
            options.gutters = options.gutters.concat(["CodeMirror-linenumbers"]);
        } else if (found > -1 && !options.lineNumbers) {
            options.gutters = options.gutters.slice(0);
            options.gutters.splice(found, 1);
        }
    }

    // SCROLLBARS

    // Prepare DOM reads needed to update the scrollbars. Done in one
    // shot to minimize update/measure roundtrips.
    function measureForScrollbars(cm) {
        var d = cm.display,
            gutterW = d.gutters.offsetWidth;
        var docH = Math.round(cm.doc.height + paddingVert(cm.display));
        return {
            clientHeight: d.scroller.clientHeight,
            viewHeight: d.wrapper.clientHeight,
            scrollWidth: d.scroller.scrollWidth,
            clientWidth: d.scroller.clientWidth,
            viewWidth: d.wrapper.clientWidth,
            barLeft: cm.options.fixedGutter ? gutterW : 0,
            docHeight: docH,
            scrollHeight: docH + scrollGap(cm) + d.barHeight,
            nativeBarWidth: d.nativeBarWidth,
            gutterWidth: gutterW
        };
    }

    function NativeScrollbars(place, scroll, cm) {
        this.cm = cm;
        var vert = this.vert = elt("div", [elt("div", null, null, "min-width: 1px")], "CodeMirror-vscrollbar");
        var horiz = this.horiz = elt("div", [elt("div", null, null, "height: 100%; min-height: 1px")], "CodeMirror-hscrollbar");
        place(vert);
        place(horiz);

        on(vert, "scroll", function() {
            if (vert.clientHeight) scroll(vert.scrollTop, "vertical");
        });
        on(horiz, "scroll", function() {
            if (horiz.clientWidth) scroll(horiz.scrollLeft, "horizontal");
        });

        this.checkedOverlay = false;
        // Need to set a minimum width to see the scrollbar on IE7 (but must not set it on IE8).
        if (ie && ie_version < 8) this.horiz.style.minHeight = this.vert.style.minWidth = "18px";
    }

    NativeScrollbars.prototype = copyObj({
        update: function(measure) {
            var needsH = measure.scrollWidth > measure.clientWidth + 1;
            var needsV = measure.scrollHeight > measure.clientHeight + 1;
            var sWidth = measure.nativeBarWidth;

            if (needsV) {
                this.vert.style.display = "block";
                this.vert.style.bottom = needsH ? sWidth + "px" : "0";
                var totalHeight = measure.viewHeight - (needsH ? sWidth : 0);
                // A bug in IE8 can cause this value to be negative, so guard it.
                this.vert.firstChild.style.height =
                    Math.max(0, measure.scrollHeight - measure.clientHeight + totalHeight) + "px";
            } else {
                this.vert.style.display = "";
                this.vert.firstChild.style.height = "0";
            }

            if (needsH) {
                this.horiz.style.display = "block";
                this.horiz.style.right = needsV ? sWidth + "px" : "0";
                this.horiz.style.left = measure.barLeft + "px";
                var totalWidth = measure.viewWidth - measure.barLeft - (needsV ? sWidth : 0);
                this.horiz.firstChild.style.width =
                    (measure.scrollWidth - measure.clientWidth + totalWidth) + "px";
            } else {
                this.horiz.style.display = "";
                this.horiz.firstChild.style.width = "0";
            }

            if (!this.checkedOverlay && measure.clientHeight > 0) {
                if (sWidth == 0) this.overlayHack();
                this.checkedOverlay = true;
            }

            return {
                right: needsV ? sWidth : 0,
                bottom: needsH ? sWidth : 0
            };
        },
        setScrollLeft: function(pos) {
            if (this.horiz.scrollLeft != pos) this.horiz.scrollLeft = pos;
        },
        setScrollTop: function(pos) {
            if (this.vert.scrollTop != pos) this.vert.scrollTop = pos;
        },
        overlayHack: function() {
            var w = mac && !mac_geMountainLion ? "12px" : "18px";
            this.horiz.style.minHeight = this.vert.style.minWidth = w;
            var self = this;
            var barMouseDown = function(e) {
                if (e_target(e) != self.vert && e_target(e) != self.horiz)
                    operation(self.cm, onMouseDown)(e);
            };
            on(this.vert, "mousedown", barMouseDown);
            on(this.horiz, "mousedown", barMouseDown);
        },
        clear: function() {
            var parent = this.horiz.parentNode;
            parent.removeChild(this.horiz);
            parent.removeChild(this.vert);
        }
    }, NativeScrollbars.prototype);

    function NullScrollbars() {}

    NullScrollbars.prototype = copyObj({
        update: function() {
            return {
                bottom: 0,
                right: 0
            };
        },
        setScrollLeft: function() {},
        setScrollTop: function() {},
        clear: function() {}
    }, NullScrollbars.prototype);

    CodeMirror.scrollbarModel = {
        "native": NativeScrollbars,
        "null": NullScrollbars
    };

    function initScrollbars(cm) {
        if (cm.display.scrollbars) {
            cm.display.scrollbars.clear();
            if (cm.display.scrollbars.addClass)
                rmClass(cm.display.wrapper, cm.display.scrollbars.addClass);
        }

        cm.display.scrollbars = new CodeMirror.scrollbarModel[cm.options.scrollbarStyle](function(node) {
            cm.display.wrapper.insertBefore(node, cm.display.scrollbarFiller);
            // Prevent clicks in the scrollbars from killing focus
            on(node, "mousedown", function() {
                if (cm.state.focused) setTimeout(function() {
                    cm.display.input.focus();
                }, 0);
            });
            node.setAttribute("cm-not-content", "true");
        }, function(pos, axis) {
            if (axis == "horizontal") setScrollLeft(cm, pos);
            else setScrollTop(cm, pos);
        }, cm);
        if (cm.display.scrollbars.addClass)
            addClass(cm.display.wrapper, cm.display.scrollbars.addClass);
    }

    function updateScrollbars(cm, measure) {
        if (!measure) measure = measureForScrollbars(cm);
        var startWidth = cm.display.barWidth,
            startHeight = cm.display.barHeight;
        updateScrollbarsInner(cm, measure);
        for (var i = 0; i < 4 && startWidth != cm.display.barWidth || startHeight != cm.display.barHeight; i++) {
            if (startWidth != cm.display.barWidth && cm.options.lineWrapping)
                updateHeightsInViewport(cm);
            updateScrollbarsInner(cm, measureForScrollbars(cm));
            startWidth = cm.display.barWidth;
            startHeight = cm.display.barHeight;
        }
    }

    // Re-synchronize the fake scrollbars with the actual size of the
    // content.
    function updateScrollbarsInner(cm, measure) {
        var d = cm.display;
        var sizes = d.scrollbars.update(measure);

        d.sizer.style.paddingRight = (d.barWidth = sizes.right) + "px";
        d.sizer.style.paddingBottom = (d.barHeight = sizes.bottom) + "px";

        if (sizes.right && sizes.bottom) {
            d.scrollbarFiller.style.display = "block";
            d.scrollbarFiller.style.height = sizes.bottom + "px";
            d.scrollbarFiller.style.width = sizes.right + "px";
        } else d.scrollbarFiller.style.display = "";
        if (sizes.bottom && cm.options.coverGutterNextToScrollbar && cm.options.fixedGutter) {
            d.gutterFiller.style.display = "block";
            d.gutterFiller.style.height = sizes.bottom + "px";
            d.gutterFiller.style.width = measure.gutterWidth + "px";
        } else d.gutterFiller.style.display = "";
    }

    // Compute the lines that are visible in a given viewport (defaults
    // the the current scroll position). viewport may contain top,
    // height, and ensure (see op.scrollToPos) properties.
    function visibleLines(display, doc, viewport) {
        var top = viewport && viewport.top != null ? Math.max(0, viewport.top) : display.scroller.scrollTop;
        top = Math.floor(top - paddingTop(display));
        var bottom = viewport && viewport.bottom != null ? viewport.bottom : top + display.wrapper.clientHeight;

        var from = lineAtHeight(doc, top),
            to = lineAtHeight(doc, bottom);
        // Ensure is a {from: {line, ch}, to: {line, ch}} object, and
        // forces those lines into the viewport (if possible).
        if (viewport && viewport.ensure) {
            var ensureFrom = viewport.ensure.from.line,
                ensureTo = viewport.ensure.to.line;
            if (ensureFrom < from) {
                from = ensureFrom;
                to = lineAtHeight(doc, heightAtLine(getLine(doc, ensureFrom)) + display.wrapper.clientHeight);
            } else if (Math.min(ensureTo, doc.lastLine()) >= to) {
                from = lineAtHeight(doc, heightAtLine(getLine(doc, ensureTo)) - display.wrapper.clientHeight);
                to = ensureTo;
            }
        }
        return {
            from: from,
            to: Math.max(to, from + 1)
        };
    }

    // LINE NUMBERS

    // Re-align line numbers and gutter marks to compensate for
    // horizontal scrolling.
    function alignHorizontally(cm) {
        var display = cm.display,
            view = display.view;
        if (!display.alignWidgets && (!display.gutters.firstChild || !cm.options.fixedGutter)) return;
        var comp = compensateForHScroll(display) - display.scroller.scrollLeft + cm.doc.scrollLeft;
        var gutterW = display.gutters.offsetWidth,
            left = comp + "px";
        for (var i = 0; i < view.length; i++)
            if (!view[i].hidden) {
                if (cm.options.fixedGutter && view[i].gutter)
                    view[i].gutter.style.left = left;
                var align = view[i].alignable;
                if (align)
                    for (var j = 0; j < align.length; j++)
                        align[j].style.left = left;
            }
        if (cm.options.fixedGutter)
            display.gutters.style.left = (comp + gutterW) + "px";
    }

    // Used to ensure that the line number gutter is still the right
    // size for the current document size. Returns true when an update
    // is needed.
    function maybeUpdateLineNumberWidth(cm) {
        if (!cm.options.lineNumbers) return false;
        var doc = cm.doc,
            last = lineNumberFor(cm.options, doc.first + doc.size - 1),
            display = cm.display;
        if (last.length != display.lineNumChars) {
            var test = display.measure.appendChild(elt("div", [elt("div", last)],
                "CodeMirror-linenumber CodeMirror-gutter-elt"));
            var innerW = test.firstChild.offsetWidth,
                padding = test.offsetWidth - innerW;
            display.lineGutter.style.width = "";
            display.lineNumInnerWidth = Math.max(innerW, display.lineGutter.offsetWidth - padding) + 1;
            display.lineNumWidth = display.lineNumInnerWidth + padding;
            display.lineNumChars = display.lineNumInnerWidth ? last.length : -1;
            display.lineGutter.style.width = display.lineNumWidth + "px";
            updateGutterSpace(cm);
            return true;
        }
        return false;
    }

    function lineNumberFor(options, i) {
        return String(options.lineNumberFormatter(i + options.firstLineNumber));
    }

    // Computes display.scroller.scrollLeft + display.gutters.offsetWidth,
    // but using getBoundingClientRect to get a sub-pixel-accurate
    // result.
    function compensateForHScroll(display) {
        return display.scroller.getBoundingClientRect().left - display.sizer.getBoundingClientRect().left;
    }

    // DISPLAY DRAWING

    function DisplayUpdate(cm, viewport, force) {
        var display = cm.display;

        this.viewport = viewport;
        // Store some values that we'll need later (but don't want to force a relayout for)
        this.visible = visibleLines(display, cm.doc, viewport);
        this.editorIsHidden = !display.wrapper.offsetWidth;
        this.wrapperHeight = display.wrapper.clientHeight;
        this.wrapperWidth = display.wrapper.clientWidth;
        this.oldDisplayWidth = displayWidth(cm);
        this.force = force;
        this.dims = getDimensions(cm);
        this.events = [];
    }

    DisplayUpdate.prototype.signal = function(emitter, type) {
        if (hasHandler(emitter, type))
            this.events.push(arguments);
    };
    DisplayUpdate.prototype.finish = function() {
        for (var i = 0; i < this.events.length; i++)
            signal.apply(null, this.events[i]);
    };

    function maybeClipScrollbars(cm) {
        var display = cm.display;
        if (!display.scrollbarsClipped && display.scroller.offsetWidth) {
            display.nativeBarWidth = display.scroller.offsetWidth - display.scroller.clientWidth;
            display.heightForcer.style.height = scrollGap(cm) + "px";
            display.sizer.style.marginBottom = -display.nativeBarWidth + "px";
            display.sizer.style.borderRightWidth = scrollGap(cm) + "px";
            display.scrollbarsClipped = true;
        }
    }

    // Does the actual updating of the line display. Bails out
    // (returning false) when there is nothing to be done and forced is
    // false.
    function updateDisplayIfNeeded(cm, update) {
        var display = cm.display,
            doc = cm.doc;

        if (update.editorIsHidden) {
            resetView(cm);
            return false;
        }

        // Bail out if the visible area is already rendered and nothing changed.
        if (!update.force &&
            update.visible.from >= display.viewFrom && update.visible.to <= display.viewTo &&
            (display.updateLineNumbers == null || display.updateLineNumbers >= display.viewTo) &&
            display.renderedView == display.view && countDirtyView(cm) == 0)
            return false;

        if (maybeUpdateLineNumberWidth(cm)) {
            resetView(cm);
            update.dims = getDimensions(cm);
        }

        // Compute a suitable new viewport (from & to)
        var end = doc.first + doc.size;
        var from = Math.max(update.visible.from - cm.options.viewportMargin, doc.first);
        var to = Math.min(end, update.visible.to + cm.options.viewportMargin);
        if (display.viewFrom < from && from - display.viewFrom < 20) from = Math.max(doc.first, display.viewFrom);
        if (display.viewTo > to && display.viewTo - to < 20) to = Math.min(end, display.viewTo);
        if (sawCollapsedSpans) {
            from = visualLineNo(cm.doc, from);
            to = visualLineEndNo(cm.doc, to);
        }

        var different = from != display.viewFrom || to != display.viewTo ||
            display.lastWrapHeight != update.wrapperHeight || display.lastWrapWidth != update.wrapperWidth;
        adjustView(cm, from, to);

        display.viewOffset = heightAtLine(getLine(cm.doc, display.viewFrom));
        // Position the mover div to align with the current scroll position
        cm.display.mover.style.top = display.viewOffset + "px";

        var toUpdate = countDirtyView(cm);
        if (!different && toUpdate == 0 && !update.force && display.renderedView == display.view &&
            (display.updateLineNumbers == null || display.updateLineNumbers >= display.viewTo))
            return false;

        // For big changes, we hide the enclosing element during the
        // update, since that speeds up the operations on most browsers.
        var focused = activeElt();
        if (toUpdate > 4) display.lineDiv.style.display = "none";
        patchDisplay(cm, display.updateLineNumbers, update.dims);
        if (toUpdate > 4) display.lineDiv.style.display = "";
        display.renderedView = display.view;
        // There might have been a widget with a focused element that got
        // hidden or updated, if so re-focus it.
        if (focused && activeElt() != focused && focused.offsetHeight) focused.focus();

        // Prevent selection and cursors from interfering with the scroll
        // width and height.
        removeChildren(display.cursorDiv);
        removeChildren(display.selectionDiv);
        display.gutters.style.height = 0;

        if (different) {
            display.lastWrapHeight = update.wrapperHeight;
            display.lastWrapWidth = update.wrapperWidth;
            startWorker(cm, 400);
        }

        display.updateLineNumbers = null;

        return true;
    }

    function postUpdateDisplay(cm, update) {
        var viewport = update.viewport;
        for (var first = true;; first = false) {
            if (!first || !cm.options.lineWrapping || update.oldDisplayWidth == displayWidth(cm)) {
                // Clip forced viewport to actual scrollable area.
                if (viewport && viewport.top != null)
                    viewport = {
                        top: Math.min(cm.doc.height + paddingVert(cm.display) - displayHeight(cm), viewport.top)
                    };
                // Updated line heights might result in the drawn area not
                // actually covering the viewport. Keep looping until it does.
                update.visible = visibleLines(cm.display, cm.doc, viewport);
                if (update.visible.from >= cm.display.viewFrom && update.visible.to <= cm.display.viewTo)
                    break;
            }
            if (!updateDisplayIfNeeded(cm, update)) break;
            updateHeightsInViewport(cm);
            var barMeasure = measureForScrollbars(cm);
            updateSelection(cm);
            setDocumentHeight(cm, barMeasure);
            updateScrollbars(cm, barMeasure);
        }

        update.signal(cm, "update", cm);
        if (cm.display.viewFrom != cm.display.reportedViewFrom || cm.display.viewTo != cm.display.reportedViewTo) {
            update.signal(cm, "viewportChange", cm, cm.display.viewFrom, cm.display.viewTo);
            cm.display.reportedViewFrom = cm.display.viewFrom;
            cm.display.reportedViewTo = cm.display.viewTo;
        }
    }

    function updateDisplaySimple(cm, viewport) {
        var update = new DisplayUpdate(cm, viewport);
        if (updateDisplayIfNeeded(cm, update)) {
            updateHeightsInViewport(cm);
            postUpdateDisplay(cm, update);
            var barMeasure = measureForScrollbars(cm);
            updateSelection(cm);
            setDocumentHeight(cm, barMeasure);
            updateScrollbars(cm, barMeasure);
            update.finish();
        }
    }

    function setDocumentHeight(cm, measure) {
        cm.display.sizer.style.minHeight = measure.docHeight + "px";
        var total = measure.docHeight + cm.display.barHeight;
        cm.display.heightForcer.style.top = total + "px";
        cm.display.gutters.style.height = Math.max(total + scrollGap(cm), measure.clientHeight) + "px";
    }

    // Read the actual heights of the rendered lines, and update their
    // stored heights to match.
    function updateHeightsInViewport(cm) {
        var display = cm.display;
        var prevBottom = display.lineDiv.offsetTop;
        for (var i = 0; i < display.view.length; i++) {
            var cur = display.view[i],
                height;
            if (cur.hidden) continue;
            if (ie && ie_version < 8) {
                var bot = cur.node.offsetTop + cur.node.offsetHeight;
                height = bot - prevBottom;
                prevBottom = bot;
            } else {
                var box = cur.node.getBoundingClientRect();
                height = box.bottom - box.top;
            }
            var diff = cur.line.height - height;
            if (height < 2) height = textHeight(display);
            if (diff > .001 || diff < -.001) {
                updateLineHeight(cur.line, height);
                updateWidgetHeight(cur.line);
                if (cur.rest)
                    for (var j = 0; j < cur.rest.length; j++)
                        updateWidgetHeight(cur.rest[j]);
            }
        }
    }

    // Read and store the height of line widgets associated with the
    // given line.
    function updateWidgetHeight(line) {
        if (line.widgets)
            for (var i = 0; i < line.widgets.length; ++i)
                line.widgets[i].height = line.widgets[i].node.offsetHeight;
    }

    // Do a bulk-read of the DOM positions and sizes needed to draw the
    // view, so that we don't interleave reading and writing to the DOM.
    function getDimensions(cm) {
        var d = cm.display,
            left = {},
            width = {};
        var gutterLeft = d.gutters.clientLeft;
        for (var n = d.gutters.firstChild, i = 0; n; n = n.nextSibling, ++i) {
            left[cm.options.gutters[i]] = n.offsetLeft + n.clientLeft + gutterLeft;
            width[cm.options.gutters[i]] = n.clientWidth;
        }
        return {
            fixedPos: compensateForHScroll(d),
            gutterTotalWidth: d.gutters.offsetWidth,
            gutterLeft: left,
            gutterWidth: width,
            wrapperWidth: d.wrapper.clientWidth
        };
    }

    // Sync the actual display DOM structure with display.view, removing
    // nodes for lines that are no longer in view, and creating the ones
    // that are not there yet, and updating the ones that are out of
    // date.
    function patchDisplay(cm, updateNumbersFrom, dims) {
        var display = cm.display,
            lineNumbers = cm.options.lineNumbers;
        var container = display.lineDiv,
            cur = container.firstChild;

        function rm(node) {
            var next = node.nextSibling;
            // Works around a throw-scroll bug in OS X Webkit
            if (webkit && mac && cm.display.currentWheelTarget == node)
                node.style.display = "none";
            else
                node.parentNode.removeChild(node);
            return next;
        }

        var view = display.view,
            lineN = display.viewFrom;
        // Loop over the elements in the view, syncing cur (the DOM nodes
        // in display.lineDiv) with the view as we go.
        for (var i = 0; i < view.length; i++) {
            var lineView = view[i];
            if (lineView.hidden) {} else if (!lineView.node || lineView.node.parentNode != container) { // Not drawn yet
                var node = buildLineElement(cm, lineView, lineN, dims);
                container.insertBefore(node, cur);
            } else { // Already drawn
                while (cur != lineView.node) cur = rm(cur);
                var updateNumber = lineNumbers && updateNumbersFrom != null &&
                    updateNumbersFrom <= lineN && lineView.lineNumber;
                if (lineView.changes) {
                    if (indexOf(lineView.changes, "gutter") > -1) updateNumber = false;
                    updateLineForChanges(cm, lineView, lineN, dims);
                }
                if (updateNumber) {
                    removeChildren(lineView.lineNumber);
                    lineView.lineNumber.appendChild(document.createTextNode(lineNumberFor(cm.options, lineN)));
                }
                cur = lineView.node.nextSibling;
            }
            lineN += lineView.size;
        }
        while (cur) cur = rm(cur);
    }

    // When an aspect of a line changes, a string is added to
    // lineView.changes. This updates the relevant part of the line's
    // DOM structure.
    function updateLineForChanges(cm, lineView, lineN, dims) {
        for (var j = 0; j < lineView.changes.length; j++) {
            var type = lineView.changes[j];
            if (type == "text") updateLineText(cm, lineView);
            else if (type == "gutter") updateLineGutter(cm, lineView, lineN, dims);
            else if (type == "class") updateLineClasses(lineView);
            else if (type == "widget") updateLineWidgets(cm, lineView, dims);
        }
        lineView.changes = null;
    }

    // Lines with gutter elements, widgets or a background class need to
    // be wrapped, and have the extra elements added to the wrapper div
    function ensureLineWrapped(lineView) {
        if (lineView.node == lineView.text) {
            lineView.node = elt("div", null, null, "position: relative");
            if (lineView.text.parentNode)
                lineView.text.parentNode.replaceChild(lineView.node, lineView.text);
            lineView.node.appendChild(lineView.text);
            if (ie && ie_version < 8) lineView.node.style.zIndex = 2;
        }
        return lineView.node;
    }

    function updateLineBackground(lineView) {
        var cls = lineView.bgClass ? lineView.bgClass + " " + (lineView.line.bgClass || "") : lineView.line.bgClass;
        if (cls) cls += " CodeMirror-linebackground";
        if (lineView.background) {
            if (cls) lineView.background.className = cls;
            else {
                lineView.background.parentNode.removeChild(lineView.background);
                lineView.background = null;
            }
        } else if (cls) {
            var wrap = ensureLineWrapped(lineView);
            lineView.background = wrap.insertBefore(elt("div", null, cls), wrap.firstChild);
        }
    }

    // Wrapper around buildLineContent which will reuse the structure
    // in display.externalMeasured when possible.
    function getLineContent(cm, lineView) {
        var ext = cm.display.externalMeasured;
        if (ext && ext.line == lineView.line) {
            cm.display.externalMeasured = null;
            lineView.measure = ext.measure;
            return ext.built;
        }
        return buildLineContent(cm, lineView);
    }

    // Redraw the line's text. Interacts with the background and text
    // classes because the mode may output tokens that influence these
    // classes.
    function updateLineText(cm, lineView) {
        var cls = lineView.text.className;
        var built = getLineContent(cm, lineView);
        if (lineView.text == lineView.node) lineView.node = built.pre;
        lineView.text.parentNode.replaceChild(built.pre, lineView.text);
        lineView.text = built.pre;
        if (built.bgClass != lineView.bgClass || built.textClass != lineView.textClass) {
            lineView.bgClass = built.bgClass;
            lineView.textClass = built.textClass;
            updateLineClasses(lineView);
        } else if (cls) {
            lineView.text.className = cls;
        }
    }

    function updateLineClasses(lineView) {
        updateLineBackground(lineView);
        if (lineView.line.wrapClass)
            ensureLineWrapped(lineView).className = lineView.line.wrapClass;
        else if (lineView.node != lineView.text)
            lineView.node.className = "";
        var textClass = lineView.textClass ? lineView.textClass + " " + (lineView.line.textClass || "") : lineView.line.textClass;
        lineView.text.className = textClass || "";
    }

    function updateLineGutter(cm, lineView, lineN, dims) {
        if (lineView.gutter) {
            lineView.node.removeChild(lineView.gutter);
            lineView.gutter = null;
        }
        var markers = lineView.line.gutterMarkers;
        if (cm.options.lineNumbers || markers) {
            var wrap = ensureLineWrapped(lineView);
            var gutterWrap = lineView.gutter = elt("div", null, "CodeMirror-gutter-wrapper", "left: " +
                (cm.options.fixedGutter ? dims.fixedPos : -dims.gutterTotalWidth) +
                "px; width: " + dims.gutterTotalWidth + "px");
            cm.display.input.setUneditable(gutterWrap);
            wrap.insertBefore(gutterWrap, lineView.text);
            if (lineView.line.gutterClass)
                gutterWrap.className += " " + lineView.line.gutterClass;
            if (cm.options.lineNumbers && (!markers || !markers["CodeMirror-linenumbers"]))
                lineView.lineNumber = gutterWrap.appendChild(
                    elt("div", lineNumberFor(cm.options, lineN),
                        "CodeMirror-linenumber CodeMirror-gutter-elt",
                        "left: " + dims.gutterLeft["CodeMirror-linenumbers"] + "px; width: " + cm.display.lineNumInnerWidth + "px"));
            if (markers)
                for (var k = 0; k < cm.options.gutters.length; ++k) {
                    var id = cm.options.gutters[k],
                        found = markers.hasOwnProperty(id) && markers[id];
                    if (found)
                        gutterWrap.appendChild(elt("div", [found], "CodeMirror-gutter-elt", "left: " +
                            dims.gutterLeft[id] + "px; width: " + dims.gutterWidth[id] + "px"));
                }
        }
    }

    function updateLineWidgets(cm, lineView, dims) {
        if (lineView.alignable) lineView.alignable = null;
        for (var node = lineView.node.firstChild, next; node; node = next) {
            var next = node.nextSibling;
            if (node.className == "CodeMirror-linewidget")
                lineView.node.removeChild(node);
        }
        insertLineWidgets(cm, lineView, dims);
    }

    // Build a line's DOM representation from scratch
    function buildLineElement(cm, lineView, lineN, dims) {
        var built = getLineContent(cm, lineView);
        lineView.text = lineView.node = built.pre;
        if (built.bgClass) lineView.bgClass = built.bgClass;
        if (built.textClass) lineView.textClass = built.textClass;

        updateLineClasses(lineView);
        updateLineGutter(cm, lineView, lineN, dims);
        insertLineWidgets(cm, lineView, dims);
        return lineView.node;
    }

    // A lineView may contain multiple logical lines (when merged by
    // collapsed spans). The widgets for all of them need to be drawn.
    function insertLineWidgets(cm, lineView, dims) {
        insertLineWidgetsFor(cm, lineView.line, lineView, dims, true);
        if (lineView.rest)
            for (var i = 0; i < lineView.rest.length; i++)
                insertLineWidgetsFor(cm, lineView.rest[i], lineView, dims, false);
    }

    function insertLineWidgetsFor(cm, line, lineView, dims, allowAbove) {
        if (!line.widgets) return;
        var wrap = ensureLineWrapped(lineView);
        for (var i = 0, ws = line.widgets; i < ws.length; ++i) {
            var widget = ws[i],
                node = elt("div", [widget.node], "CodeMirror-linewidget");
            if (!widget.handleMouseEvents) node.setAttribute("cm-ignore-events", "true");
            positionLineWidget(widget, node, lineView, dims);
            cm.display.input.setUneditable(node);
            if (allowAbove && widget.above)
                wrap.insertBefore(node, lineView.gutter || lineView.text);
            else
                wrap.appendChild(node);
            signalLater(widget, "redraw");
        }
    }

    function positionLineWidget(widget, node, lineView, dims) {
        if (widget.noHScroll) {
            (lineView.alignable || (lineView.alignable = [])).push(node);
            var width = dims.wrapperWidth;
            node.style.left = dims.fixedPos + "px";
            if (!widget.coverGutter) {
                width -= dims.gutterTotalWidth;
                node.style.paddingLeft = dims.gutterTotalWidth + "px";
            }
            node.style.width = width + "px";
        }
        if (widget.coverGutter) {
            node.style.zIndex = 5;
            node.style.position = "relative";
            if (!widget.noHScroll) node.style.marginLeft = -dims.gutterTotalWidth + "px";
        }
    }

    // POSITION OBJECT

    // A Pos instance represents a position within the text.
    var Pos = CodeMirror.Pos = function(line, ch) {
        if (!(this instanceof Pos)) return new Pos(line, ch);
        this.line = line;
        this.ch = ch;
    };

    // Compare two positions, return 0 if they are the same, a negative
    // number when a is less, and a positive number otherwise.
    var cmp = CodeMirror.cmpPos = function(a, b) {
        return a.line - b.line || a.ch - b.ch;
    };

    function copyPos(x) {
        return Pos(x.line, x.ch);
    }

    function maxPos(a, b) {
        return cmp(a, b) < 0 ? b : a;
    }

    function minPos(a, b) {
        return cmp(a, b) < 0 ? a : b;
    }

    // INPUT HANDLING

    function ensureFocus(cm) {
        if (!cm.state.focused) {
            cm.display.input.focus();
            onFocus(cm);
        }
    }

    function isReadOnly(cm) {
        return cm.options.readOnly || cm.doc.cantEdit;
    }

    // This will be set to an array of strings when copying, so that,
    // when pasting, we know what kind of selections the copied text
    // was made out of.
    var lastCopied = null;

    function applyTextInput(cm, inserted, deleted, sel, origin) {
        var doc = cm.doc;
        cm.display.shift = false;
        if (!sel) sel = doc.sel;

        var textLines = splitLines(inserted),
            multiPaste = null;
        // When pasing N lines into N selections, insert one line per selection
        if (cm.state.pasteIncoming && sel.ranges.length > 1) {
            if (lastCopied && lastCopied.join("\n") == inserted)
                multiPaste = sel.ranges.length % lastCopied.length == 0 && map(lastCopied, splitLines);
            else if (textLines.length == sel.ranges.length)
                multiPaste = map(textLines, function(l) {
                    return [l];
                });
        }

        // Normal behavior is to insert the new text into every selection
        for (var i = sel.ranges.length - 1; i >= 0; i--) {
            var range = sel.ranges[i];
            var from = range.from(),
                to = range.to();
            if (range.empty()) {
                if (deleted && deleted > 0) // Handle deletion
                    from = Pos(from.line, from.ch - deleted);
                else if (cm.state.overwrite && !cm.state.pasteIncoming) // Handle overwrite
                    to = Pos(to.line, Math.min(getLine(doc, to.line).text.length, to.ch + lst(textLines).length));
            }
            var updateInput = cm.curOp.updateInput;
            var changeEvent = {
                from: from,
                to: to,
                text: multiPaste ? multiPaste[i % multiPaste.length] : textLines,
                origin: origin || (cm.state.pasteIncoming ? "paste" : cm.state.cutIncoming ? "cut" : "+input")
            };
            makeChange(cm.doc, changeEvent);
            signalLater(cm, "inputRead", cm, changeEvent);
        }
        if (inserted && !cm.state.pasteIncoming)
            triggerElectric(cm, inserted);

        ensureCursorVisible(cm);
        cm.curOp.updateInput = updateInput;
        cm.curOp.typing = true;
        cm.state.pasteIncoming = cm.state.cutIncoming = false;
    }

    function triggerElectric(cm, inserted) {
        // When an 'electric' character is inserted, immediately trigger a reindent
        if (!cm.options.electricChars || !cm.options.smartIndent) return;
        var sel = cm.doc.sel;

        for (var i = sel.ranges.length - 1; i >= 0; i--) {
            var range = sel.ranges[i];
            if (range.head.ch > 100 || (i && sel.ranges[i - 1].head.line == range.head.line)) continue;
            var mode = cm.getModeAt(range.head);
            var indented = false;
            if (mode.electricChars) {
                for (var j = 0; j < mode.electricChars.length; j++)
                    if (inserted.indexOf(mode.electricChars.charAt(j)) > -1) {
                        indented = indentLine(cm, range.head.line, "smart");
                        break;
                    }
            } else if (mode.electricInput) {
                if (mode.electricInput.test(getLine(cm.doc, range.head.line).text.slice(0, range.head.ch)))
                    indented = indentLine(cm, range.head.line, "smart");
            }
            if (indented) signalLater(cm, "electricInput", cm, range.head.line);
        }
    }

    function copyableRanges(cm) {
        var text = [],
            ranges = [];
        for (var i = 0; i < cm.doc.sel.ranges.length; i++) {
            var line = cm.doc.sel.ranges[i].head.line;
            var lineRange = {
                anchor: Pos(line, 0),
                head: Pos(line + 1, 0)
            };
            ranges.push(lineRange);
            text.push(cm.getRange(lineRange.anchor, lineRange.head));
        }
        return {
            text: text,
            ranges: ranges
        };
    }

    function disableBrowserMagic(field) {
        field.setAttribute("autocorrect", "off");
        field.setAttribute("autocapitalize", "off");
        field.setAttribute("spellcheck", "false");
    }

    // TEXTAREA INPUT STYLE

    function TextareaInput(cm) {
        this.cm = cm;
        // See input.poll and input.reset
        this.prevInput = "";

        // Flag that indicates whether we expect input to appear real soon
        // now (after some event like 'keypress' or 'input') and are
        // polling intensively.
        this.pollingFast = false;
        // Self-resetting timeout for the poller
        this.polling = new Delayed();
        // Tracks when input.reset has punted to just putting a short
        // string into the textarea instead of the full selection.
        this.inaccurateSelection = false;
        // Used to work around IE issue with selection being forgotten when focus moves away from textarea
        this.hasSelection = false;
        this.composing = null;
    };

    function hiddenTextarea() {
        var te = elt("textarea", null, null, "position: absolute; padding: 0; width: 1px; height: 1em; outline: none");
        var div = elt("div", [te], null, "overflow: hidden; position: relative; width: 3px; height: 0px;");
        // The textarea is kept positioned near the cursor to prevent the
        // fact that it'll be scrolled into view on input from scrolling
        // our fake cursor out of view. On webkit, when wrap=off, paste is
        // very slow. So make the area wide instead.
        if (webkit) te.style.width = "1000px";
        else te.setAttribute("wrap", "off");
        // If border: 0; -- iOS fails to open keyboard (issue #1287)
        if (ios) te.style.border = "1px solid black";
        disableBrowserMagic(te);
        return div;
    }

    TextareaInput.prototype = copyObj({
        init: function(display) {
            var input = this,
                cm = this.cm;

            // Wraps and hides input textarea
            var div = this.wrapper = hiddenTextarea();
            // The semihidden textarea that is focused when the editor is
            // focused, and receives input.
            var te = this.textarea = div.firstChild;
            display.wrapper.insertBefore(div, display.wrapper.firstChild);

            // Needed to hide big blue blinking cursor on Mobile Safari (doesn't seem to work in iOS 8 anymore)
            if (ios) te.style.width = "0px";

            on(te, "input", function() {
                if (ie && ie_version >= 9 && input.hasSelection) input.hasSelection = null;
                input.poll();
            });

            on(te, "paste", function() {
                // Workaround for webkit bug https://bugs.webkit.org/show_bug.cgi?id=90206
                // Add a char to the end of textarea before paste occur so that
                // selection doesn't span to the end of textarea.
                if (webkit && !cm.state.fakedLastChar && !(new Date - cm.state.lastMiddleDown < 200)) {
                    var start = te.selectionStart,
                        end = te.selectionEnd;
                    te.value += "$";
                    // The selection end needs to be set before the start, otherwise there
                    // can be an intermediate non-empty selection between the two, which
                    // can override the middle-click paste buffer on linux and cause the
                    // wrong thing to get pasted.
                    te.selectionEnd = end;
                    te.selectionStart = start;
                    cm.state.fakedLastChar = true;
                }
                cm.state.pasteIncoming = true;
                input.fastPoll();
            });

            function prepareCopyCut(e) {
                if (cm.somethingSelected()) {
                    lastCopied = cm.getSelections();
                    if (input.inaccurateSelection) {
                        input.prevInput = "";
                        input.inaccurateSelection = false;
                        te.value = lastCopied.join("\n");
                        selectInput(te);
                    }
                } else if (!cm.options.lineWiseCopyCut) {
                    return;
                } else {
                    var ranges = copyableRanges(cm);
                    lastCopied = ranges.text;
                    if (e.type == "cut") {
                        cm.setSelections(ranges.ranges, null, sel_dontScroll);
                    } else {
                        input.prevInput = "";
                        te.value = ranges.text.join("\n");
                        selectInput(te);
                    }
                }
                if (e.type == "cut") cm.state.cutIncoming = true;
            }
            on(te, "cut", prepareCopyCut);
            on(te, "copy", prepareCopyCut);

            on(display.scroller, "paste", function(e) {
                if (eventInWidget(display, e)) return;
                cm.state.pasteIncoming = true;
                input.focus();
            });

            // Prevent normal selection in the editor (we handle our own)
            on(display.lineSpace, "selectstart", function(e) {
                if (!eventInWidget(display, e)) e_preventDefault(e);
            });

            on(te, "compositionstart", function() {
                var start = cm.getCursor("from");
                input.composing = {
                    start: start,
                    range: cm.markText(start, cm.getCursor("to"), {
                        className: "CodeMirror-composing"
                    })
                };
            });
            on(te, "compositionend", function() {
                if (input.composing) {
                    input.poll();
                    input.composing.range.clear();
                    input.composing = null;
                }
            });
        },

        prepareSelection: function() {
            // Redraw the selection and/or cursor
            var cm = this.cm,
                display = cm.display,
                doc = cm.doc;
            var result = prepareSelection(cm);

            // Move the hidden textarea near the cursor to prevent scrolling artifacts
            if (cm.options.moveInputWithCursor) {
                var headPos = cursorCoords(cm, doc.sel.primary().head, "div");
                var wrapOff = display.wrapper.getBoundingClientRect(),
                    lineOff = display.lineDiv.getBoundingClientRect();
                result.teTop = Math.max(0, Math.min(display.wrapper.clientHeight - 10,
                    headPos.top + lineOff.top - wrapOff.top));
                result.teLeft = Math.max(0, Math.min(display.wrapper.clientWidth - 10,
                    headPos.left + lineOff.left - wrapOff.left));
            }

            return result;
        },

        showSelection: function(drawn) {
            var cm = this.cm,
                display = cm.display;
            removeChildrenAndAdd(display.cursorDiv, drawn.cursors);
            removeChildrenAndAdd(display.selectionDiv, drawn.selection);
            if (drawn.teTop != null) {
                this.wrapper.style.top = drawn.teTop + "px";
                this.wrapper.style.left = drawn.teLeft + "px";
            }
        },

        // Reset the input to correspond to the selection (or to be empty,
        // when not typing and nothing is selected)
        reset: function(typing) {
            if (this.contextMenuPending) return;
            var minimal, selected, cm = this.cm,
                doc = cm.doc;
            if (cm.somethingSelected()) {
                this.prevInput = "";
                var range = doc.sel.primary();
                minimal = hasCopyEvent &&
                    (range.to().line - range.from().line > 100 || (selected = cm.getSelection()).length > 1000);
                var content = minimal ? "-" : selected || cm.getSelection();
                this.textarea.value = content;
                if (cm.state.focused) selectInput(this.textarea);
                if (ie && ie_version >= 9) this.hasSelection = content;
            } else if (!typing) {
                this.prevInput = this.textarea.value = "";
                if (ie && ie_version >= 9) this.hasSelection = null;
            }
            this.inaccurateSelection = minimal;
        },

        getField: function() {
            return this.textarea;
        },

        supportsTouch: function() {
            return false;
        },

        focus: function() {
            if (this.cm.options.readOnly != "nocursor" && (!mobile || activeElt() != this.textarea)) {
                try {
                    this.textarea.focus();
                } catch (e) {} // IE8 will throw if the textarea is display: none or not in DOM
            }
        },

        blur: function() {
            this.textarea.blur();
        },

        resetPosition: function() {
            this.wrapper.style.top = this.wrapper.style.left = 0;
        },

        receivedFocus: function() {
            this.slowPoll();
        },

        // Poll for input changes, using the normal rate of polling. This
        // runs as long as the editor is focused.
        slowPoll: function() {
            var input = this;
            if (input.pollingFast) return;
            input.polling.set(this.cm.options.pollInterval, function() {
                input.poll();
                if (input.cm.state.focused) input.slowPoll();
            });
        },

        // When an event has just come in that is likely to add or change
        // something in the input textarea, we poll faster, to ensure that
        // the change appears on the screen quickly.
        fastPoll: function() {
            var missed = false,
                input = this;
            input.pollingFast = true;

            function p() {
                var changed = input.poll();
                if (!changed && !missed) {
                    missed = true;
                    input.polling.set(60, p);
                } else {
                    input.pollingFast = false;
                    input.slowPoll();
                }
            }
            input.polling.set(20, p);
        },

        // Read input from the textarea, and update the document to match.
        // When something is selected, it is present in the textarea, and
        // selected (unless it is huge, in which case a placeholder is
        // used). When nothing is selected, the cursor sits after previously
        // seen text (can be empty), which is stored in prevInput (we must
        // not reset the textarea when typing, because that breaks IME).
        poll: function() {
            var cm = this.cm,
                input = this.textarea,
                prevInput = this.prevInput;
            // Since this is called a *lot*, try to bail out as cheaply as
            // possible when it is clear that nothing happened. hasSelection
            // will be the case when there is a lot of text in the textarea,
            // in which case reading its value would be expensive.
            if (!cm.state.focused || (hasSelection(input) && !prevInput) ||
                isReadOnly(cm) || cm.options.disableInput || cm.state.keySeq)
                return false;
            // See paste handler for more on the fakedLastChar kludge
            if (cm.state.pasteIncoming && cm.state.fakedLastChar) {
                input.value = input.value.substring(0, input.value.length - 1);
                cm.state.fakedLastChar = false;
            }
            var text = input.value;
            // If nothing changed, bail.
            if (text == prevInput && !cm.somethingSelected()) return false;
            // Work around nonsensical selection resetting in IE9/10, and
            // inexplicable appearance of private area unicode characters on
            // some key combos in Mac (#2689).
            if (ie && ie_version >= 9 && this.hasSelection === text ||
                mac && /[\uf700-\uf7ff]/.test(text)) {
                cm.display.input.reset();
                return false;
            }

            if (cm.doc.sel == cm.display.selForContextMenu) {
                var first = text.charCodeAt(0);
                if (first == 0x200b && !prevInput) prevInput = "\u200b";
                if (first == 0x21da) {
                    this.reset();
                    return this.cm.execCommand("undo");
                }
            }
            // Find the part of the input that is actually new
            var same = 0,
                l = Math.min(prevInput.length, text.length);
            while (same < l && prevInput.charCodeAt(same) == text.charCodeAt(same)) ++same;

            var self = this;
            runInOp(cm, function() {
                applyTextInput(cm, text.slice(same), prevInput.length - same,
                    null, self.composing ? "*compose" : null);

                // Don't leave long text in the textarea, since it makes further polling slow
                if (text.length > 1000 || text.indexOf("\n") > -1) input.value = self.prevInput = "";
                else self.prevInput = text;

                if (self.composing) {
                    self.composing.range.clear();
                    self.composing.range = cm.markText(self.composing.start, cm.getCursor("to"), {
                        className: "CodeMirror-composing"
                    });
                }
            });
            return true;
        },

        ensurePolled: function() {
            if (this.pollingFast && this.poll()) this.pollingFast = false;
        },

        onKeyPress: function() {
            if (ie && ie_version >= 9) this.hasSelection = null;
            this.fastPoll();
        },

        onContextMenu: function(e) {
            var input = this,
                cm = input.cm,
                display = cm.display,
                te = input.textarea;
            var pos = posFromMouse(cm, e),
                scrollPos = display.scroller.scrollTop;
            if (!pos || presto) return; // Opera is difficult.

            // Reset the current text selection only if the click is done outside of the selection
            // and 'resetSelectionOnContextMenu' option is true.
            var reset = cm.options.resetSelectionOnContextMenu;
            if (reset && cm.doc.sel.contains(pos) == -1)
                operation(cm, setSelection)(cm.doc, simpleSelection(pos), sel_dontScroll);

            var oldCSS = te.style.cssText;
            input.wrapper.style.position = "absolute";
            te.style.cssText = "position: fixed; width: 30px; height: 30px; top: " + (e.clientY - 5) +
                "px; left: " + (e.clientX - 5) + "px; z-index: 1000; background: " +
                (ie ? "rgba(255, 255, 255, .05)" : "transparent") +
                "; outline: none; border-width: 0; outline: none; overflow: hidden; opacity: .05; filter: alpha(opacity=5);";
            if (webkit) var oldScrollY = window.scrollY; // Work around Chrome issue (#2712)
            display.input.focus();
            if (webkit) window.scrollTo(null, oldScrollY);
            display.input.reset();
            // Adds "Select all" to context menu in FF
            if (!cm.somethingSelected()) te.value = input.prevInput = " ";
            input.contextMenuPending = true;
            display.selForContextMenu = cm.doc.sel;
            clearTimeout(display.detectingSelectAll);

            // Select-all will be greyed out if there's nothing to select, so
            // this adds a zero-width space so that we can later check whether
            // it got selected.
            function prepareSelectAllHack() {
                if (te.selectionStart != null) {
                    var selected = cm.somethingSelected();
                    var extval = "\u200b" + (selected ? te.value : "");
                    te.value = "\u21da"; // Used to catch context-menu undo
                    te.value = extval;
                    input.prevInput = selected ? "" : "\u200b";
                    te.selectionStart = 1;
                    te.selectionEnd = extval.length;
                    // Re-set this, in case some other handler touched the
                    // selection in the meantime.
                    display.selForContextMenu = cm.doc.sel;
                }
            }

            function rehide() {
                input.contextMenuPending = false;
                input.wrapper.style.position = "relative";
                te.style.cssText = oldCSS;
                if (ie && ie_version < 9) display.scrollbars.setScrollTop(display.scroller.scrollTop = scrollPos);

                // Try to detect the user choosing select-all
                if (te.selectionStart != null) {
                    if (!ie || (ie && ie_version < 9)) prepareSelectAllHack();
                    var i = 0,
                        poll = function() {
                            if (display.selForContextMenu == cm.doc.sel && te.selectionStart == 0 &&
                                te.selectionEnd > 0 && input.prevInput == "\u200b")
                                operation(cm, commands.selectAll)(cm);
                            else if (i++ < 10) display.detectingSelectAll = setTimeout(poll, 500);
                            else display.input.reset();
                        };
                    display.detectingSelectAll = setTimeout(poll, 200);
                }
            }

            if (ie && ie_version >= 9) prepareSelectAllHack();
            if (captureRightClick) {
                e_stop(e);
                var mouseup = function() {
                    off(window, "mouseup", mouseup);
                    setTimeout(rehide, 20);
                };
                on(window, "mouseup", mouseup);
            } else {
                setTimeout(rehide, 50);
            }
        },

        setUneditable: nothing,

        needsContentAttribute: false
    }, TextareaInput.prototype);

    // CONTENTEDITABLE INPUT STYLE

    function ContentEditableInput(cm) {
        this.cm = cm;
        this.lastAnchorNode = this.lastAnchorOffset = this.lastFocusNode = this.lastFocusOffset = null;
        this.polling = new Delayed();
        this.gracePeriod = false;
    }

    ContentEditableInput.prototype = copyObj({
        init: function(display) {
            var input = this,
                cm = input.cm;
            var div = input.div = display.lineDiv;
            div.contentEditable = "true";
            disableBrowserMagic(div);

            on(div, "paste", function(e) {
                var pasted = e.clipboardData && e.clipboardData.getData("text/plain");
                if (pasted) {
                    e.preventDefault();
                    cm.replaceSelection(pasted, null, "paste");
                }
            });

            on(div, "compositionstart", function(e) {
                var data = e.data;
                input.composing = {
                    sel: cm.doc.sel,
                    data: data,
                    startData: data
                };
                if (!data) return;
                var prim = cm.doc.sel.primary();
                var line = cm.getLine(prim.head.line);
                var found = line.indexOf(data, Math.max(0, prim.head.ch - data.length));
                if (found > -1 && found <= prim.head.ch)
                    input.composing.sel = simpleSelection(Pos(prim.head.line, found),
                        Pos(prim.head.line, found + data.length));
            });
            on(div, "compositionupdate", function(e) {
                input.composing.data = e.data;
            });
            on(div, "compositionend", function(e) {
                var ours = input.composing;
                if (!ours) return;
                if (e.data != ours.startData && !/\u200b/.test(e.data))
                    ours.data = e.data;
                // Need a small delay to prevent other code (input event,
                // selection polling) from doing damage when fired right after
                // compositionend.
                setTimeout(function() {
                    if (!ours.handled)
                        input.applyComposition(ours);
                    if (input.composing == ours)
                        input.composing = null;
                }, 50);
            });

            on(div, "touchstart", function() {
                input.forceCompositionEnd();
            });

            on(div, "input", function() {
                if (input.composing) return;
                if (!input.pollContent())
                    runInOp(input.cm, function() {
                        regChange(cm);
                    });
            });

            function onCopyCut(e) {
                if (cm.somethingSelected()) {
                    lastCopied = cm.getSelections();
                    if (e.type == "cut") cm.replaceSelection("", null, "cut");
                } else if (!cm.options.lineWiseCopyCut) {
                    return;
                } else {
                    var ranges = copyableRanges(cm);
                    lastCopied = ranges.text;
                    if (e.type == "cut") {
                        cm.operation(function() {
                            cm.setSelections(ranges.ranges, 0, sel_dontScroll);
                            cm.replaceSelection("", null, "cut");
                        });
                    }
                }
                // iOS exposes the clipboard API, but seems to discard content inserted into it
                if (e.clipboardData && !ios) {
                    e.preventDefault();
                    e.clipboardData.clearData();
                    e.clipboardData.setData("text/plain", lastCopied.join("\n"));
                } else {
                    // Old-fashioned briefly-focus-a-textarea hack
                    var kludge = hiddenTextarea(),
                        te = kludge.firstChild;
                    cm.display.lineSpace.insertBefore(kludge, cm.display.lineSpace.firstChild);
                    te.value = lastCopied.join("\n");
                    var hadFocus = document.activeElement;
                    selectInput(te);
                    setTimeout(function() {
                        cm.display.lineSpace.removeChild(kludge);
                        hadFocus.focus();
                    }, 50);
                }
            }
            on(div, "copy", onCopyCut);
            on(div, "cut", onCopyCut);
        },

        prepareSelection: function() {
            var result = prepareSelection(this.cm, false);
            result.focus = this.cm.state.focused;
            return result;
        },

        showSelection: function(info) {
            if (!info || !this.cm.display.view.length) return;
            if (info.focus) this.showPrimarySelection();
            this.showMultipleSelections(info);
        },

        showPrimarySelection: function() {
            var sel = window.getSelection(),
                prim = this.cm.doc.sel.primary();
            var curAnchor = domToPos(this.cm, sel.anchorNode, sel.anchorOffset);
            var curFocus = domToPos(this.cm, sel.focusNode, sel.focusOffset);
            if (curAnchor && !curAnchor.bad && curFocus && !curFocus.bad &&
                cmp(minPos(curAnchor, curFocus), prim.from()) == 0 &&
                cmp(maxPos(curAnchor, curFocus), prim.to()) == 0)
                return;

            var start = posToDOM(this.cm, prim.from());
            var end = posToDOM(this.cm, prim.to());
            if (!start && !end) return;

            var view = this.cm.display.view;
            var old = sel.rangeCount && sel.getRangeAt(0);
            if (!start) {
                start = {
                    node: view[0].measure.map[2],
                    offset: 0
                };
            } else if (!end) { // FIXME dangerously hacky
                var measure = view[view.length - 1].measure;
                var map = measure.maps ? measure.maps[measure.maps.length - 1] : measure.map;
                end = {
                    node: map[map.length - 1],
                    offset: map[map.length - 2] - map[map.length - 3]
                };
            }

            try {
                var rng = range(start.node, start.offset, end.offset, end.node);
            } catch (e) {} // Our model of the DOM might be outdated, in which case the range we try to set can be impossible
            if (rng) {
                sel.removeAllRanges();
                sel.addRange(rng);
                if (old && sel.anchorNode == null) sel.addRange(old);
                else if (gecko) this.startGracePeriod();
            }
            this.rememberSelection();
        },

        startGracePeriod: function() {
            var input = this;
            clearTimeout(this.gracePeriod);
            this.gracePeriod = setTimeout(function() {
                input.gracePeriod = false;
                if (input.selectionChanged())
                    input.cm.operation(function() {
                        input.cm.curOp.selectionChanged = true;
                    });
            }, 20);
        },

        showMultipleSelections: function(info) {
            removeChildrenAndAdd(this.cm.display.cursorDiv, info.cursors);
            removeChildrenAndAdd(this.cm.display.selectionDiv, info.selection);
        },

        rememberSelection: function() {
            var sel = window.getSelection();
            this.lastAnchorNode = sel.anchorNode;
            this.lastAnchorOffset = sel.anchorOffset;
            this.lastFocusNode = sel.focusNode;
            this.lastFocusOffset = sel.focusOffset;
        },

        selectionInEditor: function() {
            var sel = window.getSelection();
            if (!sel.rangeCount) return false;
            var node = sel.getRangeAt(0).commonAncestorContainer;
            return contains(this.div, node);
        },

        focus: function() {
            if (this.cm.options.readOnly != "nocursor") this.div.focus();
        },
        blur: function() {
            this.div.blur();
        },
        getField: function() {
            return this.div;
        },

        supportsTouch: function() {
            return true;
        },

        receivedFocus: function() {
            var input = this;
            if (this.selectionInEditor())
                this.pollSelection();
            else
                runInOp(this.cm, function() {
                    input.cm.curOp.selectionChanged = true;
                });

            function poll() {
                if (input.cm.state.focused) {
                    input.pollSelection();
                    input.polling.set(input.cm.options.pollInterval, poll);
                }
            }
            this.polling.set(this.cm.options.pollInterval, poll);
        },

        selectionChanged: function() {
            var sel = window.getSelection();
            return sel.anchorNode != this.lastAnchorNode || sel.anchorOffset != this.lastAnchorOffset ||
                sel.focusNode != this.lastFocusNode || sel.focusOffset != this.lastFocusOffset;
        },

        pollSelection: function() {
            if (!this.composing && !this.gracePeriod && this.selectionChanged()) {
                var sel = window.getSelection(),
                    cm = this.cm;
                this.rememberSelection();
                var anchor = domToPos(cm, sel.anchorNode, sel.anchorOffset);
                var head = domToPos(cm, sel.focusNode, sel.focusOffset);
                if (anchor && head) runInOp(cm, function() {
                    setSelection(cm.doc, simpleSelection(anchor, head), sel_dontScroll);
                    if (anchor.bad || head.bad) cm.curOp.selectionChanged = true;
                });
            }
        },

        pollContent: function() {
            var cm = this.cm,
                display = cm.display,
                sel = cm.doc.sel.primary();
            var from = sel.from(),
                to = sel.to();
            if (from.line < display.viewFrom || to.line > display.viewTo - 1) return false;

            var fromIndex;
            if (from.line == display.viewFrom || (fromIndex = findViewIndex(cm, from.line)) == 0) {
                var fromLine = lineNo(display.view[0].line);
                var fromNode = display.view[0].node;
            } else {
                var fromLine = lineNo(display.view[fromIndex].line);
                var fromNode = display.view[fromIndex - 1].node.nextSibling;
            }
            var toIndex = findViewIndex(cm, to.line);
            if (toIndex == display.view.length - 1) {
                var toLine = display.viewTo - 1;
                var toNode = display.view[toIndex].node;
            } else {
                var toLine = lineNo(display.view[toIndex + 1].line) - 1;
                var toNode = display.view[toIndex + 1].node.previousSibling;
            }

            var newText = splitLines(domTextBetween(cm, fromNode, toNode, fromLine, toLine));
            var oldText = getBetween(cm.doc, Pos(fromLine, 0), Pos(toLine, getLine(cm.doc, toLine).text.length));
            while (newText.length > 1 && oldText.length > 1) {
                if (lst(newText) == lst(oldText)) {
                    newText.pop();
                    oldText.pop();
                    toLine--;
                } else if (newText[0] == oldText[0]) {
                    newText.shift();
                    oldText.shift();
                    fromLine++;
                } else break;
            }

            var cutFront = 0,
                cutEnd = 0;
            var newTop = newText[0],
                oldTop = oldText[0],
                maxCutFront = Math.min(newTop.length, oldTop.length);
            while (cutFront < maxCutFront && newTop.charCodeAt(cutFront) == oldTop.charCodeAt(cutFront))
                ++cutFront;
            var newBot = lst(newText),
                oldBot = lst(oldText);
            var maxCutEnd = Math.min(newBot.length - (newText.length == 1 ? cutFront : 0),
                oldBot.length - (oldText.length == 1 ? cutFront : 0));
            while (cutEnd < maxCutEnd &&
                newBot.charCodeAt(newBot.length - cutEnd - 1) == oldBot.charCodeAt(oldBot.length - cutEnd - 1))
                ++cutEnd;

            newText[newText.length - 1] = newBot.slice(0, newBot.length - cutEnd);
            newText[0] = newText[0].slice(cutFront);

            var chFrom = Pos(fromLine, cutFront);
            var chTo = Pos(toLine, oldText.length ? lst(oldText).length - cutEnd : 0);
            if (newText.length > 1 || newText[0] || cmp(chFrom, chTo)) {
                replaceRange(cm.doc, newText, chFrom, chTo, "+input");
                return true;
            }
        },

        ensurePolled: function() {
            this.forceCompositionEnd();
        },
        reset: function() {
            this.forceCompositionEnd();
        },
        forceCompositionEnd: function() {
            if (!this.composing || this.composing.handled) return;
            this.applyComposition(this.composing);
            this.composing.handled = true;
            this.div.blur();
            this.div.focus();
        },
        applyComposition: function(composing) {
            if (composing.data && composing.data != composing.startData)
                operation(this.cm, applyTextInput)(this.cm, composing.data, 0, composing.sel);
        },

        setUneditable: function(node) {
            node.setAttribute("contenteditable", "false");
        },

        onKeyPress: function(e) {
            e.preventDefault();
            operation(this.cm, applyTextInput)(this.cm, String.fromCharCode(e.charCode == null ? e.keyCode : e.charCode), 0);
        },

        onContextMenu: nothing,
        resetPosition: nothing,

        needsContentAttribute: true
    }, ContentEditableInput.prototype);

    function posToDOM(cm, pos) {
        var view = findViewForLine(cm, pos.line);
        if (!view || view.hidden) return null;
        var line = getLine(cm.doc, pos.line);
        var info = mapFromLineView(view, line, pos.line);

        var order = getOrder(line),
            side = "left";
        if (order) {
            var partPos = getBidiPartAt(order, pos.ch);
            side = partPos % 2 ? "right" : "left";
        }
        var result = nodeAndOffsetInLineMap(info.map, pos.ch, side);
        result.offset = result.collapse == "right" ? result.end : result.start;
        return result;
    }

    function badPos(pos, bad) {
        if (bad) pos.bad = true;
        return pos;
    }

    function domToPos(cm, node, offset) {
        var lineNode;
        if (node == cm.display.lineDiv) {
            lineNode = cm.display.lineDiv.childNodes[offset];
            if (!lineNode) return badPos(cm.clipPos(Pos(cm.display.viewTo - 1)), true);
            node = null;
            offset = 0;
        } else {
            for (lineNode = node;; lineNode = lineNode.parentNode) {
                if (!lineNode || lineNode == cm.display.lineDiv) return null;
                if (lineNode.parentNode && lineNode.parentNode == cm.display.lineDiv) break;
            }
        }
        for (var i = 0; i < cm.display.view.length; i++) {
            var lineView = cm.display.view[i];
            if (lineView.node == lineNode)
                return locateNodeInLineView(lineView, node, offset);
        }
    }

    function locateNodeInLineView(lineView, node, offset) {
        var wrapper = lineView.text.firstChild,
            bad = false;
        if (!node || !contains(wrapper, node)) return badPos(Pos(lineNo(lineView.line), 0), true);
        if (node == wrapper) {
            bad = true;
            node = wrapper.childNodes[offset];
            offset = 0;
            if (!node) {
                var line = lineView.rest ? lst(lineView.rest) : lineView.line;
                return badPos(Pos(lineNo(line), line.text.length), bad);
            }
        }

        var textNode = node.nodeType == 3 ? node : null,
            topNode = node;
        if (!textNode && node.childNodes.length == 1 && node.firstChild.nodeType == 3) {
            textNode = node.firstChild;
            if (offset) offset = textNode.nodeValue.length;
        }
        while (topNode.parentNode != wrapper) topNode = topNode.parentNode;
        var measure = lineView.measure,
            maps = measure.maps;

        function find(textNode, topNode, offset) {
            for (var i = -1; i < (maps ? maps.length : 0); i++) {
                var map = i < 0 ? measure.map : maps[i];
                for (var j = 0; j < map.length; j += 3) {
                    var curNode = map[j + 2];
                    if (curNode == textNode || curNode == topNode) {
                        var line = lineNo(i < 0 ? lineView.line : lineView.rest[i]);
                        var ch = map[j] + offset;
                        if (offset < 0 || curNode != textNode) ch = map[j + (offset ? 1 : 0)];
                        return Pos(line, ch);
                    }
                }
            }
        }
        var found = find(textNode, topNode, offset);
        if (found) return badPos(found, bad);

        // FIXME this is all really shaky. might handle the few cases it needs to handle, but likely to cause problems
        for (var after = topNode.nextSibling, dist = textNode ? textNode.nodeValue.length - offset : 0; after; after = after.nextSibling) {
            found = find(after, after.firstChild, 0);
            if (found)
                return badPos(Pos(found.line, found.ch - dist), bad);
            else
                dist += after.textContent.length;
        }
        for (var before = topNode.previousSibling, dist = offset; before; before = before.previousSibling) {
            found = find(before, before.firstChild, -1);
            if (found)
                return badPos(Pos(found.line, found.ch + dist), bad);
            else
                dist += after.textContent.length;
        }
    }

    function domTextBetween(cm, from, to, fromLine, toLine) {
        var text = "",
            closing = false;

        function recognizeMarker(id) {
            return function(marker) {
                return marker.id == id;
            };
        }

        function walk(node) {
            if (node.nodeType == 1) {
                var cmText = node.getAttribute("cm-text");
                if (cmText != null) {
                    if (cmText == "") cmText = node.textContent.replace(/\u200b/g, "");
                    text += cmText;
                    return;
                }
                var markerID = node.getAttribute("cm-marker"),
                    range;
                if (markerID) {
                    var found = cm.findMarks(Pos(fromLine, 0), Pos(toLine + 1, 0), recognizeMarker(+markerID));
                    if (found.length && (range = found[0].find()))
                        text += getBetween(cm.doc, range.from, range.to).join("\n");
                    return;
                }
                if (node.getAttribute("contenteditable") == "false") return;
                for (var i = 0; i < node.childNodes.length; i++)
                    walk(node.childNodes[i]);
                if (/^(pre|div|p)$/i.test(node.nodeName))
                    closing = true;
            } else if (node.nodeType == 3) {
                var val = node.nodeValue;
                if (!val) return;
                if (closing) {
                    text += "\n";
                    closing = false;
                }
                text += val;
            }
        }
        for (;;) {
            walk(from);
            if (from == to) break;
            from = from.nextSibling;
        }
        return text;
    }

    CodeMirror.inputStyles = {
        "textarea": TextareaInput,
        "contenteditable": ContentEditableInput
    };

    // SELECTION / CURSOR

    // Selection objects are immutable. A new one is created every time
    // the selection changes. A selection is one or more non-overlapping
    // (and non-touching) ranges, sorted, and an integer that indicates
    // which one is the primary selection (the one that's scrolled into
    // view, that getCursor returns, etc).
    function Selection(ranges, primIndex) {
        this.ranges = ranges;
        this.primIndex = primIndex;
    }

    Selection.prototype = {
        primary: function() {
            return this.ranges[this.primIndex];
        },
        equals: function(other) {
            if (other == this) return true;
            if (other.primIndex != this.primIndex || other.ranges.length != this.ranges.length) return false;
            for (var i = 0; i < this.ranges.length; i++) {
                var here = this.ranges[i],
                    there = other.ranges[i];
                if (cmp(here.anchor, there.anchor) != 0 || cmp(here.head, there.head) != 0) return false;
            }
            return true;
        },
        deepCopy: function() {
            for (var out = [], i = 0; i < this.ranges.length; i++)
                out[i] = new Range(copyPos(this.ranges[i].anchor), copyPos(this.ranges[i].head));
            return new Selection(out, this.primIndex);
        },
        somethingSelected: function() {
            for (var i = 0; i < this.ranges.length; i++)
                if (!this.ranges[i].empty()) return true;
            return false;
        },
        contains: function(pos, end) {
            if (!end) end = pos;
            for (var i = 0; i < this.ranges.length; i++) {
                var range = this.ranges[i];
                if (cmp(end, range.from()) >= 0 && cmp(pos, range.to()) <= 0)
                    return i;
            }
            return -1;
        }
    };

    function Range(anchor, head) {
        this.anchor = anchor;
        this.head = head;
    }

    Range.prototype = {
        from: function() {
            return minPos(this.anchor, this.head);
        },
        to: function() {
            return maxPos(this.anchor, this.head);
        },
        empty: function() {
            return this.head.line == this.anchor.line && this.head.ch == this.anchor.ch;
        }
    };

    // Take an unsorted, potentially overlapping set of ranges, and
    // build a selection out of it. 'Consumes' ranges array (modifying
    // it).
    function normalizeSelection(ranges, primIndex) {
        var prim = ranges[primIndex];
        ranges.sort(function(a, b) {
            return cmp(a.from(), b.from());
        });
        primIndex = indexOf(ranges, prim);
        for (var i = 1; i < ranges.length; i++) {
            var cur = ranges[i],
                prev = ranges[i - 1];
            if (cmp(prev.to(), cur.from()) >= 0) {
                var from = minPos(prev.from(), cur.from()),
                    to = maxPos(prev.to(), cur.to());
                var inv = prev.empty() ? cur.from() == cur.head : prev.from() == prev.head;
                if (i <= primIndex) --primIndex;
                ranges.splice(--i, 2, new Range(inv ? to : from, inv ? from : to));
            }
        }
        return new Selection(ranges, primIndex);
    }

    function simpleSelection(anchor, head) {
        return new Selection([new Range(anchor, head || anchor)], 0);
    }

    // Most of the external API clips given positions to make sure they
    // actually exist within the document.
    function clipLine(doc, n) {
        return Math.max(doc.first, Math.min(n, doc.first + doc.size - 1));
    }

    function clipPos(doc, pos) {
        if (pos.line < doc.first) return Pos(doc.first, 0);
        var last = doc.first + doc.size - 1;
        if (pos.line > last) return Pos(last, getLine(doc, last).text.length);
        return clipToLen(pos, getLine(doc, pos.line).text.length);
    }

    function clipToLen(pos, linelen) {
        var ch = pos.ch;
        if (ch == null || ch > linelen) return Pos(pos.line, linelen);
        else if (ch < 0) return Pos(pos.line, 0);
        else return pos;
    }

    function isLine(doc, l) {
        return l >= doc.first && l < doc.first + doc.size;
    }

    function clipPosArray(doc, array) {
        for (var out = [], i = 0; i < array.length; i++) out[i] = clipPos(doc, array[i]);
        return out;
    }

    // SELECTION UPDATES

    // The 'scroll' parameter given to many of these indicated whether
    // the new cursor position should be scrolled into view after
    // modifying the selection.

    // If shift is held or the extend flag is set, extends a range to
    // include a given position (and optionally a second position).
    // Otherwise, simply returns the range between the given positions.
    // Used for cursor motion and such.
    function extendRange(doc, range, head, other) {
        if (doc.cm && doc.cm.display.shift || doc.extend) {
            var anchor = range.anchor;
            if (other) {
                var posBefore = cmp(head, anchor) < 0;
                if (posBefore != (cmp(other, anchor) < 0)) {
                    anchor = head;
                    head = other;
                } else if (posBefore != (cmp(head, other) < 0)) {
                    head = other;
                }
            }
            return new Range(anchor, head);
        } else {
            return new Range(other || head, head);
        }
    }

    // Extend the primary selection range, discard the rest.
    function extendSelection(doc, head, other, options) {
        setSelection(doc, new Selection([extendRange(doc, doc.sel.primary(), head, other)], 0), options);
    }

    // Extend all selections (pos is an array of selections with length
    // equal the number of selections)
    function extendSelections(doc, heads, options) {
        for (var out = [], i = 0; i < doc.sel.ranges.length; i++)
            out[i] = extendRange(doc, doc.sel.ranges[i], heads[i], null);
        var newSel = normalizeSelection(out, doc.sel.primIndex);
        setSelection(doc, newSel, options);
    }

    // Updates a single range in the selection.
    function replaceOneSelection(doc, i, range, options) {
        var ranges = doc.sel.ranges.slice(0);
        ranges[i] = range;
        setSelection(doc, normalizeSelection(ranges, doc.sel.primIndex), options);
    }

    // Reset the selection to a single range.
    function setSimpleSelection(doc, anchor, head, options) {
        setSelection(doc, simpleSelection(anchor, head), options);
    }

    // Give beforeSelectionChange handlers a change to influence a
    // selection update.
    function filterSelectionChange(doc, sel) {
        var obj = {
            ranges: sel.ranges,
            update: function(ranges) {
                this.ranges = [];
                for (var i = 0; i < ranges.length; i++)
                    this.ranges[i] = new Range(clipPos(doc, ranges[i].anchor),
                        clipPos(doc, ranges[i].head));
            }
        };
        signal(doc, "beforeSelectionChange", doc, obj);
        if (doc.cm) signal(doc.cm, "beforeSelectionChange", doc.cm, obj);
        if (obj.ranges != sel.ranges) return normalizeSelection(obj.ranges, obj.ranges.length - 1);
        else return sel;
    }

    function setSelectionReplaceHistory(doc, sel, options) {
        var done = doc.history.done,
            last = lst(done);
        if (last && last.ranges) {
            done[done.length - 1] = sel;
            setSelectionNoUndo(doc, sel, options);
        } else {
            setSelection(doc, sel, options);
        }
    }

    // Set a new selection.
    function setSelection(doc, sel, options) {
        setSelectionNoUndo(doc, sel, options);
        addSelectionToHistory(doc, doc.sel, doc.cm ? doc.cm.curOp.id : NaN, options);
    }

    function setSelectionNoUndo(doc, sel, options) {
        if (hasHandler(doc, "beforeSelectionChange") || doc.cm && hasHandler(doc.cm, "beforeSelectionChange"))
            sel = filterSelectionChange(doc, sel);

        var bias = options && options.bias ||
            (cmp(sel.primary().head, doc.sel.primary().head) < 0 ? -1 : 1);
        setSelectionInner(doc, skipAtomicInSelection(doc, sel, bias, true));

        if (!(options && options.scroll === false) && doc.cm)
            ensureCursorVisible(doc.cm);
    }

    function setSelectionInner(doc, sel) {
        if (sel.equals(doc.sel)) return;

        doc.sel = sel;

        if (doc.cm) {
            doc.cm.curOp.updateInput = doc.cm.curOp.selectionChanged = true;
            signalCursorActivity(doc.cm);
        }
        signalLater(doc, "cursorActivity", doc);
    }

    // Verify that the selection does not partially select any atomic
    // marked ranges.
    function reCheckSelection(doc) {
        setSelectionInner(doc, skipAtomicInSelection(doc, doc.sel, null, false), sel_dontScroll);
    }

    // Return a selection that does not partially select any atomic
    // ranges.
    function skipAtomicInSelection(doc, sel, bias, mayClear) {
        var out;
        for (var i = 0; i < sel.ranges.length; i++) {
            var range = sel.ranges[i];
            var newAnchor = skipAtomic(doc, range.anchor, bias, mayClear);
            var newHead = skipAtomic(doc, range.head, bias, mayClear);
            if (out || newAnchor != range.anchor || newHead != range.head) {
                if (!out) out = sel.ranges.slice(0, i);
                out[i] = new Range(newAnchor, newHead);
            }
        }
        return out ? normalizeSelection(out, sel.primIndex) : sel;
    }

    // Ensure a given position is not inside an atomic range.
    function skipAtomic(doc, pos, bias, mayClear) {
        var flipped = false,
            curPos = pos;
        var dir = bias || 1;
        doc.cantEdit = false;
        search: for (;;) {
            var line = getLine(doc, curPos.line);
            if (line.markedSpans) {
                for (var i = 0; i < line.markedSpans.length; ++i) {
                    var sp = line.markedSpans[i],
                        m = sp.marker;
                    if ((sp.from == null || (m.inclusiveLeft ? sp.from <= curPos.ch : sp.from < curPos.ch)) &&
                        (sp.to == null || (m.inclusiveRight ? sp.to >= curPos.ch : sp.to > curPos.ch))) {
                        if (mayClear) {
                            signal(m, "beforeCursorEnter");
                            if (m.explicitlyCleared) {
                                if (!line.markedSpans) break;
                                else {
                                    --i;
                                    continue;
                                }
                            }
                        }
                        if (!m.atomic) continue;
                        var newPos = m.find(dir < 0 ? -1 : 1);
                        if (cmp(newPos, curPos) == 0) {
                            newPos.ch += dir;
                            if (newPos.ch < 0) {
                                if (newPos.line > doc.first) newPos = clipPos(doc, Pos(newPos.line - 1));
                                else newPos = null;
                            } else if (newPos.ch > line.text.length) {
                                if (newPos.line < doc.first + doc.size - 1) newPos = Pos(newPos.line + 1, 0);
                                else newPos = null;
                            }
                            if (!newPos) {
                                if (flipped) {
                                    // Driven in a corner -- no valid cursor position found at all
                                    // -- try again *with* clearing, if we didn't already
                                    if (!mayClear) return skipAtomic(doc, pos, bias, true);
                                    // Otherwise, turn off editing until further notice, and return the start of the doc
                                    doc.cantEdit = true;
                                    return Pos(doc.first, 0);
                                }
                                flipped = true;
                                newPos = pos;
                                dir = -dir;
                            }
                        }
                        curPos = newPos;
                        continue search;
                    }
                }
            }
            return curPos;
        }
    }

    // SELECTION DRAWING

    function updateSelection(cm) {
        cm.display.input.showSelection(cm.display.input.prepareSelection());
    }

    function prepareSelection(cm, primary) {
        var doc = cm.doc,
            result = {};
        var curFragment = result.cursors = document.createDocumentFragment();
        var selFragment = result.selection = document.createDocumentFragment();

        for (var i = 0; i < doc.sel.ranges.length; i++) {
            if (primary === false && i == doc.sel.primIndex) continue;
            var range = doc.sel.ranges[i];
            var collapsed = range.empty();
            if (collapsed || cm.options.showCursorWhenSelecting)
                drawSelectionCursor(cm, range, curFragment);
            if (!collapsed)
                drawSelectionRange(cm, range, selFragment);
        }
        return result;
    }

    // Draws a cursor for the given range
    function drawSelectionCursor(cm, range, output) {
        var pos = cursorCoords(cm, range.head, "div", null, null, !cm.options.singleCursorHeightPerLine);

        var cursor = output.appendChild(elt("div", "\u00a0", "CodeMirror-cursor"));
        cursor.style.left = pos.left + "px";
        cursor.style.top = pos.top + "px";
        cursor.style.height = Math.max(0, pos.bottom - pos.top) * cm.options.cursorHeight + "px";

        if (pos.other) {
            // Secondary cursor, shown when on a 'jump' in bi-directional text
            var otherCursor = output.appendChild(elt("div", "\u00a0", "CodeMirror-cursor CodeMirror-secondarycursor"));
            otherCursor.style.display = "";
            otherCursor.style.left = pos.other.left + "px";
            otherCursor.style.top = pos.other.top + "px";
            otherCursor.style.height = (pos.other.bottom - pos.other.top) * .85 + "px";
        }
    }

    // Draws the given range as a highlighted selection
    function drawSelectionRange(cm, range, output) {
        var display = cm.display,
            doc = cm.doc;
        var fragment = document.createDocumentFragment();
        var padding = paddingH(cm.display),
            leftSide = padding.left;
        var rightSide = Math.max(display.sizerWidth, displayWidth(cm) - display.sizer.offsetLeft) - padding.right;

        function add(left, top, width, bottom) {
            if (top < 0) top = 0;
            top = Math.round(top);
            bottom = Math.round(bottom);
            fragment.appendChild(elt("div", null, "CodeMirror-selected", "position: absolute; left: " + left +
                "px; top: " + top + "px; width: " + (width == null ? rightSide - left : width) +
                "px; height: " + (bottom - top) + "px"));
        }

        function drawForLine(line, fromArg, toArg) {
            var lineObj = getLine(doc, line);
            var lineLen = lineObj.text.length;
            var start, end;

            function coords(ch, bias) {
                return charCoords(cm, Pos(line, ch), "div", lineObj, bias);
            }

            iterateBidiSections(getOrder(lineObj), fromArg || 0, toArg == null ? lineLen : toArg, function(from, to, dir) {
                var leftPos = coords(from, "left"),
                    rightPos, left, right;
                if (from == to) {
                    rightPos = leftPos;
                    left = right = leftPos.left;
                } else {
                    rightPos = coords(to - 1, "right");
                    if (dir == "rtl") {
                        var tmp = leftPos;
                        leftPos = rightPos;
                        rightPos = tmp;
                    }
                    left = leftPos.left;
                    right = rightPos.right;
                }
                if (fromArg == null && from == 0) left = leftSide;
                if (rightPos.top - leftPos.top > 3) { // Different lines, draw top part
                    add(left, leftPos.top, null, leftPos.bottom);
                    left = leftSide;
                    if (leftPos.bottom < rightPos.top) add(left, leftPos.bottom, null, rightPos.top);
                }
                if (toArg == null && to == lineLen) right = rightSide;
                if (!start || leftPos.top < start.top || leftPos.top == start.top && leftPos.left < start.left)
                    start = leftPos;
                if (!end || rightPos.bottom > end.bottom || rightPos.bottom == end.bottom && rightPos.right > end.right)
                    end = rightPos;
                if (left < leftSide + 1) left = leftSide;
                add(left, rightPos.top, right - left, rightPos.bottom);
            });
            return {
                start: start,
                end: end
            };
        }

        var sFrom = range.from(),
            sTo = range.to();
        if (sFrom.line == sTo.line) {
            drawForLine(sFrom.line, sFrom.ch, sTo.ch);
        } else {
            var fromLine = getLine(doc, sFrom.line),
                toLine = getLine(doc, sTo.line);
            var singleVLine = visualLine(fromLine) == visualLine(toLine);
            var leftEnd = drawForLine(sFrom.line, sFrom.ch, singleVLine ? fromLine.text.length + 1 : null).end;
            var rightStart = drawForLine(sTo.line, singleVLine ? 0 : null, sTo.ch).start;
            if (singleVLine) {
                if (leftEnd.top < rightStart.top - 2) {
                    add(leftEnd.right, leftEnd.top, null, leftEnd.bottom);
                    add(leftSide, rightStart.top, rightStart.left, rightStart.bottom);
                } else {
                    add(leftEnd.right, leftEnd.top, rightStart.left - leftEnd.right, leftEnd.bottom);
                }
            }
            if (leftEnd.bottom < rightStart.top)
                add(leftSide, leftEnd.bottom, null, rightStart.top);
        }

        output.appendChild(fragment);
    }

    // Cursor-blinking
    function restartBlink(cm) {
        if (!cm.state.focused) return;
        var display = cm.display;
        clearInterval(display.blinker);
        var on = true;
        display.cursorDiv.style.visibility = "";
        if (cm.options.cursorBlinkRate > 0)
            display.blinker = setInterval(function() {
                display.cursorDiv.style.visibility = (on = !on) ? "" : "hidden";
            }, cm.options.cursorBlinkRate);
        else if (cm.options.cursorBlinkRate < 0)
            display.cursorDiv.style.visibility = "hidden";
    }

    // HIGHLIGHT WORKER

    function startWorker(cm, time) {
        if (cm.doc.mode.startState && cm.doc.frontier < cm.display.viewTo)
            cm.state.highlight.set(time, bind(highlightWorker, cm));
    }

    function highlightWorker(cm) {
        var doc = cm.doc;
        if (doc.frontier < doc.first) doc.frontier = doc.first;
        if (doc.frontier >= cm.display.viewTo) return;
        var end = +new Date + cm.options.workTime;
        var state = copyState(doc.mode, getStateBefore(cm, doc.frontier));
        var changedLines = [];

        doc.iter(doc.frontier, Math.min(doc.first + doc.size, cm.display.viewTo + 500), function(line) {
            if (doc.frontier >= cm.display.viewFrom) { // Visible
                var oldStyles = line.styles;
                var highlighted = highlightLine(cm, line, state, true);
                line.styles = highlighted.styles;
                var oldCls = line.styleClasses,
                    newCls = highlighted.classes;
                if (newCls) line.styleClasses = newCls;
                else if (oldCls) line.styleClasses = null;
                var ischange = !oldStyles || oldStyles.length != line.styles.length ||
                    oldCls != newCls && (!oldCls || !newCls || oldCls.bgClass != newCls.bgClass || oldCls.textClass != newCls.textClass);
                for (var i = 0; !ischange && i < oldStyles.length; ++i) ischange = oldStyles[i] != line.styles[i];
                if (ischange) changedLines.push(doc.frontier);
                line.stateAfter = copyState(doc.mode, state);
            } else {
                processLine(cm, line.text, state);
                line.stateAfter = doc.frontier % 5 == 0 ? copyState(doc.mode, state) : null;
            }
            ++doc.frontier;
            if (+new Date > end) {
                startWorker(cm, cm.options.workDelay);
                return true;
            }
        });
        if (changedLines.length) runInOp(cm, function() {
            for (var i = 0; i < changedLines.length; i++)
                regLineChange(cm, changedLines[i], "text");
        });
    }

    // Finds the line to start with when starting a parse. Tries to
    // find a line with a stateAfter, so that it can start with a
    // valid state. If that fails, it returns the line with the
    // smallest indentation, which tends to need the least context to
    // parse correctly.
    function findStartLine(cm, n, precise) {
        var minindent, minline, doc = cm.doc;
        var lim = precise ? -1 : n - (cm.doc.mode.innerMode ? 1000 : 100);
        for (var search = n; search > lim; --search) {
            if (search <= doc.first) return doc.first;
            var line = getLine(doc, search - 1);
            if (line.stateAfter && (!precise || search <= doc.frontier)) return search;
            var indented = countColumn(line.text, null, cm.options.tabSize);
            if (minline == null || minindent > indented) {
                minline = search - 1;
                minindent = indented;
            }
        }
        return minline;
    }

    function getStateBefore(cm, n, precise) {
        var doc = cm.doc,
            display = cm.display;
        if (!doc.mode.startState) return true;
        var pos = findStartLine(cm, n, precise),
            state = pos > doc.first && getLine(doc, pos - 1).stateAfter;
        if (!state) state = startState(doc.mode);
        else state = copyState(doc.mode, state);
        doc.iter(pos, n, function(line) {
            processLine(cm, line.text, state);
            var save = pos == n - 1 || pos % 5 == 0 || pos >= display.viewFrom && pos < display.viewTo;
            line.stateAfter = save ? copyState(doc.mode, state) : null;
            ++pos;
        });
        if (precise) doc.frontier = pos;
        return state;
    }

    // POSITION MEASUREMENT

    function paddingTop(display) {
        return display.lineSpace.offsetTop;
    }

    function paddingVert(display) {
        return display.mover.offsetHeight - display.lineSpace.offsetHeight;
    }

    function paddingH(display) {
        if (display.cachedPaddingH) return display.cachedPaddingH;
        var e = removeChildrenAndAdd(display.measure, elt("pre", "x"));
        var style = window.getComputedStyle ? window.getComputedStyle(e) : e.currentStyle;
        var data = {
            left: parseInt(style.paddingLeft),
            right: parseInt(style.paddingRight)
        };
        if (!isNaN(data.left) && !isNaN(data.right)) display.cachedPaddingH = data;
        return data;
    }

    function scrollGap(cm) {
        return scrollerGap - cm.display.nativeBarWidth;
    }

    function displayWidth(cm) {
        return cm.display.scroller.clientWidth - scrollGap(cm) - cm.display.barWidth;
    }

    function displayHeight(cm) {
        return cm.display.scroller.clientHeight - scrollGap(cm) - cm.display.barHeight;
    }

    // Ensure the lineView.wrapping.heights array is populated. This is
    // an array of bottom offsets for the lines that make up a drawn
    // line. When lineWrapping is on, there might be more than one
    // height.
    function ensureLineHeights(cm, lineView, rect) {
        var wrapping = cm.options.lineWrapping;
        var curWidth = wrapping && displayWidth(cm);
        if (!lineView.measure.heights || wrapping && lineView.measure.width != curWidth) {
            var heights = lineView.measure.heights = [];
            if (wrapping) {
                lineView.measure.width = curWidth;
                var rects = lineView.text.firstChild.getClientRects();
                for (var i = 0; i < rects.length - 1; i++) {
                    var cur = rects[i],
                        next = rects[i + 1];
                    if (Math.abs(cur.bottom - next.bottom) > 2)
                        heights.push((cur.bottom + next.top) / 2 - rect.top);
                }
            }
            heights.push(rect.bottom - rect.top);
        }
    }

    // Find a line map (mapping character offsets to text nodes) and a
    // measurement cache for the given line number. (A line view might
    // contain multiple lines when collapsed ranges are present.)
    function mapFromLineView(lineView, line, lineN) {
        if (lineView.line == line)
            return {
                map: lineView.measure.map,
                cache: lineView.measure.cache
            };
        for (var i = 0; i < lineView.rest.length; i++)
            if (lineView.rest[i] == line)
                return {
                    map: lineView.measure.maps[i],
                    cache: lineView.measure.caches[i]
                };
        for (var i = 0; i < lineView.rest.length; i++)
            if (lineNo(lineView.rest[i]) > lineN)
                return {
                    map: lineView.measure.maps[i],
                    cache: lineView.measure.caches[i],
                    before: true
                };
    }

    // Render a line into the hidden node display.externalMeasured. Used
    // when measurement is needed for a line that's not in the viewport.
    function updateExternalMeasurement(cm, line) {
        line = visualLine(line);
        var lineN = lineNo(line);
        var view = cm.display.externalMeasured = new LineView(cm.doc, line, lineN);
        view.lineN = lineN;
        var built = view.built = buildLineContent(cm, view);
        view.text = built.pre;
        removeChildrenAndAdd(cm.display.lineMeasure, built.pre);
        return view;
    }

    // Get a {top, bottom, left, right} box (in line-local coordinates)
    // for a given character.
    function measureChar(cm, line, ch, bias) {
        return measureCharPrepared(cm, prepareMeasureForLine(cm, line), ch, bias);
    }

    // Find a line view that corresponds to the given line number.
    function findViewForLine(cm, lineN) {
        if (lineN >= cm.display.viewFrom && lineN < cm.display.viewTo)
            return cm.display.view[findViewIndex(cm, lineN)];
        var ext = cm.display.externalMeasured;
        if (ext && lineN >= ext.lineN && lineN < ext.lineN + ext.size)
            return ext;
    }

    // Measurement can be split in two steps, the set-up work that
    // applies to the whole line, and the measurement of the actual
    // character. Functions like coordsChar, that need to do a lot of
    // measurements in a row, can thus ensure that the set-up work is
    // only done once.
    function prepareMeasureForLine(cm, line) {
        var lineN = lineNo(line);
        var view = findViewForLine(cm, lineN);
        if (view && !view.text)
            view = null;
        else if (view && view.changes)
            updateLineForChanges(cm, view, lineN, getDimensions(cm));
        if (!view)
            view = updateExternalMeasurement(cm, line);

        var info = mapFromLineView(view, line, lineN);
        return {
            line: line,
            view: view,
            rect: null,
            map: info.map,
            cache: info.cache,
            before: info.before,
            hasHeights: false
        };
    }

    // Given a prepared measurement object, measures the position of an
    // actual character (or fetches it from the cache).
    function measureCharPrepared(cm, prepared, ch, bias, varHeight) {
        if (prepared.before) ch = -1;
        var key = ch + (bias || ""),
            found;
        if (prepared.cache.hasOwnProperty(key)) {
            found = prepared.cache[key];
        } else {
            if (!prepared.rect)
                prepared.rect = prepared.view.text.getBoundingClientRect();
            if (!prepared.hasHeights) {
                ensureLineHeights(cm, prepared.view, prepared.rect);
                prepared.hasHeights = true;
            }
            found = measureCharInner(cm, prepared, ch, bias);
            if (!found.bogus) prepared.cache[key] = found;
        }
        return {
            left: found.left,
            right: found.right,
            top: varHeight ? found.rtop : found.top,
            bottom: varHeight ? found.rbottom : found.bottom
        };
    }

    var nullRect = {
        left: 0,
        right: 0,
        top: 0,
        bottom: 0
    };

    function nodeAndOffsetInLineMap(map, ch, bias) {
        var node, start, end, collapse;
        // First, search the line map for the text node corresponding to,
        // or closest to, the target character.
        for (var i = 0; i < map.length; i += 3) {
            var mStart = map[i],
                mEnd = map[i + 1];
            if (ch < mStart) {
                start = 0;
                end = 1;
                collapse = "left";
            } else if (ch < mEnd) {
                start = ch - mStart;
                end = start + 1;
            } else if (i == map.length - 3 || ch == mEnd && map[i + 3] > ch) {
                end = mEnd - mStart;
                start = end - 1;
                if (ch >= mEnd) collapse = "right";
            }
            if (start != null) {
                node = map[i + 2];
                if (mStart == mEnd && bias == (node.insertLeft ? "left" : "right"))
                    collapse = bias;
                if (bias == "left" && start == 0)
                    while (i && map[i - 2] == map[i - 3] && map[i - 1].insertLeft) {
                        node = map[(i -= 3) + 2];
                        collapse = "left";
                    }
                if (bias == "right" && start == mEnd - mStart)
                    while (i < map.length - 3 && map[i + 3] == map[i + 4] && !map[i + 5].insertLeft) {
                        node = map[(i += 3) + 2];
                        collapse = "right";
                    }
                break;
            }
        }
        return {
            node: node,
            start: start,
            end: end,
            collapse: collapse,
            coverStart: mStart,
            coverEnd: mEnd
        };
    }

    function measureCharInner(cm, prepared, ch, bias) {
        var place = nodeAndOffsetInLineMap(prepared.map, ch, bias);
        var node = place.node,
            start = place.start,
            end = place.end,
            collapse = place.collapse;

        var rect;
        if (node.nodeType == 3) { // If it is a text node, use a range to retrieve the coordinates.
            for (var i = 0; i < 4; i++) { // Retry a maximum of 4 times when nonsense rectangles are returned
                while (start && isExtendingChar(prepared.line.text.charAt(place.coverStart + start))) --start;
                while (place.coverStart + end < place.coverEnd && isExtendingChar(prepared.line.text.charAt(place.coverStart + end))) ++end;
                if (ie && ie_version < 9 && start == 0 && end == place.coverEnd - place.coverStart) {
                    rect = node.parentNode.getBoundingClientRect();
                } else if (ie && cm.options.lineWrapping) {
                    var rects = range(node, start, end).getClientRects();
                    if (rects.length)
                        rect = rects[bias == "right" ? rects.length - 1 : 0];
                    else
                        rect = nullRect;
                } else {
                    rect = range(node, start, end).getBoundingClientRect() || nullRect;
                }
                if (rect.left || rect.right || start == 0) break;
                end = start;
                start = start - 1;
                collapse = "right";
            }
            if (ie && ie_version < 11) rect = maybeUpdateRectForZooming(cm.display.measure, rect);
        } else { // If it is a widget, simply get the box for the whole widget.
            if (start > 0) collapse = bias = "right";
            var rects;
            if (cm.options.lineWrapping && (rects = node.getClientRects()).length > 1)
                rect = rects[bias == "right" ? rects.length - 1 : 0];
            else
                rect = node.getBoundingClientRect();
        }
        if (ie && ie_version < 9 && !start && (!rect || !rect.left && !rect.right)) {
            var rSpan = node.parentNode.getClientRects()[0];
            if (rSpan)
                rect = {
                    left: rSpan.left,
                    right: rSpan.left + charWidth(cm.display),
                    top: rSpan.top,
                    bottom: rSpan.bottom
                };
            else
                rect = nullRect;
        }

        var rtop = rect.top - prepared.rect.top,
            rbot = rect.bottom - prepared.rect.top;
        var mid = (rtop + rbot) / 2;
        var heights = prepared.view.measure.heights;
        for (var i = 0; i < heights.length - 1; i++)
            if (mid < heights[i]) break;
        var top = i ? heights[i - 1] : 0,
            bot = heights[i];
        var result = {
            left: (collapse == "right" ? rect.right : rect.left) - prepared.rect.left,
            right: (collapse == "left" ? rect.left : rect.right) - prepared.rect.left,
            top: top,
            bottom: bot
        };
        if (!rect.left && !rect.right) result.bogus = true;
        if (!cm.options.singleCursorHeightPerLine) {
            result.rtop = rtop;
            result.rbottom = rbot;
        }

        return result;
    }

    // Work around problem with bounding client rects on ranges being
    // returned incorrectly when zoomed on IE10 and below.
    function maybeUpdateRectForZooming(measure, rect) {
        if (!window.screen || screen.logicalXDPI == null ||
            screen.logicalXDPI == screen.deviceXDPI || !hasBadZoomedRects(measure))
            return rect;
        var scaleX = screen.logicalXDPI / screen.deviceXDPI;
        var scaleY = screen.logicalYDPI / screen.deviceYDPI;
        return {
            left: rect.left * scaleX,
            right: rect.right * scaleX,
            top: rect.top * scaleY,
            bottom: rect.bottom * scaleY
        };
    }

    function clearLineMeasurementCacheFor(lineView) {
        if (lineView.measure) {
            lineView.measure.cache = {};
            lineView.measure.heights = null;
            if (lineView.rest)
                for (var i = 0; i < lineView.rest.length; i++)
                    lineView.measure.caches[i] = {};
        }
    }

    function clearLineMeasurementCache(cm) {
        cm.display.externalMeasure = null;
        removeChildren(cm.display.lineMeasure);
        for (var i = 0; i < cm.display.view.length; i++)
            clearLineMeasurementCacheFor(cm.display.view[i]);
    }

    function clearCaches(cm) {
        clearLineMeasurementCache(cm);
        cm.display.cachedCharWidth = cm.display.cachedTextHeight = cm.display.cachedPaddingH = null;
        if (!cm.options.lineWrapping) cm.display.maxLineChanged = true;
        cm.display.lineNumChars = null;
    }

    function pageScrollX() {
        return window.pageXOffset || (document.documentElement || document.body).scrollLeft;
    }

    function pageScrollY() {
        return window.pageYOffset || (document.documentElement || document.body).scrollTop;
    }

    // Converts a {top, bottom, left, right} box from line-local
    // coordinates into another coordinate system. Context may be one of
    // "line", "div" (display.lineDiv), "local"/null (editor), "window",
    // or "page".
    function intoCoordSystem(cm, lineObj, rect, context) {
        if (lineObj.widgets)
            for (var i = 0; i < lineObj.widgets.length; ++i)
                if (lineObj.widgets[i].above) {
                    var size = widgetHeight(lineObj.widgets[i]);
                    rect.top += size;
                    rect.bottom += size;
                }
        if (context == "line") return rect;
        if (!context) context = "local";
        var yOff = heightAtLine(lineObj);
        if (context == "local") yOff += paddingTop(cm.display);
        else yOff -= cm.display.viewOffset;
        if (context == "page" || context == "window") {
            var lOff = cm.display.lineSpace.getBoundingClientRect();
            yOff += lOff.top + (context == "window" ? 0 : pageScrollY());
            var xOff = lOff.left + (context == "window" ? 0 : pageScrollX());
            rect.left += xOff;
            rect.right += xOff;
        }
        rect.top += yOff;
        rect.bottom += yOff;
        return rect;
    }

    // Coverts a box from "div" coords to another coordinate system.
    // Context may be "window", "page", "div", or "local"/null.
    function fromCoordSystem(cm, coords, context) {
        if (context == "div") return coords;
        var left = coords.left,
            top = coords.top;
        // First move into "page" coordinate system
        if (context == "page") {
            left -= pageScrollX();
            top -= pageScrollY();
        } else if (context == "local" || !context) {
            var localBox = cm.display.sizer.getBoundingClientRect();
            left += localBox.left;
            top += localBox.top;
        }

        var lineSpaceBox = cm.display.lineSpace.getBoundingClientRect();
        return {
            left: left - lineSpaceBox.left,
            top: top - lineSpaceBox.top
        };
    }

    function charCoords(cm, pos, context, lineObj, bias) {
        if (!lineObj) lineObj = getLine(cm.doc, pos.line);
        return intoCoordSystem(cm, lineObj, measureChar(cm, lineObj, pos.ch, bias), context);
    }

    // Returns a box for a given cursor position, which may have an
    // 'other' property containing the position of the secondary cursor
    // on a bidi boundary.
    function cursorCoords(cm, pos, context, lineObj, preparedMeasure, varHeight) {
        lineObj = lineObj || getLine(cm.doc, pos.line);
        if (!preparedMeasure) preparedMeasure = prepareMeasureForLine(cm, lineObj);

        function get(ch, right) {
            var m = measureCharPrepared(cm, preparedMeasure, ch, right ? "right" : "left", varHeight);
            if (right) m.left = m.right;
            else m.right = m.left;
            return intoCoordSystem(cm, lineObj, m, context);
        }

        function getBidi(ch, partPos) {
            var part = order[partPos],
                right = part.level % 2;
            if (ch == bidiLeft(part) && partPos && part.level < order[partPos - 1].level) {
                part = order[--partPos];
                ch = bidiRight(part) - (part.level % 2 ? 0 : 1);
                right = true;
            } else if (ch == bidiRight(part) && partPos < order.length - 1 && part.level < order[partPos + 1].level) {
                part = order[++partPos];
                ch = bidiLeft(part) - part.level % 2;
                right = false;
            }
            if (right && ch == part.to && ch > part.from) return get(ch - 1);
            return get(ch, right);
        }
        var order = getOrder(lineObj),
            ch = pos.ch;
        if (!order) return get(ch);
        var partPos = getBidiPartAt(order, ch);
        var val = getBidi(ch, partPos);
        if (bidiOther != null) val.other = getBidi(ch, bidiOther);
        return val;
    }

    // Used to cheaply estimate the coordinates for a position. Used for
    // intermediate scroll updates.
    function estimateCoords(cm, pos) {
        var left = 0,
            pos = clipPos(cm.doc, pos);
        if (!cm.options.lineWrapping) left = charWidth(cm.display) * pos.ch;
        var lineObj = getLine(cm.doc, pos.line);
        var top = heightAtLine(lineObj) + paddingTop(cm.display);
        return {
            left: left,
            right: left,
            top: top,
            bottom: top + lineObj.height
        };
    }

    // Positions returned by coordsChar contain some extra information.
    // xRel is the relative x position of the input coordinates compared
    // to the found position (so xRel > 0 means the coordinates are to
    // the right of the character position, for example). When outside
    // is true, that means the coordinates lie outside the line's
    // vertical range.
    function PosWithInfo(line, ch, outside, xRel) {
        var pos = Pos(line, ch);
        pos.xRel = xRel;
        if (outside) pos.outside = true;
        return pos;
    }

    // Compute the character position closest to the given coordinates.
    // Input must be lineSpace-local ("div" coordinate system).
    function coordsChar(cm, x, y) {
        var doc = cm.doc;
        y += cm.display.viewOffset;
        if (y < 0) return PosWithInfo(doc.first, 0, true, -1);
        var lineN = lineAtHeight(doc, y),
            last = doc.first + doc.size - 1;
        if (lineN > last)
            return PosWithInfo(doc.first + doc.size - 1, getLine(doc, last).text.length, true, 1);
        if (x < 0) x = 0;

        var lineObj = getLine(doc, lineN);
        for (;;) {
            var found = coordsCharInner(cm, lineObj, lineN, x, y);
            var merged = collapsedSpanAtEnd(lineObj);
            var mergedPos = merged && merged.find(0, true);
            if (merged && (found.ch > mergedPos.from.ch || found.ch == mergedPos.from.ch && found.xRel > 0))
                lineN = lineNo(lineObj = mergedPos.to.line);
            else
                return found;
        }
    }

    function coordsCharInner(cm, lineObj, lineNo, x, y) {
        var innerOff = y - heightAtLine(lineObj);
        var wrongLine = false,
            adjust = 2 * cm.display.wrapper.clientWidth;
        var preparedMeasure = prepareMeasureForLine(cm, lineObj);

        function getX(ch) {
            var sp = cursorCoords(cm, Pos(lineNo, ch), "line", lineObj, preparedMeasure);
            wrongLine = true;
            if (innerOff > sp.bottom) return sp.left - adjust;
            else if (innerOff < sp.top) return sp.left + adjust;
            else wrongLine = false;
            return sp.left;
        }

        var bidi = getOrder(lineObj),
            dist = lineObj.text.length;
        var from = lineLeft(lineObj),
            to = lineRight(lineObj);
        var fromX = getX(from),
            fromOutside = wrongLine,
            toX = getX(to),
            toOutside = wrongLine;

        if (x > toX) return PosWithInfo(lineNo, to, toOutside, 1);
        // Do a binary search between these bounds.
        for (;;) {
            if (bidi ? to == from || to == moveVisually(lineObj, from, 1) : to - from <= 1) {
                var ch = x < fromX || x - fromX <= toX - x ? from : to;
                var xDiff = x - (ch == from ? fromX : toX);
                while (isExtendingChar(lineObj.text.charAt(ch))) ++ch;
                var pos = PosWithInfo(lineNo, ch, ch == from ? fromOutside : toOutside,
                    xDiff < -1 ? -1 : xDiff > 1 ? 1 : 0);
                return pos;
            }
            var step = Math.ceil(dist / 2),
                middle = from + step;
            if (bidi) {
                middle = from;
                for (var i = 0; i < step; ++i) middle = moveVisually(lineObj, middle, 1);
            }
            var middleX = getX(middle);
            if (middleX > x) {
                to = middle;
                toX = middleX;
                if (toOutside = wrongLine) toX += 1000;
                dist = step;
            } else {
                from = middle;
                fromX = middleX;
                fromOutside = wrongLine;
                dist -= step;
            }
        }
    }

    var measureText;
    // Compute the default text height.
    function textHeight(display) {
        if (display.cachedTextHeight != null) return display.cachedTextHeight;
        if (measureText == null) {
            measureText = elt("pre");
            // Measure a bunch of lines, for browsers that compute
            // fractional heights.
            for (var i = 0; i < 49; ++i) {
                measureText.appendChild(document.createTextNode("x"));
                measureText.appendChild(elt("br"));
            }
            measureText.appendChild(document.createTextNode("x"));
        }
        removeChildrenAndAdd(display.measure, measureText);
        var height = measureText.offsetHeight / 50;
        if (height > 3) display.cachedTextHeight = height;
        removeChildren(display.measure);
        return height || 1;
    }

    // Compute the default character width.
    function charWidth(display) {
        if (display.cachedCharWidth != null) return display.cachedCharWidth;
        var anchor = elt("span", "xxxxxxxxxx");
        var pre = elt("pre", [anchor]);
        removeChildrenAndAdd(display.measure, pre);
        var rect = anchor.getBoundingClientRect(),
            width = (rect.right - rect.left) / 10;
        if (width > 2) display.cachedCharWidth = width;
        return width || 10;
    }

    // OPERATIONS

    // Operations are used to wrap a series of changes to the editor
    // state in such a way that each change won't have to update the
    // cursor and display (which would be awkward, slow, and
    // error-prone). Instead, display updates are batched and then all
    // combined and executed at once.

    var operationGroup = null;

    var nextOpId = 0;
    // Start a new operation.
    function startOperation(cm) {
        cm.curOp = {
            cm: cm,
            viewChanged: false, // Flag that indicates that lines might need to be redrawn
            startHeight: cm.doc.height, // Used to detect need to update scrollbar
            forceUpdate: false, // Used to force a redraw
            updateInput: null, // Whether to reset the input textarea
            typing: false, // Whether this reset should be careful to leave existing text (for compositing)
            changeObjs: null, // Accumulated changes, for firing change events
            cursorActivityHandlers: null, // Set of handlers to fire cursorActivity on
            cursorActivityCalled: 0, // Tracks which cursorActivity handlers have been called already
            selectionChanged: false, // Whether the selection needs to be redrawn
            updateMaxLine: false, // Set when the widest line needs to be determined anew
            scrollLeft: null,
            scrollTop: null, // Intermediate scroll position, not pushed to DOM yet
            scrollToPos: null, // Used to scroll to a specific position
            focus: false,
            id: ++nextOpId // Unique ID
        };
        if (operationGroup) {
            operationGroup.ops.push(cm.curOp);
        } else {
            cm.curOp.ownsGroup = operationGroup = {
                ops: [cm.curOp],
                delayedCallbacks: []
            };
        }
    }

    function fireCallbacksForOps(group) {
        // Calls delayed callbacks and cursorActivity handlers until no
        // new ones appear
        var callbacks = group.delayedCallbacks,
            i = 0;
        do {
            for (; i < callbacks.length; i++)
                callbacks[i]();
            for (var j = 0; j < group.ops.length; j++) {
                var op = group.ops[j];
                if (op.cursorActivityHandlers)
                    while (op.cursorActivityCalled < op.cursorActivityHandlers.length)
                        op.cursorActivityHandlers[op.cursorActivityCalled++](op.cm);
            }
        } while (i < callbacks.length);
    }

    // Finish an operation, updating the display and signalling delayed events
    function endOperation(cm) {
        var op = cm.curOp,
            group = op.ownsGroup;
        if (!group) return;

        try {
            fireCallbacksForOps(group);
        } finally {
            operationGroup = null;
            for (var i = 0; i < group.ops.length; i++)
                group.ops[i].cm.curOp = null;
            endOperations(group);
        }
    }

    // The DOM updates done when an operation finishes are batched so
    // that the minimum number of relayouts are required.
    function endOperations(group) {
        var ops = group.ops;
        for (var i = 0; i < ops.length; i++) // Read DOM
            endOperation_R1(ops[i]);
        for (var i = 0; i < ops.length; i++) // Write DOM (maybe)
            endOperation_W1(ops[i]);
        for (var i = 0; i < ops.length; i++) // Read DOM
            endOperation_R2(ops[i]);
        for (var i = 0; i < ops.length; i++) // Write DOM (maybe)
            endOperation_W2(ops[i]);
        for (var i = 0; i < ops.length; i++) // Read DOM
            endOperation_finish(ops[i]);
    }

    function endOperation_R1(op) {
        var cm = op.cm,
            display = cm.display;
        maybeClipScrollbars(cm);
        if (op.updateMaxLine) findMaxLine(cm);

        op.mustUpdate = op.viewChanged || op.forceUpdate || op.scrollTop != null ||
            op.scrollToPos && (op.scrollToPos.from.line < display.viewFrom ||
                op.scrollToPos.to.line >= display.viewTo) ||
            display.maxLineChanged && cm.options.lineWrapping;
        op.update = op.mustUpdate &&
            new DisplayUpdate(cm, op.mustUpdate && {
                top: op.scrollTop,
                ensure: op.scrollToPos
            }, op.forceUpdate);
    }

    function endOperation_W1(op) {
        op.updatedDisplay = op.mustUpdate && updateDisplayIfNeeded(op.cm, op.update);
    }

    function endOperation_R2(op) {
        var cm = op.cm,
            display = cm.display;
        if (op.updatedDisplay) updateHeightsInViewport(cm);

        op.barMeasure = measureForScrollbars(cm);

        // If the max line changed since it was last measured, measure it,
        // and ensure the document's width matches it.
        // updateDisplay_W2 will use these properties to do the actual resizing
        if (display.maxLineChanged && !cm.options.lineWrapping) {
            op.adjustWidthTo = measureChar(cm, display.maxLine, display.maxLine.text.length).left + 3;
            cm.display.sizerWidth = op.adjustWidthTo;
            op.barMeasure.scrollWidth =
                Math.max(display.scroller.clientWidth, display.sizer.offsetLeft + op.adjustWidthTo + scrollGap(cm) + cm.display.barWidth);
            op.maxScrollLeft = Math.max(0, display.sizer.offsetLeft + op.adjustWidthTo - displayWidth(cm));
        }

        if (op.updatedDisplay || op.selectionChanged)
            op.preparedSelection = display.input.prepareSelection();
    }

    function endOperation_W2(op) {
        var cm = op.cm;

        if (op.adjustWidthTo != null) {
            cm.display.sizer.style.minWidth = op.adjustWidthTo + "px";
            if (op.maxScrollLeft < cm.doc.scrollLeft)
                setScrollLeft(cm, Math.min(cm.display.scroller.scrollLeft, op.maxScrollLeft), true);
            cm.display.maxLineChanged = false;
        }

        if (op.preparedSelection)
            cm.display.input.showSelection(op.preparedSelection);
        if (op.updatedDisplay)
            setDocumentHeight(cm, op.barMeasure);
        if (op.updatedDisplay || op.startHeight != cm.doc.height)
            updateScrollbars(cm, op.barMeasure);

        if (op.selectionChanged) restartBlink(cm);

        if (cm.state.focused && op.updateInput)
            cm.display.input.reset(op.typing);
        if (op.focus && op.focus == activeElt()) ensureFocus(op.cm);
    }

    function endOperation_finish(op) {
        var cm = op.cm,
            display = cm.display,
            doc = cm.doc;

        if (op.updatedDisplay) postUpdateDisplay(cm, op.update);

        // Abort mouse wheel delta measurement, when scrolling explicitly
        if (display.wheelStartX != null && (op.scrollTop != null || op.scrollLeft != null || op.scrollToPos))
            display.wheelStartX = display.wheelStartY = null;

        // Propagate the scroll position to the actual DOM scroller
        if (op.scrollTop != null && (display.scroller.scrollTop != op.scrollTop || op.forceScroll)) {
            doc.scrollTop = Math.max(0, Math.min(display.scroller.scrollHeight - display.scroller.clientHeight, op.scrollTop));
            display.scrollbars.setScrollTop(doc.scrollTop);
            display.scroller.scrollTop = doc.scrollTop;
        }
        if (op.scrollLeft != null && (display.scroller.scrollLeft != op.scrollLeft || op.forceScroll)) {
            doc.scrollLeft = Math.max(0, Math.min(display.scroller.scrollWidth - displayWidth(cm), op.scrollLeft));
            display.scrollbars.setScrollLeft(doc.scrollLeft);
            display.scroller.scrollLeft = doc.scrollLeft;
            alignHorizontally(cm);
        }
        // If we need to scroll a specific position into view, do so.
        if (op.scrollToPos) {
            var coords = scrollPosIntoView(cm, clipPos(doc, op.scrollToPos.from),
                clipPos(doc, op.scrollToPos.to), op.scrollToPos.margin);
            if (op.scrollToPos.isCursor && cm.state.focused) maybeScrollWindow(cm, coords);
        }

        // Fire events for markers that are hidden/unidden by editing or
        // undoing
        var hidden = op.maybeHiddenMarkers,
            unhidden = op.maybeUnhiddenMarkers;
        if (hidden)
            for (var i = 0; i < hidden.length; ++i)
                if (!hidden[i].lines.length) signal(hidden[i], "hide");
        if (unhidden)
            for (var i = 0; i < unhidden.length; ++i)
                if (unhidden[i].lines.length) signal(unhidden[i], "unhide");

        if (display.wrapper.offsetHeight)
            doc.scrollTop = cm.display.scroller.scrollTop;

        // Fire change events, and delayed event handlers
        if (op.changeObjs)
            signal(cm, "changes", cm, op.changeObjs);
        if (op.update)
            op.update.finish();
    }

    // Run the given function in an operation
    function runInOp(cm, f) {
        if (cm.curOp) return f();
        startOperation(cm);
        try {
            return f();
        } finally {
            endOperation(cm);
        }
    }
    // Wraps a function in an operation. Returns the wrapped function.
    function operation(cm, f) {
        return function() {
            if (cm.curOp) return f.apply(cm, arguments);
            startOperation(cm);
            try {
                return f.apply(cm, arguments);
            } finally {
                endOperation(cm);
            }
        };
    }
    // Used to add methods to editor and doc instances, wrapping them in
    // operations.
    function methodOp(f) {
        return function() {
            if (this.curOp) return f.apply(this, arguments);
            startOperation(this);
            try {
                return f.apply(this, arguments);
            } finally {
                endOperation(this);
            }
        };
    }

    function docMethodOp(f) {
        return function() {
            var cm = this.cm;
            if (!cm || cm.curOp) return f.apply(this, arguments);
            startOperation(cm);
            try {
                return f.apply(this, arguments);
            } finally {
                endOperation(cm);
            }
        };
    }

    // VIEW TRACKING

    // These objects are used to represent the visible (currently drawn)
    // part of the document. A LineView may correspond to multiple
    // logical lines, if those are connected by collapsed ranges.
    function LineView(doc, line, lineN) {
        // The starting line
        this.line = line;
        // Continuing lines, if any
        this.rest = visualLineContinued(line);
        // Number of logical lines in this visual line
        this.size = this.rest ? lineNo(lst(this.rest)) - lineN + 1 : 1;
        this.node = this.text = null;
        this.hidden = lineIsHidden(doc, line);
    }

    // Create a range of LineView objects for the given lines.
    function buildViewArray(cm, from, to) {
        var array = [],
            nextPos;
        for (var pos = from; pos < to; pos = nextPos) {
            var view = new LineView(cm.doc, getLine(cm.doc, pos), pos);
            nextPos = pos + view.size;
            array.push(view);
        }
        return array;
    }

    // Updates the display.view data structure for a given change to the
    // document. From and to are in pre-change coordinates. Lendiff is
    // the amount of lines added or subtracted by the change. This is
    // used for changes that span multiple lines, or change the way
    // lines are divided into visual lines. regLineChange (below)
    // registers single-line changes.
    function regChange(cm, from, to, lendiff) {
        if (from == null) from = cm.doc.first;
        if (to == null) to = cm.doc.first + cm.doc.size;
        if (!lendiff) lendiff = 0;

        var display = cm.display;
        if (lendiff && to < display.viewTo &&
            (display.updateLineNumbers == null || display.updateLineNumbers > from))
            display.updateLineNumbers = from;

        cm.curOp.viewChanged = true;

        if (from >= display.viewTo) { // Change after
            if (sawCollapsedSpans && visualLineNo(cm.doc, from) < display.viewTo)
                resetView(cm);
        } else if (to <= display.viewFrom) { // Change before
            if (sawCollapsedSpans && visualLineEndNo(cm.doc, to + lendiff) > display.viewFrom) {
                resetView(cm);
            } else {
                display.viewFrom += lendiff;
                display.viewTo += lendiff;
            }
        } else if (from <= display.viewFrom && to >= display.viewTo) { // Full overlap
            resetView(cm);
        } else if (from <= display.viewFrom) { // Top overlap
            var cut = viewCuttingPoint(cm, to, to + lendiff, 1);
            if (cut) {
                display.view = display.view.slice(cut.index);
                display.viewFrom = cut.lineN;
                display.viewTo += lendiff;
            } else {
                resetView(cm);
            }
        } else if (to >= display.viewTo) { // Bottom overlap
            var cut = viewCuttingPoint(cm, from, from, -1);
            if (cut) {
                display.view = display.view.slice(0, cut.index);
                display.viewTo = cut.lineN;
            } else {
                resetView(cm);
            }
        } else { // Gap in the middle
            var cutTop = viewCuttingPoint(cm, from, from, -1);
            var cutBot = viewCuttingPoint(cm, to, to + lendiff, 1);
            if (cutTop && cutBot) {
                display.view = display.view.slice(0, cutTop.index)
                    .concat(buildViewArray(cm, cutTop.lineN, cutBot.lineN))
                    .concat(display.view.slice(cutBot.index));
                display.viewTo += lendiff;
            } else {
                resetView(cm);
            }
        }

        var ext = display.externalMeasured;
        if (ext) {
            if (to < ext.lineN)
                ext.lineN += lendiff;
            else if (from < ext.lineN + ext.size)
                display.externalMeasured = null;
        }
    }

    // Register a change to a single line. Type must be one of "text",
    // "gutter", "class", "widget"
    function regLineChange(cm, line, type) {
        cm.curOp.viewChanged = true;
        var display = cm.display,
            ext = cm.display.externalMeasured;
        if (ext && line >= ext.lineN && line < ext.lineN + ext.size)
            display.externalMeasured = null;

        if (line < display.viewFrom || line >= display.viewTo) return;
        var lineView = display.view[findViewIndex(cm, line)];
        if (lineView.node == null) return;
        var arr = lineView.changes || (lineView.changes = []);
        if (indexOf(arr, type) == -1) arr.push(type);
    }

    // Clear the view.
    function resetView(cm) {
        cm.display.viewFrom = cm.display.viewTo = cm.doc.first;
        cm.display.view = [];
        cm.display.viewOffset = 0;
    }

    // Find the view element corresponding to a given line. Return null
    // when the line isn't visible.
    function findViewIndex(cm, n) {
        if (n >= cm.display.viewTo) return null;
        n -= cm.display.viewFrom;
        if (n < 0) return null;
        var view = cm.display.view;
        for (var i = 0; i < view.length; i++) {
            n -= view[i].size;
            if (n < 0) return i;
        }
    }

    function viewCuttingPoint(cm, oldN, newN, dir) {
        var index = findViewIndex(cm, oldN),
            diff, view = cm.display.view;
        if (!sawCollapsedSpans || newN == cm.doc.first + cm.doc.size)
            return {
                index: index,
                lineN: newN
            };
        for (var i = 0, n = cm.display.viewFrom; i < index; i++)
            n += view[i].size;
        if (n != oldN) {
            if (dir > 0) {
                if (index == view.length - 1) return null;
                diff = (n + view[index].size) - oldN;
                index++;
            } else {
                diff = n - oldN;
            }
            oldN += diff;
            newN += diff;
        }
        while (visualLineNo(cm.doc, newN) != newN) {
            if (index == (dir < 0 ? 0 : view.length - 1)) return null;
            newN += dir * view[index - (dir < 0 ? 1 : 0)].size;
            index += dir;
        }
        return {
            index: index,
            lineN: newN
        };
    }

    // Force the view to cover a given range, adding empty view element
    // or clipping off existing ones as needed.
    function adjustView(cm, from, to) {
        var display = cm.display,
            view = display.view;
        if (view.length == 0 || from >= display.viewTo || to <= display.viewFrom) {
            display.view = buildViewArray(cm, from, to);
            display.viewFrom = from;
        } else {
            if (display.viewFrom > from)
                display.view = buildViewArray(cm, from, display.viewFrom).concat(display.view);
            else if (display.viewFrom < from)
                display.view = display.view.slice(findViewIndex(cm, from));
            display.viewFrom = from;
            if (display.viewTo < to)
                display.view = display.view.concat(buildViewArray(cm, display.viewTo, to));
            else if (display.viewTo > to)
                display.view = display.view.slice(0, findViewIndex(cm, to));
        }
        display.viewTo = to;
    }

    // Count the number of lines in the view whose DOM representation is
    // out of date (or nonexistent).
    function countDirtyView(cm) {
        var view = cm.display.view,
            dirty = 0;
        for (var i = 0; i < view.length; i++) {
            var lineView = view[i];
            if (!lineView.hidden && (!lineView.node || lineView.changes)) ++dirty;
        }
        return dirty;
    }

    // EVENT HANDLERS

    // Attach the necessary event handlers when initializing the editor
    function registerEventHandlers(cm) {
        var d = cm.display;
        on(d.scroller, "mousedown", operation(cm, onMouseDown));
        // Older IE's will not fire a second mousedown for a double click
        if (ie && ie_version < 11)
            on(d.scroller, "dblclick", operation(cm, function(e) {
                if (signalDOMEvent(cm, e)) return;
                var pos = posFromMouse(cm, e);
                if (!pos || clickInGutter(cm, e) || eventInWidget(cm.display, e)) return;
                e_preventDefault(e);
                var word = cm.findWordAt(pos);
                extendSelection(cm.doc, word.anchor, word.head);
            }));
        else
            on(d.scroller, "dblclick", function(e) {
                signalDOMEvent(cm, e) || e_preventDefault(e);
            });
        // Some browsers fire contextmenu *after* opening the menu, at
        // which point we can't mess with it anymore. Context menu is
        // handled in onMouseDown for these browsers.
        if (!captureRightClick) on(d.scroller, "contextmenu", function(e) {
            onContextMenu(cm, e);
        });

        // Used to suppress mouse event handling when a touch happens
        var touchFinished, prevTouch = {
            end: 0
        };

        function finishTouch() {
            if (d.activeTouch) {
                touchFinished = setTimeout(function() {
                    d.activeTouch = null;
                }, 1000);
                prevTouch = d.activeTouch;
                prevTouch.end = +new Date;
            }
        };

        function isMouseLikeTouchEvent(e) {
            if (e.touches.length != 1) return false;
            var touch = e.touches[0];
            return touch.radiusX <= 1 && touch.radiusY <= 1;
        }

        function farAway(touch, other) {
            if (other.left == null) return true;
            var dx = other.left - touch.left,
                dy = other.top - touch.top;
            return dx * dx + dy * dy > 20 * 20;
        }
        on(d.scroller, "touchstart", function(e) {
            if (!isMouseLikeTouchEvent(e)) {
                clearTimeout(touchFinished);
                var now = +new Date;
                d.activeTouch = {
                    start: now,
                    moved: false,
                    prev: now - prevTouch.end <= 300 ? prevTouch : null
                };
                if (e.touches.length == 1) {
                    d.activeTouch.left = e.touches[0].pageX;
                    d.activeTouch.top = e.touches[0].pageY;
                }
            }
        });
        on(d.scroller, "touchmove", function() {
            if (d.activeTouch) d.activeTouch.moved = true;
        });
        on(d.scroller, "touchend", function(e) {
            var touch = d.activeTouch;
            if (touch && !eventInWidget(d, e) && touch.left != null &&
                !touch.moved && new Date - touch.start < 300) {
                var pos = cm.coordsChar(d.activeTouch, "page"),
                    range;
                if (!touch.prev || farAway(touch, touch.prev)) // Single tap
                    range = new Range(pos, pos);
                else if (!touch.prev.prev || farAway(touch, touch.prev.prev)) // Double tap
                    range = cm.findWordAt(pos);
                else // Triple tap
                    range = new Range(Pos(pos.line, 0), clipPos(cm.doc, Pos(pos.line + 1, 0)));
                cm.setSelection(range.anchor, range.head);
                cm.focus();
                e_preventDefault(e);
            }
            finishTouch();
        });
        on(d.scroller, "touchcancel", finishTouch);

        // Sync scrolling between fake scrollbars and real scrollable
        // area, ensure viewport is updated when scrolling.
        on(d.scroller, "scroll", function() {
            if (d.scroller.clientHeight) {
                setScrollTop(cm, d.scroller.scrollTop);
                setScrollLeft(cm, d.scroller.scrollLeft, true);
                signal(cm, "scroll", cm);
            }
        });

        // Listen to wheel events in order to try and update the viewport on time.
        on(d.scroller, "mousewheel", function(e) {
            onScrollWheel(cm, e);
        });
        on(d.scroller, "DOMMouseScroll", function(e) {
            onScrollWheel(cm, e);
        });

        // Prevent wrapper from ever scrolling
        on(d.wrapper, "scroll", function() {
            d.wrapper.scrollTop = d.wrapper.scrollLeft = 0;
        });

        d.dragFunctions = {
            simple: function(e) {
                if (!signalDOMEvent(cm, e)) e_stop(e);
            },
            start: function(e) {
                onDragStart(cm, e);
            },
            drop: operation(cm, onDrop)
        };

        var inp = d.input.getField();
        on(inp, "keyup", function(e) {
            onKeyUp.call(cm, e);
        });
        on(inp, "keydown", operation(cm, onKeyDown));
        on(inp, "keypress", operation(cm, onKeyPress));
        on(inp, "focus", bind(onFocus, cm));
        on(inp, "blur", bind(onBlur, cm));
    }

    function dragDropChanged(cm, value, old) {
        var wasOn = old && old != CodeMirror.Init;
        if (!value != !wasOn) {
            var funcs = cm.display.dragFunctions;
            var toggle = value ? on : off;
            toggle(cm.display.scroller, "dragstart", funcs.start);
            toggle(cm.display.scroller, "dragenter", funcs.simple);
            toggle(cm.display.scroller, "dragover", funcs.simple);
            toggle(cm.display.scroller, "drop", funcs.drop);
        }
    }

    // Called when the window resizes
    function onResize(cm) {
        var d = cm.display;
        if (d.lastWrapHeight == d.wrapper.clientHeight && d.lastWrapWidth == d.wrapper.clientWidth)
            return;
        // Might be a text scaling operation, clear size caches.
        d.cachedCharWidth = d.cachedTextHeight = d.cachedPaddingH = null;
        d.scrollbarsClipped = false;
        cm.setSize();
    }

    // MOUSE EVENTS

    // Return true when the given mouse event happened in a widget
    function eventInWidget(display, e) {
        for (var n = e_target(e); n != display.wrapper; n = n.parentNode) {
            if (!n || (n.nodeType == 1 && n.getAttribute("cm-ignore-events") == "true") ||
                (n.parentNode == display.sizer && n != display.mover))
                return true;
        }
    }

    // Given a mouse event, find the corresponding position. If liberal
    // is false, it checks whether a gutter or scrollbar was clicked,
    // and returns null if it was. forRect is used by rectangular
    // selections, and tries to estimate a character position even for
    // coordinates beyond the right of the text.
    function posFromMouse(cm, e, liberal, forRect) {
        var display = cm.display;
        if (!liberal && e_target(e).getAttribute("cm-not-content") == "true") return null;

        var x, y, space = display.lineSpace.getBoundingClientRect();
        // Fails unpredictably on IE[67] when mouse is dragged around quickly.
        try {
            x = e.clientX - space.left;
            y = e.clientY - space.top;
        } catch (e) {
            return null;
        }
        var coords = coordsChar(cm, x, y),
            line;
        if (forRect && coords.xRel == 1 && (line = getLine(cm.doc, coords.line).text).length == coords.ch) {
            var colDiff = countColumn(line, line.length, cm.options.tabSize) - line.length;
            coords = Pos(coords.line, Math.max(0, Math.round((x - paddingH(cm.display).left) / charWidth(cm.display)) - colDiff));
        }
        return coords;
    }

    // A mouse down can be a single click, double click, triple click,
    // start of selection drag, start of text drag, new cursor
    // (ctrl-click), rectangle drag (alt-drag), or xwin
    // middle-click-paste. Or it might be a click on something we should
    // not interfere with, such as a scrollbar or widget.
    function onMouseDown(e) {
        var cm = this,
            display = cm.display;
        if (display.activeTouch && display.input.supportsTouch() || signalDOMEvent(cm, e)) return;
        display.shift = e.shiftKey;

        if (eventInWidget(display, e)) {
            if (!webkit) {
                // Briefly turn off draggability, to allow widgets to do
                // normal dragging things.
                display.scroller.draggable = false;
                setTimeout(function() {
                    display.scroller.draggable = true;
                }, 100);
            }
            return;
        }
        if (clickInGutter(cm, e)) return;
        var start = posFromMouse(cm, e);
        window.focus();

        switch (e_button(e)) {
            case 1:
                if (start)
                    leftButtonDown(cm, e, start);
                else if (e_target(e) == display.scroller)
                    e_preventDefault(e);
                break;
            case 2:
                if (webkit) cm.state.lastMiddleDown = +new Date;
                if (start) extendSelection(cm.doc, start);
                setTimeout(function() {
                    display.input.focus();
                }, 20);
                e_preventDefault(e);
                break;
            case 3:
                if (captureRightClick) onContextMenu(cm, e);
                else delayBlurEvent(cm);
                break;
        }
    }

    var lastClick, lastDoubleClick;

    function leftButtonDown(cm, e, start) {
        if (ie) setTimeout(bind(ensureFocus, cm), 0);
        else cm.curOp.focus = activeElt();

        var now = +new Date,
            type;
        if (lastDoubleClick && lastDoubleClick.time > now - 400 && cmp(lastDoubleClick.pos, start) == 0) {
            type = "triple";
        } else if (lastClick && lastClick.time > now - 400 && cmp(lastClick.pos, start) == 0) {
            type = "double";
            lastDoubleClick = {
                time: now,
                pos: start
            };
        } else {
            type = "single";
            lastClick = {
                time: now,
                pos: start
            };
        }

        var sel = cm.doc.sel,
            modifier = mac ? e.metaKey : e.ctrlKey,
            contained;
        if (cm.options.dragDrop && dragAndDrop && !isReadOnly(cm) &&
            type == "single" && (contained = sel.contains(start)) > -1 &&
            !sel.ranges[contained].empty())
            leftButtonStartDrag(cm, e, start, modifier);
        else
            leftButtonSelect(cm, e, start, type, modifier);
    }

    // Start a text drag. When it ends, see if any dragging actually
    // happen, and treat as a click if it didn't.
    function leftButtonStartDrag(cm, e, start, modifier) {
        var display = cm.display,
            startTime = +new Date;
        var dragEnd = operation(cm, function(e2) {
            if (webkit) display.scroller.draggable = false;
            cm.state.draggingText = false;
            off(document, "mouseup", dragEnd);
            off(display.scroller, "drop", dragEnd);
            if (Math.abs(e.clientX - e2.clientX) + Math.abs(e.clientY - e2.clientY) < 10) {
                e_preventDefault(e2);
                if (!modifier && +new Date - 200 < startTime)
                    extendSelection(cm.doc, start);
                // Work around unexplainable focus problem in IE9 (#2127) and Chrome (#3081)
                if (webkit || ie && ie_version == 9)
                    setTimeout(function() {
                        document.body.focus();
                        display.input.focus();
                    }, 20);
                else
                    display.input.focus();
            }
        });
        // Let the drag handler handle this.
        if (webkit) display.scroller.draggable = true;
        cm.state.draggingText = dragEnd;
        // IE's approach to draggable
        if (display.scroller.dragDrop) display.scroller.dragDrop();
        on(document, "mouseup", dragEnd);
        on(display.scroller, "drop", dragEnd);
    }

    // Normal selection, as opposed to text dragging.
    function leftButtonSelect(cm, e, start, type, addNew) {
        var display = cm.display,
            doc = cm.doc;
        e_preventDefault(e);

        var ourRange, ourIndex, startSel = doc.sel,
            ranges = startSel.ranges;
        if (addNew && !e.shiftKey) {
            ourIndex = doc.sel.contains(start);
            if (ourIndex > -1)
                ourRange = ranges[ourIndex];
            else
                ourRange = new Range(start, start);
        } else {
            ourRange = doc.sel.primary();
            ourIndex = doc.sel.primIndex;
        }

        if (e.altKey) {
            type = "rect";
            if (!addNew) ourRange = new Range(start, start);
            start = posFromMouse(cm, e, true, true);
            ourIndex = -1;
        } else if (type == "double") {
            var word = cm.findWordAt(start);
            if (cm.display.shift || doc.extend)
                ourRange = extendRange(doc, ourRange, word.anchor, word.head);
            else
                ourRange = word;
        } else if (type == "triple") {
            var line = new Range(Pos(start.line, 0), clipPos(doc, Pos(start.line + 1, 0)));
            if (cm.display.shift || doc.extend)
                ourRange = extendRange(doc, ourRange, line.anchor, line.head);
            else
                ourRange = line;
        } else {
            ourRange = extendRange(doc, ourRange, start);
        }

        if (!addNew) {
            ourIndex = 0;
            setSelection(doc, new Selection([ourRange], 0), sel_mouse);
            startSel = doc.sel;
        } else if (ourIndex == -1) {
            ourIndex = ranges.length;
            setSelection(doc, normalizeSelection(ranges.concat([ourRange]), ourIndex), {
                scroll: false,
                origin: "*mouse"
            });
        } else if (ranges.length > 1 && ranges[ourIndex].empty() && type == "single" && !e.shiftKey) {
            setSelection(doc, normalizeSelection(ranges.slice(0, ourIndex).concat(ranges.slice(ourIndex + 1)), 0));
            startSel = doc.sel;
        } else {
            replaceOneSelection(doc, ourIndex, ourRange, sel_mouse);
        }

        var lastPos = start;

        function extendTo(pos) {
            if (cmp(lastPos, pos) == 0) return;
            lastPos = pos;

            if (type == "rect") {
                var ranges = [],
                    tabSize = cm.options.tabSize;
                var startCol = countColumn(getLine(doc, start.line).text, start.ch, tabSize);
                var posCol = countColumn(getLine(doc, pos.line).text, pos.ch, tabSize);
                var left = Math.min(startCol, posCol),
                    right = Math.max(startCol, posCol);
                for (var line = Math.min(start.line, pos.line), end = Math.min(cm.lastLine(), Math.max(start.line, pos.line)); line <= end; line++) {
                    var text = getLine(doc, line).text,
                        leftPos = findColumn(text, left, tabSize);
                    if (left == right)
                        ranges.push(new Range(Pos(line, leftPos), Pos(line, leftPos)));
                    else if (text.length > leftPos)
                        ranges.push(new Range(Pos(line, leftPos), Pos(line, findColumn(text, right, tabSize))));
                }
                if (!ranges.length) ranges.push(new Range(start, start));
                setSelection(doc, normalizeSelection(startSel.ranges.slice(0, ourIndex).concat(ranges), ourIndex), {
                    origin: "*mouse",
                    scroll: false
                });
                cm.scrollIntoView(pos);
            } else {
                var oldRange = ourRange;
                var anchor = oldRange.anchor,
                    head = pos;
                if (type != "single") {
                    if (type == "double")
                        var range = cm.findWordAt(pos);
                    else
                        var range = new Range(Pos(pos.line, 0), clipPos(doc, Pos(pos.line + 1, 0)));
                    if (cmp(range.anchor, anchor) > 0) {
                        head = range.head;
                        anchor = minPos(oldRange.from(), range.anchor);
                    } else {
                        head = range.anchor;
                        anchor = maxPos(oldRange.to(), range.head);
                    }
                }
                var ranges = startSel.ranges.slice(0);
                ranges[ourIndex] = new Range(clipPos(doc, anchor), head);
                setSelection(doc, normalizeSelection(ranges, ourIndex), sel_mouse);
            }
        }

        var editorSize = display.wrapper.getBoundingClientRect();
        // Used to ensure timeout re-tries don't fire when another extend
        // happened in the meantime (clearTimeout isn't reliable -- at
        // least on Chrome, the timeouts still happen even when cleared,
        // if the clear happens after their scheduled firing time).
        var counter = 0;

        function extend(e) {
            var curCount = ++counter;
            var cur = posFromMouse(cm, e, true, type == "rect");
            if (!cur) return;
            if (cmp(cur, lastPos) != 0) {
                cm.curOp.focus = activeElt();
                extendTo(cur);
                var visible = visibleLines(display, doc);
                if (cur.line >= visible.to || cur.line < visible.from)
                    setTimeout(operation(cm, function() {
                        if (counter == curCount) extend(e);
                    }), 150);
            } else {
                var outside = e.clientY < editorSize.top ? -20 : e.clientY > editorSize.bottom ? 20 : 0;
                if (outside) setTimeout(operation(cm, function() {
                    if (counter != curCount) return;
                    display.scroller.scrollTop += outside;
                    extend(e);
                }), 50);
            }
        }

        function done(e) {
            counter = Infinity;
            e_preventDefault(e);
            display.input.focus();
            off(document, "mousemove", move);
            off(document, "mouseup", up);
            doc.history.lastSelOrigin = null;
        }

        var move = operation(cm, function(e) {
            if (!e_button(e)) done(e);
            else extend(e);
        });
        var up = operation(cm, done);
        on(document, "mousemove", move);
        on(document, "mouseup", up);
    }

    // Determines whether an event happened in the gutter, and fires the
    // handlers for the corresponding event.
    function gutterEvent(cm, e, type, prevent, signalfn) {
        try {
            var mX = e.clientX,
                mY = e.clientY;
        } catch (e) {
            return false;
        }
        if (mX >= Math.floor(cm.display.gutters.getBoundingClientRect().right)) return false;
        if (prevent) e_preventDefault(e);

        var display = cm.display;
        var lineBox = display.lineDiv.getBoundingClientRect();

        if (mY > lineBox.bottom || !hasHandler(cm, type)) return e_defaultPrevented(e);
        mY -= lineBox.top - display.viewOffset;

        for (var i = 0; i < cm.options.gutters.length; ++i) {
            var g = display.gutters.childNodes[i];
            if (g && g.getBoundingClientRect().right >= mX) {
                var line = lineAtHeight(cm.doc, mY);
                var gutter = cm.options.gutters[i];
                signalfn(cm, type, cm, line, gutter, e);
                return e_defaultPrevented(e);
            }
        }
    }

    function clickInGutter(cm, e) {
        return gutterEvent(cm, e, "gutterClick", true, signalLater);
    }

    // Kludge to work around strange IE behavior where it'll sometimes
    // re-fire a series of drag-related events right after the drop (#1551)
    var lastDrop = 0;

    function onDrop(e) {
        var cm = this;
        if (signalDOMEvent(cm, e) || eventInWidget(cm.display, e))
            return;
        e_preventDefault(e);
        if (ie) lastDrop = +new Date;
        var pos = posFromMouse(cm, e, true),
            files = e.dataTransfer.files;
        if (!pos || isReadOnly(cm)) return;
        // Might be a file drop, in which case we simply extract the text
        // and insert it.
        if (files && files.length && window.FileReader && window.File) {
            var n = files.length,
                text = Array(n),
                read = 0;
            var loadFile = function(file, i) {
                var reader = new FileReader;
                reader.onload = operation(cm, function() {
                    text[i] = reader.result;
                    if (++read == n) {
                        pos = clipPos(cm.doc, pos);
                        var change = {
                            from: pos,
                            to: pos,
                            text: splitLines(text.join("\n")),
                            origin: "paste"
                        };
                        makeChange(cm.doc, change);
                        setSelectionReplaceHistory(cm.doc, simpleSelection(pos, changeEnd(change)));
                    }
                });
                reader.readAsText(file);
            };
            for (var i = 0; i < n; ++i) loadFile(files[i], i);
        } else { // Normal drop
            // Don't do a replace if the drop happened inside of the selected text.
            if (cm.state.draggingText && cm.doc.sel.contains(pos) > -1) {
                cm.state.draggingText(e);
                // Ensure the editor is re-focused
                setTimeout(function() {
                    cm.display.input.focus();
                }, 20);
                return;
            }
            try {
                var text = e.dataTransfer.getData("Text");
                if (text) {
                    if (cm.state.draggingText && !(mac ? e.altKey : e.ctrlKey))
                        var selected = cm.listSelections();
                    setSelectionNoUndo(cm.doc, simpleSelection(pos, pos));
                    if (selected)
                        for (var i = 0; i < selected.length; ++i)
                            replaceRange(cm.doc, "", selected[i].anchor, selected[i].head, "drag");
                    cm.replaceSelection(text, "around", "paste");
                    cm.display.input.focus();
                }
            } catch (e) {}
        }
    }

    function onDragStart(cm, e) {
        if (ie && (!cm.state.draggingText || +new Date - lastDrop < 100)) {
            e_stop(e);
            return;
        }
        if (signalDOMEvent(cm, e) || eventInWidget(cm.display, e)) return;

        e.dataTransfer.setData("Text", cm.getSelection());

        // Use dummy image instead of default browsers image.
        // Recent Safari (~6.0.2) have a tendency to segfault when this happens, so we don't do it there.
        if (e.dataTransfer.setDragImage && !safari) {
            var img = elt("img", null, null, "position: fixed; left: 0; top: 0;");
            img.src = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";
            if (presto) {
                img.width = img.height = 1;
                cm.display.wrapper.appendChild(img);
                // Force a relayout, or Opera won't use our image for some obscure reason
                img._top = img.offsetTop;
            }
            e.dataTransfer.setDragImage(img, 0, 0);
            if (presto) img.parentNode.removeChild(img);
        }
    }

    // SCROLL EVENTS

    // Sync the scrollable area and scrollbars, ensure the viewport
    // covers the visible area.
    function setScrollTop(cm, val) {
        if (Math.abs(cm.doc.scrollTop - val) < 2) return;
        cm.doc.scrollTop = val;
        if (!gecko) updateDisplaySimple(cm, {
            top: val
        });
        if (cm.display.scroller.scrollTop != val) cm.display.scroller.scrollTop = val;
        cm.display.scrollbars.setScrollTop(val);
        if (gecko) updateDisplaySimple(cm);
        startWorker(cm, 100);
    }
    // Sync scroller and scrollbar, ensure the gutter elements are
    // aligned.
    function setScrollLeft(cm, val, isScroller) {
        if (isScroller ? val == cm.doc.scrollLeft : Math.abs(cm.doc.scrollLeft - val) < 2) return;
        val = Math.min(val, cm.display.scroller.scrollWidth - cm.display.scroller.clientWidth);
        cm.doc.scrollLeft = val;
        alignHorizontally(cm);
        if (cm.display.scroller.scrollLeft != val) cm.display.scroller.scrollLeft = val;
        cm.display.scrollbars.setScrollLeft(val);
    }

    // Since the delta values reported on mouse wheel events are
    // unstandardized between browsers and even browser versions, and
    // generally horribly unpredictable, this code starts by measuring
    // the scroll effect that the first few mouse wheel events have,
    // and, from that, detects the way it can convert deltas to pixel
    // offsets afterwards.
    //
    // The reason we want to know the amount a wheel event will scroll
    // is that it gives us a chance to update the display before the
    // actual scrolling happens, reducing flickering.

    var wheelSamples = 0,
        wheelPixelsPerUnit = null;
    // Fill in a browser-detected starting value on browsers where we
    // know one. These don't have to be accurate -- the result of them
    // being wrong would just be a slight flicker on the first wheel
    // scroll (if it is large enough).
    if (ie) wheelPixelsPerUnit = -.53;
    else if (gecko) wheelPixelsPerUnit = 15;
    else if (chrome) wheelPixelsPerUnit = -.7;
    else if (safari) wheelPixelsPerUnit = -1 / 3;

    var wheelEventDelta = function(e) {
        var dx = e.wheelDeltaX,
            dy = e.wheelDeltaY;
        if (dx == null && e.detail && e.axis == e.HORIZONTAL_AXIS) dx = e.detail;
        if (dy == null && e.detail && e.axis == e.VERTICAL_AXIS) dy = e.detail;
        else if (dy == null) dy = e.wheelDelta;
        return {
            x: dx,
            y: dy
        };
    };
    CodeMirror.wheelEventPixels = function(e) {
        var delta = wheelEventDelta(e);
        delta.x *= wheelPixelsPerUnit;
        delta.y *= wheelPixelsPerUnit;
        return delta;
    };

    function onScrollWheel(cm, e) {
        var delta = wheelEventDelta(e),
            dx = delta.x,
            dy = delta.y;

        var display = cm.display,
            scroll = display.scroller;
        // Quit if there's nothing to scroll here
        if (!(dx && scroll.scrollWidth > scroll.clientWidth ||
                dy && scroll.scrollHeight > scroll.clientHeight)) return;

        // Webkit browsers on OS X abort momentum scrolls when the target
        // of the scroll event is removed from the scrollable element.
        // This hack (see related code in patchDisplay) makes sure the
        // element is kept around.
        if (dy && mac && webkit) {
            outer: for (var cur = e.target, view = display.view; cur != scroll; cur = cur.parentNode) {
                for (var i = 0; i < view.length; i++) {
                    if (view[i].node == cur) {
                        cm.display.currentWheelTarget = cur;
                        break outer;
                    }
                }
            }
        }

        // On some browsers, horizontal scrolling will cause redraws to
        // happen before the gutter has been realigned, causing it to
        // wriggle around in a most unseemly way. When we have an
        // estimated pixels/delta value, we just handle horizontal
        // scrolling entirely here. It'll be slightly off from native, but
        // better than glitching out.
        if (dx && !gecko && !presto && wheelPixelsPerUnit != null) {
            if (dy)
                setScrollTop(cm, Math.max(0, Math.min(scroll.scrollTop + dy * wheelPixelsPerUnit, scroll.scrollHeight - scroll.clientHeight)));
            setScrollLeft(cm, Math.max(0, Math.min(scroll.scrollLeft + dx * wheelPixelsPerUnit, scroll.scrollWidth - scroll.clientWidth)));
            e_preventDefault(e);
            display.wheelStartX = null; // Abort measurement, if in progress
            return;
        }

        // 'Project' the visible viewport to cover the area that is being
        // scrolled into view (if we know enough to estimate it).
        if (dy && wheelPixelsPerUnit != null) {
            var pixels = dy * wheelPixelsPerUnit;
            var top = cm.doc.scrollTop,
                bot = top + display.wrapper.clientHeight;
            if (pixels < 0) top = Math.max(0, top + pixels - 50);
            else bot = Math.min(cm.doc.height, bot + pixels + 50);
            updateDisplaySimple(cm, {
                top: top,
                bottom: bot
            });
        }

        if (wheelSamples < 20) {
            if (display.wheelStartX == null) {
                display.wheelStartX = scroll.scrollLeft;
                display.wheelStartY = scroll.scrollTop;
                display.wheelDX = dx;
                display.wheelDY = dy;
                setTimeout(function() {
                    if (display.wheelStartX == null) return;
                    var movedX = scroll.scrollLeft - display.wheelStartX;
                    var movedY = scroll.scrollTop - display.wheelStartY;
                    var sample = (movedY && display.wheelDY && movedY / display.wheelDY) ||
                        (movedX && display.wheelDX && movedX / display.wheelDX);
                    display.wheelStartX = display.wheelStartY = null;
                    if (!sample) return;
                    wheelPixelsPerUnit = (wheelPixelsPerUnit * wheelSamples + sample) / (wheelSamples + 1);
                    ++wheelSamples;
                }, 200);
            } else {
                display.wheelDX += dx;
                display.wheelDY += dy;
            }
        }
    }

    // KEY EVENTS

    // Run a handler that was bound to a key.
    function doHandleBinding(cm, bound, dropShift) {
        if (typeof bound == "string") {
            bound = commands[bound];
            if (!bound) return false;
        }
        // Ensure previous input has been read, so that the handler sees a
        // consistent view of the document
        cm.display.input.ensurePolled();
        var prevShift = cm.display.shift,
            done = false;
        try {
            if (isReadOnly(cm)) cm.state.suppressEdits = true;
            if (dropShift) cm.display.shift = false;
            done = bound(cm) != Pass;
        } finally {
            cm.display.shift = prevShift;
            cm.state.suppressEdits = false;
        }
        return done;
    }

    function lookupKeyForEditor(cm, name, handle) {
        for (var i = 0; i < cm.state.keyMaps.length; i++) {
            var result = lookupKey(name, cm.state.keyMaps[i], handle, cm);
            if (result) return result;
        }
        return (cm.options.extraKeys && lookupKey(name, cm.options.extraKeys, handle, cm)) || lookupKey(name, cm.options.keyMap, handle, cm);
    }

    var stopSeq = new Delayed;

    function dispatchKey(cm, name, e, handle) {
        var seq = cm.state.keySeq;
        if (seq) {
            if (isModifierKey(name)) return "handled";
            stopSeq.set(50, function() {
                if (cm.state.keySeq == seq) {
                    cm.state.keySeq = null;
                    cm.display.input.reset();
                }
            });
            name = seq + " " + name;
        }
        var result = lookupKeyForEditor(cm, name, handle);

        if (result == "multi")
            cm.state.keySeq = name;
        if (result == "handled")
            signalLater(cm, "keyHandled", cm, name, e);

        if (result == "handled" || result == "multi") {
            e_preventDefault(e);
            restartBlink(cm);
        }

        if (seq && !result && /\'$/.test(name)) {
            e_preventDefault(e);
            return true;
        }
        return !!result;
    }

    // Handle a key from the keydown event.
    function handleKeyBinding(cm, e) {
        var name = keyName(e, true);
        if (!name) return false;

        if (e.shiftKey && !cm.state.keySeq) {
            // First try to resolve full name (including 'Shift-'). Failing
            // that, see if there is a cursor-motion command (starting with
            // 'go') bound to the keyname without 'Shift-'.
            return dispatchKey(cm, "Shift-" + name, e, function(b) {
                return doHandleBinding(cm, b, true);
            }) || dispatchKey(cm, name, e, function(b) {
                if (typeof b == "string" ? /^go[A-Z]/.test(b) : b.motion)
                    return doHandleBinding(cm, b);
            });
        } else {
            return dispatchKey(cm, name, e, function(b) {
                return doHandleBinding(cm, b);
            });
        }
    }

    // Handle a key from the keypress event
    function handleCharBinding(cm, e, ch) {
        return dispatchKey(cm, "'" + ch + "'", e,
            function(b) {
                return doHandleBinding(cm, b, true);
            });
    }

    var lastStoppedKey = null;

    function onKeyDown(e) {
        var cm = this;
        cm.curOp.focus = activeElt();
        if (signalDOMEvent(cm, e)) return;
        // IE does strange things with escape.
        if (ie && ie_version < 11 && e.keyCode == 27) e.returnValue = false;
        var code = e.keyCode;
        cm.display.shift = code == 16 || e.shiftKey;
        var handled = handleKeyBinding(cm, e);
        if (presto) {
            lastStoppedKey = handled ? code : null;
            // Opera has no cut event... we try to at least catch the key combo
            if (!handled && code == 88 && !hasCopyEvent && (mac ? e.metaKey : e.ctrlKey))
                cm.replaceSelection("", null, "cut");
        }

        // Turn mouse into crosshair when Alt is held on Mac.
        if (code == 18 && !/\bCodeMirror-crosshair\b/.test(cm.display.lineDiv.className))
            showCrossHair(cm);
    }

    function showCrossHair(cm) {
        var lineDiv = cm.display.lineDiv;
        addClass(lineDiv, "CodeMirror-crosshair");

        function up(e) {
            if (e.keyCode == 18 || !e.altKey) {
                rmClass(lineDiv, "CodeMirror-crosshair");
                off(document, "keyup", up);
                off(document, "mouseover", up);
            }
        }
        on(document, "keyup", up);
        on(document, "mouseover", up);
    }

    function onKeyUp(e) {
        if (e.keyCode == 16) this.doc.sel.shift = false;
        signalDOMEvent(this, e);
    }

    function onKeyPress(e) {
        var cm = this;
        if (eventInWidget(cm.display, e) || signalDOMEvent(cm, e) || e.ctrlKey && !e.altKey || mac && e.metaKey) return;
        var keyCode = e.keyCode,
            charCode = e.charCode;
        if (presto && keyCode == lastStoppedKey) {
            lastStoppedKey = null;
            e_preventDefault(e);
            return;
        }
        if ((presto && (!e.which || e.which < 10)) && handleKeyBinding(cm, e)) return;
        var ch = String.fromCharCode(charCode == null ? keyCode : charCode);
        if (handleCharBinding(cm, e, ch)) return;
        cm.display.input.onKeyPress(e);
    }

    // FOCUS/BLUR EVENTS

    function delayBlurEvent(cm) {
        cm.state.delayingBlurEvent = true;
        setTimeout(function() {
            if (cm.state.delayingBlurEvent) {
                cm.state.delayingBlurEvent = false;
                onBlur(cm);
            }
        }, 100);
    }

    function onFocus(cm) {
        if (cm.state.delayingBlurEvent) cm.state.delayingBlurEvent = false;

        if (cm.options.readOnly == "nocursor") return;
        if (!cm.state.focused) {
            signal(cm, "focus", cm);
            cm.state.focused = true;
            addClass(cm.display.wrapper, "CodeMirror-focused");
            // This test prevents this from firing when a context
            // menu is closed (since the input reset would kill the
            // select-all detection hack)
            if (!cm.curOp && cm.display.selForContextMenu != cm.doc.sel) {
                cm.display.input.reset();
                if (webkit) setTimeout(function() {
                    cm.display.input.reset(true);
                }, 20); // Issue #1730
            }
            cm.display.input.receivedFocus();
        }
        restartBlink(cm);
    }

    function onBlur(cm) {
        if (cm.state.delayingBlurEvent) return;

        if (cm.state.focused) {
            signal(cm, "blur", cm);
            cm.state.focused = false;
            rmClass(cm.display.wrapper, "CodeMirror-focused");
        }
        clearInterval(cm.display.blinker);
        setTimeout(function() {
            if (!cm.state.focused) cm.display.shift = false;
        }, 150);
    }

    // CONTEXT MENU HANDLING

    // To make the context menu work, we need to briefly unhide the
    // textarea (making it as unobtrusive as possible) to let the
    // right-click take effect on it.
    function onContextMenu(cm, e) {
        if (eventInWidget(cm.display, e) || contextMenuInGutter(cm, e)) return;
        cm.display.input.onContextMenu(e);
    }

    function contextMenuInGutter(cm, e) {
        if (!hasHandler(cm, "gutterContextMenu")) return false;
        return gutterEvent(cm, e, "gutterContextMenu", false, signal);
    }

    // UPDATING

    // Compute the position of the end of a change (its 'to' property
    // refers to the pre-change end).
    var changeEnd = CodeMirror.changeEnd = function(change) {
        if (!change.text) return change.to;
        return Pos(change.from.line + change.text.length - 1,
            lst(change.text).length + (change.text.length == 1 ? change.from.ch : 0));
    };

    // Adjust a position to refer to the post-change position of the
    // same text, or the end of the change if the change covers it.
    function adjustForChange(pos, change) {
        if (cmp(pos, change.from) < 0) return pos;
        if (cmp(pos, change.to) <= 0) return changeEnd(change);

        var line = pos.line + change.text.length - (change.to.line - change.from.line) - 1,
            ch = pos.ch;
        if (pos.line == change.to.line) ch += changeEnd(change).ch - change.to.ch;
        return Pos(line, ch);
    }

    function computeSelAfterChange(doc, change) {
        var out = [];
        for (var i = 0; i < doc.sel.ranges.length; i++) {
            var range = doc.sel.ranges[i];
            out.push(new Range(adjustForChange(range.anchor, change),
                adjustForChange(range.head, change)));
        }
        return normalizeSelection(out, doc.sel.primIndex);
    }

    function offsetPos(pos, old, nw) {
        if (pos.line == old.line)
            return Pos(nw.line, pos.ch - old.ch + nw.ch);
        else
            return Pos(nw.line + (pos.line - old.line), pos.ch);
    }

    // Used by replaceSelections to allow moving the selection to the
    // start or around the replaced test. Hint may be "start" or "around".
    function computeReplacedSel(doc, changes, hint) {
        var out = [];
        var oldPrev = Pos(doc.first, 0),
            newPrev = oldPrev;
        for (var i = 0; i < changes.length; i++) {
            var change = changes[i];
            var from = offsetPos(change.from, oldPrev, newPrev);
            var to = offsetPos(changeEnd(change), oldPrev, newPrev);
            oldPrev = change.to;
            newPrev = to;
            if (hint == "around") {
                var range = doc.sel.ranges[i],
                    inv = cmp(range.head, range.anchor) < 0;
                out[i] = new Range(inv ? to : from, inv ? from : to);
            } else {
                out[i] = new Range(from, from);
            }
        }
        return new Selection(out, doc.sel.primIndex);
    }

    // Allow "beforeChange" event handlers to influence a change
    function filterChange(doc, change, update) {
        var obj = {
            canceled: false,
            from: change.from,
            to: change.to,
            text: change.text,
            origin: change.origin,
            cancel: function() {
                this.canceled = true;
            }
        };
        if (update) obj.update = function(from, to, text, origin) {
            if (from) this.from = clipPos(doc, from);
            if (to) this.to = clipPos(doc, to);
            if (text) this.text = text;
            if (origin !== undefined) this.origin = origin;
        };
        signal(doc, "beforeChange", doc, obj);
        if (doc.cm) signal(doc.cm, "beforeChange", doc.cm, obj);

        if (obj.canceled) return null;
        return {
            from: obj.from,
            to: obj.to,
            text: obj.text,
            origin: obj.origin
        };
    }

    // Apply a change to a document, and add it to the document's
    // history, and propagating it to all linked documents.
    function makeChange(doc, change, ignoreReadOnly) {
        if (doc.cm) {
            if (!doc.cm.curOp) return operation(doc.cm, makeChange)(doc, change, ignoreReadOnly);
            if (doc.cm.state.suppressEdits) return;
        }

        if (hasHandler(doc, "beforeChange") || doc.cm && hasHandler(doc.cm, "beforeChange")) {
            change = filterChange(doc, change, true);
            if (!change) return;
        }

        // Possibly split or suppress the update based on the presence
        // of read-only spans in its range.
        var split = sawReadOnlySpans && !ignoreReadOnly && removeReadOnlyRanges(doc, change.from, change.to);
        if (split) {
            for (var i = split.length - 1; i >= 0; --i)
                makeChangeInner(doc, {
                    from: split[i].from,
                    to: split[i].to,
                    text: i ? [""] : change.text
                });
        } else {
            makeChangeInner(doc, change);
        }
    }

    function makeChangeInner(doc, change) {
        if (change.text.length == 1 && change.text[0] == "" && cmp(change.from, change.to) == 0) return;
        var selAfter = computeSelAfterChange(doc, change);
        addChangeToHistory(doc, change, selAfter, doc.cm ? doc.cm.curOp.id : NaN);

        makeChangeSingleDoc(doc, change, selAfter, stretchSpansOverChange(doc, change));
        var rebased = [];

        linkedDocs(doc, function(doc, sharedHist) {
            if (!sharedHist && indexOf(rebased, doc.history) == -1) {
                rebaseHist(doc.history, change);
                rebased.push(doc.history);
            }
            makeChangeSingleDoc(doc, change, null, stretchSpansOverChange(doc, change));
        });
    }

    // Revert a change stored in a document's history.
    function makeChangeFromHistory(doc, type, allowSelectionOnly) {
        if (doc.cm && doc.cm.state.suppressEdits) return;

        var hist = doc.history,
            event, selAfter = doc.sel;
        var source = type == "undo" ? hist.done : hist.undone,
            dest = type == "undo" ? hist.undone : hist.done;

        // Verify that there is a useable event (so that ctrl-z won't
        // needlessly clear selection events)
        for (var i = 0; i < source.length; i++) {
            event = source[i];
            if (allowSelectionOnly ? event.ranges && !event.equals(doc.sel) : !event.ranges)
                break;
        }
        if (i == source.length) return;
        hist.lastOrigin = hist.lastSelOrigin = null;

        for (;;) {
            event = source.pop();
            if (event.ranges) {
                pushSelectionToHistory(event, dest);
                if (allowSelectionOnly && !event.equals(doc.sel)) {
                    setSelection(doc, event, {
                        clearRedo: false
                    });
                    return;
                }
                selAfter = event;
            } else break;
        }

        // Build up a reverse change object to add to the opposite history
        // stack (redo when undoing, and vice versa).
        var antiChanges = [];
        pushSelectionToHistory(selAfter, dest);
        dest.push({
            changes: antiChanges,
            generation: hist.generation
        });
        hist.generation = event.generation || ++hist.maxGeneration;

        var filter = hasHandler(doc, "beforeChange") || doc.cm && hasHandler(doc.cm, "beforeChange");

        for (var i = event.changes.length - 1; i >= 0; --i) {
            var change = event.changes[i];
            change.origin = type;
            if (filter && !filterChange(doc, change, false)) {
                source.length = 0;
                return;
            }

            antiChanges.push(historyChangeFromChange(doc, change));

            var after = i ? computeSelAfterChange(doc, change) : lst(source);
            makeChangeSingleDoc(doc, change, after, mergeOldSpans(doc, change));
            if (!i && doc.cm) doc.cm.scrollIntoView({
                from: change.from,
                to: changeEnd(change)
            });
            var rebased = [];

            // Propagate to the linked documents
            linkedDocs(doc, function(doc, sharedHist) {
                if (!sharedHist && indexOf(rebased, doc.history) == -1) {
                    rebaseHist(doc.history, change);
                    rebased.push(doc.history);
                }
                makeChangeSingleDoc(doc, change, null, mergeOldSpans(doc, change));
            });
        }
    }

    // Sub-views need their line numbers shifted when text is added
    // above or below them in the parent document.
    function shiftDoc(doc, distance) {
        if (distance == 0) return;
        doc.first += distance;
        doc.sel = new Selection(map(doc.sel.ranges, function(range) {
            return new Range(Pos(range.anchor.line + distance, range.anchor.ch),
                Pos(range.head.line + distance, range.head.ch));
        }), doc.sel.primIndex);
        if (doc.cm) {
            regChange(doc.cm, doc.first, doc.first - distance, distance);
            for (var d = doc.cm.display, l = d.viewFrom; l < d.viewTo; l++)
                regLineChange(doc.cm, l, "gutter");
        }
    }

    // More lower-level change function, handling only a single document
    // (not linked ones).
    function makeChangeSingleDoc(doc, change, selAfter, spans) {
        if (doc.cm && !doc.cm.curOp)
            return operation(doc.cm, makeChangeSingleDoc)(doc, change, selAfter, spans);

        if (change.to.line < doc.first) {
            shiftDoc(doc, change.text.length - 1 - (change.to.line - change.from.line));
            return;
        }
        if (change.from.line > doc.lastLine()) return;

        // Clip the change to the size of this doc
        if (change.from.line < doc.first) {
            var shift = change.text.length - 1 - (doc.first - change.from.line);
            shiftDoc(doc, shift);
            change = {
                from: Pos(doc.first, 0),
                to: Pos(change.to.line + shift, change.to.ch),
                text: [lst(change.text)],
                origin: change.origin
            };
        }
        var last = doc.lastLine();
        if (change.to.line > last) {
            change = {
                from: change.from,
                to: Pos(last, getLine(doc, last).text.length),
                text: [change.text[0]],
                origin: change.origin
            };
        }

        change.removed = getBetween(doc, change.from, change.to);

        if (!selAfter) selAfter = computeSelAfterChange(doc, change);
        if (doc.cm) makeChangeSingleDocInEditor(doc.cm, change, spans);
        else updateDoc(doc, change, spans);
        setSelectionNoUndo(doc, selAfter, sel_dontScroll);
    }

    // Handle the interaction of a change to a document with the editor
    // that this document is part of.
    function makeChangeSingleDocInEditor(cm, change, spans) {
        var doc = cm.doc,
            display = cm.display,
            from = change.from,
            to = change.to;

        var recomputeMaxLength = false,
            checkWidthStart = from.line;
        if (!cm.options.lineWrapping) {
            checkWidthStart = lineNo(visualLine(getLine(doc, from.line)));
            doc.iter(checkWidthStart, to.line + 1, function(line) {
                if (line == display.maxLine) {
                    recomputeMaxLength = true;
                    return true;
                }
            });
        }

        if (doc.sel.contains(change.from, change.to) > -1)
            signalCursorActivity(cm);

        updateDoc(doc, change, spans, estimateHeight(cm));

        if (!cm.options.lineWrapping) {
            doc.iter(checkWidthStart, from.line + change.text.length, function(line) {
                var len = lineLength(line);
                if (len > display.maxLineLength) {
                    display.maxLine = line;
                    display.maxLineLength = len;
                    display.maxLineChanged = true;
                    recomputeMaxLength = false;
                }
            });
            if (recomputeMaxLength) cm.curOp.updateMaxLine = true;
        }

        // Adjust frontier, schedule worker
        doc.frontier = Math.min(doc.frontier, from.line);
        startWorker(cm, 400);

        var lendiff = change.text.length - (to.line - from.line) - 1;
        // Remember that these lines changed, for updating the display
        if (change.full)
            regChange(cm);
        else if (from.line == to.line && change.text.length == 1 && !isWholeLineUpdate(cm.doc, change))
            regLineChange(cm, from.line, "text");
        else
            regChange(cm, from.line, to.line + 1, lendiff);

        var changesHandler = hasHandler(cm, "changes"),
            changeHandler = hasHandler(cm, "change");
        if (changeHandler || changesHandler) {
            var obj = {
                from: from,
                to: to,
                text: change.text,
                removed: change.removed,
                origin: change.origin
            };
            if (changeHandler) signalLater(cm, "change", cm, obj);
            if (changesHandler)(cm.curOp.changeObjs || (cm.curOp.changeObjs = [])).push(obj);
        }
        cm.display.selForContextMenu = null;
    }

    function replaceRange(doc, code, from, to, origin) {
        if (!to) to = from;
        if (cmp(to, from) < 0) {
            var tmp = to;
            to = from;
            from = tmp;
        }
        if (typeof code == "string") code = splitLines(code);
        makeChange(doc, {
            from: from,
            to: to,
            text: code,
            origin: origin
        });
    }

    // SCROLLING THINGS INTO VIEW

    // If an editor sits on the top or bottom of the window, partially
    // scrolled out of view, this ensures that the cursor is visible.
    function maybeScrollWindow(cm, coords) {
        if (signalDOMEvent(cm, "scrollCursorIntoView")) return;

        var display = cm.display,
            box = display.sizer.getBoundingClientRect(),
            doScroll = null;
        if (coords.top + box.top < 0) doScroll = true;
        else if (coords.bottom + box.top > (window.innerHeight || document.documentElement.clientHeight)) doScroll = false;
        if (doScroll != null && !phantom) {
            var scrollNode = elt("div", "\u200b", null, "position: absolute; top: " +
                (coords.top - display.viewOffset - paddingTop(cm.display)) + "px; height: " +
                (coords.bottom - coords.top + scrollGap(cm) + display.barHeight) + "px; left: " +
                coords.left + "px; width: 2px;");
            cm.display.lineSpace.appendChild(scrollNode);
            scrollNode.scrollIntoView(doScroll);
            cm.display.lineSpace.removeChild(scrollNode);
        }
    }

    // Scroll a given position into view (immediately), verifying that
    // it actually became visible (as line heights are accurately
    // measured, the position of something may 'drift' during drawing).
    function scrollPosIntoView(cm, pos, end, margin) {
        if (margin == null) margin = 0;
        for (var limit = 0; limit < 5; limit++) {
            var changed = false,
                coords = cursorCoords(cm, pos);
            var endCoords = !end || end == pos ? coords : cursorCoords(cm, end);
            var scrollPos = calculateScrollPos(cm, Math.min(coords.left, endCoords.left),
                Math.min(coords.top, endCoords.top) - margin,
                Math.max(coords.left, endCoords.left),
                Math.max(coords.bottom, endCoords.bottom) + margin);
            var startTop = cm.doc.scrollTop,
                startLeft = cm.doc.scrollLeft;
            if (scrollPos.scrollTop != null) {
                setScrollTop(cm, scrollPos.scrollTop);
                if (Math.abs(cm.doc.scrollTop - startTop) > 1) changed = true;
            }
            if (scrollPos.scrollLeft != null) {
                setScrollLeft(cm, scrollPos.scrollLeft);
                if (Math.abs(cm.doc.scrollLeft - startLeft) > 1) changed = true;
            }
            if (!changed) break;
        }
        return coords;
    }

    // Scroll a given set of coordinates into view (immediately).
    function scrollIntoView(cm, x1, y1, x2, y2) {
        var scrollPos = calculateScrollPos(cm, x1, y1, x2, y2);
        if (scrollPos.scrollTop != null) setScrollTop(cm, scrollPos.scrollTop);
        if (scrollPos.scrollLeft != null) setScrollLeft(cm, scrollPos.scrollLeft);
    }

    // Calculate a new scroll position needed to scroll the given
    // rectangle into view. Returns an object with scrollTop and
    // scrollLeft properties. When these are undefined, the
    // vertical/horizontal position does not need to be adjusted.
    function calculateScrollPos(cm, x1, y1, x2, y2) {
        var display = cm.display,
            snapMargin = textHeight(cm.display);
        if (y1 < 0) y1 = 0;
        var screentop = cm.curOp && cm.curOp.scrollTop != null ? cm.curOp.scrollTop : display.scroller.scrollTop;
        var screen = displayHeight(cm),
            result = {};
        if (y2 - y1 > screen) y2 = y1 + screen;
        var docBottom = cm.doc.height + paddingVert(display);
        var atTop = y1 < snapMargin,
            atBottom = y2 > docBottom - snapMargin;
        if (y1 < screentop) {
            result.scrollTop = atTop ? 0 : y1;
        } else if (y2 > screentop + screen) {
            var newTop = Math.min(y1, (atBottom ? docBottom : y2) - screen);
            if (newTop != screentop) result.scrollTop = newTop;
        }

        var screenleft = cm.curOp && cm.curOp.scrollLeft != null ? cm.curOp.scrollLeft : display.scroller.scrollLeft;
        var screenw = displayWidth(cm) - (cm.options.fixedGutter ? display.gutters.offsetWidth : 0);
        var tooWide = x2 - x1 > screenw;
        if (tooWide) x2 = x1 + screenw;
        if (x1 < 10)
            result.scrollLeft = 0;
        else if (x1 < screenleft)
            result.scrollLeft = Math.max(0, x1 - (tooWide ? 0 : 10));
        else if (x2 > screenw + screenleft - 3)
            result.scrollLeft = x2 + (tooWide ? 0 : 10) - screenw;
        return result;
    }

    // Store a relative adjustment to the scroll position in the current
    // operation (to be applied when the operation finishes).
    function addToScrollPos(cm, left, top) {
        if (left != null || top != null) resolveScrollToPos(cm);
        if (left != null)
            cm.curOp.scrollLeft = (cm.curOp.scrollLeft == null ? cm.doc.scrollLeft : cm.curOp.scrollLeft) + left;
        if (top != null)
            cm.curOp.scrollTop = (cm.curOp.scrollTop == null ? cm.doc.scrollTop : cm.curOp.scrollTop) + top;
    }

    // Make sure that at the end of the operation the current cursor is
    // shown.
    function ensureCursorVisible(cm) {
        resolveScrollToPos(cm);
        var cur = cm.getCursor(),
            from = cur,
            to = cur;
        if (!cm.options.lineWrapping) {
            from = cur.ch ? Pos(cur.line, cur.ch - 1) : cur;
            to = Pos(cur.line, cur.ch + 1);
        }
        cm.curOp.scrollToPos = {
            from: from,
            to: to,
            margin: cm.options.cursorScrollMargin,
            isCursor: true
        };
    }

    // When an operation has its scrollToPos property set, and another
    // scroll action is applied before the end of the operation, this
    // 'simulates' scrolling that position into view in a cheap way, so
    // that the effect of intermediate scroll commands is not ignored.
    function resolveScrollToPos(cm) {
        var range = cm.curOp.scrollToPos;
        if (range) {
            cm.curOp.scrollToPos = null;
            var from = estimateCoords(cm, range.from),
                to = estimateCoords(cm, range.to);
            var sPos = calculateScrollPos(cm, Math.min(from.left, to.left),
                Math.min(from.top, to.top) - range.margin,
                Math.max(from.right, to.right),
                Math.max(from.bottom, to.bottom) + range.margin);
            cm.scrollTo(sPos.scrollLeft, sPos.scrollTop);
        }
    }

    // API UTILITIES

    // Indent the given line. The how parameter can be "smart",
    // "add"/null, "subtract", or "prev". When aggressive is false
    // (typically set to true for forced single-line indents), empty
    // lines are not indented, and places where the mode returns Pass
    // are left alone.
    function indentLine(cm, n, how, aggressive) {
        var doc = cm.doc,
            state;
        if (how == null) how = "add";
        if (how == "smart") {
            // Fall back to "prev" when the mode doesn't have an indentation
            // method.
            if (!doc.mode.indent) how = "prev";
            else state = getStateBefore(cm, n);
        }

        var tabSize = cm.options.tabSize;
        var line = getLine(doc, n),
            curSpace = countColumn(line.text, null, tabSize);
        if (line.stateAfter) line.stateAfter = null;
        var curSpaceString = line.text.match(/^\s*/)[0],
            indentation;
        if (!aggressive && !/\S/.test(line.text)) {
            indentation = 0;
            how = "not";
        } else if (how == "smart") {
            indentation = doc.mode.indent(state, line.text.slice(curSpaceString.length), line.text);
            if (indentation == Pass || indentation > 150) {
                if (!aggressive) return;
                how = "prev";
            }
        }
        if (how == "prev") {
            if (n > doc.first) indentation = countColumn(getLine(doc, n - 1).text, null, tabSize);
            else indentation = 0;
        } else if (how == "add") {
            indentation = curSpace + cm.options.indentUnit;
        } else if (how == "subtract") {
            indentation = curSpace - cm.options.indentUnit;
        } else if (typeof how == "number") {
            indentation = curSpace + how;
        }
        indentation = Math.max(0, indentation);

        var indentString = "",
            pos = 0;
        if (cm.options.indentWithTabs)
            for (var i = Math.floor(indentation / tabSize); i; --i) {
                pos += tabSize;
                indentString += "\t";
            }
        if (pos < indentation) indentString += spaceStr(indentation - pos);

        if (indentString != curSpaceString) {
            replaceRange(doc, indentString, Pos(n, 0), Pos(n, curSpaceString.length), "+input");
            line.stateAfter = null;
            return true;
        } else {
            // Ensure that, if the cursor was in the whitespace at the start
            // of the line, it is moved to the end of that space.
            for (var i = 0; i < doc.sel.ranges.length; i++) {
                var range = doc.sel.ranges[i];
                if (range.head.line == n && range.head.ch < curSpaceString.length) {
                    var pos = Pos(n, curSpaceString.length);
                    replaceOneSelection(doc, i, new Range(pos, pos));
                    break;
                }
            }
        }
    }

    // Utility for applying a change to a line by handle or number,
    // returning the number and optionally registering the line as
    // changed.
    function changeLine(doc, handle, changeType, op) {
        var no = handle,
            line = handle;
        if (typeof handle == "number") line = getLine(doc, clipLine(doc, handle));
        else no = lineNo(handle);
        if (no == null) return null;
        if (op(line, no) && doc.cm) regLineChange(doc.cm, no, changeType);
        return line;
    }

    // Helper for deleting text near the selection(s), used to implement
    // backspace, delete, and similar functionality.
    function deleteNearSelection(cm, compute) {
        var ranges = cm.doc.sel.ranges,
            kill = [];
        // Build up a set of ranges to kill first, merging overlapping
        // ranges.
        for (var i = 0; i < ranges.length; i++) {
            var toKill = compute(ranges[i]);
            while (kill.length && cmp(toKill.from, lst(kill).to) <= 0) {
                var replaced = kill.pop();
                if (cmp(replaced.from, toKill.from) < 0) {
                    toKill.from = replaced.from;
                    break;
                }
            }
            kill.push(toKill);
        }
        // Next, remove those actual ranges.
        runInOp(cm, function() {
            for (var i = kill.length - 1; i >= 0; i--)
                replaceRange(cm.doc, "", kill[i].from, kill[i].to, "+delete");
            ensureCursorVisible(cm);
        });
    }

    // Used for horizontal relative motion. Dir is -1 or 1 (left or
    // right), unit can be "char", "column" (like char, but doesn't
    // cross line boundaries), "word" (across next word), or "group" (to
    // the start of next group of word or non-word-non-whitespace
    // chars). The visually param controls whether, in right-to-left
    // text, direction 1 means to move towards the next index in the
    // string, or towards the character to the right of the current
    // position. The resulting position will have a hitSide=true
    // property if it reached the end of the document.
    function findPosH(doc, pos, dir, unit, visually) {
        var line = pos.line,
            ch = pos.ch,
            origDir = dir;
        var lineObj = getLine(doc, line);
        var possible = true;

        function findNextLine() {
            var l = line + dir;
            if (l < doc.first || l >= doc.first + doc.size) return (possible = false);
            line = l;
            return lineObj = getLine(doc, l);
        }

        function moveOnce(boundToLine) {
            var next = (visually ? moveVisually : moveLogically)(lineObj, ch, dir, true);
            if (next == null) {
                if (!boundToLine && findNextLine()) {
                    if (visually) ch = (dir < 0 ? lineRight : lineLeft)(lineObj);
                    else ch = dir < 0 ? lineObj.text.length : 0;
                } else return (possible = false);
            } else ch = next;
            return true;
        }

        if (unit == "char") moveOnce();
        else if (unit == "column") moveOnce(true);
        else if (unit == "word" || unit == "group") {
            var sawType = null,
                group = unit == "group";
            var helper = doc.cm && doc.cm.getHelper(pos, "wordChars");
            for (var first = true;; first = false) {
                if (dir < 0 && !moveOnce(!first)) break;
                var cur = lineObj.text.charAt(ch) || "\n";
                var type = isWordChar(cur, helper) ? "w" : group && cur == "\n" ? "n" : !group || /\s/.test(cur) ? null : "p";
                if (group && !first && !type) type = "s";
                if (sawType && sawType != type) {
                    if (dir < 0) {
                        dir = 1;
                        moveOnce();
                    }
                    break;
                }

                if (type) sawType = type;
                if (dir > 0 && !moveOnce(!first)) break;
            }
        }
        var result = skipAtomic(doc, Pos(line, ch), origDir, true);
        if (!possible) result.hitSide = true;
        return result;
    }

    // For relative vertical movement. Dir may be -1 or 1. Unit can be
    // "page" or "line". The resulting position will have a hitSide=true
    // property if it reached the end of the document.
    function findPosV(cm, pos, dir, unit) {
        var doc = cm.doc,
            x = pos.left,
            y;
        if (unit == "page") {
            var pageSize = Math.min(cm.display.wrapper.clientHeight, window.innerHeight || document.documentElement.clientHeight);
            y = pos.top + dir * (pageSize - (dir < 0 ? 1.5 : .5) * textHeight(cm.display));
        } else if (unit == "line") {
            y = dir > 0 ? pos.bottom + 3 : pos.top - 3;
        }
        for (;;) {
            var target = coordsChar(cm, x, y);
            if (!target.outside) break;
            if (dir < 0 ? y <= 0 : y >= doc.height) {
                target.hitSide = true;
                break;
            }
            y += dir * 5;
        }
        return target;
    }

    // EDITOR METHODS

    // The publicly visible API. Note that methodOp(f) means
    // 'wrap f in an operation, performed on its `this` parameter'.

    // This is not the complete set of editor methods. Most of the
    // methods defined on the Doc type are also injected into
    // CodeMirror.prototype, for backwards compatibility and
    // convenience.

    CodeMirror.prototype = {
        constructor: CodeMirror,
        focus: function() {
            window.focus();
            this.display.input.focus();
        },

        setOption: function(option, value) {
            var options = this.options,
                old = options[option];
            if (options[option] == value && option != "mode") return;
            options[option] = value;
            if (optionHandlers.hasOwnProperty(option))
                operation(this, optionHandlers[option])(this, value, old);
        },

        getOption: function(option) {
            return this.options[option];
        },
        getDoc: function() {
            return this.doc;
        },

        addKeyMap: function(map, bottom) {
            this.state.keyMaps[bottom ? "push" : "unshift"](getKeyMap(map));
        },
        removeKeyMap: function(map) {
            var maps = this.state.keyMaps;
            for (var i = 0; i < maps.length; ++i)
                if (maps[i] == map || maps[i].name == map) {
                    maps.splice(i, 1);
                    return true;
                }
        },

        addOverlay: methodOp(function(spec, options) {
            var mode = spec.token ? spec : CodeMirror.getMode(this.options, spec);
            if (mode.startState) throw new Error("Overlays may not be stateful.");
            this.state.overlays.push({
                mode: mode,
                modeSpec: spec,
                opaque: options && options.opaque
            });
            this.state.modeGen++;
            regChange(this);
        }),
        removeOverlay: methodOp(function(spec) {
            var overlays = this.state.overlays;
            for (var i = 0; i < overlays.length; ++i) {
                var cur = overlays[i].modeSpec;
                if (cur == spec || typeof spec == "string" && cur.name == spec) {
                    overlays.splice(i, 1);
                    this.state.modeGen++;
                    regChange(this);
                    return;
                }
            }
        }),

        indentLine: methodOp(function(n, dir, aggressive) {
            if (typeof dir != "string" && typeof dir != "number") {
                if (dir == null) dir = this.options.smartIndent ? "smart" : "prev";
                else dir = dir ? "add" : "subtract";
            }
            if (isLine(this.doc, n)) indentLine(this, n, dir, aggressive);
        }),
        indentSelection: methodOp(function(how) {
            var ranges = this.doc.sel.ranges,
                end = -1;
            for (var i = 0; i < ranges.length; i++) {
                var range = ranges[i];
                if (!range.empty()) {
                    var from = range.from(),
                        to = range.to();
                    var start = Math.max(end, from.line);
                    end = Math.min(this.lastLine(), to.line - (to.ch ? 0 : 1)) + 1;
                    for (var j = start; j < end; ++j)
                        indentLine(this, j, how);
                    var newRanges = this.doc.sel.ranges;
                    if (from.ch == 0 && ranges.length == newRanges.length && newRanges[i].from().ch > 0)
                        replaceOneSelection(this.doc, i, new Range(from, newRanges[i].to()), sel_dontScroll);
                } else if (range.head.line > end) {
                    indentLine(this, range.head.line, how, true);
                    end = range.head.line;
                    if (i == this.doc.sel.primIndex) ensureCursorVisible(this);
                }
            }
        }),

        // Fetch the parser token for a given character. Useful for hacks
        // that want to inspect the mode state (say, for completion).
        getTokenAt: function(pos, precise) {
            return takeToken(this, pos, precise);
        },

        getLineTokens: function(line, precise) {
            return takeToken(this, Pos(line), precise, true);
        },

        getTokenTypeAt: function(pos) {
            pos = clipPos(this.doc, pos);
            var styles = getLineStyles(this, getLine(this.doc, pos.line));
            var before = 0,
                after = (styles.length - 1) / 2,
                ch = pos.ch;
            var type;
            if (ch == 0) type = styles[2];
            else
                for (;;) {
                    var mid = (before + after) >> 1;
                    if ((mid ? styles[mid * 2 - 1] : 0) >= ch) after = mid;
                    else if (styles[mid * 2 + 1] < ch) before = mid + 1;
                    else {
                        type = styles[mid * 2 + 2];
                        break;
                    }
                }
            var cut = type ? type.indexOf("cm-overlay ") : -1;
            return cut < 0 ? type : cut == 0 ? null : type.slice(0, cut - 1);
        },

        getModeAt: function(pos) {
            var mode = this.doc.mode;
            if (!mode.innerMode) return mode;
            return CodeMirror.innerMode(mode, this.getTokenAt(pos).state).mode;
        },

        getHelper: function(pos, type) {
            return this.getHelpers(pos, type)[0];
        },

        getHelpers: function(pos, type) {
            var found = [];
            if (!helpers.hasOwnProperty(type)) return found;
            var help = helpers[type],
                mode = this.getModeAt(pos);
            if (typeof mode[type] == "string") {
                if (help[mode[type]]) found.push(help[mode[type]]);
            } else if (mode[type]) {
                for (var i = 0; i < mode[type].length; i++) {
                    var val = help[mode[type][i]];
                    if (val) found.push(val);
                }
            } else if (mode.helperType && help[mode.helperType]) {
                found.push(help[mode.helperType]);
            } else if (help[mode.name]) {
                found.push(help[mode.name]);
            }
            for (var i = 0; i < help._global.length; i++) {
                var cur = help._global[i];
                if (cur.pred(mode, this) && indexOf(found, cur.val) == -1)
                    found.push(cur.val);
            }
            return found;
        },

        getStateAfter: function(line, precise) {
            var doc = this.doc;
            line = clipLine(doc, line == null ? doc.first + doc.size - 1 : line);
            return getStateBefore(this, line + 1, precise);
        },

        cursorCoords: function(start, mode) {
            var pos, range = this.doc.sel.primary();
            if (start == null) pos = range.head;
            else if (typeof start == "object") pos = clipPos(this.doc, start);
            else pos = start ? range.from() : range.to();
            return cursorCoords(this, pos, mode || "page");
        },

        charCoords: function(pos, mode) {
            return charCoords(this, clipPos(this.doc, pos), mode || "page");
        },

        coordsChar: function(coords, mode) {
            coords = fromCoordSystem(this, coords, mode || "page");
            return coordsChar(this, coords.left, coords.top);
        },

        lineAtHeight: function(height, mode) {
            height = fromCoordSystem(this, {
                top: height,
                left: 0
            }, mode || "page").top;
            return lineAtHeight(this.doc, height + this.display.viewOffset);
        },
        heightAtLine: function(line, mode) {
            var end = false,
                lineObj;
            if (typeof line == "number") {
                var last = this.doc.first + this.doc.size - 1;
                if (line < this.doc.first) line = this.doc.first;
                else if (line > last) {
                    line = last;
                    end = true;
                }
                lineObj = getLine(this.doc, line);
            } else {
                lineObj = line;
            }
            return intoCoordSystem(this, lineObj, {
                    top: 0,
                    left: 0
                }, mode || "page").top +
                (end ? this.doc.height - heightAtLine(lineObj) : 0);
        },

        defaultTextHeight: function() {
            return textHeight(this.display);
        },
        defaultCharWidth: function() {
            return charWidth(this.display);
        },

        setGutterMarker: methodOp(function(line, gutterID, value) {
            return changeLine(this.doc, line, "gutter", function(line) {
                var markers = line.gutterMarkers || (line.gutterMarkers = {});
                markers[gutterID] = value;
                if (!value && isEmpty(markers)) line.gutterMarkers = null;
                return true;
            });
        }),

        clearGutter: methodOp(function(gutterID) {
            var cm = this,
                doc = cm.doc,
                i = doc.first;
            doc.iter(function(line) {
                if (line.gutterMarkers && line.gutterMarkers[gutterID]) {
                    line.gutterMarkers[gutterID] = null;
                    regLineChange(cm, i, "gutter");
                    if (isEmpty(line.gutterMarkers)) line.gutterMarkers = null;
                }
                ++i;
            });
        }),

        lineInfo: function(line) {
            if (typeof line == "number") {
                if (!isLine(this.doc, line)) return null;
                var n = line;
                line = getLine(this.doc, line);
                if (!line) return null;
            } else {
                var n = lineNo(line);
                if (n == null) return null;
            }
            return {
                line: n,
                handle: line,
                text: line.text,
                gutterMarkers: line.gutterMarkers,
                textClass: line.textClass,
                bgClass: line.bgClass,
                wrapClass: line.wrapClass,
                widgets: line.widgets
            };
        },

        getViewport: function() {
            return {
                from: this.display.viewFrom,
                to: this.display.viewTo
            };
        },

        addWidget: function(pos, node, scroll, vert, horiz) {
            var display = this.display;
            pos = cursorCoords(this, clipPos(this.doc, pos));
            var top = pos.bottom,
                left = pos.left;
            node.style.position = "absolute";
            node.setAttribute("cm-ignore-events", "true");
            this.display.input.setUneditable(node);
            display.sizer.appendChild(node);
            if (vert == "over") {
                top = pos.top;
            } else if (vert == "above" || vert == "near") {
                var vspace = Math.max(display.wrapper.clientHeight, this.doc.height),
                    hspace = Math.max(display.sizer.clientWidth, display.lineSpace.clientWidth);
                // Default to positioning above (if specified and possible); otherwise default to positioning below
                if ((vert == 'above' || pos.bottom + node.offsetHeight > vspace) && pos.top > node.offsetHeight)
                    top = pos.top - node.offsetHeight;
                else if (pos.bottom + node.offsetHeight <= vspace)
                    top = pos.bottom;
                if (left + node.offsetWidth > hspace)
                    left = hspace - node.offsetWidth;
            }
            node.style.top = top + "px";
            node.style.left = node.style.right = "";
            if (horiz == "right") {
                left = display.sizer.clientWidth - node.offsetWidth;
                node.style.right = "0px";
            } else {
                if (horiz == "left") left = 0;
                else if (horiz == "middle") left = (display.sizer.clientWidth - node.offsetWidth) / 2;
                node.style.left = left + "px";
            }
            if (scroll)
                scrollIntoView(this, left, top, left + node.offsetWidth, top + node.offsetHeight);
        },

        triggerOnKeyDown: methodOp(onKeyDown),
        triggerOnKeyPress: methodOp(onKeyPress),
        triggerOnKeyUp: onKeyUp,

        execCommand: function(cmd) {
            if (commands.hasOwnProperty(cmd))
                return commands[cmd](this);
        },

        triggerElectric: methodOp(function(text) {
            triggerElectric(this, text);
        }),

        findPosH: function(from, amount, unit, visually) {
            var dir = 1;
            if (amount < 0) {
                dir = -1;
                amount = -amount;
            }
            for (var i = 0, cur = clipPos(this.doc, from); i < amount; ++i) {
                cur = findPosH(this.doc, cur, dir, unit, visually);
                if (cur.hitSide) break;
            }
            return cur;
        },

        moveH: methodOp(function(dir, unit) {
            var cm = this;
            cm.extendSelectionsBy(function(range) {
                if (cm.display.shift || cm.doc.extend || range.empty())
                    return findPosH(cm.doc, range.head, dir, unit, cm.options.rtlMoveVisually);
                else
                    return dir < 0 ? range.from() : range.to();
            }, sel_move);
        }),

        deleteH: methodOp(function(dir, unit) {
            var sel = this.doc.sel,
                doc = this.doc;
            if (sel.somethingSelected())
                doc.replaceSelection("", null, "+delete");
            else
                deleteNearSelection(this, function(range) {
                    var other = findPosH(doc, range.head, dir, unit, false);
                    return dir < 0 ? {
                        from: other,
                        to: range.head
                    } : {
                        from: range.head,
                        to: other
                    };
                });
        }),

        findPosV: function(from, amount, unit, goalColumn) {
            var dir = 1,
                x = goalColumn;
            if (amount < 0) {
                dir = -1;
                amount = -amount;
            }
            for (var i = 0, cur = clipPos(this.doc, from); i < amount; ++i) {
                var coords = cursorCoords(this, cur, "div");
                if (x == null) x = coords.left;
                else coords.left = x;
                cur = findPosV(this, coords, dir, unit);
                if (cur.hitSide) break;
            }
            return cur;
        },

        moveV: methodOp(function(dir, unit) {
            var cm = this,
                doc = this.doc,
                goals = [];
            var collapse = !cm.display.shift && !doc.extend && doc.sel.somethingSelected();
            doc.extendSelectionsBy(function(range) {
                if (collapse)
                    return dir < 0 ? range.from() : range.to();
                var headPos = cursorCoords(cm, range.head, "div");
                if (range.goalColumn != null) headPos.left = range.goalColumn;
                goals.push(headPos.left);
                var pos = findPosV(cm, headPos, dir, unit);
                if (unit == "page" && range == doc.sel.primary())
                    addToScrollPos(cm, null, charCoords(cm, pos, "div").top - headPos.top);
                return pos;
            }, sel_move);
            if (goals.length)
                for (var i = 0; i < doc.sel.ranges.length; i++)
                    doc.sel.ranges[i].goalColumn = goals[i];
        }),

        // Find the word at the given position (as returned by coordsChar).
        findWordAt: function(pos) {
            var doc = this.doc,
                line = getLine(doc, pos.line).text;
            var start = pos.ch,
                end = pos.ch;
            if (line) {
                var helper = this.getHelper(pos, "wordChars");
                if ((pos.xRel < 0 || end == line.length) && start) --start;
                else ++end;
                var startChar = line.charAt(start);
                var check = isWordChar(startChar, helper) ? function(ch) {
                    return isWordChar(ch, helper);
                } : /\s/.test(startChar) ? function(ch) {
                    return /\s/.test(ch);
                } : function(ch) {
                    return !/\s/.test(ch) && !isWordChar(ch);
                };
                while (start > 0 && check(line.charAt(start - 1))) --start;
                while (end < line.length && check(line.charAt(end))) ++end;
            }
            return new Range(Pos(pos.line, start), Pos(pos.line, end));
        },

        toggleOverwrite: function(value) {
            if (value != null && value == this.state.overwrite) return;
            if (this.state.overwrite = !this.state.overwrite)
                addClass(this.display.cursorDiv, "CodeMirror-overwrite");
            else
                rmClass(this.display.cursorDiv, "CodeMirror-overwrite");

            signal(this, "overwriteToggle", this, this.state.overwrite);
        },
        hasFocus: function() {
            return this.display.input.getField() == activeElt();
        },

        scrollTo: methodOp(function(x, y) {
            if (x != null || y != null) resolveScrollToPos(this);
            if (x != null) this.curOp.scrollLeft = x;
            if (y != null) this.curOp.scrollTop = y;
        }),
        getScrollInfo: function() {
            var scroller = this.display.scroller;
            return {
                left: scroller.scrollLeft,
                top: scroller.scrollTop,
                height: scroller.scrollHeight - scrollGap(this) - this.display.barHeight,
                width: scroller.scrollWidth - scrollGap(this) - this.display.barWidth,
                clientHeight: displayHeight(this),
                clientWidth: displayWidth(this)
            };
        },

        scrollIntoView: methodOp(function(range, margin) {
            if (range == null) {
                range = {
                    from: this.doc.sel.primary().head,
                    to: null
                };
                if (margin == null) margin = this.options.cursorScrollMargin;
            } else if (typeof range == "number") {
                range = {
                    from: Pos(range, 0),
                    to: null
                };
            } else if (range.from == null) {
                range = {
                    from: range,
                    to: null
                };
            }
            if (!range.to) range.to = range.from;
            range.margin = margin || 0;

            if (range.from.line != null) {
                resolveScrollToPos(this);
                this.curOp.scrollToPos = range;
            } else {
                var sPos = calculateScrollPos(this, Math.min(range.from.left, range.to.left),
                    Math.min(range.from.top, range.to.top) - range.margin,
                    Math.max(range.from.right, range.to.right),
                    Math.max(range.from.bottom, range.to.bottom) + range.margin);
                this.scrollTo(sPos.scrollLeft, sPos.scrollTop);
            }
        }),

        setSize: methodOp(function(width, height) {
            var cm = this;

            function interpret(val) {
                return typeof val == "number" || /^\d+$/.test(String(val)) ? val + "px" : val;
            }
            if (width != null) cm.display.wrapper.style.width = interpret(width);
            if (height != null) cm.display.wrapper.style.height = interpret(height);
            if (cm.options.lineWrapping) clearLineMeasurementCache(this);
            var lineNo = cm.display.viewFrom;
            cm.doc.iter(lineNo, cm.display.viewTo, function(line) {
                if (line.widgets)
                    for (var i = 0; i < line.widgets.length; i++)
                        if (line.widgets[i].noHScroll) {
                            regLineChange(cm, lineNo, "widget");
                            break;
                        }
                        ++lineNo;
            });
            cm.curOp.forceUpdate = true;
            signal(cm, "refresh", this);
        }),

        operation: function(f) {
            return runInOp(this, f);
        },

        refresh: methodOp(function() {
            var oldHeight = this.display.cachedTextHeight;
            regChange(this);
            this.curOp.forceUpdate = true;
            clearCaches(this);
            this.scrollTo(this.doc.scrollLeft, this.doc.scrollTop);
            updateGutterSpace(this);
            if (oldHeight == null || Math.abs(oldHeight - textHeight(this.display)) > .5)
                estimateLineHeights(this);
            signal(this, "refresh", this);
        }),

        swapDoc: methodOp(function(doc) {
            var old = this.doc;
            old.cm = null;
            attachDoc(this, doc);
            clearCaches(this);
            this.display.input.reset();
            this.scrollTo(doc.scrollLeft, doc.scrollTop);
            this.curOp.forceScroll = true;
            signalLater(this, "swapDoc", this, old);
            return old;
        }),

        getInputField: function() {
            return this.display.input.getField();
        },
        getWrapperElement: function() {
            return this.display.wrapper;
        },
        getScrollerElement: function() {
            return this.display.scroller;
        },
        getGutterElement: function() {
            return this.display.gutters;
        }
    };
    eventMixin(CodeMirror);

    // OPTION DEFAULTS

    // The default configuration options.
    var defaults = CodeMirror.defaults = {};
    // Functions to run when options are changed.
    var optionHandlers = CodeMirror.optionHandlers = {};

    function option(name, deflt, handle, notOnInit) {
        CodeMirror.defaults[name] = deflt;
        if (handle) optionHandlers[name] =
            notOnInit ? function(cm, val, old) {
                if (old != Init) handle(cm, val, old);
            } : handle;
    }

    // Passed to option handlers when there is no old value.
    var Init = CodeMirror.Init = {
        toString: function() {
            return "CodeMirror.Init";
        }
    };

    // These two are, on init, called from the constructor because they
    // have to be initialized before the editor can start at all.
    option("value", "", function(cm, val) {
        cm.setValue(val);
    }, true);
    option("mode", null, function(cm, val) {
        cm.doc.modeOption = val;
        loadMode(cm);
    }, true);

    option("indentUnit", 2, loadMode, true);
    option("indentWithTabs", false);
    option("smartIndent", true);
    option("tabSize", 4, function(cm) {
        resetModeState(cm);
        clearCaches(cm);
        regChange(cm);
    }, true);
    option("specialChars", /[\t\u0000-\u0019\u00ad\u200b-\u200f\u2028\u2029\ufeff]/g, function(cm, val, old) {
        cm.state.specialChars = new RegExp(val.source + (val.test("\t") ? "" : "|\t"), "g");
        if (old != CodeMirror.Init) cm.refresh();
    });
    option("specialCharPlaceholder", defaultSpecialCharPlaceholder, function(cm) {
        cm.refresh();
    }, true);
    option("electricChars", true);
    option("inputStyle", mobile ? "contenteditable" : "textarea", function() {
        throw new Error("inputStyle can not (yet) be changed in a running editor"); // FIXME
    }, true);
    option("rtlMoveVisually", !windows);
    option("wholeLineUpdateBefore", true);

    option("theme", "default", function(cm) {
        themeChanged(cm);
        guttersChanged(cm);
    }, true);
    option("keyMap", "default", function(cm, val, old) {
        var next = getKeyMap(val);
        var prev = old != CodeMirror.Init && getKeyMap(old);
        if (prev && prev.detach) prev.detach(cm, next);
        if (next.attach) next.attach(cm, prev || null);
    });
    option("extraKeys", null);

    option("lineWrapping", false, wrappingChanged, true);
    option("gutters", [], function(cm) {
        setGuttersForLineNumbers(cm.options);
        guttersChanged(cm);
    }, true);
    option("fixedGutter", true, function(cm, val) {
        cm.display.gutters.style.left = val ? compensateForHScroll(cm.display) + "px" : "0";
        cm.refresh();
    }, true);
    option("coverGutterNextToScrollbar", false, function(cm) {
        updateScrollbars(cm);
    }, true);
    option("scrollbarStyle", "native", function(cm) {
        initScrollbars(cm);
        updateScrollbars(cm);
        cm.display.scrollbars.setScrollTop(cm.doc.scrollTop);
        cm.display.scrollbars.setScrollLeft(cm.doc.scrollLeft);
    }, true);
    option("lineNumbers", false, function(cm) {
        setGuttersForLineNumbers(cm.options);
        guttersChanged(cm);
    }, true);
    option("firstLineNumber", 1, guttersChanged, true);
    option("lineNumberFormatter", function(integer) {
        return integer;
    }, guttersChanged, true);
    option("showCursorWhenSelecting", false, updateSelection, true);

    option("resetSelectionOnContextMenu", true);
    option("lineWiseCopyCut", true);

    option("readOnly", false, function(cm, val) {
        if (val == "nocursor") {
            onBlur(cm);
            cm.display.input.blur();
            cm.display.disabled = true;
        } else {
            cm.display.disabled = false;
            if (!val) cm.display.input.reset();
        }
    });
    option("disableInput", false, function(cm, val) {
        if (!val) cm.display.input.reset();
    }, true);
    option("dragDrop", true, dragDropChanged);

    option("cursorBlinkRate", 530);
    option("cursorScrollMargin", 0);
    option("cursorHeight", 1, updateSelection, true);
    option("singleCursorHeightPerLine", true, updateSelection, true);
    option("workTime", 100);
    option("workDelay", 100);
    option("flattenSpans", true, resetModeState, true);
    option("addModeClass", false, resetModeState, true);
    option("pollInterval", 100);
    option("undoDepth", 200, function(cm, val) {
        cm.doc.history.undoDepth = val;
    });
    option("historyEventDelay", 1250);
    option("viewportMargin", 10, function(cm) {
        cm.refresh();
    }, true);
    option("maxHighlightLength", 10000, resetModeState, true);
    option("moveInputWithCursor", true, function(cm, val) {
        if (!val) cm.display.input.resetPosition();
    });

    option("tabindex", null, function(cm, val) {
        cm.display.input.getField().tabIndex = val || "";
    });
    option("autofocus", null);

    // MODE DEFINITION AND QUERYING

    // Known modes, by name and by MIME
    var modes = CodeMirror.modes = {},
        mimeModes = CodeMirror.mimeModes = {};

    // Extra arguments are stored as the mode's dependencies, which is
    // used by (legacy) mechanisms like loadmode.js to automatically
    // load a mode. (Preferred mechanism is the require/define calls.)
    CodeMirror.defineMode = function(name, mode) {
        if (!CodeMirror.defaults.mode && name != "null") CodeMirror.defaults.mode = name;
        if (arguments.length > 2)
            mode.dependencies = Array.prototype.slice.call(arguments, 2);
        modes[name] = mode;
    };

    CodeMirror.defineMIME = function(mime, spec) {
        mimeModes[mime] = spec;
    };

    // Given a MIME type, a {name, ...options} config object, or a name
    // string, return a mode config object.
    CodeMirror.resolveMode = function(spec) {
        if (typeof spec == "string" && mimeModes.hasOwnProperty(spec)) {
            spec = mimeModes[spec];
        } else if (spec && typeof spec.name == "string" && mimeModes.hasOwnProperty(spec.name)) {
            var found = mimeModes[spec.name];
            if (typeof found == "string") found = {
                name: found
            };
            spec = createObj(found, spec);
            spec.name = found.name;
        } else if (typeof spec == "string" && /^[\w\-]+\/[\w\-]+\+xml$/.test(spec)) {
            return CodeMirror.resolveMode("application/xml");
        }
        if (typeof spec == "string") return {
            name: spec
        };
        else return spec || {
            name: "null"
        };
    };

    // Given a mode spec (anything that resolveMode accepts), find and
    // initialize an actual mode object.
    CodeMirror.getMode = function(options, spec) {
        var spec = CodeMirror.resolveMode(spec);
        var mfactory = modes[spec.name];
        if (!mfactory) return CodeMirror.getMode(options, "text/plain");
        var modeObj = mfactory(options, spec);
        if (modeExtensions.hasOwnProperty(spec.name)) {
            var exts = modeExtensions[spec.name];
            for (var prop in exts) {
                if (!exts.hasOwnProperty(prop)) continue;
                if (modeObj.hasOwnProperty(prop)) modeObj["_" + prop] = modeObj[prop];
                modeObj[prop] = exts[prop];
            }
        }
        modeObj.name = spec.name;
        if (spec.helperType) modeObj.helperType = spec.helperType;
        if (spec.modeProps)
            for (var prop in spec.modeProps)
                modeObj[prop] = spec.modeProps[prop];

        return modeObj;
    };

    // Minimal default mode.
    CodeMirror.defineMode("null", function() {
        return {
            token: function(stream) {
                stream.skipToEnd();
            }
        };
    });
    CodeMirror.defineMIME("text/plain", "null");

    // This can be used to attach properties to mode objects from
    // outside the actual mode definition.
    var modeExtensions = CodeMirror.modeExtensions = {};
    CodeMirror.extendMode = function(mode, properties) {
        var exts = modeExtensions.hasOwnProperty(mode) ? modeExtensions[mode] : (modeExtensions[mode] = {});
        copyObj(properties, exts);
    };

    // EXTENSIONS

    CodeMirror.defineExtension = function(name, func) {
        CodeMirror.prototype[name] = func;
    };
    CodeMirror.defineDocExtension = function(name, func) {
        Doc.prototype[name] = func;
    };
    CodeMirror.defineOption = option;

    var initHooks = [];
    CodeMirror.defineInitHook = function(f) {
        initHooks.push(f);
    };

    var helpers = CodeMirror.helpers = {};
    CodeMirror.registerHelper = function(type, name, value) {
        if (!helpers.hasOwnProperty(type)) helpers[type] = CodeMirror[type] = {
            _global: []
        };
        helpers[type][name] = value;
    };
    CodeMirror.registerGlobalHelper = function(type, name, predicate, value) {
        CodeMirror.registerHelper(type, name, value);
        helpers[type]._global.push({
            pred: predicate,
            val: value
        });
    };

    // MODE STATE HANDLING

    // Utility functions for working with state. Exported because nested
    // modes need to do this for their inner modes.

    var copyState = CodeMirror.copyState = function(mode, state) {
        if (state === true) return state;
        if (mode.copyState) return mode.copyState(state);
        var nstate = {};
        for (var n in state) {
            var val = state[n];
            if (val instanceof Array) val = val.concat([]);
            nstate[n] = val;
        }
        return nstate;
    };

    var startState = CodeMirror.startState = function(mode, a1, a2) {
        return mode.startState ? mode.startState(a1, a2) : true;
    };

    // Given a mode and a state (for that mode), find the inner mode and
    // state at the position that the state refers to.
    CodeMirror.innerMode = function(mode, state) {
        while (mode.innerMode) {
            var info = mode.innerMode(state);
            if (!info || info.mode == mode) break;
            state = info.state;
            mode = info.mode;
        }
        return info || {
            mode: mode,
            state: state
        };
    };

    // STANDARD COMMANDS

    // Commands are parameter-less actions that can be performed on an
    // editor, mostly used for keybindings.
    var commands = CodeMirror.commands = {
        selectAll: function(cm) {
            cm.setSelection(Pos(cm.firstLine(), 0), Pos(cm.lastLine()), sel_dontScroll);
        },
        singleSelection: function(cm) {
            cm.setSelection(cm.getCursor("anchor"), cm.getCursor("head"), sel_dontScroll);
        },
        killLine: function(cm) {
            deleteNearSelection(cm, function(range) {
                if (range.empty()) {
                    var len = getLine(cm.doc, range.head.line).text.length;
                    if (range.head.ch == len && range.head.line < cm.lastLine())
                        return {
                            from: range.head,
                            to: Pos(range.head.line + 1, 0)
                        };
                    else
                        return {
                            from: range.head,
                            to: Pos(range.head.line, len)
                        };
                } else {
                    return {
                        from: range.from(),
                        to: range.to()
                    };
                }
            });
        },
        deleteLine: function(cm) {
            deleteNearSelection(cm, function(range) {
                return {
                    from: Pos(range.from().line, 0),
                    to: clipPos(cm.doc, Pos(range.to().line + 1, 0))
                };
            });
        },
        delLineLeft: function(cm) {
            deleteNearSelection(cm, function(range) {
                return {
                    from: Pos(range.from().line, 0),
                    to: range.from()
                };
            });
        },
        delWrappedLineLeft: function(cm) {
            deleteNearSelection(cm, function(range) {
                var top = cm.charCoords(range.head, "div").top + 5;
                var leftPos = cm.coordsChar({
                    left: 0,
                    top: top
                }, "div");
                return {
                    from: leftPos,
                    to: range.from()
                };
            });
        },
        delWrappedLineRight: function(cm) {
            deleteNearSelection(cm, function(range) {
                var top = cm.charCoords(range.head, "div").top + 5;
                var rightPos = cm.coordsChar({
                    left: cm.display.lineDiv.offsetWidth + 100,
                    top: top
                }, "div");
                return {
                    from: range.from(),
                    to: rightPos
                };
            });
        },
        undo: function(cm) {
            cm.undo();
        },
        redo: function(cm) {
            cm.redo();
        },
        undoSelection: function(cm) {
            cm.undoSelection();
        },
        redoSelection: function(cm) {
            cm.redoSelection();
        },
        goDocStart: function(cm) {
            cm.extendSelection(Pos(cm.firstLine(), 0));
        },
        goDocEnd: function(cm) {
            cm.extendSelection(Pos(cm.lastLine()));
        },
        goLineStart: function(cm) {
            cm.extendSelectionsBy(function(range) {
                return lineStart(cm, range.head.line);
            }, {
                origin: "+move",
                bias: 1
            });
        },
        goLineStartSmart: function(cm) {
            cm.extendSelectionsBy(function(range) {
                return lineStartSmart(cm, range.head);
            }, {
                origin: "+move",
                bias: 1
            });
        },
        goLineEnd: function(cm) {
            cm.extendSelectionsBy(function(range) {
                return lineEnd(cm, range.head.line);
            }, {
                origin: "+move",
                bias: -1
            });
        },
        goLineRight: function(cm) {
            cm.extendSelectionsBy(function(range) {
                var top = cm.charCoords(range.head, "div").top + 5;
                return cm.coordsChar({
                    left: cm.display.lineDiv.offsetWidth + 100,
                    top: top
                }, "div");
            }, sel_move);
        },
        goLineLeft: function(cm) {
            cm.extendSelectionsBy(function(range) {
                var top = cm.charCoords(range.head, "div").top + 5;
                return cm.coordsChar({
                    left: 0,
                    top: top
                }, "div");
            }, sel_move);
        },
        goLineLeftSmart: function(cm) {
            cm.extendSelectionsBy(function(range) {
                var top = cm.charCoords(range.head, "div").top + 5;
                var pos = cm.coordsChar({
                    left: 0,
                    top: top
                }, "div");
                if (pos.ch < cm.getLine(pos.line).search(/\S/)) return lineStartSmart(cm, range.head);
                return pos;
            }, sel_move);
        },
        goLineUp: function(cm) {
            cm.moveV(-1, "line");
        },
        goLineDown: function(cm) {
            cm.moveV(1, "line");
        },
        goPageUp: function(cm) {
            cm.moveV(-1, "page");
        },
        goPageDown: function(cm) {
            cm.moveV(1, "page");
        },
        goCharLeft: function(cm) {
            cm.moveH(-1, "char");
        },
        goCharRight: function(cm) {
            cm.moveH(1, "char");
        },
        goColumnLeft: function(cm) {
            cm.moveH(-1, "column");
        },
        goColumnRight: function(cm) {
            cm.moveH(1, "column");
        },
        goWordLeft: function(cm) {
            cm.moveH(-1, "word");
        },
        goGroupRight: function(cm) {
            cm.moveH(1, "group");
        },
        goGroupLeft: function(cm) {
            cm.moveH(-1, "group");
        },
        goWordRight: function(cm) {
            cm.moveH(1, "word");
        },
        delCharBefore: function(cm) {
            cm.deleteH(-1, "char");
        },
        delCharAfter: function(cm) {
            cm.deleteH(1, "char");
        },
        delWordBefore: function(cm) {
            cm.deleteH(-1, "word");
        },
        delWordAfter: function(cm) {
            cm.deleteH(1, "word");
        },
        delGroupBefore: function(cm) {
            cm.deleteH(-1, "group");
        },
        delGroupAfter: function(cm) {
            cm.deleteH(1, "group");
        },
        indentAuto: function(cm) {
            cm.indentSelection("smart");
        },
        indentMore: function(cm) {
            cm.indentSelection("add");
        },
        indentLess: function(cm) {
            cm.indentSelection("subtract");
        },
        insertTab: function(cm) {
            cm.replaceSelection("\t");
        },
        insertSoftTab: function(cm) {
            var spaces = [],
                ranges = cm.listSelections(),
                tabSize = cm.options.tabSize;
            for (var i = 0; i < ranges.length; i++) {
                var pos = ranges[i].from();
                var col = countColumn(cm.getLine(pos.line), pos.ch, tabSize);
                spaces.push(new Array(tabSize - col % tabSize + 1).join(" "));
            }
            cm.replaceSelections(spaces);
        },
        defaultTab: function(cm) {
            if (cm.somethingSelected()) cm.indentSelection("add");
            else cm.execCommand("insertTab");
        },
        transposeChars: function(cm) {
            runInOp(cm, function() {
                var ranges = cm.listSelections(),
                    newSel = [];
                for (var i = 0; i < ranges.length; i++) {
                    var cur = ranges[i].head,
                        line = getLine(cm.doc, cur.line).text;
                    if (line) {
                        if (cur.ch == line.length) cur = new Pos(cur.line, cur.ch - 1);
                        if (cur.ch > 0) {
                            cur = new Pos(cur.line, cur.ch + 1);
                            cm.replaceRange(line.charAt(cur.ch - 1) + line.charAt(cur.ch - 2),
                                Pos(cur.line, cur.ch - 2), cur, "+transpose");
                        } else if (cur.line > cm.doc.first) {
                            var prev = getLine(cm.doc, cur.line - 1).text;
                            if (prev)
                                cm.replaceRange(line.charAt(0) + "\n" + prev.charAt(prev.length - 1),
                                    Pos(cur.line - 1, prev.length - 1), Pos(cur.line, 1), "+transpose");
                        }
                    }
                    newSel.push(new Range(cur, cur));
                }
                cm.setSelections(newSel);
            });
        },
        newlineAndIndent: function(cm) {
            runInOp(cm, function() {
                var len = cm.listSelections().length;
                for (var i = 0; i < len; i++) {
                    var range = cm.listSelections()[i];
                    cm.replaceRange("\n", range.anchor, range.head, "+input");
                    cm.indentLine(range.from().line + 1, null, true);
                    ensureCursorVisible(cm);
                }
            });
        },
        toggleOverwrite: function(cm) {
            cm.toggleOverwrite();
        }
    };


    // STANDARD KEYMAPS

    var keyMap = CodeMirror.keyMap = {};

    keyMap.basic = {
        "Left": "goCharLeft",
        "Right": "goCharRight",
        "Up": "goLineUp",
        "Down": "goLineDown",
        "End": "goLineEnd",
        "Home": "goLineStartSmart",
        "PageUp": "goPageUp",
        "PageDown": "goPageDown",
        "Delete": "delCharAfter",
        "Backspace": "delCharBefore",
        "Shift-Backspace": "delCharBefore",
        "Tab": "defaultTab",
        "Shift-Tab": "indentAuto",
        "Enter": "newlineAndIndent",
        "Insert": "toggleOverwrite",
        "Esc": "singleSelection"
    };
    // Note that the save and find-related commands aren't defined by
    // default. User code or addons can define them. Unknown commands
    // are simply ignored.
    keyMap.pcDefault = {
        "Ctrl-A": "selectAll",
        "Ctrl-D": "deleteLine",
        "Ctrl-Z": "undo",
        "Shift-Ctrl-Z": "redo",
        "Ctrl-Y": "redo",
        "Ctrl-Home": "goDocStart",
        "Ctrl-End": "goDocEnd",
        "Ctrl-Up": "goLineUp",
        "Ctrl-Down": "goLineDown",
        "Ctrl-Left": "goGroupLeft",
        "Ctrl-Right": "goGroupRight",
        "Alt-Left": "goLineStart",
        "Alt-Right": "goLineEnd",
        "Ctrl-Backspace": "delGroupBefore",
        "Ctrl-Delete": "delGroupAfter",
        "Ctrl-S": "save",
        "Ctrl-F": "find",
        "Ctrl-G": "findNext",
        "Shift-Ctrl-G": "findPrev",
        "Shift-Ctrl-F": "replace",
        "Shift-Ctrl-R": "replaceAll",
        "Ctrl-[": "indentLess",
        "Ctrl-]": "indentMore",
        "Ctrl-U": "undoSelection",
        "Shift-Ctrl-U": "redoSelection",
        "Alt-U": "redoSelection",
        fallthrough: "basic"
    };
    // Very basic readline/emacs-style bindings, which are standard on Mac.
    keyMap.emacsy = {
        "Ctrl-F": "goCharRight",
        "Ctrl-B": "goCharLeft",
        "Ctrl-P": "goLineUp",
        "Ctrl-N": "goLineDown",
        "Alt-F": "goWordRight",
        "Alt-B": "goWordLeft",
        "Ctrl-A": "goLineStart",
        "Ctrl-E": "goLineEnd",
        "Ctrl-V": "goPageDown",
        "Shift-Ctrl-V": "goPageUp",
        "Ctrl-D": "delCharAfter",
        "Ctrl-H": "delCharBefore",
        "Alt-D": "delWordAfter",
        "Alt-Backspace": "delWordBefore",
        "Ctrl-K": "killLine",
        "Ctrl-T": "transposeChars"
    };
    keyMap.macDefault = {
        "Cmd-A": "selectAll",
        "Cmd-D": "deleteLine",
        "Cmd-Z": "undo",
        "Shift-Cmd-Z": "redo",
        "Cmd-Y": "redo",
        "Cmd-Home": "goDocStart",
        "Cmd-Up": "goDocStart",
        "Cmd-End": "goDocEnd",
        "Cmd-Down": "goDocEnd",
        "Alt-Left": "goGroupLeft",
        "Alt-Right": "goGroupRight",
        "Cmd-Left": "goLineLeft",
        "Cmd-Right": "goLineRight",
        "Alt-Backspace": "delGroupBefore",
        "Ctrl-Alt-Backspace": "delGroupAfter",
        "Alt-Delete": "delGroupAfter",
        "Cmd-S": "save",
        "Cmd-F": "find",
        "Cmd-G": "findNext",
        "Shift-Cmd-G": "findPrev",
        "Cmd-Alt-F": "replace",
        "Shift-Cmd-Alt-F": "replaceAll",
        "Cmd-[": "indentLess",
        "Cmd-]": "indentMore",
        "Cmd-Backspace": "delWrappedLineLeft",
        "Cmd-Delete": "delWrappedLineRight",
        "Cmd-U": "undoSelection",
        "Shift-Cmd-U": "redoSelection",
        "Ctrl-Up": "goDocStart",
        "Ctrl-Down": "goDocEnd",
        fallthrough: ["basic", "emacsy"]
    };
    keyMap["default"] = mac ? keyMap.macDefault : keyMap.pcDefault;

    // KEYMAP DISPATCH

    function normalizeKeyName(name) {
        var parts = name.split(/-(?!$)/),
            name = parts[parts.length - 1];
        var alt, ctrl, shift, cmd;
        for (var i = 0; i < parts.length - 1; i++) {
            var mod = parts[i];
            if (/^(cmd|meta|m)$/i.test(mod)) cmd = true;
            else if (/^a(lt)?$/i.test(mod)) alt = true;
            else if (/^(c|ctrl|control)$/i.test(mod)) ctrl = true;
            else if (/^s(hift)$/i.test(mod)) shift = true;
            else throw new Error("Unrecognized modifier name: " + mod);
        }
        if (alt) name = "Alt-" + name;
        if (ctrl) name = "Ctrl-" + name;
        if (cmd) name = "Cmd-" + name;
        if (shift) name = "Shift-" + name;
        return name;
    }

    // This is a kludge to keep keymaps mostly working as raw objects
    // (backwards compatibility) while at the same time support features
    // like normalization and multi-stroke key bindings. It compiles a
    // new normalized keymap, and then updates the old object to reflect
    // this.
    CodeMirror.normalizeKeyMap = function(keymap) {
        var copy = {};
        for (var keyname in keymap)
            if (keymap.hasOwnProperty(keyname)) {
                var value = keymap[keyname];
                if (/^(name|fallthrough|(de|at)tach)$/.test(keyname)) continue;
                if (value == "...") {
                    delete keymap[keyname];
                    continue;
                }

                var keys = map(keyname.split(" "), normalizeKeyName);
                for (var i = 0; i < keys.length; i++) {
                    var val, name;
                    if (i == keys.length - 1) {
                        name = keys.join(" ");
                        val = value;
                    } else {
                        name = keys.slice(0, i + 1).join(" ");
                        val = "...";
                    }
                    var prev = copy[name];
                    if (!prev) copy[name] = val;
                    else if (prev != val) throw new Error("Inconsistent bindings for " + name);
                }
                delete keymap[keyname];
            }
        for (var prop in copy) keymap[prop] = copy[prop];
        return keymap;
    };

    var lookupKey = CodeMirror.lookupKey = function(key, map, handle, context) {
        map = getKeyMap(map);
        var found = map.call ? map.call(key, context) : map[key];
        if (found === false) return "nothing";
        if (found === "...") return "multi";
        if (found != null && handle(found)) return "handled";

        if (map.fallthrough) {
            if (Object.prototype.toString.call(map.fallthrough) != "[object Array]")
                return lookupKey(key, map.fallthrough, handle, context);
            for (var i = 0; i < map.fallthrough.length; i++) {
                var result = lookupKey(key, map.fallthrough[i], handle, context);
                if (result) return result;
            }
        }
    };

    // Modifier key presses don't count as 'real' key presses for the
    // purpose of keymap fallthrough.
    var isModifierKey = CodeMirror.isModifierKey = function(value) {
        var name = typeof value == "string" ? value : keyNames[value.keyCode];
        return name == "Ctrl" || name == "Alt" || name == "Shift" || name == "Mod";
    };

    // Look up the name of a key as indicated by an event object.
    var keyName = CodeMirror.keyName = function(event, noShift) {
        if (presto && event.keyCode == 34 && event["char"]) return false;
        var base = keyNames[event.keyCode],
            name = base;
        if (name == null || event.altGraphKey) return false;
        if (event.altKey && base != "Alt") name = "Alt-" + name;
        if ((flipCtrlCmd ? event.metaKey : event.ctrlKey) && base != "Ctrl") name = "Ctrl-" + name;
        if ((flipCtrlCmd ? event.ctrlKey : event.metaKey) && base != "Cmd") name = "Cmd-" + name;
        if (!noShift && event.shiftKey && base != "Shift") name = "Shift-" + name;
        return name;
    };

    function getKeyMap(val) {
        return typeof val == "string" ? keyMap[val] : val;
    }

    // FROMTEXTAREA

    CodeMirror.fromTextArea = function(textarea, options) {
        options = options ? copyObj(options) : {};
        options.value = textarea.value;
        if (!options.tabindex && textarea.tabIndex)
            options.tabindex = textarea.tabIndex;
        if (!options.placeholder && textarea.placeholder)
            options.placeholder = textarea.placeholder;
        // Set autofocus to true if this textarea is focused, or if it has
        // autofocus and no other element is focused.
        if (options.autofocus == null) {
            var hasFocus = activeElt();
            options.autofocus = hasFocus == textarea ||
                textarea.getAttribute("autofocus") != null && hasFocus == document.body;
        }

        function save() {
            textarea.value = cm.getValue();
        }
        if (textarea.form) {
            on(textarea.form, "submit", save);
            // Deplorable hack to make the submit method do the right thing.
            if (!options.leaveSubmitMethodAlone) {
                var form = textarea.form,
                    realSubmit = form.submit;
                try {
                    var wrappedSubmit = form.submit = function() {
                        save();
                        form.submit = realSubmit;
                        form.submit();
                        form.submit = wrappedSubmit;
                    };
                } catch (e) {}
            }
        }

        options.finishInit = function(cm) {
            cm.save = save;
            cm.getTextArea = function() {
                return textarea;
            };
            cm.toTextArea = function() {
                cm.toTextArea = isNaN; // Prevent this from being ran twice
                save();
                textarea.parentNode.removeChild(cm.getWrapperElement());
                textarea.style.display = "";
                if (textarea.form) {
                    off(textarea.form, "submit", save);
                    if (typeof textarea.form.submit == "function")
                        textarea.form.submit = realSubmit;
                }
            };
        };

        textarea.style.display = "none";
        var cm = CodeMirror(function(node) {
            textarea.parentNode.insertBefore(node, textarea.nextSibling);
        }, options);
        return cm;
    };

    // STRING STREAM

    // Fed to the mode parsers, provides helper functions to make
    // parsers more succinct.

    var StringStream = CodeMirror.StringStream = function(string, tabSize) {
        this.pos = this.start = 0;
        this.string = string;
        this.tabSize = tabSize || 8;
        this.lastColumnPos = this.lastColumnValue = 0;
        this.lineStart = 0;
    };

    StringStream.prototype = {
        eol: function() {
            return this.pos >= this.string.length;
        },
        sol: function() {
            return this.pos == this.lineStart;
        },
        peek: function() {
            return this.string.charAt(this.pos) || undefined;
        },
        next: function() {
            if (this.pos < this.string.length)
                return this.string.charAt(this.pos++);
        },
        eat: function(match) {
            var ch = this.string.charAt(this.pos);
            if (typeof match == "string") var ok = ch == match;
            else var ok = ch && (match.test ? match.test(ch) : match(ch));
            if (ok) {
                ++this.pos;
                return ch;
            }
        },
        eatWhile: function(match) {
            var start = this.pos;
            while (this.eat(match)) {}
            return this.pos > start;
        },
        eatSpace: function() {
            var start = this.pos;
            while (/[\s\u00a0]/.test(this.string.charAt(this.pos))) ++this.pos;
            return this.pos > start;
        },
        skipToEnd: function() {
            this.pos = this.string.length;
        },
        skipTo: function(ch) {
            var found = this.string.indexOf(ch, this.pos);
            if (found > -1) {
                this.pos = found;
                return true;
            }
        },
        backUp: function(n) {
            this.pos -= n;
        },
        column: function() {
            if (this.lastColumnPos < this.start) {
                this.lastColumnValue = countColumn(this.string, this.start, this.tabSize, this.lastColumnPos, this.lastColumnValue);
                this.lastColumnPos = this.start;
            }
            return this.lastColumnValue - (this.lineStart ? countColumn(this.string, this.lineStart, this.tabSize) : 0);
        },
        indentation: function() {
            return countColumn(this.string, null, this.tabSize) -
                (this.lineStart ? countColumn(this.string, this.lineStart, this.tabSize) : 0);
        },
        match: function(pattern, consume, caseInsensitive) {
            if (typeof pattern == "string") {
                var cased = function(str) {
                    return caseInsensitive ? str.toLowerCase() : str;
                };
                var substr = this.string.substr(this.pos, pattern.length);
                if (cased(substr) == cased(pattern)) {
                    if (consume !== false) this.pos += pattern.length;
                    return true;
                }
            } else {
                var match = this.string.slice(this.pos).match(pattern);
                if (match && match.index > 0) return null;
                if (match && consume !== false) this.pos += match[0].length;
                return match;
            }
        },
        current: function() {
            return this.string.slice(this.start, this.pos);
        },
        hideFirstChars: function(n, inner) {
            this.lineStart += n;
            try {
                return inner();
            } finally {
                this.lineStart -= n;
            }
        }
    };

    // TEXTMARKERS

    // Created with markText and setBookmark methods. A TextMarker is a
    // handle that can be used to clear or find a marked position in the
    // document. Line objects hold arrays (markedSpans) containing
    // {from, to, marker} object pointing to such marker objects, and
    // indicating that such a marker is present on that line. Multiple
    // lines may point to the same marker when it spans across lines.
    // The spans will have null for their from/to properties when the
    // marker continues beyond the start/end of the line. Markers have
    // links back to the lines they currently touch.

    var nextMarkerId = 0;

    var TextMarker = CodeMirror.TextMarker = function(doc, type) {
        this.lines = [];
        this.type = type;
        this.doc = doc;
        this.id = ++nextMarkerId;
    };
    eventMixin(TextMarker);

    // Clear the marker.
    TextMarker.prototype.clear = function() {
        if (this.explicitlyCleared) return;
        var cm = this.doc.cm,
            withOp = cm && !cm.curOp;
        if (withOp) startOperation(cm);
        if (hasHandler(this, "clear")) {
            var found = this.find();
            if (found) signalLater(this, "clear", found.from, found.to);
        }
        var min = null,
            max = null;
        for (var i = 0; i < this.lines.length; ++i) {
            var line = this.lines[i];
            var span = getMarkedSpanFor(line.markedSpans, this);
            if (cm && !this.collapsed) regLineChange(cm, lineNo(line), "text");
            else if (cm) {
                if (span.to != null) max = lineNo(line);
                if (span.from != null) min = lineNo(line);
            }
            line.markedSpans = removeMarkedSpan(line.markedSpans, span);
            if (span.from == null && this.collapsed && !lineIsHidden(this.doc, line) && cm)
                updateLineHeight(line, textHeight(cm.display));
        }
        if (cm && this.collapsed && !cm.options.lineWrapping)
            for (var i = 0; i < this.lines.length; ++i) {
                var visual = visualLine(this.lines[i]),
                    len = lineLength(visual);
                if (len > cm.display.maxLineLength) {
                    cm.display.maxLine = visual;
                    cm.display.maxLineLength = len;
                    cm.display.maxLineChanged = true;
                }
            }

        if (min != null && cm && this.collapsed) regChange(cm, min, max + 1);
        this.lines.length = 0;
        this.explicitlyCleared = true;
        if (this.atomic && this.doc.cantEdit) {
            this.doc.cantEdit = false;
            if (cm) reCheckSelection(cm.doc);
        }
        if (cm) signalLater(cm, "markerCleared", cm, this);
        if (withOp) endOperation(cm);
        if (this.parent) this.parent.clear();
    };

    // Find the position of the marker in the document. Returns a {from,
    // to} object by default. Side can be passed to get a specific side
    // -- 0 (both), -1 (left), or 1 (right). When lineObj is true, the
    // Pos objects returned contain a line object, rather than a line
    // number (used to prevent looking up the same line twice).
    TextMarker.prototype.find = function(side, lineObj) {
        if (side == null && this.type == "bookmark") side = 1;
        var from, to;
        for (var i = 0; i < this.lines.length; ++i) {
            var line = this.lines[i];
            var span = getMarkedSpanFor(line.markedSpans, this);
            if (span.from != null) {
                from = Pos(lineObj ? line : lineNo(line), span.from);
                if (side == -1) return from;
            }
            if (span.to != null) {
                to = Pos(lineObj ? line : lineNo(line), span.to);
                if (side == 1) return to;
            }
        }
        return from && {
            from: from,
            to: to
        };
    };

    // Signals that the marker's widget changed, and surrounding layout
    // should be recomputed.
    TextMarker.prototype.changed = function() {
        var pos = this.find(-1, true),
            widget = this,
            cm = this.doc.cm;
        if (!pos || !cm) return;
        runInOp(cm, function() {
            var line = pos.line,
                lineN = lineNo(pos.line);
            var view = findViewForLine(cm, lineN);
            if (view) {
                clearLineMeasurementCacheFor(view);
                cm.curOp.selectionChanged = cm.curOp.forceUpdate = true;
            }
            cm.curOp.updateMaxLine = true;
            if (!lineIsHidden(widget.doc, line) && widget.height != null) {
                var oldHeight = widget.height;
                widget.height = null;
                var dHeight = widgetHeight(widget) - oldHeight;
                if (dHeight)
                    updateLineHeight(line, line.height + dHeight);
            }
        });
    };

    TextMarker.prototype.attachLine = function(line) {
        if (!this.lines.length && this.doc.cm) {
            var op = this.doc.cm.curOp;
            if (!op.maybeHiddenMarkers || indexOf(op.maybeHiddenMarkers, this) == -1)
                (op.maybeUnhiddenMarkers || (op.maybeUnhiddenMarkers = [])).push(this);
        }
        this.lines.push(line);
    };
    TextMarker.prototype.detachLine = function(line) {
        this.lines.splice(indexOf(this.lines, line), 1);
        if (!this.lines.length && this.doc.cm) {
            var op = this.doc.cm.curOp;
            (op.maybeHiddenMarkers || (op.maybeHiddenMarkers = [])).push(this);
        }
    };

    // Collapsed markers have unique ids, in order to be able to order
    // them, which is needed for uniquely determining an outer marker
    // when they overlap (they may nest, but not partially overlap).
    var nextMarkerId = 0;

    // Create a marker, wire it up to the right lines, and
    function markText(doc, from, to, options, type) {
        // Shared markers (across linked documents) are handled separately
        // (markTextShared will call out to this again, once per
        // document).
        if (options && options.shared) return markTextShared(doc, from, to, options, type);
        // Ensure we are in an operation.
        if (doc.cm && !doc.cm.curOp) return operation(doc.cm, markText)(doc, from, to, options, type);

        var marker = new TextMarker(doc, type),
            diff = cmp(from, to);
        if (options) copyObj(options, marker, false);
        // Don't connect empty markers unless clearWhenEmpty is false
        if (diff > 0 || diff == 0 && marker.clearWhenEmpty !== false)
            return marker;
        if (marker.replacedWith) {
            // Showing up as a widget implies collapsed (widget replaces text)
            marker.collapsed = true;
            marker.widgetNode = elt("span", [marker.replacedWith], "CodeMirror-widget");
            if (!options.handleMouseEvents) marker.widgetNode.setAttribute("cm-ignore-events", "true");
            if (options.insertLeft) marker.widgetNode.insertLeft = true;
        }
        if (marker.collapsed) {
            if (conflictingCollapsedRange(doc, from.line, from, to, marker) ||
                from.line != to.line && conflictingCollapsedRange(doc, to.line, from, to, marker))
                throw new Error("Inserting collapsed marker partially overlapping an existing one");
            sawCollapsedSpans = true;
        }

        if (marker.addToHistory)
            addChangeToHistory(doc, {
                from: from,
                to: to,
                origin: "markText"
            }, doc.sel, NaN);

        var curLine = from.line,
            cm = doc.cm,
            updateMaxLine;
        doc.iter(curLine, to.line + 1, function(line) {
            if (cm && marker.collapsed && !cm.options.lineWrapping && visualLine(line) == cm.display.maxLine)
                updateMaxLine = true;
            if (marker.collapsed && curLine != from.line) updateLineHeight(line, 0);
            addMarkedSpan(line, new MarkedSpan(marker,
                curLine == from.line ? from.ch : null,
                curLine == to.line ? to.ch : null));
            ++curLine;
        });
        // lineIsHidden depends on the presence of the spans, so needs a second pass
        if (marker.collapsed) doc.iter(from.line, to.line + 1, function(line) {
            if (lineIsHidden(doc, line)) updateLineHeight(line, 0);
        });

        if (marker.clearOnEnter) on(marker, "beforeCursorEnter", function() {
            marker.clear();
        });

        if (marker.readOnly) {
            sawReadOnlySpans = true;
            if (doc.history.done.length || doc.history.undone.length)
                doc.clearHistory();
        }
        if (marker.collapsed) {
            marker.id = ++nextMarkerId;
            marker.atomic = true;
        }
        if (cm) {
            // Sync editor state
            if (updateMaxLine) cm.curOp.updateMaxLine = true;
            if (marker.collapsed)
                regChange(cm, from.line, to.line + 1);
            else if (marker.className || marker.title || marker.startStyle || marker.endStyle || marker.css)
                for (var i = from.line; i <= to.line; i++) regLineChange(cm, i, "text");
            if (marker.atomic) reCheckSelection(cm.doc);
            signalLater(cm, "markerAdded", cm, marker);
        }
        return marker;
    }

    // SHARED TEXTMARKERS

    // A shared marker spans multiple linked documents. It is
    // implemented as a meta-marker-object controlling multiple normal
    // markers.
    var SharedTextMarker = CodeMirror.SharedTextMarker = function(markers, primary) {
        this.markers = markers;
        this.primary = primary;
        for (var i = 0; i < markers.length; ++i)
            markers[i].parent = this;
    };
    eventMixin(SharedTextMarker);

    SharedTextMarker.prototype.clear = function() {
        if (this.explicitlyCleared) return;
        this.explicitlyCleared = true;
        for (var i = 0; i < this.markers.length; ++i)
            this.markers[i].clear();
        signalLater(this, "clear");
    };
    SharedTextMarker.prototype.find = function(side, lineObj) {
        return this.primary.find(side, lineObj);
    };

    function markTextShared(doc, from, to, options, type) {
        options = copyObj(options);
        options.shared = false;
        var markers = [markText(doc, from, to, options, type)],
            primary = markers[0];
        var widget = options.widgetNode;
        linkedDocs(doc, function(doc) {
            if (widget) options.widgetNode = widget.cloneNode(true);
            markers.push(markText(doc, clipPos(doc, from), clipPos(doc, to), options, type));
            for (var i = 0; i < doc.linked.length; ++i)
                if (doc.linked[i].isParent) return;
            primary = lst(markers);
        });
        return new SharedTextMarker(markers, primary);
    }

    function findSharedMarkers(doc) {
        return doc.findMarks(Pos(doc.first, 0), doc.clipPos(Pos(doc.lastLine())),
            function(m) {
                return m.parent;
            });
    }

    function copySharedMarkers(doc, markers) {
        for (var i = 0; i < markers.length; i++) {
            var marker = markers[i],
                pos = marker.find();
            var mFrom = doc.clipPos(pos.from),
                mTo = doc.clipPos(pos.to);
            if (cmp(mFrom, mTo)) {
                var subMark = markText(doc, mFrom, mTo, marker.primary, marker.primary.type);
                marker.markers.push(subMark);
                subMark.parent = marker;
            }
        }
    }

    function detachSharedMarkers(markers) {
        for (var i = 0; i < markers.length; i++) {
            var marker = markers[i],
                linked = [marker.primary.doc];;
            linkedDocs(marker.primary.doc, function(d) {
                linked.push(d);
            });
            for (var j = 0; j < marker.markers.length; j++) {
                var subMarker = marker.markers[j];
                if (indexOf(linked, subMarker.doc) == -1) {
                    subMarker.parent = null;
                    marker.markers.splice(j--, 1);
                }
            }
        }
    }

    // TEXTMARKER SPANS

    function MarkedSpan(marker, from, to) {
        this.marker = marker;
        this.from = from;
        this.to = to;
    }

    // Search an array of spans for a span matching the given marker.
    function getMarkedSpanFor(spans, marker) {
        if (spans)
            for (var i = 0; i < spans.length; ++i) {
                var span = spans[i];
                if (span.marker == marker) return span;
            }
    }
    // Remove a span from an array, returning undefined if no spans are
    // left (we don't store arrays for lines without spans).
    function removeMarkedSpan(spans, span) {
        for (var r, i = 0; i < spans.length; ++i)
            if (spans[i] != span)(r || (r = [])).push(spans[i]);
        return r;
    }
    // Add a span to a line.
    function addMarkedSpan(line, span) {
        line.markedSpans = line.markedSpans ? line.markedSpans.concat([span]) : [span];
        span.marker.attachLine(line);
    }

    // Used for the algorithm that adjusts markers for a change in the
    // document. These functions cut an array of spans at a given
    // character position, returning an array of remaining chunks (or
    // undefined if nothing remains).
    function markedSpansBefore(old, startCh, isInsert) {
        if (old)
            for (var i = 0, nw; i < old.length; ++i) {
                var span = old[i],
                    marker = span.marker;
                var startsBefore = span.from == null || (marker.inclusiveLeft ? span.from <= startCh : span.from < startCh);
                if (startsBefore || span.from == startCh && marker.type == "bookmark" && (!isInsert || !span.marker.insertLeft)) {
                    var endsAfter = span.to == null || (marker.inclusiveRight ? span.to >= startCh : span.to > startCh);
                    (nw || (nw = [])).push(new MarkedSpan(marker, span.from, endsAfter ? null : span.to));
                }
            }
        return nw;
    }

    function markedSpansAfter(old, endCh, isInsert) {
        if (old)
            for (var i = 0, nw; i < old.length; ++i) {
                var span = old[i],
                    marker = span.marker;
                var endsAfter = span.to == null || (marker.inclusiveRight ? span.to >= endCh : span.to > endCh);
                if (endsAfter || span.from == endCh && marker.type == "bookmark" && (!isInsert || span.marker.insertLeft)) {
                    var startsBefore = span.from == null || (marker.inclusiveLeft ? span.from <= endCh : span.from < endCh);
                    (nw || (nw = [])).push(new MarkedSpan(marker, startsBefore ? null : span.from - endCh,
                        span.to == null ? null : span.to - endCh));
                }
            }
        return nw;
    }

    // Given a change object, compute the new set of marker spans that
    // cover the line in which the change took place. Removes spans
    // entirely within the change, reconnects spans belonging to the
    // same marker that appear on both sides of the change, and cuts off
    // spans partially within the change. Returns an array of span
    // arrays with one element for each line in (after) the change.
    function stretchSpansOverChange(doc, change) {
        if (change.full) return null;
        var oldFirst = isLine(doc, change.from.line) && getLine(doc, change.from.line).markedSpans;
        var oldLast = isLine(doc, change.to.line) && getLine(doc, change.to.line).markedSpans;
        if (!oldFirst && !oldLast) return null;

        var startCh = change.from.ch,
            endCh = change.to.ch,
            isInsert = cmp(change.from, change.to) == 0;
        // Get the spans that 'stick out' on both sides
        var first = markedSpansBefore(oldFirst, startCh, isInsert);
        var last = markedSpansAfter(oldLast, endCh, isInsert);

        // Next, merge those two ends
        var sameLine = change.text.length == 1,
            offset = lst(change.text).length + (sameLine ? startCh : 0);
        if (first) {
            // Fix up .to properties of first
            for (var i = 0; i < first.length; ++i) {
                var span = first[i];
                if (span.to == null) {
                    var found = getMarkedSpanFor(last, span.marker);
                    if (!found) span.to = startCh;
                    else if (sameLine) span.to = found.to == null ? null : found.to + offset;
                }
            }
        }
        if (last) {
            // Fix up .from in last (or move them into first in case of sameLine)
            for (var i = 0; i < last.length; ++i) {
                var span = last[i];
                if (span.to != null) span.to += offset;
                if (span.from == null) {
                    var found = getMarkedSpanFor(first, span.marker);
                    if (!found) {
                        span.from = offset;
                        if (sameLine)(first || (first = [])).push(span);
                    }
                } else {
                    span.from += offset;
                    if (sameLine)(first || (first = [])).push(span);
                }
            }
        }
        // Make sure we didn't create any zero-length spans
        if (first) first = clearEmptySpans(first);
        if (last && last != first) last = clearEmptySpans(last);

        var newMarkers = [first];
        if (!sameLine) {
            // Fill gap with whole-line-spans
            var gap = change.text.length - 2,
                gapMarkers;
            if (gap > 0 && first)
                for (var i = 0; i < first.length; ++i)
                    if (first[i].to == null)
                        (gapMarkers || (gapMarkers = [])).push(new MarkedSpan(first[i].marker, null, null));
            for (var i = 0; i < gap; ++i)
                newMarkers.push(gapMarkers);
            newMarkers.push(last);
        }
        return newMarkers;
    }

    // Remove spans that are empty and don't have a clearWhenEmpty
    // option of false.
    function clearEmptySpans(spans) {
        for (var i = 0; i < spans.length; ++i) {
            var span = spans[i];
            if (span.from != null && span.from == span.to && span.marker.clearWhenEmpty !== false)
                spans.splice(i--, 1);
        }
        if (!spans.length) return null;
        return spans;
    }

    // Used for un/re-doing changes from the history. Combines the
    // result of computing the existing spans with the set of spans that
    // existed in the history (so that deleting around a span and then
    // undoing brings back the span).
    function mergeOldSpans(doc, change) {
        var old = getOldSpans(doc, change);
        var stretched = stretchSpansOverChange(doc, change);
        if (!old) return stretched;
        if (!stretched) return old;

        for (var i = 0; i < old.length; ++i) {
            var oldCur = old[i],
                stretchCur = stretched[i];
            if (oldCur && stretchCur) {
                spans: for (var j = 0; j < stretchCur.length; ++j) {
                    var span = stretchCur[j];
                    for (var k = 0; k < oldCur.length; ++k)
                        if (oldCur[k].marker == span.marker) continue spans;
                    oldCur.push(span);
                }
            } else if (stretchCur) {
                old[i] = stretchCur;
            }
        }
        return old;
    }

    // Used to 'clip' out readOnly ranges when making a change.
    function removeReadOnlyRanges(doc, from, to) {
        var markers = null;
        doc.iter(from.line, to.line + 1, function(line) {
            if (line.markedSpans)
                for (var i = 0; i < line.markedSpans.length; ++i) {
                    var mark = line.markedSpans[i].marker;
                    if (mark.readOnly && (!markers || indexOf(markers, mark) == -1))
                        (markers || (markers = [])).push(mark);
                }
        });
        if (!markers) return null;
        var parts = [{
            from: from,
            to: to
        }];
        for (var i = 0; i < markers.length; ++i) {
            var mk = markers[i],
                m = mk.find(0);
            for (var j = 0; j < parts.length; ++j) {
                var p = parts[j];
                if (cmp(p.to, m.from) < 0 || cmp(p.from, m.to) > 0) continue;
                var newParts = [j, 1],
                    dfrom = cmp(p.from, m.from),
                    dto = cmp(p.to, m.to);
                if (dfrom < 0 || !mk.inclusiveLeft && !dfrom)
                    newParts.push({
                        from: p.from,
                        to: m.from
                    });
                if (dto > 0 || !mk.inclusiveRight && !dto)
                    newParts.push({
                        from: m.to,
                        to: p.to
                    });
                parts.splice.apply(parts, newParts);
                j += newParts.length - 1;
            }
        }
        return parts;
    }

    // Connect or disconnect spans from a line.
    function detachMarkedSpans(line) {
        var spans = line.markedSpans;
        if (!spans) return;
        for (var i = 0; i < spans.length; ++i)
            spans[i].marker.detachLine(line);
        line.markedSpans = null;
    }

    function attachMarkedSpans(line, spans) {
        if (!spans) return;
        for (var i = 0; i < spans.length; ++i)
            spans[i].marker.attachLine(line);
        line.markedSpans = spans;
    }

    // Helpers used when computing which overlapping collapsed span
    // counts as the larger one.
    function extraLeft(marker) {
        return marker.inclusiveLeft ? -1 : 0;
    }

    function extraRight(marker) {
        return marker.inclusiveRight ? 1 : 0;
    }

    // Returns a number indicating which of two overlapping collapsed
    // spans is larger (and thus includes the other). Falls back to
    // comparing ids when the spans cover exactly the same range.
    function compareCollapsedMarkers(a, b) {
        var lenDiff = a.lines.length - b.lines.length;
        if (lenDiff != 0) return lenDiff;
        var aPos = a.find(),
            bPos = b.find();
        var fromCmp = cmp(aPos.from, bPos.from) || extraLeft(a) - extraLeft(b);
        if (fromCmp) return -fromCmp;
        var toCmp = cmp(aPos.to, bPos.to) || extraRight(a) - extraRight(b);
        if (toCmp) return toCmp;
        return b.id - a.id;
    }

    // Find out whether a line ends or starts in a collapsed span. If
    // so, return the marker for that span.
    function collapsedSpanAtSide(line, start) {
        var sps = sawCollapsedSpans && line.markedSpans,
            found;
        if (sps)
            for (var sp, i = 0; i < sps.length; ++i) {
                sp = sps[i];
                if (sp.marker.collapsed && (start ? sp.from : sp.to) == null &&
                    (!found || compareCollapsedMarkers(found, sp.marker) < 0))
                    found = sp.marker;
            }
        return found;
    }

    function collapsedSpanAtStart(line) {
        return collapsedSpanAtSide(line, true);
    }

    function collapsedSpanAtEnd(line) {
        return collapsedSpanAtSide(line, false);
    }

    // Test whether there exists a collapsed span that partially
    // overlaps (covers the start or end, but not both) of a new span.
    // Such overlap is not allowed.
    function conflictingCollapsedRange(doc, lineNo, from, to, marker) {
        var line = getLine(doc, lineNo);
        var sps = sawCollapsedSpans && line.markedSpans;
        if (sps)
            for (var i = 0; i < sps.length; ++i) {
                var sp = sps[i];
                if (!sp.marker.collapsed) continue;
                var found = sp.marker.find(0);
                var fromCmp = cmp(found.from, from) || extraLeft(sp.marker) - extraLeft(marker);
                var toCmp = cmp(found.to, to) || extraRight(sp.marker) - extraRight(marker);
                if (fromCmp >= 0 && toCmp <= 0 || fromCmp <= 0 && toCmp >= 0) continue;
                if (fromCmp <= 0 && (cmp(found.to, from) > 0 || (sp.marker.inclusiveRight && marker.inclusiveLeft)) ||
                    fromCmp >= 0 && (cmp(found.from, to) < 0 || (sp.marker.inclusiveLeft && marker.inclusiveRight)))
                    return true;
            }
    }

    // A visual line is a line as drawn on the screen. Folding, for
    // example, can cause multiple logical lines to appear on the same
    // visual line. This finds the start of the visual line that the
    // given line is part of (usually that is the line itself).
    function visualLine(line) {
        var merged;
        while (merged = collapsedSpanAtStart(line))
            line = merged.find(-1, true).line;
        return line;
    }

    // Returns an array of logical lines that continue the visual line
    // started by the argument, or undefined if there are no such lines.
    function visualLineContinued(line) {
        var merged, lines;
        while (merged = collapsedSpanAtEnd(line)) {
            line = merged.find(1, true).line;
            (lines || (lines = [])).push(line);
        }
        return lines;
    }

    // Get the line number of the start of the visual line that the
    // given line number is part of.
    function visualLineNo(doc, lineN) {
        var line = getLine(doc, lineN),
            vis = visualLine(line);
        if (line == vis) return lineN;
        return lineNo(vis);
    }
    // Get the line number of the start of the next visual line after
    // the given line.
    function visualLineEndNo(doc, lineN) {
        if (lineN > doc.lastLine()) return lineN;
        var line = getLine(doc, lineN),
            merged;
        if (!lineIsHidden(doc, line)) return lineN;
        while (merged = collapsedSpanAtEnd(line))
            line = merged.find(1, true).line;
        return lineNo(line) + 1;
    }

    // Compute whether a line is hidden. Lines count as hidden when they
    // are part of a visual line that starts with another line, or when
    // they are entirely covered by collapsed, non-widget span.
    function lineIsHidden(doc, line) {
        var sps = sawCollapsedSpans && line.markedSpans;
        if (sps)
            for (var sp, i = 0; i < sps.length; ++i) {
                sp = sps[i];
                if (!sp.marker.collapsed) continue;
                if (sp.from == null) return true;
                if (sp.marker.widgetNode) continue;
                if (sp.from == 0 && sp.marker.inclusiveLeft && lineIsHiddenInner(doc, line, sp))
                    return true;
            }
    }

    function lineIsHiddenInner(doc, line, span) {
        if (span.to == null) {
            var end = span.marker.find(1, true);
            return lineIsHiddenInner(doc, end.line, getMarkedSpanFor(end.line.markedSpans, span.marker));
        }
        if (span.marker.inclusiveRight && span.to == line.text.length)
            return true;
        for (var sp, i = 0; i < line.markedSpans.length; ++i) {
            sp = line.markedSpans[i];
            if (sp.marker.collapsed && !sp.marker.widgetNode && sp.from == span.to &&
                (sp.to == null || sp.to != span.from) &&
                (sp.marker.inclusiveLeft || span.marker.inclusiveRight) &&
                lineIsHiddenInner(doc, line, sp)) return true;
        }
    }

    // LINE WIDGETS

    // Line widgets are block elements displayed above or below a line.

    var LineWidget = CodeMirror.LineWidget = function(doc, node, options) {
        if (options)
            for (var opt in options)
                if (options.hasOwnProperty(opt))
                    this[opt] = options[opt];
        this.doc = doc;
        this.node = node;
    };
    eventMixin(LineWidget);

    function adjustScrollWhenAboveVisible(cm, line, diff) {
        if (heightAtLine(line) < ((cm.curOp && cm.curOp.scrollTop) || cm.doc.scrollTop))
            addToScrollPos(cm, null, diff);
    }

    LineWidget.prototype.clear = function() {
        var cm = this.doc.cm,
            ws = this.line.widgets,
            line = this.line,
            no = lineNo(line);
        if (no == null || !ws) return;
        for (var i = 0; i < ws.length; ++i)
            if (ws[i] == this) ws.splice(i--, 1);
        if (!ws.length) line.widgets = null;
        var height = widgetHeight(this);
        updateLineHeight(line, Math.max(0, line.height - height));
        if (cm) runInOp(cm, function() {
            adjustScrollWhenAboveVisible(cm, line, -height);
            regLineChange(cm, no, "widget");
        });
    };
    LineWidget.prototype.changed = function() {
        var oldH = this.height,
            cm = this.doc.cm,
            line = this.line;
        this.height = null;
        var diff = widgetHeight(this) - oldH;
        if (!diff) return;
        updateLineHeight(line, line.height + diff);
        if (cm) runInOp(cm, function() {
            cm.curOp.forceUpdate = true;
            adjustScrollWhenAboveVisible(cm, line, diff);
        });
    };

    function widgetHeight(widget) {
        if (widget.height != null) return widget.height;
        var cm = widget.doc.cm;
        if (!cm) return 0;
        if (!contains(document.body, widget.node)) {
            var parentStyle = "position: relative;";
            if (widget.coverGutter)
                parentStyle += "margin-left: -" + cm.display.gutters.offsetWidth + "px;";
            if (widget.noHScroll)
                parentStyle += "width: " + cm.display.wrapper.clientWidth + "px;";
            removeChildrenAndAdd(cm.display.measure, elt("div", [widget.node], null, parentStyle));
        }
        return widget.height = widget.node.offsetHeight;
    }

    function addLineWidget(doc, handle, node, options) {
        var widget = new LineWidget(doc, node, options);
        var cm = doc.cm;
        if (cm && widget.noHScroll) cm.display.alignWidgets = true;
        changeLine(doc, handle, "widget", function(line) {
            var widgets = line.widgets || (line.widgets = []);
            if (widget.insertAt == null) widgets.push(widget);
            else widgets.splice(Math.min(widgets.length - 1, Math.max(0, widget.insertAt)), 0, widget);
            widget.line = line;
            if (cm && !lineIsHidden(doc, line)) {
                var aboveVisible = heightAtLine(line) < doc.scrollTop;
                updateLineHeight(line, line.height + widgetHeight(widget));
                if (aboveVisible) addToScrollPos(cm, null, widget.height);
                cm.curOp.forceUpdate = true;
            }
            return true;
        });
        return widget;
    }

    // LINE DATA STRUCTURE

    // Line objects. These hold state related to a line, including
    // highlighting info (the styles array).
    var Line = CodeMirror.Line = function(text, markedSpans, estimateHeight) {
        this.text = text;
        attachMarkedSpans(this, markedSpans);
        this.height = estimateHeight ? estimateHeight(this) : 1;
    };
    eventMixin(Line);
    Line.prototype.lineNo = function() {
        return lineNo(this);
    };

    // Change the content (text, markers) of a line. Automatically
    // invalidates cached information and tries to re-estimate the
    // line's height.
    function updateLine(line, text, markedSpans, estimateHeight) {
        line.text = text;
        if (line.stateAfter) line.stateAfter = null;
        if (line.styles) line.styles = null;
        if (line.order != null) line.order = null;
        detachMarkedSpans(line);
        attachMarkedSpans(line, markedSpans);
        var estHeight = estimateHeight ? estimateHeight(line) : 1;
        if (estHeight != line.height) updateLineHeight(line, estHeight);
    }

    // Detach a line from the document tree and its markers.
    function cleanUpLine(line) {
        line.parent = null;
        detachMarkedSpans(line);
    }

    function extractLineClasses(type, output) {
        if (type)
            for (;;) {
                var lineClass = type.match(/(?:^|\s+)line-(background-)?(\S+)/);
                if (!lineClass) break;
                type = type.slice(0, lineClass.index) + type.slice(lineClass.index + lineClass[0].length);
                var prop = lineClass[1] ? "bgClass" : "textClass";
                if (output[prop] == null)
                    output[prop] = lineClass[2];
                else if (!(new RegExp("(?:^|\s)" + lineClass[2] + "(?:$|\s)")).test(output[prop]))
                    output[prop] += " " + lineClass[2];
            }
        return type;
    }

    function callBlankLine(mode, state) {
        if (mode.blankLine) return mode.blankLine(state);
        if (!mode.innerMode) return;
        var inner = CodeMirror.innerMode(mode, state);
        if (inner.mode.blankLine) return inner.mode.blankLine(inner.state);
    }

    function readToken(mode, stream, state, inner) {
        for (var i = 0; i < 10; i++) {
            if (inner) inner[0] = CodeMirror.innerMode(mode, state).mode;
            var style = mode.token(stream, state);
            if (stream.pos > stream.start) return style;
        }
        throw new Error("Mode " + mode.name + " failed to advance stream.");
    }

    // Utility for getTokenAt and getLineTokens
    function takeToken(cm, pos, precise, asArray) {
        function getObj(copy) {
            return {
                start: stream.start,
                end: stream.pos,
                string: stream.current(),
                type: style || null,
                state: copy ? copyState(doc.mode, state) : state
            };
        }

        var doc = cm.doc,
            mode = doc.mode,
            style;
        pos = clipPos(doc, pos);
        var line = getLine(doc, pos.line),
            state = getStateBefore(cm, pos.line, precise);
        var stream = new StringStream(line.text, cm.options.tabSize),
            tokens;
        if (asArray) tokens = [];
        while ((asArray || stream.pos < pos.ch) && !stream.eol()) {
            stream.start = stream.pos;
            style = readToken(mode, stream, state);
            if (asArray) tokens.push(getObj(true));
        }
        return asArray ? tokens : getObj();
    }

    // Run the given mode's parser over a line, calling f for each token.
    function runMode(cm, text, mode, state, f, lineClasses, forceToEnd) {
        var flattenSpans = mode.flattenSpans;
        if (flattenSpans == null) flattenSpans = cm.options.flattenSpans;
        var curStart = 0,
            curStyle = null;
        var stream = new StringStream(text, cm.options.tabSize),
            style;
        var inner = cm.options.addModeClass && [null];
        if (text == "") extractLineClasses(callBlankLine(mode, state), lineClasses);
        while (!stream.eol()) {
            if (stream.pos > cm.options.maxHighlightLength) {
                flattenSpans = false;
                if (forceToEnd) processLine(cm, text, state, stream.pos);
                stream.pos = text.length;
                style = null;
            } else {
                style = extractLineClasses(readToken(mode, stream, state, inner), lineClasses);
            }
            if (inner) {
                var mName = inner[0].name;
                if (mName) style = "m-" + (style ? mName + " " + style : mName);
            }
            if (!flattenSpans || curStyle != style) {
                while (curStart < stream.start) {
                    curStart = Math.min(stream.start, curStart + 50000);
                    f(curStart, curStyle);
                }
                curStyle = style;
            }
            stream.start = stream.pos;
        }
        while (curStart < stream.pos) {
            // Webkit seems to refuse to render text nodes longer than 57444 characters
            var pos = Math.min(stream.pos, curStart + 50000);
            f(pos, curStyle);
            curStart = pos;
        }
    }

    // Compute a style array (an array starting with a mode generation
    // -- for invalidation -- followed by pairs of end positions and
    // style strings), which is used to highlight the tokens on the
    // line.
    function highlightLine(cm, line, state, forceToEnd) {
        // A styles array always starts with a number identifying the
        // mode/overlays that it is based on (for easy invalidation).
        var st = [cm.state.modeGen],
            lineClasses = {};
        // Compute the base array of styles
        runMode(cm, line.text, cm.doc.mode, state, function(end, style) {
            st.push(end, style);
        }, lineClasses, forceToEnd);

        // Run overlays, adjust style array.
        for (var o = 0; o < cm.state.overlays.length; ++o) {
            var overlay = cm.state.overlays[o],
                i = 1,
                at = 0;
            runMode(cm, line.text, overlay.mode, true, function(end, style) {
                var start = i;
                // Ensure there's a token end at the current position, and that i points at it
                while (at < end) {
                    var i_end = st[i];
                    if (i_end > end)
                        st.splice(i, 1, end, st[i + 1], i_end);
                    i += 2;
                    at = Math.min(end, i_end);
                }
                if (!style) return;
                if (overlay.opaque) {
                    st.splice(start, i - start, end, "cm-overlay " + style);
                    i = start + 2;
                } else {
                    for (; start < i; start += 2) {
                        var cur = st[start + 1];
                        st[start + 1] = (cur ? cur + " " : "") + "cm-overlay " + style;
                    }
                }
            }, lineClasses);
        }

        return {
            styles: st,
            classes: lineClasses.bgClass || lineClasses.textClass ? lineClasses : null
        };
    }

    function getLineStyles(cm, line, updateFrontier) {
        if (!line.styles || line.styles[0] != cm.state.modeGen) {
            var result = highlightLine(cm, line, line.stateAfter = getStateBefore(cm, lineNo(line)));
            line.styles = result.styles;
            if (result.classes) line.styleClasses = result.classes;
            else if (line.styleClasses) line.styleClasses = null;
            if (updateFrontier === cm.doc.frontier) cm.doc.frontier++;
        }
        return line.styles;
    }

    // Lightweight form of highlight -- proceed over this line and
    // update state, but don't save a style array. Used for lines that
    // aren't currently visible.
    function processLine(cm, text, state, startAt) {
        var mode = cm.doc.mode;
        var stream = new StringStream(text, cm.options.tabSize);
        stream.start = stream.pos = startAt || 0;
        if (text == "") callBlankLine(mode, state);
        while (!stream.eol() && stream.pos <= cm.options.maxHighlightLength) {
            readToken(mode, stream, state);
            stream.start = stream.pos;
        }
    }

    // Convert a style as returned by a mode (either null, or a string
    // containing one or more styles) to a CSS style. This is cached,
    // and also looks for line-wide styles.
    var styleToClassCache = {},
        styleToClassCacheWithMode = {};

    function interpretTokenStyle(style, options) {
        if (!style || /^\s*$/.test(style)) return null;
        var cache = options.addModeClass ? styleToClassCacheWithMode : styleToClassCache;
        return cache[style] ||
            (cache[style] = style.replace(/\S+/g, "cm-$&"));
    }

    // Render the DOM representation of the text of a line. Also builds
    // up a 'line map', which points at the DOM nodes that represent
    // specific stretches of text, and is used by the measuring code.
    // The returned object contains the DOM node, this map, and
    // information about line-wide styles that were set by the mode.
    function buildLineContent(cm, lineView) {
        // The padding-right forces the element to have a 'border', which
        // is needed on Webkit to be able to get line-level bounding
        // rectangles for it (in measureChar).
        var content = elt("span", null, null, webkit ? "padding-right: .1px" : null);
        var builder = {
            pre: elt("pre", [content]),
            content: content,
            col: 0,
            pos: 0,
            cm: cm,
            splitSpaces: (ie || webkit) && cm.getOption("lineWrapping")
        };
        lineView.measure = {};

        // Iterate over the logical lines that make up this visual line.
        for (var i = 0; i <= (lineView.rest ? lineView.rest.length : 0); i++) {
            var line = i ? lineView.rest[i - 1] : lineView.line,
                order;
            builder.pos = 0;
            builder.addToken = buildToken;
            // Optionally wire in some hacks into the token-rendering
            // algorithm, to deal with browser quirks.
            if (hasBadBidiRects(cm.display.measure) && (order = getOrder(line)))
                builder.addToken = buildTokenBadBidi(builder.addToken, order);
            builder.map = [];
            var allowFrontierUpdate = lineView != cm.display.externalMeasured && lineNo(line);
            insertLineContent(line, builder, getLineStyles(cm, line, allowFrontierUpdate));
            if (line.styleClasses) {
                if (line.styleClasses.bgClass)
                    builder.bgClass = joinClasses(line.styleClasses.bgClass, builder.bgClass || "");
                if (line.styleClasses.textClass)
                    builder.textClass = joinClasses(line.styleClasses.textClass, builder.textClass || "");
            }

            // Ensure at least a single node is present, for measuring.
            if (builder.map.length == 0)
                builder.map.push(0, 0, builder.content.appendChild(zeroWidthElement(cm.display.measure)));

            // Store the map and a cache object for the current logical line
            if (i == 0) {
                lineView.measure.map = builder.map;
                lineView.measure.cache = {};
            } else {
                (lineView.measure.maps || (lineView.measure.maps = [])).push(builder.map);
                (lineView.measure.caches || (lineView.measure.caches = [])).push({});
            }
        }

        // See issue #2901
        if (webkit && /\bcm-tab\b/.test(builder.content.lastChild.className))
            builder.content.className = "cm-tab-wrap-hack";

        signal(cm, "renderLine", cm, lineView.line, builder.pre);
        if (builder.pre.className)
            builder.textClass = joinClasses(builder.pre.className, builder.textClass || "");

        return builder;
    }

    function defaultSpecialCharPlaceholder(ch) {
        var token = elt("span", "\u2022", "cm-invalidchar");
        token.title = "\\u" + ch.charCodeAt(0).toString(16);
        token.setAttribute("aria-label", token.title);
        return token;
    }

    // Build up the DOM representation for a single token, and add it to
    // the line map. Takes care to render special characters separately.
    function buildToken(builder, text, style, startStyle, endStyle, title, css) {
        if (!text) return;
        var displayText = builder.splitSpaces ? text.replace(/ {3,}/g, splitSpaces) : text;
        var special = builder.cm.state.specialChars,
            mustWrap = false;
        if (!special.test(text)) {
            builder.col += text.length;
            var content = document.createTextNode(displayText);
            builder.map.push(builder.pos, builder.pos + text.length, content);
            if (ie && ie_version < 9) mustWrap = true;
            builder.pos += text.length;
        } else {
            var content = document.createDocumentFragment(),
                pos = 0;
            while (true) {
                special.lastIndex = pos;
                var m = special.exec(text);
                var skipped = m ? m.index - pos : text.length - pos;
                if (skipped) {
                    var txt = document.createTextNode(displayText.slice(pos, pos + skipped));
                    if (ie && ie_version < 9) content.appendChild(elt("span", [txt]));
                    else content.appendChild(txt);
                    builder.map.push(builder.pos, builder.pos + skipped, txt);
                    builder.col += skipped;
                    builder.pos += skipped;
                }
                if (!m) break;
                pos += skipped + 1;
                if (m[0] == "\t") {
                    var tabSize = builder.cm.options.tabSize,
                        tabWidth = tabSize - builder.col % tabSize;
                    var txt = content.appendChild(elt("span", spaceStr(tabWidth), "cm-tab"));
                    txt.setAttribute("role", "presentation");
                    txt.setAttribute("cm-text", "\t");
                    builder.col += tabWidth;
                } else {
                    var txt = builder.cm.options.specialCharPlaceholder(m[0]);
                    txt.setAttribute("cm-text", m[0]);
                    if (ie && ie_version < 9) content.appendChild(elt("span", [txt]));
                    else content.appendChild(txt);
                    builder.col += 1;
                }
                builder.map.push(builder.pos, builder.pos + 1, txt);
                builder.pos++;
            }
        }
        if (style || startStyle || endStyle || mustWrap || css) {
            var fullStyle = style || "";
            if (startStyle) fullStyle += startStyle;
            if (endStyle) fullStyle += endStyle;
            var token = elt("span", [content], fullStyle, css);
            if (title) token.title = title;
            return builder.content.appendChild(token);
        }
        builder.content.appendChild(content);
    }

    function splitSpaces(old) {
        var out = " ";
        for (var i = 0; i < old.length - 2; ++i) out += i % 2 ? " " : "\u00a0";
        out += " ";
        return out;
    }

    // Work around nonsense dimensions being reported for stretches of
    // right-to-left text.
    function buildTokenBadBidi(inner, order) {
        return function(builder, text, style, startStyle, endStyle, title, css) {
            style = style ? style + " cm-force-border" : "cm-force-border";
            var start = builder.pos,
                end = start + text.length;
            for (;;) {
                // Find the part that overlaps with the start of this text
                for (var i = 0; i < order.length; i++) {
                    var part = order[i];
                    if (part.to > start && part.from <= start) break;
                }
                if (part.to >= end) return inner(builder, text, style, startStyle, endStyle, title, css);
                inner(builder, text.slice(0, part.to - start), style, startStyle, null, title, css);
                startStyle = null;
                text = text.slice(part.to - start);
                start = part.to;
            }
        };
    }

    function buildCollapsedSpan(builder, size, marker, ignoreWidget) {
        var widget = !ignoreWidget && marker.widgetNode;
        if (widget) builder.map.push(builder.pos, builder.pos + size, widget);
        if (!ignoreWidget && builder.cm.display.input.needsContentAttribute) {
            if (!widget)
                widget = builder.content.appendChild(document.createElement("span"));
            widget.setAttribute("cm-marker", marker.id);
        }
        if (widget) {
            builder.cm.display.input.setUneditable(widget);
            builder.content.appendChild(widget);
        }
        builder.pos += size;
    }

    // Outputs a number of spans to make up a line, taking highlighting
    // and marked text into account.
    function insertLineContent(line, builder, styles) {
        var spans = line.markedSpans,
            allText = line.text,
            at = 0;
        if (!spans) {
            for (var i = 1; i < styles.length; i += 2)
                builder.addToken(builder, allText.slice(at, at = styles[i]), interpretTokenStyle(styles[i + 1], builder.cm.options));
            return;
        }

        var len = allText.length,
            pos = 0,
            i = 1,
            text = "",
            style, css;
        var nextChange = 0,
            spanStyle, spanEndStyle, spanStartStyle, title, collapsed;
        for (;;) {
            if (nextChange == pos) { // Update current marker set
                spanStyle = spanEndStyle = spanStartStyle = title = css = "";
                collapsed = null;
                nextChange = Infinity;
                var foundBookmarks = [];
                for (var j = 0; j < spans.length; ++j) {
                    var sp = spans[j],
                        m = sp.marker;
                    if (m.type == "bookmark" && sp.from == pos && m.widgetNode) {
                        foundBookmarks.push(m);
                    } else if (sp.from <= pos && (sp.to == null || sp.to > pos || m.collapsed && sp.to == pos && sp.from == pos)) {
                        if (sp.to != null && sp.to != pos && nextChange > sp.to) {
                            nextChange = sp.to;
                            spanEndStyle = "";
                        }
                        if (m.className) spanStyle += " " + m.className;
                        if (m.css) css = m.css;
                        if (m.startStyle && sp.from == pos) spanStartStyle += " " + m.startStyle;
                        if (m.endStyle && sp.to == nextChange) spanEndStyle += " " + m.endStyle;
                        if (m.title && !title) title = m.title;
                        if (m.collapsed && (!collapsed || compareCollapsedMarkers(collapsed.marker, m) < 0))
                            collapsed = sp;
                    } else if (sp.from > pos && nextChange > sp.from) {
                        nextChange = sp.from;
                    }
                }
                if (collapsed && (collapsed.from || 0) == pos) {
                    buildCollapsedSpan(builder, (collapsed.to == null ? len + 1 : collapsed.to) - pos,
                        collapsed.marker, collapsed.from == null);
                    if (collapsed.to == null) return;
                    if (collapsed.to == pos) collapsed = false;
                }
                if (!collapsed && foundBookmarks.length)
                    for (var j = 0; j < foundBookmarks.length; ++j)
                        buildCollapsedSpan(builder, 0, foundBookmarks[j]);
            }
            if (pos >= len) break;

            var upto = Math.min(len, nextChange);
            while (true) {
                if (text) {
                    var end = pos + text.length;
                    if (!collapsed) {
                        var tokenText = end > upto ? text.slice(0, upto - pos) : text;
                        builder.addToken(builder, tokenText, style ? style + spanStyle : spanStyle,
                            spanStartStyle, pos + tokenText.length == nextChange ? spanEndStyle : "", title, css);
                    }
                    if (end >= upto) {
                        text = text.slice(upto - pos);
                        pos = upto;
                        break;
                    }
                    pos = end;
                    spanStartStyle = "";
                }
                text = allText.slice(at, at = styles[i++]);
                style = interpretTokenStyle(styles[i++], builder.cm.options);
            }
        }
    }

    // DOCUMENT DATA STRUCTURE

    // By default, updates that start and end at the beginning of a line
    // are treated specially, in order to make the association of line
    // widgets and marker elements with the text behave more intuitive.
    function isWholeLineUpdate(doc, change) {
        return change.from.ch == 0 && change.to.ch == 0 && lst(change.text) == "" &&
            (!doc.cm || doc.cm.options.wholeLineUpdateBefore);
    }

    // Perform a change on the document data structure.
    function updateDoc(doc, change, markedSpans, estimateHeight) {
        function spansFor(n) {
            return markedSpans ? markedSpans[n] : null;
        }

        function update(line, text, spans) {
            updateLine(line, text, spans, estimateHeight);
            signalLater(line, "change", line, change);
        }

        function linesFor(start, end) {
            for (var i = start, result = []; i < end; ++i)
                result.push(new Line(text[i], spansFor(i), estimateHeight));
            return result;
        }

        var from = change.from,
            to = change.to,
            text = change.text;
        var firstLine = getLine(doc, from.line),
            lastLine = getLine(doc, to.line);
        var lastText = lst(text),
            lastSpans = spansFor(text.length - 1),
            nlines = to.line - from.line;

        // Adjust the line structure
        if (change.full) {
            doc.insert(0, linesFor(0, text.length));
            doc.remove(text.length, doc.size - text.length);
        } else if (isWholeLineUpdate(doc, change)) {
            // This is a whole-line replace. Treated specially to make
            // sure line objects move the way they are supposed to.
            var added = linesFor(0, text.length - 1);
            update(lastLine, lastLine.text, lastSpans);
            if (nlines) doc.remove(from.line, nlines);
            if (added.length) doc.insert(from.line, added);
        } else if (firstLine == lastLine) {
            if (text.length == 1) {
                update(firstLine, firstLine.text.slice(0, from.ch) + lastText + firstLine.text.slice(to.ch), lastSpans);
            } else {
                var added = linesFor(1, text.length - 1);
                added.push(new Line(lastText + firstLine.text.slice(to.ch), lastSpans, estimateHeight));
                update(firstLine, firstLine.text.slice(0, from.ch) + text[0], spansFor(0));
                doc.insert(from.line + 1, added);
            }
        } else if (text.length == 1) {
            update(firstLine, firstLine.text.slice(0, from.ch) + text[0] + lastLine.text.slice(to.ch), spansFor(0));
            doc.remove(from.line + 1, nlines);
        } else {
            update(firstLine, firstLine.text.slice(0, from.ch) + text[0], spansFor(0));
            update(lastLine, lastText + lastLine.text.slice(to.ch), lastSpans);
            var added = linesFor(1, text.length - 1);
            if (nlines > 1) doc.remove(from.line + 1, nlines - 1);
            doc.insert(from.line + 1, added);
        }

        signalLater(doc, "change", doc, change);
    }

    // The document is represented as a BTree consisting of leaves, with
    // chunk of lines in them, and branches, with up to ten leaves or
    // other branch nodes below them. The top node is always a branch
    // node, and is the document object itself (meaning it has
    // additional methods and properties).
    //
    // All nodes have parent links. The tree is used both to go from
    // line numbers to line objects, and to go from objects to numbers.
    // It also indexes by height, and is used to convert between height
    // and line object, and to find the total height of the document.
    //
    // See also http://marijnhaverbeke.nl/blog/codemirror-line-tree.html

    function LeafChunk(lines) {
        this.lines = lines;
        this.parent = null;
        for (var i = 0, height = 0; i < lines.length; ++i) {
            lines[i].parent = this;
            height += lines[i].height;
        }
        this.height = height;
    }

    LeafChunk.prototype = {
        chunkSize: function() {
            return this.lines.length;
        },
        // Remove the n lines at offset 'at'.
        removeInner: function(at, n) {
            for (var i = at, e = at + n; i < e; ++i) {
                var line = this.lines[i];
                this.height -= line.height;
                cleanUpLine(line);
                signalLater(line, "delete");
            }
            this.lines.splice(at, n);
        },
        // Helper used to collapse a small branch into a single leaf.
        collapse: function(lines) {
            lines.push.apply(lines, this.lines);
        },
        // Insert the given array of lines at offset 'at', count them as
        // having the given height.
        insertInner: function(at, lines, height) {
            this.height += height;
            this.lines = this.lines.slice(0, at).concat(lines).concat(this.lines.slice(at));
            for (var i = 0; i < lines.length; ++i) lines[i].parent = this;
        },
        // Used to iterate over a part of the tree.
        iterN: function(at, n, op) {
            for (var e = at + n; at < e; ++at)
                if (op(this.lines[at])) return true;
        }
    };

    function BranchChunk(children) {
        this.children = children;
        var size = 0,
            height = 0;
        for (var i = 0; i < children.length; ++i) {
            var ch = children[i];
            size += ch.chunkSize();
            height += ch.height;
            ch.parent = this;
        }
        this.size = size;
        this.height = height;
        this.parent = null;
    }

    BranchChunk.prototype = {
        chunkSize: function() {
            return this.size;
        },
        removeInner: function(at, n) {
            this.size -= n;
            for (var i = 0; i < this.children.length; ++i) {
                var child = this.children[i],
                    sz = child.chunkSize();
                if (at < sz) {
                    var rm = Math.min(n, sz - at),
                        oldHeight = child.height;
                    child.removeInner(at, rm);
                    this.height -= oldHeight - child.height;
                    if (sz == rm) {
                        this.children.splice(i--, 1);
                        child.parent = null;
                    }
                    if ((n -= rm) == 0) break;
                    at = 0;
                } else at -= sz;
            }
            // If the result is smaller than 25 lines, ensure that it is a
            // single leaf node.
            if (this.size - n < 25 &&
                (this.children.length > 1 || !(this.children[0] instanceof LeafChunk))) {
                var lines = [];
                this.collapse(lines);
                this.children = [new LeafChunk(lines)];
                this.children[0].parent = this;
            }
        },
        collapse: function(lines) {
            for (var i = 0; i < this.children.length; ++i) this.children[i].collapse(lines);
        },
        insertInner: function(at, lines, height) {
            this.size += lines.length;
            this.height += height;
            for (var i = 0; i < this.children.length; ++i) {
                var child = this.children[i],
                    sz = child.chunkSize();
                if (at <= sz) {
                    child.insertInner(at, lines, height);
                    if (child.lines && child.lines.length > 50) {
                        while (child.lines.length > 50) {
                            var spilled = child.lines.splice(child.lines.length - 25, 25);
                            var newleaf = new LeafChunk(spilled);
                            child.height -= newleaf.height;
                            this.children.splice(i + 1, 0, newleaf);
                            newleaf.parent = this;
                        }
                        this.maybeSpill();
                    }
                    break;
                }
                at -= sz;
            }
        },
        // When a node has grown, check whether it should be split.
        maybeSpill: function() {
            if (this.children.length <= 10) return;
            var me = this;
            do {
                var spilled = me.children.splice(me.children.length - 5, 5);
                var sibling = new BranchChunk(spilled);
                if (!me.parent) { // Become the parent node
                    var copy = new BranchChunk(me.children);
                    copy.parent = me;
                    me.children = [copy, sibling];
                    me = copy;
                } else {
                    me.size -= sibling.size;
                    me.height -= sibling.height;
                    var myIndex = indexOf(me.parent.children, me);
                    me.parent.children.splice(myIndex + 1, 0, sibling);
                }
                sibling.parent = me.parent;
            } while (me.children.length > 10);
            me.parent.maybeSpill();
        },
        iterN: function(at, n, op) {
            for (var i = 0; i < this.children.length; ++i) {
                var child = this.children[i],
                    sz = child.chunkSize();
                if (at < sz) {
                    var used = Math.min(n, sz - at);
                    if (child.iterN(at, used, op)) return true;
                    if ((n -= used) == 0) break;
                    at = 0;
                } else at -= sz;
            }
        }
    };

    var nextDocId = 0;
    var Doc = CodeMirror.Doc = function(text, mode, firstLine) {
        if (!(this instanceof Doc)) return new Doc(text, mode, firstLine);
        if (firstLine == null) firstLine = 0;

        BranchChunk.call(this, [new LeafChunk([new Line("", null)])]);
        this.first = firstLine;
        this.scrollTop = this.scrollLeft = 0;
        this.cantEdit = false;
        this.cleanGeneration = 1;
        this.frontier = firstLine;
        var start = Pos(firstLine, 0);
        this.sel = simpleSelection(start);
        this.history = new History(null);
        this.id = ++nextDocId;
        this.modeOption = mode;

        if (typeof text == "string") text = splitLines(text);
        updateDoc(this, {
            from: start,
            to: start,
            text: text
        });
        setSelection(this, simpleSelection(start), sel_dontScroll);
    };

    Doc.prototype = createObj(BranchChunk.prototype, {
        constructor: Doc,
        // Iterate over the document. Supports two forms -- with only one
        // argument, it calls that for each line in the document. With
        // three, it iterates over the range given by the first two (with
        // the second being non-inclusive).
        iter: function(from, to, op) {
            if (op) this.iterN(from - this.first, to - from, op);
            else this.iterN(this.first, this.first + this.size, from);
        },

        // Non-public interface for adding and removing lines.
        insert: function(at, lines) {
            var height = 0;
            for (var i = 0; i < lines.length; ++i) height += lines[i].height;
            this.insertInner(at - this.first, lines, height);
        },
        remove: function(at, n) {
            this.removeInner(at - this.first, n);
        },

        // From here, the methods are part of the public interface. Most
        // are also available from CodeMirror (editor) instances.

        getValue: function(lineSep) {
            var lines = getLines(this, this.first, this.first + this.size);
            if (lineSep === false) return lines;
            return lines.join(lineSep || "\n");
        },
        setValue: docMethodOp(function(code) {
            var top = Pos(this.first, 0),
                last = this.first + this.size - 1;
            makeChange(this, {
                from: top,
                to: Pos(last, getLine(this, last).text.length),
                text: splitLines(code),
                origin: "setValue",
                full: true
            }, true);
            setSelection(this, simpleSelection(top));
        }),
        replaceRange: function(code, from, to, origin) {
            from = clipPos(this, from);
            to = to ? clipPos(this, to) : from;
            replaceRange(this, code, from, to, origin);
        },
        getRange: function(from, to, lineSep) {
            var lines = getBetween(this, clipPos(this, from), clipPos(this, to));
            if (lineSep === false) return lines;
            return lines.join(lineSep || "\n");
        },

        getLine: function(line) {
            var l = this.getLineHandle(line);
            return l && l.text;
        },

        getLineHandle: function(line) {
            if (isLine(this, line)) return getLine(this, line);
        },
        getLineNumber: function(line) {
            return lineNo(line);
        },

        getLineHandleVisualStart: function(line) {
            if (typeof line == "number") line = getLine(this, line);
            return visualLine(line);
        },

        lineCount: function() {
            return this.size;
        },
        firstLine: function() {
            return this.first;
        },
        lastLine: function() {
            return this.first + this.size - 1;
        },

        clipPos: function(pos) {
            return clipPos(this, pos);
        },

        getCursor: function(start) {
            var range = this.sel.primary(),
                pos;
            if (start == null || start == "head") pos = range.head;
            else if (start == "anchor") pos = range.anchor;
            else if (start == "end" || start == "to" || start === false) pos = range.to();
            else pos = range.from();
            return pos;
        },
        listSelections: function() {
            return this.sel.ranges;
        },
        somethingSelected: function() {
            return this.sel.somethingSelected();
        },

        setCursor: docMethodOp(function(line, ch, options) {
            setSimpleSelection(this, clipPos(this, typeof line == "number" ? Pos(line, ch || 0) : line), null, options);
        }),
        setSelection: docMethodOp(function(anchor, head, options) {
            setSimpleSelection(this, clipPos(this, anchor), clipPos(this, head || anchor), options);
        }),
        extendSelection: docMethodOp(function(head, other, options) {
            extendSelection(this, clipPos(this, head), other && clipPos(this, other), options);
        }),
        extendSelections: docMethodOp(function(heads, options) {
            extendSelections(this, clipPosArray(this, heads, options));
        }),
        extendSelectionsBy: docMethodOp(function(f, options) {
            extendSelections(this, map(this.sel.ranges, f), options);
        }),
        setSelections: docMethodOp(function(ranges, primary, options) {
            if (!ranges.length) return;
            for (var i = 0, out = []; i < ranges.length; i++)
                out[i] = new Range(clipPos(this, ranges[i].anchor),
                    clipPos(this, ranges[i].head));
            if (primary == null) primary = Math.min(ranges.length - 1, this.sel.primIndex);
            setSelection(this, normalizeSelection(out, primary), options);
        }),
        addSelection: docMethodOp(function(anchor, head, options) {
            var ranges = this.sel.ranges.slice(0);
            ranges.push(new Range(clipPos(this, anchor), clipPos(this, head || anchor)));
            setSelection(this, normalizeSelection(ranges, ranges.length - 1), options);
        }),

        getSelection: function(lineSep) {
            var ranges = this.sel.ranges,
                lines;
            for (var i = 0; i < ranges.length; i++) {
                var sel = getBetween(this, ranges[i].from(), ranges[i].to());
                lines = lines ? lines.concat(sel) : sel;
            }
            if (lineSep === false) return lines;
            else return lines.join(lineSep || "\n");
        },
        getSelections: function(lineSep) {
            var parts = [],
                ranges = this.sel.ranges;
            for (var i = 0; i < ranges.length; i++) {
                var sel = getBetween(this, ranges[i].from(), ranges[i].to());
                if (lineSep !== false) sel = sel.join(lineSep || "\n");
                parts[i] = sel;
            }
            return parts;
        },
        replaceSelection: function(code, collapse, origin) {
            var dup = [];
            for (var i = 0; i < this.sel.ranges.length; i++)
                dup[i] = code;
            this.replaceSelections(dup, collapse, origin || "+input");
        },
        replaceSelections: docMethodOp(function(code, collapse, origin) {
            var changes = [],
                sel = this.sel;
            for (var i = 0; i < sel.ranges.length; i++) {
                var range = sel.ranges[i];
                changes[i] = {
                    from: range.from(),
                    to: range.to(),
                    text: splitLines(code[i]),
                    origin: origin
                };
            }
            var newSel = collapse && collapse != "end" && computeReplacedSel(this, changes, collapse);
            for (var i = changes.length - 1; i >= 0; i--)
                makeChange(this, changes[i]);
            if (newSel) setSelectionReplaceHistory(this, newSel);
            else if (this.cm) ensureCursorVisible(this.cm);
        }),
        undo: docMethodOp(function() {
            makeChangeFromHistory(this, "undo");
        }),
        redo: docMethodOp(function() {
            makeChangeFromHistory(this, "redo");
        }),
        undoSelection: docMethodOp(function() {
            makeChangeFromHistory(this, "undo", true);
        }),
        redoSelection: docMethodOp(function() {
            makeChangeFromHistory(this, "redo", true);
        }),

        setExtending: function(val) {
            this.extend = val;
        },
        getExtending: function() {
            return this.extend;
        },

        historySize: function() {
            var hist = this.history,
                done = 0,
                undone = 0;
            for (var i = 0; i < hist.done.length; i++)
                if (!hist.done[i].ranges) ++done;
            for (var i = 0; i < hist.undone.length; i++)
                if (!hist.undone[i].ranges) ++undone;
            return {
                undo: done,
                redo: undone
            };
        },
        clearHistory: function() {
            this.history = new History(this.history.maxGeneration);
        },

        markClean: function() {
            this.cleanGeneration = this.changeGeneration(true);
        },
        changeGeneration: function(forceSplit) {
            if (forceSplit)
                this.history.lastOp = this.history.lastSelOp = this.history.lastOrigin = null;
            return this.history.generation;
        },
        isClean: function(gen) {
            return this.history.generation == (gen || this.cleanGeneration);
        },

        getHistory: function() {
            return {
                done: copyHistoryArray(this.history.done),
                undone: copyHistoryArray(this.history.undone)
            };
        },
        setHistory: function(histData) {
            var hist = this.history = new History(this.history.maxGeneration);
            hist.done = copyHistoryArray(histData.done.slice(0), null, true);
            hist.undone = copyHistoryArray(histData.undone.slice(0), null, true);
        },

        addLineClass: docMethodOp(function(handle, where, cls) {
            return changeLine(this, handle, where == "gutter" ? "gutter" : "class", function(line) {
                var prop = where == "text" ? "textClass" : where == "background" ? "bgClass" : where == "gutter" ? "gutterClass" : "wrapClass";
                if (!line[prop]) line[prop] = cls;
                else if (classTest(cls).test(line[prop])) return false;
                else line[prop] += " " + cls;
                return true;
            });
        }),
        removeLineClass: docMethodOp(function(handle, where, cls) {
            return changeLine(this, handle, where == "gutter" ? "gutter" : "class", function(line) {
                var prop = where == "text" ? "textClass" : where == "background" ? "bgClass" : where == "gutter" ? "gutterClass" : "wrapClass";
                var cur = line[prop];
                if (!cur) return false;
                else if (cls == null) line[prop] = null;
                else {
                    var found = cur.match(classTest(cls));
                    if (!found) return false;
                    var end = found.index + found[0].length;
                    line[prop] = cur.slice(0, found.index) + (!found.index || end == cur.length ? "" : " ") + cur.slice(end) || null;
                }
                return true;
            });
        }),

        addLineWidget: docMethodOp(function(handle, node, options) {
            return addLineWidget(this, handle, node, options);
        }),
        removeLineWidget: function(widget) {
            widget.clear();
        },

        markText: function(from, to, options) {
            return markText(this, clipPos(this, from), clipPos(this, to), options, "range");
        },
        setBookmark: function(pos, options) {
            var realOpts = {
                replacedWith: options && (options.nodeType == null ? options.widget : options),
                insertLeft: options && options.insertLeft,
                clearWhenEmpty: false,
                shared: options && options.shared,
                handleMouseEvents: options && options.handleMouseEvents
            };
            pos = clipPos(this, pos);
            return markText(this, pos, pos, realOpts, "bookmark");
        },
        findMarksAt: function(pos) {
            pos = clipPos(this, pos);
            var markers = [],
                spans = getLine(this, pos.line).markedSpans;
            if (spans)
                for (var i = 0; i < spans.length; ++i) {
                    var span = spans[i];
                    if ((span.from == null || span.from <= pos.ch) &&
                        (span.to == null || span.to >= pos.ch))
                        markers.push(span.marker.parent || span.marker);
                }
            return markers;
        },
        findMarks: function(from, to, filter) {
            from = clipPos(this, from);
            to = clipPos(this, to);
            var found = [],
                lineNo = from.line;
            this.iter(from.line, to.line + 1, function(line) {
                var spans = line.markedSpans;
                if (spans)
                    for (var i = 0; i < spans.length; i++) {
                        var span = spans[i];
                        if (!(lineNo == from.line && from.ch > span.to ||
                                span.from == null && lineNo != from.line ||
                                lineNo == to.line && span.from > to.ch) &&
                            (!filter || filter(span.marker)))
                            found.push(span.marker.parent || span.marker);
                    }
                    ++lineNo;
            });
            return found;
        },
        getAllMarks: function() {
            var markers = [];
            this.iter(function(line) {
                var sps = line.markedSpans;
                if (sps)
                    for (var i = 0; i < sps.length; ++i)
                        if (sps[i].from != null) markers.push(sps[i].marker);
            });
            return markers;
        },

        posFromIndex: function(off) {
            var ch, lineNo = this.first;
            this.iter(function(line) {
                var sz = line.text.length + 1;
                if (sz > off) {
                    ch = off;
                    return true;
                }
                off -= sz;
                ++lineNo;
            });
            return clipPos(this, Pos(lineNo, ch));
        },
        indexFromPos: function(coords) {
            coords = clipPos(this, coords);
            var index = coords.ch;
            if (coords.line < this.first || coords.ch < 0) return 0;
            this.iter(this.first, coords.line, function(line) {
                index += line.text.length + 1;
            });
            return index;
        },

        copy: function(copyHistory) {
            var doc = new Doc(getLines(this, this.first, this.first + this.size), this.modeOption, this.first);
            doc.scrollTop = this.scrollTop;
            doc.scrollLeft = this.scrollLeft;
            doc.sel = this.sel;
            doc.extend = false;
            if (copyHistory) {
                doc.history.undoDepth = this.history.undoDepth;
                doc.setHistory(this.getHistory());
            }
            return doc;
        },

        linkedDoc: function(options) {
            if (!options) options = {};
            var from = this.first,
                to = this.first + this.size;
            if (options.from != null && options.from > from) from = options.from;
            if (options.to != null && options.to < to) to = options.to;
            var copy = new Doc(getLines(this, from, to), options.mode || this.modeOption, from);
            if (options.sharedHist) copy.history = this.history;
            (this.linked || (this.linked = [])).push({
                doc: copy,
                sharedHist: options.sharedHist
            });
            copy.linked = [{
                doc: this,
                isParent: true,
                sharedHist: options.sharedHist
            }];
            copySharedMarkers(copy, findSharedMarkers(this));
            return copy;
        },
        unlinkDoc: function(other) {
            if (other instanceof CodeMirror) other = other.doc;
            if (this.linked)
                for (var i = 0; i < this.linked.length; ++i) {
                    var link = this.linked[i];
                    if (link.doc != other) continue;
                    this.linked.splice(i, 1);
                    other.unlinkDoc(this);
                    detachSharedMarkers(findSharedMarkers(this));
                    break;
                }
                // If the histories were shared, split them again
            if (other.history == this.history) {
                var splitIds = [other.id];
                linkedDocs(other, function(doc) {
                    splitIds.push(doc.id);
                }, true);
                other.history = new History(null);
                other.history.done = copyHistoryArray(this.history.done, splitIds);
                other.history.undone = copyHistoryArray(this.history.undone, splitIds);
            }
        },
        iterLinkedDocs: function(f) {
            linkedDocs(this, f);
        },

        getMode: function() {
            return this.mode;
        },
        getEditor: function() {
            return this.cm;
        }
    });

    // Public alias.
    Doc.prototype.eachLine = Doc.prototype.iter;

    // Set up methods on CodeMirror's prototype to redirect to the editor's document.
    var dontDelegate = "iter insert remove copy getEditor".split(" ");
    for (var prop in Doc.prototype)
        if (Doc.prototype.hasOwnProperty(prop) && indexOf(dontDelegate, prop) < 0)
            CodeMirror.prototype[prop] = (function(method) {
                return function() {
                    return method.apply(this.doc, arguments);
                };
            })(Doc.prototype[prop]);

    eventMixin(Doc);

    // Call f for all linked documents.
    function linkedDocs(doc, f, sharedHistOnly) {
        function propagate(doc, skip, sharedHist) {
            if (doc.linked)
                for (var i = 0; i < doc.linked.length; ++i) {
                    var rel = doc.linked[i];
                    if (rel.doc == skip) continue;
                    var shared = sharedHist && rel.sharedHist;
                    if (sharedHistOnly && !shared) continue;
                    f(rel.doc, shared);
                    propagate(rel.doc, doc, shared);
                }
        }
        propagate(doc, null, true);
    }

    // Attach a document to an editor.
    function attachDoc(cm, doc) {
        if (doc.cm) throw new Error("This document is already in use.");
        cm.doc = doc;
        doc.cm = cm;
        estimateLineHeights(cm);
        loadMode(cm);
        if (!cm.options.lineWrapping) findMaxLine(cm);
        cm.options.mode = doc.modeOption;
        regChange(cm);
    }

    // LINE UTILITIES

    // Find the line object corresponding to the given line number.
    function getLine(doc, n) {
        n -= doc.first;
        if (n < 0 || n >= doc.size) throw new Error("There is no line " + (n + doc.first) + " in the document.");
        for (var chunk = doc; !chunk.lines;) {
            for (var i = 0;; ++i) {
                var child = chunk.children[i],
                    sz = child.chunkSize();
                if (n < sz) {
                    chunk = child;
                    break;
                }
                n -= sz;
            }
        }
        return chunk.lines[n];
    }

    // Get the part of a document between two positions, as an array of
    // strings.
    function getBetween(doc, start, end) {
        var out = [],
            n = start.line;
        doc.iter(start.line, end.line + 1, function(line) {
            var text = line.text;
            if (n == end.line) text = text.slice(0, end.ch);
            if (n == start.line) text = text.slice(start.ch);
            out.push(text);
            ++n;
        });
        return out;
    }
    // Get the lines between from and to, as array of strings.
    function getLines(doc, from, to) {
        var out = [];
        doc.iter(from, to, function(line) {
            out.push(line.text);
        });
        return out;
    }

    // Update the height of a line, propagating the height change
    // upwards to parent nodes.
    function updateLineHeight(line, height) {
        var diff = height - line.height;
        if (diff)
            for (var n = line; n; n = n.parent) n.height += diff;
    }

    // Given a line object, find its line number by walking up through
    // its parent links.
    function lineNo(line) {
        if (line.parent == null) return null;
        var cur = line.parent,
            no = indexOf(cur.lines, line);
        for (var chunk = cur.parent; chunk; cur = chunk, chunk = chunk.parent) {
            for (var i = 0;; ++i) {
                if (chunk.children[i] == cur) break;
                no += chunk.children[i].chunkSize();
            }
        }
        return no + cur.first;
    }

    // Find the line at the given vertical position, using the height
    // information in the document tree.
    function lineAtHeight(chunk, h) {
        var n = chunk.first;
        outer: do {
            for (var i = 0; i < chunk.children.length; ++i) {
                var child = chunk.children[i],
                    ch = child.height;
                if (h < ch) {
                    chunk = child;
                    continue outer;
                }
                h -= ch;
                n += child.chunkSize();
            }
            return n;
        } while (!chunk.lines);
        for (var i = 0; i < chunk.lines.length; ++i) {
            var line = chunk.lines[i],
                lh = line.height;
            if (h < lh) break;
            h -= lh;
        }
        return n + i;
    }


    // Find the height above the given line.
    function heightAtLine(lineObj) {
        lineObj = visualLine(lineObj);

        var h = 0,
            chunk = lineObj.parent;
        for (var i = 0; i < chunk.lines.length; ++i) {
            var line = chunk.lines[i];
            if (line == lineObj) break;
            else h += line.height;
        }
        for (var p = chunk.parent; p; chunk = p, p = chunk.parent) {
            for (var i = 0; i < p.children.length; ++i) {
                var cur = p.children[i];
                if (cur == chunk) break;
                else h += cur.height;
            }
        }
        return h;
    }

    // Get the bidi ordering for the given line (and cache it). Returns
    // false for lines that are fully left-to-right, and an array of
    // BidiSpan objects otherwise.
    function getOrder(line) {
        var order = line.order;
        if (order == null) order = line.order = bidiOrdering(line.text);
        return order;
    }

    // HISTORY

    function History(startGen) {
        // Arrays of change events and selections. Doing something adds an
        // event to done and clears undo. Undoing moves events from done
        // to undone, redoing moves them in the other direction.
        this.done = [];
        this.undone = [];
        this.undoDepth = Infinity;
        // Used to track when changes can be merged into a single undo
        // event
        this.lastModTime = this.lastSelTime = 0;
        this.lastOp = this.lastSelOp = null;
        this.lastOrigin = this.lastSelOrigin = null;
        // Used by the isClean() method
        this.generation = this.maxGeneration = startGen || 1;
    }

    // Create a history change event from an updateDoc-style change
    // object.
    function historyChangeFromChange(doc, change) {
        var histChange = {
            from: copyPos(change.from),
            to: changeEnd(change),
            text: getBetween(doc, change.from, change.to)
        };
        attachLocalSpans(doc, histChange, change.from.line, change.to.line + 1);
        linkedDocs(doc, function(doc) {
            attachLocalSpans(doc, histChange, change.from.line, change.to.line + 1);
        }, true);
        return histChange;
    }

    // Pop all selection events off the end of a history array. Stop at
    // a change event.
    function clearSelectionEvents(array) {
        while (array.length) {
            var last = lst(array);
            if (last.ranges) array.pop();
            else break;
        }
    }

    // Find the top change event in the history. Pop off selection
    // events that are in the way.
    function lastChangeEvent(hist, force) {
        if (force) {
            clearSelectionEvents(hist.done);
            return lst(hist.done);
        } else if (hist.done.length && !lst(hist.done).ranges) {
            return lst(hist.done);
        } else if (hist.done.length > 1 && !hist.done[hist.done.length - 2].ranges) {
            hist.done.pop();
            return lst(hist.done);
        }
    }

    // Register a change in the history. Merges changes that are within
    // a single operation, ore are close together with an origin that
    // allows merging (starting with "+") into a single event.
    function addChangeToHistory(doc, change, selAfter, opId) {
        var hist = doc.history;
        hist.undone.length = 0;
        var time = +new Date,
            cur;

        if ((hist.lastOp == opId ||
                hist.lastOrigin == change.origin && change.origin &&
                ((change.origin.charAt(0) == "+" && doc.cm && hist.lastModTime > time - doc.cm.options.historyEventDelay) ||
                    change.origin.charAt(0) == "*")) &&
            (cur = lastChangeEvent(hist, hist.lastOp == opId))) {
            // Merge this change into the last event
            var last = lst(cur.changes);
            if (cmp(change.from, change.to) == 0 && cmp(change.from, last.to) == 0) {
                // Optimized case for simple insertion -- don't want to add
                // new changesets for every character typed
                last.to = changeEnd(change);
            } else {
                // Add new sub-event
                cur.changes.push(historyChangeFromChange(doc, change));
            }
        } else {
            // Can not be merged, start a new event.
            var before = lst(hist.done);
            if (!before || !before.ranges)
                pushSelectionToHistory(doc.sel, hist.done);
            cur = {
                changes: [historyChangeFromChange(doc, change)],
                generation: hist.generation
            };
            hist.done.push(cur);
            while (hist.done.length > hist.undoDepth) {
                hist.done.shift();
                if (!hist.done[0].ranges) hist.done.shift();
            }
        }
        hist.done.push(selAfter);
        hist.generation = ++hist.maxGeneration;
        hist.lastModTime = hist.lastSelTime = time;
        hist.lastOp = hist.lastSelOp = opId;
        hist.lastOrigin = hist.lastSelOrigin = change.origin;

        if (!last) signal(doc, "historyAdded");
    }

    function selectionEventCanBeMerged(doc, origin, prev, sel) {
        var ch = origin.charAt(0);
        return ch == "*" ||
            ch == "+" &&
            prev.ranges.length == sel.ranges.length &&
            prev.somethingSelected() == sel.somethingSelected() &&
            new Date - doc.history.lastSelTime <= (doc.cm ? doc.cm.options.historyEventDelay : 500);
    }

    // Called whenever the selection changes, sets the new selection as
    // the pending selection in the history, and pushes the old pending
    // selection into the 'done' array when it was significantly
    // different (in number of selected ranges, emptiness, or time).
    function addSelectionToHistory(doc, sel, opId, options) {
        var hist = doc.history,
            origin = options && options.origin;

        // A new event is started when the previous origin does not match
        // the current, or the origins don't allow matching. Origins
        // starting with * are always merged, those starting with + are
        // merged when similar and close together in time.
        if (opId == hist.lastSelOp ||
            (origin && hist.lastSelOrigin == origin &&
                (hist.lastModTime == hist.lastSelTime && hist.lastOrigin == origin ||
                    selectionEventCanBeMerged(doc, origin, lst(hist.done), sel))))
            hist.done[hist.done.length - 1] = sel;
        else
            pushSelectionToHistory(sel, hist.done);

        hist.lastSelTime = +new Date;
        hist.lastSelOrigin = origin;
        hist.lastSelOp = opId;
        if (options && options.clearRedo !== false)
            clearSelectionEvents(hist.undone);
    }

    function pushSelectionToHistory(sel, dest) {
        var top = lst(dest);
        if (!(top && top.ranges && top.equals(sel)))
            dest.push(sel);
    }

    // Used to store marked span information in the history.
    function attachLocalSpans(doc, change, from, to) {
        var existing = change["spans_" + doc.id],
            n = 0;
        doc.iter(Math.max(doc.first, from), Math.min(doc.first + doc.size, to), function(line) {
            if (line.markedSpans)
                (existing || (existing = change["spans_" + doc.id] = {}))[n] = line.markedSpans;
            ++n;
        });
    }

    // When un/re-doing restores text containing marked spans, those
    // that have been explicitly cleared should not be restored.
    function removeClearedSpans(spans) {
        if (!spans) return null;
        for (var i = 0, out; i < spans.length; ++i) {
            if (spans[i].marker.explicitlyCleared) {
                if (!out) out = spans.slice(0, i);
            } else if (out) out.push(spans[i]);
        }
        return !out ? spans : out.length ? out : null;
    }

    // Retrieve and filter the old marked spans stored in a change event.
    function getOldSpans(doc, change) {
        var found = change["spans_" + doc.id];
        if (!found) return null;
        for (var i = 0, nw = []; i < change.text.length; ++i)
            nw.push(removeClearedSpans(found[i]));
        return nw;
    }

    // Used both to provide a JSON-safe object in .getHistory, and, when
    // detaching a document, to split the history in two
    function copyHistoryArray(events, newGroup, instantiateSel) {
        for (var i = 0, copy = []; i < events.length; ++i) {
            var event = events[i];
            if (event.ranges) {
                copy.push(instantiateSel ? Selection.prototype.deepCopy.call(event) : event);
                continue;
            }
            var changes = event.changes,
                newChanges = [];
            copy.push({
                changes: newChanges
            });
            for (var j = 0; j < changes.length; ++j) {
                var change = changes[j],
                    m;
                newChanges.push({
                    from: change.from,
                    to: change.to,
                    text: change.text
                });
                if (newGroup)
                    for (var prop in change)
                        if (m = prop.match(/^spans_(\d+)$/)) {
                            if (indexOf(newGroup, Number(m[1])) > -1) {
                                lst(newChanges)[prop] = change[prop];
                                delete change[prop];
                            }
                        }
            }
        }
        return copy;
    }

    // Rebasing/resetting history to deal with externally-sourced changes

    function rebaseHistSelSingle(pos, from, to, diff) {
        if (to < pos.line) {
            pos.line += diff;
        } else if (from < pos.line) {
            pos.line = from;
            pos.ch = 0;
        }
    }

    // Tries to rebase an array of history events given a change in the
    // document. If the change touches the same lines as the event, the
    // event, and everything 'behind' it, is discarded. If the change is
    // before the event, the event's positions are updated. Uses a
    // copy-on-write scheme for the positions, to avoid having to
    // reallocate them all on every rebase, but also avoid problems with
    // shared position objects being unsafely updated.
    function rebaseHistArray(array, from, to, diff) {
        for (var i = 0; i < array.length; ++i) {
            var sub = array[i],
                ok = true;
            if (sub.ranges) {
                if (!sub.copied) {
                    sub = array[i] = sub.deepCopy();
                    sub.copied = true;
                }
                for (var j = 0; j < sub.ranges.length; j++) {
                    rebaseHistSelSingle(sub.ranges[j].anchor, from, to, diff);
                    rebaseHistSelSingle(sub.ranges[j].head, from, to, diff);
                }
                continue;
            }
            for (var j = 0; j < sub.changes.length; ++j) {
                var cur = sub.changes[j];
                if (to < cur.from.line) {
                    cur.from = Pos(cur.from.line + diff, cur.from.ch);
                    cur.to = Pos(cur.to.line + diff, cur.to.ch);
                } else if (from <= cur.to.line) {
                    ok = false;
                    break;
                }
            }
            if (!ok) {
                array.splice(0, i + 1);
                i = 0;
            }
        }
    }

    function rebaseHist(hist, change) {
        var from = change.from.line,
            to = change.to.line,
            diff = change.text.length - (to - from) - 1;
        rebaseHistArray(hist.done, from, to, diff);
        rebaseHistArray(hist.undone, from, to, diff);
    }

    // EVENT UTILITIES

    // Due to the fact that we still support jurassic IE versions, some
    // compatibility wrappers are needed.

    var e_preventDefault = CodeMirror.e_preventDefault = function(e) {
        if (e.preventDefault) e.preventDefault();
        else e.returnValue = false;
    };
    var e_stopPropagation = CodeMirror.e_stopPropagation = function(e) {
        if (e.stopPropagation) e.stopPropagation();
        else e.cancelBubble = true;
    };

    function e_defaultPrevented(e) {
        return e.defaultPrevented != null ? e.defaultPrevented : e.returnValue == false;
    }
    var e_stop = CodeMirror.e_stop = function(e) {
        e_preventDefault(e);
        e_stopPropagation(e);
    };

    function e_target(e) {
        return e.target || e.srcElement;
    }

    function e_button(e) {
        var b = e.which;
        if (b == null) {
            if (e.button & 1) b = 1;
            else if (e.button & 2) b = 3;
            else if (e.button & 4) b = 2;
        }
        if (mac && e.ctrlKey && b == 1) b = 3;
        return b;
    }

    // EVENT HANDLING

    // Lightweight event framework. on/off also work on DOM nodes,
    // registering native DOM handlers.

    var on = CodeMirror.on = function(emitter, type, f) {
        if (emitter.addEventListener)
            emitter.addEventListener(type, f, false);
        else if (emitter.attachEvent)
            emitter.attachEvent("on" + type, f);
        else {
            var map = emitter._handlers || (emitter._handlers = {});
            var arr = map[type] || (map[type] = []);
            arr.push(f);
        }
    };

    var off = CodeMirror.off = function(emitter, type, f) {
        if (emitter.removeEventListener)
            emitter.removeEventListener(type, f, false);
        else if (emitter.detachEvent)
            emitter.detachEvent("on" + type, f);
        else {
            var arr = emitter._handlers && emitter._handlers[type];
            if (!arr) return;
            for (var i = 0; i < arr.length; ++i)
                if (arr[i] == f) {
                    arr.splice(i, 1);
                    break;
                }
        }
    };

    var signal = CodeMirror.signal = function(emitter, type /*, values...*/ ) {
        var arr = emitter._handlers && emitter._handlers[type];
        if (!arr) return;
        var args = Array.prototype.slice.call(arguments, 2);
        for (var i = 0; i < arr.length; ++i) arr[i].apply(null, args);
    };

    var orphanDelayedCallbacks = null;

    // Often, we want to signal events at a point where we are in the
    // middle of some work, but don't want the handler to start calling
    // other methods on the editor, which might be in an inconsistent
    // state or simply not expect any other events to happen.
    // signalLater looks whether there are any handlers, and schedules
    // them to be executed when the last operation ends, or, if no
    // operation is active, when a timeout fires.
    function signalLater(emitter, type /*, values...*/ ) {
        var arr = emitter._handlers && emitter._handlers[type];
        if (!arr) return;
        var args = Array.prototype.slice.call(arguments, 2),
            list;
        if (operationGroup) {
            list = operationGroup.delayedCallbacks;
        } else if (orphanDelayedCallbacks) {
            list = orphanDelayedCallbacks;
        } else {
            list = orphanDelayedCallbacks = [];
            setTimeout(fireOrphanDelayed, 0);
        }

        function bnd(f) {
            return function() {
                f.apply(null, args);
            };
        };
        for (var i = 0; i < arr.length; ++i)
            list.push(bnd(arr[i]));
    }

    function fireOrphanDelayed() {
        var delayed = orphanDelayedCallbacks;
        orphanDelayedCallbacks = null;
        for (var i = 0; i < delayed.length; ++i) delayed[i]();
    }

    // The DOM events that CodeMirror handles can be overridden by
    // registering a (non-DOM) handler on the editor for the event name,
    // and preventDefault-ing the event in that handler.
    function signalDOMEvent(cm, e, override) {
        if (typeof e == "string")
            e = {
                type: e,
                preventDefault: function() {
                    this.defaultPrevented = true;
                }
            };
        signal(cm, override || e.type, cm, e);
        return e_defaultPrevented(e) || e.codemirrorIgnore;
    }

    function signalCursorActivity(cm) {
        var arr = cm._handlers && cm._handlers.cursorActivity;
        if (!arr) return;
        var set = cm.curOp.cursorActivityHandlers || (cm.curOp.cursorActivityHandlers = []);
        for (var i = 0; i < arr.length; ++i)
            if (indexOf(set, arr[i]) == -1)
                set.push(arr[i]);
    }

    function hasHandler(emitter, type) {
        var arr = emitter._handlers && emitter._handlers[type];
        return arr && arr.length > 0;
    }

    // Add on and off methods to a constructor's prototype, to make
    // registering events on such objects more convenient.
    function eventMixin(ctor) {
        ctor.prototype.on = function(type, f) {
            on(this, type, f);
        };
        ctor.prototype.off = function(type, f) {
            off(this, type, f);
        };
    }

    // MISC UTILITIES

    // Number of pixels added to scroller and sizer to hide scrollbar
    var scrollerGap = 30;

    // Returned or thrown by various protocols to signal 'I'm not
    // handling this'.
    var Pass = CodeMirror.Pass = {
        toString: function() {
            return "CodeMirror.Pass";
        }
    };

    // Reused option objects for setSelection & friends
    var sel_dontScroll = {
            scroll: false
        },
        sel_mouse = {
            origin: "*mouse"
        },
        sel_move = {
            origin: "+move"
        };

    function Delayed() {
        this.id = null;
    }
    Delayed.prototype.set = function(ms, f) {
        clearTimeout(this.id);
        this.id = setTimeout(f, ms);
    };

    // Counts the column offset in a string, taking tabs into account.
    // Used mostly to find indentation.
    var countColumn = CodeMirror.countColumn = function(string, end, tabSize, startIndex, startValue) {
        if (end == null) {
            end = string.search(/[^\s\u00a0]/);
            if (end == -1) end = string.length;
        }
        for (var i = startIndex || 0, n = startValue || 0;;) {
            var nextTab = string.indexOf("\t", i);
            if (nextTab < 0 || nextTab >= end)
                return n + (end - i);
            n += nextTab - i;
            n += tabSize - (n % tabSize);
            i = nextTab + 1;
        }
    };

    // The inverse of countColumn -- find the offset that corresponds to
    // a particular column.
    function findColumn(string, goal, tabSize) {
        for (var pos = 0, col = 0;;) {
            var nextTab = string.indexOf("\t", pos);
            if (nextTab == -1) nextTab = string.length;
            var skipped = nextTab - pos;
            if (nextTab == string.length || col + skipped >= goal)
                return pos + Math.min(skipped, goal - col);
            col += nextTab - pos;
            col += tabSize - (col % tabSize);
            pos = nextTab + 1;
            if (col >= goal) return pos;
        }
    }

    var spaceStrs = [""];

    function spaceStr(n) {
        while (spaceStrs.length <= n)
            spaceStrs.push(lst(spaceStrs) + " ");
        return spaceStrs[n];
    }

    function lst(arr) {
        return arr[arr.length - 1];
    }

    var selectInput = function(node) {
        node.select();
    };
    if (ios) // Mobile Safari apparently has a bug where select() is broken.
        selectInput = function(node) {
        node.selectionStart = 0;
        node.selectionEnd = node.value.length;
    };
    else if (ie) // Suppress mysterious IE10 errors
        selectInput = function(node) {
        try {
            node.select();
        } catch (_e) {}
    };

    function indexOf(array, elt) {
        for (var i = 0; i < array.length; ++i)
            if (array[i] == elt) return i;
        return -1;
    }

    function map(array, f) {
        var out = [];
        for (var i = 0; i < array.length; i++) out[i] = f(array[i], i);
        return out;
    }

    function nothing() {}

    function createObj(base, props) {
        var inst;
        if (Object.create) {
            inst = Object.create(base);
        } else {
            nothing.prototype = base;
            inst = new nothing();
        }
        if (props) copyObj(props, inst);
        return inst;
    };

    function copyObj(obj, target, overwrite) {
        if (!target) target = {};
        for (var prop in obj)
            if (obj.hasOwnProperty(prop) && (overwrite !== false || !target.hasOwnProperty(prop)))
                target[prop] = obj[prop];
        return target;
    }

    function bind(f) {
        var args = Array.prototype.slice.call(arguments, 1);
        return function() {
            return f.apply(null, args);
        };
    }

    var nonASCIISingleCaseWordChar = /[\u00df\u0587\u0590-\u05f4\u0600-\u06ff\u3040-\u309f\u30a0-\u30ff\u3400-\u4db5\u4e00-\u9fcc\uac00-\ud7af]/;
    var isWordCharBasic = CodeMirror.isWordChar = function(ch) {
        return /\w/.test(ch) || ch > "\x80" &&
            (ch.toUpperCase() != ch.toLowerCase() || nonASCIISingleCaseWordChar.test(ch));
    };

    function isWordChar(ch, helper) {
        if (!helper) return isWordCharBasic(ch);
        if (helper.source.indexOf("\\w") > -1 && isWordCharBasic(ch)) return true;
        return helper.test(ch);
    }

    function isEmpty(obj) {
        for (var n in obj)
            if (obj.hasOwnProperty(n) && obj[n]) return false;
        return true;
    }

    // Extending unicode characters. A series of a non-extending char +
    // any number of extending chars is treated as a single unit as far
    // as editing and measuring is concerned. This is not fully correct,
    // since some scripts/fonts/browsers also treat other configurations
    // of code points as a group.
    var extendingChars = /[\u0300-\u036f\u0483-\u0489\u0591-\u05bd\u05bf\u05c1\u05c2\u05c4\u05c5\u05c7\u0610-\u061a\u064b-\u065e\u0670\u06d6-\u06dc\u06de-\u06e4\u06e7\u06e8\u06ea-\u06ed\u0711\u0730-\u074a\u07a6-\u07b0\u07eb-\u07f3\u0816-\u0819\u081b-\u0823\u0825-\u0827\u0829-\u082d\u0900-\u0902\u093c\u0941-\u0948\u094d\u0951-\u0955\u0962\u0963\u0981\u09bc\u09be\u09c1-\u09c4\u09cd\u09d7\u09e2\u09e3\u0a01\u0a02\u0a3c\u0a41\u0a42\u0a47\u0a48\u0a4b-\u0a4d\u0a51\u0a70\u0a71\u0a75\u0a81\u0a82\u0abc\u0ac1-\u0ac5\u0ac7\u0ac8\u0acd\u0ae2\u0ae3\u0b01\u0b3c\u0b3e\u0b3f\u0b41-\u0b44\u0b4d\u0b56\u0b57\u0b62\u0b63\u0b82\u0bbe\u0bc0\u0bcd\u0bd7\u0c3e-\u0c40\u0c46-\u0c48\u0c4a-\u0c4d\u0c55\u0c56\u0c62\u0c63\u0cbc\u0cbf\u0cc2\u0cc6\u0ccc\u0ccd\u0cd5\u0cd6\u0ce2\u0ce3\u0d3e\u0d41-\u0d44\u0d4d\u0d57\u0d62\u0d63\u0dca\u0dcf\u0dd2-\u0dd4\u0dd6\u0ddf\u0e31\u0e34-\u0e3a\u0e47-\u0e4e\u0eb1\u0eb4-\u0eb9\u0ebb\u0ebc\u0ec8-\u0ecd\u0f18\u0f19\u0f35\u0f37\u0f39\u0f71-\u0f7e\u0f80-\u0f84\u0f86\u0f87\u0f90-\u0f97\u0f99-\u0fbc\u0fc6\u102d-\u1030\u1032-\u1037\u1039\u103a\u103d\u103e\u1058\u1059\u105e-\u1060\u1071-\u1074\u1082\u1085\u1086\u108d\u109d\u135f\u1712-\u1714\u1732-\u1734\u1752\u1753\u1772\u1773\u17b7-\u17bd\u17c6\u17c9-\u17d3\u17dd\u180b-\u180d\u18a9\u1920-\u1922\u1927\u1928\u1932\u1939-\u193b\u1a17\u1a18\u1a56\u1a58-\u1a5e\u1a60\u1a62\u1a65-\u1a6c\u1a73-\u1a7c\u1a7f\u1b00-\u1b03\u1b34\u1b36-\u1b3a\u1b3c\u1b42\u1b6b-\u1b73\u1b80\u1b81\u1ba2-\u1ba5\u1ba8\u1ba9\u1c2c-\u1c33\u1c36\u1c37\u1cd0-\u1cd2\u1cd4-\u1ce0\u1ce2-\u1ce8\u1ced\u1dc0-\u1de6\u1dfd-\u1dff\u200c\u200d\u20d0-\u20f0\u2cef-\u2cf1\u2de0-\u2dff\u302a-\u302f\u3099\u309a\ua66f-\ua672\ua67c\ua67d\ua6f0\ua6f1\ua802\ua806\ua80b\ua825\ua826\ua8c4\ua8e0-\ua8f1\ua926-\ua92d\ua947-\ua951\ua980-\ua982\ua9b3\ua9b6-\ua9b9\ua9bc\uaa29-\uaa2e\uaa31\uaa32\uaa35\uaa36\uaa43\uaa4c\uaab0\uaab2-\uaab4\uaab7\uaab8\uaabe\uaabf\uaac1\uabe5\uabe8\uabed\udc00-\udfff\ufb1e\ufe00-\ufe0f\ufe20-\ufe26\uff9e\uff9f]/;

    function isExtendingChar(ch) {
        return ch.charCodeAt(0) >= 768 && extendingChars.test(ch);
    }

    // DOM UTILITIES

    function elt(tag, content, className, style) {
        var e = document.createElement(tag);
        if (className) e.className = className;
        if (style) e.style.cssText = style;
        if (typeof content == "string") e.appendChild(document.createTextNode(content));
        else if (content)
            for (var i = 0; i < content.length; ++i) e.appendChild(content[i]);
        return e;
    }

    var range;
    if (document.createRange) range = function(node, start, end, endNode) {
        var r = document.createRange();
        r.setEnd(endNode || node, end);
        r.setStart(node, start);
        return r;
    };
    else range = function(node, start, end) {
        var r = document.body.createTextRange();
        try {
            r.moveToElementText(node.parentNode);
        } catch (e) {
            return r;
        }
        r.collapse(true);
        r.moveEnd("character", end);
        r.moveStart("character", start);
        return r;
    };

    function removeChildren(e) {
        for (var count = e.childNodes.length; count > 0; --count)
            e.removeChild(e.firstChild);
        return e;
    }

    function removeChildrenAndAdd(parent, e) {
        return removeChildren(parent).appendChild(e);
    }

    var contains = CodeMirror.contains = function(parent, child) {
        if (child.nodeType == 3) // Android browser always returns false when child is a textnode
            child = child.parentNode;
        if (parent.contains)
            return parent.contains(child);
        do {
            if (child.nodeType == 11) child = child.host;
            if (child == parent) return true;
        } while (child = child.parentNode);
    };

    function activeElt() {
        return document.activeElement;
    }
    // Older versions of IE throws unspecified error when touching
    // document.activeElement in some cases (during loading, in iframe)
    if (ie && ie_version < 11) activeElt = function() {
        try {
            return document.activeElement;
        } catch (e) {
            return document.body;
        }
    };

    function classTest(cls) {
        return new RegExp("(^|\\s)" + cls + "(?:$|\\s)\\s*");
    }
    var rmClass = CodeMirror.rmClass = function(node, cls) {
        var current = node.className;
        var match = classTest(cls).exec(current);
        if (match) {
            var after = current.slice(match.index + match[0].length);
            node.className = current.slice(0, match.index) + (after ? match[1] + after : "");
        }
    };
    var addClass = CodeMirror.addClass = function(node, cls) {
        var current = node.className;
        if (!classTest(cls).test(current)) node.className += (current ? " " : "") + cls;
    };

    function joinClasses(a, b) {
        var as = a.split(" ");
        for (var i = 0; i < as.length; i++)
            if (as[i] && !classTest(as[i]).test(b)) b += " " + as[i];
        return b;
    }

    // WINDOW-WIDE EVENTS

    // These must be handled carefully, because naively registering a
    // handler for each editor will cause the editors to never be
    // garbage collected.

    function forEachCodeMirror(f) {
        if (!document.body.getElementsByClassName) return;
        var byClass = document.body.getElementsByClassName("CodeMirror");
        for (var i = 0; i < byClass.length; i++) {
            var cm = byClass[i].CodeMirror;
            if (cm) f(cm);
        }
    }

    var globalsRegistered = false;

    function ensureGlobalHandlers() {
        if (globalsRegistered) return;
        registerGlobalHandlers();
        globalsRegistered = true;
    }

    function registerGlobalHandlers() {
        // When the window resizes, we need to refresh active editors.
        var resizeTimer;
        on(window, "resize", function() {
            if (resizeTimer == null) resizeTimer = setTimeout(function() {
                resizeTimer = null;
                forEachCodeMirror(onResize);
            }, 100);
        });
        // When the window loses focus, we want to show the editor as blurred
        on(window, "blur", function() {
            forEachCodeMirror(onBlur);
        });
    }

    // FEATURE DETECTION

    // Detect drag-and-drop
    var dragAndDrop = function() {
        // There is *some* kind of drag-and-drop support in IE6-8, but I
        // couldn't get it to work yet.
        if (ie && ie_version < 9) return false;
        var div = elt('div');
        return "draggable" in div || "dragDrop" in div;
    }();

    var zwspSupported;

    function zeroWidthElement(measure) {
        if (zwspSupported == null) {
            var test = elt("span", "\u200b");
            removeChildrenAndAdd(measure, elt("span", [test, document.createTextNode("x")]));
            if (measure.firstChild.offsetHeight != 0)
                zwspSupported = test.offsetWidth <= 1 && test.offsetHeight > 2 && !(ie && ie_version < 8);
        }
        var node = zwspSupported ? elt("span", "\u200b") :
            elt("span", "\u00a0", null, "display: inline-block; width: 1px; margin-right: -1px");
        node.setAttribute("cm-text", "");
        return node;
    }

    // Feature-detect IE's crummy client rect reporting for bidi text
    var badBidiRects;

    function hasBadBidiRects(measure) {
        if (badBidiRects != null) return badBidiRects;
        var txt = removeChildrenAndAdd(measure, document.createTextNode("A\u062eA"));
        var r0 = range(txt, 0, 1).getBoundingClientRect();
        if (!r0 || r0.left == r0.right) return false; // Safari returns null in some cases (#2780)
        var r1 = range(txt, 1, 2).getBoundingClientRect();
        return badBidiRects = (r1.right - r0.right < 3);
    }

    // See if "".split is the broken IE version, if so, provide an
    // alternative way to split lines.
    var splitLines = CodeMirror.splitLines = "\n\nb".split(/\n/).length != 3 ? function(string) {
        var pos = 0,
            result = [],
            l = string.length;
        while (pos <= l) {
            var nl = string.indexOf("\n", pos);
            if (nl == -1) nl = string.length;
            var line = string.slice(pos, string.charAt(nl - 1) == "\r" ? nl - 1 : nl);
            var rt = line.indexOf("\r");
            if (rt != -1) {
                result.push(line.slice(0, rt));
                pos += rt + 1;
            } else {
                result.push(line);
                pos = nl + 1;
            }
        }
        return result;
    } : function(string) {
        return string.split(/\r\n?|\n/);
    };

    var hasSelection = window.getSelection ? function(te) {
        try {
            return te.selectionStart != te.selectionEnd;
        } catch (e) {
            return false;
        }
    } : function(te) {
        try {
            var range = te.ownerDocument.selection.createRange();
        } catch (e) {}
        if (!range || range.parentElement() != te) return false;
        return range.compareEndPoints("StartToEnd", range) != 0;
    };

    var hasCopyEvent = (function() {
        var e = elt("div");
        if ("oncopy" in e) return true;
        e.setAttribute("oncopy", "return;");
        return typeof e.oncopy == "function";
    })();

    var badZoomedRects = null;

    function hasBadZoomedRects(measure) {
        if (badZoomedRects != null) return badZoomedRects;
        var node = removeChildrenAndAdd(measure, elt("span", "x"));
        var normal = node.getBoundingClientRect();
        var fromRange = range(node, 0, 1).getBoundingClientRect();
        return badZoomedRects = Math.abs(normal.left - fromRange.left) > 1;
    }

    // KEY NAMES

    var keyNames = {
        3: "Enter",
        8: "Backspace",
        9: "Tab",
        13: "Enter",
        16: "Shift",
        17: "Ctrl",
        18: "Alt",
        19: "Pause",
        20: "CapsLock",
        27: "Esc",
        32: "Space",
        33: "PageUp",
        34: "PageDown",
        35: "End",
        36: "Home",
        37: "Left",
        38: "Up",
        39: "Right",
        40: "Down",
        44: "PrintScrn",
        45: "Insert",
        46: "Delete",
        59: ";",
        61: "=",
        91: "Mod",
        92: "Mod",
        93: "Mod",
        107: "=",
        109: "-",
        127: "Delete",
        173: "-",
        186: ";",
        187: "=",
        188: ",",
        189: "-",
        190: ".",
        191: "/",
        192: "`",
        219: "[",
        220: "\\",
        221: "]",
        222: "'",
        63232: "Up",
        63233: "Down",
        63234: "Left",
        63235: "Right",
        63272: "Delete",
        63273: "Home",
        63275: "End",
        63276: "PageUp",
        63277: "PageDown",
        63302: "Insert"
    };
    CodeMirror.keyNames = keyNames;
    (function() {
        // Number keys
        for (var i = 0; i < 10; i++) keyNames[i + 48] = keyNames[i + 96] = String(i);
        // Alphabetic keys
        for (var i = 65; i <= 90; i++) keyNames[i] = String.fromCharCode(i);
        // Function keys
        for (var i = 1; i <= 12; i++) keyNames[i + 111] = keyNames[i + 63235] = "F" + i;
    })();

    // BIDI HELPERS

    function iterateBidiSections(order, from, to, f) {
        if (!order) return f(from, to, "ltr");
        var found = false;
        for (var i = 0; i < order.length; ++i) {
            var part = order[i];
            if (part.from < to && part.to > from || from == to && part.to == from) {
                f(Math.max(part.from, from), Math.min(part.to, to), part.level == 1 ? "rtl" : "ltr");
                found = true;
            }
        }
        if (!found) f(from, to, "ltr");
    }

    function bidiLeft(part) {
        return part.level % 2 ? part.to : part.from;
    }

    function bidiRight(part) {
        return part.level % 2 ? part.from : part.to;
    }

    function lineLeft(line) {
        var order = getOrder(line);
        return order ? bidiLeft(order[0]) : 0;
    }

    function lineRight(line) {
        var order = getOrder(line);
        if (!order) return line.text.length;
        return bidiRight(lst(order));
    }

    function lineStart(cm, lineN) {
        var line = getLine(cm.doc, lineN);
        var visual = visualLine(line);
        if (visual != line) lineN = lineNo(visual);
        var order = getOrder(visual);
        var ch = !order ? 0 : order[0].level % 2 ? lineRight(visual) : lineLeft(visual);
        return Pos(lineN, ch);
    }

    function lineEnd(cm, lineN) {
        var merged, line = getLine(cm.doc, lineN);
        while (merged = collapsedSpanAtEnd(line)) {
            line = merged.find(1, true).line;
            lineN = null;
        }
        var order = getOrder(line);
        var ch = !order ? line.text.length : order[0].level % 2 ? lineLeft(line) : lineRight(line);
        return Pos(lineN == null ? lineNo(line) : lineN, ch);
    }

    function lineStartSmart(cm, pos) {
        var start = lineStart(cm, pos.line);
        var line = getLine(cm.doc, start.line);
        var order = getOrder(line);
        if (!order || order[0].level == 0) {
            var firstNonWS = Math.max(0, line.text.search(/\S/));
            var inWS = pos.line == start.line && pos.ch <= firstNonWS && pos.ch;
            return Pos(start.line, inWS ? 0 : firstNonWS);
        }
        return start;
    }

    function compareBidiLevel(order, a, b) {
        var linedir = order[0].level;
        if (a == linedir) return true;
        if (b == linedir) return false;
        return a < b;
    }
    var bidiOther;

    function getBidiPartAt(order, pos) {
        bidiOther = null;
        for (var i = 0, found; i < order.length; ++i) {
            var cur = order[i];
            if (cur.from < pos && cur.to > pos) return i;
            if ((cur.from == pos || cur.to == pos)) {
                if (found == null) {
                    found = i;
                } else if (compareBidiLevel(order, cur.level, order[found].level)) {
                    if (cur.from != cur.to) bidiOther = found;
                    return i;
                } else {
                    if (cur.from != cur.to) bidiOther = i;
                    return found;
                }
            }
        }
        return found;
    }

    function moveInLine(line, pos, dir, byUnit) {
        if (!byUnit) return pos + dir;
        do pos += dir;
        while (pos > 0 && isExtendingChar(line.text.charAt(pos)));
        return pos;
    }

    // This is needed in order to move 'visually' through bi-directional
    // text -- i.e., pressing left should make the cursor go left, even
    // when in RTL text. The tricky part is the 'jumps', where RTL and
    // LTR text touch each other. This often requires the cursor offset
    // to move more than one unit, in order to visually move one unit.
    function moveVisually(line, start, dir, byUnit) {
        var bidi = getOrder(line);
        if (!bidi) return moveLogically(line, start, dir, byUnit);
        var pos = getBidiPartAt(bidi, start),
            part = bidi[pos];
        var target = moveInLine(line, start, part.level % 2 ? -dir : dir, byUnit);

        for (;;) {
            if (target > part.from && target < part.to) return target;
            if (target == part.from || target == part.to) {
                if (getBidiPartAt(bidi, target) == pos) return target;
                part = bidi[pos += dir];
                return (dir > 0) == part.level % 2 ? part.to : part.from;
            } else {
                part = bidi[pos += dir];
                if (!part) return null;
                if ((dir > 0) == part.level % 2)
                    target = moveInLine(line, part.to, -1, byUnit);
                else
                    target = moveInLine(line, part.from, 1, byUnit);
            }
        }
    }

    function moveLogically(line, start, dir, byUnit) {
        var target = start + dir;
        if (byUnit)
            while (target > 0 && isExtendingChar(line.text.charAt(target))) target += dir;
        return target < 0 || target > line.text.length ? null : target;
    }

    // Bidirectional ordering algorithm
    // See http://unicode.org/reports/tr9/tr9-13.html for the algorithm
    // that this (partially) implements.

    // One-char codes used for character types:
    // L (L):   Left-to-Right
    // R (R):   Right-to-Left
    // r (AL):  Right-to-Left Arabic
    // 1 (EN):  European Number
    // + (ES):  European Number Separator
    // % (ET):  European Number Terminator
    // n (AN):  Arabic Number
    // , (CS):  Common Number Separator
    // m (NSM): Non-Spacing Mark
    // b (BN):  Boundary Neutral
    // s (B):   Paragraph Separator
    // t (S):   Segment Separator
    // w (WS):  Whitespace
    // N (ON):  Other Neutrals

    // Returns null if characters are ordered as they appear
    // (left-to-right), or an array of sections ({from, to, level}
    // objects) in the order in which they occur visually.
    var bidiOrdering = (function() {
        // Character types for codepoints 0 to 0xff
        var lowTypes = "bbbbbbbbbtstwsbbbbbbbbbbbbbbssstwNN%%%NNNNNN,N,N1111111111NNNNNNNLLLLLLLLLLLLLLLLLLLLLLLLLLNNNNNNLLLLLLLLLLLLLLLLLLLLLLLLLLNNNNbbbbbbsbbbbbbbbbbbbbbbbbbbbbbbbbb,N%%%%NNNNLNNNNN%%11NLNNN1LNNNNNLLLLLLLLLLLLLLLLLLLLLLLNLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLLN";
        // Character types for codepoints 0x600 to 0x6ff
        var arabicTypes = "rrrrrrrrrrrr,rNNmmmmmmrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrmmmmmmmmmmmmmmrrrrrrrnnnnnnnnnn%nnrrrmrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrmmmmmmmmmmmmmmmmmmmNmmmm";

        function charType(code) {
            if (code <= 0xf7) return lowTypes.charAt(code);
            else if (0x590 <= code && code <= 0x5f4) return "R";
            else if (0x600 <= code && code <= 0x6ed) return arabicTypes.charAt(code - 0x600);
            else if (0x6ee <= code && code <= 0x8ac) return "r";
            else if (0x2000 <= code && code <= 0x200b) return "w";
            else if (code == 0x200c) return "b";
            else return "L";
        }

        var bidiRE = /[\u0590-\u05f4\u0600-\u06ff\u0700-\u08ac]/;
        var isNeutral = /[stwN]/,
            isStrong = /[LRr]/,
            countsAsLeft = /[Lb1n]/,
            countsAsNum = /[1n]/;
        // Browsers seem to always treat the boundaries of block elements as being L.
        var outerType = "L";

        function BidiSpan(level, from, to) {
            this.level = level;
            this.from = from;
            this.to = to;
        }

        return function(str) {
            if (!bidiRE.test(str)) return false;
            var len = str.length,
                types = [];
            for (var i = 0, type; i < len; ++i)
                types.push(type = charType(str.charCodeAt(i)));

            // W1. Examine each non-spacing mark (NSM) in the level run, and
            // change the type of the NSM to the type of the previous
            // character. If the NSM is at the start of the level run, it will
            // get the type of sor.
            for (var i = 0, prev = outerType; i < len; ++i) {
                var type = types[i];
                if (type == "m") types[i] = prev;
                else prev = type;
            }

            // W2. Search backwards from each instance of a European number
            // until the first strong type (R, L, AL, or sor) is found. If an
            // AL is found, change the type of the European number to Arabic
            // number.
            // W3. Change all ALs to R.
            for (var i = 0, cur = outerType; i < len; ++i) {
                var type = types[i];
                if (type == "1" && cur == "r") types[i] = "n";
                else if (isStrong.test(type)) {
                    cur = type;
                    if (type == "r") types[i] = "R";
                }
            }

            // W4. A single European separator between two European numbers
            // changes to a European number. A single common separator between
            // two numbers of the same type changes to that type.
            for (var i = 1, prev = types[0]; i < len - 1; ++i) {
                var type = types[i];
                if (type == "+" && prev == "1" && types[i + 1] == "1") types[i] = "1";
                else if (type == "," && prev == types[i + 1] &&
                    (prev == "1" || prev == "n")) types[i] = prev;
                prev = type;
            }

            // W5. A sequence of European terminators adjacent to European
            // numbers changes to all European numbers.
            // W6. Otherwise, separators and terminators change to Other
            // Neutral.
            for (var i = 0; i < len; ++i) {
                var type = types[i];
                if (type == ",") types[i] = "N";
                else if (type == "%") {
                    for (var end = i + 1; end < len && types[end] == "%"; ++end) {}
                    var replace = (i && types[i - 1] == "!") || (end < len && types[end] == "1") ? "1" : "N";
                    for (var j = i; j < end; ++j) types[j] = replace;
                    i = end - 1;
                }
            }

            // W7. Search backwards from each instance of a European number
            // until the first strong type (R, L, or sor) is found. If an L is
            // found, then change the type of the European number to L.
            for (var i = 0, cur = outerType; i < len; ++i) {
                var type = types[i];
                if (cur == "L" && type == "1") types[i] = "L";
                else if (isStrong.test(type)) cur = type;
            }

            // N1. A sequence of neutrals takes the direction of the
            // surrounding strong text if the text on both sides has the same
            // direction. European and Arabic numbers act as if they were R in
            // terms of their influence on neutrals. Start-of-level-run (sor)
            // and end-of-level-run (eor) are used at level run boundaries.
            // N2. Any remaining neutrals take the embedding direction.
            for (var i = 0; i < len; ++i) {
                if (isNeutral.test(types[i])) {
                    for (var end = i + 1; end < len && isNeutral.test(types[end]); ++end) {}
                    var before = (i ? types[i - 1] : outerType) == "L";
                    var after = (end < len ? types[end] : outerType) == "L";
                    var replace = before || after ? "L" : "R";
                    for (var j = i; j < end; ++j) types[j] = replace;
                    i = end - 1;
                }
            }

            // Here we depart from the documented algorithm, in order to avoid
            // building up an actual levels array. Since there are only three
            // levels (0, 1, 2) in an implementation that doesn't take
            // explicit embedding into account, we can build up the order on
            // the fly, without following the level-based algorithm.
            var order = [],
                m;
            for (var i = 0; i < len;) {
                if (countsAsLeft.test(types[i])) {
                    var start = i;
                    for (++i; i < len && countsAsLeft.test(types[i]); ++i) {}
                    order.push(new BidiSpan(0, start, i));
                } else {
                    var pos = i,
                        at = order.length;
                    for (++i; i < len && types[i] != "L"; ++i) {}
                    for (var j = pos; j < i;) {
                        if (countsAsNum.test(types[j])) {
                            if (pos < j) order.splice(at, 0, new BidiSpan(1, pos, j));
                            var nstart = j;
                            for (++j; j < i && countsAsNum.test(types[j]); ++j) {}
                            order.splice(at, 0, new BidiSpan(2, nstart, j));
                            pos = j;
                        } else ++j;
                    }
                    if (pos < i) order.splice(at, 0, new BidiSpan(1, pos, i));
                }
            }
            if (order[0].level == 1 && (m = str.match(/^\s+/))) {
                order[0].from = m[0].length;
                order.unshift(new BidiSpan(0, 0, m[0].length));
            }
            if (lst(order).level == 1 && (m = str.match(/\s+$/))) {
                lst(order).to -= m[0].length;
                order.push(new BidiSpan(0, len - m[0].length, len));
            }
            if (order[0].level == 2)
                order.unshift(new BidiSpan(1, order[0].to, order[0].to));
            if (order[0].level != lst(order).level)
                order.push(new BidiSpan(order[0].level, len, len));

            return order;
        };
    })();

    // THE END

    CodeMirror.version = "5.3.0";

    return CodeMirror;
});

// ##############################################################################
// FILE: Vendor/CodeMirror/codemirrorMarkdown.js
// ##############################################################################

// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

(function(mod) {
    if (typeof exports == "object" && typeof module == "object") // CommonJS
        mod(require("../../lib/codemirror"), require("../xml/xml"), require("../meta"));
    else if (typeof define == "function" && define.amd) // AMD
        define(["../../lib/codemirror", "../xml/xml", "../meta"], mod);
    else // Plain browser env
        mod(CodeMirror);
})(function(CodeMirror) {
    "use strict";

    CodeMirror.defineMode("markdown", function(cmCfg, modeCfg) {

        var htmlFound = CodeMirror.modes.hasOwnProperty("xml");
        var htmlMode = CodeMirror.getMode(cmCfg, htmlFound ? {
            name: "xml",
            htmlMode: true
        } : "text/plain");

        function getMode(name) {
            if (CodeMirror.findModeByName) {
                var found = CodeMirror.findModeByName(name);
                if (found) name = found.mime || found.mimes[0];
            }
            var mode = CodeMirror.getMode(cmCfg, name);
            return mode.name == "null" ? null : mode;
        }

        // Should characters that affect highlighting be highlighted separate?
        // Does not include characters that will be output (such as `1.` and `-` for lists)
        if (modeCfg.highlightFormatting === undefined)
            modeCfg.highlightFormatting = false;

        // Maximum number of nested blockquotes. Set to 0 for infinite nesting.
        // Excess `>` will emit `error` token.
        if (modeCfg.maxBlockquoteDepth === undefined)
            modeCfg.maxBlockquoteDepth = 0;

        // Should underscores in words open/close em/strong?
        if (modeCfg.underscoresBreakWords === undefined)
            modeCfg.underscoresBreakWords = true;

        // Turn on fenced code blocks? ("```" to start/end)
        if (modeCfg.fencedCodeBlocks === undefined) modeCfg.fencedCodeBlocks = false;

        // Turn on task lists? ("- [ ] " and "- [x] ")
        if (modeCfg.taskLists === undefined) modeCfg.taskLists = false;

        // Turn on strikethrough syntax
        if (modeCfg.strikethrough === undefined)
            modeCfg.strikethrough = false;

        var codeDepth = 0;

        var header = 'header',
            code = 'comment',
            quote = 'quote',
            list1 = 'variable-2',
            list2 = 'variable-3',
            list3 = 'keyword',
            hr = 'hr',
            image = 'tag',
            formatting = 'formatting',
            linkinline = 'link',
            linkemail = 'link',
            linktext = 'link',
            linkhref = 'string',
            em = 'em',
            strong = 'strong',
            strikethrough = 'strikethrough';

        var hrRE = /^([*\-=_])(?:\s*\1){2,}\s*$/,
            ulRE = /^[*\-+]\s+/,
            olRE = /^[0-9]+\.\s+/,
            taskListRE = /^\[(x| )\](?=\s)/ // Must follow ulRE or olRE
            ,
            atxHeaderRE = /^#+ ?/,
            setextHeaderRE = /^(?:\={1,}|-{1,})$/,
            textRE = /^[^#!\[\]*_\\<>` "'(~]+/;

        function switchInline(stream, state, f) {
            state.f = state.inline = f;
            return f(stream, state);
        }

        function switchBlock(stream, state, f) {
            state.f = state.block = f;
            return f(stream, state);
        }


        // Blocks

        function blankLine(state) {
            // Reset linkTitle state
            state.linkTitle = false;
            // Reset EM state
            state.em = false;
            // Reset STRONG state
            state.strong = false;
            // Reset strikethrough state
            state.strikethrough = false;
            // Reset state.quote
            state.quote = 0;
            if (!htmlFound && state.f == htmlBlock) {
                state.f = inlineNormal;
                state.block = blockNormal;
            }
            // Reset state.trailingSpace
            state.trailingSpace = 0;
            state.trailingSpaceNewLine = false;
            // Mark this line as blank
            state.thisLineHasContent = false;
            return null;
        }

        function blockNormal(stream, state) {

            var sol = stream.sol();

            var prevLineIsList = state.list !== false;
            if (prevLineIsList) {
                if (state.indentationDiff >= 0) { // Continued list
                    if (state.indentationDiff < 4) { // Only adjust indentation if *not* a code block
                        state.indentation -= state.indentationDiff;
                    }
                    state.list = null;
                } else if (state.indentation > 0) {
                    state.list = null;
                    state.listDepth = Math.floor(state.indentation / 4);
                } else { // No longer a list
                    state.list = false;
                    state.listDepth = 0;
                }
            }

            var match = null;
            if (state.indentationDiff >= 4) {
                state.indentation -= 4;
                stream.skipToEnd();
                return code;
            } else if (stream.eatSpace()) {
                return null;
            } else if (match = stream.match(atxHeaderRE)) {
                state.header = Math.min(6, match[0].indexOf(" ") !== -1 ? match[0].length - 1 : match[0].length);
                if (modeCfg.highlightFormatting) state.formatting = "header";
                state.f = state.inline;
                return getType(state);
            } else if (state.prevLineHasContent && (match = stream.match(setextHeaderRE))) {
                state.header = match[0].charAt(0) == '=' ? 1 : 2;
                if (modeCfg.highlightFormatting) state.formatting = "header";
                state.f = state.inline;
                return getType(state);
            } else if (stream.eat('>')) {
                state.indentation++;
                state.quote = sol ? 1 : state.quote + 1;
                if (modeCfg.highlightFormatting) state.formatting = "quote";
                stream.eatSpace();
                return getType(state);
            } else if (stream.peek() === '[') {
                return switchInline(stream, state, footnoteLink);
            } else if (stream.match(hrRE, true)) {
                return hr;
            } else if ((!state.prevLineHasContent || prevLineIsList) && (stream.match(ulRE, false) || stream.match(olRE, false))) {
                var listType = null;
                if (stream.match(ulRE, true)) {
                    listType = 'ul';
                } else {
                    stream.match(olRE, true);
                    listType = 'ol';
                }
                state.indentation += 4;
                state.list = true;
                state.listDepth++;
                if (modeCfg.taskLists && stream.match(taskListRE, false)) {
                    state.taskList = true;
                }
                state.f = state.inline;
                if (modeCfg.highlightFormatting) state.formatting = ["list", "list-" + listType];
                return getType(state);
            } else if (modeCfg.fencedCodeBlocks && stream.match(/^```[ \t]*([\w+#]*)/, true)) {
                // try switching mode
                state.localMode = getMode(RegExp.$1);
                if (state.localMode) state.localState = state.localMode.startState();
                state.f = state.block = local;
                if (modeCfg.highlightFormatting) state.formatting = "code-block";
                state.code = true;
                return getType(state);
            }

            return switchInline(stream, state, state.inline);
        }

        function htmlBlock(stream, state) {
            var style = htmlMode.token(stream, state.htmlState);
            if ((htmlFound && state.htmlState.tagStart === null && !state.htmlState.context) ||
                (state.md_inside && stream.current().indexOf(">") > -1)) {
                state.f = inlineNormal;
                state.block = blockNormal;
                state.htmlState = null;
            }
            return style;
        }

        function local(stream, state) {
            if (stream.sol() && stream.match("```", false)) {
                state.localMode = state.localState = null;
                state.f = state.block = leavingLocal;
                return null;
            } else if (state.localMode) {
                return state.localMode.token(stream, state.localState);
            } else {
                stream.skipToEnd();
                return code;
            }
        }

        function leavingLocal(stream, state) {
            stream.match("```");
            state.block = blockNormal;
            state.f = inlineNormal;
            if (modeCfg.highlightFormatting) state.formatting = "code-block";
            state.code = true;
            var returnType = getType(state);
            state.code = false;
            return returnType;
        }

        // Inline
        function getType(state) {
            var styles = [];

            if (state.formatting) {
                styles.push(formatting);

                if (typeof state.formatting === "string") state.formatting = [state.formatting];

                for (var i = 0; i < state.formatting.length; i++) {
                    styles.push(formatting + "-" + state.formatting[i]);

                    if (state.formatting[i] === "header") {
                        styles.push(formatting + "-" + state.formatting[i] + "-" + state.header);
                    }

                    // Add `formatting-quote` and `formatting-quote-#` for blockquotes
                    // Add `error` instead if the maximum blockquote nesting depth is passed
                    if (state.formatting[i] === "quote") {
                        if (!modeCfg.maxBlockquoteDepth || modeCfg.maxBlockquoteDepth >= state.quote) {
                            styles.push(formatting + "-" + state.formatting[i] + "-" + state.quote);
                        } else {
                            styles.push("error");
                        }
                    }
                }
            }

            if (state.taskOpen) {
                styles.push("meta");
                return styles.length ? styles.join(' ') : null;
            }
            if (state.taskClosed) {
                styles.push("property");
                return styles.length ? styles.join(' ') : null;
            }

            if (state.linkHref) {
                styles.push(linkhref);
                return styles.length ? styles.join(' ') : null;
            }

            if (state.strong) {
                styles.push(strong);
            }
            if (state.em) {
                styles.push(em);
            }
            if (state.strikethrough) {
                styles.push(strikethrough);
            }

            if (state.linkText) {
                styles.push(linktext);
            }

            if (state.code) {
                styles.push(code);
            }

            if (state.header) {
                styles.push(header);
                styles.push(header + "-" + state.header);
            }

            if (state.quote) {
                styles.push(quote);

                // Add `quote-#` where the maximum for `#` is modeCfg.maxBlockquoteDepth
                if (!modeCfg.maxBlockquoteDepth || modeCfg.maxBlockquoteDepth >= state.quote) {
                    styles.push(quote + "-" + state.quote);
                } else {
                    styles.push(quote + "-" + modeCfg.maxBlockquoteDepth);
                }
            }

            if (state.list !== false) {
                var listMod = (state.listDepth - 1) % 3;
                if (!listMod) {
                    styles.push(list1);
                } else if (listMod === 1) {
                    styles.push(list2);
                } else {
                    styles.push(list3);
                }
            }

            if (state.trailingSpaceNewLine) {
                styles.push("trailing-space-new-line");
            } else if (state.trailingSpace) {
                styles.push("trailing-space-" + (state.trailingSpace % 2 ? "a" : "b"));
            }

            return styles.length ? styles.join(' ') : null;
        }

        function handleText(stream, state) {
            if (stream.match(textRE, true)) {
                return getType(state);
            }
            return undefined;
        }

        function inlineNormal(stream, state) {
            var style = state.text(stream, state);
            if (typeof style !== 'undefined')
                return style;

            if (state.list) { // List marker (*, +, -, 1., etc)
                state.list = null;
                return getType(state);
            }

            if (state.taskList) {
                var taskOpen = stream.match(taskListRE, true)[1] !== "x";
                if (taskOpen) state.taskOpen = true;
                else state.taskClosed = true;
                if (modeCfg.highlightFormatting) state.formatting = "task";
                state.taskList = false;
                return getType(state);
            }

            state.taskOpen = false;
            state.taskClosed = false;

            if (state.header && stream.match(/^#+$/, true)) {
                if (modeCfg.highlightFormatting) state.formatting = "header";
                return getType(state);
            }

            // Get sol() value now, before character is consumed
            var sol = stream.sol();

            var ch = stream.next();

            if (ch === '\\') {
                stream.next();
                if (modeCfg.highlightFormatting) {
                    var type = getType(state);
                    return type ? type + " formatting-escape" : "formatting-escape";
                }
            }

            // Matches link titles present on next line
            if (state.linkTitle) {
                state.linkTitle = false;
                var matchCh = ch;
                if (ch === '(') {
                    matchCh = ')';
                }
                matchCh = (matchCh + '').replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
                var regex = '^\\s*(?:[^' + matchCh + '\\\\]+|\\\\\\\\|\\\\.)' + matchCh;
                if (stream.match(new RegExp(regex), true)) {
                    return linkhref;
                }
            }

            // If this block is changed, it may need to be updated in GFM mode
            if (ch === '`') {
                var previousFormatting = state.formatting;
                if (modeCfg.highlightFormatting) state.formatting = "code";
                var t = getType(state);
                var before = stream.pos;
                stream.eatWhile('`');
                var difference = 1 + stream.pos - before;
                if (!state.code) {
                    codeDepth = difference;
                    state.code = true;
                    return getType(state);
                } else {
                    if (difference === codeDepth) { // Must be exact
                        state.code = false;
                        return t;
                    }
                    state.formatting = previousFormatting;
                    return getType(state);
                }
            } else if (state.code) {
                return getType(state);
            }

            if (ch === '!' && stream.match(/\[[^\]]*\] ?(?:\(|\[)/, false)) {
                stream.match(/\[[^\]]*\]/);
                state.inline = state.f = linkHref;
                return image;
            }

            if (ch === '[' && stream.match(/.*\](\(.*\)| ?\[.*\])/, false)) {
                state.linkText = true;
                if (modeCfg.highlightFormatting) state.formatting = "link";
                return getType(state);
            }

            if (ch === ']' && state.linkText && stream.match(/\(.*\)| ?\[.*\]/, false)) {
                if (modeCfg.highlightFormatting) state.formatting = "link";
                var type = getType(state);
                state.linkText = false;
                state.inline = state.f = linkHref;
                return type;
            }

            if (ch === '<' && stream.match(/^(https?|ftps?):\/\/(?:[^\\>]|\\.)+>/, false)) {
                state.f = state.inline = linkInline;
                if (modeCfg.highlightFormatting) state.formatting = "link";
                var type = getType(state);
                if (type) {
                    type += " ";
                } else {
                    type = "";
                }
                return type + linkinline;
            }

            if (ch === '<' && stream.match(/^[^> \\]+@(?:[^\\>]|\\.)+>/, false)) {
                state.f = state.inline = linkInline;
                if (modeCfg.highlightFormatting) state.formatting = "link";
                var type = getType(state);
                if (type) {
                    type += " ";
                } else {
                    type = "";
                }
                return type + linkemail;
            }

            if (ch === '<' && stream.match(/^\w/, false)) {
                if (stream.string.indexOf(">") != -1) {
                    var atts = stream.string.substring(1, stream.string.indexOf(">"));
                    if (/markdown\s*=\s*('|"){0,1}1('|"){0,1}/.test(atts)) {
                        state.md_inside = true;
                    }
                }
                stream.backUp(1);
                state.htmlState = CodeMirror.startState(htmlMode);
                return switchBlock(stream, state, htmlBlock);
            }

            if (ch === '<' && stream.match(/^\/\w*?>/)) {
                state.md_inside = false;
                return "tag";
            }

            var ignoreUnderscore = false;
            if (!modeCfg.underscoresBreakWords) {
                if (ch === '_' && stream.peek() !== '_' && stream.match(/(\w)/, false)) {
                    var prevPos = stream.pos - 2;
                    if (prevPos >= 0) {
                        var prevCh = stream.string.charAt(prevPos);
                        if (prevCh !== '_' && prevCh.match(/(\w)/, false)) {
                            ignoreUnderscore = true;
                        }
                    }
                }
            }
            if (ch === '*' || (ch === '_' && !ignoreUnderscore)) {
                if (sol && stream.peek() === ' ') {
                    // Do nothing, surrounded by newline and space
                } else if (state.strong === ch && stream.eat(ch)) { // Remove STRONG
                    if (modeCfg.highlightFormatting) state.formatting = "strong";
                    var t = getType(state);
                    state.strong = false;
                    return t;
                } else if (!state.strong && stream.eat(ch)) { // Add STRONG
                    state.strong = ch;
                    if (modeCfg.highlightFormatting) state.formatting = "strong";
                    return getType(state);
                } else if (state.em === ch) { // Remove EM
                    if (modeCfg.highlightFormatting) state.formatting = "em";
                    var t = getType(state);
                    state.em = false;
                    return t;
                } else if (!state.em) { // Add EM
                    state.em = ch;
                    if (modeCfg.highlightFormatting) state.formatting = "em";
                    return getType(state);
                }
            } else if (ch === ' ') {
                if (stream.eat('*') || stream.eat('_')) { // Probably surrounded by spaces
                    if (stream.peek() === ' ') { // Surrounded by spaces, ignore
                        return getType(state);
                    } else { // Not surrounded by spaces, back up pointer
                        stream.backUp(1);
                    }
                }
            }

            if (modeCfg.strikethrough) {
                if (ch === '~' && stream.eatWhile(ch)) {
                    if (state.strikethrough) { // Remove strikethrough
                        if (modeCfg.highlightFormatting) state.formatting = "strikethrough";
                        var t = getType(state);
                        state.strikethrough = false;
                        return t;
                    } else if (stream.match(/^[^\s]/, false)) { // Add strikethrough
                        state.strikethrough = true;
                        if (modeCfg.highlightFormatting) state.formatting = "strikethrough";
                        return getType(state);
                    }
                } else if (ch === ' ') {
                    if (stream.match(/^~~/, true)) { // Probably surrounded by space
                        if (stream.peek() === ' ') { // Surrounded by spaces, ignore
                            return getType(state);
                        } else { // Not surrounded by spaces, back up pointer
                            stream.backUp(2);
                        }
                    }
                }
            }

            if (ch === ' ') {
                if (stream.match(/ +$/, false)) {
                    state.trailingSpace++;
                } else if (state.trailingSpace) {
                    state.trailingSpaceNewLine = true;
                }
            }

            return getType(state);
        }

        function linkInline(stream, state) {
            var ch = stream.next();

            if (ch === ">") {
                state.f = state.inline = inlineNormal;
                if (modeCfg.highlightFormatting) state.formatting = "link";
                var type = getType(state);
                if (type) {
                    type += " ";
                } else {
                    type = "";
                }
                return type + linkinline;
            }

            stream.match(/^[^>]+/, true);

            return linkinline;
        }

        function linkHref(stream, state) {
            // Check if space, and return NULL if so (to avoid marking the space)
            if (stream.eatSpace()) {
                return null;
            }
            var ch = stream.next();
            if (ch === '(' || ch === '[') {
                state.f = state.inline = getLinkHrefInside(ch === "(" ? ")" : "]");
                if (modeCfg.highlightFormatting) state.formatting = "link-string";
                state.linkHref = true;
                return getType(state);
            }
            return 'error';
        }

        function getLinkHrefInside(endChar) {
            return function(stream, state) {
                var ch = stream.next();

                if (ch === endChar) {
                    state.f = state.inline = inlineNormal;
                    if (modeCfg.highlightFormatting) state.formatting = "link-string";
                    var returnState = getType(state);
                    state.linkHref = false;
                    return returnState;
                }

                if (stream.match(inlineRE(endChar), true)) {
                    stream.backUp(1);
                }

                state.linkHref = true;
                return getType(state);
            };
        }

        function footnoteLink(stream, state) {
            if (stream.match(/^[^\]]*\]:/, false)) {
                state.f = footnoteLinkInside;
                stream.next(); // Consume [
                if (modeCfg.highlightFormatting) state.formatting = "link";
                state.linkText = true;
                return getType(state);
            }
            return switchInline(stream, state, inlineNormal);
        }

        function footnoteLinkInside(stream, state) {
            if (stream.match(/^\]:/, true)) {
                state.f = state.inline = footnoteUrl;
                if (modeCfg.highlightFormatting) state.formatting = "link";
                var returnType = getType(state);
                state.linkText = false;
                return returnType;
            }

            stream.match(/^[^\]]+/, true);

            return linktext;
        }

        function footnoteUrl(stream, state) {
            // Check if space, and return NULL if so (to avoid marking the space)
            if (stream.eatSpace()) {
                return null;
            }
            // Match URL
            stream.match(/^[^\s]+/, true);
            // Check for link title
            if (stream.peek() === undefined) { // End of line, set flag to check next line
                state.linkTitle = true;
            } else { // More content on line, check if link title
                stream.match(/^(?:\s+(?:"(?:[^"\\]|\\\\|\\.)+"|'(?:[^'\\]|\\\\|\\.)+'|\((?:[^)\\]|\\\\|\\.)+\)))?/, true);
            }
            state.f = state.inline = inlineNormal;
            return linkhref;
        }

        var savedInlineRE = [];

        function inlineRE(endChar) {
            if (!savedInlineRE[endChar]) {
                // Escape endChar for RegExp (taken from http://stackoverflow.com/a/494122/526741)
                endChar = (endChar + '').replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
                // Match any non-endChar, escaped character, as well as the closing
                // endChar.
                savedInlineRE[endChar] = new RegExp('^(?:[^\\\\]|\\\\.)*?(' + endChar + ')');
            }
            return savedInlineRE[endChar];
        }

        var mode = {
            startState: function() {
                return {
                    f: blockNormal,

                    prevLineHasContent: false,
                    thisLineHasContent: false,

                    block: blockNormal,
                    htmlState: null,
                    indentation: 0,

                    inline: inlineNormal,
                    text: handleText,

                    formatting: false,
                    linkText: false,
                    linkHref: false,
                    linkTitle: false,
                    em: false,
                    strong: false,
                    header: 0,
                    taskList: false,
                    list: false,
                    listDepth: 0,
                    quote: 0,
                    trailingSpace: 0,
                    trailingSpaceNewLine: false,
                    strikethrough: false
                };
            },

            copyState: function(s) {
                return {
                    f: s.f,

                    prevLineHasContent: s.prevLineHasContent,
                    thisLineHasContent: s.thisLineHasContent,

                    block: s.block,
                    htmlState: s.htmlState && CodeMirror.copyState(htmlMode, s.htmlState),
                    indentation: s.indentation,

                    localMode: s.localMode,
                    localState: s.localMode ? CodeMirror.copyState(s.localMode, s.localState) : null,

                    inline: s.inline,
                    text: s.text,
                    formatting: false,
                    linkTitle: s.linkTitle,
                    em: s.em,
                    strong: s.strong,
                    strikethrough: s.strikethrough,
                    header: s.header,
                    taskList: s.taskList,
                    list: s.list,
                    listDepth: s.listDepth,
                    quote: s.quote,
                    trailingSpace: s.trailingSpace,
                    trailingSpaceNewLine: s.trailingSpaceNewLine,
                    md_inside: s.md_inside
                };
            },

            token: function(stream, state) {

                // Reset state.formatting
                state.formatting = false;

                if (stream.sol()) {
                    var forceBlankLine = !!state.header;

                    // Reset state.header
                    state.header = 0;

                    if (stream.match(/^\s*$/, true) || forceBlankLine) {
                        state.prevLineHasContent = false;
                        blankLine(state);
                        return forceBlankLine ? this.token(stream, state) : null;
                    } else {
                        state.prevLineHasContent = state.thisLineHasContent;
                        state.thisLineHasContent = true;
                    }

                    // Reset state.taskList
                    state.taskList = false;

                    // Reset state.code
                    state.code = false;

                    // Reset state.trailingSpace
                    state.trailingSpace = 0;
                    state.trailingSpaceNewLine = false;

                    state.f = state.block;
                    var indentation = stream.match(/^\s*/, true)[0].replace(/\t/g, '    ').length;
                    var difference = Math.floor((indentation - state.indentation) / 4) * 4;
                    if (difference > 4) difference = 4;
                    var adjustedIndentation = state.indentation + difference;
                    state.indentationDiff = adjustedIndentation - state.indentation;
                    state.indentation = adjustedIndentation;
                    if (indentation > 0) return null;
                }
                return state.f(stream, state);
            },

            innerMode: function(state) {
                if (state.block == htmlBlock) return {
                    state: state.htmlState,
                    mode: htmlMode
                };
                if (state.localState) return {
                    state: state.localState,
                    mode: state.localMode
                };
                return {
                    state: state,
                    mode: mode
                };
            },

            blankLine: blankLine,

            getType: getType,

            fold: "markdown"
        };
        return mode;
    }, "xml");

    CodeMirror.defineMIME("text/x-markdown", "markdown");

});

// ##############################################################################
// FILE: Vendor/CodeMirror/codemirrorSimpleScrollbars.js
// ##############################################################################

// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

(function(mod) {
    if (typeof exports == "object" && typeof module == "object") // CommonJS
        mod(require("../../lib/codemirror"));
    else if (typeof define == "function" && define.amd) // AMD
        define(["../../lib/codemirror"], mod);
    else // Plain browser env
        mod(CodeMirror);
})(function(CodeMirror) {
    "use strict";

    function Bar(cls, orientation, scroll) {
        this.orientation = orientation;
        this.scroll = scroll;
        this.screen = this.total = this.size = 1;
        this.pos = 0;

        this.node = document.createElement("div");
        this.node.className = cls + "-" + orientation;
        this.inner = this.node.appendChild(document.createElement("div"));

        var self = this;
        CodeMirror.on(this.inner, "mousedown", function(e) {
            if (e.which != 1) return;
            CodeMirror.e_preventDefault(e);
            var axis = self.orientation == "horizontal" ? "pageX" : "pageY";
            var start = e[axis],
                startpos = self.pos;

            function done() {
                CodeMirror.off(document, "mousemove", move);
                CodeMirror.off(document, "mouseup", done);
            }

            function move(e) {
                if (e.which != 1) return done();
                self.moveTo(startpos + (e[axis] - start) * (self.total / self.size));
            }
            CodeMirror.on(document, "mousemove", move);
            CodeMirror.on(document, "mouseup", done);
        });

        CodeMirror.on(this.node, "click", function(e) {
            CodeMirror.e_preventDefault(e);
            var innerBox = self.inner.getBoundingClientRect(),
                where;
            if (self.orientation == "horizontal")
                where = e.clientX < innerBox.left ? -1 : e.clientX > innerBox.right ? 1 : 0;
            else
                where = e.clientY < innerBox.top ? -1 : e.clientY > innerBox.bottom ? 1 : 0;
            self.moveTo(self.pos + where * self.screen);
        });

        function onWheel(e) {
            var moved = CodeMirror.wheelEventPixels(e)[self.orientation == "horizontal" ? "x" : "y"];
            var oldPos = self.pos;
            self.moveTo(self.pos + moved);
            if (self.pos != oldPos) CodeMirror.e_preventDefault(e);
        }
        CodeMirror.on(this.node, "mousewheel", onWheel);
        CodeMirror.on(this.node, "DOMMouseScroll", onWheel);
    }

    Bar.prototype.moveTo = function(pos, update) {
        if (pos < 0) pos = 0;
        if (pos > this.total - this.screen) pos = this.total - this.screen;
        if (pos == this.pos) return;
        this.pos = pos;
        this.inner.style[this.orientation == "horizontal" ? "left" : "top"] =
            (pos * (this.size / this.total)) + "px";
        if (update !== false) this.scroll(pos, this.orientation);
    };

    var minButtonSize = 10;

    Bar.prototype.update = function(scrollSize, clientSize, barSize) {
        this.screen = clientSize;
        this.total = scrollSize;
        this.size = barSize;

        var buttonSize = this.screen * (this.size / this.total);
        if (buttonSize < minButtonSize) {
            this.size -= minButtonSize - buttonSize;
            buttonSize = minButtonSize;
        }
        this.inner.style[this.orientation == "horizontal" ? "width" : "height"] =
            buttonSize + "px";
        this.inner.style[this.orientation == "horizontal" ? "left" : "top"] =
            this.pos * (this.size / this.total) + "px";
    };

    function SimpleScrollbars(cls, place, scroll) {
        this.addClass = cls;
        this.horiz = new Bar(cls, "horizontal", scroll);
        place(this.horiz.node);
        this.vert = new Bar(cls, "vertical", scroll);
        place(this.vert.node);
        this.width = null;
    }

    SimpleScrollbars.prototype.update = function(measure) {
        if (this.width == null) {
            var style = window.getComputedStyle ? window.getComputedStyle(this.horiz.node) : this.horiz.node.currentStyle;
            if (style) this.width = parseInt(style.height);
        }
        var width = this.width || 0;

        var needsH = measure.scrollWidth > measure.clientWidth + 1;
        var needsV = measure.scrollHeight > measure.clientHeight + 1;
        this.vert.node.style.display = needsV ? "block" : "none";
        this.horiz.node.style.display = needsH ? "block" : "none";

        if (needsV) {
            this.vert.update(measure.scrollHeight, measure.clientHeight,
                measure.viewHeight - (needsH ? width : 0));
            this.vert.node.style.display = "block";
            this.vert.node.style.bottom = needsH ? width + "px" : "0";
        }
        if (needsH) {
            this.horiz.update(measure.scrollWidth, measure.clientWidth,
                measure.viewWidth - (needsV ? width : 0) - measure.barLeft);
            this.horiz.node.style.right = needsV ? width + "px" : "0";
            this.horiz.node.style.left = measure.barLeft + "px";
        }

        return {
            right: needsV ? width : 0,
            bottom: needsH ? width : 0
        };
    };

    SimpleScrollbars.prototype.setScrollTop = function(pos) {
        this.vert.moveTo(pos, false);
    };

    SimpleScrollbars.prototype.setScrollLeft = function(pos) {
        this.horiz.moveTo(pos, false);
    };

    SimpleScrollbars.prototype.clear = function() {
        var parent = this.horiz.node.parentNode;
        parent.removeChild(this.horiz.node);
        parent.removeChild(this.vert.node);
    };

    CodeMirror.scrollbarModel.simple = function(place, scroll) {
        return new SimpleScrollbars("CodeMirror-simplescroll", place, scroll);
    };
    CodeMirror.scrollbarModel.overlay = function(place, scroll) {
        return new SimpleScrollbars("CodeMirror-overlayscroll", place, scroll);
    };
});

// ##############################################################################
// FILE: Vendor/HighlightJS/highlight.js
// ##############################################################################

/*
Copyright (c) 2006, Ivan Sagalaev
All rights reserved.
Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of highlight.js nor the names of its contributors 
      may be used to endorse or promote products derived from this software 
      without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE REGENTS AND CONTRIBUTORS BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

! function(e) {
    "undefined" != typeof exports ? e(exports) : (window.hljs = e({}), "function" == typeof define && define.amd && define("hljs", [], function() {
        return window.hljs
    }))
}(function(e) {
    function n(e) {
        return e.replace(/&/gm, "&amp;").replace(/</gm, "&lt;").replace(/>/gm, "&gt;")
    }

    function t(e) {
        return e.nodeName.toLowerCase()
    }

    function r(e, n) {
        var t = e && e.exec(n);
        return t && 0 == t.index
    }

    function a(e) {
        return /no-?highlight|plain|text/.test(e)
    }

    function i(e) {
        var n, t, r, i = e.className + " ";
        if (i += e.parentNode ? e.parentNode.className : "", t = /\blang(?:uage)?-([\w-]+)\b/.exec(i)) return E(t[1]) ? t[1] : "no-highlight";
        for (i = i.split(/\s+/), n = 0, r = i.length; r > n; n++)
            if (E(i[n]) || a(i[n])) return i[n]
    }

    function o(e, n) {
        var t, r = {};
        for (t in e) r[t] = e[t];
        if (n)
            for (t in n) r[t] = n[t];
        return r
    }

    function u(e) {
        var n = [];
        return function r(e, a) {
            for (var i = e.firstChild; i; i = i.nextSibling) 3 == i.nodeType ? a += i.nodeValue.length : 1 == i.nodeType && (n.push({
                event: "start",
                offset: a,
                node: i
            }), a = r(i, a), t(i).match(/br|hr|img|input/) || n.push({
                event: "stop",
                offset: a,
                node: i
            }));
            return a
        }(e, 0), n
    }

    function c(e, r, a) {
        function i() {
            return e.length && r.length ? e[0].offset != r[0].offset ? e[0].offset < r[0].offset ? e : r : "start" == r[0].event ? e : r : e.length ? e : r
        }

        function o(e) {
            function r(e) {
                return " " + e.nodeName + '="' + n(e.value) + '"'
            }
            f += "<" + t(e) + Array.prototype.map.call(e.attributes, r).join("") + ">"
        }

        function u(e) {
            f += "</" + t(e) + ">"
        }

        function c(e) {
            ("start" == e.event ? o : u)(e.node)
        }
        for (var s = 0, f = "", l = []; e.length || r.length;) {
            var g = i();
            if (f += n(a.substr(s, g[0].offset - s)), s = g[0].offset, g == e) {
                l.reverse().forEach(u);
                do c(g.splice(0, 1)[0]), g = i(); while (g == e && g.length && g[0].offset == s);
                l.reverse().forEach(o)
            } else "start" == g[0].event ? l.push(g[0].node) : l.pop(), c(g.splice(0, 1)[0])
        }
        return f + n(a.substr(s))
    }

    function s(e) {
        function n(e) {
            return e && e.source || e
        }

        function t(t, r) {
            return new RegExp(n(t), "m" + (e.cI ? "i" : "") + (r ? "g" : ""))
        }

        function r(a, i) {
            if (!a.compiled) {
                if (a.compiled = !0, a.k = a.k || a.bK, a.k) {
                    var u = {},
                        c = function(n, t) {
                            e.cI && (t = t.toLowerCase()), t.split(" ").forEach(function(e) {
                                var t = e.split("|");
                                u[t[0]] = [n, t[1] ? Number(t[1]) : 1]
                            })
                        };
                    "string" == typeof a.k ? c("keyword", a.k) : Object.keys(a.k).forEach(function(e) {
                        c(e, a.k[e])
                    }), a.k = u
                }
                a.lR = t(a.l || /\b\w+\b/, !0), i && (a.bK && (a.b = "\\b(" + a.bK.split(" ").join("|") + ")\\b"), a.b || (a.b = /\B|\b/), a.bR = t(a.b), a.e || a.eW || (a.e = /\B|\b/), a.e && (a.eR = t(a.e)), a.tE = n(a.e) || "", a.eW && i.tE && (a.tE += (a.e ? "|" : "") + i.tE)), a.i && (a.iR = t(a.i)), void 0 === a.r && (a.r = 1), a.c || (a.c = []);
                var s = [];
                a.c.forEach(function(e) {
                    e.v ? e.v.forEach(function(n) {
                        s.push(o(e, n))
                    }) : s.push("self" == e ? a : e)
                }), a.c = s, a.c.forEach(function(e) {
                    r(e, a)
                }), a.starts && r(a.starts, i);
                var f = a.c.map(function(e) {
                    return e.bK ? "\\.?(" + e.b + ")\\.?" : e.b
                }).concat([a.tE, a.i]).map(n).filter(Boolean);
                a.t = f.length ? t(f.join("|"), !0) : {
                    exec: function() {
                        return null
                    }
                }
            }
        }
        r(e)
    }

    function f(e, t, a, i) {
        function o(e, n) {
            for (var t = 0; t < n.c.length; t++)
                if (r(n.c[t].bR, e)) return n.c[t]
        }

        function u(e, n) {
            if (r(e.eR, n)) {
                for (; e.endsParent && e.parent;) e = e.parent;
                return e
            }
            return e.eW ? u(e.parent, n) : void 0
        }

        function c(e, n) {
            return !a && r(n.iR, e)
        }

        function g(e, n) {
            var t = N.cI ? n[0].toLowerCase() : n[0];
            return e.k.hasOwnProperty(t) && e.k[t]
        }

        function h(e, n, t, r) {
            var a = r ? "" : w.classPrefix,
                i = '<span class="' + a,
                o = t ? "" : "</span>";
            return i += e + '">', i + n + o
        }

        function p() {
            if (!L.k) return n(B);
            var e = "",
                t = 0;
            L.lR.lastIndex = 0;
            for (var r = L.lR.exec(B); r;) {
                e += n(B.substr(t, r.index - t));
                var a = g(L, r);
                a ? (y += a[1], e += h(a[0], n(r[0]))) : e += n(r[0]), t = L.lR.lastIndex, r = L.lR.exec(B)
            }
            return e + n(B.substr(t))
        }

        function d() {
            if (L.sL && !x[L.sL]) return n(B);
            var e = L.sL ? f(L.sL, B, !0, M[L.sL]) : l(B);
            return L.r > 0 && (y += e.r), "continuous" == L.subLanguageMode && (M[L.sL] = e.top), h(e.language, e.value, !1, !0)
        }

        function b() {
            return void 0 !== L.sL ? d() : p()
        }

        function v(e, t) {
            var r = e.cN ? h(e.cN, "", !0) : "";
            e.rB ? (k += r, B = "") : e.eB ? (k += n(t) + r, B = "") : (k += r, B = t), L = Object.create(e, {
                parent: {
                    value: L
                }
            })
        }

        function m(e, t) {
            if (B += e, void 0 === t) return k += b(), 0;
            var r = o(t, L);
            if (r) return k += b(), v(r, t), r.rB ? 0 : t.length;
            var a = u(L, t);
            if (a) {
                var i = L;
                i.rE || i.eE || (B += t), k += b();
                do L.cN && (k += "</span>"), y += L.r, L = L.parent; while (L != a.parent);
                return i.eE && (k += n(t)), B = "", a.starts && v(a.starts, ""), i.rE ? 0 : t.length
            }
            if (c(t, L)) throw new Error('Illegal lexeme "' + t + '" for mode "' + (L.cN || "<unnamed>") + '"');
            return B += t, t.length || 1
        }
        var N = E(e);
        if (!N) throw new Error('Unknown language: "' + e + '"');
        s(N);
        var R, L = i || N,
            M = {},
            k = "";
        for (R = L; R != N; R = R.parent) R.cN && (k = h(R.cN, "", !0) + k);
        var B = "",
            y = 0;
        try {
            for (var C, j, I = 0;;) {
                if (L.t.lastIndex = I, C = L.t.exec(t), !C) break;
                j = m(t.substr(I, C.index - I), C[0]), I = C.index + j
            }
            for (m(t.substr(I)), R = L; R.parent; R = R.parent) R.cN && (k += "</span>");
            return {
                r: y,
                value: k,
                language: e,
                top: L
            }
        } catch (O) {
            if (-1 != O.message.indexOf("Illegal")) return {
                r: 0,
                value: n(t)
            };
            throw O
        }
    }

    function l(e, t) {
        t = t || w.languages || Object.keys(x);
        var r = {
                r: 0,
                value: n(e)
            },
            a = r;
        return t.forEach(function(n) {
            if (E(n)) {
                var t = f(n, e, !1);
                t.language = n, t.r > a.r && (a = t), t.r > r.r && (a = r, r = t)
            }
        }), a.language && (r.second_best = a), r
    }

    function g(e) {
        return w.tabReplace && (e = e.replace(/^((<[^>]+>|\t)+)/gm, function(e, n) {
            return n.replace(/\t/g, w.tabReplace)
        })), w.useBR && (e = e.replace(/\n/g, "<br>")), e
    }

    function h(e, n, t) {
        var r = n ? R[n] : t,
            a = [e.trim()];
        return e.match(/\bhljs\b/) || a.push("hljs"), -1 === e.indexOf(r) && a.push(r), a.join(" ").trim()
    }

    function p(e) {
        var n = i(e);
        if (!a(n)) {
            var t;
            w.useBR ? (t = document.createElementNS("http://www.w3.org/1999/xhtml", "div"), t.innerHTML = e.innerHTML.replace(/\n/g, "").replace(/<br[ \/]*>/g, "\n")) : t = e;
            var r = t.textContent,
                o = n ? f(n, r, !0) : l(r),
                s = u(t);
            if (s.length) {
                var p = document.createElementNS("http://www.w3.org/1999/xhtml", "div");
                p.innerHTML = o.value, o.value = c(s, u(p), r)
            }
            o.value = g(o.value), e.innerHTML = o.value, e.className = h(e.className, n, o.language), e.result = {
                language: o.language,
                re: o.r
            }, o.second_best && (e.second_best = {
                language: o.second_best.language,
                re: o.second_best.r
            })
        }
    }

    function d(e) {
        w = o(w, e)
    }

    function b() {
        if (!b.called) {
            b.called = !0;
            var e = document.querySelectorAll("pre code");
            Array.prototype.forEach.call(e, p)
        }
    }

    function v() {
        addEventListener("DOMContentLoaded", b, !1), addEventListener("load", b, !1)
    }

    function m(n, t) {
        var r = x[n] = t(e);
        r.aliases && r.aliases.forEach(function(e) {
            R[e] = n
        })
    }

    function N() {
        return Object.keys(x)
    }

    function E(e) {
        return x[e] || x[R[e]]
    }
    var w = {
            classPrefix: "hljs-",
            tabReplace: null,
            useBR: !1,
            languages: void 0
        },
        x = {},
        R = {};
    return e.highlight = f, e.highlightAuto = l, e.fixMarkup = g, e.highlightBlock = p, e.configure = d, e.initHighlighting = b, e.initHighlightingOnLoad = v, e.registerLanguage = m, e.listLanguages = N, e.getLanguage = E, e.inherit = o, e.IR = "[a-zA-Z]\\w*", e.UIR = "[a-zA-Z_]\\w*", e.NR = "\\b\\d+(\\.\\d+)?", e.CNR = "\\b(0[xX][a-fA-F0-9]+|(\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)", e.BNR = "\\b(0b[01]+)", e.RSR = "!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~", e.BE = {
        b: "\\\\[\\s\\S]",
        r: 0
    }, e.ASM = {
        cN: "string",
        b: "'",
        e: "'",
        i: "\\n",
        c: [e.BE]
    }, e.QSM = {
        cN: "string",
        b: '"',
        e: '"',
        i: "\\n",
        c: [e.BE]
    }, e.PWM = {
        b: /\b(a|an|the|are|I|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such)\b/
    }, e.C = function(n, t, r) {
        var a = e.inherit({
            cN: "comment",
            b: n,
            e: t,
            c: []
        }, r || {});
        return a.c.push(e.PWM), a.c.push({
            cN: "doctag",
            bK: "TODO FIXME NOTE BUG XXX",
            r: 0
        }), a
    }, e.CLCM = e.C("//", "$"), e.CBCM = e.C("/\\*", "\\*/"), e.HCM = e.C("#", "$"), e.NM = {
        cN: "number",
        b: e.NR,
        r: 0
    }, e.CNM = {
        cN: "number",
        b: e.CNR,
        r: 0
    }, e.BNM = {
        cN: "number",
        b: e.BNR,
        r: 0
    }, e.CSSNM = {
        cN: "number",
        b: e.NR + "(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",
        r: 0
    }, e.RM = {
        cN: "regexp",
        b: /\//,
        e: /\/[gimuy]*/,
        i: /\n/,
        c: [e.BE, {
            b: /\[/,
            e: /\]/,
            r: 0,
            c: [e.BE]
        }]
    }, e.TM = {
        cN: "title",
        b: e.IR,
        r: 0
    }, e.UTM = {
        cN: "title",
        b: e.UIR,
        r: 0
    }, e
});
hljs.registerLanguage("coffeescript", function(e) {
    var c = {
            keyword: "in if for while finally new do return else break catch instanceof throw try this switch continue typeof delete debugger super then unless until loop of by when and or is isnt not",
            literal: "true false null undefined yes no on off",
            reserved: "case default function var void with const let enum export import native __hasProp __extends __slice __bind __indexOf",
            built_in: "npm require console print module global window document"
        },
        n = "[A-Za-z$_][0-9A-Za-z$_]*",
        t = {
            cN: "subst",
            b: /#\{/,
            e: /}/,
            k: c
        },
        r = [e.BNM, e.inherit(e.CNM, {
            starts: {
                e: "(\\s*/)?",
                r: 0
            }
        }), {
            cN: "string",
            v: [{
                b: /'''/,
                e: /'''/,
                c: [e.BE]
            }, {
                b: /'/,
                e: /'/,
                c: [e.BE]
            }, {
                b: /"""/,
                e: /"""/,
                c: [e.BE, t]
            }, {
                b: /"/,
                e: /"/,
                c: [e.BE, t]
            }]
        }, {
            cN: "regexp",
            v: [{
                b: "///",
                e: "///",
                c: [t, e.HCM]
            }, {
                b: "//[gim]*",
                r: 0
            }, {
                b: /\/(?![ *])(\\\/|.)*?\/[gim]*(?=\W|$)/
            }]
        }, {
            cN: "property",
            b: "@" + n
        }, {
            b: "`",
            e: "`",
            eB: !0,
            eE: !0,
            sL: "javascript"
        }];
    t.c = r;
    var i = e.inherit(e.TM, {
            b: n
        }),
        s = "(\\(.*\\))?\\s*\\B[-=]>",
        o = {
            cN: "params",
            b: "\\([^\\(]",
            rB: !0,
            c: [{
                b: /\(/,
                e: /\)/,
                k: c,
                c: ["self"].concat(r)
            }]
        };
    return {
        aliases: ["coffee", "cson", "iced"],
        k: c,
        i: /\/\*/,
        c: r.concat([e.C("###", "###"), e.HCM, {
            cN: "function",
            b: "^\\s*" + n + "\\s*=\\s*" + s,
            e: "[-=]>",
            rB: !0,
            c: [i, o]
        }, {
            b: /[:\(,=]\s*/,
            r: 0,
            c: [{
                cN: "function",
                b: s,
                e: "[-=]>",
                rB: !0,
                c: [o]
            }]
        }, {
            cN: "class",
            bK: "class",
            e: "$",
            i: /[:="\[\]]/,
            c: [{
                bK: "extends",
                eW: !0,
                i: /[:="\[\]]/,
                c: [i]
            }, i]
        }, {
            cN: "attribute",
            b: n + ":",
            e: ":",
            rB: !0,
            rE: !0,
            r: 0
        }])
    }
});
hljs.registerLanguage("cpp", function(t) {
    var e = {
            cN: "keyword",
            b: "[a-z\\d_]*_t"
        },
        r = {
            keyword: "false int float while private char catch export virtual operator sizeof dynamic_cast|10 typedef const_cast|10 const struct for static_cast|10 union namespace unsigned long volatile static protected bool template mutable if public friend do goto auto void enum else break extern using true class asm case typeid short reinterpret_cast|10 default double register explicit signed typename try this switch continue inline delete alignof constexpr decltype noexcept nullptr static_assert thread_local restrict _Bool complex _Complex _Imaginary atomic_bool atomic_char atomic_schar atomic_uchar atomic_short atomic_ushort atomic_int atomic_uint atomic_long atomic_ulong atomic_llong atomic_ullong",
            built_in: "std string cin cout cerr clog stringstream istringstream ostringstream auto_ptr deque list queue stack vector map set bitset multiset multimap unordered_set unordered_map unordered_multiset unordered_multimap array shared_ptr abort abs acos asin atan2 atan calloc ceil cosh cos exit exp fabs floor fmod fprintf fputs free frexp fscanf isalnum isalpha iscntrl isdigit isgraph islower isprint ispunct isspace isupper isxdigit tolower toupper labs ldexp log10 log malloc memchr memcmp memcpy memset modf pow printf putchar puts scanf sinh sin snprintf sprintf sqrt sscanf strcat strchr strcmp strcpy strcspn strlen strncat strncmp strncpy strpbrk strrchr strspn strstr tanh tan vfprintf vprintf vsprintf"
        };
    return {
        aliases: ["c", "cc", "h", "c++", "h++", "hpp"],
        k: r,
        i: "</",
        c: [e, t.CLCM, t.CBCM, {
            cN: "string",
            v: [t.inherit(t.QSM, {
                b: '((u8?|U)|L)?"'
            }), {
                b: '(u8?|U)?R"',
                e: '"',
                c: [t.BE]
            }, {
                b: "'\\\\?.",
                e: "'",
                i: "."
            }]
        }, {
            cN: "number",
            b: "\\b(\\d+(\\.\\d*)?|\\.\\d+)(u|U|l|L|ul|UL|f|F)"
        }, t.CNM, {
            cN: "preprocessor",
            b: "#",
            e: "$",
            k: "if else elif endif define undef warning error line pragma",
            c: [{
                b: /\\\n/,
                r: 0
            }, {
                b: 'include\\s*[<"]',
                e: '[>"]',
                k: "include",
                i: "\\n"
            }, t.CLCM]
        }, {
            b: "\\b(deque|list|queue|stack|vector|map|set|bitset|multiset|multimap|unordered_map|unordered_set|unordered_multiset|unordered_multimap|array)\\s*<",
            e: ">",
            k: r,
            c: ["self", e]
        }, {
            b: t.IR + "::",
            k: r
        }, {
            bK: "new throw return else",
            r: 0
        }, {
            cN: "function",
            b: "(" + t.IR + "\\s+)+" + t.IR + "\\s*\\(",
            rB: !0,
            e: /[{;=]/,
            eE: !0,
            k: r,
            c: [{
                b: t.IR + "\\s*\\(",
                rB: !0,
                c: [t.TM],
                r: 0
            }, {
                cN: "params",
                b: /\(/,
                e: /\)/,
                k: r,
                r: 0,
                c: [t.CBCM]
            }, t.CLCM, t.CBCM]
        }]
    }
});
hljs.registerLanguage("python", function(e) {
    var r = {
            cN: "prompt",
            b: /^(>>>|\.\.\.) /
        },
        b = {
            cN: "string",
            c: [e.BE],
            v: [{
                b: /(u|b)?r?'''/,
                e: /'''/,
                c: [r],
                r: 10
            }, {
                b: /(u|b)?r?"""/,
                e: /"""/,
                c: [r],
                r: 10
            }, {
                b: /(u|r|ur)'/,
                e: /'/,
                r: 10
            }, {
                b: /(u|r|ur)"/,
                e: /"/,
                r: 10
            }, {
                b: /(b|br)'/,
                e: /'/
            }, {
                b: /(b|br)"/,
                e: /"/
            }, e.ASM, e.QSM]
        },
        l = {
            cN: "number",
            r: 0,
            v: [{
                b: e.BNR + "[lLjJ]?"
            }, {
                b: "\\b(0o[0-7]+)[lLjJ]?"
            }, {
                b: e.CNR + "[lLjJ]?"
            }]
        },
        c = {
            cN: "params",
            b: /\(/,
            e: /\)/,
            c: ["self", r, l, b]
        };
    return {
        aliases: ["py", "gyp"],
        k: {
            keyword: "and elif is global as in if from raise for except finally print import pass return exec else break not with class assert yield try while continue del or def lambda nonlocal|10 None True False",
            built_in: "Ellipsis NotImplemented"
        },
        i: /(<\/|->|\?)/,
        c: [r, l, b, e.HCM, {
            v: [{
                cN: "function",
                bK: "def",
                r: 10
            }, {
                cN: "class",
                bK: "class"
            }],
            e: /:/,
            i: /[${=;\n,]/,
            c: [e.UTM, c]
        }, {
            cN: "decorator",
            b: /@/,
            e: /$/
        }, {
            b: /\b(print|exec)\(/
        }]
    }
});
hljs.registerLanguage("sql", function(e) {
    var t = e.C("--", "$");
    return {
        cI: !0,
        i: /[<>]/,
        c: [{
            cN: "operator",
            bK: "begin end start commit rollback savepoint lock alter create drop rename call delete do handler insert load replace select truncate update set show pragma grant merge describe use explain help declare prepare execute deallocate savepoint release unlock purge reset change stop analyze cache flush optimize repair kill install uninstall checksum restore check backup revoke",
            e: /;/,
            eW: !0,
            k: {
                keyword: "abs absolute acos action add adddate addtime aes_decrypt aes_encrypt after aggregate all allocate alter analyze and any are as asc ascii asin assertion at atan atan2 atn2 authorization authors avg backup before begin benchmark between bin binlog bit_and bit_count bit_length bit_or bit_xor both by cache call cascade cascaded case cast catalog ceil ceiling chain change changed char_length character_length charindex charset check checksum checksum_agg choose close coalesce coercibility collate collation collationproperty column columns columns_updated commit compress concat concat_ws concurrent connect connection connection_id consistent constraint constraints continue contributors conv convert convert_tz corresponding cos cot count count_big crc32 create cross cume_dist curdate current current_date current_time current_timestamp current_user cursor curtime data database databases datalength date_add date_format date_sub dateadd datediff datefromparts datename datepart datetime2fromparts datetimeoffsetfromparts day dayname dayofmonth dayofweek dayofyear deallocate declare decode default deferrable deferred degrees delayed delete des_decrypt des_encrypt des_key_file desc describe descriptor diagnostics difference disconnect distinct distinctrow div do domain double drop dumpfile each else elt enclosed encode encrypt end end-exec engine engines eomonth errors escape escaped event eventdata events except exception exec execute exists exp explain export_set extended external extract fast fetch field fields find_in_set first first_value floor flush for force foreign format found found_rows from from_base64 from_days from_unixtime full function get get_format get_lock getdate getutcdate global go goto grant grants greatest group group_concat grouping grouping_id gtid_subset gtid_subtract handler having help hex high_priority hosts hour ident_current ident_incr ident_seed identified identity if ifnull ignore iif ilike immediate in index indicator inet6_aton inet6_ntoa inet_aton inet_ntoa infile initially inner innodb input insert install instr intersect into is is_free_lock is_ipv4 is_ipv4_compat is_ipv4_mapped is_not is_not_null is_used_lock isdate isnull isolation join key kill language last last_day last_insert_id last_value lcase lead leading least leaves left len lenght level like limit lines ln load load_file local localtime localtimestamp locate lock log log10 log2 logfile logs low_priority lower lpad ltrim make_set makedate maketime master master_pos_wait match matched max md5 medium merge microsecond mid min minute mod mode module month monthname mutex name_const names national natural nchar next no no_write_to_binlog not now nullif nvarchar oct octet_length of old_password on only open optimize option optionally or ord order outer outfile output pad parse partial partition password patindex percent_rank percentile_cont percentile_disc period_add period_diff pi plugin position pow power pragma precision prepare preserve primary prior privileges procedure procedure_analyze processlist profile profiles public publishingservername purge quarter query quick quote quotename radians rand read references regexp relative relaylog release release_lock rename repair repeat replace replicate reset restore restrict return returns reverse revoke right rlike rollback rollup round row row_count rows rpad rtrim savepoint schema scroll sec_to_time second section select serializable server session session_user set sha sha1 sha2 share show sign sin size slave sleep smalldatetimefromparts snapshot some soname soundex sounds_like space sql sql_big_result sql_buffer_result sql_cache sql_calc_found_rows sql_no_cache sql_small_result sql_variant_property sqlstate sqrt square start starting status std stddev stddev_pop stddev_samp stdev stdevp stop str str_to_date straight_join strcmp string stuff subdate substr substring subtime subtring_index sum switchoffset sysdate sysdatetime sysdatetimeoffset system_user sysutcdatetime table tables tablespace tan temporary terminated tertiary_weights then time time_format time_to_sec timediff timefromparts timestamp timestampadd timestampdiff timezone_hour timezone_minute to to_base64 to_days to_seconds todatetimeoffset trailing transaction translation trigger trigger_nestlevel triggers trim truncate try_cast try_convert try_parse ucase uncompress uncompressed_length unhex unicode uninstall union unique unix_timestamp unknown unlock update upgrade upped upper usage use user user_resources using utc_date utc_time utc_timestamp uuid uuid_short validate_password_strength value values var var_pop var_samp variables variance varp version view warnings week weekday weekofyear weight_string when whenever where with work write xml xor year yearweek zon",
                literal: "true false null",
                built_in: "array bigint binary bit blob boolean char character date dec decimal float int integer interval number numeric real serial smallint varchar varying int8 serial8 text"
            },
            c: [{
                cN: "string",
                b: "'",
                e: "'",
                c: [e.BE, {
                    b: "''"
                }]
            }, {
                cN: "string",
                b: '"',
                e: '"',
                c: [e.BE, {
                    b: '""'
                }]
            }, {
                cN: "string",
                b: "`",
                e: "`",
                c: [e.BE]
            }, e.CNM, e.CBCM, t]
        }, e.CBCM, t]
    }
});
hljs.registerLanguage("java", function(e) {
    var a = e.UIR + "(<" + e.UIR + ">)?",
        t = "false synchronized int abstract float private char boolean static null if const for true while long strictfp finally protected import native final void enum else break transient catch instanceof byte super volatile case assert short package default double public try this switch continue throws protected public private",
        c = "\\b(0[bB]([01]+[01_]+[01]+|[01]+)|0[xX]([a-fA-F0-9]+[a-fA-F0-9_]+[a-fA-F0-9]+|[a-fA-F0-9]+)|(([\\d]+[\\d_]+[\\d]+|[\\d]+)(\\.([\\d]+[\\d_]+[\\d]+|[\\d]+))?|\\.([\\d]+[\\d_]+[\\d]+|[\\d]+))([eE][-+]?\\d+)?)[lLfF]?",
        r = {
            cN: "number",
            b: c,
            r: 0
        };
    return {
        aliases: ["jsp"],
        k: t,
        i: /<\//,
        c: [e.C("/\\*\\*", "\\*/", {
            r: 0,
            c: [{
                cN: "doctag",
                b: "@[A-Za-z]+"
            }]
        }), e.CLCM, e.CBCM, e.ASM, e.QSM, {
            cN: "class",
            bK: "class interface",
            e: /[{;=]/,
            eE: !0,
            k: "class interface",
            i: /[:"\[\]]/,
            c: [{
                bK: "extends implements"
            }, e.UTM]
        }, {
            bK: "new throw return else",
            r: 0
        }, {
            cN: "function",
            b: "(" + a + "\\s+)+" + e.UIR + "\\s*\\(",
            rB: !0,
            e: /[{;=]/,
            eE: !0,
            k: t,
            c: [{
                b: e.UIR + "\\s*\\(",
                rB: !0,
                r: 0,
                c: [e.UTM]
            }, {
                cN: "params",
                b: /\(/,
                e: /\)/,
                k: t,
                r: 0,
                c: [e.ASM, e.QSM, e.CNM, e.CBCM]
            }, e.CLCM, e.CBCM]
        }, r, {
            cN: "annotation",
            b: "@[A-Za-z]+"
        }]
    }
});
hljs.registerLanguage("ruby", function(e) {
    var c = "[a-zA-Z_]\\w*[!?=]?|[-+~]\\@|<<|>>|=~|===?|<=>|[<>]=?|\\*\\*|[-/+%^&*~`|]|\\[\\]=?",
        r = "and false then defined module in return redo if BEGIN retry end for true self when next until do begin unless END rescue nil else break undef not super class case require yield alias while ensure elsif or include attr_reader attr_writer attr_accessor",
        b = {
            cN: "doctag",
            b: "@[A-Za-z]+"
        },
        a = {
            cN: "value",
            b: "#<",
            e: ">"
        },
        n = [e.C("#", "$", {
            c: [b]
        }), e.C("^\\=begin", "^\\=end", {
            c: [b],
            r: 10
        }), e.C("^__END__", "\\n$")],
        s = {
            cN: "subst",
            b: "#\\{",
            e: "}",
            k: r
        },
        t = {
            cN: "string",
            c: [e.BE, s],
            v: [{
                b: /'/,
                e: /'/
            }, {
                b: /"/,
                e: /"/
            }, {
                b: /`/,
                e: /`/
            }, {
                b: "%[qQwWx]?\\(",
                e: "\\)"
            }, {
                b: "%[qQwWx]?\\[",
                e: "\\]"
            }, {
                b: "%[qQwWx]?{",
                e: "}"
            }, {
                b: "%[qQwWx]?<",
                e: ">"
            }, {
                b: "%[qQwWx]?/",
                e: "/"
            }, {
                b: "%[qQwWx]?%",
                e: "%"
            }, {
                b: "%[qQwWx]?-",
                e: "-"
            }, {
                b: "%[qQwWx]?\\|",
                e: "\\|"
            }, {
                b: /\B\?(\\\d{1,3}|\\x[A-Fa-f0-9]{1,2}|\\u[A-Fa-f0-9]{4}|\\?\S)\b/
            }]
        },
        i = {
            cN: "params",
            b: "\\(",
            e: "\\)",
            k: r
        },
        d = [t, a, {
            cN: "class",
            bK: "class module",
            e: "$|;",
            i: /=/,
            c: [e.inherit(e.TM, {
                b: "[A-Za-z_]\\w*(::\\w+)*(\\?|\\!)?"
            }), {
                cN: "inheritance",
                b: "<\\s*",
                c: [{
                    cN: "parent",
                    b: "(" + e.IR + "::)?" + e.IR
                }]
            }].concat(n)
        }, {
            cN: "function",
            bK: "def",
            e: " |$|;",
            r: 0,
            c: [e.inherit(e.TM, {
                b: c
            }), i].concat(n)
        }, {
            cN: "constant",
            b: "(::)?(\\b[A-Z]\\w*(::)?)+",
            r: 0
        }, {
            cN: "symbol",
            b: e.UIR + "(\\!|\\?)?:",
            r: 0
        }, {
            cN: "symbol",
            b: ":",
            c: [t, {
                b: c
            }],
            r: 0
        }, {
            cN: "number",
            b: "(\\b0[0-7_]+)|(\\b0x[0-9a-fA-F_]+)|(\\b[1-9][0-9_]*(\\.[0-9_]+)?)|[0_]\\b",
            r: 0
        }, {
            cN: "variable",
            b: "(\\$\\W)|((\\$|\\@\\@?)(\\w+))"
        }, {
            b: "(" + e.RSR + ")\\s*",
            c: [a, {
                cN: "regexp",
                c: [e.BE, s],
                i: /\n/,
                v: [{
                    b: "/",
                    e: "/[a-z]*"
                }, {
                    b: "%r{",
                    e: "}[a-z]*"
                }, {
                    b: "%r\\(",
                    e: "\\)[a-z]*"
                }, {
                    b: "%r!",
                    e: "![a-z]*"
                }, {
                    b: "%r\\[",
                    e: "\\][a-z]*"
                }]
            }].concat(n),
            r: 0
        }].concat(n);
    s.c = d, i.c = d;
    var o = "[>?]>",
        l = "[\\w#]+\\(\\w+\\):\\d+:\\d+>",
        u = "(\\w+-)?\\d+\\.\\d+\\.\\d(p\\d+)?[^>]+>",
        N = [{
            b: /^\s*=>/,
            cN: "status",
            starts: {
                e: "$",
                c: d
            }
        }, {
            cN: "prompt",
            b: "^(" + o + "|" + l + "|" + u + ")",
            starts: {
                e: "$",
                c: d
            }
        }];
    return {
        aliases: ["rb", "gemspec", "podspec", "thor", "irb"],
        k: r,
        c: n.concat(N).concat(d)
    }
});
hljs.registerLanguage("livescript", function(e) {
    var t = {
            keyword: "in if for while finally new do return else break catch instanceof throw try this switch continue typeof delete debugger case default function var with then unless until loop of by when and or is isnt not it that otherwise from to til fallthrough super case default function var void const let enum export import native __hasProp __extends __slice __bind __indexOf",
            literal: "true false null undefined yes no on off it that void",
            built_in: "npm require console print module global window document"
        },
        s = "[A-Za-z$_](?:-[0-9A-Za-z$_]|[0-9A-Za-z$_])*",
        i = e.inherit(e.TM, {
            b: s
        }),
        n = {
            cN: "subst",
            b: /#\{/,
            e: /}/,
            k: t
        },
        r = {
            cN: "subst",
            b: /#[A-Za-z$_]/,
            e: /(?:\-[0-9A-Za-z$_]|[0-9A-Za-z$_])*/,
            k: t
        },
        c = [e.BNM, {
            cN: "number",
            b: "(\\b0[xX][a-fA-F0-9_]+)|(\\b\\d(\\d|_\\d)*(\\.(\\d(\\d|_\\d)*)?)?(_*[eE]([-+]\\d(_\\d|\\d)*)?)?[_a-z]*)",
            r: 0,
            starts: {
                e: "(\\s*/)?",
                r: 0
            }
        }, {
            cN: "string",
            v: [{
                b: /'''/,
                e: /'''/,
                c: [e.BE]
            }, {
                b: /'/,
                e: /'/,
                c: [e.BE]
            }, {
                b: /"""/,
                e: /"""/,
                c: [e.BE, n, r]
            }, {
                b: /"/,
                e: /"/,
                c: [e.BE, n, r]
            }, {
                b: /\\/,
                e: /(\s|$)/,
                eE: !0
            }]
        }, {
            cN: "pi",
            v: [{
                b: "//",
                e: "//[gim]*",
                c: [n, e.HCM]
            }, {
                b: /\/(?![ *])(\\\/|.)*?\/[gim]*(?=\W|$)/
            }]
        }, {
            cN: "property",
            b: "@" + s
        }, {
            b: "``",
            e: "``",
            eB: !0,
            eE: !0,
            sL: "javascript"
        }];
    n.c = c;
    var a = {
        cN: "params",
        b: "\\(",
        rB: !0,
        c: [{
            b: /\(/,
            e: /\)/,
            k: t,
            c: ["self"].concat(c)
        }]
    };
    return {
        aliases: ["ls"],
        k: t,
        i: /\/\*/,
        c: c.concat([e.C("\\/\\*", "\\*\\/"), e.HCM, {
            cN: "function",
            c: [i, a],
            rB: !0,
            v: [{
                b: "(" + s + "\\s*(?:=|:=)\\s*)?(\\(.*\\))?\\s*\\B\\->\\*?",
                e: "\\->\\*?"
            }, {
                b: "(" + s + "\\s*(?:=|:=)\\s*)?!?(\\(.*\\))?\\s*\\B[-~]{1,2}>\\*?",
                e: "[-~]{1,2}>\\*?"
            }, {
                b: "(" + s + "\\s*(?:=|:=)\\s*)?(\\(.*\\))?\\s*\\B!?[-~]{1,2}>\\*?",
                e: "!?[-~]{1,2}>\\*?"
            }]
        }, {
            cN: "class",
            bK: "class",
            e: "$",
            i: /[:="\[\]]/,
            c: [{
                bK: "extends",
                eW: !0,
                i: /[:="\[\]]/,
                c: [i]
            }, i]
        }, {
            cN: "attribute",
            b: s + ":",
            e: ":",
            rB: !0,
            rE: !0,
            r: 0
        }])
    }
});
hljs.registerLanguage("objectivec", function(e) {
    var t = {
            cN: "built_in",
            b: "(AV|CA|CF|CG|CI|MK|MP|NS|UI)\\w+"
        },
        i = {
            keyword: "int float while char export sizeof typedef const struct for union unsigned long volatile static bool mutable if do return goto void enum else break extern asm case short default double register explicit signed typename this switch continue wchar_t inline readonly assign readwrite self @synchronized id typeof nonatomic super unichar IBOutlet IBAction strong weak copy in out inout bycopy byref oneway __strong __weak __block __autoreleasing @private @protected @public @try @property @end @throw @catch @finally @autoreleasepool @synthesize @dynamic @selector @optional @required",
            literal: "false true FALSE TRUE nil YES NO NULL",
            built_in: "BOOL dispatch_once_t dispatch_queue_t dispatch_sync dispatch_async dispatch_once"
        },
        o = /[a-zA-Z@][a-zA-Z0-9_]*/,
        n = "@interface @class @protocol @implementation";
    return {
        aliases: ["mm", "objc", "obj-c"],
        k: i,
        l: o,
        i: "</",
        c: [t, e.CLCM, e.CBCM, e.CNM, e.QSM, {
            cN: "string",
            v: [{
                b: '@"',
                e: '"',
                i: "\\n",
                c: [e.BE]
            }, {
                b: "'",
                e: "[^\\\\]'",
                i: "[^\\\\][^']"
            }]
        }, {
            cN: "preprocessor",
            b: "#",
            e: "$",
            c: [{
                cN: "title",
                v: [{
                    b: '"',
                    e: '"'
                }, {
                    b: "<",
                    e: ">"
                }]
            }]
        }, {
            cN: "class",
            b: "(" + n.split(" ").join("|") + ")\\b",
            e: "({|$)",
            eE: !0,
            k: n,
            l: o,
            c: [e.UTM]
        }, {
            cN: "variable",
            b: "\\." + e.UIR,
            r: 0
        }]
    }
});
hljs.registerLanguage("javascript", function(e) {
    return {
        aliases: ["js"],
        k: {
            keyword: "in of if for while finally var new function do return void else break catch instanceof with throw case default try this switch continue typeof delete let yield const export super debugger as async await",
            literal: "true false null undefined NaN Infinity",
            built_in: "eval isFinite isNaN parseFloat parseInt decodeURI decodeURIComponent encodeURI encodeURIComponent escape unescape Object Function Boolean Error EvalError InternalError RangeError ReferenceError StopIteration SyntaxError TypeError URIError Number Math Date String RegExp Array Float32Array Float64Array Int16Array Int32Array Int8Array Uint16Array Uint32Array Uint8Array Uint8ClampedArray ArrayBuffer DataView JSON Intl arguments require module console window document Symbol Set Map WeakSet WeakMap Proxy Reflect Promise"
        },
        c: [{
            cN: "pi",
            r: 10,
            b: /^\s*['"]use (strict|asm)['"]/
        }, e.ASM, e.QSM, {
            cN: "string",
            b: "`",
            e: "`",
            c: [e.BE, {
                cN: "subst",
                b: "\\$\\{",
                e: "\\}"
            }]
        }, e.CLCM, e.CBCM, {
            cN: "number",
            v: [{
                b: "\\b(0[bB][01]+)"
            }, {
                b: "\\b(0[oO][0-7]+)"
            }, {
                b: e.CNR
            }],
            r: 0
        }, {
            b: "(" + e.RSR + "|\\b(case|return|throw)\\b)\\s*",
            k: "return throw case",
            c: [e.CLCM, e.CBCM, e.RM, {
                b: /</,
                e: />\s*[);\]]/,
                r: 0,
                sL: "xml"
            }],
            r: 0
        }, {
            cN: "function",
            bK: "function",
            e: /\{/,
            eE: !0,
            c: [e.inherit(e.TM, {
                b: /[A-Za-z$_][0-9A-Za-z$_]*/
            }), {
                cN: "params",
                b: /\(/,
                e: /\)/,
                eB: !0,
                eE: !0,
                c: [e.CLCM, e.CBCM],
                i: /["'\(]/
            }],
            i: /\[|%/
        }, {
            b: /\$[(.]/
        }, {
            b: "\\." + e.IR,
            r: 0
        }, {
            bK: "import",
            e: "[;$]",
            k: "import from as",
            c: [e.ASM, e.QSM]
        }, {
            cN: "class",
            bK: "class",
            e: /[{;=]/,
            eE: !0,
            i: /[:"\[\]]/,
            c: [{
                bK: "extends"
            }, e.UTM]
        }]
    }
});
hljs.registerLanguage("nginx", function(e) {
    var r = {
            cN: "variable",
            v: [{
                b: /\$\d+/
            }, {
                b: /\$\{/,
                e: /}/
            }, {
                b: "[\\$\\@]" + e.UIR
            }]
        },
        b = {
            eW: !0,
            l: "[a-z/_]+",
            k: {
                built_in: "on off yes no true false none blocked debug info notice warn error crit select break last permanent redirect kqueue rtsig epoll poll /dev/poll"
            },
            r: 0,
            i: "=>",
            c: [e.HCM, {
                cN: "string",
                c: [e.BE, r],
                v: [{
                    b: /"/,
                    e: /"/
                }, {
                    b: /'/,
                    e: /'/
                }]
            }, {
                cN: "url",
                b: "([a-z]+):/",
                e: "\\s",
                eW: !0,
                eE: !0,
                c: [r]
            }, {
                cN: "regexp",
                c: [e.BE, r],
                v: [{
                    b: "\\s\\^",
                    e: "\\s|{|;",
                    rE: !0
                }, {
                    b: "~\\*?\\s+",
                    e: "\\s|{|;",
                    rE: !0
                }, {
                    b: "\\*(\\.[a-z\\-]+)+"
                }, {
                    b: "([a-z\\-]+\\.)+\\*"
                }]
            }, {
                cN: "number",
                b: "\\b\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}(:\\d{1,5})?\\b"
            }, {
                cN: "number",
                b: "\\b\\d+[kKmMgGdshdwy]*\\b",
                r: 0
            }, r]
        };
    return {
        aliases: ["nginxconf"],
        c: [e.HCM, {
            b: e.UIR + "\\s",
            e: ";|{",
            rB: !0,
            c: [{
                cN: "title",
                b: e.UIR,
                starts: b
            }],
            r: 0
        }],
        i: "[^\\s\\}]"
    }
});
hljs.registerLanguage("markdown", function(e) {
    return {
        aliases: ["md", "mkdown", "mkd"],
        c: [{
            cN: "header",
            v: [{
                b: "^#{1,6}",
                e: "$"
            }, {
                b: "^.+?\\n[=-]{2,}$"
            }]
        }, {
            b: "<",
            e: ">",
            sL: "xml",
            r: 0
        }, {
            cN: "bullet",
            b: "^([*+-]|(\\d+\\.))\\s+"
        }, {
            cN: "strong",
            b: "[*_]{2}.+?[*_]{2}"
        }, {
            cN: "emphasis",
            v: [{
                b: "\\*.+?\\*"
            }, {
                b: "_.+?_",
                r: 0
            }]
        }, {
            cN: "blockquote",
            b: "^>\\s+",
            e: "$"
        }, {
            cN: "code",
            v: [{
                b: "`.+?`"
            }, {
                b: "^( {4}| )",
                e: "$",
                r: 0
            }]
        }, {
            cN: "horizontal_rule",
            b: "^[-\\*]{3,}",
            e: "$"
        }, {
            b: "\\[.+?\\][\\(\\[].*?[\\)\\]]",
            rB: !0,
            c: [{
                cN: "link_label",
                b: "\\[",
                e: "\\]",
                eB: !0,
                rE: !0,
                r: 0
            }, {
                cN: "link_url",
                b: "\\]\\(",
                e: "\\)",
                eB: !0,
                eE: !0
            }, {
                cN: "link_reference",
                b: "\\]\\[",
                e: "\\]",
                eB: !0,
                eE: !0
            }],
            r: 10
        }, {
            b: "^\\[.+\\]:",
            rB: !0,
            c: [{
                cN: "link_reference",
                b: "\\[",
                e: "\\]:",
                eB: !0,
                eE: !0,
                starts: {
                    cN: "link_url",
                    e: "$"
                }
            }]
        }]
    }
});
hljs.registerLanguage("json", function(e) {
    var t = {
            literal: "true false null"
        },
        i = [e.QSM, e.CNM],
        l = {
            cN: "value",
            e: ",",
            eW: !0,
            eE: !0,
            c: i,
            k: t
        },
        c = {
            b: "{",
            e: "}",
            c: [{
                cN: "attribute",
                b: '\\s*"',
                e: '"\\s*:\\s*',
                eB: !0,
                eE: !0,
                c: [e.BE],
                i: "\\n",
                starts: l
            }],
            i: "\\S"
        },
        n = {
            b: "\\[",
            e: "\\]",
            c: [e.inherit(l, {
                cN: null
            })],
            i: "\\S"
        };
    return i.splice(i.length, 0, c, n), {
        c: i,
        k: t,
        i: "\\S"
    }
});
hljs.registerLanguage("php", function(e) {
    var c = {
            cN: "variable",
            b: "\\$+[a-zA-Z_-ÿ][a-zA-Z0-9_-ÿ]*"
        },
        a = {
            cN: "preprocessor",
            b: /<\?(php)?|\?>/
        },
        i = {
            cN: "string",
            c: [e.BE, a],
            v: [{
                b: 'b"',
                e: '"'
            }, {
                b: "b'",
                e: "'"
            }, e.inherit(e.ASM, {
                i: null
            }), e.inherit(e.QSM, {
                i: null
            })]
        },
        n = {
            v: [e.BNM, e.CNM]
        };
    return {
        aliases: ["php3", "php4", "php5", "php6"],
        cI: !0,
        k: "and include_once list abstract global private echo interface as static endswitch array null if endwhile or const for endforeach self var while isset public protected exit foreach throw elseif include __FILE__ empty require_once do xor return parent clone use __CLASS__ __LINE__ else break print eval new catch __METHOD__ case exception default die require __FUNCTION__ enddeclare final try switch continue endfor endif declare unset true false trait goto instanceof insteadof __DIR__ __NAMESPACE__ yield finally",
        c: [e.CLCM, e.HCM, e.C("/\\*", "\\*/", {
            c: [{
                cN: "doctag",
                b: "@[A-Za-z]+"
            }, a]
        }), e.C("__halt_compiler.+?;", !1, {
            eW: !0,
            k: "__halt_compiler",
            l: e.UIR
        }), {
            cN: "string",
            b: "<<<['\"]?\\w+['\"]?$",
            e: "^\\w+;",
            c: [e.BE]
        }, a, c, {
            b: /(::|->)+[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/
        }, {
            cN: "function",
            bK: "function",
            e: /[;{]/,
            eE: !0,
            i: "\\$|\\[|%",
            c: [e.UTM, {
                cN: "params",
                b: "\\(",
                e: "\\)",
                c: ["self", c, e.CBCM, i, n]
            }]
        }, {
            cN: "class",
            bK: "class interface",
            e: "{",
            eE: !0,
            i: /[:\(\$"]/,
            c: [{
                bK: "extends implements"
            }, e.UTM]
        }, {
            bK: "namespace",
            e: ";",
            i: /[\.']/,
            c: [e.UTM]
        }, {
            bK: "use",
            e: ";",
            c: [e.UTM]
        }, {
            b: "=>"
        }, i, n]
    }
});
hljs.registerLanguage("diff", function(e) {
    return {
        aliases: ["patch"],
        c: [{
            cN: "chunk",
            r: 10,
            v: [{
                b: /^@@ +\-\d+,\d+ +\+\d+,\d+ +@@$/
            }, {
                b: /^\*\*\* +\d+,\d+ +\*\*\*\*$/
            }, {
                b: /^\-\-\- +\d+,\d+ +\-\-\-\-$/
            }]
        }, {
            cN: "header",
            v: [{
                b: /Index: /,
                e: /$/
            }, {
                b: /=====/,
                e: /=====$/
            }, {
                b: /^\-\-\-/,
                e: /$/
            }, {
                b: /^\*{3} /,
                e: /$/
            }, {
                b: /^\+\+\+/,
                e: /$/
            }, {
                b: /\*{5}/,
                e: /\*{5}$/
            }]
        }, {
            cN: "addition",
            b: "^\\+",
            e: "$"
        }, {
            cN: "deletion",
            b: "^\\-",
            e: "$"
        }, {
            cN: "change",
            b: "^\\!",
            e: "$"
        }]
    }
});
hljs.registerLanguage("apache", function(e) {
    var r = {
        cN: "number",
        b: "[\\$%]\\d+"
    };
    return {
        aliases: ["apacheconf"],
        cI: !0,
        c: [e.HCM, {
            cN: "tag",
            b: "</?",
            e: ">"
        }, {
            cN: "keyword",
            b: /\w+/,
            r: 0,
            k: {
                common: "order deny allow setenv rewriterule rewriteengine rewritecond documentroot sethandler errordocument loadmodule options header listen serverroot servername"
            },
            starts: {
                e: /$/,
                r: 0,
                k: {
                    literal: "on off all"
                },
                c: [{
                    cN: "sqbracket",
                    b: "\\s\\[",
                    e: "\\]$"
                }, {
                    cN: "cbracket",
                    b: "[\\$%]\\{",
                    e: "\\}",
                    c: ["self", r]
                }, r, e.QSM]
            }
        }],
        i: /\S/
    }
});
hljs.registerLanguage("http", function(t) {
    return {
        aliases: ["https"],
        i: "\\S",
        c: [{
            cN: "status",
            b: "^HTTP/[0-9\\.]+",
            e: "$",
            c: [{
                cN: "number",
                b: "\\b\\d{3}\\b"
            }]
        }, {
            cN: "request",
            b: "^[A-Z]+ (.*?) HTTP/[0-9\\.]+$",
            rB: !0,
            e: "$",
            c: [{
                cN: "string",
                b: " ",
                e: " ",
                eB: !0,
                eE: !0
            }]
        }, {
            cN: "attribute",
            b: "^\\w",
            e: ": ",
            eE: !0,
            i: "\\n|\\s|=",
            starts: {
                cN: "string",
                e: "$"
            }
        }, {
            b: "\\n\\n",
            starts: {
                sL: "",
                eW: !0
            }
        }]
    }
});
hljs.registerLanguage("less", function(e) {
    var r = "[\\w-]+",
        t = "(" + r + "|@{" + r + "})",
        a = [],
        c = [],
        n = function(e) {
            return {
                cN: "string",
                b: "~?" + e + ".*?" + e
            }
        },
        i = function(e, r, t) {
            return {
                cN: e,
                b: r,
                r: t
            }
        },
        s = function(r, t, a) {
            return e.inherit({
                cN: r,
                b: t + "\\(",
                e: "\\(",
                rB: !0,
                eE: !0,
                r: 0
            }, a)
        },
        b = {
            b: "\\(",
            e: "\\)",
            c: c,
            r: 0
        };
    c.push(e.CLCM, e.CBCM, n("'"), n('"'), e.CSSNM, i("hexcolor", "#[0-9A-Fa-f]+\\b"), s("function", "(url|data-uri)", {
        starts: {
            cN: "string",
            e: "[\\)\\n]",
            eE: !0
        }
    }), s("function", r), b, i("variable", "@@?" + r, 10), i("variable", "@{" + r + "}"), i("built_in", "~?`[^`]*?`"), {
        cN: "attribute",
        b: r + "\\s*:",
        e: ":",
        rB: !0,
        eE: !0
    });
    var o = c.concat({
            b: "{",
            e: "}",
            c: a
        }),
        u = {
            bK: "when",
            eW: !0,
            c: [{
                bK: "and not"
            }].concat(c)
        },
        C = {
            cN: "attribute",
            b: t,
            e: ":",
            eE: !0,
            c: [e.CLCM, e.CBCM],
            i: /\S/,
            starts: {
                e: "[;}]",
                rE: !0,
                c: c,
                i: "[<=$]"
            }
        },
        l = {
            cN: "at_rule",
            b: "@(import|media|charset|font-face|(-[a-z]+-)?keyframes|supports|document|namespace|page|viewport|host)\\b",
            starts: {
                e: "[;{}]",
                rE: !0,
                c: c,
                r: 0
            }
        },
        d = {
            cN: "variable",
            v: [{
                b: "@" + r + "\\s*:",
                r: 15
            }, {
                b: "@" + r
            }],
            starts: {
                e: "[;}]",
                rE: !0,
                c: o
            }
        },
        p = {
            v: [{
                b: "[\\.#:&\\[]",
                e: "[;{}]"
            }, {
                b: t + "[^;]*{",
                e: "{"
            }],
            rB: !0,
            rE: !0,
            i: "[<='$\"]",
            c: [e.CLCM, e.CBCM, u, i("keyword", "all\\b"), i("variable", "@{" + r + "}"), i("tag", t + "%?", 0), i("id", "#" + t), i("class", "\\." + t, 0), i("keyword", "&", 0), s("pseudo", ":not"), s("keyword", ":extend"), i("pseudo", "::?" + t), {
                cN: "attr_selector",
                b: "\\[",
                e: "\\]"
            }, {
                b: "\\(",
                e: "\\)",
                c: o
            }, {
                b: "!important"
            }]
        };
    return a.push(e.CLCM, e.CBCM, l, d, p, C), {
        cI: !0,
        i: "[=>'/<($\"]",
        c: a
    }
});
hljs.registerLanguage("css", function(e) {
    var c = "[a-zA-Z-][a-zA-Z0-9_-]*",
        a = {
            cN: "function",
            b: c + "\\(",
            rB: !0,
            eE: !0,
            e: "\\("
        },
        r = {
            cN: "rule",
            b: /[A-Z\_\.\-]+\s*:/,
            rB: !0,
            e: ";",
            eW: !0,
            c: [{
                cN: "attribute",
                b: /\S/,
                e: ":",
                eE: !0,
                starts: {
                    cN: "value",
                    eW: !0,
                    eE: !0,
                    c: [a, e.CSSNM, e.QSM, e.ASM, e.CBCM, {
                        cN: "hexcolor",
                        b: "#[0-9A-Fa-f]+"
                    }, {
                        cN: "important",
                        b: "!important"
                    }]
                }
            }]
        };
    return {
        cI: !0,
        i: /[=\/|'\$]/,
        c: [e.CBCM, r, {
            cN: "id",
            b: /\#[A-Za-z0-9_-]+/
        }, {
            cN: "class",
            b: /\.[A-Za-z0-9_-]+/
        }, {
            cN: "attr_selector",
            b: /\[/,
            e: /\]/,
            i: "$"
        }, {
            cN: "pseudo",
            b: /:(:)?[a-zA-Z0-9\_\-\+\(\)"']+/
        }, {
            cN: "at_rule",
            b: "@(font-face|page)",
            l: "[a-z-]+",
            k: "font-face page"
        }, {
            cN: "at_rule",
            b: "@",
            e: "[{;]",
            c: [{
                cN: "keyword",
                b: /\S+/
            }, {
                b: /\s/,
                eW: !0,
                eE: !0,
                r: 0,
                c: [a, e.ASM, e.QSM, e.CSSNM]
            }]
        }, {
            cN: "tag",
            b: c,
            r: 0
        }, {
            cN: "rules",
            b: "{",
            e: "}",
            i: /\S/,
            c: [e.CBCM, r]
        }]
    }
});
hljs.registerLanguage("cs", function(e) {
    var r = "abstract as base bool break byte case catch char checked const continue decimal dynamic default delegate do double else enum event explicit extern false finally fixed float for foreach goto if implicit in int interface internal is lock long null when object operator out override params private protected public readonly ref sbyte sealed short sizeof stackalloc static string struct switch this true try typeof uint ulong unchecked unsafe ushort using virtual volatile void while async protected public private internal ascending descending from get group into join let orderby partial select set value var where yield",
        t = e.IR + "(<" + e.IR + ">)?";
    return {
        aliases: ["csharp"],
        k: r,
        i: /::/,
        c: [e.C("///", "$", {
            rB: !0,
            c: [{
                cN: "xmlDocTag",
                v: [{
                    b: "///",
                    r: 0
                }, {
                    b: "<!--|-->"
                }, {
                    b: "</?",
                    e: ">"
                }]
            }]
        }), e.CLCM, e.CBCM, {
            cN: "preprocessor",
            b: "#",
            e: "$",
            k: "if else elif endif define undef warning error line region endregion pragma checksum"
        }, {
            cN: "string",
            b: '@"',
            e: '"',
            c: [{
                b: '""'
            }]
        }, e.ASM, e.QSM, e.CNM, {
            bK: "class interface",
            e: /[{;=]/,
            i: /[^\s:]/,
            c: [e.TM, e.CLCM, e.CBCM]
        }, {
            bK: "namespace",
            e: /[{;=]/,
            i: /[^\s:]/,
            c: [{
                cN: "title",
                b: "[a-zA-Z](\\.?\\w)*",
                r: 0
            }, e.CLCM, e.CBCM]
        }, {
            bK: "new return throw await",
            r: 0
        }, {
            cN: "function",
            b: "(" + t + "\\s+)+" + e.IR + "\\s*\\(",
            rB: !0,
            e: /[{;=]/,
            eE: !0,
            k: r,
            c: [{
                b: e.IR + "\\s*\\(",
                rB: !0,
                c: [e.TM],
                r: 0
            }, {
                cN: "params",
                b: /\(/,
                e: /\)/,
                eB: !0,
                eE: !0,
                k: r,
                r: 0,
                c: [e.ASM, e.QSM, e.CNM, e.CBCM]
            }, e.CLCM, e.CBCM]
        }]
    }
});
hljs.registerLanguage("xml", function(t) {
    var e = "[A-Za-z0-9\\._:-]+",
        s = {
            b: /<\?(php)?(?!\w)/,
            e: /\?>/,
            sL: "php",
            subLanguageMode: "continuous"
        },
        c = {
            eW: !0,
            i: /</,
            r: 0,
            c: [s, {
                cN: "attribute",
                b: e,
                r: 0
            }, {
                b: "=",
                r: 0,
                c: [{
                    cN: "value",
                    c: [s],
                    v: [{
                        b: /"/,
                        e: /"/
                    }, {
                        b: /'/,
                        e: /'/
                    }, {
                        b: /[^\s\/>]+/
                    }]
                }]
            }]
        };
    return {
        aliases: ["html", "xhtml", "rss", "atom", "xsl", "plist"],
        cI: !0,
        c: [{
            cN: "doctype",
            b: "<!DOCTYPE",
            e: ">",
            r: 10,
            c: [{
                b: "\\[",
                e: "\\]"
            }]
        }, t.C("<!--", "-->", {
            r: 10
        }), {
            cN: "cdata",
            b: "<\\!\\[CDATA\\[",
            e: "\\]\\]>",
            r: 10
        }, {
            cN: "tag",
            b: "<style(?=\\s|>|$)",
            e: ">",
            k: {
                title: "style"
            },
            c: [c],
            starts: {
                e: "</style>",
                rE: !0,
                sL: "css"
            }
        }, {
            cN: "tag",
            b: "<script(?=\\s|>|$)",
            e: ">",
            k: {
                title: "script"
            },
            c: [c],
            starts: {
                e: "</script>",
                rE: !0,
                sL: ""
            }
        }, s, {
            cN: "pi",
            b: /<\?\w+/,
            e: /\?>/,
            r: 10
        }, {
            cN: "tag",
            b: "</?",
            e: "/?>",
            c: [{
                cN: "title",
                b: /[^ \/><\n\t]+/,
                r: 0
            }, c]
        }]
    }
});
hljs.registerLanguage("stylus", function(t) {
    var e = {
            cN: "variable",
            b: "\\$" + t.IR
        },
        o = {
            cN: "hexcolor",
            b: "#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})",
            r: 10
        },
        i = ["charset", "css", "debug", "extend", "font-face", "for", "import", "include", "media", "mixin", "page", "warn", "while"],
        r = ["after", "before", "first-letter", "first-line", "active", "first-child", "focus", "hover", "lang", "link", "visited"],
        n = ["a", "abbr", "address", "article", "aside", "audio", "b", "blockquote", "body", "button", "canvas", "caption", "cite", "code", "dd", "del", "details", "dfn", "div", "dl", "dt", "em", "fieldset", "figcaption", "figure", "footer", "form", "h1", "h2", "h3", "h4", "h5", "h6", "header", "hgroup", "html", "i", "iframe", "img", "input", "ins", "kbd", "label", "legend", "li", "mark", "menu", "nav", "object", "ol", "p", "q", "quote", "samp", "section", "span", "strong", "summary", "sup", "table", "tbody", "td", "textarea", "tfoot", "th", "thead", "time", "tr", "ul", "var", "video"],
        a = "[\\.\\s\\n\\[\\:,]",
        l = ["align-content", "align-items", "align-self", "animation", "animation-delay", "animation-direction", "animation-duration", "animation-fill-mode", "animation-iteration-count", "animation-name", "animation-play-state", "animation-timing-function", "auto", "backface-visibility", "background", "background-attachment", "background-clip", "background-color", "background-image", "background-origin", "background-position", "background-repeat", "background-size", "border", "border-bottom", "border-bottom-color", "border-bottom-left-radius", "border-bottom-right-radius", "border-bottom-style", "border-bottom-width", "border-collapse", "border-color", "border-image", "border-image-outset", "border-image-repeat", "border-image-slice", "border-image-source", "border-image-width", "border-left", "border-left-color", "border-left-style", "border-left-width", "border-radius", "border-right", "border-right-color", "border-right-style", "border-right-width", "border-spacing", "border-style", "border-top", "border-top-color", "border-top-left-radius", "border-top-right-radius", "border-top-style", "border-top-width", "border-width", "bottom", "box-decoration-break", "box-shadow", "box-sizing", "break-after", "break-before", "break-inside", "caption-side", "clear", "clip", "clip-path", "color", "column-count", "column-fill", "column-gap", "column-rule", "column-rule-color", "column-rule-style", "column-rule-width", "column-span", "column-width", "columns", "content", "counter-increment", "counter-reset", "cursor", "direction", "display", "empty-cells", "filter", "flex", "flex-basis", "flex-direction", "flex-flow", "flex-grow", "flex-shrink", "flex-wrap", "float", "font", "font-family", "font-feature-settings", "font-kerning", "font-language-override", "font-size", "font-size-adjust", "font-stretch", "font-style", "font-variant", "font-variant-ligatures", "font-weight", "height", "hyphens", "icon", "image-orientation", "image-rendering", "image-resolution", "ime-mode", "inherit", "initial", "justify-content", "left", "letter-spacing", "line-height", "list-style", "list-style-image", "list-style-position", "list-style-type", "margin", "margin-bottom", "margin-left", "margin-right", "margin-top", "marks", "mask", "max-height", "max-width", "min-height", "min-width", "nav-down", "nav-index", "nav-left", "nav-right", "nav-up", "none", "normal", "object-fit", "object-position", "opacity", "order", "orphans", "outline", "outline-color", "outline-offset", "outline-style", "outline-width", "overflow", "overflow-wrap", "overflow-x", "overflow-y", "padding", "padding-bottom", "padding-left", "padding-right", "padding-top", "page-break-after", "page-break-before", "page-break-inside", "perspective", "perspective-origin", "pointer-events", "position", "quotes", "resize", "right", "tab-size", "table-layout", "text-align", "text-align-last", "text-decoration", "text-decoration-color", "text-decoration-line", "text-decoration-style", "text-indent", "text-overflow", "text-rendering", "text-shadow", "text-transform", "text-underline-position", "top", "transform", "transform-origin", "transform-style", "transition", "transition-delay", "transition-duration", "transition-property", "transition-timing-function", "unicode-bidi", "vertical-align", "visibility", "white-space", "widows", "width", "word-break", "word-spacing", "word-wrap", "z-index"],
        d = ["\\{", "\\}", "\\?", "(\\bReturn\\b)", "(\\bEnd\\b)", "(\\bend\\b)", ";", "#\\s", "\\*\\s", "===\\s", "\\|", "%"];
    return {
        aliases: ["styl"],
        cI: !1,
        i: "(" + d.join("|") + ")",
        k: "if else for in",
        c: [t.QSM, t.ASM, t.CLCM, t.CBCM, o, {
            b: "\\.[a-zA-Z][a-zA-Z0-9_-]*" + a,
            rB: !0,
            c: [{
                cN: "class",
                b: "\\.[a-zA-Z][a-zA-Z0-9_-]*"
            }]
        }, {
            b: "\\#[a-zA-Z][a-zA-Z0-9_-]*" + a,
            rB: !0,
            c: [{
                cN: "id",
                b: "\\#[a-zA-Z][a-zA-Z0-9_-]*"
            }]
        }, {
            b: "\\b(" + n.join("|") + ")" + a,
            rB: !0,
            c: [{
                cN: "tag",
                b: "\\b[a-zA-Z][a-zA-Z0-9_-]*"
            }]
        }, {
            cN: "pseudo",
            b: "&?:?:\\b(" + r.join("|") + ")" + a
        }, {
            cN: "at_rule",
            b: "@(" + i.join("|") + ")\\b"
        }, e, t.CSSNM, t.NM, {
            cN: "function",
            b: "\\b[a-zA-Z][a-zA-Z0-9_-]*\\(.*\\)",
            i: "[\\n]",
            rB: !0,
            c: [{
                cN: "title",
                b: "\\b[a-zA-Z][a-zA-Z0-9_-]*"
            }, {
                cN: "params",
                b: /\(/,
                e: /\)/,
                c: [o, e, t.ASM, t.CSSNM, t.NM, t.QSM]
            }]
        }, {
            cN: "attribute",
            b: "\\b(" + l.reverse().join("|") + ")\\b"
        }]
    }
});
hljs.registerLanguage("scss", function(e) {
    var t = "[a-zA-Z-][a-zA-Z0-9_-]*",
        i = {
            cN: "variable",
            b: "(\\$" + t + ")\\b"
        },
        r = {
            cN: "function",
            b: t + "\\(",
            rB: !0,
            eE: !0,
            e: "\\("
        },
        o = {
            cN: "hexcolor",
            b: "#[0-9A-Fa-f]+"
        };
    ({
        cN: "attribute",
        b: "[A-Z\\_\\.\\-]+",
        e: ":",
        eE: !0,
        i: "[^\\s]",
        starts: {
            cN: "value",
            eW: !0,
            eE: !0,
            c: [r, o, e.CSSNM, e.QSM, e.ASM, e.CBCM, {
                cN: "important",
                b: "!important"
            }]
        }
    });
    return {
        cI: !0,
        i: "[=/|']",
        c: [e.CLCM, e.CBCM, r, {
            cN: "id",
            b: "\\#[A-Za-z0-9_-]+",
            r: 0
        }, {
            cN: "class",
            b: "\\.[A-Za-z0-9_-]+",
            r: 0
        }, {
            cN: "attr_selector",
            b: "\\[",
            e: "\\]",
            i: "$"
        }, {
            cN: "tag",
            b: "\\b(a|abbr|acronym|address|area|article|aside|audio|b|base|big|blockquote|body|br|button|canvas|caption|cite|code|col|colgroup|command|datalist|dd|del|details|dfn|div|dl|dt|em|embed|fieldset|figcaption|figure|footer|form|frame|frameset|(h[1-6])|head|header|hgroup|hr|html|i|iframe|img|input|ins|kbd|keygen|label|legend|li|link|map|mark|meta|meter|nav|noframes|noscript|object|ol|optgroup|option|output|p|param|pre|progress|q|rp|rt|ruby|samp|script|section|select|small|span|strike|strong|style|sub|sup|table|tbody|td|textarea|tfoot|th|thead|time|title|tr|tt|ul|var|video)\\b",
            r: 0
        }, {
            cN: "pseudo",
            b: ":(visited|valid|root|right|required|read-write|read-only|out-range|optional|only-of-type|only-child|nth-of-type|nth-last-of-type|nth-last-child|nth-child|not|link|left|last-of-type|last-child|lang|invalid|indeterminate|in-range|hover|focus|first-of-type|first-line|first-letter|first-child|first|enabled|empty|disabled|default|checked|before|after|active)"
        }, {
            cN: "pseudo",
            b: "::(after|before|choices|first-letter|first-line|repeat-index|repeat-item|selection|value)"
        }, i, {
            cN: "attribute",
            b: "\\b(z-index|word-wrap|word-spacing|word-break|width|widows|white-space|visibility|vertical-align|unicode-bidi|transition-timing-function|transition-property|transition-duration|transition-delay|transition|transform-style|transform-origin|transform|top|text-underline-position|text-transform|text-shadow|text-rendering|text-overflow|text-indent|text-decoration-style|text-decoration-line|text-decoration-color|text-decoration|text-align-last|text-align|tab-size|table-layout|right|resize|quotes|position|pointer-events|perspective-origin|perspective|page-break-inside|page-break-before|page-break-after|padding-top|padding-right|padding-left|padding-bottom|padding|overflow-y|overflow-x|overflow-wrap|overflow|outline-width|outline-style|outline-offset|outline-color|outline|orphans|order|opacity|object-position|object-fit|normal|none|nav-up|nav-right|nav-left|nav-index|nav-down|min-width|min-height|max-width|max-height|mask|marks|margin-top|margin-right|margin-left|margin-bottom|margin|list-style-type|list-style-position|list-style-image|list-style|line-height|letter-spacing|left|justify-content|initial|inherit|ime-mode|image-orientation|image-resolution|image-rendering|icon|hyphens|height|font-weight|font-variant-ligatures|font-variant|font-style|font-stretch|font-size-adjust|font-size|font-language-override|font-kerning|font-feature-settings|font-family|font|float|flex-wrap|flex-shrink|flex-grow|flex-flow|flex-direction|flex-basis|flex|filter|empty-cells|display|direction|cursor|counter-reset|counter-increment|content|column-width|column-span|column-rule-width|column-rule-style|column-rule-color|column-rule|column-gap|column-fill|column-count|columns|color|clip-path|clip|clear|caption-side|break-inside|break-before|break-after|box-sizing|box-shadow|box-decoration-break|bottom|border-width|border-top-width|border-top-style|border-top-right-radius|border-top-left-radius|border-top-color|border-top|border-style|border-spacing|border-right-width|border-right-style|border-right-color|border-right|border-radius|border-left-width|border-left-style|border-left-color|border-left|border-image-width|border-image-source|border-image-slice|border-image-repeat|border-image-outset|border-image|border-color|border-collapse|border-bottom-width|border-bottom-style|border-bottom-right-radius|border-bottom-left-radius|border-bottom-color|border-bottom|border|background-size|background-repeat|background-position|background-origin|background-image|background-color|background-clip|background-attachment|background-blend-mode|background|backface-visibility|auto|animation-timing-function|animation-play-state|animation-name|animation-iteration-count|animation-fill-mode|animation-duration|animation-direction|animation-delay|animation|align-self|align-items|align-content)\\b",
            i: "[^\\s]"
        }, {
            cN: "value",
            b: "\\b(whitespace|wait|w-resize|visible|vertical-text|vertical-ideographic|uppercase|upper-roman|upper-alpha|underline|transparent|top|thin|thick|text|text-top|text-bottom|tb-rl|table-header-group|table-footer-group|sw-resize|super|strict|static|square|solid|small-caps|separate|se-resize|scroll|s-resize|rtl|row-resize|ridge|right|repeat|repeat-y|repeat-x|relative|progress|pointer|overline|outside|outset|oblique|nowrap|not-allowed|normal|none|nw-resize|no-repeat|no-drop|newspaper|ne-resize|n-resize|move|middle|medium|ltr|lr-tb|lowercase|lower-roman|lower-alpha|loose|list-item|line|line-through|line-edge|lighter|left|keep-all|justify|italic|inter-word|inter-ideograph|inside|inset|inline|inline-block|inherit|inactive|ideograph-space|ideograph-parenthesis|ideograph-numeric|ideograph-alpha|horizontal|hidden|help|hand|groove|fixed|ellipsis|e-resize|double|dotted|distribute|distribute-space|distribute-letter|distribute-all-lines|disc|disabled|default|decimal|dashed|crosshair|collapse|col-resize|circle|char|center|capitalize|break-word|break-all|bottom|both|bolder|bold|block|bidi-override|below|baseline|auto|always|all-scroll|absolute|table|table-cell)\\b"
        }, {
            cN: "value",
            b: ":",
            e: ";",
            c: [r, i, o, e.CSSNM, e.QSM, e.ASM, {
                cN: "important",
                b: "!important"
            }]
        }, {
            cN: "at_rule",
            b: "@",
            e: "[{;]",
            k: "mixin include extend for if else each while charset import debug media page content font-face namespace warn",
            c: [r, i, e.QSM, e.ASM, o, e.CSSNM, {
                cN: "preprocessor",
                b: "\\s[A-Za-z0-9_.-]+",
                r: 0
            }]
        }]
    }
});
hljs.registerLanguage("makefile", function(e) {
    var a = {
        cN: "variable",
        b: /\$\(/,
        e: /\)/,
        c: [e.BE]
    };
    return {
        aliases: ["mk", "mak"],
        c: [e.HCM, {
            b: /^\w+\s*\W*=/,
            rB: !0,
            r: 0,
            starts: {
                cN: "constant",
                e: /\s*\W*=/,
                eE: !0,
                starts: {
                    e: /$/,
                    r: 0,
                    c: [a]
                }
            }
        }, {
            cN: "title",
            b: /^[\w]+:\s*$/
        }, {
            cN: "phony",
            b: /^\.PHONY:/,
            e: /$/,
            k: ".PHONY",
            l: /[\.\w]+/
        }, {
            b: /^\t+/,
            e: /$/,
            r: 0,
            c: [e.QSM, a]
        }]
    }
});
hljs.registerLanguage("ini", function(e) {
    return {
        cI: !0,
        i: /\S/,
        c: [e.C(";", "$"), {
            cN: "title",
            b: "^\\[",
            e: "\\]"
        }, {
            cN: "setting",
            b: "^[a-z0-9\\[\\]_-]+[ \\t]*=[ \\t]*",
            e: "$",
            c: [{
                cN: "value",
                eW: !0,
                k: "on off true false yes no",
                c: [e.QSM, e.NM],
                r: 0
            }]
        }]
    }
});
hljs.registerLanguage("bash", function(e) {
    var t = {
            cN: "variable",
            v: [{
                b: /\$[\w\d#@][\w\d_]*/
            }, {
                b: /\$\{(.*?)}/
            }]
        },
        s = {
            cN: "string",
            b: /"/,
            e: /"/,
            c: [e.BE, t, {
                cN: "variable",
                b: /\$\(/,
                e: /\)/,
                c: [e.BE]
            }]
        },
        a = {
            cN: "string",
            b: /'/,
            e: /'/
        };
    return {
        aliases: ["sh", "zsh"],
        l: /-?[a-z\.]+/,
        k: {
            keyword: "if then else elif fi for while in do done case esac function",
            literal: "true false",
            built_in: "break cd continue eval exec exit export getopts hash pwd readonly return shift test times trap umask unset alias bind builtin caller command declare echo enable help let local logout mapfile printf read readarray source type typeset ulimit unalias set shopt autoload bg bindkey bye cap chdir clone comparguments compcall compctl compdescribe compfiles compgroups compquote comptags comptry compvalues dirs disable disown echotc echoti emulate fc fg float functions getcap getln history integer jobs kill limit log noglob popd print pushd pushln rehash sched setcap setopt stat suspend ttyctl unfunction unhash unlimit unsetopt vared wait whence where which zcompile zformat zftp zle zmodload zparseopts zprof zpty zregexparse zsocket zstyle ztcp",
            operator: "-ne -eq -lt -gt -f -d -e -s -l -a"
        },
        c: [{
            cN: "shebang",
            b: /^#![^\n]+sh\s*$/,
            r: 10
        }, {
            cN: "function",
            b: /\w[\w\d_]*\s*\(\s*\)\s*\{/,
            rB: !0,
            c: [e.inherit(e.TM, {
                b: /\w[\w\d_]*/
            })],
            r: 0
        }, e.HCM, e.NM, s, a, t]
    }
});
hljs.registerLanguage("perl", function(e) {
    var t = "getpwent getservent quotemeta msgrcv scalar kill dbmclose undef lc ma syswrite tr send umask sysopen shmwrite vec qx utime local oct semctl localtime readpipe do return format read sprintf dbmopen pop getpgrp not getpwnam rewinddir qqfileno qw endprotoent wait sethostent bless s|0 opendir continue each sleep endgrent shutdown dump chomp connect getsockname die socketpair close flock exists index shmgetsub for endpwent redo lstat msgctl setpgrp abs exit select print ref gethostbyaddr unshift fcntl syscall goto getnetbyaddr join gmtime symlink semget splice x|0 getpeername recv log setsockopt cos last reverse gethostbyname getgrnam study formline endhostent times chop length gethostent getnetent pack getprotoent getservbyname rand mkdir pos chmod y|0 substr endnetent printf next open msgsnd readdir use unlink getsockopt getpriority rindex wantarray hex system getservbyport endservent int chr untie rmdir prototype tell listen fork shmread ucfirst setprotoent else sysseek link getgrgid shmctl waitpid unpack getnetbyname reset chdir grep split require caller lcfirst until warn while values shift telldir getpwuid my getprotobynumber delete and sort uc defined srand accept package seekdir getprotobyname semop our rename seek if q|0 chroot sysread setpwent no crypt getc chown sqrt write setnetent setpriority foreach tie sin msgget map stat getlogin unless elsif truncate exec keys glob tied closedirioctl socket readlink eval xor readline binmode setservent eof ord bind alarm pipe atan2 getgrent exp time push setgrent gt lt or ne m|0 break given say state when",
        r = {
            cN: "subst",
            b: "[$@]\\{",
            e: "\\}",
            k: t
        },
        s = {
            b: "->{",
            e: "}"
        },
        n = {
            cN: "variable",
            v: [{
                b: /\$\d/
            }, {
                b: /[\$%@](\^\w\b|#\w+(::\w+)*|{\w+}|\w+(::\w*)*)/
            }, {
                b: /[\$%@][^\s\w{]/,
                r: 0
            }]
        },
        i = e.C("^(__END__|__DATA__)", "\\n$", {
            r: 5
        }),
        o = [e.BE, r, n],
        a = [n, e.HCM, i, e.C("^\\=\\w", "\\=cut", {
            eW: !0
        }), s, {
            cN: "string",
            c: o,
            v: [{
                b: "q[qwxr]?\\s*\\(",
                e: "\\)",
                r: 5
            }, {
                b: "q[qwxr]?\\s*\\[",
                e: "\\]",
                r: 5
            }, {
                b: "q[qwxr]?\\s*\\{",
                e: "\\}",
                r: 5
            }, {
                b: "q[qwxr]?\\s*\\|",
                e: "\\|",
                r: 5
            }, {
                b: "q[qwxr]?\\s*\\<",
                e: "\\>",
                r: 5
            }, {
                b: "qw\\s+q",
                e: "q",
                r: 5
            }, {
                b: "'",
                e: "'",
                c: [e.BE]
            }, {
                b: '"',
                e: '"'
            }, {
                b: "`",
                e: "`",
                c: [e.BE]
            }, {
                b: "{\\w+}",
                c: [],
                r: 0
            }, {
                b: "-?\\w+\\s*\\=\\>",
                c: [],
                r: 0
            }]
        }, {
            cN: "number",
            b: "(\\b0[0-7_]+)|(\\b0x[0-9a-fA-F_]+)|(\\b[1-9][0-9_]*(\\.[0-9_]+)?)|[0_]\\b",
            r: 0
        }, {
            b: "(\\/\\/|" + e.RSR + "|\\b(split|return|print|reverse|grep)\\b)\\s*",
            k: "split return print reverse grep",
            r: 0,
            c: [e.HCM, i, {
                cN: "regexp",
                b: "(s|tr|y)/(\\\\.|[^/])*/(\\\\.|[^/])*/[a-z]*",
                r: 10
            }, {
                cN: "regexp",
                b: "(m|qr)?/",
                e: "/[a-z]*",
                c: [e.BE],
                r: 0
            }]
        }, {
            cN: "sub",
            bK: "sub",
            e: "(\\s*\\(.*?\\))?[;{]",
            r: 5
        }, {
            cN: "operator",
            b: "-\\w\\b",
            r: 0
        }];
    return r.c = a, s.c = a, {
        aliases: ["pl"],
        k: t,
        c: a
    }
});
hljs.registerLanguage("haml", function(s) {
    return {
        cI: !0,
        c: [{
            cN: "doctype",
            b: "^!!!( (5|1\\.1|Strict|Frameset|Basic|Mobile|RDFa|XML\\b.*))?$",
            r: 10
        }, s.C("^\\s*(!=#|=#|-#|/).*$", !1, {
            r: 0
        }), {
            b: "^\\s*(-|=|!=)(?!#)",
            starts: {
                e: "\\n",
                sL: "ruby"
            }
        }, {
            cN: "tag",
            b: "^\\s*%",
            c: [{
                cN: "title",
                b: "\\w+"
            }, {
                cN: "value",
                b: "[#\\.][\\w-]+"
            }, {
                b: "{\\s*",
                e: "\\s*}",
                eE: !0,
                c: [{
                    b: ":\\w+\\s*=>",
                    e: ",\\s+",
                    rB: !0,
                    eW: !0,
                    c: [{
                        cN: "symbol",
                        b: ":\\w+"
                    }, s.ASM, s.QSM, {
                        b: "\\w+",
                        r: 0
                    }]
                }]
            }, {
                b: "\\(\\s*",
                e: "\\s*\\)",
                eE: !0,
                c: [{
                    b: "\\w+\\s*=",
                    e: "\\s+",
                    rB: !0,
                    eW: !0,
                    c: [{
                        cN: "attribute",
                        b: "\\w+",
                        r: 0
                    }, s.ASM, s.QSM, {
                        b: "\\w+",
                        r: 0
                    }]
                }]
            }]
        }, {
            cN: "bullet",
            b: "^\\s*[=~]\\s*",
            r: 0
        }, {
            b: "#{",
            starts: {
                e: "}",
                sL: "ruby"
            }
        }]
    }
});

// ##############################################################################
// FILE: Vendor/MarkdownIt/markdownIt.js
// ##############################################################################

/*! markdown-it 4.2.1 https://github.com//markdown-it/markdown-it @license MIT */
! function(e) {
    if ("object" == typeof exports && "undefined" != typeof module) module.exports = e();
    else if ("function" == typeof define && define.amd) define([], e);
    else {
        var r;
        r = "undefined" != typeof window ? window : "undefined" != typeof global ? global : "undefined" != typeof self ? self : this, r.markdownit = e()
    }
}(function() {
    var e;
    return function r(e, t, n) {
        function s(o, a) {
            if (!t[o]) {
                if (!e[o]) {
                    var c = "function" == typeof require && require;
                    if (!a && c) return c(o, !0);
                    if (i) return i(o, !0);
                    var l = new Error("Cannot find module '" + o + "'");
                    throw l.code = "MODULE_NOT_FOUND", l
                }
                var u = t[o] = {
                    exports: {}
                };
                e[o][0].call(u.exports, function(r) {
                    var t = e[o][1][r];
                    return s(t ? t : r)
                }, u, u.exports, r, e, t, n)
            }
            return t[o].exports
        }
        for (var i = "function" == typeof require && require, o = 0; o < n.length; o++) s(n[o]);
        return s
    }({
        1: [function(e, r, t) {
            "use strict";
            r.exports = e("entities/maps/entities.json")
        }, {
            "entities/maps/entities.json": 52
        }],
        2: [function(e, r, t) {
            "use strict";
            var n = {};
            ["article", "aside", "button", "blockquote", "body", "canvas", "caption", "col", "colgroup", "dd", "div", "dl", "dt", "embed", "fieldset", "figcaption", "figure", "footer", "form", "h1", "h2", "h3", "h4", "h5", "h6", "header", "hgroup", "hr", "iframe", "li", "map", "object", "ol", "output", "p", "pre", "progress", "script", "section", "style", "table", "tbody", "td", "textarea", "tfoot", "th", "tr", "thead", "ul", "video"].forEach(function(e) {
                n[e] = !0
            }), r.exports = n
        }, {}],
        3: [function(e, r, t) {
            "use strict";
            var n = "[a-zA-Z_:][a-zA-Z0-9:._-]*",
                s = "[^\"'=<>`\\x00-\\x20]+",
                i = "'[^']*'",
                o = '"[^"]*"',
                a = "(?:" + s + "|" + i + "|" + o + ")",
                c = "(?:\\s+" + n + "(?:\\s*=\\s*" + a + ")?)",
                l = "<[A-Za-z][A-Za-z0-9\\-]*" + c + "*\\s*\\/?>",
                u = "<\\/[A-Za-z][A-Za-z0-9\\-]*\\s*>",
                p = "<!---->|<!--(?:-?[^>-])(?:-?[^-])*-->",
                h = "<[?].*?[?]>",
                f = "<![A-Z]+\\s+[^>]*>",
                d = "<!\\[CDATA\\[[\\s\\S]*?\\]\\]>",
                m = new RegExp("^(?:" + l + "|" + u + "|" + p + "|" + h + "|" + f + "|" + d + ")");
            r.exports.HTML_TAG_RE = m
        }, {}],
        4: [function(e, r, t) {
            "use strict";
            r.exports = ["coap", "doi", "javascript", "aaa", "aaas", "about", "acap", "cap", "cid", "crid", "data", "dav", "dict", "dns", "file", "ftp", "geo", "go", "gopher", "h323", "http", "https", "iax", "icap", "im", "imap", "info", "ipp", "iris", "iris.beep", "iris.xpc", "iris.xpcs", "iris.lwz", "ldap", "mailto", "mid", "msrp", "msrps", "mtqp", "mupdate", "news", "nfs", "ni", "nih", "nntp", "opaquelocktoken", "pop", "pres", "rtsp", "service", "session", "shttp", "sieve", "sip", "sips", "sms", "snmp", "soap.beep", "soap.beeps", "tag", "tel", "telnet", "tftp", "thismessage", "tn3270", "tip", "tv", "urn", "vemmi", "ws", "wss", "xcon", "xcon-userid", "xmlrpc.beep", "xmlrpc.beeps", "xmpp", "z39.50r", "z39.50s", "adiumxtra", "afp", "afs", "aim", "apt", "attachment", "aw", "beshare", "bitcoin", "bolo", "callto", "chrome", "chrome-extension", "com-eventbrite-attendee", "content", "cvs", "dlna-playsingle", "dlna-playcontainer", "dtn", "dvb", "ed2k", "facetime", "feed", "finger", "fish", "gg", "git", "gizmoproject", "gtalk", "hcp", "icon", "ipn", "irc", "irc6", "ircs", "itms", "jar", "jms", "keyparc", "lastfm", "ldaps", "magnet", "maps", "market", "message", "mms", "ms-help", "msnim", "mumble", "mvn", "notes", "oid", "palm", "paparazzi", "platform", "proxy", "psyc", "query", "res", "resource", "rmi", "rsync", "rtmp", "secondlife", "sftp", "sgn", "skype", "smb", "soldat", "spotify", "ssh", "steam", "svn", "teamspeak", "things", "udp", "unreal", "ut2004", "ventrilo", "view-source", "webcal", "wtai", "wyciwyg", "xfire", "xri", "ymsgr"]
        }, {}],
        5: [function(e, r, t) {
            "use strict";

            function n(e) {
                return Object.prototype.toString.call(e)
            }

            function s(e) {
                return "[object String]" === n(e)
            }

            function i(e, r) {
                return v.call(e, r)
            }

            function o(e) {
                var r = Array.prototype.slice.call(arguments, 1);
                return r.forEach(function(r) {
                    if (r) {
                        if ("object" != typeof r) throw new TypeError(r + "must be object");
                        Object.keys(r).forEach(function(t) {
                            e[t] = r[t]
                        })
                    }
                }), e
            }

            function a(e, r, t) {
                return [].concat(e.slice(0, r), t, e.slice(r + 1))
            }

            function c(e) {
                return e >= 55296 && 57343 >= e ? !1 : e >= 64976 && 65007 >= e ? !1 : 65535 === (65535 & e) || 65534 === (65535 & e) ? !1 : e >= 0 && 8 >= e ? !1 : 11 === e ? !1 : e >= 14 && 31 >= e ? !1 : e >= 127 && 159 >= e ? !1 : e > 1114111 ? !1 : !0
            }

            function l(e) {
                if (e > 65535) {
                    e -= 65536;
                    var r = 55296 + (e >> 10),
                        t = 56320 + (1023 & e);
                    return String.fromCharCode(r, t)
                }
                return String.fromCharCode(e)
            }

            function u(e, r) {
                var t = 0;
                return i(w, r) ? w[r] : 35 === r.charCodeAt(0) && C.test(r) && (t = "x" === r[1].toLowerCase() ? parseInt(r.slice(2), 16) : parseInt(r.slice(1), 10), c(t)) ? l(t) : e
            }

            function p(e) {
                return e.indexOf("\\") < 0 ? e : e.replace(x, "$1")
            }

            function h(e) {
                return e.indexOf("\\") < 0 && e.indexOf("&") < 0 ? e : e.replace(A, function(e, r, t) {
                    return r ? r : u(e, t)
                })
            }

            function f(e) {
                return E[e]
            }

            function d(e) {
                return q.test(e) ? e.replace(D, f) : e
            }

            function m(e) {
                return e.replace(S, "\\$&")
            }

            function g(e) {
                if (e >= 8192 && 8202 >= e) return !0;
                switch (e) {
                    case 9:
                    case 10:
                    case 11:
                    case 12:
                    case 13:
                    case 32:
                    case 160:
                    case 5760:
                    case 8239:
                    case 8287:
                    case 12288:
                        return !0
                }
                return !1
            }

            function _(e) {
                return F.test(e)
            }

            function b(e) {
                switch (e) {
                    case 33:
                    case 34:
                    case 35:
                    case 36:
                    case 37:
                    case 38:
                    case 39:
                    case 40:
                    case 41:
                    case 42:
                    case 43:
                    case 44:
                    case 45:
                    case 46:
                    case 47:
                    case 58:
                    case 59:
                    case 60:
                    case 61:
                    case 62:
                    case 63:
                    case 64:
                    case 91:
                    case 92:
                    case 93:
                    case 94:
                    case 95:
                    case 96:
                    case 123:
                    case 124:
                    case 125:
                    case 126:
                        return !0;
                    default:
                        return !1
                }
            }

            function k(e) {
                return e.trim().replace(/\s+/g, " ").toUpperCase()
            }
            var v = Object.prototype.hasOwnProperty,
                x = /\\([!"#$%&'()*+,\-.\/:;<=>?@[\\\]^_`{|}~])/g,
                y = /&([a-z#][a-z0-9]{1,31});/gi,
                A = new RegExp(x.source + "|" + y.source, "gi"),
                C = /^#((?:x[a-f0-9]{1,8}|[0-9]{1,8}))/i,
                w = e("./entities"),
                q = /[&<>"]/,
                D = /[&<>"]/g,
                E = {
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;"
                },
                S = /[.?*+^$[\]\\(){}|-]/g,
                F = e("uc.micro/categories/P/regex");
            t.lib = {}, t.lib.mdurl = e("mdurl"), t.lib.ucmicro = e("uc.micro"), t.assign = o, t.isString = s, t.has = i, t.unescapeMd = p, t.unescapeAll = h, t.isValidEntityCode = c, t.fromCodePoint = l, t.escapeHtml = d, t.arrayReplaceAt = a, t.isWhiteSpace = g, t.isMdAsciiPunct = b, t.isPunctChar = _, t.escapeRE = m, t.normalizeReference = k
        }, {
            "./entities": 1,
            mdurl: 58,
            "uc.micro": 64,
            "uc.micro/categories/P/regex": 62
        }],
        6: [function(e, r, t) {
            "use strict";
            t.parseLinkLabel = e("./parse_link_label"), t.parseLinkDestination = e("./parse_link_destination"), t.parseLinkTitle = e("./parse_link_title")
        }, {
            "./parse_link_destination": 7,
            "./parse_link_label": 8,
            "./parse_link_title": 9
        }],
        7: [function(e, r, t) {
            "use strict";
            var n = e("../common/utils").unescapeAll;
            r.exports = function(e, r, t) {
                var s, i, o = 0,
                    a = r,
                    c = {
                        ok: !1,
                        pos: 0,
                        lines: 0,
                        str: ""
                    };
                if (60 === e.charCodeAt(r)) {
                    for (r++; t > r;) {
                        if (s = e.charCodeAt(r), 10 === s) return c;
                        if (62 === s) return c.pos = r + 1, c.str = n(e.slice(a + 1, r)), c.ok = !0, c;
                        92 === s && t > r + 1 ? r += 2 : r++
                    }
                    return c
                }
                for (i = 0; t > r && (s = e.charCodeAt(r), 32 !== s) && !(32 > s || 127 === s);)
                    if (92 === s && t > r + 1) r += 2;
                    else {
                        if (40 === s && (i++, i > 1)) break;
                        if (41 === s && (i--, 0 > i)) break;
                        r++
                    }
                return a === r ? c : (c.str = n(e.slice(a, r)), c.lines = o, c.pos = r, c.ok = !0, c)
            }
        }, {
            "../common/utils": 5
        }],
        8: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r, t) {
                var n, s, i, o, a = -1,
                    c = e.posMax,
                    l = e.pos;
                for (e.pos = r + 1, n = 1; e.pos < c;) {
                    if (i = e.src.charCodeAt(e.pos), 93 === i && (n--, 0 === n)) {
                        s = !0;
                        break
                    }
                    if (o = e.pos, e.md.inline.skipToken(e), 91 === i)
                        if (o === e.pos - 1) n++;
                        else if (t) return e.pos = l, -1
                }
                return s && (a = e.pos), e.pos = l, a
            }
        }, {}],
        9: [function(e, r, t) {
            "use strict";
            var n = e("../common/utils").unescapeAll;
            r.exports = function(e, r, t) {
                var s, i, o = 0,
                    a = r,
                    c = {
                        ok: !1,
                        pos: 0,
                        lines: 0,
                        str: ""
                    };
                if (r >= t) return c;
                if (i = e.charCodeAt(r), 34 !== i && 39 !== i && 40 !== i) return c;
                for (r++, 40 === i && (i = 41); t > r;) {
                    if (s = e.charCodeAt(r), s === i) return c.pos = r + 1, c.lines = o, c.str = n(e.slice(a + 1, r)), c.ok = !0, c;
                    10 === s ? o++ : 92 === s && t > r + 1 && (r++, 10 === e.charCodeAt(r) && o++), r++
                }
                return c
            }
        }, {
            "../common/utils": 5
        }],
        10: [function(e, r, t) {
            "use strict";

            function n(e) {
                var r = e.trim().toLowerCase();
                return _.test(r) ? b.test(r) ? !0 : !1 : !0
            }

            function s(e) {
                var r = d.parse(e, !0);
                if (r.hostname && (!r.protocol || k.indexOf(r.protocol) >= 0)) try {
                    r.hostname = m.toASCII(r.hostname)
                } catch (t) {}
                return d.encode(d.format(r))
            }

            function i(e) {
                var r = d.parse(e, !0);
                if (r.hostname && (!r.protocol || k.indexOf(r.protocol) >= 0)) try {
                    r.hostname = m.toUnicode(r.hostname)
                } catch (t) {}
                return d.decode(d.format(r))
            }

            function o(e, r) {
                return this instanceof o ? (r || a.isString(e) || (r = e || {}, e = "default"), this.inline = new h, this.block = new p, this.core = new u, this.renderer = new l, this.linkify = new f, this.validateLink = n, this.normalizeLink = s, this.normalizeLinkText = i, this.utils = a, this.helpers = c, this.options = {}, this.configure(e), void(r && this.set(r))) : new o(e, r)
            }
            var a = e("./common/utils"),
                c = e("./helpers"),
                l = e("./renderer"),
                u = e("./parser_core"),
                p = e("./parser_block"),
                h = e("./parser_inline"),
                f = e("linkify-it"),
                d = e("mdurl"),
                m = e("punycode"),
                g = {
                    "default": e("./presets/default"),
                    zero: e("./presets/zero"),
                    commonmark: e("./presets/commonmark")
                },
                _ = /^(vbscript|javascript|file|data):/,
                b = /^data:image\/(gif|png|jpeg|webp);/,
                k = ["http:", "https:", "mailto:"];
            o.prototype.set = function(e) {
                return a.assign(this.options, e), this
            }, o.prototype.configure = function(e) {
                var r, t = this;
                if (a.isString(e) && (r = e, e = g[r], !e)) throw new Error('Wrong `markdown-it` preset "' + r + '", check name');
                if (!e) throw new Error("Wrong `markdown-it` preset, can't be empty");
                return e.options && t.set(e.options), e.components && Object.keys(e.components).forEach(function(r) {
                    e.components[r].rules && t[r].ruler.enableOnly(e.components[r].rules)
                }), this
            }, o.prototype.enable = function(e, r) {
                var t = [];
                Array.isArray(e) || (e = [e]), ["core", "block", "inline"].forEach(function(r) {
                    t = t.concat(this[r].ruler.enable(e, !0))
                }, this);
                var n = e.filter(function(e) {
                    return t.indexOf(e) < 0
                });
                if (n.length && !r) throw new Error("MarkdownIt. Failed to enable unknown rule(s): " + n);
                return this
            }, o.prototype.disable = function(e, r) {
                var t = [];
                Array.isArray(e) || (e = [e]), ["core", "block", "inline"].forEach(function(r) {
                    t = t.concat(this[r].ruler.disable(e, !0))
                }, this);
                var n = e.filter(function(e) {
                    return t.indexOf(e) < 0
                });
                if (n.length && !r) throw new Error("MarkdownIt. Failed to disable unknown rule(s): " + n);
                return this
            }, o.prototype.use = function(e) {
                var r = [this].concat(Array.prototype.slice.call(arguments, 1));
                return e.apply(e, r), this
            }, o.prototype.parse = function(e, r) {
                var t = new this.core.State(e, this, r);
                return this.core.process(t), t.tokens
            }, o.prototype.render = function(e, r) {
                return r = r || {}, this.renderer.render(this.parse(e, r), this.options, r)
            }, o.prototype.parseInline = function(e, r) {
                var t = new this.core.State(e, this, r);
                return t.inlineMode = !0, this.core.process(t), t.tokens
            }, o.prototype.renderInline = function(e, r) {
                return r = r || {}, this.renderer.render(this.parseInline(e, r), this.options, r)
            }, r.exports = o
        }, {
            "./common/utils": 5,
            "./helpers": 6,
            "./parser_block": 11,
            "./parser_core": 12,
            "./parser_inline": 13,
            "./presets/commonmark": 14,
            "./presets/default": 15,
            "./presets/zero": 16,
            "./renderer": 17,
            "linkify-it": 53,
            mdurl: 58,
            punycode: 51
        }],
        11: [function(e, r, t) {
            "use strict";

            function n() {
                this.ruler = new s;
                for (var e = 0; e < i.length; e++) this.ruler.push(i[e][0], i[e][1], {
                    alt: (i[e][2] || []).slice()
                })
            }
            var s = e("./ruler"),
                i = [
                    ["code", e("./rules_block/code")],
                    ["fence", e("./rules_block/fence"), ["paragraph", "reference", "blockquote", "list"]],
                    ["blockquote", e("./rules_block/blockquote"), ["paragraph", "reference", "list"]],
                    ["hr", e("./rules_block/hr"), ["paragraph", "reference", "blockquote", "list"]],
                    ["list", e("./rules_block/list"), ["paragraph", "reference", "blockquote"]],
                    ["reference", e("./rules_block/reference")],
                    ["heading", e("./rules_block/heading"), ["paragraph", "reference", "blockquote"]],
                    ["lheading", e("./rules_block/lheading")],
                    ["html_block", e("./rules_block/html_block"), ["paragraph", "reference", "blockquote"]],
                    ["table", e("./rules_block/table"), ["paragraph", "reference"]],
                    ["paragraph", e("./rules_block/paragraph")]
                ];
            n.prototype.tokenize = function(e, r, t) {
                for (var n, s, i = this.ruler.getRules(""), o = i.length, a = r, c = !1, l = e.md.options.maxNesting; t > a && (e.line = a = e.skipEmptyLines(a), !(a >= t)) && !(e.tShift[a] < e.blkIndent);) {
                    if (e.level >= l) {
                        e.line = t;
                        break
                    }
                    for (s = 0; o > s && !(n = i[s](e, a, t, !1)); s++);
                    if (e.tight = !c, e.isEmpty(e.line - 1) && (c = !0), a = e.line, t > a && e.isEmpty(a)) {
                        if (c = !0, a++, t > a && "list" === e.parentType && e.isEmpty(a)) break;
                        e.line = a
                    }
                }
            }, n.prototype.parse = function(e, r, t, n) {
                var s;
                return e ? (s = new this.State(e, r, t, n), void this.tokenize(s, s.line, s.lineMax)) : []
            }, n.prototype.State = e("./rules_block/state_block"), r.exports = n
        }, {
            "./ruler": 18,
            "./rules_block/blockquote": 19,
            "./rules_block/code": 20,
            "./rules_block/fence": 21,
            "./rules_block/heading": 22,
            "./rules_block/hr": 23,
            "./rules_block/html_block": 24,
            "./rules_block/lheading": 25,
            "./rules_block/list": 26,
            "./rules_block/paragraph": 27,
            "./rules_block/reference": 28,
            "./rules_block/state_block": 29,
            "./rules_block/table": 30
        }],
        12: [function(e, r, t) {
            "use strict";

            function n() {
                this.ruler = new s;
                for (var e = 0; e < i.length; e++) this.ruler.push(i[e][0], i[e][1])
            }
            var s = e("./ruler"),
                i = [
                    ["normalize", e("./rules_core/normalize")],
                    ["block", e("./rules_core/block")],
                    ["inline", e("./rules_core/inline")],
                    ["linkify", e("./rules_core/linkify")],
                    ["replacements", e("./rules_core/replacements")],
                    ["smartquotes", e("./rules_core/smartquotes")]
                ];
            n.prototype.process = function(e) {
                var r, t, n;
                for (n = this.ruler.getRules(""), r = 0, t = n.length; t > r; r++) n[r](e)
            }, n.prototype.State = e("./rules_core/state_core"), r.exports = n
        }, {
            "./ruler": 18,
            "./rules_core/block": 31,
            "./rules_core/inline": 32,
            "./rules_core/linkify": 33,
            "./rules_core/normalize": 34,
            "./rules_core/replacements": 35,
            "./rules_core/smartquotes": 36,
            "./rules_core/state_core": 37
        }],
        13: [function(e, r, t) {
            "use strict";

            function n() {
                this.ruler = new s;
                for (var e = 0; e < i.length; e++) this.ruler.push(i[e][0], i[e][1])
            }
            var s = e("./ruler"),
                i = [
                    ["text", e("./rules_inline/text")],
                    ["newline", e("./rules_inline/newline")],
                    ["escape", e("./rules_inline/escape")],
                    ["backticks", e("./rules_inline/backticks")],
                    ["strikethrough", e("./rules_inline/strikethrough")],
                    ["emphasis", e("./rules_inline/emphasis")],
                    ["link", e("./rules_inline/link")],
                    ["image", e("./rules_inline/image")],
                    ["autolink", e("./rules_inline/autolink")],
                    ["html_inline", e("./rules_inline/html_inline")],
                    ["entity", e("./rules_inline/entity")]
                ];
            n.prototype.skipToken = function(e) {
                var r, t = e.pos,
                    n = this.ruler.getRules(""),
                    s = n.length,
                    i = e.md.options.maxNesting,
                    o = e.cache;
                if ("undefined" != typeof o[t]) return void(e.pos = o[t]);
                if (e.level < i)
                    for (r = 0; s > r; r++)
                        if (n[r](e, !0)) return void(o[t] = e.pos);
                e.pos++, o[t] = e.pos
            }, n.prototype.tokenize = function(e) {
                for (var r, t, n = this.ruler.getRules(""), s = n.length, i = e.posMax, o = e.md.options.maxNesting; e.pos < i;) {
                    if (e.level < o)
                        for (t = 0; s > t && !(r = n[t](e, !1)); t++);
                    if (r) {
                        if (e.pos >= i) break
                    } else e.pending += e.src[e.pos++]
                }
                e.pending && e.pushPending()
            }, n.prototype.parse = function(e, r, t, n) {
                var s = new this.State(e, r, t, n);
                this.tokenize(s)
            }, n.prototype.State = e("./rules_inline/state_inline"), r.exports = n
        }, {
            "./ruler": 18,
            "./rules_inline/autolink": 38,
            "./rules_inline/backticks": 39,
            "./rules_inline/emphasis": 40,
            "./rules_inline/entity": 41,
            "./rules_inline/escape": 42,
            "./rules_inline/html_inline": 43,
            "./rules_inline/image": 44,
            "./rules_inline/link": 45,
            "./rules_inline/newline": 46,
            "./rules_inline/state_inline": 47,
            "./rules_inline/strikethrough": 48,
            "./rules_inline/text": 49
        }],
        14: [function(e, r, t) {
            "use strict";
            r.exports = {
                options: {
                    html: !0,
                    xhtmlOut: !0,
                    breaks: !1,
                    langPrefix: "language-",
                    linkify: !1,
                    typographer: !1,
                    quotes: "\u201c\u201d\u2018\u2019",
                    highlight: null,
                    maxNesting: 20
                },
                components: {
                    core: {
                        rules: ["normalize", "block", "inline"]
                    },
                    block: {
                        rules: ["blockquote", "code", "fence", "heading", "hr", "html_block", "lheading", "list", "reference", "paragraph"]
                    },
                    inline: {
                        rules: ["autolink", "backticks", "emphasis", "entity", "escape", "html_inline", "image", "link", "newline", "text"]
                    }
                }
            }
        }, {}],
        15: [function(e, r, t) {
            "use strict";
            r.exports = {
                options: {
                    html: !1,
                    xhtmlOut: !1,
                    breaks: !1,
                    langPrefix: "language-",
                    linkify: !1,
                    typographer: !1,
                    quotes: "\u201c\u201d\u2018\u2019",
                    highlight: null,
                    maxNesting: 20
                },
                components: {
                    core: {},
                    block: {},
                    inline: {}
                }
            }
        }, {}],
        16: [function(e, r, t) {
            "use strict";
            r.exports = {
                options: {
                    html: !1,
                    xhtmlOut: !1,
                    breaks: !1,
                    langPrefix: "language-",
                    linkify: !1,
                    typographer: !1,
                    quotes: "\u201c\u201d\u2018\u2019",
                    highlight: null,
                    maxNesting: 20
                },
                components: {
                    core: {
                        rules: ["normalize", "block", "inline"]
                    },
                    block: {
                        rules: ["paragraph"]
                    },
                    inline: {
                        rules: ["text"]
                    }
                }
            }
        }, {}],
        17: [function(e, r, t) {
            "use strict";

            function n() {
                this.rules = s({}, a)
            }
            var s = e("./common/utils").assign,
                i = e("./common/utils").unescapeAll,
                o = e("./common/utils").escapeHtml,
                a = {};
            a.code_inline = function(e, r) {
                return "<code>" + o(e[r].content) + "</code>"
            }, a.code_block = function(e, r) {
                return "<pre><code>" + o(e[r].content) + "</code></pre>\n"
            }, a.fence = function(e, r, t, n, s) {
                var a, c = e[r],
                    l = "";
                return c.info && (l = i(c.info.trim().split(/\s+/g)[0]), c.attrPush(["class", t.langPrefix + l])), a = t.highlight ? t.highlight(c.content, l) || o(c.content) : o(c.content), "<pre><code" + s.renderAttrs(c) + ">" + a + "</code></pre>\n"
            }, a.image = function(e, r, t, n, s) {
                var i = e[r];
                return i.attrs[i.attrIndex("alt")][1] = s.renderInlineAsText(i.children, t, n), s.renderToken(e, r, t)
            }, a.hardbreak = function(e, r, t) {
                return t.xhtmlOut ? "<br />\n" : "<br>\n"
            }, a.softbreak = function(e, r, t) {
                return t.breaks ? t.xhtmlOut ? "<br />\n" : "<br>\n" : "\n"
            }, a.text = function(e, r) {
                return o(e[r].content)
            }, a.html_block = function(e, r) {
                return e[r].content
            }, a.html_inline = function(e, r) {
                return e[r].content
            }, n.prototype.renderAttrs = function(e) {
                var r, t, n;
                if (!e.attrs) return "";
                for (n = "", r = 0, t = e.attrs.length; t > r; r++) n += " " + o(e.attrs[r][0]) + '="' + o(e.attrs[r][1]) + '"';
                return n
            }, n.prototype.renderToken = function(e, r, t) {
                var n, s = "",
                    i = !1,
                    o = e[r];
                return o.hidden ? "" : (o.block && -1 !== o.nesting && r && e[r - 1].hidden && (s += "\n"), s += (-1 === o.nesting ? "</" : "<") + o.tag, s += this.renderAttrs(o), 0 === o.nesting && t.xhtmlOut && (s += " /"), o.block && (i = !0, 1 === o.nesting && r + 1 < e.length && (n = e[r + 1], "inline" === n.type || n.hidden ? i = !1 : -1 === n.nesting && n.tag === o.tag && (i = !1))), s += i ? ">\n" : ">")
            }, n.prototype.renderInline = function(e, r, t) {
                for (var n, s = "", i = this.rules, o = 0, a = e.length; a > o; o++) n = e[o].type, s += "undefined" != typeof i[n] ? i[n](e, o, r, t, this) : this.renderToken(e, o, r);
                return s
            }, n.prototype.renderInlineAsText = function(e, r, t) {
                for (var n = "", s = this.rules, i = 0, o = e.length; o > i; i++) "text" === e[i].type ? n += s.text(e, i, r, t, this) : "image" === e[i].type && (n += this.renderInlineAsText(e[i].children, r, t));
                return n
            }, n.prototype.render = function(e, r, t) {
                var n, s, i, o = "",
                    a = this.rules;
                for (n = 0, s = e.length; s > n; n++) i = e[n].type, o += "inline" === i ? this.renderInline(e[n].children, r, t) : "undefined" != typeof a[i] ? a[e[n].type](e, n, r, t, this) : this.renderToken(e, n, r, t);
                return o
            }, r.exports = n
        }, {
            "./common/utils": 5
        }],
        18: [function(e, r, t) {
            "use strict";

            function n() {
                this.__rules__ = [], this.__cache__ = null
            }
            n.prototype.__find__ = function(e) {
                for (var r = 0; r < this.__rules__.length; r++)
                    if (this.__rules__[r].name === e) return r;
                return -1
            }, n.prototype.__compile__ = function() {
                var e = this,
                    r = [""];
                e.__rules__.forEach(function(e) {
                    e.enabled && e.alt.forEach(function(e) {
                        r.indexOf(e) < 0 && r.push(e)
                    })
                }), e.__cache__ = {}, r.forEach(function(r) {
                    e.__cache__[r] = [], e.__rules__.forEach(function(t) {
                        t.enabled && (r && t.alt.indexOf(r) < 0 || e.__cache__[r].push(t.fn))
                    })
                })
            }, n.prototype.at = function(e, r, t) {
                var n = this.__find__(e),
                    s = t || {};
                if (-1 === n) throw new Error("Parser rule not found: " + e);
                this.__rules__[n].fn = r, this.__rules__[n].alt = s.alt || [], this.__cache__ = null
            }, n.prototype.before = function(e, r, t, n) {
                var s = this.__find__(e),
                    i = n || {};
                if (-1 === s) throw new Error("Parser rule not found: " + e);
                this.__rules__.splice(s, 0, {
                    name: r,
                    enabled: !0,
                    fn: t,
                    alt: i.alt || []
                }), this.__cache__ = null
            }, n.prototype.after = function(e, r, t, n) {
                var s = this.__find__(e),
                    i = n || {};
                if (-1 === s) throw new Error("Parser rule not found: " + e);
                this.__rules__.splice(s + 1, 0, {
                    name: r,
                    enabled: !0,
                    fn: t,
                    alt: i.alt || []
                }), this.__cache__ = null
            }, n.prototype.push = function(e, r, t) {
                var n = t || {};
                this.__rules__.push({
                    name: e,
                    enabled: !0,
                    fn: r,
                    alt: n.alt || []
                }), this.__cache__ = null
            }, n.prototype.enable = function(e, r) {
                Array.isArray(e) || (e = [e]);
                var t = [];
                return e.forEach(function(e) {
                    var n = this.__find__(e);
                    if (0 > n) {
                        if (r) return;
                        throw new Error("Rules manager: invalid rule name " + e)
                    }
                    this.__rules__[n].enabled = !0, t.push(e)
                }, this), this.__cache__ = null, t
            }, n.prototype.enableOnly = function(e, r) {
                Array.isArray(e) || (e = [e]), this.__rules__.forEach(function(e) {
                    e.enabled = !1
                }), this.enable(e, r)
            }, n.prototype.disable = function(e, r) {
                Array.isArray(e) || (e = [e]);
                var t = [];
                return e.forEach(function(e) {
                    var n = this.__find__(e);
                    if (0 > n) {
                        if (r) return;
                        throw new Error("Rules manager: invalid rule name " + e)
                    }
                    this.__rules__[n].enabled = !1, t.push(e)
                }, this), this.__cache__ = null, t
            }, n.prototype.getRules = function(e) {
                return null === this.__cache__ && this.__compile__(), this.__cache__[e] || []
            }, r.exports = n
        }, {}],
        19: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r, t, n) {
                var s, i, o, a, c, l, u, p, h, f, d, m, g = e.bMarks[r] + e.tShift[r],
                    _ = e.eMarks[r];
                if (62 !== e.src.charCodeAt(g++)) return !1;
                if (n) return !0;
                for (32 === e.src.charCodeAt(g) && g++, c = e.blkIndent, e.blkIndent = 0, a = [e.bMarks[r]], e.bMarks[r] = g, g = _ > g ? e.skipSpaces(g) : g, i = g >= _, o = [e.tShift[r]], e.tShift[r] = g - e.bMarks[r], p = e.md.block.ruler.getRules("blockquote"), s = r + 1; t > s && (g = e.bMarks[s] + e.tShift[s], _ = e.eMarks[s], !(g >= _)); s++)
                    if (62 !== e.src.charCodeAt(g++)) {
                        if (i) break;
                        for (m = !1, f = 0, d = p.length; d > f; f++)
                            if (p[f](e, s, t, !0)) {
                                m = !0;
                                break
                            }
                        if (m) break;
                        a.push(e.bMarks[s]), o.push(e.tShift[s]), e.tShift[s] = -1337
                    } else 32 === e.src.charCodeAt(g) && g++, a.push(e.bMarks[s]), e.bMarks[s] = g, g = _ > g ? e.skipSpaces(g) : g, i = g >= _, o.push(e.tShift[s]), e.tShift[s] = g - e.bMarks[s];
                for (l = e.parentType, e.parentType = "blockquote", h = e.push("blockquote_open", "blockquote", 1), h.markup = ">", h.map = u = [r, 0], e.md.block.tokenize(e, r, s), h = e.push("blockquote_close", "blockquote", -1), h.markup = ">", e.parentType = l, u[1] = e.line, f = 0; f < o.length; f++) e.bMarks[f + r] = a[f], e.tShift[f + r] = o[f];
                return e.blkIndent = c, !0
            }
        }, {}],
        20: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r, t) {
                var n, s, i;
                if (e.tShift[r] - e.blkIndent < 4) return !1;
                for (s = n = r + 1; t > n;)
                    if (e.isEmpty(n)) n++;
                    else {
                        if (!(e.tShift[n] - e.blkIndent >= 4)) break;
                        n++, s = n
                    }
                return e.line = n, i = e.push("code_block", "code", 0), i.content = e.getLines(r, s, 4 + e.blkIndent, !0), i.map = [r, e.line], !0
            }
        }, {}],
        21: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r, t, n) {
                var s, i, o, a, c, l, u, p = !1,
                    h = e.bMarks[r] + e.tShift[r],
                    f = e.eMarks[r];
                if (h + 3 > f) return !1;
                if (s = e.src.charCodeAt(h), 126 !== s && 96 !== s) return !1;
                if (c = h, h = e.skipChars(h, s), i = h - c, 3 > i) return !1;
                if (u = e.src.slice(c, h), o = e.src.slice(h, f), o.indexOf("`") >= 0) return !1;
                if (n) return !0;
                for (a = r;
                    (a++, !(a >= t)) && (h = c = e.bMarks[a] + e.tShift[a], f = e.eMarks[a], !(f > h && e.tShift[a] < e.blkIndent));)
                    if (e.src.charCodeAt(h) === s && !(e.tShift[a] - e.blkIndent >= 4 || (h = e.skipChars(h, s), i > h - c || (h = e.skipSpaces(h), f > h)))) {
                        p = !0;
                        break
                    }
                return i = e.tShift[r], e.line = a + (p ? 1 : 0), l = e.push("fence", "code", 0), l.info = o, l.content = e.getLines(r + 1, a, i, !0), l.markup = u, l.map = [r, e.line], !0
            }
        }, {}],
        22: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r, t, n) {
                var s, i, o, a, c = e.bMarks[r] + e.tShift[r],
                    l = e.eMarks[r];
                if (s = e.src.charCodeAt(c), 35 !== s || c >= l) return !1;
                for (i = 1, s = e.src.charCodeAt(++c); 35 === s && l > c && 6 >= i;) i++, s = e.src.charCodeAt(++c);
                return i > 6 || l > c && 32 !== s ? !1 : n ? !0 : (l = e.skipCharsBack(l, 32, c), o = e.skipCharsBack(l, 35, c), o > c && 32 === e.src.charCodeAt(o - 1) && (l = o), e.line = r + 1, a = e.push("heading_open", "h" + String(i), 1), a.markup = "########".slice(0, i), a.map = [r, e.line], a = e.push("inline", "", 0), a.content = e.src.slice(c, l).trim(), a.map = [r, e.line], a.children = [], a = e.push("heading_close", "h" + String(i), -1), a.markup = "########".slice(0, i), !0)
            }
        }, {}],
        23: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r, t, n) {
                var s, i, o, a, c = e.bMarks[r] + e.tShift[r],
                    l = e.eMarks[r];
                if (s = e.src.charCodeAt(c++), 42 !== s && 45 !== s && 95 !== s) return !1;
                for (i = 1; l > c;) {
                    if (o = e.src.charCodeAt(c++), o !== s && 32 !== o) return !1;
                    o === s && i++
                }
                return 3 > i ? !1 : n ? !0 : (e.line = r + 1, a = e.push("hr", "hr", 0), a.map = [r, e.line], a.markup = Array(i + 1).join(String.fromCharCode(s)), !0)
            }
        }, {}],
        24: [function(e, r, t) {
            "use strict";

            function n(e) {
                var r = 32 | e;
                return r >= 97 && 122 >= r
            }
            var s = e("../common/html_blocks"),
                i = /^<([a-zA-Z][a-zA-Z0-9]{0,14})[\s\/>]/,
                o = /^<\/([a-zA-Z][a-zA-Z0-9]{0,14})[\s>]/;
            r.exports = function(e, r, t, a) {
                var c, l, u, p, h = e.bMarks[r],
                    f = e.eMarks[r],
                    d = e.tShift[r];
                if (h += d, !e.md.options.html) return !1;
                if (d > 3 || h + 2 >= f) return !1;
                if (60 !== e.src.charCodeAt(h)) return !1;
                if (c = e.src.charCodeAt(h + 1), 33 === c || 63 === c) {
                    if (a) return !0
                } else {
                    if (47 !== c && !n(c)) return !1;
                    if (47 === c) {
                        if (l = e.src.slice(h, f).match(o), !l) return !1
                    } else if (l = e.src.slice(h, f).match(i), !l) return !1;
                    if (s[l[1].toLowerCase()] !== !0) return !1;
                    if (a) return !0
                }
                for (u = r + 1; u < e.lineMax && !e.isEmpty(u);) u++;
                return e.line = u, p = e.push("html_block", "", 0), p.map = [r, e.line], p.content = e.getLines(r, u, 0, !0), !0
            }
        }, {
            "../common/html_blocks": 2
        }],
        25: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r, t) {
                var n, s, i, o, a, c = r + 1;
                return c >= t ? !1 : e.tShift[c] < e.blkIndent ? !1 : e.tShift[c] - e.blkIndent > 3 ? !1 : (s = e.bMarks[c] + e.tShift[c], i = e.eMarks[c], s >= i ? !1 : (n = e.src.charCodeAt(s), 45 !== n && 61 !== n ? !1 : (s = e.skipChars(s, n), s = e.skipSpaces(s), i > s ? !1 : (s = e.bMarks[r] + e.tShift[r], e.line = c + 1, a = 61 === n ? 1 : 2, o = e.push("heading_open", "h" + String(a), 1), o.markup = String.fromCharCode(n), o.map = [r, e.line], o = e.push("inline", "", 0), o.content = e.src.slice(s, e.eMarks[r]).trim(), o.map = [r, e.line - 1], o.children = [], o = e.push("heading_close", "h" + String(a), -1), o.markup = String.fromCharCode(n), !0))))
            }
        }, {}],
        26: [function(e, r, t) {
            "use strict";

            function n(e, r) {
                var t, n, s;
                return n = e.bMarks[r] + e.tShift[r], s = e.eMarks[r], t = e.src.charCodeAt(n++), 42 !== t && 45 !== t && 43 !== t ? -1 : s > n && 32 !== e.src.charCodeAt(n) ? -1 : n
            }

            function s(e, r) {
                var t, n = e.bMarks[r] + e.tShift[r],
                    s = e.eMarks[r];
                if (n + 1 >= s) return -1;
                if (t = e.src.charCodeAt(n++), 48 > t || t > 57) return -1;
                for (;;) {
                    if (n >= s) return -1;
                    if (t = e.src.charCodeAt(n++), !(t >= 48 && 57 >= t)) {
                        if (41 === t || 46 === t) break;
                        return -1
                    }
                }
                return s > n && 32 !== e.src.charCodeAt(n) ? -1 : n
            }

            function i(e, r) {
                var t, n, s = e.level + 2;
                for (t = r + 2, n = e.tokens.length - 2; n > t; t++) e.tokens[t].level === s && "paragraph_open" === e.tokens[t].type && (e.tokens[t + 2].hidden = !0, e.tokens[t].hidden = !0, t += 2)
            }
            r.exports = function(e, r, t, o) {
                var a, c, l, u, p, h, f, d, m, g, _, b, k, v, x, y, A, C, w, q, D, E, S, F = !0;
                if ((d = s(e, r)) >= 0) k = !0;
                else {
                    if (!((d = n(e, r)) >= 0)) return !1;
                    k = !1
                }
                if (b = e.src.charCodeAt(d - 1), o) return !0;
                for (x = e.tokens.length, k ? (f = e.bMarks[r] + e.tShift[r], _ = Number(e.src.substr(f, d - f - 1)), q = e.push("ordered_list_open", "ol", 1), _ > 1 && (q.attrs = [
                        ["start", _]
                    ])) : q = e.push("bullet_list_open", "ul", 1), q.map = A = [r, 0], q.markup = String.fromCharCode(b), a = r, y = !1, w = e.md.block.ruler.getRules("list"); !(!(t > a) || (v = e.skipSpaces(d), m = e.eMarks[a], g = v >= m ? 1 : v - d, g > 4 && (g = 1), c = d - e.bMarks[a] + g, q = e.push("list_item_open", "li", 1), q.markup = String.fromCharCode(b), q.map = C = [r, 0], u = e.blkIndent, p = e.tight, l = e.tShift[r], h = e.parentType, e.tShift[r] = v - e.bMarks[r], e.blkIndent = c, e.tight = !0, e.parentType = "list", e.md.block.tokenize(e, r, t, !0), (!e.tight || y) && (F = !1), y = e.line - r > 1 && e.isEmpty(e.line - 1), e.blkIndent = u, e.tShift[r] = l, e.tight = p, e.parentType = h, q = e.push("list_item_close", "li", -1), q.markup = String.fromCharCode(b), a = r = e.line, C[1] = a, v = e.bMarks[r], a >= t) || e.isEmpty(a) || e.tShift[a] < e.blkIndent);) {
                    for (S = !1, D = 0, E = w.length; E > D; D++)
                        if (w[D](e, a, t, !0)) {
                            S = !0;
                            break
                        }
                    if (S) break;
                    if (k) {
                        if (d = s(e, a), 0 > d) break
                    } else if (d = n(e, a), 0 > d) break;
                    if (b !== e.src.charCodeAt(d - 1)) break
                }
                return q = k ? e.push("ordered_list_close", "ol", -1) : e.push("bullet_list_close", "ul", -1), q.markup = String.fromCharCode(b), A[1] = a, e.line = a, F && i(e, x), !0
            }
        }, {}],
        27: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r) {
                for (var t, n, s, i, o, a = r + 1, c = e.md.block.ruler.getRules("paragraph"), l = e.lineMax; l > a && !e.isEmpty(a); a++)
                    if (!(e.tShift[a] - e.blkIndent > 3)) {
                        for (n = !1, s = 0, i = c.length; i > s; s++)
                            if (c[s](e, a, l, !0)) {
                                n = !0;
                                break
                            }
                        if (n) break
                    }
                return t = e.getLines(r, a, e.blkIndent, !1).trim(), e.line = a, o = e.push("paragraph_open", "p", 1), o.map = [r, e.line], o = e.push("inline", "", 0), o.content = t, o.map = [r, e.line], o.children = [], o = e.push("paragraph_close", "p", -1), !0
            }
        }, {}],
        28: [function(e, r, t) {
            "use strict";
            var n = e("../helpers/parse_link_destination"),
                s = e("../helpers/parse_link_title"),
                i = e("../common/utils").normalizeReference;
            r.exports = function(e, r, t, o) {
                var a, c, l, u, p, h, f, d, m, g, _, b, k, v, x, y = 0,
                    A = e.bMarks[r] + e.tShift[r],
                    C = e.eMarks[r],
                    w = r + 1;
                if (91 !== e.src.charCodeAt(A)) return !1;
                for (; ++A < C;)
                    if (93 === e.src.charCodeAt(A) && 92 !== e.src.charCodeAt(A - 1)) {
                        if (A + 1 === C) return !1;
                        if (58 !== e.src.charCodeAt(A + 1)) return !1;
                        break
                    }
                for (u = e.lineMax, v = e.md.block.ruler.getRules("reference"); u > w && !e.isEmpty(w); w++)
                    if (!(e.tShift[w] - e.blkIndent > 3)) {
                        for (k = !1, h = 0, f = v.length; f > h; h++)
                            if (v[h](e, w, u, !0)) {
                                k = !0;
                                break
                            }
                        if (k) break
                    }
                for (b = e.getLines(r, w, e.blkIndent, !1).trim(), C = b.length, A = 1; C > A; A++) {
                    if (a = b.charCodeAt(A), 91 === a) return !1;
                    if (93 === a) {
                        m = A;
                        break
                    }
                    10 === a ? y++ : 92 === a && (A++, C > A && 10 === b.charCodeAt(A) && y++)
                }
                if (0 > m || 58 !== b.charCodeAt(m + 1)) return !1;
                for (A = m + 2; C > A; A++)
                    if (a = b.charCodeAt(A), 10 === a) y++;
                    else if (32 !== a) break;
                if (g = n(b, A, C), !g.ok) return !1;
                if (p = e.md.normalizeLink(g.str), !e.md.validateLink(p)) return !1;
                for (A = g.pos, y += g.lines, c = A, l = y, _ = A; C > A; A++)
                    if (a = b.charCodeAt(A), 10 === a) y++;
                    else if (32 !== a) break;
                for (g = s(b, A, C), C > A && _ !== A && g.ok ? (x = g.str, A = g.pos, y += g.lines) : (x = "", A = c, y = l); C > A && 32 === b.charCodeAt(A);) A++;
                return C > A && 10 !== b.charCodeAt(A) ? !1 : o ? !0 : (d = i(b.slice(1, m)), "undefined" == typeof e.env.references && (e.env.references = {}), "undefined" == typeof e.env.references[d] && (e.env.references[d] = {
                    title: x,
                    href: p
                }), e.line = r + y + 1, !0)
            }
        }, {
            "../common/utils": 5,
            "../helpers/parse_link_destination": 7,
            "../helpers/parse_link_title": 9
        }],
        29: [function(e, r, t) {
            "use strict";

            function n(e, r, t, n) {
                var s, i, o, a, c, l, u;
                for (this.src = e, this.md = r, this.env = t, this.tokens = n, this.bMarks = [], this.eMarks = [], this.tShift = [], this.blkIndent = 0, this.line = 0, this.lineMax = 0, this.tight = !1, this.parentType = "root", this.ddIndent = -1, this.level = 0, this.result = "", i = this.src, l = 0, u = !1, o = a = l = 0, c = i.length; c > a; a++) {
                    if (s = i.charCodeAt(a), !u) {
                        if (32 === s) {
                            l++;
                            continue
                        }
                        u = !0
                    }(10 === s || a === c - 1) && (10 !== s && a++, this.bMarks.push(o), this.eMarks.push(a), this.tShift.push(l), u = !1, l = 0, o = a + 1)
                }
                this.bMarks.push(i.length), this.eMarks.push(i.length), this.tShift.push(0), this.lineMax = this.bMarks.length - 1
            }
            var s = e("../token");
            n.prototype.push = function(e, r, t) {
                var n = new s(e, r, t);
                return n.block = !0, 0 > t && this.level--, n.level = this.level, t > 0 && this.level++, this.tokens.push(n), n
            }, n.prototype.isEmpty = function(e) {
                return this.bMarks[e] + this.tShift[e] >= this.eMarks[e]
            }, n.prototype.skipEmptyLines = function(e) {
                for (var r = this.lineMax; r > e && !(this.bMarks[e] + this.tShift[e] < this.eMarks[e]); e++);
                return e
            }, n.prototype.skipSpaces = function(e) {
                for (var r = this.src.length; r > e && 32 === this.src.charCodeAt(e); e++);
                return e
            }, n.prototype.skipChars = function(e, r) {
                for (var t = this.src.length; t > e && this.src.charCodeAt(e) === r; e++);
                return e
            }, n.prototype.skipCharsBack = function(e, r, t) {
                if (t >= e) return e;
                for (; e > t;)
                    if (r !== this.src.charCodeAt(--e)) return e + 1;
                return e
            }, n.prototype.getLines = function(e, r, t, n) {
                var s, i, o, a, c, l = e;
                if (e >= r) return "";
                if (l + 1 === r) return i = this.bMarks[l] + Math.min(this.tShift[l], t), o = n ? this.bMarks[r] : this.eMarks[r - 1], this.src.slice(i, o);
                for (a = new Array(r - e), s = 0; r > l; l++, s++) c = this.tShift[l], c > t && (c = t), 0 > c && (c = 0), i = this.bMarks[l] + c, o = r > l + 1 || n ? this.eMarks[l] + 1 : this.eMarks[l], a[s] = this.src.slice(i, o);
                return a.join("")
            }, n.prototype.Token = s, r.exports = n
        }, {
            "../token": 50
        }],
        30: [function(e, r, t) {
            "use strict";

            function n(e, r) {
                var t = e.bMarks[r] + e.blkIndent,
                    n = e.eMarks[r];
                return e.src.substr(t, n - t)
            }

            function s(e) {
                var r, t = [],
                    n = 0,
                    s = e.length,
                    i = 0,
                    o = 0,
                    a = !1,
                    c = 0;
                for (r = e.charCodeAt(n); s > n;) 96 === r && i % 2 === 0 ? (a = !a, c = n) : 124 !== r || i % 2 !== 0 || a ? 92 === r ? i++ : i = 0 : (t.push(e.substring(o, n)), o = n + 1), n++, n === s && a && (a = !1, n = c + 1), r = e.charCodeAt(n);
                return t.push(e.substring(o)), t
            }
            r.exports = function(e, r, t, i) {
                var o, a, c, l, u, p, h, f, d, m, g;
                if (r + 2 > t) return !1;
                if (u = r + 1, e.tShift[u] < e.blkIndent) return !1;
                if (c = e.bMarks[u] + e.tShift[u], c >= e.eMarks[u]) return !1;
                if (o = e.src.charCodeAt(c), 124 !== o && 45 !== o && 58 !== o) return !1;
                if (a = n(e, r + 1), !/^[-:| ]+$/.test(a)) return !1;
                if (p = a.split("|"), p.length < 2) return !1;
                for (f = [], l = 0; l < p.length; l++) {
                    if (d = p[l].trim(), !d) {
                        if (0 === l || l === p.length - 1) continue;
                        return !1
                    }
                    if (!/^:?-+:?$/.test(d)) return !1;
                    f.push(58 === d.charCodeAt(d.length - 1) ? 58 === d.charCodeAt(0) ? "center" : "right" : 58 === d.charCodeAt(0) ? "left" : "")
                }
                if (a = n(e, r).trim(), -1 === a.indexOf("|")) return !1;
                if (p = s(a.replace(/^\||\|$/g, "")), f.length !== p.length) return !1;
                if (i) return !0;
                for (h = e.push("table_open", "table", 1), h.map = m = [r, 0], h = e.push("thead_open", "thead", 1), h.map = [r, r + 1], h = e.push("tr_open", "tr", 1), h.map = [r, r + 1], l = 0; l < p.length; l++) h = e.push("th_open", "th", 1), h.map = [r, r + 1], f[l] && (h.attrs = [
                    ["style", "text-align:" + f[l]]
                ]), h = e.push("inline", "", 0), h.content = p[l].trim(), h.map = [r, r + 1], h.children = [], h = e.push("th_close", "th", -1);
                for (h = e.push("tr_close", "tr", -1), h = e.push("thead_close", "thead", -1), h = e.push("tbody_open", "tbody", 1), h.map = g = [r + 2, 0], u = r + 2; t > u && !(e.tShift[u] < e.blkIndent) && (a = n(e, u).trim(), -1 !== a.indexOf("|")); u++) {
                    for (p = s(a.replace(/^\||\|$/g, "")), p.length = f.length, h = e.push("tr_open", "tr", 1), l = 0; l < p.length; l++) h = e.push("td_open", "td", 1), f[l] && (h.attrs = [
                        ["style", "text-align:" + f[l]]
                    ]), h = e.push("inline", "", 0), h.content = p[l] ? p[l].trim() : "", h.children = [], h = e.push("td_close", "td", -1);
                    h = e.push("tr_close", "tr", -1)
                }
                return h = e.push("tbody_close", "tbody", -1), h = e.push("table_close", "table", -1), m[1] = g[1] = u, e.line = u, !0
            }
        }, {}],
        31: [function(e, r, t) {
            "use strict";
            r.exports = function(e) {
                var r;
                e.inlineMode ? (r = new e.Token("inline", "", 0), r.content = e.src, r.map = [0, 1], r.children = [], e.tokens.push(r)) : e.md.block.parse(e.src, e.md, e.env, e.tokens)
            }
        }, {}],
        32: [function(e, r, t) {
            "use strict";
            r.exports = function(e) {
                var r, t, n, s = e.tokens;
                for (t = 0, n = s.length; n > t; t++) r = s[t], "inline" === r.type && e.md.inline.parse(r.content, e.md, e.env, r.children)
            }
        }, {}],
        33: [function(e, r, t) {
            "use strict";

            function n(e) {
                return /^<a[>\s]/i.test(e)
            }

            function s(e) {
                return /^<\/a\s*>/i.test(e)
            }
            var i = e("../common/utils").arrayReplaceAt;
            r.exports = function(e) {
                var r, t, o, a, c, l, u, p, h, f, d, m, g, _, b, k, v, x = e.tokens;
                if (e.md.options.linkify)
                    for (t = 0, o = x.length; o > t; t++)
                        if ("inline" === x[t].type && e.md.linkify.pretest(x[t].content))
                            for (a = x[t].children, g = 0, r = a.length - 1; r >= 0; r--)
                                if (l = a[r], "link_close" !== l.type) {
                                    if ("html_inline" === l.type && (n(l.content) && g > 0 && g--, s(l.content) && g++), !(g > 0) && "text" === l.type && e.md.linkify.test(l.content)) {
                                        for (h = l.content, v = e.md.linkify.match(h), u = [], m = l.level, d = 0, p = 0; p < v.length; p++) _ = v[p].url, b = e.md.normalizeLink(_), e.md.validateLink(b) && (k = v[p].text, k = v[p].schema ? "mailto:" !== v[p].schema || /^mailto:/i.test(k) ? e.md.normalizeLinkText(k) : e.md.normalizeLinkText("mailto:" + k).replace(/^mailto:/, "") : e.md.normalizeLinkText("http://" + k).replace(/^http:\/\//, ""), f = v[p].index, f > d && (c = new e.Token("text", "", 0), c.content = h.slice(d, f), c.level = m, u.push(c)), c = new e.Token("link_open", "a", 1), c.attrs = [
                                            ["href", b]
                                        ], c.level = m++, c.markup = "linkify", c.info = "auto", u.push(c), c = new e.Token("text", "", 0), c.content = k, c.level = m, u.push(c), c = new e.Token("link_close", "a", -1), c.level = --m, c.markup = "linkify", c.info = "auto", u.push(c), d = v[p].lastIndex);
                                        d < h.length && (c = new e.Token("text", "", 0), c.content = h.slice(d), c.level = m, u.push(c)), x[t].children = a = i(a, r, u)
                                    }
                                } else
                                    for (r--; a[r].level !== l.level && "link_open" !== a[r].type;) r--
            }
        }, {
            "../common/utils": 5
        }],
        34: [function(e, r, t) {
            "use strict";
            var n = /[\n\t]/g,
                s = /\r[\n\u0085]|[\u2424\u2028\u0085]/g,
                i = /\u0000/g;
            r.exports = function(e) {
                var r, t, o;
                r = e.src.replace(s, "\n"), r = r.replace(i, "\ufffd"), r.indexOf("  ") >= 0 && (t = 0, o = 0, r = r.replace(n, function(e, n) {
                    var s;
                    return 10 === r.charCodeAt(n) ? (t = n + 1, o = 0, e) : (s = "    ".slice((n - t - o) % 4), o = n - t + 1, s)
                })), e.src = r
            }
        }, {}],
        35: [function(e, r, t) {
            "use strict";

            function n(e, r) {
                return l[r.toLowerCase()]
            }

            function s(e) {
                var r, t;
                for (r = e.length - 1; r >= 0; r--) t = e[r], "text" === t.type && (t.content = t.content.replace(c, n))
            }

            function i(e) {
                var r, t;
                for (r = e.length - 1; r >= 0; r--) t = e[r], "text" === t.type && o.test(t.content) && (t.content = t.content.replace(/\+-/g, "\xb1").replace(/\.{2,}/g, "\u2026").replace(/([?!])\u2026/g, "$1..").replace(/([?!]){4,}/g, "$1$1$1").replace(/,{2,}/g, ",").replace(/(^|[^-])---([^-]|$)/gm, "$1\u2014$2").replace(/(^|\s)--(\s|$)/gm, "$1\u2013$2").replace(/(^|[^-\s])--([^-\s]|$)/gm, "$1\u2013$2"))
            }
            var o = /\+-|\.\.|\?\?\?\?|!!!!|,,|--/,
                a = /\((c|tm|r|p)\)/i,
                c = /\((c|tm|r|p)\)/gi,
                l = {
                    c: "\xa9",
                    r: "\xae",
                    p: "\xa7",
                    tm: "\u2122"
                };
            r.exports = function(e) {
                var r;
                if (e.md.options.typographer)
                    for (r = e.tokens.length - 1; r >= 0; r--) "inline" === e.tokens[r].type && (a.test(e.tokens[r].content) && s(e.tokens[r].children), o.test(e.tokens[r].content) && i(e.tokens[r].children))
            }
        }, {}],
        36: [function(e, r, t) {
            "use strict";

            function n(e, r, t) {
                return e.substr(0, r) + t + e.substr(r + 1)
            }

            function s(e, r) {
                var t, s, c, p, h, f, d, m, g, _, b, k, v, x, y, A, C, w, q;
                for (q = [], t = 0; t < e.length; t++) {
                    for (s = e[t], d = e[t].level, C = q.length - 1; C >= 0 && !(q[C].level <= d); C--);
                    if (q.length = C + 1, "text" === s.type) {
                        c = s.content, h = 0, f = c.length;
                        e: for (; f > h && (l.lastIndex = h, p = l.exec(c));)
                            if (y = A = !0, h = p.index + 1, w = "'" === p[0], g = p.index - 1 >= 0 ? c.charCodeAt(p.index - 1) : 32, _ = f > h ? c.charCodeAt(h) : 32, b = a(g) || o(String.fromCharCode(g)), k = a(_) || o(String.fromCharCode(_)), v = i(g), x = i(_), x ? y = !1 : k && (v || b || (y = !1)), v ? A = !1 : b && (x || k || (A = !1)), 34 === _ && '"' === p[0] && g >= 48 && 57 >= g && (A = y = !1), y && A && (y = !1, A = k), y || A) {
                                if (A)
                                    for (C = q.length - 1; C >= 0 && (m = q[C], !(q[C].level < d)); C--)
                                        if (m.single === w && q[C].level === d) {
                                            m = q[C], w ? (e[m.token].content = n(e[m.token].content, m.pos, r.md.options.quotes[2]), s.content = n(s.content, p.index, r.md.options.quotes[3])) : (e[m.token].content = n(e[m.token].content, m.pos, r.md.options.quotes[0]), s.content = n(s.content, p.index, r.md.options.quotes[1])), q.length = C;
                                            continue e
                                        }
                                y ? q.push({
                                    token: t,
                                    pos: p.index,
                                    single: w,
                                    level: d
                                }) : A && w && (s.content = n(s.content, p.index, u))
                            } else w && (s.content = n(s.content, p.index, u))
                    }
                }
            }
            var i = e("../common/utils").isWhiteSpace,
                o = e("../common/utils").isPunctChar,
                a = e("../common/utils").isMdAsciiPunct,
                c = /['"]/,
                l = /['"]/g,
                u = "\u2019";
            r.exports = function(e) {
                var r;
                if (e.md.options.typographer)
                    for (r = e.tokens.length - 1; r >= 0; r--) "inline" === e.tokens[r].type && c.test(e.tokens[r].content) && s(e.tokens[r].children, e)
            }
        }, {
            "../common/utils": 5
        }],
        37: [function(e, r, t) {
            "use strict";

            function n(e, r, t) {
                this.src = e, this.env = t, this.tokens = [], this.inlineMode = !1, this.md = r
            }
            var s = e("../token");
            n.prototype.Token = s, r.exports = n
        }, {
            "../token": 50
        }],
        38: [function(e, r, t) {
            "use strict";
            var n = e("../common/url_schemas"),
                s = /^<([a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*)>/,
                i = /^<([a-zA-Z.\-]{1,25}):([^<>\x00-\x20]*)>/;
            r.exports = function(e, r) {
                var t, o, a, c, l, u, p = e.pos;
                return 60 !== e.src.charCodeAt(p) ? !1 : (t = e.src.slice(p), t.indexOf(">") < 0 ? !1 : i.test(t) ? (o = t.match(i), n.indexOf(o[1].toLowerCase()) < 0 ? !1 : (c = o[0].slice(1, -1), l = e.md.normalizeLink(c), e.md.validateLink(l) ? (r || (u = e.push("link_open", "a", 1), u.attrs = [
                    ["href", l]
                ], u = e.push("text", "", 0), u.content = e.md.normalizeLinkText(c), u = e.push("link_close", "a", -1)), e.pos += o[0].length, !0) : !1)) : s.test(t) ? (a = t.match(s), c = a[0].slice(1, -1), l = e.md.normalizeLink("mailto:" + c), e.md.validateLink(l) ? (r || (u = e.push("link_open", "a", 1), u.attrs = [
                    ["href", l]
                ], u.markup = "autolink", u.info = "auto", u = e.push("text", "", 0), u.content = e.md.normalizeLinkText(c), u = e.push("link_close", "a", -1), u.markup = "autolink", u.info = "auto"), e.pos += a[0].length, !0) : !1) : !1)
            }
        }, {
            "../common/url_schemas": 4
        }],
        39: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r) {
                var t, n, s, i, o, a, c = e.pos,
                    l = e.src.charCodeAt(c);
                if (96 !== l) return !1;
                for (t = c, c++, n = e.posMax; n > c && 96 === e.src.charCodeAt(c);) c++;
                for (s = e.src.slice(t, c), i = o = c; - 1 !== (i = e.src.indexOf("`", o));) {
                    for (o = i + 1; n > o && 96 === e.src.charCodeAt(o);) o++;
                    if (o - i === s.length) return r || (a = e.push("code_inline", "code", 0), a.markup = s, a.content = e.src.slice(c, i).replace(/[ \n]+/g, " ").trim()), e.pos = o, !0
                }
                return r || (e.pending += s), e.pos += s.length, !0
            }
        }, {}],
        40: [function(e, r, t) {
            "use strict";

            function n(e, r) {
                var t, n, a, c, l, u, p, h, f, d = r,
                    m = !0,
                    g = !0,
                    _ = e.posMax,
                    b = e.src.charCodeAt(r);
                for (t = r > 0 ? e.src.charCodeAt(r - 1) : 32; _ > d && e.src.charCodeAt(d) === b;) d++;
                return a = d - r, n = _ > d ? e.src.charCodeAt(d) : 32, p = o(t) || i(String.fromCharCode(t)), f = o(n) || i(String.fromCharCode(n)), u = s(t), h = s(n), h ? m = !1 : f && (u || p || (m = !1)), u ? g = !1 : p && (h || f || (g = !1)), 95 === b ? (c = m && (!g || p), l = g && (!m || f)) : (c = m, l = g), {
                    can_open: c,
                    can_close: l,
                    delims: a
                }
            }
            var s = e("../common/utils").isWhiteSpace,
                i = e("../common/utils").isPunctChar,
                o = e("../common/utils").isMdAsciiPunct;
            r.exports = function(e, r) {
                var t, s, i, o, a, c, l, u, p = e.posMax,
                    h = e.pos,
                    f = e.src.charCodeAt(h);
                if (95 !== f && 42 !== f) return !1;
                if (r) return !1;
                if (l = n(e, h), t = l.delims, !l.can_open) return e.pos += t, e.pending += e.src.slice(h, e.pos), !0;
                for (e.pos = h + t, c = [t]; e.pos < p;)
                    if (e.src.charCodeAt(e.pos) !== f) e.md.inline.skipToken(e);
                    else {
                        if (l = n(e, e.pos), s = l.delims, l.can_close) {
                            for (o = c.pop(), a = s; o !== a;) {
                                if (o > a) {
                                    c.push(o - a);
                                    break
                                }
                                if (a -= o, 0 === c.length) break;
                                e.pos += o, o = c.pop()
                            }
                            if (0 === c.length) {
                                t = o, i = !0;
                                break
                            }
                            e.pos += s;
                            continue
                        }
                        l.can_open && c.push(s), e.pos += s
                    }
                if (!i) return e.pos = h, !1;
                for (e.posMax = e.pos, e.pos = h + t, s = t; s > 1; s -= 2) u = e.push("strong_open", "strong", 1), u.markup = String.fromCharCode(f) + String.fromCharCode(f);
                for (s % 2 && (u = e.push("em_open", "em", 1), u.markup = String.fromCharCode(f)), e.md.inline.tokenize(e), s % 2 && (u = e.push("em_close", "em", -1), u.markup = String.fromCharCode(f)), s = t; s > 1; s -= 2) u = e.push("strong_close", "strong", -1), u.markup = String.fromCharCode(f) + String.fromCharCode(f);
                return e.pos = e.posMax + t, e.posMax = p, !0
            }
        }, {
            "../common/utils": 5
        }],
        41: [function(e, r, t) {
            "use strict";
            var n = e("../common/entities"),
                s = e("../common/utils").has,
                i = e("../common/utils").isValidEntityCode,
                o = e("../common/utils").fromCodePoint,
                a = /^&#((?:x[a-f0-9]{1,8}|[0-9]{1,8}));/i,
                c = /^&([a-z][a-z0-9]{1,31});/i;
            r.exports = function(e, r) {
                var t, l, u, p = e.pos,
                    h = e.posMax;
                if (38 !== e.src.charCodeAt(p)) return !1;
                if (h > p + 1)
                    if (t = e.src.charCodeAt(p + 1), 35 === t) {
                        if (u = e.src.slice(p).match(a)) return r || (l = "x" === u[1][0].toLowerCase() ? parseInt(u[1].slice(1), 16) : parseInt(u[1], 10), e.pending += o(i(l) ? l : 65533)), e.pos += u[0].length, !0
                    } else if (u = e.src.slice(p).match(c), u && s(n, u[1])) return r || (e.pending += n[u[1]]), e.pos += u[0].length, !0;
                return r || (e.pending += "&"), e.pos++, !0
            }
        }, {
            "../common/entities": 1,
            "../common/utils": 5
        }],
        42: [function(e, r, t) {
            "use strict";
            for (var n = [], s = 0; 256 > s; s++) n.push(0);
            "\\!\"#$%&'()*+,./:;<=>?@[]^_`{|}~-".split("").forEach(function(e) {
                n[e.charCodeAt(0)] = 1
            }), r.exports = function(e, r) {
                var t, s = e.pos,
                    i = e.posMax;
                if (92 !== e.src.charCodeAt(s)) return !1;
                if (s++, i > s) {
                    if (t = e.src.charCodeAt(s), 256 > t && 0 !== n[t]) return r || (e.pending += e.src[s]), e.pos += 2, !0;
                    if (10 === t) {
                        for (r || e.push("hardbreak", "br", 0), s++; i > s && 32 === e.src.charCodeAt(s);) s++;
                        return e.pos = s, !0
                    }
                }
                return r || (e.pending += "\\"), e.pos++, !0
            }
        }, {}],
        43: [function(e, r, t) {
            "use strict";

            function n(e) {
                var r = 32 | e;
                return r >= 97 && 122 >= r
            }
            var s = e("../common/html_re").HTML_TAG_RE;
            r.exports = function(e, r) {
                var t, i, o, a, c = e.pos;
                return e.md.options.html ? (o = e.posMax, 60 !== e.src.charCodeAt(c) || c + 2 >= o ? !1 : (t = e.src.charCodeAt(c + 1), (33 === t || 63 === t || 47 === t || n(t)) && (i = e.src.slice(c).match(s)) ? (r || (a = e.push("html_inline", "", 0), a.content = e.src.slice(c, c + i[0].length)), e.pos += i[0].length, !0) : !1)) : !1
            }
        }, {
            "../common/html_re": 3
        }],
        44: [function(e, r, t) {
            "use strict";
            var n = e("../helpers/parse_link_label"),
                s = e("../helpers/parse_link_destination"),
                i = e("../helpers/parse_link_title"),
                o = e("../common/utils").normalizeReference;
            r.exports = function(e, r) {
                var t, a, c, l, u, p, h, f, d, m, g, _, b = "",
                    k = e.pos,
                    v = e.posMax;
                if (33 !== e.src.charCodeAt(e.pos)) return !1;
                if (91 !== e.src.charCodeAt(e.pos + 1)) return !1;
                if (u = e.pos + 2, l = n(e, e.pos + 1, !1), 0 > l) return !1;
                if (p = l + 1, v > p && 40 === e.src.charCodeAt(p)) {
                    for (p++; v > p && (a = e.src.charCodeAt(p), 32 === a || 10 === a); p++);
                    if (p >= v) return !1;
                    for (_ = p, f = s(e.src, p, e.posMax), f.ok && (b = e.md.normalizeLink(f.str), e.md.validateLink(b) ? p = f.pos : b = ""), _ = p; v > p && (a = e.src.charCodeAt(p), 32 === a || 10 === a); p++);
                    if (f = i(e.src, p, e.posMax), v > p && _ !== p && f.ok)
                        for (d = f.str, p = f.pos; v > p && (a = e.src.charCodeAt(p), 32 === a || 10 === a); p++);
                    else d = "";
                    if (p >= v || 41 !== e.src.charCodeAt(p)) return e.pos = k, !1;
                    p++
                } else {
                    if ("undefined" == typeof e.env.references) return !1;
                    for (; v > p && (a = e.src.charCodeAt(p), 32 === a || 10 === a); p++);
                    if (v > p && 91 === e.src.charCodeAt(p) ? (_ = p + 1, p = n(e, p), p >= 0 ? c = e.src.slice(_, p++) : p = l + 1) : p = l + 1, c || (c = e.src.slice(u, l)), h = e.env.references[o(c)], !h) return e.pos = k, !1;
                    b = h.href, d = h.title
                }
                if (!r) {
                    e.pos = u, e.posMax = l;
                    var x = new e.md.inline.State(e.src.slice(u, l), e.md, e.env, g = []);
                    x.md.inline.tokenize(x), m = e.push("image", "img", 0), m.attrs = t = [
                        ["src", b],
                        ["alt", ""]
                    ], m.children = g, d && t.push(["title", d])
                }
                return e.pos = p, e.posMax = v, !0
            }
        }, {
            "../common/utils": 5,
            "../helpers/parse_link_destination": 7,
            "../helpers/parse_link_label": 8,
            "../helpers/parse_link_title": 9
        }],
        45: [function(e, r, t) {
            "use strict";
            var n = e("../helpers/parse_link_label"),
                s = e("../helpers/parse_link_destination"),
                i = e("../helpers/parse_link_title"),
                o = e("../common/utils").normalizeReference;
            r.exports = function(e, r) {
                var t, a, c, l, u, p, h, f, d, m, g = "",
                    _ = e.pos,
                    b = e.posMax,
                    k = e.pos;
                if (91 !== e.src.charCodeAt(e.pos)) return !1;
                if (u = e.pos + 1, l = n(e, e.pos, !0), 0 > l) return !1;
                if (p = l + 1, b > p && 40 === e.src.charCodeAt(p)) {
                    for (p++; b > p && (a = e.src.charCodeAt(p), 32 === a || 10 === a); p++);
                    if (p >= b) return !1;
                    for (k = p, h = s(e.src, p, e.posMax), h.ok && (g = e.md.normalizeLink(h.str), e.md.validateLink(g) ? p = h.pos : g = ""), k = p; b > p && (a = e.src.charCodeAt(p), 32 === a || 10 === a); p++);
                    if (h = i(e.src, p, e.posMax), b > p && k !== p && h.ok)
                        for (d = h.str, p = h.pos; b > p && (a = e.src.charCodeAt(p), 32 === a || 10 === a); p++);
                    else d = "";
                    if (p >= b || 41 !== e.src.charCodeAt(p)) return e.pos = _, !1;
                    p++
                } else {
                    if ("undefined" == typeof e.env.references) return !1;
                    for (; b > p && (a = e.src.charCodeAt(p), 32 === a || 10 === a); p++);
                    if (b > p && 91 === e.src.charCodeAt(p) ? (k = p + 1, p = n(e, p), p >= 0 ? c = e.src.slice(k, p++) : p = l + 1) : p = l + 1, c || (c = e.src.slice(u, l)), f = e.env.references[o(c)], !f) return e.pos = _, !1;
                    g = f.href, d = f.title
                }
                return r || (e.pos = u, e.posMax = l, m = e.push("link_open", "a", 1), m.attrs = t = [
                    ["href", g]
                ], d && t.push(["title", d]), e.md.inline.tokenize(e), m = e.push("link_close", "a", -1)), e.pos = p, e.posMax = b, !0
            }
        }, {
            "../common/utils": 5,
            "../helpers/parse_link_destination": 7,
            "../helpers/parse_link_label": 8,
            "../helpers/parse_link_title": 9
        }],
        46: [function(e, r, t) {
            "use strict";
            r.exports = function(e, r) {
                var t, n, s = e.pos;
                if (10 !== e.src.charCodeAt(s)) return !1;
                for (t = e.pending.length - 1, n = e.posMax, r || (t >= 0 && 32 === e.pending.charCodeAt(t) ? t >= 1 && 32 === e.pending.charCodeAt(t - 1) ? (e.pending = e.pending.replace(/ +$/, ""), e.push("hardbreak", "br", 0)) : (e.pending = e.pending.slice(0, -1), e.push("softbreak", "br", 0)) : e.push("softbreak", "br", 0)), s++; n > s && 32 === e.src.charCodeAt(s);) s++;
                return e.pos = s, !0
            }
        }, {}],
        47: [function(e, r, t) {
            "use strict";

            function n(e, r, t, n) {
                this.src = e, this.env = t, this.md = r, this.tokens = n, this.pos = 0, this.posMax = this.src.length, this.level = 0, this.pending = "", this.pendingLevel = 0, this.cache = {}
            }
            var s = e("../token");
            n.prototype.pushPending = function() {
                var e = new s("text", "", 0);
                return e.content = this.pending, e.level = this.pendingLevel, this.tokens.push(e), this.pending = "", e
            }, n.prototype.push = function(e, r, t) {
                this.pending && this.pushPending();
                var n = new s(e, r, t);
                return 0 > t && this.level--, n.level = this.level, t > 0 && this.level++, this.pendingLevel = this.level, this.tokens.push(n), n
            }, n.prototype.Token = s, r.exports = n
        }, {
            "../token": 50
        }],
        48: [function(e, r, t) {
            "use strict";

            function n(e, r) {
                var t, n, a, c, l, u, p, h = r,
                    f = !0,
                    d = !0,
                    m = e.posMax,
                    g = e.src.charCodeAt(r);
                for (t = r > 0 ? e.src.charCodeAt(r - 1) : 32; m > h && e.src.charCodeAt(h) === g;) h++;
                return h >= m && (f = !1), a = h - r, n = m > h ? e.src.charCodeAt(h) : 32, l = o(t) || i(String.fromCharCode(t)), p = o(n) || i(String.fromCharCode(n)), c = s(t), u = s(n), u ? f = !1 : p && (c || l || (f = !1)), c ? d = !1 : l && (u || p || (d = !1)), {
                    can_open: f,
                    can_close: d,
                    delims: a
                }
            }
            var s = e("../common/utils").isWhiteSpace,
                i = e("../common/utils").isPunctChar,
                o = e("../common/utils").isMdAsciiPunct;
            r.exports = function(e, r) {
                var t, s, i, o, a, c, l, u = e.posMax,
                    p = e.pos,
                    h = e.src.charCodeAt(p);
                if (126 !== h) return !1;
                if (r) return !1;
                if (c = n(e, p), t = c.delims, !c.can_open) return e.pos += t, e.pending += e.src.slice(p, e.pos), !0;
                if (a = Math.floor(t / 2), 0 >= a) return !1;
                for (e.pos = p + t; e.pos < u;)
                    if (e.src.charCodeAt(e.pos) !== h) e.md.inline.skipToken(e);
                    else {
                        if (c = n(e, e.pos), s = c.delims, i = Math.floor(s / 2), c.can_close) {
                            if (i >= a) {
                                e.pos += s - 2, o = !0;
                                break
                            }
                            a -= i, e.pos += s;
                            continue
                        }
                        c.can_open && (a += i), e.pos += s
                    }
                return o ? (e.posMax = e.pos, e.pos = p + 2, l = e.push("s_open", "s", 1), l.markup = "~~", e.md.inline.tokenize(e), l = e.push("s_close", "s", -1), l.markup = "~~", e.pos = e.posMax + 2, e.posMax = u, !0) : (e.pos = p, !1)
            }
        }, {
            "../common/utils": 5
        }],
        49: [function(e, r, t) {
            "use strict";

            function n(e) {
                switch (e) {
                    case 10:
                    case 33:
                    case 35:
                    case 36:
                    case 37:
                    case 38:
                    case 42:
                    case 43:
                    case 45:
                    case 58:
                    case 60:
                    case 61:
                    case 62:
                    case 64:
                    case 91:
                    case 92:
                    case 93:
                    case 94:
                    case 95:
                    case 96:
                    case 123:
                    case 125:
                    case 126:
                        return !0;
                    default:
                        return !1
                }
            }
            r.exports = function(e, r) {
                for (var t = e.pos; t < e.posMax && !n(e.src.charCodeAt(t));) t++;
                return t === e.pos ? !1 : (r || (e.pending += e.src.slice(e.pos, t)), e.pos = t, !0)
            }
        }, {}],
        50: [function(e, r, t) {
            "use strict";

            function n(e, r, t) {
                this.type = e, this.tag = r, this.attrs = null, this.map = null, this.nesting = t, this.level = 0, this.children = null, this.content = "", this.markup = "", this.info = "", this.meta = null, this.block = !1, this.hidden = !1
            }
            n.prototype.attrIndex = function(e) {
                var r, t, n;
                if (!this.attrs) return -1;
                for (r = this.attrs, t = 0, n = r.length; n > t; t++)
                    if (r[t][0] === e) return t;
                return -1
            }, n.prototype.attrPush = function(e) {
                this.attrs ? this.attrs.push(e) : this.attrs = [e]
            }, r.exports = n
        }, {}],
        51: [function(r, t, n) {
            (function(r) {
                ! function(s) {
                    function i(e) {
                        throw RangeError(M[e])
                    }

                    function o(e, r) {
                        for (var t = e.length; t--;) e[t] = r(e[t]);
                        return e
                    }

                    function a(e, r) {
                        return o(e.split(T), r).join(".")
                    }

                    function c(e) {
                        for (var r, t, n = [], s = 0, i = e.length; i > s;) r = e.charCodeAt(s++), r >= 55296 && 56319 >= r && i > s ? (t = e.charCodeAt(s++), 56320 == (64512 & t) ? n.push(((1023 & r) << 10) + (1023 & t) + 65536) : (n.push(r), s--)) : n.push(r);
                        return n
                    }

                    function l(e) {
                        return o(e, function(e) {
                            var r = "";
                            return e > 65535 && (e -= 65536, r += B(e >>> 10 & 1023 | 55296), e = 56320 | 1023 & e), r += B(e)
                        }).join("")
                    }

                    function u(e) {
                        return 10 > e - 48 ? e - 22 : 26 > e - 65 ? e - 65 : 26 > e - 97 ? e - 97 : A
                    }

                    function p(e, r) {
                        return e + 22 + 75 * (26 > e) - ((0 != r) << 5)
                    }

                    function h(e, r, t) {
                        var n = 0;
                        for (e = t ? I(e / D) : e >> 1, e += I(e / r); e > R * w >> 1; n += A) e = I(e / R);
                        return I(n + (R + 1) * e / (e + q))
                    }

                    function f(e) {
                        var r, t, n, s, o, a, c, p, f, d, m = [],
                            g = e.length,
                            _ = 0,
                            b = S,
                            k = E;
                        for (t = e.lastIndexOf(F), 0 > t && (t = 0), n = 0; t > n; ++n) e.charCodeAt(n) >= 128 && i("not-basic"), m.push(e.charCodeAt(n));
                        for (s = t > 0 ? t + 1 : 0; g > s;) {
                            for (o = _, a = 1, c = A; s >= g && i("invalid-input"), p = u(e.charCodeAt(s++)), (p >= A || p > I((y - _) / a)) && i("overflow"), _ += p * a, f = k >= c ? C : c >= k + w ? w : c - k, !(f > p); c += A) d = A - f, a > I(y / d) && i("overflow"), a *= d;
                            r = m.length + 1, k = h(_ - o, r, 0 == o), I(_ / r) > y - b && i("overflow"), b += I(_ / r), _ %= r, m.splice(_++, 0, b)
                        }
                        return l(m)
                    }

                    function d(e) {
                        var r, t, n, s, o, a, l, u, f, d, m, g, _, b, k, v = [];
                        for (e = c(e), g = e.length, r = S, t = 0, o = E, a = 0; g > a; ++a) m = e[a], 128 > m && v.push(B(m));
                        for (n = s = v.length, s && v.push(F); g > n;) {
                            for (l = y, a = 0; g > a; ++a) m = e[a], m >= r && l > m && (l = m);
                            for (_ = n + 1, l - r > I((y - t) / _) && i("overflow"), t += (l - r) * _, r = l, a = 0; g > a; ++a)
                                if (m = e[a], r > m && ++t > y && i("overflow"), m == r) {
                                    for (u = t, f = A; d = o >= f ? C : f >= o + w ? w : f - o, !(d > u); f += A) k = u - d, b = A - d, v.push(B(p(d + k % b, 0))), u = I(k / b);
                                    v.push(B(p(u, 0))), o = h(t, _, n == s), t = 0, ++n
                                }++t, ++r
                        }
                        return v.join("")
                    }

                    function m(e) {
                        return a(e, function(e) {
                            return z.test(e) ? f(e.slice(4).toLowerCase()) : e
                        })
                    }

                    function g(e) {
                        return a(e, function(e) {
                            return L.test(e) ? "xn--" + d(e) : e
                        })
                    }
                    var _ = "object" == typeof n && n,
                        b = "object" == typeof t && t && t.exports == _ && t,
                        k = "object" == typeof r && r;
                    (k.global === k || k.window === k) && (s = k);
                    var v, x, y = 2147483647,
                        A = 36,
                        C = 1,
                        w = 26,
                        q = 38,
                        D = 700,
                        E = 72,
                        S = 128,
                        F = "-",
                        z = /^xn--/,
                        L = /[^ -~]/,
                        T = /\x2E|\u3002|\uFF0E|\uFF61/g,
                        M = {
                            overflow: "Overflow: input needs wider integers to process",
                            "not-basic": "Illegal input >= 0x80 (not a basic code point)",
                            "invalid-input": "Invalid input"
                        },
                        R = A - C,
                        I = Math.floor,
                        B = String.fromCharCode;
                    if (v = {
                            version: "1.2.4",
                            ucs2: {
                                decode: c,
                                encode: l
                            },
                            decode: f,
                            encode: d,
                            toASCII: g,
                            toUnicode: m
                        }, "function" == typeof e && "object" == typeof e.amd && e.amd) e("punycode", function() {
                        return v
                    });
                    else if (_ && !_.nodeType)
                        if (b) b.exports = v;
                        else
                            for (x in v) v.hasOwnProperty(x) && (_[x] = v[x]);
                    else s.punycode = v
                }(this)
            }).call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {})
        }, {}],
        52: [function(e, r, t) {
            r.exports = {
                Aacute: "\xc1",
                aacute: "\xe1",
                Abreve: "\u0102",
                abreve: "\u0103",
                ac: "\u223e",
                acd: "\u223f",
                acE: "\u223e\u0333",
                Acirc: "\xc2",
                acirc: "\xe2",
                acute: "\xb4",
                Acy: "\u0410",
                acy: "\u0430",
                AElig: "\xc6",
                aelig: "\xe6",
                af: "\u2061",
                Afr: "\ud835\udd04",
                afr: "\ud835\udd1e",
                Agrave: "\xc0",
                agrave: "\xe0",
                alefsym: "\u2135",
                aleph: "\u2135",
                Alpha: "\u0391",
                alpha: "\u03b1",
                Amacr: "\u0100",
                amacr: "\u0101",
                amalg: "\u2a3f",
                amp: "&",
                AMP: "&",
                andand: "\u2a55",
                And: "\u2a53",
                and: "\u2227",
                andd: "\u2a5c",
                andslope: "\u2a58",
                andv: "\u2a5a",
                ang: "\u2220",
                ange: "\u29a4",
                angle: "\u2220",
                angmsdaa: "\u29a8",
                angmsdab: "\u29a9",
                angmsdac: "\u29aa",
                angmsdad: "\u29ab",
                angmsdae: "\u29ac",
                angmsdaf: "\u29ad",
                angmsdag: "\u29ae",
                angmsdah: "\u29af",
                angmsd: "\u2221",
                angrt: "\u221f",
                angrtvb: "\u22be",
                angrtvbd: "\u299d",
                angsph: "\u2222",
                angst: "\xc5",
                angzarr: "\u237c",
                Aogon: "\u0104",
                aogon: "\u0105",
                Aopf: "\ud835\udd38",
                aopf: "\ud835\udd52",
                apacir: "\u2a6f",
                ap: "\u2248",
                apE: "\u2a70",
                ape: "\u224a",
                apid: "\u224b",
                apos: "'",
                ApplyFunction: "\u2061",
                approx: "\u2248",
                approxeq: "\u224a",
                Aring: "\xc5",
                aring: "\xe5",
                Ascr: "\ud835\udc9c",
                ascr: "\ud835\udcb6",
                Assign: "\u2254",
                ast: "*",
                asymp: "\u2248",
                asympeq: "\u224d",
                Atilde: "\xc3",
                atilde: "\xe3",
                Auml: "\xc4",
                auml: "\xe4",
                awconint: "\u2233",
                awint: "\u2a11",
                backcong: "\u224c",
                backepsilon: "\u03f6",
                backprime: "\u2035",
                backsim: "\u223d",
                backsimeq: "\u22cd",
                Backslash: "\u2216",
                Barv: "\u2ae7",
                barvee: "\u22bd",
                barwed: "\u2305",
                Barwed: "\u2306",
                barwedge: "\u2305",
                bbrk: "\u23b5",
                bbrktbrk: "\u23b6",
                bcong: "\u224c",
                Bcy: "\u0411",
                bcy: "\u0431",
                bdquo: "\u201e",
                becaus: "\u2235",
                because: "\u2235",
                Because: "\u2235",
                bemptyv: "\u29b0",
                bepsi: "\u03f6",
                bernou: "\u212c",
                Bernoullis: "\u212c",
                Beta: "\u0392",
                beta: "\u03b2",
                beth: "\u2136",
                between: "\u226c",
                Bfr: "\ud835\udd05",
                bfr: "\ud835\udd1f",
                bigcap: "\u22c2",
                bigcirc: "\u25ef",
                bigcup: "\u22c3",
                bigodot: "\u2a00",
                bigoplus: "\u2a01",
                bigotimes: "\u2a02",
                bigsqcup: "\u2a06",
                bigstar: "\u2605",
                bigtriangledown: "\u25bd",
                bigtriangleup: "\u25b3",
                biguplus: "\u2a04",
                bigvee: "\u22c1",
                bigwedge: "\u22c0",
                bkarow: "\u290d",
                blacklozenge: "\u29eb",
                blacksquare: "\u25aa",
                blacktriangle: "\u25b4",
                blacktriangledown: "\u25be",
                blacktriangleleft: "\u25c2",
                blacktriangleright: "\u25b8",
                blank: "\u2423",
                blk12: "\u2592",
                blk14: "\u2591",
                blk34: "\u2593",
                block: "\u2588",
                bne: "=\u20e5",
                bnequiv: "\u2261\u20e5",
                bNot: "\u2aed",
                bnot: "\u2310",
                Bopf: "\ud835\udd39",
                bopf: "\ud835\udd53",
                bot: "\u22a5",
                bottom: "\u22a5",
                bowtie: "\u22c8",
                boxbox: "\u29c9",
                boxdl: "\u2510",
                boxdL: "\u2555",
                boxDl: "\u2556",
                boxDL: "\u2557",
                boxdr: "\u250c",
                boxdR: "\u2552",
                boxDr: "\u2553",
                boxDR: "\u2554",
                boxh: "\u2500",
                boxH: "\u2550",
                boxhd: "\u252c",
                boxHd: "\u2564",
                boxhD: "\u2565",
                boxHD: "\u2566",
                boxhu: "\u2534",
                boxHu: "\u2567",
                boxhU: "\u2568",
                boxHU: "\u2569",
                boxminus: "\u229f",
                boxplus: "\u229e",
                boxtimes: "\u22a0",
                boxul: "\u2518",
                boxuL: "\u255b",
                boxUl: "\u255c",
                boxUL: "\u255d",
                boxur: "\u2514",
                boxuR: "\u2558",
                boxUr: "\u2559",
                boxUR: "\u255a",
                boxv: "\u2502",
                boxV: "\u2551",
                boxvh: "\u253c",
                boxvH: "\u256a",
                boxVh: "\u256b",
                boxVH: "\u256c",
                boxvl: "\u2524",
                boxvL: "\u2561",
                boxVl: "\u2562",
                boxVL: "\u2563",
                boxvr: "\u251c",
                boxvR: "\u255e",
                boxVr: "\u255f",
                boxVR: "\u2560",
                bprime: "\u2035",
                breve: "\u02d8",
                Breve: "\u02d8",
                brvbar: "\xa6",
                bscr: "\ud835\udcb7",
                Bscr: "\u212c",
                bsemi: "\u204f",
                bsim: "\u223d",
                bsime: "\u22cd",
                bsolb: "\u29c5",
                bsol: "\\",
                bsolhsub: "\u27c8",
                bull: "\u2022",
                bullet: "\u2022",
                bump: "\u224e",
                bumpE: "\u2aae",
                bumpe: "\u224f",
                Bumpeq: "\u224e",
                bumpeq: "\u224f",
                Cacute: "\u0106",
                cacute: "\u0107",
                capand: "\u2a44",
                capbrcup: "\u2a49",
                capcap: "\u2a4b",
                cap: "\u2229",
                Cap: "\u22d2",
                capcup: "\u2a47",
                capdot: "\u2a40",
                CapitalDifferentialD: "\u2145",
                caps: "\u2229\ufe00",
                caret: "\u2041",
                caron: "\u02c7",
                Cayleys: "\u212d",
                ccaps: "\u2a4d",
                Ccaron: "\u010c",
                ccaron: "\u010d",
                Ccedil: "\xc7",
                ccedil: "\xe7",
                Ccirc: "\u0108",
                ccirc: "\u0109",
                Cconint: "\u2230",
                ccups: "\u2a4c",
                ccupssm: "\u2a50",
                Cdot: "\u010a",
                cdot: "\u010b",
                cedil: "\xb8",
                Cedilla: "\xb8",
                cemptyv: "\u29b2",
                cent: "\xa2",
                centerdot: "\xb7",
                CenterDot: "\xb7",
                cfr: "\ud835\udd20",
                Cfr: "\u212d",
                CHcy: "\u0427",
                chcy: "\u0447",
                check: "\u2713",
                checkmark: "\u2713",
                Chi: "\u03a7",
                chi: "\u03c7",
                circ: "\u02c6",
                circeq: "\u2257",
                circlearrowleft: "\u21ba",
                circlearrowright: "\u21bb",
                circledast: "\u229b",
                circledcirc: "\u229a",
                circleddash: "\u229d",
                CircleDot: "\u2299",
                circledR: "\xae",
                circledS: "\u24c8",
                CircleMinus: "\u2296",
                CirclePlus: "\u2295",
                CircleTimes: "\u2297",
                cir: "\u25cb",
                cirE: "\u29c3",
                cire: "\u2257",
                cirfnint: "\u2a10",
                cirmid: "\u2aef",
                cirscir: "\u29c2",
                ClockwiseContourIntegral: "\u2232",
                CloseCurlyDoubleQuote: "\u201d",
                CloseCurlyQuote: "\u2019",
                clubs: "\u2663",
                clubsuit: "\u2663",
                colon: ":",
                Colon: "\u2237",
                Colone: "\u2a74",
                colone: "\u2254",
                coloneq: "\u2254",
                comma: ",",
                commat: "@",
                comp: "\u2201",
                compfn: "\u2218",
                complement: "\u2201",
                complexes: "\u2102",
                cong: "\u2245",
                congdot: "\u2a6d",
                Congruent: "\u2261",
                conint: "\u222e",
                Conint: "\u222f",
                ContourIntegral: "\u222e",
                copf: "\ud835\udd54",
                Copf: "\u2102",
                coprod: "\u2210",
                Coproduct: "\u2210",
                copy: "\xa9",
                COPY: "\xa9",
                copysr: "\u2117",
                CounterClockwiseContourIntegral: "\u2233",
                crarr: "\u21b5",
                cross: "\u2717",
                Cross: "\u2a2f",
                Cscr: "\ud835\udc9e",
                cscr: "\ud835\udcb8",
                csub: "\u2acf",
                csube: "\u2ad1",
                csup: "\u2ad0",
                csupe: "\u2ad2",
                ctdot: "\u22ef",
                cudarrl: "\u2938",
                cudarrr: "\u2935",
                cuepr: "\u22de",
                cuesc: "\u22df",
                cularr: "\u21b6",
                cularrp: "\u293d",
                cupbrcap: "\u2a48",
                cupcap: "\u2a46",
                CupCap: "\u224d",
                cup: "\u222a",
                Cup: "\u22d3",
                cupcup: "\u2a4a",
                cupdot: "\u228d",
                cupor: "\u2a45",
                cups: "\u222a\ufe00",
                curarr: "\u21b7",
                curarrm: "\u293c",
                curlyeqprec: "\u22de",
                curlyeqsucc: "\u22df",
                curlyvee: "\u22ce",
                curlywedge: "\u22cf",
                curren: "\xa4",
                curvearrowleft: "\u21b6",
                curvearrowright: "\u21b7",
                cuvee: "\u22ce",
                cuwed: "\u22cf",
                cwconint: "\u2232",
                cwint: "\u2231",
                cylcty: "\u232d",
                dagger: "\u2020",
                Dagger: "\u2021",
                daleth: "\u2138",
                darr: "\u2193",
                Darr: "\u21a1",
                dArr: "\u21d3",
                dash: "\u2010",
                Dashv: "\u2ae4",
                dashv: "\u22a3",
                dbkarow: "\u290f",
                dblac: "\u02dd",
                Dcaron: "\u010e",
                dcaron: "\u010f",
                Dcy: "\u0414",
                dcy: "\u0434",
                ddagger: "\u2021",
                ddarr: "\u21ca",
                DD: "\u2145",
                dd: "\u2146",
                DDotrahd: "\u2911",
                ddotseq: "\u2a77",
                deg: "\xb0",
                Del: "\u2207",
                Delta: "\u0394",
                delta: "\u03b4",
                demptyv: "\u29b1",
                dfisht: "\u297f",
                Dfr: "\ud835\udd07",
                dfr: "\ud835\udd21",
                dHar: "\u2965",
                dharl: "\u21c3",
                dharr: "\u21c2",
                DiacriticalAcute: "\xb4",
                DiacriticalDot: "\u02d9",
                DiacriticalDoubleAcute: "\u02dd",
                DiacriticalGrave: "`",
                DiacriticalTilde: "\u02dc",
                diam: "\u22c4",
                diamond: "\u22c4",
                Diamond: "\u22c4",
                diamondsuit: "\u2666",
                diams: "\u2666",
                die: "\xa8",
                DifferentialD: "\u2146",
                digamma: "\u03dd",
                disin: "\u22f2",
                div: "\xf7",
                divide: "\xf7",
                divideontimes: "\u22c7",
                divonx: "\u22c7",
                DJcy: "\u0402",
                djcy: "\u0452",
                dlcorn: "\u231e",
                dlcrop: "\u230d",
                dollar: "$",
                Dopf: "\ud835\udd3b",
                dopf: "\ud835\udd55",
                Dot: "\xa8",
                dot: "\u02d9",
                DotDot: "\u20dc",
                doteq: "\u2250",
                doteqdot: "\u2251",
                DotEqual: "\u2250",
                dotminus: "\u2238",
                dotplus: "\u2214",
                dotsquare: "\u22a1",
                doublebarwedge: "\u2306",
                DoubleContourIntegral: "\u222f",
                DoubleDot: "\xa8",
                DoubleDownArrow: "\u21d3",
                DoubleLeftArrow: "\u21d0",
                DoubleLeftRightArrow: "\u21d4",
                DoubleLeftTee: "\u2ae4",
                DoubleLongLeftArrow: "\u27f8",
                DoubleLongLeftRightArrow: "\u27fa",
                DoubleLongRightArrow: "\u27f9",
                DoubleRightArrow: "\u21d2",
                DoubleRightTee: "\u22a8",
                DoubleUpArrow: "\u21d1",
                DoubleUpDownArrow: "\u21d5",
                DoubleVerticalBar: "\u2225",
                DownArrowBar: "\u2913",
                downarrow: "\u2193",
                DownArrow: "\u2193",
                Downarrow: "\u21d3",
                DownArrowUpArrow: "\u21f5",
                DownBreve: "\u0311",
                downdownarrows: "\u21ca",
                downharpoonleft: "\u21c3",
                downharpoonright: "\u21c2",
                DownLeftRightVector: "\u2950",
                DownLeftTeeVector: "\u295e",
                DownLeftVectorBar: "\u2956",
                DownLeftVector: "\u21bd",
                DownRightTeeVector: "\u295f",
                DownRightVectorBar: "\u2957",
                DownRightVector: "\u21c1",
                DownTeeArrow: "\u21a7",
                DownTee: "\u22a4",
                drbkarow: "\u2910",
                drcorn: "\u231f",
                drcrop: "\u230c",
                Dscr: "\ud835\udc9f",
                dscr: "\ud835\udcb9",
                DScy: "\u0405",
                dscy: "\u0455",
                dsol: "\u29f6",
                Dstrok: "\u0110",
                dstrok: "\u0111",
                dtdot: "\u22f1",
                dtri: "\u25bf",
                dtrif: "\u25be",
                duarr: "\u21f5",
                duhar: "\u296f",
                dwangle: "\u29a6",
                DZcy: "\u040f",
                dzcy: "\u045f",
                dzigrarr: "\u27ff",
                Eacute: "\xc9",
                eacute: "\xe9",
                easter: "\u2a6e",
                Ecaron: "\u011a",
                ecaron: "\u011b",
                Ecirc: "\xca",
                ecirc: "\xea",
                ecir: "\u2256",
                ecolon: "\u2255",
                Ecy: "\u042d",
                ecy: "\u044d",
                eDDot: "\u2a77",
                Edot: "\u0116",
                edot: "\u0117",
                eDot: "\u2251",
                ee: "\u2147",
                efDot: "\u2252",
                Efr: "\ud835\udd08",
                efr: "\ud835\udd22",
                eg: "\u2a9a",
                Egrave: "\xc8",
                egrave: "\xe8",
                egs: "\u2a96",
                egsdot: "\u2a98",
                el: "\u2a99",
                Element: "\u2208",
                elinters: "\u23e7",
                ell: "\u2113",
                els: "\u2a95",
                elsdot: "\u2a97",
                Emacr: "\u0112",
                emacr: "\u0113",
                empty: "\u2205",
                emptyset: "\u2205",
                EmptySmallSquare: "\u25fb",
                emptyv: "\u2205",
                EmptyVerySmallSquare: "\u25ab",
                emsp13: "\u2004",
                emsp14: "\u2005",
                emsp: "\u2003",
                ENG: "\u014a",
                eng: "\u014b",
                ensp: "\u2002",
                Eogon: "\u0118",
                eogon: "\u0119",
                Eopf: "\ud835\udd3c",
                eopf: "\ud835\udd56",
                epar: "\u22d5",
                eparsl: "\u29e3",
                eplus: "\u2a71",
                epsi: "\u03b5",
                Epsilon: "\u0395",
                epsilon: "\u03b5",
                epsiv: "\u03f5",
                eqcirc: "\u2256",
                eqcolon: "\u2255",
                eqsim: "\u2242",
                eqslantgtr: "\u2a96",
                eqslantless: "\u2a95",
                Equal: "\u2a75",
                equals: "=",
                EqualTilde: "\u2242",
                equest: "\u225f",
                Equilibrium: "\u21cc",
                equiv: "\u2261",
                equivDD: "\u2a78",
                eqvparsl: "\u29e5",
                erarr: "\u2971",
                erDot: "\u2253",
                escr: "\u212f",
                Escr: "\u2130",
                esdot: "\u2250",
                Esim: "\u2a73",
                esim: "\u2242",
                Eta: "\u0397",
                eta: "\u03b7",
                ETH: "\xd0",
                eth: "\xf0",
                Euml: "\xcb",
                euml: "\xeb",
                euro: "\u20ac",
                excl: "!",
                exist: "\u2203",
                Exists: "\u2203",
                expectation: "\u2130",
                exponentiale: "\u2147",
                ExponentialE: "\u2147",
                fallingdotseq: "\u2252",
                Fcy: "\u0424",
                fcy: "\u0444",
                female: "\u2640",
                ffilig: "\ufb03",
                fflig: "\ufb00",
                ffllig: "\ufb04",
                Ffr: "\ud835\udd09",
                ffr: "\ud835\udd23",
                filig: "\ufb01",
                FilledSmallSquare: "\u25fc",
                FilledVerySmallSquare: "\u25aa",
                fjlig: "fj",
                flat: "\u266d",
                fllig: "\ufb02",
                fltns: "\u25b1",
                fnof: "\u0192",
                Fopf: "\ud835\udd3d",
                fopf: "\ud835\udd57",
                forall: "\u2200",
                ForAll: "\u2200",
                fork: "\u22d4",
                forkv: "\u2ad9",
                Fouriertrf: "\u2131",
                fpartint: "\u2a0d",
                frac12: "\xbd",
                frac13: "\u2153",
                frac14: "\xbc",
                frac15: "\u2155",
                frac16: "\u2159",
                frac18: "\u215b",
                frac23: "\u2154",
                frac25: "\u2156",
                frac34: "\xbe",
                frac35: "\u2157",
                frac38: "\u215c",
                frac45: "\u2158",
                frac56: "\u215a",
                frac58: "\u215d",
                frac78: "\u215e",
                frasl: "\u2044",
                frown: "\u2322",
                fscr: "\ud835\udcbb",
                Fscr: "\u2131",
                gacute: "\u01f5",
                Gamma: "\u0393",
                gamma: "\u03b3",
                Gammad: "\u03dc",
                gammad: "\u03dd",
                gap: "\u2a86",
                Gbreve: "\u011e",
                gbreve: "\u011f",
                Gcedil: "\u0122",
                Gcirc: "\u011c",
                gcirc: "\u011d",
                Gcy: "\u0413",
                gcy: "\u0433",
                Gdot: "\u0120",
                gdot: "\u0121",
                ge: "\u2265",
                gE: "\u2267",
                gEl: "\u2a8c",
                gel: "\u22db",
                geq: "\u2265",
                geqq: "\u2267",
                geqslant: "\u2a7e",
                gescc: "\u2aa9",
                ges: "\u2a7e",
                gesdot: "\u2a80",
                gesdoto: "\u2a82",
                gesdotol: "\u2a84",
                gesl: "\u22db\ufe00",
                gesles: "\u2a94",
                Gfr: "\ud835\udd0a",
                gfr: "\ud835\udd24",
                gg: "\u226b",
                Gg: "\u22d9",
                ggg: "\u22d9",
                gimel: "\u2137",
                GJcy: "\u0403",
                gjcy: "\u0453",
                gla: "\u2aa5",
                gl: "\u2277",
                glE: "\u2a92",
                glj: "\u2aa4",
                gnap: "\u2a8a",
                gnapprox: "\u2a8a",
                gne: "\u2a88",
                gnE: "\u2269",
                gneq: "\u2a88",
                gneqq: "\u2269",
                gnsim: "\u22e7",
                Gopf: "\ud835\udd3e",
                gopf: "\ud835\udd58",
                grave: "`",
                GreaterEqual: "\u2265",
                GreaterEqualLess: "\u22db",
                GreaterFullEqual: "\u2267",
                GreaterGreater: "\u2aa2",
                GreaterLess: "\u2277",
                GreaterSlantEqual: "\u2a7e",
                GreaterTilde: "\u2273",
                Gscr: "\ud835\udca2",
                gscr: "\u210a",
                gsim: "\u2273",
                gsime: "\u2a8e",
                gsiml: "\u2a90",
                gtcc: "\u2aa7",
                gtcir: "\u2a7a",
                gt: ">",
                GT: ">",
                Gt: "\u226b",
                gtdot: "\u22d7",
                gtlPar: "\u2995",
                gtquest: "\u2a7c",
                gtrapprox: "\u2a86",
                gtrarr: "\u2978",
                gtrdot: "\u22d7",
                gtreqless: "\u22db",
                gtreqqless: "\u2a8c",
                gtrless: "\u2277",
                gtrsim: "\u2273",
                gvertneqq: "\u2269\ufe00",
                gvnE: "\u2269\ufe00",
                Hacek: "\u02c7",
                hairsp: "\u200a",
                half: "\xbd",
                hamilt: "\u210b",
                HARDcy: "\u042a",
                hardcy: "\u044a",
                harrcir: "\u2948",
                harr: "\u2194",
                hArr: "\u21d4",
                harrw: "\u21ad",
                Hat: "^",
                hbar: "\u210f",
                Hcirc: "\u0124",
                hcirc: "\u0125",
                hearts: "\u2665",
                heartsuit: "\u2665",
                hellip: "\u2026",
                hercon: "\u22b9",
                hfr: "\ud835\udd25",
                Hfr: "\u210c",
                HilbertSpace: "\u210b",
                hksearow: "\u2925",
                hkswarow: "\u2926",
                hoarr: "\u21ff",
                homtht: "\u223b",
                hookleftarrow: "\u21a9",
                hookrightarrow: "\u21aa",
                hopf: "\ud835\udd59",
                Hopf: "\u210d",
                horbar: "\u2015",
                HorizontalLine: "\u2500",
                hscr: "\ud835\udcbd",
                Hscr: "\u210b",
                hslash: "\u210f",
                Hstrok: "\u0126",
                hstrok: "\u0127",
                HumpDownHump: "\u224e",
                HumpEqual: "\u224f",
                hybull: "\u2043",
                hyphen: "\u2010",
                Iacute: "\xcd",
                iacute: "\xed",
                ic: "\u2063",
                Icirc: "\xce",
                icirc: "\xee",
                Icy: "\u0418",
                icy: "\u0438",
                Idot: "\u0130",
                IEcy: "\u0415",
                iecy: "\u0435",
                iexcl: "\xa1",
                iff: "\u21d4",
                ifr: "\ud835\udd26",
                Ifr: "\u2111",
                Igrave: "\xcc",
                igrave: "\xec",
                ii: "\u2148",
                iiiint: "\u2a0c",
                iiint: "\u222d",
                iinfin: "\u29dc",
                iiota: "\u2129",
                IJlig: "\u0132",
                ijlig: "\u0133",
                Imacr: "\u012a",
                imacr: "\u012b",
                image: "\u2111",
                ImaginaryI: "\u2148",
                imagline: "\u2110",
                imagpart: "\u2111",
                imath: "\u0131",
                Im: "\u2111",
                imof: "\u22b7",
                imped: "\u01b5",
                Implies: "\u21d2",
                incare: "\u2105",
                "in": "\u2208",
                infin: "\u221e",
                infintie: "\u29dd",
                inodot: "\u0131",
                intcal: "\u22ba",
                "int": "\u222b",
                Int: "\u222c",
                integers: "\u2124",
                Integral: "\u222b",
                intercal: "\u22ba",
                Intersection: "\u22c2",
                intlarhk: "\u2a17",
                intprod: "\u2a3c",
                InvisibleComma: "\u2063",
                InvisibleTimes: "\u2062",
                IOcy: "\u0401",
                iocy: "\u0451",
                Iogon: "\u012e",
                iogon: "\u012f",
                Iopf: "\ud835\udd40",
                iopf: "\ud835\udd5a",
                Iota: "\u0399",
                iota: "\u03b9",
                iprod: "\u2a3c",
                iquest: "\xbf",
                iscr: "\ud835\udcbe",
                Iscr: "\u2110",
                isin: "\u2208",
                isindot: "\u22f5",
                isinE: "\u22f9",
                isins: "\u22f4",
                isinsv: "\u22f3",
                isinv: "\u2208",
                it: "\u2062",
                Itilde: "\u0128",
                itilde: "\u0129",
                Iukcy: "\u0406",
                iukcy: "\u0456",
                Iuml: "\xcf",
                iuml: "\xef",
                Jcirc: "\u0134",
                jcirc: "\u0135",
                Jcy: "\u0419",
                jcy: "\u0439",
                Jfr: "\ud835\udd0d",
                jfr: "\ud835\udd27",
                jmath: "\u0237",
                Jopf: "\ud835\udd41",
                jopf: "\ud835\udd5b",
                Jscr: "\ud835\udca5",
                jscr: "\ud835\udcbf",
                Jsercy: "\u0408",
                jsercy: "\u0458",
                Jukcy: "\u0404",
                jukcy: "\u0454",
                Kappa: "\u039a",
                kappa: "\u03ba",
                kappav: "\u03f0",
                Kcedil: "\u0136",
                kcedil: "\u0137",
                Kcy: "\u041a",
                kcy: "\u043a",
                Kfr: "\ud835\udd0e",
                kfr: "\ud835\udd28",
                kgreen: "\u0138",
                KHcy: "\u0425",
                khcy: "\u0445",
                KJcy: "\u040c",
                kjcy: "\u045c",
                Kopf: "\ud835\udd42",
                kopf: "\ud835\udd5c",
                Kscr: "\ud835\udca6",
                kscr: "\ud835\udcc0",
                lAarr: "\u21da",
                Lacute: "\u0139",
                lacute: "\u013a",
                laemptyv: "\u29b4",
                lagran: "\u2112",
                Lambda: "\u039b",
                lambda: "\u03bb",
                lang: "\u27e8",
                Lang: "\u27ea",
                langd: "\u2991",
                langle: "\u27e8",
                lap: "\u2a85",
                Laplacetrf: "\u2112",
                laquo: "\xab",
                larrb: "\u21e4",
                larrbfs: "\u291f",
                larr: "\u2190",
                Larr: "\u219e",
                lArr: "\u21d0",
                larrfs: "\u291d",
                larrhk: "\u21a9",
                larrlp: "\u21ab",
                larrpl: "\u2939",
                larrsim: "\u2973",
                larrtl: "\u21a2",
                latail: "\u2919",
                lAtail: "\u291b",
                lat: "\u2aab",
                late: "\u2aad",
                lates: "\u2aad\ufe00",
                lbarr: "\u290c",
                lBarr: "\u290e",
                lbbrk: "\u2772",
                lbrace: "{",
                lbrack: "[",
                lbrke: "\u298b",
                lbrksld: "\u298f",
                lbrkslu: "\u298d",
                Lcaron: "\u013d",
                lcaron: "\u013e",
                Lcedil: "\u013b",
                lcedil: "\u013c",
                lceil: "\u2308",
                lcub: "{",
                Lcy: "\u041b",
                lcy: "\u043b",
                ldca: "\u2936",
                ldquo: "\u201c",
                ldquor: "\u201e",
                ldrdhar: "\u2967",
                ldrushar: "\u294b",
                ldsh: "\u21b2",
                le: "\u2264",
                lE: "\u2266",
                LeftAngleBracket: "\u27e8",
                LeftArrowBar: "\u21e4",
                leftarrow: "\u2190",
                LeftArrow: "\u2190",
                Leftarrow: "\u21d0",
                LeftArrowRightArrow: "\u21c6",
                leftarrowtail: "\u21a2",
                LeftCeiling: "\u2308",
                LeftDoubleBracket: "\u27e6",
                LeftDownTeeVector: "\u2961",
                LeftDownVectorBar: "\u2959",
                LeftDownVector: "\u21c3",
                LeftFloor: "\u230a",
                leftharpoondown: "\u21bd",
                leftharpoonup: "\u21bc",
                leftleftarrows: "\u21c7",
                leftrightarrow: "\u2194",
                LeftRightArrow: "\u2194",
                Leftrightarrow: "\u21d4",
                leftrightarrows: "\u21c6",
                leftrightharpoons: "\u21cb",
                leftrightsquigarrow: "\u21ad",
                LeftRightVector: "\u294e",
                LeftTeeArrow: "\u21a4",
                LeftTee: "\u22a3",
                LeftTeeVector: "\u295a",
                leftthreetimes: "\u22cb",
                LeftTriangleBar: "\u29cf",
                LeftTriangle: "\u22b2",
                LeftTriangleEqual: "\u22b4",
                LeftUpDownVector: "\u2951",
                LeftUpTeeVector: "\u2960",
                LeftUpVectorBar: "\u2958",
                LeftUpVector: "\u21bf",
                LeftVectorBar: "\u2952",
                LeftVector: "\u21bc",
                lEg: "\u2a8b",
                leg: "\u22da",
                leq: "\u2264",
                leqq: "\u2266",
                leqslant: "\u2a7d",
                lescc: "\u2aa8",
                les: "\u2a7d",
                lesdot: "\u2a7f",
                lesdoto: "\u2a81",
                lesdotor: "\u2a83",
                lesg: "\u22da\ufe00",
                lesges: "\u2a93",
                lessapprox: "\u2a85",
                lessdot: "\u22d6",
                lesseqgtr: "\u22da",
                lesseqqgtr: "\u2a8b",
                LessEqualGreater: "\u22da",
                LessFullEqual: "\u2266",
                LessGreater: "\u2276",
                lessgtr: "\u2276",
                LessLess: "\u2aa1",
                lesssim: "\u2272",
                LessSlantEqual: "\u2a7d",
                LessTilde: "\u2272",
                lfisht: "\u297c",
                lfloor: "\u230a",
                Lfr: "\ud835\udd0f",
                lfr: "\ud835\udd29",
                lg: "\u2276",
                lgE: "\u2a91",
                lHar: "\u2962",
                lhard: "\u21bd",
                lharu: "\u21bc",
                lharul: "\u296a",
                lhblk: "\u2584",
                LJcy: "\u0409",
                ljcy: "\u0459",
                llarr: "\u21c7",
                ll: "\u226a",
                Ll: "\u22d8",
                llcorner: "\u231e",
                Lleftarrow: "\u21da",
                llhard: "\u296b",
                lltri: "\u25fa",
                Lmidot: "\u013f",
                lmidot: "\u0140",
                lmoustache: "\u23b0",
                lmoust: "\u23b0",
                lnap: "\u2a89",
                lnapprox: "\u2a89",
                lne: "\u2a87",
                lnE: "\u2268",
                lneq: "\u2a87",
                lneqq: "\u2268",
                lnsim: "\u22e6",
                loang: "\u27ec",
                loarr: "\u21fd",
                lobrk: "\u27e6",
                longleftarrow: "\u27f5",
                LongLeftArrow: "\u27f5",
                Longleftarrow: "\u27f8",
                longleftrightarrow: "\u27f7",
                LongLeftRightArrow: "\u27f7",
                Longleftrightarrow: "\u27fa",
                longmapsto: "\u27fc",
                longrightarrow: "\u27f6",
                LongRightArrow: "\u27f6",
                Longrightarrow: "\u27f9",
                looparrowleft: "\u21ab",
                looparrowright: "\u21ac",
                lopar: "\u2985",
                Lopf: "\ud835\udd43",
                lopf: "\ud835\udd5d",
                loplus: "\u2a2d",
                lotimes: "\u2a34",
                lowast: "\u2217",
                lowbar: "_",
                LowerLeftArrow: "\u2199",
                LowerRightArrow: "\u2198",
                loz: "\u25ca",
                lozenge: "\u25ca",
                lozf: "\u29eb",
                lpar: "(",
                lparlt: "\u2993",
                lrarr: "\u21c6",
                lrcorner: "\u231f",
                lrhar: "\u21cb",
                lrhard: "\u296d",
                lrm: "\u200e",
                lrtri: "\u22bf",
                lsaquo: "\u2039",
                lscr: "\ud835\udcc1",
                Lscr: "\u2112",
                lsh: "\u21b0",
                Lsh: "\u21b0",
                lsim: "\u2272",
                lsime: "\u2a8d",
                lsimg: "\u2a8f",
                lsqb: "[",
                lsquo: "\u2018",
                lsquor: "\u201a",
                Lstrok: "\u0141",
                lstrok: "\u0142",
                ltcc: "\u2aa6",
                ltcir: "\u2a79",
                lt: "<",
                LT: "<",
                Lt: "\u226a",
                ltdot: "\u22d6",
                lthree: "\u22cb",
                ltimes: "\u22c9",
                ltlarr: "\u2976",
                ltquest: "\u2a7b",
                ltri: "\u25c3",
                ltrie: "\u22b4",
                ltrif: "\u25c2",
                ltrPar: "\u2996",
                lurdshar: "\u294a",
                luruhar: "\u2966",
                lvertneqq: "\u2268\ufe00",
                lvnE: "\u2268\ufe00",
                macr: "\xaf",
                male: "\u2642",
                malt: "\u2720",
                maltese: "\u2720",
                Map: "\u2905",
                map: "\u21a6",
                mapsto: "\u21a6",
                mapstodown: "\u21a7",
                mapstoleft: "\u21a4",
                mapstoup: "\u21a5",
                marker: "\u25ae",
                mcomma: "\u2a29",
                Mcy: "\u041c",
                mcy: "\u043c",
                mdash: "\u2014",
                mDDot: "\u223a",
                measuredangle: "\u2221",
                MediumSpace: "\u205f",
                Mellintrf: "\u2133",
                Mfr: "\ud835\udd10",
                mfr: "\ud835\udd2a",
                mho: "\u2127",
                micro: "\xb5",
                midast: "*",
                midcir: "\u2af0",
                mid: "\u2223",
                middot: "\xb7",
                minusb: "\u229f",
                minus: "\u2212",
                minusd: "\u2238",
                minusdu: "\u2a2a",
                MinusPlus: "\u2213",
                mlcp: "\u2adb",
                mldr: "\u2026",
                mnplus: "\u2213",
                models: "\u22a7",
                Mopf: "\ud835\udd44",
                mopf: "\ud835\udd5e",
                mp: "\u2213",
                mscr: "\ud835\udcc2",
                Mscr: "\u2133",
                mstpos: "\u223e",
                Mu: "\u039c",
                mu: "\u03bc",
                multimap: "\u22b8",
                mumap: "\u22b8",
                nabla: "\u2207",
                Nacute: "\u0143",
                nacute: "\u0144",
                nang: "\u2220\u20d2",
                nap: "\u2249",
                napE: "\u2a70\u0338",
                napid: "\u224b\u0338",
                napos: "\u0149",
                napprox: "\u2249",
                natural: "\u266e",
                naturals: "\u2115",
                natur: "\u266e",
                nbsp: "\xa0",
                nbump: "\u224e\u0338",
                nbumpe: "\u224f\u0338",
                ncap: "\u2a43",
                Ncaron: "\u0147",
                ncaron: "\u0148",
                Ncedil: "\u0145",
                ncedil: "\u0146",
                ncong: "\u2247",
                ncongdot: "\u2a6d\u0338",
                ncup: "\u2a42",
                Ncy: "\u041d",
                ncy: "\u043d",
                ndash: "\u2013",
                nearhk: "\u2924",
                nearr: "\u2197",
                neArr: "\u21d7",
                nearrow: "\u2197",
                ne: "\u2260",
                nedot: "\u2250\u0338",
                NegativeMediumSpace: "\u200b",
                NegativeThickSpace: "\u200b",
                NegativeThinSpace: "\u200b",
                NegativeVeryThinSpace: "\u200b",
                nequiv: "\u2262",
                nesear: "\u2928",
                nesim: "\u2242\u0338",
                NestedGreaterGreater: "\u226b",
                NestedLessLess: "\u226a",
                NewLine: "\n",
                nexist: "\u2204",
                nexists: "\u2204",
                Nfr: "\ud835\udd11",
                nfr: "\ud835\udd2b",
                ngE: "\u2267\u0338",
                nge: "\u2271",
                ngeq: "\u2271",
                ngeqq: "\u2267\u0338",
                ngeqslant: "\u2a7e\u0338",
                nges: "\u2a7e\u0338",
                nGg: "\u22d9\u0338",
                ngsim: "\u2275",
                nGt: "\u226b\u20d2",
                ngt: "\u226f",
                ngtr: "\u226f",
                nGtv: "\u226b\u0338",
                nharr: "\u21ae",
                nhArr: "\u21ce",
                nhpar: "\u2af2",
                ni: "\u220b",
                nis: "\u22fc",
                nisd: "\u22fa",
                niv: "\u220b",
                NJcy: "\u040a",
                njcy: "\u045a",
                nlarr: "\u219a",
                nlArr: "\u21cd",
                nldr: "\u2025",
                nlE: "\u2266\u0338",
                nle: "\u2270",
                nleftarrow: "\u219a",
                nLeftarrow: "\u21cd",
                nleftrightarrow: "\u21ae",
                nLeftrightarrow: "\u21ce",
                nleq: "\u2270",
                nleqq: "\u2266\u0338",
                nleqslant: "\u2a7d\u0338",
                nles: "\u2a7d\u0338",
                nless: "\u226e",
                nLl: "\u22d8\u0338",
                nlsim: "\u2274",
                nLt: "\u226a\u20d2",
                nlt: "\u226e",
                nltri: "\u22ea",
                nltrie: "\u22ec",
                nLtv: "\u226a\u0338",
                nmid: "\u2224",
                NoBreak: "\u2060",
                NonBreakingSpace: "\xa0",
                nopf: "\ud835\udd5f",
                Nopf: "\u2115",
                Not: "\u2aec",
                not: "\xac",
                NotCongruent: "\u2262",
                NotCupCap: "\u226d",
                NotDoubleVerticalBar: "\u2226",
                NotElement: "\u2209",
                NotEqual: "\u2260",
                NotEqualTilde: "\u2242\u0338",
                NotExists: "\u2204",
                NotGreater: "\u226f",
                NotGreaterEqual: "\u2271",
                NotGreaterFullEqual: "\u2267\u0338",
                NotGreaterGreater: "\u226b\u0338",
                NotGreaterLess: "\u2279",
                NotGreaterSlantEqual: "\u2a7e\u0338",
                NotGreaterTilde: "\u2275",
                NotHumpDownHump: "\u224e\u0338",
                NotHumpEqual: "\u224f\u0338",
                notin: "\u2209",
                notindot: "\u22f5\u0338",
                notinE: "\u22f9\u0338",
                notinva: "\u2209",
                notinvb: "\u22f7",
                notinvc: "\u22f6",
                NotLeftTriangleBar: "\u29cf\u0338",
                NotLeftTriangle: "\u22ea",
                NotLeftTriangleEqual: "\u22ec",
                NotLess: "\u226e",
                NotLessEqual: "\u2270",
                NotLessGreater: "\u2278",
                NotLessLess: "\u226a\u0338",
                NotLessSlantEqual: "\u2a7d\u0338",
                NotLessTilde: "\u2274",
                NotNestedGreaterGreater: "\u2aa2\u0338",
                NotNestedLessLess: "\u2aa1\u0338",
                notni: "\u220c",
                notniva: "\u220c",
                notnivb: "\u22fe",
                notnivc: "\u22fd",
                NotPrecedes: "\u2280",
                NotPrecedesEqual: "\u2aaf\u0338",
                NotPrecedesSlantEqual: "\u22e0",
                NotReverseElement: "\u220c",
                NotRightTriangleBar: "\u29d0\u0338",
                NotRightTriangle: "\u22eb",
                NotRightTriangleEqual: "\u22ed",
                NotSquareSubset: "\u228f\u0338",
                NotSquareSubsetEqual: "\u22e2",
                NotSquareSuperset: "\u2290\u0338",
                NotSquareSupersetEqual: "\u22e3",
                NotSubset: "\u2282\u20d2",
                NotSubsetEqual: "\u2288",
                NotSucceeds: "\u2281",
                NotSucceedsEqual: "\u2ab0\u0338",
                NotSucceedsSlantEqual: "\u22e1",
                NotSucceedsTilde: "\u227f\u0338",
                NotSuperset: "\u2283\u20d2",
                NotSupersetEqual: "\u2289",
                NotTilde: "\u2241",
                NotTildeEqual: "\u2244",
                NotTildeFullEqual: "\u2247",
                NotTildeTilde: "\u2249",
                NotVerticalBar: "\u2224",
                nparallel: "\u2226",
                npar: "\u2226",
                nparsl: "\u2afd\u20e5",
                npart: "\u2202\u0338",
                npolint: "\u2a14",
                npr: "\u2280",
                nprcue: "\u22e0",
                nprec: "\u2280",
                npreceq: "\u2aaf\u0338",
                npre: "\u2aaf\u0338",
                nrarrc: "\u2933\u0338",
                nrarr: "\u219b",
                nrArr: "\u21cf",
                nrarrw: "\u219d\u0338",
                nrightarrow: "\u219b",
                nRightarrow: "\u21cf",
                nrtri: "\u22eb",
                nrtrie: "\u22ed",
                nsc: "\u2281",
                nsccue: "\u22e1",
                nsce: "\u2ab0\u0338",
                Nscr: "\ud835\udca9",
                nscr: "\ud835\udcc3",
                nshortmid: "\u2224",
                nshortparallel: "\u2226",
                nsim: "\u2241",
                nsime: "\u2244",
                nsimeq: "\u2244",
                nsmid: "\u2224",
                nspar: "\u2226",
                nsqsube: "\u22e2",
                nsqsupe: "\u22e3",
                nsub: "\u2284",
                nsubE: "\u2ac5\u0338",
                nsube: "\u2288",
                nsubset: "\u2282\u20d2",
                nsubseteq: "\u2288",
                nsubseteqq: "\u2ac5\u0338",
                nsucc: "\u2281",
                nsucceq: "\u2ab0\u0338",
                nsup: "\u2285",
                nsupE: "\u2ac6\u0338",
                nsupe: "\u2289",
                nsupset: "\u2283\u20d2",
                nsupseteq: "\u2289",
                nsupseteqq: "\u2ac6\u0338",
                ntgl: "\u2279",
                Ntilde: "\xd1",
                ntilde: "\xf1",
                ntlg: "\u2278",
                ntriangleleft: "\u22ea",
                ntrianglelefteq: "\u22ec",
                ntriangleright: "\u22eb",
                ntrianglerighteq: "\u22ed",
                Nu: "\u039d",
                nu: "\u03bd",
                num: "#",
                numero: "\u2116",
                numsp: "\u2007",
                nvap: "\u224d\u20d2",
                nvdash: "\u22ac",
                nvDash: "\u22ad",
                nVdash: "\u22ae",
                nVDash: "\u22af",
                nvge: "\u2265\u20d2",
                nvgt: ">\u20d2",
                nvHarr: "\u2904",
                nvinfin: "\u29de",
                nvlArr: "\u2902",
                nvle: "\u2264\u20d2",
                nvlt: "<\u20d2",
                nvltrie: "\u22b4\u20d2",
                nvrArr: "\u2903",
                nvrtrie: "\u22b5\u20d2",
                nvsim: "\u223c\u20d2",
                nwarhk: "\u2923",
                nwarr: "\u2196",
                nwArr: "\u21d6",
                nwarrow: "\u2196",
                nwnear: "\u2927",
                Oacute: "\xd3",
                oacute: "\xf3",
                oast: "\u229b",
                Ocirc: "\xd4",
                ocirc: "\xf4",
                ocir: "\u229a",
                Ocy: "\u041e",
                ocy: "\u043e",
                odash: "\u229d",
                Odblac: "\u0150",
                odblac: "\u0151",
                odiv: "\u2a38",
                odot: "\u2299",
                odsold: "\u29bc",
                OElig: "\u0152",
                oelig: "\u0153",
                ofcir: "\u29bf",
                Ofr: "\ud835\udd12",
                ofr: "\ud835\udd2c",
                ogon: "\u02db",
                Ograve: "\xd2",
                ograve: "\xf2",
                ogt: "\u29c1",
                ohbar: "\u29b5",
                ohm: "\u03a9",
                oint: "\u222e",
                olarr: "\u21ba",
                olcir: "\u29be",
                olcross: "\u29bb",
                oline: "\u203e",
                olt: "\u29c0",
                Omacr: "\u014c",
                omacr: "\u014d",
                Omega: "\u03a9",
                omega: "\u03c9",
                Omicron: "\u039f",
                omicron: "\u03bf",
                omid: "\u29b6",
                ominus: "\u2296",
                Oopf: "\ud835\udd46",
                oopf: "\ud835\udd60",
                opar: "\u29b7",
                OpenCurlyDoubleQuote: "\u201c",
                OpenCurlyQuote: "\u2018",
                operp: "\u29b9",
                oplus: "\u2295",
                orarr: "\u21bb",
                Or: "\u2a54",
                or: "\u2228",
                ord: "\u2a5d",
                order: "\u2134",
                orderof: "\u2134",
                ordf: "\xaa",
                ordm: "\xba",
                origof: "\u22b6",
                oror: "\u2a56",
                orslope: "\u2a57",
                orv: "\u2a5b",
                oS: "\u24c8",
                Oscr: "\ud835\udcaa",
                oscr: "\u2134",
                Oslash: "\xd8",
                oslash: "\xf8",
                osol: "\u2298",
                Otilde: "\xd5",
                otilde: "\xf5",
                otimesas: "\u2a36",
                Otimes: "\u2a37",
                otimes: "\u2297",
                Ouml: "\xd6",
                ouml: "\xf6",
                ovbar: "\u233d",
                OverBar: "\u203e",
                OverBrace: "\u23de",
                OverBracket: "\u23b4",
                OverParenthesis: "\u23dc",
                para: "\xb6",
                parallel: "\u2225",
                par: "\u2225",
                parsim: "\u2af3",
                parsl: "\u2afd",
                part: "\u2202",
                PartialD: "\u2202",
                Pcy: "\u041f",
                pcy: "\u043f",
                percnt: "%",
                period: ".",
                permil: "\u2030",
                perp: "\u22a5",
                pertenk: "\u2031",
                Pfr: "\ud835\udd13",
                pfr: "\ud835\udd2d",
                Phi: "\u03a6",
                phi: "\u03c6",
                phiv: "\u03d5",
                phmmat: "\u2133",
                phone: "\u260e",
                Pi: "\u03a0",
                pi: "\u03c0",
                pitchfork: "\u22d4",
                piv: "\u03d6",
                planck: "\u210f",
                planckh: "\u210e",
                plankv: "\u210f",
                plusacir: "\u2a23",
                plusb: "\u229e",
                pluscir: "\u2a22",
                plus: "+",
                plusdo: "\u2214",
                plusdu: "\u2a25",
                pluse: "\u2a72",
                PlusMinus: "\xb1",
                plusmn: "\xb1",
                plussim: "\u2a26",
                plustwo: "\u2a27",
                pm: "\xb1",
                Poincareplane: "\u210c",
                pointint: "\u2a15",
                popf: "\ud835\udd61",
                Popf: "\u2119",
                pound: "\xa3",
                prap: "\u2ab7",
                Pr: "\u2abb",
                pr: "\u227a",
                prcue: "\u227c",
                precapprox: "\u2ab7",
                prec: "\u227a",
                preccurlyeq: "\u227c",
                Precedes: "\u227a",
                PrecedesEqual: "\u2aaf",
                PrecedesSlantEqual: "\u227c",
                PrecedesTilde: "\u227e",
                preceq: "\u2aaf",
                precnapprox: "\u2ab9",
                precneqq: "\u2ab5",
                precnsim: "\u22e8",
                pre: "\u2aaf",
                prE: "\u2ab3",
                precsim: "\u227e",
                prime: "\u2032",
                Prime: "\u2033",
                primes: "\u2119",
                prnap: "\u2ab9",
                prnE: "\u2ab5",
                prnsim: "\u22e8",
                prod: "\u220f",
                Product: "\u220f",
                profalar: "\u232e",
                profline: "\u2312",
                profsurf: "\u2313",
                prop: "\u221d",
                Proportional: "\u221d",
                Proportion: "\u2237",
                propto: "\u221d",
                prsim: "\u227e",
                prurel: "\u22b0",
                Pscr: "\ud835\udcab",
                pscr: "\ud835\udcc5",
                Psi: "\u03a8",
                psi: "\u03c8",
                puncsp: "\u2008",
                Qfr: "\ud835\udd14",
                qfr: "\ud835\udd2e",
                qint: "\u2a0c",
                qopf: "\ud835\udd62",
                Qopf: "\u211a",
                qprime: "\u2057",
                Qscr: "\ud835\udcac",
                qscr: "\ud835\udcc6",
                quaternions: "\u210d",
                quatint: "\u2a16",
                quest: "?",
                questeq: "\u225f",
                quot: '"',
                QUOT: '"',
                rAarr: "\u21db",
                race: "\u223d\u0331",
                Racute: "\u0154",
                racute: "\u0155",
                radic: "\u221a",
                raemptyv: "\u29b3",
                rang: "\u27e9",
                Rang: "\u27eb",
                rangd: "\u2992",
                range: "\u29a5",
                rangle: "\u27e9",
                raquo: "\xbb",
                rarrap: "\u2975",
                rarrb: "\u21e5",
                rarrbfs: "\u2920",
                rarrc: "\u2933",
                rarr: "\u2192",
                Rarr: "\u21a0",
                rArr: "\u21d2",
                rarrfs: "\u291e",
                rarrhk: "\u21aa",
                rarrlp: "\u21ac",
                rarrpl: "\u2945",
                rarrsim: "\u2974",
                Rarrtl: "\u2916",
                rarrtl: "\u21a3",
                rarrw: "\u219d",
                ratail: "\u291a",
                rAtail: "\u291c",
                ratio: "\u2236",
                rationals: "\u211a",
                rbarr: "\u290d",
                rBarr: "\u290f",
                RBarr: "\u2910",
                rbbrk: "\u2773",
                rbrace: "}",
                rbrack: "]",
                rbrke: "\u298c",
                rbrksld: "\u298e",
                rbrkslu: "\u2990",
                Rcaron: "\u0158",
                rcaron: "\u0159",
                Rcedil: "\u0156",
                rcedil: "\u0157",
                rceil: "\u2309",
                rcub: "}",
                Rcy: "\u0420",
                rcy: "\u0440",
                rdca: "\u2937",
                rdldhar: "\u2969",
                rdquo: "\u201d",
                rdquor: "\u201d",
                rdsh: "\u21b3",
                real: "\u211c",
                realine: "\u211b",
                realpart: "\u211c",
                reals: "\u211d",
                Re: "\u211c",
                rect: "\u25ad",
                reg: "\xae",
                REG: "\xae",
                ReverseElement: "\u220b",
                ReverseEquilibrium: "\u21cb",
                ReverseUpEquilibrium: "\u296f",
                rfisht: "\u297d",
                rfloor: "\u230b",
                rfr: "\ud835\udd2f",
                Rfr: "\u211c",
                rHar: "\u2964",
                rhard: "\u21c1",
                rharu: "\u21c0",
                rharul: "\u296c",
                Rho: "\u03a1",
                rho: "\u03c1",
                rhov: "\u03f1",
                RightAngleBracket: "\u27e9",
                RightArrowBar: "\u21e5",
                rightarrow: "\u2192",
                RightArrow: "\u2192",
                Rightarrow: "\u21d2",
                RightArrowLeftArrow: "\u21c4",
                rightarrowtail: "\u21a3",
                RightCeiling: "\u2309",
                RightDoubleBracket: "\u27e7",
                RightDownTeeVector: "\u295d",
                RightDownVectorBar: "\u2955",
                RightDownVector: "\u21c2",
                RightFloor: "\u230b",
                rightharpoondown: "\u21c1",
                rightharpoonup: "\u21c0",
                rightleftarrows: "\u21c4",
                rightleftharpoons: "\u21cc",
                rightrightarrows: "\u21c9",
                rightsquigarrow: "\u219d",
                RightTeeArrow: "\u21a6",
                RightTee: "\u22a2",
                RightTeeVector: "\u295b",
                rightthreetimes: "\u22cc",
                RightTriangleBar: "\u29d0",
                RightTriangle: "\u22b3",
                RightTriangleEqual: "\u22b5",
                RightUpDownVector: "\u294f",
                RightUpTeeVector: "\u295c",
                RightUpVectorBar: "\u2954",
                RightUpVector: "\u21be",
                RightVectorBar: "\u2953",
                RightVector: "\u21c0",
                ring: "\u02da",
                risingdotseq: "\u2253",
                rlarr: "\u21c4",
                rlhar: "\u21cc",
                rlm: "\u200f",
                rmoustache: "\u23b1",
                rmoust: "\u23b1",
                rnmid: "\u2aee",
                roang: "\u27ed",
                roarr: "\u21fe",
                robrk: "\u27e7",
                ropar: "\u2986",
                ropf: "\ud835\udd63",
                Ropf: "\u211d",
                roplus: "\u2a2e",
                rotimes: "\u2a35",
                RoundImplies: "\u2970",
                rpar: ")",
                rpargt: "\u2994",
                rppolint: "\u2a12",
                rrarr: "\u21c9",
                Rrightarrow: "\u21db",
                rsaquo: "\u203a",
                rscr: "\ud835\udcc7",
                Rscr: "\u211b",
                rsh: "\u21b1",
                Rsh: "\u21b1",
                rsqb: "]",
                rsquo: "\u2019",
                rsquor: "\u2019",
                rthree: "\u22cc",
                rtimes: "\u22ca",
                rtri: "\u25b9",
                rtrie: "\u22b5",
                rtrif: "\u25b8",
                rtriltri: "\u29ce",
                RuleDelayed: "\u29f4",
                ruluhar: "\u2968",
                rx: "\u211e",
                Sacute: "\u015a",
                sacute: "\u015b",
                sbquo: "\u201a",
                scap: "\u2ab8",
                Scaron: "\u0160",
                scaron: "\u0161",
                Sc: "\u2abc",
                sc: "\u227b",
                sccue: "\u227d",
                sce: "\u2ab0",
                scE: "\u2ab4",
                Scedil: "\u015e",
                scedil: "\u015f",
                Scirc: "\u015c",
                scirc: "\u015d",
                scnap: "\u2aba",
                scnE: "\u2ab6",
                scnsim: "\u22e9",
                scpolint: "\u2a13",
                scsim: "\u227f",
                Scy: "\u0421",
                scy: "\u0441",
                sdotb: "\u22a1",
                sdot: "\u22c5",
                sdote: "\u2a66",
                searhk: "\u2925",
                searr: "\u2198",
                seArr: "\u21d8",
                searrow: "\u2198",
                sect: "\xa7",
                semi: ";",
                seswar: "\u2929",
                setminus: "\u2216",
                setmn: "\u2216",
                sext: "\u2736",
                Sfr: "\ud835\udd16",
                sfr: "\ud835\udd30",
                sfrown: "\u2322",
                sharp: "\u266f",
                SHCHcy: "\u0429",
                shchcy: "\u0449",
                SHcy: "\u0428",
                shcy: "\u0448",
                ShortDownArrow: "\u2193",
                ShortLeftArrow: "\u2190",
                shortmid: "\u2223",
                shortparallel: "\u2225",
                ShortRightArrow: "\u2192",
                ShortUpArrow: "\u2191",
                shy: "\xad",
                Sigma: "\u03a3",
                sigma: "\u03c3",
                sigmaf: "\u03c2",
                sigmav: "\u03c2",
                sim: "\u223c",
                simdot: "\u2a6a",
                sime: "\u2243",
                simeq: "\u2243",
                simg: "\u2a9e",
                simgE: "\u2aa0",
                siml: "\u2a9d",
                simlE: "\u2a9f",
                simne: "\u2246",
                simplus: "\u2a24",
                simrarr: "\u2972",
                slarr: "\u2190",
                SmallCircle: "\u2218",
                smallsetminus: "\u2216",
                smashp: "\u2a33",
                smeparsl: "\u29e4",
                smid: "\u2223",
                smile: "\u2323",
                smt: "\u2aaa",
                smte: "\u2aac",
                smtes: "\u2aac\ufe00",
                SOFTcy: "\u042c",
                softcy: "\u044c",
                solbar: "\u233f",
                solb: "\u29c4",
                sol: "/",
                Sopf: "\ud835\udd4a",
                sopf: "\ud835\udd64",
                spades: "\u2660",
                spadesuit: "\u2660",
                spar: "\u2225",
                sqcap: "\u2293",
                sqcaps: "\u2293\ufe00",
                sqcup: "\u2294",
                sqcups: "\u2294\ufe00",
                Sqrt: "\u221a",
                sqsub: "\u228f",
                sqsube: "\u2291",
                sqsubset: "\u228f",
                sqsubseteq: "\u2291",
                sqsup: "\u2290",
                sqsupe: "\u2292",
                sqsupset: "\u2290",
                sqsupseteq: "\u2292",
                square: "\u25a1",
                Square: "\u25a1",
                SquareIntersection: "\u2293",
                SquareSubset: "\u228f",
                SquareSubsetEqual: "\u2291",
                SquareSuperset: "\u2290",
                SquareSupersetEqual: "\u2292",
                SquareUnion: "\u2294",
                squarf: "\u25aa",
                squ: "\u25a1",
                squf: "\u25aa",
                srarr: "\u2192",
                Sscr: "\ud835\udcae",
                sscr: "\ud835\udcc8",
                ssetmn: "\u2216",
                ssmile: "\u2323",
                sstarf: "\u22c6",
                Star: "\u22c6",
                star: "\u2606",
                starf: "\u2605",
                straightepsilon: "\u03f5",
                straightphi: "\u03d5",
                strns: "\xaf",
                sub: "\u2282",
                Sub: "\u22d0",
                subdot: "\u2abd",
                subE: "\u2ac5",
                sube: "\u2286",
                subedot: "\u2ac3",
                submult: "\u2ac1",
                subnE: "\u2acb",
                subne: "\u228a",
                subplus: "\u2abf",
                subrarr: "\u2979",
                subset: "\u2282",
                Subset: "\u22d0",
                subseteq: "\u2286",
                subseteqq: "\u2ac5",
                SubsetEqual: "\u2286",
                subsetneq: "\u228a",
                subsetneqq: "\u2acb",
                subsim: "\u2ac7",
                subsub: "\u2ad5",
                subsup: "\u2ad3",
                succapprox: "\u2ab8",
                succ: "\u227b",
                succcurlyeq: "\u227d",
                Succeeds: "\u227b",
                SucceedsEqual: "\u2ab0",
                SucceedsSlantEqual: "\u227d",
                SucceedsTilde: "\u227f",
                succeq: "\u2ab0",
                succnapprox: "\u2aba",
                succneqq: "\u2ab6",
                succnsim: "\u22e9",
                succsim: "\u227f",
                SuchThat: "\u220b",
                sum: "\u2211",
                Sum: "\u2211",
                sung: "\u266a",
                sup1: "\xb9",
                sup2: "\xb2",
                sup3: "\xb3",
                sup: "\u2283",
                Sup: "\u22d1",
                supdot: "\u2abe",
                supdsub: "\u2ad8",
                supE: "\u2ac6",
                supe: "\u2287",
                supedot: "\u2ac4",
                Superset: "\u2283",
                SupersetEqual: "\u2287",
                suphsol: "\u27c9",
                suphsub: "\u2ad7",
                suplarr: "\u297b",
                supmult: "\u2ac2",
                supnE: "\u2acc",
                supne: "\u228b",
                supplus: "\u2ac0",
                supset: "\u2283",
                Supset: "\u22d1",
                supseteq: "\u2287",
                supseteqq: "\u2ac6",
                supsetneq: "\u228b",
                supsetneqq: "\u2acc",
                supsim: "\u2ac8",
                supsub: "\u2ad4",
                supsup: "\u2ad6",
                swarhk: "\u2926",
                swarr: "\u2199",
                swArr: "\u21d9",
                swarrow: "\u2199",
                swnwar: "\u292a",
                szlig: "\xdf",
                Tab: " ",
                target: "\u2316",
                Tau: "\u03a4",
                tau: "\u03c4",
                tbrk: "\u23b4",
                Tcaron: "\u0164",
                tcaron: "\u0165",
                Tcedil: "\u0162",
                tcedil: "\u0163",
                Tcy: "\u0422",
                tcy: "\u0442",
                tdot: "\u20db",
                telrec: "\u2315",
                Tfr: "\ud835\udd17",
                tfr: "\ud835\udd31",
                there4: "\u2234",
                therefore: "\u2234",
                Therefore: "\u2234",
                Theta: "\u0398",
                theta: "\u03b8",
                thetasym: "\u03d1",
                thetav: "\u03d1",
                thickapprox: "\u2248",
                thicksim: "\u223c",
                ThickSpace: "\u205f\u200a",
                ThinSpace: "\u2009",
                thinsp: "\u2009",
                thkap: "\u2248",
                thksim: "\u223c",
                THORN: "\xde",
                thorn: "\xfe",
                tilde: "\u02dc",
                Tilde: "\u223c",
                TildeEqual: "\u2243",
                TildeFullEqual: "\u2245",
                TildeTilde: "\u2248",
                timesbar: "\u2a31",
                timesb: "\u22a0",
                times: "\xd7",
                timesd: "\u2a30",
                tint: "\u222d",
                toea: "\u2928",
                topbot: "\u2336",
                topcir: "\u2af1",
                top: "\u22a4",
                Topf: "\ud835\udd4b",
                topf: "\ud835\udd65",
                topfork: "\u2ada",
                tosa: "\u2929",
                tprime: "\u2034",
                trade: "\u2122",
                TRADE: "\u2122",
                triangle: "\u25b5",
                triangledown: "\u25bf",
                triangleleft: "\u25c3",
                trianglelefteq: "\u22b4",
                triangleq: "\u225c",
                triangleright: "\u25b9",
                trianglerighteq: "\u22b5",
                tridot: "\u25ec",
                trie: "\u225c",
                triminus: "\u2a3a",
                TripleDot: "\u20db",
                triplus: "\u2a39",
                trisb: "\u29cd",
                tritime: "\u2a3b",
                trpezium: "\u23e2",
                Tscr: "\ud835\udcaf",
                tscr: "\ud835\udcc9",
                TScy: "\u0426",
                tscy: "\u0446",
                TSHcy: "\u040b",
                tshcy: "\u045b",
                Tstrok: "\u0166",
                tstrok: "\u0167",
                twixt: "\u226c",
                twoheadleftarrow: "\u219e",
                twoheadrightarrow: "\u21a0",
                Uacute: "\xda",
                uacute: "\xfa",
                uarr: "\u2191",
                Uarr: "\u219f",
                uArr: "\u21d1",
                Uarrocir: "\u2949",
                Ubrcy: "\u040e",
                ubrcy: "\u045e",
                Ubreve: "\u016c",
                ubreve: "\u016d",
                Ucirc: "\xdb",
                ucirc: "\xfb",
                Ucy: "\u0423",
                ucy: "\u0443",
                udarr: "\u21c5",
                Udblac: "\u0170",
                udblac: "\u0171",
                udhar: "\u296e",
                ufisht: "\u297e",
                Ufr: "\ud835\udd18",
                ufr: "\ud835\udd32",
                Ugrave: "\xd9",
                ugrave: "\xf9",
                uHar: "\u2963",
                uharl: "\u21bf",
                uharr: "\u21be",
                uhblk: "\u2580",
                ulcorn: "\u231c",
                ulcorner: "\u231c",
                ulcrop: "\u230f",
                ultri: "\u25f8",
                Umacr: "\u016a",
                umacr: "\u016b",
                uml: "\xa8",
                UnderBar: "_",
                UnderBrace: "\u23df",
                UnderBracket: "\u23b5",
                UnderParenthesis: "\u23dd",
                Union: "\u22c3",
                UnionPlus: "\u228e",
                Uogon: "\u0172",
                uogon: "\u0173",
                Uopf: "\ud835\udd4c",
                uopf: "\ud835\udd66",
                UpArrowBar: "\u2912",
                uparrow: "\u2191",
                UpArrow: "\u2191",
                Uparrow: "\u21d1",
                UpArrowDownArrow: "\u21c5",
                updownarrow: "\u2195",
                UpDownArrow: "\u2195",
                Updownarrow: "\u21d5",
                UpEquilibrium: "\u296e",
                upharpoonleft: "\u21bf",
                upharpoonright: "\u21be",
                uplus: "\u228e",
                UpperLeftArrow: "\u2196",
                UpperRightArrow: "\u2197",
                upsi: "\u03c5",
                Upsi: "\u03d2",
                upsih: "\u03d2",
                Upsilon: "\u03a5",
                upsilon: "\u03c5",
                UpTeeArrow: "\u21a5",
                UpTee: "\u22a5",
                upuparrows: "\u21c8",
                urcorn: "\u231d",
                urcorner: "\u231d",
                urcrop: "\u230e",
                Uring: "\u016e",
                uring: "\u016f",
                urtri: "\u25f9",
                Uscr: "\ud835\udcb0",
                uscr: "\ud835\udcca",
                utdot: "\u22f0",
                Utilde: "\u0168",
                utilde: "\u0169",
                utri: "\u25b5",
                utrif: "\u25b4",
                uuarr: "\u21c8",
                Uuml: "\xdc",
                uuml: "\xfc",
                uwangle: "\u29a7",
                vangrt: "\u299c",
                varepsilon: "\u03f5",
                varkappa: "\u03f0",
                varnothing: "\u2205",
                varphi: "\u03d5",
                varpi: "\u03d6",
                varpropto: "\u221d",
                varr: "\u2195",
                vArr: "\u21d5",
                varrho: "\u03f1",
                varsigma: "\u03c2",
                varsubsetneq: "\u228a\ufe00",
                varsubsetneqq: "\u2acb\ufe00",
                varsupsetneq: "\u228b\ufe00",
                varsupsetneqq: "\u2acc\ufe00",
                vartheta: "\u03d1",
                vartriangleleft: "\u22b2",
                vartriangleright: "\u22b3",
                vBar: "\u2ae8",
                Vbar: "\u2aeb",
                vBarv: "\u2ae9",
                Vcy: "\u0412",
                vcy: "\u0432",
                vdash: "\u22a2",
                vDash: "\u22a8",
                Vdash: "\u22a9",
                VDash: "\u22ab",
                Vdashl: "\u2ae6",
                veebar: "\u22bb",
                vee: "\u2228",
                Vee: "\u22c1",
                veeeq: "\u225a",
                vellip: "\u22ee",
                verbar: "|",
                Verbar: "\u2016",
                vert: "|",
                Vert: "\u2016",
                VerticalBar: "\u2223",
                VerticalLine: "|",
                VerticalSeparator: "\u2758",
                VerticalTilde: "\u2240",
                VeryThinSpace: "\u200a",
                Vfr: "\ud835\udd19",
                vfr: "\ud835\udd33",
                vltri: "\u22b2",
                vnsub: "\u2282\u20d2",
                vnsup: "\u2283\u20d2",
                Vopf: "\ud835\udd4d",
                vopf: "\ud835\udd67",
                vprop: "\u221d",
                vrtri: "\u22b3",
                Vscr: "\ud835\udcb1",
                vscr: "\ud835\udccb",
                vsubnE: "\u2acb\ufe00",
                vsubne: "\u228a\ufe00",
                vsupnE: "\u2acc\ufe00",
                vsupne: "\u228b\ufe00",
                Vvdash: "\u22aa",
                vzigzag: "\u299a",
                Wcirc: "\u0174",
                wcirc: "\u0175",
                wedbar: "\u2a5f",
                wedge: "\u2227",
                Wedge: "\u22c0",
                wedgeq: "\u2259",
                weierp: "\u2118",
                Wfr: "\ud835\udd1a",
                wfr: "\ud835\udd34",
                Wopf: "\ud835\udd4e",
                wopf: "\ud835\udd68",
                wp: "\u2118",
                wr: "\u2240",
                wreath: "\u2240",
                Wscr: "\ud835\udcb2",
                wscr: "\ud835\udccc",
                xcap: "\u22c2",
                xcirc: "\u25ef",
                xcup: "\u22c3",
                xdtri: "\u25bd",
                Xfr: "\ud835\udd1b",
                xfr: "\ud835\udd35",
                xharr: "\u27f7",
                xhArr: "\u27fa",
                Xi: "\u039e",
                xi: "\u03be",
                xlarr: "\u27f5",
                xlArr: "\u27f8",
                xmap: "\u27fc",
                xnis: "\u22fb",
                xodot: "\u2a00",
                Xopf: "\ud835\udd4f",
                xopf: "\ud835\udd69",
                xoplus: "\u2a01",
                xotime: "\u2a02",
                xrarr: "\u27f6",
                xrArr: "\u27f9",
                Xscr: "\ud835\udcb3",
                xscr: "\ud835\udccd",
                xsqcup: "\u2a06",
                xuplus: "\u2a04",
                xutri: "\u25b3",
                xvee: "\u22c1",
                xwedge: "\u22c0",
                Yacute: "\xdd",
                yacute: "\xfd",
                YAcy: "\u042f",
                yacy: "\u044f",
                Ycirc: "\u0176",
                ycirc: "\u0177",
                Ycy: "\u042b",
                ycy: "\u044b",
                yen: "\xa5",
                Yfr: "\ud835\udd1c",
                yfr: "\ud835\udd36",
                YIcy: "\u0407",
                yicy: "\u0457",
                Yopf: "\ud835\udd50",
                yopf: "\ud835\udd6a",
                Yscr: "\ud835\udcb4",
                yscr: "\ud835\udcce",
                YUcy: "\u042e",
                yucy: "\u044e",
                yuml: "\xff",
                Yuml: "\u0178",
                Zacute: "\u0179",
                zacute: "\u017a",
                Zcaron: "\u017d",
                zcaron: "\u017e",
                Zcy: "\u0417",
                zcy: "\u0437",
                Zdot: "\u017b",
                zdot: "\u017c",
                zeetrf: "\u2128",
                ZeroWidthSpace: "\u200b",
                Zeta: "\u0396",
                zeta: "\u03b6",
                zfr: "\ud835\udd37",
                Zfr: "\u2128",
                ZHcy: "\u0416",
                zhcy: "\u0436",
                zigrarr: "\u21dd",
                zopf: "\ud835\udd6b",
                Zopf: "\u2124",
                Zscr: "\ud835\udcb5",
                zscr: "\ud835\udccf",
                zwj: "\u200d",
                zwnj: "\u200c"
            }
        }, {}],
        53: [function(e, r, t) {
            "use strict";

            function n(e) {
                var r = Array.prototype.slice.call(arguments, 1);
                return r.forEach(function(r) {
                    r && Object.keys(r).forEach(function(t) {
                        e[t] = r[t]
                    })
                }), e
            }

            function s(e) {
                return Object.prototype.toString.call(e)
            }

            function i(e) {
                return "[object String]" === s(e)
            }

            function o(e) {
                return "[object Object]" === s(e)
            }

            function a(e) {
                return "[object RegExp]" === s(e)
            }

            function c(e) {
                return "[object Function]" === s(e)
            }

            function l(e) {
                return e.replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&")
            }

            function u(e) {
                return Object.keys(e || {}).reduce(function(e, r) {
                    return e || b.hasOwnProperty(r)
                }, !1)
            }

            function p(e) {
                e.__index__ = -1, e.__text_cache__ = ""
            }

            function h(e) {
                return function(r, t) {
                    var n = r.slice(t);
                    return e.test(n) ? n.match(e)[0].length : 0
                }
            }

            function f() {
                return function(e, r) {
                    r.normalize(e)
                }
            }

            function d(r) {
                function t(e) {
                    return e.replace("%TLDS%", u.src_tlds)
                }

                function s(e, r) {
                    throw new Error('(LinkifyIt) Invalid schema "' + e + '": ' + r)
                }
                var u = r.re = n({}, e("./lib/re")),
                    d = r.__tlds__.slice();
                r.__tlds_replaced__ || d.push(v), d.push(u.src_xn), u.src_tlds = d.join("|"), u.email_fuzzy = RegExp(t(u.tpl_email_fuzzy), "i"), u.link_fuzzy = RegExp(t(u.tpl_link_fuzzy), "i"), u.link_no_ip_fuzzy = RegExp(t(u.tpl_link_no_ip_fuzzy), "i"), u.host_fuzzy_test = RegExp(t(u.tpl_host_fuzzy_test), "i");
                var m = [];
                r.__compiled__ = {}, Object.keys(r.__schemas__).forEach(function(e) {
                    var t = r.__schemas__[e];
                    if (null !== t) {
                        var n = {
                            validate: null,
                            link: null
                        };
                        return r.__compiled__[e] = n, o(t) ? (a(t.validate) ? n.validate = h(t.validate) : c(t.validate) ? n.validate = t.validate : s(e, t), void(c(t.normalize) ? n.normalize = t.normalize : t.normalize ? s(e, t) : n.normalize = f())) : i(t) ? void m.push(e) : void s(e, t)
                    }
                }), m.forEach(function(e) {
                    r.__compiled__[r.__schemas__[e]] && (r.__compiled__[e].validate = r.__compiled__[r.__schemas__[e]].validate, r.__compiled__[e].normalize = r.__compiled__[r.__schemas__[e]].normalize)
                }), r.__compiled__[""] = {
                    validate: null,
                    normalize: f()
                };
                var g = Object.keys(r.__compiled__).filter(function(e) {
                    return e.length > 0 && r.__compiled__[e]
                }).map(l).join("|");
                r.re.schema_test = RegExp("(^|(?!_)(?:>|" + u.src_ZPCc + "))(" + g + ")", "i"), r.re.schema_search = RegExp("(^|(?!_)(?:>|" + u.src_ZPCc + "))(" + g + ")", "ig"), r.re.pretest = RegExp("(" + r.re.schema_test.source + ")|(" + r.re.host_fuzzy_test.source + ")|@", "i"), p(r)
            }

            function m(e, r) {
                var t = e.__index__,
                    n = e.__last_index__,
                    s = e.__text_cache__.slice(t, n);
                this.schema = e.__schema__.toLowerCase(), this.index = t + r, this.lastIndex = n + r, this.raw = s, this.text = s, this.url = s
            }

            function g(e, r) {
                var t = new m(e, r);
                return e.__compiled__[t.schema].normalize(t, e), t
            }

            function _(e, r) {
                return this instanceof _ ? (r || u(e) && (r = e, e = {}), this.__opts__ = n({}, b, r), this.__index__ = -1, this.__last_index__ = -1, this.__schema__ = "", this.__text_cache__ = "", this.__schemas__ = n({}, k, e), this.__compiled__ = {}, this.__tlds__ = x, this.__tlds_replaced__ = !1, this.re = {}, void d(this)) : new _(e, r)
            }
            var b = {
                    fuzzyLink: !0,
                    fuzzyEmail: !0,
                    fuzzyIP: !1
                },
                k = {
                    "http:": {
                        validate: function(e, r, t) {
                            var n = e.slice(r);
                            return t.re.http || (t.re.http = new RegExp("^\\/\\/" + t.re.src_auth + t.re.src_host_port_strict + t.re.src_path, "i")), t.re.http.test(n) ? n.match(t.re.http)[0].length : 0
                        }
                    },
                    "https:": "http:",
                    "ftp:": "http:",
                    "//": {
                        validate: function(e, r, t) {
                            var n = e.slice(r);
                            return t.re.no_http || (t.re.no_http = new RegExp("^" + t.re.src_auth + t.re.src_host_port_strict + t.re.src_path, "i")), t.re.no_http.test(n) ? r >= 3 && ":" === e[r - 3] ? 0 : n.match(t.re.no_http)[0].length : 0
                        }
                    },
                    "mailto:": {
                        validate: function(e, r, t) {
                            var n = e.slice(r);
                            return t.re.mailto || (t.re.mailto = new RegExp("^" + t.re.src_email_name + "@" + t.re.src_host_strict, "i")), t.re.mailto.test(n) ? n.match(t.re.mailto)[0].length : 0
                        }
                    }
                },
                v = "a[cdefgilmnoqrstuwxz]|b[abdefghijmnorstvwyz]|c[acdfghiklmnoruvwxyz]|d[ejkmoz]|e[cegrstu]|f[ijkmor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcikrstuvy]|m[acdeghklmnopqrstuvwxyz]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eosuw]|s[abcdeghijklmnortuvxyz]|t[cdfghjklmnortvwz]|u[agksyz]|v[aceginu]|w[fs]|y[et]|z[amw]",
                x = "biz|com|edu|gov|net|org|pro|web|xxx|aero|asia|coop|info|museum|name|shop|\u0440\u0444".split("|");
            _.prototype.add = function(e, r) {
                return this.__schemas__[e] = r, d(this), this
            }, _.prototype.set = function(e) {
                return this.__opts__ = n(this.__opts__, e), this
            }, _.prototype.test = function(e) {
                if (this.__text_cache__ = e, this.__index__ = -1, !e.length) return !1;
                var r, t, n, s, i, o, a, c, l;
                if (this.re.schema_test.test(e))
                    for (a = this.re.schema_search, a.lastIndex = 0; null !== (r = a.exec(e));)
                        if (s = this.testSchemaAt(e, r[2], a.lastIndex)) {
                            this.__schema__ = r[2], this.__index__ = r.index + r[1].length, this.__last_index__ = r.index + r[0].length + s;
                            break
                        }
                return this.__opts__.fuzzyLink && this.__compiled__["http:"] && (c = e.search(this.re.host_fuzzy_test), c >= 0 && (this.__index__ < 0 || c < this.__index__) && null !== (t = e.match(this.__opts__.fuzzyIP ? this.re.link_fuzzy : this.re.link_no_ip_fuzzy)) && (i = t.index + t[1].length, (this.__index__ < 0 || i < this.__index__) && (this.__schema__ = "", this.__index__ = i, this.__last_index__ = t.index + t[0].length))), this.__opts__.fuzzyEmail && this.__compiled__["mailto:"] && (l = e.indexOf("@"), l >= 0 && null !== (n = e.match(this.re.email_fuzzy)) && (i = n.index + n[1].length, o = n.index + n[0].length, (this.__index__ < 0 || i < this.__index__ || i === this.__index__ && o > this.__last_index__) && (this.__schema__ = "mailto:", this.__index__ = i, this.__last_index__ = o))), this.__index__ >= 0
            }, _.prototype.pretest = function(e) {
                return this.re.pretest.test(e)
            }, _.prototype.testSchemaAt = function(e, r, t) {
                return this.__compiled__[r.toLowerCase()] ? this.__compiled__[r.toLowerCase()].validate(e, t, this) : 0
            }, _.prototype.match = function(e) {
                var r = 0,
                    t = [];
                this.__index__ >= 0 && this.__text_cache__ === e && (t.push(g(this, r)), r = this.__last_index__);
                for (var n = r ? e.slice(r) : e; this.test(n);) t.push(g(this, r)), n = n.slice(this.__last_index__), r += this.__last_index__;
                return t.length ? t : null
            }, _.prototype.tlds = function(e, r) {
                return e = Array.isArray(e) ? e : [e], r ? (this.__tlds__ = this.__tlds__.concat(e).sort().filter(function(e, r, t) {
                    return e !== t[r - 1]
                }).reverse(), d(this), this) : (this.__tlds__ = e.slice(), this.__tlds_replaced__ = !0, d(this), this)
            }, _.prototype.normalize = function(e) {
                e.schema || (e.url = "http://" + e.url), "mailto:" !== e.schema || /^mailto:/i.test(e.url) || (e.url = "mailto:" + e.url)
            }, r.exports = _
        }, {
            "./lib/re": 54
        }],
        54: [function(e, r, t) {
            "use strict";
            var n = t.src_Any = e("uc.micro/properties/Any/regex").source,
                s = t.src_Cc = e("uc.micro/categories/Cc/regex").source,
                i = t.src_Z = e("uc.micro/categories/Z/regex").source,
                o = t.src_P = e("uc.micro/categories/P/regex").source,
                a = t.src_ZPCc = [i, o, s].join("|"),
                c = t.src_ZCc = [i, s].join("|"),
                l = "(?:(?!" + a + ")" + n + ")",
                u = "(?:(?![0-9]|" + a + ")" + n + ")",
                p = t.src_ip4 = "(?:(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)";
            t.src_auth = "(?:(?:(?!" + c + ").)+@)?";
            var h = t.src_port = "(?::(?:6(?:[0-4]\\d{3}|5(?:[0-4]\\d{2}|5(?:[0-2]\\d|3[0-5])))|[1-5]?\\d{1,4}))?",
                f = t.src_host_terminator = "(?=$|" + a + ")(?!-|_|:\\d|\\.-|\\.(?!$|" + a + "))",
                d = t.src_path = "(?:[/?#](?:(?!" + c + "|[()[\\]{}.,\"'?!\\-]).|\\[(?:(?!" + c + "|\\]).)*\\]|\\((?:(?!" + c + "|[)]).)*\\)|\\{(?:(?!" + c + '|[}]).)*\\}|\\"(?:(?!' + c + '|["]).)+\\"|\\\'(?:(?!' + c + "|[']).)+\\'|\\'(?=" + l + ").|\\.{2,3}[a-zA-Z0-9%]|\\.(?!" + c + "|[.]).|\\-(?!" + c + "|--(?:[^-]|$))(?:[-]+|.)|\\,(?!" + c + ").|\\!(?!" + c + "|[!]).|\\?(?!" + c + "|[?]).)+|\\/)?",
                m = t.src_email_name = '[\\-;:&=\\+\\$,\\"\\.a-zA-Z0-9_]+',
                g = t.src_xn = "xn--[a-z0-9\\-]{1,59}",
                _ = t.src_domain_root = "(?:" + g + "|" + u + "{1,63})",
                b = t.src_domain = "(?:" + g + "|(?:" + l + ")|(?:" + l + "(?:-(?!-)|" + l + "){0,61}" + l + "))",
                k = t.src_host = "(?:" + p + "|(?:(?:(?:" + b + ")\\.)*" + _ + "))",
                v = t.tpl_host_fuzzy = "(?:" + p + "|(?:(?:(?:" + b + ")\\.)+(?:%TLDS%)))",
                x = t.tpl_host_no_ip_fuzzy = "(?:(?:(?:" + b + ")\\.)+(?:%TLDS%))";
            t.src_host_strict = k + f;
            var y = t.tpl_host_fuzzy_strict = v + f;
            t.src_host_port_strict = k + h + f;
            var A = t.tpl_host_port_fuzzy_strict = v + h + f,
                C = t.tpl_host_port_no_ip_fuzzy_strict = x + h + f;
            t.tpl_host_fuzzy_test = "localhost|\\.\\d{1,3}\\.|(?:\\.(?:%TLDS%)(?:" + a + "|$))", t.tpl_email_fuzzy = "(^|>|" + c + ")(" + m + "@" + y + ")", t.tpl_link_fuzzy = "(^|(?![.:/\\-_@])(?:[$+<=>^`|]|" + a + "))((?![$+<=>^`|])" + A + d + ")", t.tpl_link_no_ip_fuzzy = "(^|(?![.:/\\-_@])(?:[$+<=>^`|]|" + a + "))((?![$+<=>^`|])" + C + d + ")"
        }, {
            "uc.micro/categories/Cc/regex": 60,
            "uc.micro/categories/P/regex": 62,
            "uc.micro/categories/Z/regex": 63,
            "uc.micro/properties/Any/regex": 65
        }],
        55: [function(e, r, t) {
            "use strict";

            function n(e) {
                var r, t, n = i[e];
                if (n) return n;
                for (n = i[e] = [], r = 0; 128 > r; r++) t = String.fromCharCode(r), n.push(t);
                for (r = 0; r < e.length; r++) t = e.charCodeAt(r), n[t] = "%" + ("0" + t.toString(16).toUpperCase()).slice(-2);
                return n
            }

            function s(e, r) {
                var t;
                return "string" != typeof r && (r = s.defaultChars), t = n(r), e.replace(/(%[a-f0-9]{2})+/gi, function(e) {
                    var r, n, s, i, o, a, c, l = "";
                    for (r = 0, n = e.length; n > r; r += 3) s = parseInt(e.slice(r + 1, r + 3), 16), 128 > s ? l += t[s] : 192 === (224 & s) && n > r + 3 && (i = parseInt(e.slice(r + 4, r + 6), 16), 128 === (192 & i)) ? (c = s << 6 & 1984 | 63 & i, l += 128 > c ? "\ufffd\ufffd" : String.fromCharCode(c), r += 3) : 224 === (240 & s) && n > r + 6 && (i = parseInt(e.slice(r + 4, r + 6), 16), o = parseInt(e.slice(r + 7, r + 9), 16), 128 === (192 & i) && 128 === (192 & o)) ? (c = s << 12 & 61440 | i << 6 & 4032 | 63 & o, l += 2048 > c || c >= 55296 && 57343 >= c ? "\ufffd\ufffd\ufffd" : String.fromCharCode(c), r += 6) : 240 === (248 & s) && n > r + 9 && (i = parseInt(e.slice(r + 4, r + 6), 16), o = parseInt(e.slice(r + 7, r + 9), 16), a = parseInt(e.slice(r + 10, r + 12), 16), 128 === (192 & i) && 128 === (192 & o) && 128 === (192 & a)) ? (c = s << 18 & 1835008 | i << 12 & 258048 | o << 6 & 4032 | 63 & a, 65536 > c || c > 1114111 ? l += "\ufffd\ufffd\ufffd\ufffd" : (c -= 65536, l += String.fromCharCode(55296 + (c >> 10), 56320 + (1023 & c))), r += 9) : l += "\ufffd";
                    return l
                })
            }
            var i = {};
            s.defaultChars = ";/?:@&=+$,#", s.componentChars = "", r.exports = s
        }, {}],
        56: [function(e, r, t) {
            "use strict";

            function n(e) {
                var r, t, n = i[e];
                if (n) return n;
                for (n = i[e] = [], r = 0; 128 > r; r++) t = String.fromCharCode(r), n.push(/^[0-9a-z]$/i.test(t) ? t : "%" + ("0" + r.toString(16).toUpperCase()).slice(-2));
                for (r = 0; r < e.length; r++) n[e.charCodeAt(r)] = e[r];
                return n
            }

            function s(e, r, t) {
                var i, o, a, c, l, u = "";
                for ("string" != typeof r && (t = r, r = s.defaultChars),
                    "undefined" == typeof t && (t = !0), l = n(r), i = 0, o = e.length; o > i; i++)
                    if (a = e.charCodeAt(i), t && 37 === a && o > i + 2 && /^[0-9a-f]{2}$/i.test(e.slice(i + 1, i + 3))) u += e.slice(i, i + 3), i += 2;
                    else if (128 > a) u += l[a];
                else if (a >= 55296 && 57343 >= a) {
                    if (a >= 55296 && 56319 >= a && o > i + 1 && (c = e.charCodeAt(i + 1), c >= 56320 && 57343 >= c)) {
                        u += encodeURIComponent(e[i] + e[i + 1]), i++;
                        continue
                    }
                    u += "%EF%BF%BD"
                } else u += encodeURIComponent(e[i]);
                return u
            }
            var i = {};
            s.defaultChars = ";/?:@&=+$,-_.!~*'()#", s.componentChars = "-_.!~*'()", r.exports = s
        }, {}],
        57: [function(e, r, t) {
            "use strict";
            r.exports = function(e) {
                var r = "";
                return r += e.protocol || "", r += e.slashes ? "//" : "", r += e.auth ? e.auth + "@" : "", r += e.hostname && -1 !== e.hostname.indexOf(":") ? "[" + e.hostname + "]" : e.hostname || "", r += e.port ? ":" + e.port : "", r += e.pathname || "", r += e.search || "", r += e.hash || ""
            }
        }, {}],
        58: [function(e, r, t) {
            "use strict";
            r.exports.encode = e("./encode"), r.exports.decode = e("./decode"), r.exports.format = e("./format"), r.exports.parse = e("./parse")
        }, {
            "./decode": 55,
            "./encode": 56,
            "./format": 57,
            "./parse": 59
        }],
        59: [function(e, r, t) {
            "use strict";

            function n() {
                this.protocol = null, this.slashes = null, this.auth = null, this.port = null, this.hostname = null, this.hash = null, this.search = null, this.pathname = null
            }

            function s(e, r) {
                if (e && e instanceof n) return e;
                var t = new n;
                return t.parse(e, r), t
            }
            var i = /^([a-z0-9.+-]+:)/i,
                o = /:[0-9]*$/,
                a = /^(\/\/?(?!\/)[^\?\s]*)(\?[^\s]*)?$/,
                c = ["<", ">", '"', "`", " ", "\r", "\n", " "],
                l = ["{", "}", "|", "\\", "^", "`"].concat(c),
                u = ["'"].concat(l),
                p = ["%", "/", "?", ";", "#"].concat(u),
                h = ["/", "?", "#"],
                f = 255,
                d = /^[+a-z0-9A-Z_-]{0,63}$/,
                m = /^([+a-z0-9A-Z_-]{0,63})(.*)$/,
                g = {
                    javascript: !0,
                    "javascript:": !0
                },
                _ = {
                    http: !0,
                    https: !0,
                    ftp: !0,
                    gopher: !0,
                    file: !0,
                    "http:": !0,
                    "https:": !0,
                    "ftp:": !0,
                    "gopher:": !0,
                    "file:": !0
                };
            n.prototype.parse = function(e, r) {
                var t, n, s, o, c, l = e;
                if (l = l.trim(), !r && 1 === e.split("#").length) {
                    var u = a.exec(l);
                    if (u) return this.pathname = u[1], u[2] && (this.search = u[2]), this
                }
                var b = i.exec(l);
                if (b && (b = b[0], s = b.toLowerCase(), this.protocol = b, l = l.substr(b.length)), (r || b || l.match(/^\/\/[^@\/]+@[^@\/]+/)) && (c = "//" === l.substr(0, 2), !c || b && g[b] || (l = l.substr(2), this.slashes = !0)), !g[b] && (c || b && !_[b])) {
                    var k = -1;
                    for (t = 0; t < h.length; t++) o = l.indexOf(h[t]), -1 !== o && (-1 === k || k > o) && (k = o);
                    var v, x;
                    for (x = -1 === k ? l.lastIndexOf("@") : l.lastIndexOf("@", k), -1 !== x && (v = l.slice(0, x), l = l.slice(x + 1), this.auth = v), k = -1, t = 0; t < p.length; t++) o = l.indexOf(p[t]), -1 !== o && (-1 === k || k > o) && (k = o); - 1 === k && (k = l.length), ":" === l[k - 1] && k--;
                    var y = l.slice(0, k);
                    l = l.slice(k), this.parseHost(y), this.hostname = this.hostname || "";
                    var A = "[" === this.hostname[0] && "]" === this.hostname[this.hostname.length - 1];
                    if (!A) {
                        var C = this.hostname.split(/\./);
                        for (t = 0, n = C.length; n > t; t++) {
                            var w = C[t];
                            if (w && !w.match(d)) {
                                for (var q = "", D = 0, E = w.length; E > D; D++) q += w.charCodeAt(D) > 127 ? "x" : w[D];
                                if (!q.match(d)) {
                                    var S = C.slice(0, t),
                                        F = C.slice(t + 1),
                                        z = w.match(m);
                                    z && (S.push(z[1]), F.unshift(z[2])), F.length && (l = F.join(".") + l), this.hostname = S.join(".");
                                    break
                                }
                            }
                        }
                    }
                    this.hostname.length > f && (this.hostname = ""), A && (this.hostname = this.hostname.substr(1, this.hostname.length - 2))
                }
                var L = l.indexOf("#"); - 1 !== L && (this.hash = l.substr(L), l = l.slice(0, L));
                var T = l.indexOf("?");
                return -1 !== T && (this.search = l.substr(T), l = l.slice(0, T)), l && (this.pathname = l), _[s] && this.hostname && !this.pathname && (this.pathname = ""), this
            }, n.prototype.parseHost = function(e) {
                var r = o.exec(e);
                r && (r = r[0], ":" !== r && (this.port = r.substr(1)), e = e.substr(0, e.length - r.length)), e && (this.hostname = e)
            }, r.exports = s
        }, {}],
        60: [function(e, r, t) {
            r.exports = /[\0-\x1F\x7F-\x9F]/
        }, {}],
        61: [function(e, r, t) {
            r.exports = /[\xAD\u0600-\u0605\u061C\u06DD\u070F\u180E\u200B-\u200F\u202A-\u202E\u2060-\u2064\u2066-\u206F\uFEFF\uFFF9-\uFFFB]|\uD804\uDCBD|\uD82F[\uDCA0-\uDCA3]|\uD834[\uDD73-\uDD7A]|\uDB40[\uDC01\uDC20-\uDC7F]/
        }, {}],
        62: [function(e, r, t) {
            r.exports = /[!-#%-\*,-/:;\?@\[-\]_\{\}\xA1\xA7\xAB\xB6\xB7\xBB\xBF\u037E\u0387\u055A-\u055F\u0589\u058A\u05BE\u05C0\u05C3\u05C6\u05F3\u05F4\u0609\u060A\u060C\u060D\u061B\u061E\u061F\u066A-\u066D\u06D4\u0700-\u070D\u07F7-\u07F9\u0830-\u083E\u085E\u0964\u0965\u0970\u0AF0\u0DF4\u0E4F\u0E5A\u0E5B\u0F04-\u0F12\u0F14\u0F3A-\u0F3D\u0F85\u0FD0-\u0FD4\u0FD9\u0FDA\u104A-\u104F\u10FB\u1360-\u1368\u1400\u166D\u166E\u169B\u169C\u16EB-\u16ED\u1735\u1736\u17D4-\u17D6\u17D8-\u17DA\u1800-\u180A\u1944\u1945\u1A1E\u1A1F\u1AA0-\u1AA6\u1AA8-\u1AAD\u1B5A-\u1B60\u1BFC-\u1BFF\u1C3B-\u1C3F\u1C7E\u1C7F\u1CC0-\u1CC7\u1CD3\u2010-\u2027\u2030-\u2043\u2045-\u2051\u2053-\u205E\u207D\u207E\u208D\u208E\u2308-\u230B\u2329\u232A\u2768-\u2775\u27C5\u27C6\u27E6-\u27EF\u2983-\u2998\u29D8-\u29DB\u29FC\u29FD\u2CF9-\u2CFC\u2CFE\u2CFF\u2D70\u2E00-\u2E2E\u2E30-\u2E42\u3001-\u3003\u3008-\u3011\u3014-\u301F\u3030\u303D\u30A0\u30FB\uA4FE\uA4FF\uA60D-\uA60F\uA673\uA67E\uA6F2-\uA6F7\uA874-\uA877\uA8CE\uA8CF\uA8F8-\uA8FA\uA92E\uA92F\uA95F\uA9C1-\uA9CD\uA9DE\uA9DF\uAA5C-\uAA5F\uAADE\uAADF\uAAF0\uAAF1\uABEB\uFD3E\uFD3F\uFE10-\uFE19\uFE30-\uFE52\uFE54-\uFE61\uFE63\uFE68\uFE6A\uFE6B\uFF01-\uFF03\uFF05-\uFF0A\uFF0C-\uFF0F\uFF1A\uFF1B\uFF1F\uFF20\uFF3B-\uFF3D\uFF3F\uFF5B\uFF5D\uFF5F-\uFF65]|\uD800[\uDD00-\uDD02\uDF9F\uDFD0]|\uD801\uDD6F|\uD802[\uDC57\uDD1F\uDD3F\uDE50-\uDE58\uDE7F\uDEF0-\uDEF6\uDF39-\uDF3F\uDF99-\uDF9C]|\uD804[\uDC47-\uDC4D\uDCBB\uDCBC\uDCBE-\uDCC1\uDD40-\uDD43\uDD74\uDD75\uDDC5-\uDDC8\uDDCD\uDE38-\uDE3D]|\uD805[\uDCC6\uDDC1-\uDDC9\uDE41-\uDE43]|\uD809[\uDC70-\uDC74]|\uD81A[\uDE6E\uDE6F\uDEF5\uDF37-\uDF3B\uDF44]|\uD82F\uDC9F/
        }, {}],
        63: [function(e, r, t) {
            r.exports = /[ \xA0\u1680\u2000-\u200A\u2028\u2029\u202F\u205F\u3000]/
        }, {}],
        64: [function(e, r, t) {
            r.exports.Any = e("./properties/Any/regex"), r.exports.Cc = e("./categories/Cc/regex"), r.exports.Cf = e("./categories/Cf/regex"), r.exports.P = e("./categories/P/regex"), r.exports.Z = e("./categories/Z/regex")
        }, {
            "./categories/Cc/regex": 60,
            "./categories/Cf/regex": 61,
            "./categories/P/regex": 62,
            "./categories/Z/regex": 63,
            "./properties/Any/regex": 65
        }],
        65: [function(e, r, t) {
            r.exports = /[\0-\uD7FF\uDC00-\uFFFF]|[\uD800-\uDBFF][\uDC00-\uDFFF]|[\uD800-\uDBFF]/
        }, {}],
        66: [function(e, r, t) {
            "use strict";
            r.exports = e("./lib/")
        }, {
            "./lib/": 10
        }]
    }, {}, [66])(66)
});

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
