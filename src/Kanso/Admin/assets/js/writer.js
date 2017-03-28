// ##############################################################################
// FILE: Libs/Writer/variables.js
// ##############################################################################

// ##############################################################################
// KANSO WRITER APPLICATION START
// ##############################################################################
(function() {

    // Helpers
    function closest(e,t){if(t=t.toLowerCase(),"undefined"==typeof e)return null;if(e.nodeName.toLowerCase()===t)return e;if(e.parentNode&&e.parentNode.nodeName.toLowerCase()===t)return e.parentNode;for(var n=e.parentNode;n!==document.body&&"undefined"!=typeof n&&null!==n;)if(n=n.parentNode,n&&n.nodeName.toLowerCase()===t)return n;return null}function parentUntillClass(e,t){if(hasClass(e,t))return e;if(hasClass(e.parentNode,t))return e.parentNode;for(var n=e.parentNode;n!==document.body;)if(n=n.parentNode,hasClass(n,t))return n;return null}function nextUntillType(e,t){if(t=t.toLowerCase(),e.nextSibling&&e.nextSibling.nodeName.toLowerCase===t)return e.nextSibling;for(var n=e.nextSibling;n!==document.body&&"undefined"!=typeof n&&null!==n;)if(n=n.nextSibling,n&&n.nodeName.toLowerCase()===t)return n;return null}function isJSON(e){var t;try{t=JSON.parse(e)}catch(n){return!1}return t}function newNode(e,t,n,r,i){var o=document.createElement(e);return t="undefined"==typeof t?null:t,n="undefined"==typeof n?null:n,r="undefined"==typeof r?null:r,null!==t&&(o.className=t),null!==n&&(o.id=n),null!==r&&(o.innerHTML=r),i.appendChild(o),o}function nodeExists(e){return"undefined"!=typeof e&&null!==e&&"undefined"!=typeof e.parentNode&&null!==e.parentNode}function makeid(e){for(var t="",n="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz",r=0;e>r;r++)t+=n.charAt(Math.floor(Math.random()*n.length));return t}function removeFromDOM(e){nodeExists(e)&&e.parentNode.removeChild(e)}function addClass(e,t){if(nodeExists(e))if("[object Array]"!==Object.prototype.toString.call(t))e.classList.add(t);else for(var n=0;n<t.length;n++)e.classList.add(t[n])}function removeClass(e,t){if(nodeExists(e))if("[object Array]"!==Object.prototype.toString.call(t))e.classList.remove(t);else for(var n=0;n<t.length;n++)e.classList.remove(t[n])}function hasClass(e,t){if(!nodeExists(e))return!1;if("[object Array]"===Object.prototype.toString.call(t)){for(var n=0;n<t.length;n++)if(e.classList.contains(t[n]))return!0;return!1}return e.classList.contains(t)}function removeClassNodeList(e,t,n){[].forEach.call(e,function(e){"undefined"==typeof n?e.classList.remove(t):e.classList[e==n?"add":"remove"](t)})}function addClassNodeList(e,t,n){[].forEach.call(e,function(e){"undefined"==typeof n?e.classList.add(t):e.classList[e==n?"remove":"add"](t)})}function isNodeType(e,t){return e.tagName.toUpperCase()===t.toUpperCase()}function getCoords(e){var t=e.getBoundingClientRect(),n=document.body,r=document.documentElement,i=window.pageYOffset||r.scrollTop||n.scrollTop,o=window.pageXOffset||r.scrollLeft||n.scrollLeft,u=r.clientTop||n.clientTop||0,a=r.clientLeft||n.clientLeft||0,s=t.top+i-u,l=t.left+o-a,f=parseInt(getStyleVal(e,"width")),c=parseInt(getStyleVal(e,"height"));return{top:Math.round(s),left:Math.round(l),right:Math.round(l+f),bottom:Math.round(s+c)}}function getStyle(e,t){return window.getComputedStyle?window.getComputedStyle(e,null).getPropertyValue(t):e.currentStyle?e.currentStyle[t]:void 0}function triggerEvent(e,t){if("createEvent"in document){var n=document.createEvent("HTMLEvents");n.initEvent(t,!1,!0),e.dispatchEvent(n)}else e.fireEvent(t)}function fadeOut(e){nodeExists(e)&&(addClass(e,"animated"),addClass(e,"fadeOut"))}function fadeOutAndRemove(e){nodeExists(e)&&(addClass(e,"animated"),addClass(e,"fadeOut")),e.addEventListener("animationend",function t(){removeFromDOM(e),e.removeEventListener("animationend",t,!1)},!1)}function cloneObj(e){var t={};for(var n in e)e.hasOwnProperty(n)&&(t[n]=e[n]);return t}function getFormInputs(e){for(var t=$All("input, textarea, select",e),n=t.length;n--;){var r=t[n];"radio"==r.type&&r.checked!==!0&&t.splice(n,1)}return t}function getInputValue(e){return"checkbox"==e.type?e.checked:"select"==e.type?e.options[e.selectedIndex].value:e.value}function is_numeric(e){var t=" \n\r    \f\x0B            ​\u2028\u2029　";return("number"==typeof e||"string"==typeof e&&-1===t.indexOf(e.slice(-1)))&&""!==e&&!isNaN(e)}function isCallable(e){return"[object Function]"===Object.prototype.toString.call(e)}function count(e,t){var n,r=0;if(null===e||"undefined"==typeof e)return 0;if(e.constructor!==Array&&e.constructor!==Object)return 1;"COUNT_RECURSIVE"===t&&(t=1),1!=t&&(t=0);for(n in e)e.hasOwnProperty(n)&&(r++,1!=t||!e[n]||e[n].constructor!==Array&&e[n].constructor!==Object||(r+=this.count(e[n],1)));return r}function in_array(e,t,n){var r="",i=!!n;if(i){for(r in t)if(t[r]===e)return!0}else for(r in t)if(t[r]==e)return!0;return!1}function ltrim(e,t){t=t?(t+"").replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g,"$1"):" \\s ";var n=new RegExp("^["+t+"]+","g");return(e+"").replace(n,"")}function rtrim(e,t){t=t?(t+"").replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g,"\\$1"):" \\s ";var n=new RegExp("["+t+"]+$","g");return(e+"").replace(n,"")}function preg_quote(e,t){return String(e).replace(new RegExp("[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\"+(t||"")+"-]","g"),"\\$&")}function str_replace(e,t,n,r){r="undefined"==typeof r?1:r;for(var i=0;r>i;i++)n=n.replace(e,t);return n}function preg_match_all(e,t){if("string"==typeof e)try{e=new RegExp(e)}catch(n){return null}var r=[],i=e.exec(t);if(null!==i){for(var o=0;i=e.exec(t);)t=str_split_index(t,i.index+i[0].length-1)[1],i.index=o>0?i.index+(i[0].length-1):i.index-1,r.push(i),o++;return r}return null}function str_split_index(e,t){return[e.substring(0,t+1),e.substring(t+1)]}function closest_number(e,t){for(var n=e[0],r=0;r<e.length;r++){var i=e[r];Math.abs(t-i)<Math.abs(t-n)&&(n=i)}return n}function cleanInnerHTML(e){return e.join("")}function bool(e){if(e="undefined"==typeof e?!1:e,"boolean"==typeof e)return e;if("number"==typeof e)return e>0;if("string"==typeof e){if("false"===e.toLowerCase())return!1;if("true"===e.toLowerCase())return!0;if("on"===e.toLowerCase())return!0;if("off"===e.toLowerCase())return!1;if("undefined"===e.toLowerCase())return!1;if(is_numeric(e))return Number(e)>0;if(""===e)return!1}return!1}function ucfirst(e){return e.charAt(0).toUpperCase()+e.slice(1)}function strReduce(e,t,n,r){if(r="undefined"==typeof r,n="undefined"==typeof n?"":n,r)return e.length>t?e.substring(0,t)+n:e;var i=e.split(" ");return count(i)>t?fruits.slice(0,t).join(" ").suffix:e}function arrReduce(e,t){return array_slice(e,0,t)}function implode(e,t,n){var r="";for(i=0;i<e.length;i++)r+=i===e.length-1?t+e[i]:t+e[i]+n;return r}function array_slice(e,t,n,r){var i="";if("[object Array]"!==Object.prototype.toString.call(e)||r&&0!==t){var o=0,u={};for(i in e)o+=1,u[i]=e[i];e=u,t=0>t?o+t:t,n=void 0===n?o:0>n?o+n-t:n;var a={},s=!1,l=-1,f=0,c=0;for(i in e){if(++l,f>=n)break;l==t&&(s=!0),s&&(++f,this.is_int(i)&&!r?a[c++]=e[i]:a[i]=e[i])}return a}return void 0===n?e.slice(t):n>=0?e.slice(t,t+n):e.slice(t,n)}function timeAgo(e,t){t="undefined"!=typeof t,e=isValidTimeStamp(e)?parseInt(e):strtotime(e);var n=[{name:"second",limit:60,in_seconds:1},{name:"minute",limit:3600,in_seconds:60},{name:"hour",limit:86400,in_seconds:3600},{name:"day",limit:604800,in_seconds:86400},{name:"week",limit:2629743,in_seconds:604800},{name:"month",limit:31556926,in_seconds:2629743},{name:"year",limit:null,in_seconds:31556926}],r=(new Date-new Date(1e3*e))/1e3;if(5>r)return"now";for(var i,o=0;i=n[o++];)if(r<i.limit||!i.limit){var r=Math.floor(r/i.in_seconds);return t?{unit:i.name+(r>1?"s":""),time:r}:r+" "+i.name+(r>1?"s":"")}}function isValidTimeStamp(e){return is_numeric(e)&&parseInt(e)==e}function strtotime(e){return Math.round(new Date(e).getTime()/1e3)}function is_numeric(e){var t=" \n\r \f\x0B            ​\u2028\u2029　";return("number"==typeof e||"string"==typeof e&&-1===t.indexOf(e.slice(-1)))&&""!==e&&!isNaN(e)}function isset(){var e,t=arguments,n=t.length,r=0;if(0===n)throw new Error("Empty isset");for(;r!==n;){if(t[r]===e||null===t[r])return!1;r++}return!0}function empty(e){if(e="undefined"==typeof e?!1:e,"boolean"==typeof e)return e!==!0;if("number"==typeof e)return 1>e;if("string"==typeof e){if("undefined"===e.toLowerCase())return!0;if(is_numeric(e))return Number(e)<1;if(""===e)return!0;if(""!==e)return!1}return"[object Array]"===Object.prototype.toString.call(e)?e.length<1:"[object Object]"===Object.prototype.toString.call(e)?0===Object.getOwnPropertyNames(e).length:!1}function paginate(e,t,n){t=t===!1||0===t?1:t,n=n?n:10;var r=count(e),i=Math.ceil(r/n),o=(t-1)*n,u=(Math.min(o+n,r),[]);if(t>i)return!1;for(var a=0;i>a;a++)o=a*n,u.push(e.slice(o,n));return u}function $All(e,t){return t="undefined"==typeof t?document:t,Array.prototype.slice.call(t.querySelectorAll(e))}function $(e,t){return t="undefined"==typeof t?document:t,t.querySelector(e)}

    // Image resizer
    var ImageResizer=function(t,e,i){return this instanceof ImageResizer?(this.original_w=t,this.original_h=e,this.allow_enlarge=i,this.dest_x=0,this.dest_y=0,this.source_x,this.source_y,this.source_w,this.source_h,this.dest_w,void this.dest_h):new ImageResizer(t,e,i)};ImageResizer.prototype={resizeToHeight:function(t){var e=t/this.getSourceHeight(),i=this.getSourceWidth()*e;return this.resize(i,t),this},resizeToWidth:function(t){var e=t/this.getSourceWidth(),i=this.getSourceHeight()*e;return this.resize(t,i),this},scale:function(t){var e=this.getSourceWidth()*t/100,i=this.getSourceHeight()*t/100;return this.resize(e,i),this},resize:function(t,e){return this.allow_enlarge||(t>this.getSourceWidth()||e>this.getSourceHeight())&&(t=this.getSourceWidth(),e=this.getSourceHeight()),this.source_x=0,this.source_y=0,this.dest_w=t,this.dest_h=e,this.source_w=this.getSourceWidth(),this.source_h=this.getSourceHeight(),this},crop:function(t,e){this.allow_enlarge||(t>this.getSourceWidth()&&(t=this.getSourceWidth()),e>this.getSourceHeight()&&(e=this.getSourceHeight()));var i=this.getSourceWidth()/this.getSourceHeight(),h=t/e;if(i>h){this.resizeToHeight(e);var s=(this.getDestWidth()-t)/this.getDestWidth()*this.getSourceWidth();this.source_w=this.getSourceWidth()-s,this.source_x=s/2,this.dest_w=t}else{this.resizeToWidth(t);var r=(this.getDestHeight()-e)/this.getDestHeight()*this.getSourceHeight();this.source_h=this.getSourceHeight()-r,this.source_y=r/2,this.dest_h=e}return this},getSourceWidth:function(){return this.original_w},getSourceHeight:function(){return this.original_h},getDestWidth:function(){return this.dest_w},getDestHeight:function(){return this.dest_h}};

    /*-------------------------------------------------------------
    ** Global variables for application
    --------------------------------------------------------------*/
    document.getElementsByTagName('html')[0].className = 'writer-html';
    document.body.className = 'writing markdown';

    var doc     = document.documentElement;
    var ajaxURL = window.location.href.replace(/admin(.+)/, 'admin/writer/');
    var Ajax    = Modules.require('Ajax');

    // Writer
    var writerTextArea = $('.js-writer-textarea');
    var CodeMirrorDiv;

    // Inputs and buttons
    var saveBtn       = $('.js-writer-footer .js-save-post');
    var publishBtn    = $('.js-review-wrap .js-writer-form button[type=submit]');
    var articleForm   = $('.js-review-wrap .js-writer-form');

    // Global writer dopzone variables
    var writerDZ;
    var writerDZ_sendTimer;
    var writerDZ_errorTimer;
    var writerDZ_sendFiles = true;
    var writerDZ_droppedFiles = 0;
    var writerDZ_imgInserted = [];

    // Global hero image dropzone variables
    var heroDZ;
    var heroDZ_dropwrap    = $('.js-hero-drop form');
    var heroDZ_progressBar = $('.js-hero-drop .progress');
    var heroDZ_sendTimer;
    var heroDZ_errorTimer;
    var heroDZ_sendFiles = true;
    var heroDZ_droppedFiles = 0;

    // Panels
    var writerWrap = $('.js-writer-wrap');
    var readWrap = $('.js-reader-wrap');
    var reviewWrap = $('.js-review-wrap');
    var viewWraps;

    // Panel scrolls
    var readScroll = 0;
    var writeScroll = 0;
    var reviewScroll = 0;

    // footer
    var writerFooter = $('.js-writer-footer');
    var footerTimer;

    // footer view togglers
    var writeTrigger = $('.js-writer-footer .js-raw');
    var readTrigger = $('.js-writer-footer .js-html');
    var reviewTrigger = $('.js-writer-footer .js-pre-publish');
    var toggleTriggers;
    var sbTimer;
    var GLOBAL_PROGRESS_WRAP = $('.js-writer-progress');
    var GLOBAL_PROGRESS      = $('.js-writer-progress span');


// ##############################################################################
// FILE: Libs/Writer/initialize.js
// ##############################################################################

/*-------------------------------------------------------------
**  Writer application core
--------------------------------------------------------------*/
var KansoWriter = function() {

    this.version   = "1.0.0";
    this.author    = "Joe Howard";
    this.copyright = "Kanso 2015";
    this.writer    = null;
    this.saveTimer = null;
    this.hasSaved  = false;

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
    this.initThumbnailImage();
    this.initWindowResize();
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

    this.initSbTimer();


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
KansoWriter.prototype.initSbTimer = function() {
    var sb = $('.js-sidebar');
    var self = this;
    window.addEventListener("mousemove", function() {
        var fromSide = event.clientX;
        if (fromSide < 120) {
            clearTimeout(sbTimer);
            sb.style.opacity = "1";
            sbTimer = setTimeout(function() {
                sb.style.opacity = "0";
            }, 3000);
        }
        else {
            clearTimeout(sbTimer);
            sbTimer = setTimeout(function() {
                sb.style.opacity = "0";
            }, 3000);
        }
    });
    window.addEventListener("resize", function() {
        clearTimeout(sbTimer);
        sb.style.opacity = "0";
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
    $('.js-writer-footer .js-insert-h1').addEventListener('click', function() {
        self.toggleHeading('#', self);
    });
    $('.js-writer-footer .js-insert-h2').addEventListener('click', function() {
        self.toggleHeading('##', self);
    });
    $('.js-writer-footer .js-insert-h3').addEventListener('click', function() {
        self.toggleHeading('###', self);
    });
    $('.js-writer-footer .js-insert-h4').addEventListener('click', function() {
        self.toggleHeading('####', self);
    });
    $('.js-writer-footer .js-insert-h5').addEventListener('click', function() {
        self.toggleHeading('#####', self);
    });
    $('.js-writer-footer .js-insert-h6').addEventListener('click', function() {
        self.toggleHeading('######', self);
    });

    // Lists listeners
    $('.js-writer-footer .js-insert-list-normal').addEventListener('click', function() {
        self.toggleList(self, true);
    });
    $('.js-writer-footer .js-insert-list-numbered').addEventListener('click', function() {
        self.toggleList(self, false);
    });

    // Text styles
    $('.js-writer-footer .js-insert-bold').addEventListener('click', function() {
        self.toggleTextStyle('**', self);
    });
    $('.js-writer-footer .js-insert-italic').addEventListener('click', function() {
        self.toggleTextStyle('_', self);
    });
    $('.js-writer-footer .js-insert-strike').addEventListener('click', function() {
        self.toggleTextStyle('~~', self);
    });

    // links and images
    $('.js-writer-footer .js-insert-link').addEventListener('click', function() {
        self.insertWrapText('[', '](href)', '[text](href)', self);
    });
    
    /*$('.js-writer-footer .js-insert-image').addEventListener('click', function() {
        self.insertWrapText('![', '](src)', '![altText](src)', self);
    });*/

    window.addEventListener("resize", function() {
        clearTimeout(footerTimer);
        removeClass(writerFooter, 'active');
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

    var acitve = $('.js-writer-footer .view-toggles button.active');

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
        var inputTitle = $('.js-writer-form input[name=title]').value.trim();
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
        }, 3000);
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

        var re  = new RegExp('^' + text + '\\s.+' + '$');
        var _re = new RegExp('^' + text + '$');
        var __re  = new RegExp('^' + text + '\\s+' + '$');
        // if The current line is a heading (eg h1) but the clicked button was different (eg h3),
        // we should replace it to the clicked heading (eg h3), rather than removing the 
        // heading alltogether
        if (!re.test(lineText) && !_re.test(lineText) && !__re.test(lineText)) {
            lineText = text + ' ' + ltrim(ltrim(lineText, ['#', '##', '###', '####', '#####', '#####']));
        } else {
            lineText = ltrim(ltrim(lineText, ['#', '##', '###', '####', '#####', '#####']));
        }

    } 
    else {
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

    var content = writerTextArea.innerHTML.trim();
    content = ltrim(content, '<!--[CDATA[');
    content = content.replace(/\]\]\-\-\>$/, '');
    content = content.trim();

    this.writer = CodeMirror(
        function(editor) {
            writerTextArea.parentNode.replaceChild(editor, writerTextArea);
        },
        {
            value: content,
            mode: 'markdown',
            lineWrapping: true,
            lineNumbers: false,
            dragDrop: false,
            theme: "base16-light",
            scrollbarStyle: 'overlay',
            extraKeys: {
                "Enter": "newlineAndIndentContinueMarkdownList"
            }
        }
    );

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
    writerWrap.style.height = window.innerHeight + "px";
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

    clearTimeout(sbTimer);
    $('.js-sidebar').style.opacity = "0";

    clearTimeout(footerTimer);
    removeClass(writerFooter, 'active');

}

// ##############################################################################
// FILE: Libs/Writer/dropZone.js
// ##############################################################################
KansoWriter.prototype.initThumbnailImage = function() {
    var showMediaLibTrigger = $('.js-select-img-trigger');
    var removeImgTrigger    = $('.js-remove-img-trigger');
    var setFeatureTrigger   = $('.js-set-feature-image');
    var imgWrap             = $('.js-feature-img');
    var img                 = $('.js-feature-img img');
    var featureInput        = $('.js-feature-id');
    
    showMediaLibTrigger.addEventListener('click', function(e) {
        e = e || window.event;
        e.preventDefault();
        addClass($('.js-media-library'), 'feature-image');
    });

    setFeatureTrigger.addEventListener('click', function(e) {
        e = e || window.event;
        e.preventDefault();
        Modules.get('MediaLibrary')._hideLibrary();
        featureInput.value = $('#media_id').value;
        img.src = $('#media_url').value;
        addClass(imgWrap, 'active');
    });

    removeImgTrigger.addEventListener('click', function(e) {
        e = e || window.event;
        e.preventDefault();
        featureInput.value = '';
        removeClass(imgWrap, 'active');
        img.src = '';
    });

}

// ##############################################################################
// FILE: Libs/Writer/dropZone.js
// ##############################################################################

/*-------------------------------------------------------------
**  Initialize the DropZone for images on the writer
--------------------------------------------------------------*/
KansoWriter.prototype.initWriterDZ = function(self) {

    var options = {
        url: ajaxURL,
        maxFilesize: 10,
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
        formdata.append("ajax_request", 'writer_image_upload');
    });

    writerDZ.on("uploadprogress", function(file, progress) {
        GLOBAL_PROGRESS.style.width = progress + "%";
        addClass(GLOBAL_PROGRESS_WRAP, 'active');
    });

    writerDZ.on("error", function(file, response, xhr) {
        Modules.require('Notifications', { type : 'danger', msg : response});
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
                Modules.require('Notifications', { type : 'success', msg : 'Your file was successfully uploaded!'});
                clearTimeout(self.savePostTimer);
                addClass($('.js-save-post'), 'active');
                return;
            }
        }
        Modules.require('Notifications', { type : 'danger', msg : 'There was an error processing the request. Try again in a few moments.'});
    });

    writerDZ.on("complete", function(file) {
        writerDZ_sendFiles = true;
        writerDZ_droppedFiles = 0;
        GLOBAL_PROGRESS.style.width = "0%";
        writerDZ_imgInserted = [];
        removeClass(GLOBAL_PROGRESS_WRAP, 'active');
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
    Modules.require('Notifications', { type : 'danger', msg : 'Error! Too many uploads at once. Upload limit is 1 file per drop.'});
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
// FILE: Libs/Writer/ajax.js
// ##############################################################################

/*-------------------------------------------------------------
**  Reset the save button when any inputs changed
--------------------------------------------------------------*/
KansoWriter.prototype.initInputChanges = function() {
    var self = this;
    var allInputs = $All('.js-review-wrap .input-default');
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
    var validator = Modules.get('FormValidator', $('.js-writer-form'));

    validator.append('ajax_request', self.ajaxType);
    validator.append('id', self.articleID);
    validator.append('content', self.writer.getValue());

    Ajax.post(ajaxURL, validator.form(), function(success) {
        var responseObj = isJSON(success);

        if (responseObj && responseObj.details) {
            self.articleID = responseObj['details']['id'];
            self.ajaxType = 'writer_save_existing_article';
            Modules.require('Notifications', { type : 'success', msg : 'Your article was successfully saved!'});
            clearTimeout(self.saveTimer);
            removeClass($('.js-save-post'), 'active');
            return;
        } 
        else {
            Modules.require('Notifications', { type : 'danger', msg : 'The server encountered an error while saving the article.'});
        }
    },
    function(error) {
        Modules.require('Notifications', { type : 'danger', msg : 'The server encountered an error while saving the article.'});
        return;
    });
    
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
    var validator = Modules.get('FormValidator', $('.js-writer-form'));

    validator.append('ajax_request', 'writer_publish_article');
    validator.append('id', self.articleID);
    validator.append('content', self.writer.getValue());


    Ajax.post(ajaxURL, validator.form(), function(success) {
        var responseObj = isJSON(success);

        if (responseObj && responseObj.details) {
            self.articleID = responseObj['details']['id'];
            self.ajaxType  = 'writer_save_existing_article';
            var slug       = location.protocol + "//" + location.host + '/' + responseObj['details']['slug'];
            Modules.require('Notifications', { type : 'success', msg : 'Your article was successfully published. Click <a href="' + slug + '" target="_blank">here</a> to view live.'});
            clearTimeout(self.saveTimer);
            removeClass($('.js-save-post'), 'active');
            return;
        } else {
            Modules.require('Notifications', { type : 'danger', msg : 'The server encountered an error while publishing the article.'});
            return;
        }
    },
    function(error) {
        Modules.require('Notifications', { type : 'danger', msg : 'The server encountered an error while publishing the article.'});
        return;
    });
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

Modules.singleton('KansoWriter', KansoWriter).get('KansoWriter')

})();
