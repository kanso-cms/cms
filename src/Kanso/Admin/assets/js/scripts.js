// ##############################################################################
// FILE: Libs/helper.js
// ##############################################################################


/*
    General Domish Helper function
*/

/* Closest */
function closest(el, type) {
    type = type.toLowerCase();
    if (typeof el === "undefined") return null;
    if (el.nodeName.toLowerCase() === type) return el;
    if (el.parentNode && el.parentNode.nodeName.toLowerCase() === type) return el.parentNode;
    var parent = el.parentNode;
    while (parent !== document.body && typeof parent !== "undefined" && parent !== null) {
        parent = parent.parentNode;
        if (parent && parent.nodeName.toLowerCase() === type) return parent;
    }
    return null;
}

/* Parant untill class */
function parentUntillClass(el, clas) {
    if (hasClass(el, clas)) {
        return el;
    }
    if (hasClass(el.parentNode, clas)) {
        return el.parentNode;
    }
    var parent = el.parentNode;
    while (parent !== document.body) {
        parent = parent.parentNode;
        if (hasClass(parent, clas)) {
            return parent;
        }
    }
    return null;
}

/* Next untill type */
function nextUntillType(el, type) {
    type = type.toLowerCase();
    if (el.nextSibling && el.nextSibling.nodeName.toLowerCase === type) return el.nextSibling;
    var next = el.nextSibling;
    while (next !== document.body && typeof next !== "undefined" && next !== null) {
        next = next.nextSibling;
        if (next && next.nodeName.toLowerCase() === type) {
            return next;
        }
    }
    return null;
}

/* Is JSON */
function isJSON(str) {
    var obj;
    try {
        obj = JSON.parse(str);
    } catch (e) {
        return false;
    }
    return obj;
}

/* New Node */
function newNode(type, classes, ID, content, target) {
    var node = document.createElement(type);
    classes = (typeof classes === "undefined" ? null : classes);
    ID = (typeof ID === "undefined" ? null : ID);
    content = (typeof content === "undefined" ? null : content);
    if (classes !== null) {
        node.className = classes
    }
    if (ID !== null) {
        node.id = ID
    }
    if (content !== null) {
        node.innerHTML = content
    }
    target.appendChild(node);
    return node
}

/* Node Exists */
function nodeExists(element) {
    if (typeof(element) !== "undefined" && element !== null) {
        if (typeof(element.parentNode) !== "undefined" && element.parentNode !== null) {
            return true
        }
    }
    return false
}

/* Make random id */
function makeid(length) {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    for (var i = 0; i < length; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length))
    }
    return text
}

/* Remove From DOM */
function removeFromDOM(el) {
    if (nodeExists(el)) el.parentNode.removeChild(el);
}

/* Add class */
function addClass(el, className) {
    if (!nodeExists(el)) return;
    if (Object.prototype.toString.call(className) === '[object Array]') {
        for (var i = 0; i < className.length; i++) {
            el.classList.add(className[i]);
        }
        return;
    }
    el.classList.add(className);
}

/* Remove Class */
function removeClass(el, className) {
    if (!nodeExists(el)) return;
    if (Object.prototype.toString.call(className) === '[object Array]') {
        for (var i = 0; i < className.length; i++) {
            el.classList.remove(className[i]);
        }
        return;
    }
    el.classList.remove(className);
}

/* Has Class */
function hasClass(el, className) {
    if (!nodeExists(el)) return false;
    if (Object.prototype.toString.call(className) === '[object Array]') {
        for (var i = 0; i < className.length; i++) {
            if (el.classList.contains(className[i])) return true;
        }
        return false;
    }
    return el.classList.contains(className);
}

/* Remove class with exception */
function removeClassNodeList(nodeList, className, exception) {
    [].forEach.call(nodeList, function(a) {
        typeof exception === 'undefined' ? a.classList.remove(className) : a.classList[a == exception ? 'add' : 'remove'](className);

    });
}

/* Add class with exception */
function addClassNodeList(nodeList, className, exception) {
    [].forEach.call(nodeList, function(a) {
        typeof exception === 'undefined' ? a.classList.add(className) : a.classList[a == exception ? 'remove' : 'add'](className);

    });
}


/* Is node type */
function isNodeType(el, NodeType) {
    return el.tagName.toUpperCase() === NodeType.toUpperCase()
}

/* Get Element Coordinates */
function getCoords(elem) {
    var box = elem.getBoundingClientRect();
    var body = document.body;
    var docEl = document.documentElement;
    var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;
    var clientTop = docEl.clientTop || body.clientTop || 0;
    var clientLeft = docEl.clientLeft || body.clientLeft || 0;
    var top = box.top + scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;
    var width = parseInt(getStyleVal(elem, "width"));
    var height = parseInt(getStyleVal(elem, "height"));
    return {
        top: Math.round(top),
        left: Math.round(left),
        right: Math.round(left + width),
        bottom: Math.round(top + height)
    }
}

/* Get style */
function getStyle(elem, prop) {
    if (window.getComputedStyle) {
        return window.getComputedStyle(elem, null).getPropertyValue(prop)
    } else {
        if (elem.currentStyle) {
            return elem.currentStyle[prop]
        }
    }
}

/* Trigger a native event */
function triggerEvent(el, type) {
    if ("createEvent" in document) {
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent(type, false, true);
        el.dispatchEvent(evt)
    } else {
        el.fireEvent(type)
    }
}

/* Fade an element out */
function fadeOut(el) {
    if (nodeExists(el)) {
        addClass(el, 'animated');
        addClass(el, 'fadeOut');
    }
}

/* Fade an element out and remove it */
function fadeOutAndRemove(el) {
    if (nodeExists(el)) {
        addClass(el, 'animated');
        addClass(el, 'fadeOut');
    }
    el.addEventListener('animationend', function transitionEnd(event) {
        removeFromDOM(el);
        el.removeEventListener('animationend', transitionEnd, false);
    }, false);
}

/* Clone an object */
function cloneObj(src) {
    var clone = {};
    for (var prop in src) {
        if (src.hasOwnProperty(prop)) clone[prop] = src[prop];
    }
    return clone;
}

/* Get all inputs from a form */
function getFormInputs(form) {
    var allInputs = $All('input, textarea, select', form);

    var i = allInputs.length;
    while (i--) {
        var input = allInputs[i];
        if (input.type == "radio" && input.checked !== true) allInputs.splice(i, 1);
    }

    return allInputs
}

/* Get input value */
function getInputValue(input) {
    if (input.type == "checkbox") return input.checked;
    if (input.type == "select") return input.options[input.selectedIndex].value;
    return input.value;
}

/* Is numberic */
function is_numeric(mixed_var) {
    var whitespace =
        " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
        1)) && mixed_var !== '' && !isNaN(mixed_var);
}

/* Is callback */
function isCallable(obj) {
    return Object.prototype.toString.call(obj) === '[object Function]';
}

/* Count an object or string */
function count(mixed_var, mode) {
    var key, cnt = 0;
    if (mixed_var === null || typeof mixed_var === 'undefined') {
        return 0;
    } else if (mixed_var.constructor !== Array && mixed_var.constructor !== Object) {
        return 1;
    }

    if (mode === 'COUNT_RECURSIVE') {
        mode = 1;
    }
    if (mode != 1) {
        mode = 0;
    }

    for (key in mixed_var) {
        if (mixed_var.hasOwnProperty(key)) {
            cnt++;
            if (mode == 1 && mixed_var[key] && (mixed_var[key].constructor === Array || mixed_var[key].constructor ===
                    Object)) {
                cnt += this.count(mixed_var[key], 1);
            }
        }
    }

    return cnt;
}

/* In array */
function in_array(needle, haystack, argStrict) {

    var key = '',
        strict = !!argStrict;

    //we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
    //in just one for, in order to improve the performance 
    //deciding wich type of comparation will do before walk array
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }

    return false;
}

/* Left trim */
function ltrim(str, charlist) {
    //  discuss at: http://phpjs.org/functions/ltrim/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Erkekjetter
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Onno Marsman
    //   example 1: ltrim('    Kevin van Zonneveld    ');
    //   returns 1: 'Kevin van Zonneveld    '

    charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
        .replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
    var re = new RegExp('^[' + charlist + ']+', 'g');
    return (str + '')
        .replace(re, '');
}

/* Left trim */
function rtrim(str, charlist) {
    //  discuss at: http://phpjs.org/functions/rtrim/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Erkekjetter
    //    input by: rem
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Onno Marsman
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //   example 1: rtrim('    Kevin van Zonneveld    ');
    //   returns 1: '    Kevin van Zonneveld'

    charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
        .replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\\$1');
    var re = new RegExp('[' + charlist + ']+$', 'g');
    return (str + '')
        .replace(re, '');
}
/* regex escape */
function preg_quote(str, delimiter) {
    //  discuss at: http://phpjs.org/functions/preg_quote/
    // original by: booeyOH
    // improved by: Ates Goral (http://magnetiq.com)
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: Onno Marsman
    //   example 1: preg_quote("$40");
    //   returns 1: '\\$40'
    //   example 2: preg_quote("*RRRING* Hello?");
    //   returns 2: '\\*RRRING\\* Hello\\?'
    //   example 3: preg_quote("\\.+*?[^]$(){}=!<>|:");
    //   returns 3: '\\\\\\.\\+\\*\\?\\[\\^\\]\\$\\(\\)\\{\\}\\=\\!\\<\\>\\|\\:'

    return String(str)
        .replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
}

/* str replace */
function str_replace(search, replace, subject, count) {
    count = typeof count === 'undefined' ? 1 : count;
    for (var i = 0; i < count; i++) {
        subject = subject.replace(search, replace);
    }
    return subject;
}

/* Preg match all */
function preg_match_all(pattern, subject) {

    // convert the pattern to regix
    // if needed. return null on fail
    if (typeof pattern === 'string') {
        try {
            pattern = new RegExp(pattern);
        } catch (err) {
            return null;
        }
    }

    var matches = [];
    var matched = pattern.exec(subject);
    if (matched !== null) {
        var i = 0;
        while (matched = pattern.exec(subject)) {
            subject = str_split_index(subject, (matched.index + matched[0].length - 1))[1];
            matched.index = i > 0 ? (matched.index + (matched[0].length - 1)) : matched.index - 1;
            matches.push(matched);
            i++;
        }
        return matches;
    }
    return null;
}

/* split string at index */
function str_split_index(value, index) {
    return [value.substring(0, index + 1), value.substring(index + 1)];
}

/* closest Number */
function closest_number(numbers, target) {
    var curr = numbers[0];
    for (var i = 0; i < numbers.length; i++) {
        var val = numbers[i];
        if (Math.abs(target - val) < Math.abs(target - curr)) curr = val;
    }
    return curr
}

function cleanInnerHTML(array) {
    return array.join('');
}

function bool(value) {

    value = (typeof value === 'undefined' ? false : value);

    if (typeof value === 'boolean') return value;

    if (typeof value === 'number') return value > 0;

    if (typeof value === 'string') {
        if (value.toLowerCase() === 'false') return false;
        if (value.toLowerCase() === 'true') return true;
        if (value.toLowerCase() === 'on') return true;
        if (value.toLowerCase() === 'off') return false;
        if (value.toLowerCase() === 'undefined') return false;
        if (is_numeric(value)) return Number(value) > 0;
        if (value === '') return false;
    }

    return false;
}

function ucfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function strReduce(string, length, suffix, toChar) {

    toChar = (typeof toChar === 'undefined' ? true : false);
    suffix = (typeof suffix === 'undefined' ? '' : suffix);

    if (toChar) return (string.length > length) ? string.substring(0, length) + suffix : string;

    var words = string.split(" ");

    if (count(words) > length) return fruits.slice(0, length).join(' ').suffix;

    return string;

}

function arrReduce(array, count) {
    return array_slice(array, 0, count);
}

function implode(array, prefix, suffix) {
    var str = '';
    for (i = 0; i < array.length; i++) {
        if (i === array.length - 1) {
            str += prefix + array[i];
        } else {
            str += prefix + array[i] + suffix;
        }
    }
    return str;
}

function array_slice(arr, offst, lgth, preserve_keys) {
    //  discuss at: http://phpjs.org/functions/array_slice/
    // original by: Brett Zamir (http://brett-zamir.me)
    //  depends on: is_int
    //    input by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //        note: Relies on is_int because !isNaN accepts floats
    //   example 1: array_slice(["a", "b", "c", "d", "e"], 2, -1);
    //   returns 1: {0: 'c', 1: 'd'}
    //   example 2: array_slice(["a", "b", "c", "d", "e"], 2, -1, true);
    //   returns 2: {2: 'c', 3: 'd'}

    /*
    if ('callee' in arr && 'length' in arr) {
      arr = Array.prototype.slice.call(arr);
    }
    */

    var key = '';

    if (Object.prototype.toString.call(arr) !== '[object Array]' ||
        (preserve_keys && offst !== 0)) { // Assoc. array as input or if required as output
        var lgt = 0,
            newAssoc = {};
        for (key in arr) {
            //if (key !== 'length') {
            lgt += 1;
            newAssoc[key] = arr[key];
            //}
        }
        arr = newAssoc;

        offst = (offst < 0) ? lgt + offst : offst;
        lgth = lgth === undefined ? lgt : (lgth < 0) ? lgt + lgth - offst : lgth;

        var assoc = {};
        var start = false,
            it = -1,
            arrlgth = 0,
            no_pk_idx = 0;
        for (key in arr) {
            ++it;
            if (arrlgth >= lgth) {
                break;
            }
            if (it == offst) {
                start = true;
            }
            if (!start) {
                continue;
            }++arrlgth;
            if (this.is_int(key) && !preserve_keys) {
                assoc[no_pk_idx++] = arr[key];
            } else {
                assoc[key] = arr[key];
            }
        }
        //assoc.length = arrlgth; // Make as array-like object (though length will not be dynamic)
        return assoc;
    }

    if (lgth === undefined) {
        return arr.slice(offst);
    } else if (lgth >= 0) {
        return arr.slice(offst, offst + lgth);
    } else {
        return arr.slice(offst, lgth);
    }
}


function timeAgo(time, asArray) {
    asArray = (typeof asArray === 'undefined' ? false : true);
    time = isValidTimeStamp(time) ? parseInt(time) : strtotime(time);
    var units = [{
        name: "second",
        limit: 60,
        in_seconds: 1
    }, {
        name: "minute",
        limit: 3600,
        in_seconds: 60
    }, {
        name: "hour",
        limit: 86400,
        in_seconds: 3600
    }, {
        name: "day",
        limit: 604800,
        in_seconds: 86400
    }, {
        name: "week",
        limit: 2629743,
        in_seconds: 604800
    }, {
        name: "month",
        limit: 31556926,
        in_seconds: 2629743
    }, {
        name: "year",
        limit: null,
        in_seconds: 31556926
    }];
    var diff = (new Date() - new Date(time * 1000)) / 1000;
    if (diff < 5) return "now";

    var i = 0,
        unit;
    while (unit = units[i++]) {
        if (diff < unit.limit || !unit.limit) {
            var diff = Math.floor(diff / unit.in_seconds);
            if (asArray) {
                return {
                    unit: unit.name + (diff > 1 ? "s" : ""),
                    time: diff
                };
            }
            return diff + " " + unit.name + (diff > 1 ? "s" : "");
        }
    }
}

function isValidTimeStamp(timestamp) {
    return is_numeric(timestamp) && parseInt(timestamp) == timestamp;
}

function strtotime(text) {
    return Math.round(new Date(text).getTime() / 1000);
}


function is_numeric(mixed_var) {
    //  discuss at: http://phpjs.org/functions/is_numeric/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: David
    // improved by: taith
    // bugfixed by: Tim de Koning
    // bugfixed by: WebDevHobo (http://webdevhobo.blogspot.com/)
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: Denis Chenu (http://shnoulle.net)
    //   example 1: is_numeric(186.31);
    //   returns 1: true
    //   example 2: is_numeric('Kevin van Zonneveld');
    //   returns 2: false
    //   example 3: is_numeric(' +186.31e2');
    //   returns 3: true
    //   example 4: is_numeric('');
    //   returns 4: false
    //   example 5: is_numeric([]);
    //   returns 5: false
    //   example 6: is_numeric('1 ');
    //   returns 6: false

    var whitespace =
        " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
    return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
        1)) && mixed_var !== '' && !isNaN(mixed_var);
}

function isset() {
    //  discuss at: http://phpjs.org/functions/isset/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: FremyCompany
    // improved by: Onno Marsman
    // improved by: Rafał Kukawski
    //   example 1: isset( undefined, true);
    //   returns 1: false
    //   example 2: isset( 'Kevin van Zonneveld' );
    //   returns 2: true

    var a = arguments,
        l = a.length,
        i = 0,
        undef;

    if (l === 0) {
        throw new Error('Empty isset');
    }

    while (i !== l) {
        if (a[i] === undef || a[i] === null) {
            return false;
        }
        i++;
    }
    return true;
}

function empty(value) {

    value = (typeof value === 'undefined' ? false : value);

    if (typeof value === 'boolean') return value !== true;

    if (typeof value === 'number') return value < 1;

    if (typeof value === 'string') {
        if (value.toLowerCase() === 'undefined') return true;
        if (is_numeric(value)) return Number(value) < 1;
        if (value === '') return true;
        if (value !== '') return false;
    }

    if (Object.prototype.toString.call(value) === '[object Array]') return value.length < 1;

    if (Object.prototype.toString.call(value) === '[object Object]') return (Object.getOwnPropertyNames(value).length === 0);

    return false;

}


function paginate(array, page, limit) {
    page = (page === false || page === 0 ? 1 : page);
    limit = (limit ? limit : 10);
    var total = count(array);
    var pages = Math.ceil((total / limit));
    var offset = (page - 1) * limit;
    var start = offset + 1;
    var end = Math.min((offset + limit), total);
    var paged = [];

    if (page > pages) return false;

    for (var i = 0; i < pages; i++) {
        offset = i * limit;
        paged.push(array.slice(offset, limit));
    }

    return paged;
}


/* Quick selctor all */
function $All(selector, context) {
    context = (typeof context === 'undefined' ? document : context);
    return Array.prototype.slice.call(context.querySelectorAll(selector));
}

/* Quck selector single */
function $(selector, context) {
    context = (typeof context === 'undefined' ? document : context);
    return context.querySelector(selector)
}

// ##############################################################################
// FILE: Libs/formValidator.js
// ##############################################################################

/* 
    Form validator 
*/
var formValidator = function(inputs) {
    if (!(this instanceof formValidator)) {
        return new formValidator(inputs)
    }

    // Save inputs 
    this.inputs = inputs;

    // Validation index
    this.validationIndex = [];

    // Form result
    this.validForm = true;

    // Invalid inputs
    this.invalids = [];

    this.formObj = {};

    this.inputsIndex = {};

    // Index the inputs based on type
    this.indexInputs();

    // generate the form
    this.generateForm();


};

formValidator.prototype = {

    // index the inputs base on validation types
    indexInputs: function() {
        for (var i = 0; i < this.inputs.length; i++) {
            var name = this.inputs[i].name;
            this.inputsIndex[name] = this.inputs[i];
            this.validationIndex.push({
                node: this.inputs[i],
                isRequired: this.inputs[i].dataset.jsRequired || null,
                validationMinLength: this.inputs[i].dataset.jsMinLegnth || null,
                validationMaxLength: this.inputs[i].dataset.jsMaxLegnth || null,
                validationType: this.inputs[i].dataset.jsValidation || null,
                isValid: true,
            });
        }
    },

    validateForm: function() {
        this.invalids = [];
        this.validForm = true;

        for (var i = 0; i < this.validationIndex.length; i++) {
            this.validationIndex[i].isValid = true;

            var pos = this.validationIndex[i];
            var value = getInputValue(pos.node);

            if (!pos.isRequired && value === '') {
                continue;
            } else if (pos.isRequired && !this.validateEmpty(value)) {
                this.devalidate(i);
            } else if (pos.validationMinLength && !this.validateMinLength(value, pos.validationMinLength)) {
                this.devalidate(i);
            } else if (pos.validationMaxLength && !this.validateMaxLength(value, pos.validationMaxLength)) {
                this.devalidate(i);
            } else if (pos.validationType) {
                var isValid = true;
                if (pos.validationType === 'email') isValid = this.validateEmail(value);
                if (pos.validationType === 'name') isValid = this.validateName(value);
                if (pos.validationType === 'password') isValid = this.validatePassword(value);
                if (pos.validationType === 'website') isValid = this.validateWebsite(value);
                if (pos.validationType === 'plain-text') isValid = this.validatePlainText(value);
                if (pos.validationType === 'numbers') isValid = this.validateNumbers(value);
                if (pos.validationType === 'list') isValid = this.validateList(value);
                if (pos.validationType === 'no-spaces-text') isValid = this.validatePlainTextNoSpace(value);
                if (pos.validationType === 'slug') isValid = this.validateSlug(value);
                if (pos.validationType === 'creditcard') isValid = this.validateCreditCard(value);
                if (pos.validationType === 'cvv') isValid = this.validateCVV(value);
                if (pos.validationType === 'permalinks') isValid = this.validatePermalinks(value);
                if (pos.validationType === 'comma-list-numbers') isValid = this.validateCommaListNumbers(value);
                if (pos.validationType === 'url-path') isValid = this.validateURLPath(value);


                if (!isValid) this.devalidate(i);
            }
        }
        return this;
    },

    getInput: function(name) {
        if (name in this.inputsIndex) return this.inputsIndex[name];
        return null;
    },

    generateForm: function() {
        for (var i = 0; i < this.inputs.length; i++) {
            var value = getInputValue(this.inputs[i]);
            if (is_numeric(value)) value = parseInt(value);
            this.formObj[this.inputs[i].name] = value;
        }
        this.formAppend('public_key', GLOBAL_PUBLIC_KEY);
        this.formAppend('referer', window.location.href);
        return this.formObj;
    },

    formAppend: function(key, value) {
        this.formObj[key] = value;
        return this.formObj;
    },

    getForm: function() {
        return this.formObj;
    },

    devalidate: function(i) {
        this.validationIndex[i].isValid = false;
        this.validForm = false;
        this.invalids.push(this.validationIndex[i].node);
    },

    validateEmpty: function(value) {
        value = value.trim();
        var re = /^\s*$/;
        return re.test(value) ? false : true;
    },

    validateEmail: function(value) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(value);
    },

    validateName: function(value) {
        var re = /^[A-z \-]+$/;
        return re.test(value);
    },

    validateNumbers: function(value) {
        var re = /^[\d]+$/;
        return re.test(value);
    },

    validatePassword: function(value) {
        var re = /^(?=.*[^a-zA-Z]).{6,40}$/;
        return re.test(value);
    },

    validateWebsite: function(value) {
        re = /^(www\.|[A-z]|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
        return re.test(value);
    },

    validateMinLength: function(value, min) {
        return value.length >= min;
    },

    validateMaxLength: function(value, max) {
        return value.length < max;
    },

    validatePlainText: function(value) {
        var re = /^[A-z _-]+$/;
        return re.test(value);
    },

    validatePlainTextNoSpace: function(value) {
        var re = /^[A-z_-]+$/;
        return re.test(value);
    },

    validateList: function(value) {
        var re = /^[-\w\s]+(?:,[-\w\s]*)*$/;
        return re.test(value);
    },

    validateSlug: function(value) {
        var re = /^[A-z\/\-\_]+$/;
        return re.test(value);
    },

    validateCreditCard: function(value) {
        value = value.replace(/ /g, "");
        var re = /^[0-9]+$/;
        var check = re.test(value);
        if (check === false) return false;
        if (value.length !== 16) return false;
        return true;
    },

    validateCVV: function(value) {
        if (value.length > 4) return false;
        var re = /^[0-9]+$/;
        return re.test(value);
    },

    validatePermalinks: function(value) {
        var re = /^((year|month|postname|category|author|day|hour|minute|second)\/)+$/;
        return re.test(value);
    },

    validateCommaListNumbers: function(value) {
        var re = /^((\d+),\s)+(\d+)$/;
        if (re.test(value)) return true;
        var _re = /^(((\d+)\s*(\d+),)\s*)(((\d+)\s*(\d+),)\s*)(((\d+)\s*(\d+))\s*)$/;
        if (_re.test(value)) return true;
        return false;
    },

    validateURLPath: function(value) {
        var re = /[A-z-_ \.\/]+/;
        return re.test(value);
    },

};

// ##############################################################################
// FILE: Libs/ImageResizer.js
// ##############################################################################

/* Image resizer for CTX - Done right */
var ImageResizer = function(width, height, allow_enlarge) {
    if (!(this instanceof ImageResizer)) {
        return new ImageResizer(width, height, allow_enlarge)
    }

    this.original_w = width;
    this.original_h = height;
    this.allow_enlarge = allow_enlarge;

    this.dest_x = 0;
    this.dest_y = 0;

    this.source_x;
    this.source_y;

    this.source_w;
    this.source_h;

    this.dest_w;
    this.dest_h;
};

ImageResizer.prototype = {

    resizeToHeight: function(height) {
        var ratio = height / this.getSourceHeight();
        var width = this.getSourceWidth() * ratio;

        this.resize(width, height);

        return this;
    },

    resizeToWidth: function(width) {
        var ratio = width / this.getSourceWidth();
        var height = this.getSourceHeight() * ratio;
        this.resize(width, height);
        return this;
    },

    scale: function(scale) {
        var width = this.getSourceWidth() * scale / 100;
        var height = this.getSourceHeight() * scale / 100;
        this.resize(width, height);
        return this;
    },

    resize: function(width, height) {
        if (!this.allow_enlarge) {
            if (width > this.getSourceWidth() || height > this.getSourceHeight()) {
                width = this.getSourceWidth();
                height = this.getSourceHeight();
            }
        }

        this.source_x = 0;
        this.source_y = 0;

        this.dest_w = width;
        this.dest_h = height;

        this.source_w = this.getSourceWidth();
        this.source_h = this.getSourceHeight();

        return this;
    },

    crop: function(width, height) {
        if (!this.allow_enlarge) {
            // this logic is slightly different to resize(),
            // it will only reset dimensions to the original
            // if that particular dimenstion is larger

            if (width > this.getSourceWidth()) {
                width = this.getSourceWidth();
            }

            if (height > this.getSourceHeight()) {
                height = this.getSourceHeight();
            }
        }

        var ratio_source = this.getSourceWidth() / this.getSourceHeight();
        var ratio_dest = width / height;

        if (ratio_dest < ratio_source) {
            this.resizeToHeight(height);

            var excess_width = (this.getDestWidth() - width) / this.getDestWidth() * this.getSourceWidth();

            this.source_w = this.getSourceWidth() - excess_width;
            this.source_x = excess_width / 2;

            this.dest_w = width;
        } else {
            this.resizeToWidth(width);

            var excess_height = (this.getDestHeight() - height) / this.getDestHeight() * this.getSourceHeight();

            this.source_h = this.getSourceHeight() - excess_height;
            this.source_y = excess_height / 2;

            this.dest_h = height;
        }

        return this;
    },

    getSourceWidth: function() {
        return this.original_w;
    },

    getSourceHeight: function() {
        return this.original_h;
    },

    getDestWidth: function() {
        return this.dest_w;
    },

    getDestHeight: function() {
        return this.dest_h;
    },

};

// ##############################################################################
// FILE: Libs/fileUploader.js
// ##############################################################################

/* Image resizer for CTX - Done right */
var Uploader = function(input, acceptedMime, maxFiles, maxFileSize) {
    if (!(this instanceof Uploader)) {
        return new Uploader(input, acceptedMime, maxFiles, maxFileSize)
    }

    this.files = input.files;
    this.acceptedMime = acceptedMime;
    this.maxFiles = (typeof maxFiles === 'undefined' ? 1 : maxFiles);
    this.formObj = new FormData;
    this.maxFileSize = (typeof maxFileSize === 'undefined' ? 5000000 : maxFileSize);

    return this;
};

Uploader.prototype = {

    init: function() {
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            if (in_array(file.type, this.acceptedMime)) this.formObj.append('file[]', file, file.name);
        }
        return this;
    },

    validateMime: function() {
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            if (!in_array(file.type, this.acceptedMime)) return false;
        }
        return true;
    },

    validateMaxFiles: function() {
        return this.files.length <= this.maxFiles;
    },

    validateFileSizes: function() {
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            if (file.size > this.maxFileSize) return false;
        }
        return true;
    },

    append: function(key, value) {
        this.formObj.append(key, value);
        return this;
    },

    upload: function(url, success, error, onProgress) {

        var self = this;
        var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X_REQUESTED_WITH', 'XMLHttpRequest');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                var percentComplete = (e.loaded / e.total) * 100;
                if (isCallable(onProgress)) onProgress(percentComplete);
            }
        }

        xhr.onload = function() {
            if (xhr.readyState == 4) {

                if (xhr.status >= 200 && xhr.status < 300 || xhr.status === 304) {

                    var data = xhr.responseXML || xhr.responseText;

                    if (isCallable(success)) success(data, xhr);
                } else {
                    if (isCallable(error)) error(xhr, xhr.status);
                }
            }
        }

        xhr.send(this.formObj);
    }
};

// ##############################################################################
// FILE: Libs/pluralize.js
// ##############################################################################

/* global define */

(function(root, pluralize) {
    // Browser global.
    root.pluralize = pluralize();
})(this, function() {


    function pluralize(word, count) {

        count = (typeof count === 'undefined' ? 2 : count);

        // Return the word if we don't need to pluralize
        if (count === 1) return word;

        // Set class variables for use
        pluralize.word = word;
        pluralize.lowercase = word.toLowerCase();
        pluralize.upperCase = word.toUpperCase();
        pluralize.sentenceCase = ucfirst(word);
        pluralize.casing = getCasing();
        pluralize.sibilants = ['x', 's', 'z', 's'];
        pluralize.vowels = ['a', 'e', 'i', 'o', 'u'];
        pluralize.consonants = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'];

        // save some time in the case that singular and plural are the same
        if (isUncountable()) return word;

        // check for irregular forms
        var irregular = isIrregular();
        if (irregular) return toCasing(irregular, pluralize.casing);

        // nouns that end in -ch, x, s, z or s-like sounds require an es for the plural:
        if (in_array(suffix(pluralize.lowercase, 1), pluralize.sibilants) || (suffix(pluralize.lowercase, 2) === 'ch')) return toCasing(word + 'es', pluralize.casing);

        // Nouns that end in a vowel + y take the letter s:
        if (in_array(nthLast(pluralize.lowercase, 1), pluralize.vowels) && suffix(pluralize.lowercase, 1) === 'y') return toCasing(word + 's', pluralize.casing);

        // Nouns that end in a consonant + y drop the y and take ies:
        if (in_array(nthLast(pluralize.lowercase, 1), pluralize.consonants) && suffix(pluralize.lowercase, 1) === 'y') return toCasing(sliceFromEnd(word, 1) + 'ies', pluralize.casing);

        // Nouns that end in a consonant + o add s:
        if (in_array(nthLast(pluralize.lowercase, 1), pluralize.consonants) && suffix(pluralize.lowercase, 1) === 'o') return toCasing(word + 's', pluralize.casing);

        // Nouns that end in a vowel + o take the letter s:
        if (in_array(nthLast(pluralize.lowercase, 1), pluralize.vowels) && suffix(pluralize.lowercase, 1) === 'o') return toCasing(word + 's', pluralize.casing);

        // irregular suffixes that cant be pluralized
        if (suffix(pluralize.lowercase, 4) === 'ness' || suffix(pluralize.lowercase, 3) === 'ess') return word;

        // Lastly, change the word based on suffix rules
        var pluralized = autoSuffix(pluralize.lowercase);
        if (pluralized) return toCasing(sliceFromEnd(word, pluralized[0]) + pluralized[1], pluralize.casing);

        return word + 's';
    };

    function isUncountable() {
        var uncountable = [
            'sheep',
            'fish',
            'deer',
            'series',
            'species',
            'money',
            'rice',
            'information',
            'equipment',
            'bison',
            'buffalo',
            'duck',
            'pike',
            'plankton',
            'salmon',
            'squid',
            'swine',
            'trout',
            'moose',
            'aircraft',
            'you',
            'pants',
            'shorts',
            'eyeglasses',
            'scissors',
            'offspring',
            'eries',
            'premises',
            'kudos',
            'corps',
            'heep',
        ];
        return in_array(pluralize.lowercase, uncountable);
    };

    function isIrregular() {
        var irregular = {
            'addendum': 'addenda',
            'alga': 'algae',
            'alumna': 'alumnae',
            'alumnus': 'alumni',
            'analysis': 'analyses',
            'antenna': 'antennae',
            'apparatus': 'apparatuses',
            'appendix': 'appendices',
            'axis': 'axes',
            'bacillus': 'bacilli',
            'bacterium': 'bacteria',
            'basis': 'bases',
            'beau': 'beaux',
            'kilo': 'kilos',
            'bureau': 'bureaus',
            'bus': 'busses',
            'cactus': 'cacti',
            'calf': 'calves',
            'child': 'children',
            'corps': 'corps',
            'corpus': 'corpora',
            'crisis': 'crises',
            'criterion': 'criteria',
            'curriculum': 'curricula',
            'datum': 'data',
            'deer': 'deer',
            'die': 'dice',
            'dwarf': 'dwarves',
            'diagnosis': 'diagnoses',
            'echo': 'echoes',
            'elf': 'elves',
            'ellipsis': 'ellipses',
            'embargo': 'embargoes',
            'emphasis': 'emphases',
            'erratum': 'errata',
            'fireman': 'firemen',
            'fish': 'fish',
            'focus': 'focuses',
            'foot': 'feet',
            'formula': 'formulas',
            'fungus': 'fungi',
            'genus': 'genera',
            'goose': 'geese',
            'half': 'halves',
            'hero': 'heroes',
            'hippopotamus': 'hippopotami',
            'hoof': 'hooves',
            'hypothesis': 'hypotheses',
            'index': 'indices',
            'knife': 'knives',
            'leaf': 'leaves',
            'life': 'lives',
            'loaf': 'loaves',
            'louse': 'lice',
            'man': 'men',
            'matrix': 'matrices',
            'means': 'means',
            'medium': 'media',
            'memorandum': 'memoranda',
            'millennium': 'millenniums',
            'moose': 'moose',
            'mosquito': 'mosquitoes',
            'mouse': 'mice',
            'nebula': 'nebulae',
            'neurosis': 'neuroses',
            'nucleus': 'nuclei',
            'neurosis': 'neuroses',
            'nucleus': 'nuclei',
            'oasis': 'oases',
            'octopus': 'octopi',
            'ovum': 'ova',
            'ox': 'oxen',
            'paralysis': 'paralyses',
            'parenthesis': 'parentheses',
            'person': 'people',
            'phenomenon': 'phenomena',
            'potato': 'potatoes',
            'radius': 'radii',
            'scarf': 'scarfs',
            'self': 'selves',
            'series': 'series',
            'sheep': 'sheep',
            'shelf': 'shelves',
            'scissors': 'scissors',
            'species': 'species',
            'stimulus': 'stimuli',
            'stratum': 'strata',
            'syllabus': 'syllabi',
            'symposium': 'symposia',
            'synthesis': 'syntheses',
            'synopsis': 'synopses',
            'tableau': 'tableaux',
            'that': 'those',
            'thesis': 'theses',
            'thief': 'thieves',
            'this': 'these',
            'tomato': 'tomatoes',
            'tooth': 'teeth',
            'torpedo': 'torpedoes',
            'vertebra': 'vertebrae',
            'veto': 'vetoes',
            'vita': 'vitae',
            'watch': 'watches',
            'wife': 'wives',
            'wolf': 'wolves',
            'woman': 'women',
            'is': 'are',
            'was': 'were',
            'he': 'they',
            'she': 'they',
            'i': 'we',
            'zero': 'zeroes',
        };

        if (isset(irregular[pluralize.lowercase])) return irregular[pluralize.lowercase];

        return false;
    };

    function autoSuffix() {

        var suffix1 = suffix(pluralize.lowercase, 1);
        var suffix2 = suffix(pluralize.lowercase, 2);
        var suffix3 = suffix(pluralize.lowercase, 3);

        if (suffix(pluralize.lowercase, 4) === 'zoon') return [4, 'zoa'];

        if (suffix3 === 'eau') return [3, 'eaux'];
        if (suffix3 === 'ieu') return [3, 'ieux'];
        if (suffix3 === 'ion') return [3, 'ia'];
        if (suffix3 === 'oof') return [3, 'ooves'];

        if (suffix2 === 'an') return [2, 'en'];
        if (suffix2 === 'ch') return [2, 'ches'];
        if (suffix2 === 'en') return [2, 'ina'];
        if (suffix2 === 'ex') return [2, 'exes'];
        if (suffix2 === 'is') return [2, 'ises'];
        if (suffix2 === 'ix') return [2, 'ices'];
        if (suffix2 === 'nx') return [2, 'nges'];
        if (suffix2 === 'nx') return [2, 'nges'];
        if (suffix2 === 'fe') return [2, 'ves'];
        if (suffix2 === 'on') return [2, 'a'];
        if (suffix2 === 'sh') return [2, 'shes'];
        if (suffix2 === 'um') return [2, 'a'];
        if (suffix2 === 'us') return [2, 'i'];
        if (suffix2 === 'x') return [1, 'xes'];
        if (suffix2 === 'y') return [1, 'ies'];

        if (suffix1 === 'a') return [1, 'ae'];
        if (suffix1 === 'o') return [1, 'oes'];
        if (suffix1 === 'f') return [1, 'ves'];

        return false;
    };

    function getCasing() {
        var casing = 'toLowerCase';
        casing = pluralize.lowercase === pluralize.word ? 'lower' : casing;
        casing = pluralize.upperCase === pluralize.word ? 'upper' : casing;
        casing = pluralize.sentenceCase === pluralize.word ? 'sentence' : casing;
        return casing;
    };

    function toCasing(word, casing) {
        if (casing === 'lower') return word.toLowerCase();
        if (casing === 'upper') return word.toUpperCase();
        if (casing === 'sentence') return ucfirst(word);
        return word;
    };

    function suffix(word, count) {
        return word.substr(word.length - count);
    };

    function nthLast(word, count) {
        return word.split('').reverse().join('').charAt(count);
    };

    function sliceFromEnd(word, count) {
        return word.substring(0, word.length - count);
    };

    function ucfirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    };

    function in_array(needle, haystack, argStrict) {

        var key = '',
            strict = !!argStrict;

        //we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
        //in just one for, in order to improve the performance 
        //deciding wich type of comparation will do before walk array
        if (strict) {
            for (key in haystack) {
                if (haystack[key] === needle) return true;
            }
        } else {
            for (key in haystack) {
                if (haystack[key] == needle) return true;
            }
        }

        return false;
    };

    function isset() {
        var a = arguments,
            l = a.length,
            i = 0,
            undef;

        if (l === 0) throw new Error('Empty isset');

        while (i !== l) {
            if (a[i] === undef || a[i] === null) return false;
            i++;
        }
        return true;
    };

    return pluralize;

});

// ##############################################################################
// FILE: Authenification/encrypt.js
// ##############################################################################

/**
 * Encrypt key with salt
 *
 * @return    string 
 */
function encrypt(sData, sKey) {
    var sResult = '';
    for (var i = 0; i < strlen(sData); i++) {
        var sChar = _substr(sData, i, 1);
        var sKeyChar = _substr(sKey, (i % strlen(sKey)) - 1, 1);
        var sChar = chr(ord(sChar) + ord(sKeyChar));
        sResult += sChar;
    }
    return encode_base64(sResult);
}

/**
 * Decrypt key with salt
 *
 * @return    string 
 */
function decrypt(sData, sKey) {
    var sResult = '';
    var sData = decode_base64(sData);
    for (var i = 0; i < strlen(sData); i++) {
        var sChar = _substr(sData, i, 1);
        var sKeyChar = _substr(sKey, (i % strlen(sKey)) - 1, 1);
        var sChar = chr(ord(sChar) - ord(sKeyChar));
        sResult += sChar;
    }
    return sResult;
}

function encode_base64(sData) {
    var sBase64 = base64_encode(sData);
    return strtr(sBase64, '+/', '-_');
}

function decode_base64(sData) {
    var sBase64 = strtr(sData, '-_', '+/');
    return base64_decode(sBase64);
}

/**
 * PHP's base64_encode();
 */
function base64_encode(data) {
    //  discuss at: http://phpjs.org/functions/base64_encode/
    // original by: Tyler Akins (http://rumkin.com)
    // improved by: Bayron Guevara
    // improved by: Thunder.m
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Rafał Kukawski (http://kukawski.pl)
    // bugfixed by: Pellentesque Malesuada
    //   example 1: base64_encode('Kevin van Zonneveld');
    //   returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
    //   example 2: base64_encode('a');
    //   returns 2: 'YQ=='

    var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        enc = '',
        tmp_arr = [];

    if (!data) {
        return data;
    }

    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);

        bits = o1 << 16 | o2 << 8 | o3;

        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;

        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);

    enc = tmp_arr.join('');

    var r = data.length % 3;

    return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}

/**
 * PHP's base64_decode();
 */
function base64_decode(data) {
    //  discuss at: http://phpjs.org/functions/base64_decode/
    // original by: Tyler Akins (http://rumkin.com)
    // improved by: Thunder.m
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Aman Gupta
    //    input by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: Onno Marsman
    // bugfixed by: Pellentesque Malesuada
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //   example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
    //   returns 1: 'Kevin van Zonneveld'
    //   example 2: base64_decode('YQ===');
    //   returns 2: 'a'

    var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        dec = '',
        tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');

    return dec.replace(/\0+$/, '');
}

/**
 * PHP's strtr();
 */
function strtr(str, from, to) {
    //  discuss at: http://phpjs.org/functions/strtr/
    // original by: Brett Zamir (http://brett-zamir.me)
    //    input by: uestla
    //    input by: Alan C
    //    input by: Taras Bogach
    //    input by: jpfle
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //  depends on: krsort
    //  depends on: ini_set
    //   example 1: $trans = {'hello' : 'hi', 'hi' : 'hello'};
    //   example 1: strtr('hi all, I said hello', $trans)
    //   returns 1: 'hello all, I said hi'
    //   example 2: strtr('äaabaåccasdeöoo', 'äåö','aao');
    //   returns 2: 'aaabaaccasdeooo'
    //   example 3: strtr('ääääääää', 'ä', 'a');
    //   returns 3: 'aaaaaaaa'
    //   example 4: strtr('http', 'pthxyz','xyzpth');
    //   returns 4: 'zyyx'
    //   example 5: strtr('zyyx', 'pthxyz','xyzpth');
    //   returns 5: 'http'
    //   example 6: strtr('aa', {'a':1,'aa':2});
    //   returns 6: '2'

    var fr = '',
        i = 0,
        j = 0,
        lenStr = 0,
        lenFrom = 0,
        tmpStrictForIn = false,
        fromTypeStr = '',
        toTypeStr = '',
        istr = '';
    var tmpFrom = [];
    var tmpTo = [];
    var ret = '';
    var match = false;

    // Received replace_pairs?
    // Convert to normal from->to chars
    if (typeof from === 'object') {
        tmpStrictForIn = this.ini_set('phpjs.strictForIn', false); // Not thread-safe; temporarily set to true
        from = this.krsort(from);
        this.ini_set('phpjs.strictForIn', tmpStrictForIn);

        for (fr in from) {
            if (from.hasOwnProperty(fr)) {
                tmpFrom.push(fr);
                tmpTo.push(from[fr]);
            }
        }

        from = tmpFrom;
        to = tmpTo;
    }

    // Walk through subject and replace chars when needed
    lenStr = str.length;
    lenFrom = from.length;
    fromTypeStr = typeof from === 'string';
    toTypeStr = typeof to === 'string';

    for (i = 0; i < lenStr; i++) {
        match = false;
        if (fromTypeStr) {
            istr = str.charAt(i);
            for (j = 0; j < lenFrom; j++) {
                if (istr == from.charAt(j)) {
                    match = true;
                    break;
                }
            }
        } else {
            for (j = 0; j < lenFrom; j++) {
                if (str._substr(i, from[j].length) == from[j]) {
                    match = true;
                    // Fast forward
                    i = (i + from[j].length) - 1;
                    break;
                }
            }
        }
        if (match) {
            ret += toTypeStr ? to.charAt(j) : to[j];
        } else {
            ret += str.charAt(i);
        }
    }

    return ret;
}

/**
 * PHP's strlen();
 */
function strlen(string) {
    //  discuss at: http://phpjs.org/functions/strlen/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Sakimori
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Kirk Strobeck
    // bugfixed by: Onno Marsman
    //  revised by: Brett Zamir (http://brett-zamir.me)
    //        note: May look like overkill, but in order to be truly faithful to handling all Unicode
    //        note: characters and to this function in PHP which does not count the number of bytes
    //        note: but counts the number of characters, something like this is really necessary.
    //   example 1: strlen('Kevin van Zonneveld');
    //   returns 1: 19
    //   example 2: ini_set('unicode.semantics', 'on');
    //   example 2: strlen('A\ud87e\udc04Z');
    //   returns 2: 3

    var str = string + '';
    var i = 0,
        chr = '',
        lgth = 0;

    if (!this.php_js || !this.php_js.ini || !this.php_js.ini['unicode.semantics'] || this.php_js.ini[
            'unicode.semantics'].local_value.toLowerCase() !== 'on') {
        return string.length;
    }

    var getWholeChar = function(str, i) {
        var code = str.charCodeAt(i);
        var next = '',
            prev = '';
        if (0xD800 <= code && code <= 0xDBFF) { // High surrogate (could change last hex to 0xDB7F to treat high private surrogates as single characters)
            if (str.length <= (i + 1)) {
                throw 'High surrogate without following low surrogate';
            }
            next = str.charCodeAt(i + 1);
            if (0xDC00 > next || next > 0xDFFF) {
                throw 'High surrogate without following low surrogate';
            }
            return str.charAt(i) + str.charAt(i + 1);
        } else if (0xDC00 <= code && code <= 0xDFFF) { // Low surrogate
            if (i === 0) {
                throw 'Low surrogate without preceding high surrogate';
            }
            prev = str.charCodeAt(i - 1);
            if (0xD800 > prev || prev > 0xDBFF) { //(could change last hex to 0xDB7F to treat high private surrogates as single characters)
                throw 'Low surrogate without preceding high surrogate';
            }
            return false; // We can pass over low surrogates now as the second component in a pair which we have already processed
        }
        return str.charAt(i);
    };

    for (i = 0, lgth = 0; i < str.length; i++) {
        if ((chr = getWholeChar(str, i)) === false) {
            continue;
        } // Adapt this line at the top of any loop, passing in the whole string and the current iteration and returning a variable to represent the individual character; purpose is to treat the first part of a surrogate pair as the whole character and then ignore the second part
        lgth++;
    }
    return lgth;
}

/**
 * PHP's substr();
 */
function _substr(str, start, len) {
    //  discuss at: http://phpjs.org/functions/_substr/
    //     version: 909.322
    // original by: Martijn Wieringa
    // bugfixed by: T.Wild
    // improved by: Onno Marsman
    // improved by: Brett Zamir (http://brett-zamir.me)
    //  revised by: Theriault
    //        note: Handles rare Unicode characters if 'unicode.semantics' ini (PHP6) is set to 'on'
    //   example 1: _substr('abcdef', 0, -1);
    //   returns 1: 'abcde'
    //   example 2: _substr(2, 0, -6);
    //   returns 2: false
    //   example 3: ini_set('unicode.semantics',  'on');
    //   example 3: _substr('a\uD801\uDC00', 0, -1);
    //   returns 3: 'a'
    //   example 4: ini_set('unicode.semantics',  'on');
    //   example 4: _substr('a\uD801\uDC00', 0, 2);
    //   returns 4: 'a\uD801\uDC00'
    //   example 5: ini_set('unicode.semantics',  'on');
    //   example 5: _substr('a\uD801\uDC00', -1, 1);
    //   returns 5: '\uD801\uDC00'
    //   example 6: ini_set('unicode.semantics',  'on');
    //   example 6: _substr('a\uD801\uDC00z\uD801\uDC00', -3, 2);
    //   returns 6: '\uD801\uDC00z'
    //   example 7: ini_set('unicode.semantics',  'on');
    //   example 7: _substr('a\uD801\uDC00z\uD801\uDC00', -3, -1)
    //   returns 7: '\uD801\uDC00z'

    var i = 0,
        allBMP = true,
        es = 0,
        el = 0,
        se = 0,
        ret = '';
    str += '';
    var end = str.length;

    // BEGIN REDUNDANT
    this.php_js = this.php_js || {};
    this.php_js.ini = this.php_js.ini || {};
    // END REDUNDANT
    switch ((this.php_js.ini['unicode.semantics'] && this.php_js.ini['unicode.semantics'].local_value.toLowerCase())) {
        case 'on':
            // Full-blown Unicode including non-Basic-Multilingual-Plane characters
            // strlen()
            for (i = 0; i < str.length; i++) {
                if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                    allBMP = false;
                    break;
                }
            }

            if (!allBMP) {
                if (start < 0) {
                    for (i = end - 1, es = (start += end); i >= es; i--) {
                        if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                            start--;
                            es--;
                        }
                    }
                } else {
                    var surrogatePairs = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
                    while ((surrogatePairs.exec(str)) != null) {
                        var li = surrogatePairs.lastIndex;
                        if (li - 2 < start) {
                            start++;
                        } else {
                            break;
                        }
                    }
                }

                if (start >= end || start < 0) {
                    return false;
                }
                if (len < 0) {
                    for (i = end - 1, el = (end += len); i >= el; i--) {
                        if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
                            end--;
                            el--;
                        }
                    }
                    if (start > end) {
                        return false;
                    }
                    return str.slice(start, end);
                } else {
                    se = start + len;
                    for (i = start; i < se; i++) {
                        ret += str.charAt(i);
                        if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
                            se++; // Go one further, since one of the "characters" is part of a surrogate pair
                        }
                    }
                    return ret;
                }
                break;
            }
            // Fall-through
        case 'off':
            // assumes there are no non-BMP characters;
            //    if there may be such characters, then it is best to turn it on (critical in true XHTML/XML)
        default:
            if (start < 0) {
                start += end;
            }
            end = typeof len === 'undefined' ? end : (len < 0 ? len + end : len + start);
            // PHP returns false if start does not fall within the string.
            // PHP returns false if the calculated end comes before the calculated start.
            // PHP returns an empty string if start and end are the same.
            // Otherwise, PHP returns the portion of the string from start to end.
            return start >= str.length || start < 0 || start > end ? !1 : str.slice(start, end);
    }
    return undefined; // Please Netbeans
}

/**
 * PHP's chr();
 */
function chr(codePt) {
    //  discuss at: http://phpjs.org/functions/chr/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Brett Zamir (http://brett-zamir.me)
    //   example 1: chr(75) === 'K';
    //   example 1: chr(65536) === '\uD800\uDC00';
    //   returns 1: true
    //   returns 1: true

    if (codePt > 0xFFFF) { // Create a four-byte string (length 2) since this code point is high
        //   enough for the UTF-16 encoding (JavaScript internal use), to
        //   require representation with two surrogates (reserved non-characters
        //   used for building other characters; the first is "high" and the next "low")
        codePt -= 0x10000;
        return String.fromCharCode(0xD800 + (codePt >> 10), 0xDC00 + (codePt & 0x3FF));
    }
    return String.fromCharCode(codePt);
}

/**
 * PHP's ord();
 */
function ord(string) {
    //  discuss at: http://phpjs.org/functions/ord/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: Onno Marsman
    // improved by: Brett Zamir (http://brett-zamir.me)
    //    input by: incidence
    //   example 1: ord('K');
    //   returns 1: 75
    //   example 2: ord('\uD800\uDC00'); // surrogate pair to create a single Unicode character
    //   returns 2: 65536

    var str = string + '',
        code = str.charCodeAt(0);
    if (0xD800 <= code && code <= 0xDBFF) { // High surrogate (could change last hex to 0xDB7F to treat high private surrogates as single characters)
        var hi = code;
        if (str.length === 1) {
            return code; // This is just a high surrogate with no following low surrogate, so we return its value;
            // we could also throw an error as it is not a complete character, but someone may want to know
        }
        var low = str.charCodeAt(1);
        return ((hi - 0xD800) * 0x400) + (low - 0xDC00) + 0x10000;
    }
    if (0xDC00 <= code && code <= 0xDFFF) { // Low surrogate
        return code; // This is just a low surrogate with no preceding high surrogate, so we return its value;
        // we could also throw an error as it is not a complete character, but someone may want to know
    }
    return code;
}

// ##############################################################################
// FILE: Ajax/publicKey.js
// ##############################################################################
var GLOBAL_PUBLIC_KEY = '';
var GLOBAL_AJAX_ENABLED = false;
var GLOBAL_AJAX_URL = window.location.href.replace(/admin(.+)/, 'admin/');
var GLOBAL_AJAX_QUEUE = [];

// ##############################################################################
// FILE: Ajax/queue.js
// ##############################################################################

function queueAjax(url, type, data, success, error) {
    GLOBAL_AJAX_QUEUE.push({
        url: url,
        type: type.toLowerCase(),
        data: data,
        success: success,
        error: error
    });
}

function bufferAjaxQueue() {
    for (var i = 0; i < GLOBAL_AJAX_QUEUE.length; i++) {
        var params = GLOBAL_AJAX_QUEUE[i];
        var type = params['type'];
        params['data']['public_key'] = GLOBAL_PUBLIC_KEY;
        Ajax[type](params['url'], params['data'], params['success'], params['error']);
    }

}

Ajax.post(GLOBAL_AJAX_URL, {
    ajaxRequest: 'public_key'
}, function(success) {

    response = isJSON(success);

    if (response) {
        GLOBAL_AJAX_ENABLED = true;
        GLOBAL_PUBLIC_KEY = decrypt(response.details.k, response.details.s);
        bufferAjaxQueue();
    }
});

// ##############################################################################
// AJAX FROMS
// FILE: Ajax/froms.js
// ##############################################################################

/* 
    Prevennt ajax forms from being submitted 
    and add active class on click
*/
var GLOBAL_PROGRESS = $('.js-global-progress .progress');

(function() {

    var ajaxSubmitButtons = $All('form.ajax-form button.submit');

    if (nodeExists($('form.ajax-form button.submit'))) {
        for (var i = 0; i < ajaxSubmitButtons.length; i++) {
            preventFormSubmit(i);
        }
    }

    function preventFormSubmit(i) {
        ajaxSubmitButtons[i].addEventListener("click", function(e) {
            e.preventDefault();
        });
    }

}());

/* Show/hide errors on ajax forms */
function clearAjaxInputErrors(form) {
    var inputs = $All('input', form);
    var submitBtn = $('button.submit', form);
    var resultWrap = $('.form-result', form);
    var results = $All('.animated', form);

    hideInputErrors(inputs);
    addClass(submitBtn, 'active');

    if (nodeExists(resultWrap)) resultWrap.className = 'form-result';
    removeClassNodeList(results, 'animated');
}

function showAjaxInputErrors(errorInputs, form) {
    showInputErrors(errorInputs);
    removeClass($('button.submit', form), 'active');
}

function showAjaxFormResult(form, resultClass, message) {
    if (typeof message !== 'undefined') {
        var messageP = $('.form-result .message.' + resultClass + ' .message-body p', form);
        if (nodeExists(messageP)) messageP.innerHTML = message;
    }
    removeClass($('button.submit', form), 'active');
    addClass($('.form-result', form), resultClass);
    addClass($('.form-result .message.' + resultClass, form), 'animated');
}

function showInputErrors(inputs) {
    for (var i = 0; i < inputs.length; i++) {
        addClass(inputs[i].parentNode, 'error');
    }
}

function hideInputErrors(inputs) {
    for (var i = 0; i < inputs.length; i++) {
        removeClass(inputs[i].parentNode, 'error');
    }
}

// ##############################################################################
// FILE: Ajax/articles.js
// ##############################################################################

// ##############################################################################
// AJAX ARTICLES LISTS
// ##############################################################################
(function() {

    var ajaxArticles = function(listWrap) {
        if (!(this instanceof ajaxArticles)) {
            return new ajaxArticles(listWrap)
        }

        this.currentPage = 1;
        this.maxPages = 1;
        this.sortBy = 'newest';
        this.expanded = false;
        this.search = false;
        this.haveItems = false;

        this.node_list_listwrap = listWrap;
        this.node_list_list = $('.js-ajax-list', this.node_list_listwrap);
        this.node_list_itemPublish = [];
        this.node_list_itemUnpublish = [];
        this.node_list_itemDeletes = [];

        this.node_list_powersWrap = $('.js-list-powers', this.node_list_listwrap);
        this.node_list_powers_checkAll = $('.js-check-all', this.node_list_powersWrap);
        this.node_list_powers_searchInput = $('.js-search-input', this.node_list_powersWrap);
        this.node_list_powers_cancelSearch = $('.js-cancel-search', this.node_list_powersWrap);
        this.node_list_powers_expandList = $('.js-expand-list', this.node_list_powersWrap);
        this.node_list_powers_sortOptions = $All('.js-sort-list .drop a', this.node_list_powersWrap);
        this.node_list_powers_publish = $('.js-publish', this.node_list_powersWrap);
        this.node_list_powers_delete = $('.js-delete', this.node_list_powersWrap);
        this.node_list_powers_unpublish = $('.js-unpublish', this.node_list_powersWrap);

        this.node_list_nav_navWrap = $('.js-list-nav', this.node_list_listwrap);
        this.node_list_nav_pageInput = $('.js-current-page', this.node_list_nav_navWrap);
        this.node_list_nav_maxPages = $('.js-max-pages', this.node_list_nav_navWrap);
        this.node_list_nav_nextPage = $('.js-next', this.node_list_nav_navWrap);
        this.node_list_nav_prevPage = $('.js-prev', this.node_list_nav_navWrap);

        this.node_list_items = [];
        this.have_items = !empty(this.node_list_items);

        this.node_list_expandItem = [];
        this.node_list_collapseItems = [];

        return this;
    };

    ajaxArticles.prototype = {

        init: function() {

            var _this = this;

            var form = this.getListForm();

            makeLoading(this.node_list_list, true, 300);

            queueAjax(GLOBAL_AJAX_URL, 'POST', form, function(success) {
                    _this.dispatchList(success);
                    _this.addDynamicListeners();
                },
                function(error) {
                    _this.handleError();
                });

            this.initializeListeners();
        },

        getListForm: function() {
            this.currentPage = parseInt(this.node_list_nav_pageInput.value.trim());
            this.search = (this.node_list_powers_searchInput.value.trim() === '' ? false : this.node_list_powers_searchInput.value.trim());
            return {
                search: this.search,
                page: this.currentPage,
                sortBy: this.sortBy,
                ajaxRequest: this.node_list_list.dataset.listName,
                public_key: GLOBAL_PUBLIC_KEY,
                referer: window.location.href
            };
        },

        getItemActionForm: function(action, ids) {
            return {
                article_ids: ids,
                ajaxRequest: action,
                public_key: GLOBAL_PUBLIC_KEY,
                referer: window.location.href
            };
        },

        getCheckedItems: function() {
            var ids = [];
            if (this.have_items) {
                var checks = $All('.js-item-check', this.node_list_list);
                for (var a = 0; a < checks.length; a++) {
                    var check = checks[a];
                    if (check.checked == true) {
                        ids.push(parentUntillClass(check, 'js-item').dataset.articleId);
                    }
                }
            }
            return ids;
        },

        initializeListeners: function() {

            var _this = this;

            // Mask page-number-input to only numbers
            VMasker(this.node_list_nav_pageInput).maskNumber();

            // Listener on page-number-input 
            this.node_list_nav_pageInput.addEventListener('keyup', function(e) {
                _this.skipToPage(e);
            });

            // Listener on check-all
            this.node_list_powers_checkAll.addEventListener('change', function() {
                var isChecked = event.target.checked;
                if (isChecked && _this.haveItems) {
                    _this.checkAll();
                } else {
                    _this.uncheckAll();
                }
            });

            // Listener on Search enter
            this.node_list_powers_searchInput.addEventListener('keyup', function(e) {
                _this.initSearch(e);
            });

            // Listener on search blur
            this.node_list_powers_searchInput.addEventListener('blur', function(e) {
                if (_this.node_list_powers_searchInput.value.trim() === '' && _this.search !== false) _this.clearSearch();
            });

            // Listener on close search
            this.node_list_powers_cancelSearch.addEventListener('click', function(e) {
                e.preventDefault();
                _this.node_list_powers_searchInput.value = '';
                if (_this.search !== false) _this.clearSearch();
            });

            // Listener on sort change
            for (var j = 0; j < this.node_list_powers_sortOptions.length; j++) {
                var option = this.node_list_powers_sortOptions[j];
                option.addEventListener('click', function() {
                    var sortBy = event.target.dataset.sort;
                    if (sortBy !== _this.sortBy) {
                        _this.sortBy = sortBy;
                        _this.refreshList();
                    }
                });
            }

            // Listener on expand-list
            this.node_list_powers_expandList.addEventListener('click', function(e) {
                e.preventDefault();
                var items = $All('.js-item', _this.node_list_list);
                if (!empty(items)) {
                    if (_this.expanded === false) {
                        _this.expandList(items);
                        _this.expanded = true;
                        _this.node_list_powers_expandList.innerText = 'Collapse';
                    } else {
                        _this.collapseList(items);
                        _this.expanded = false;
                        _this.node_list_powers_expandList.innerText = 'Expand';
                    }
                }
            });

            // Publish checked items
            this.node_list_powers_publish.addEventListener('click', function() {
                event.preventDefault();
                var checkedItems = _this.getCheckedItems();
                if (!empty(checkedItems)) {
                    var form = _this.getItemActionForm('admin_publish_articles', checkedItems);
                    var names = _this.getArticleTitles(form.article_ids);
                    _this.confirmItemAction(form, 'publish', names);
                }
            });

            // Delete checked items
            this.node_list_powers_delete.addEventListener('click', function() {
                event.preventDefault();
                var checkedItems = _this.getCheckedItems();
                if (!empty(checkedItems)) {
                    var form = _this.getItemActionForm('admin_delete_articles', checkedItems);
                    var names = _this.getArticleTitles(form.article_ids);
                    _this.confirmItemAction(form, 'delete', names);
                }
            });

            // Spam checked items
            this.node_list_powers_unpublish.addEventListener('click', function() {
                event.preventDefault();
                var checkedItems = _this.getCheckedItems();
                if (!empty(checkedItems)) {
                    var form = _this.getItemActionForm('admin_unpublish_articles', checkedItems);
                    var names = _this.getArticleTitles(form.article_ids);
                    _this.confirmItemAction(form, 'draft', names);
                }
            });

            // Next page
            this.node_list_nav_nextPage.addEventListener('click', function() {
                event.preventDefault();
                _this.skipToPage(_this.currentPage + 1);

            });
            // Prvious page
            this.node_list_nav_prevPage.addEventListener('click', function() {
                event.preventDefault();
                _this.skipToPage(_this.currentPage - 1);
            });

        },


        addDynamicListeners: function() {

            var _this = this;

            this.node_list_items = $All('.js-item', this.node_list_listwrap);
            this.have_items = !empty(this.node_list_items);
            this.node_list_expandItem = $All('.js-expand-item', this.node_list_listwrap);
            this.node_list_collapseItems = $All('.js-collapse-item', this.node_list_listwrap);

            this.node_list_itemPublish = $All('.js-item-publish', this.node_list_listwrap);;
            this.node_list_itemUnpublish = $All('.js-item-unpublish', this.node_list_listwrap);;
            this.node_list_itemDeletes = $All('.js-item-delete', this.node_list_listwrap);;

            // expand list items
            if (this.have_items) {
                for (var b = 0; b < this.node_list_expandItem.length; b++) {
                    var expander = this.node_list_expandItem[b];
                    expander.addEventListener('click', function() {
                        event.preventDefault();
                        var item = parentUntillClass(event.target, 'js-item');
                        addClass(item, 'expanded');
                    });
                }
            }

            // collapse list items
            if (this.have_items) {
                for (var c = 0; c < this.node_list_collapseItems.length; c++) {
                    var collapser = this.node_list_collapseItems[c];
                    collapser.addEventListener('click', function() {
                        event.preventDefault();
                        var item = parentUntillClass(event.target, 'js-item');
                        removeClass(item, 'expanded');
                    });
                }
            }

            // publish list item
            if (this.have_items) {
                for (var d = 0; d < this.node_list_itemPublish.length; d++) {
                    var button = this.node_list_itemPublish[d];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        var item = parentUntillClass(event.target, 'js-item');
                        var form = _this.getItemActionForm('admin_publish_articles', [item.dataset.articleId]);
                        var names = [item.dataset.articleTitle];
                        if (!hasClass(link.parentNode, 'disabled')) _this.confirmItemAction(form, 'publish', names);
                    });
                }
            }

            // Unpublish list item
            if (this.have_items) {
                for (var e = 0; e < this.node_list_itemUnpublish.length; e++) {
                    var button = this.node_list_itemUnpublish[e];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        var item = parentUntillClass(event.target, 'js-item');
                        var form = _this.getItemActionForm('admin_unpublish_articles', [item.dataset.articleId]);
                        var names = [item.dataset.articleTitle];
                        if (!hasClass(link.parentNode, 'disabled')) _this.confirmItemAction(form, 'draft', names);
                    });
                }
            }

            // Delete list item
            if (this.have_items) {
                for (var f = 0; f < this.node_list_itemDeletes.length; f++) {
                    var button = this.node_list_itemDeletes[f];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        var item = parentUntillClass(event.target, 'js-item');
                        var form = _this.getItemActionForm('admin_delete_articles', [item.dataset.articleId]);
                        var names = [item.dataset.articleTitle];
                        if (!hasClass(link.parentNode, 'disabled')) _this.confirmItemAction(form, 'delete', names);
                    });
                }
            }
        },

        getArticleTitles: function(ids) {
            var titles = [];
            for (var i = 0; i < ids.length; i++) {
                var item = $('[data-article-id="' + ids[i] + '"]', this.node_list_list);
                if (nodeExists(item)) titles.push(item.dataset.articleTitle);
            }
            return titles;
        },

        checkAll: function() {
            var itemChecks = $All('.js-item-select input[type=\'checkbox\']', this.node_list_list);
            if (!empty(itemChecks)) {
                for (var i = 0; i < itemChecks.length; i++) {
                    itemChecks[i].checked = true;
                }
            }
        },

        uncheckAll: function() {
            var itemChecks = $All('.js-item-select input[type=\'checkbox\']', this.node_list_list);
            if (!empty(itemChecks) && this.haveItems) {
                for (var i = 0; i < itemChecks.length; i++) {
                    itemChecks[i].checked = false;
                }
            }
            this.node_list_powers_checkAll.checked = false;
        },

        expandList: function(items) {
            for (var i = 0; i < items.length; i++) {
                addClass(items[i], 'expanded');
            }
        },

        collapseList: function(items) {
            for (var i = 0; i < items.length; i++) {
                removeClass(items[i], 'expanded');
            }
        },

        initSearch: function(e) {

            if (typeof e === 'string') {
                var query = e;
                this.node_list_powers_searchInput.value = query;
                addClass(this.node_list_powers_searchInput.parentNode, 'active');
                this.search = query;
                this.currentPage = 1;
                this.maxPages = 1;
                this.node_list_nav_pageInput.value = 1;
                triggerEvent(this.node_list_tabClickers[0], 'click');
                return;
            }

            var query = this.node_list_powers_searchInput.value.trim();

            if (query === '' && e.keyCode == 13 && this.search !== false) {
                this.clearSearch();
                return;
            }

            addClass(this.node_list_powers_searchInput.parentNode, 'active');
            if (e.keyCode == 13) {
                this.search = query;
                this.refreshList();
            }

        },

        clearSearch: function() {
            removeClass(this.node_list_powers_searchInput.parentNode, 'active');
            this.search = false;
            this.refreshList();
        },

        skipToPage: function(e) {

            var requestedPage = false;

            if (typeof e === 'number') {
                requestedPage = e;
            } else if (e.keyCode && e.keyCode == 13) {
                requestedPage = this.node_list_nav_pageInput.value.trim();
                requestedPage = (requestedPage === '' ? 0 : requestedPage);
                requestedPage = parseInt(requestedPage);
            }

            if (requestedPage !== false) {

                if (requestedPage > this.maxPages || requestedPage < 1 || requestedPage === this.currentPage) {
                    this.node_list_nav_pageInput.value = this.currentPage;
                } else {
                    this.currentPage = requestedPage;
                    this.node_list_nav_pageInput.value = requestedPage;
                    this.refreshList();
                }
            }
        },

        updateNav: function(items) {

            var currentPage = this.currentPage;
            var form = {
                ajaxRequest: 'admin_all_article_pages',
                public_key: GLOBAL_PUBLIC_KEY,
                referer: window.location.href
            };
            var _this = this;
            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    xhr = isJSON(success);

                    _this.maxPages = parseInt(xhr.details);

                    _this.node_list_nav_maxPages.innerHTML = 'of ' + _this.maxPages;

                    if (currentPage >= _this.maxPages) {
                        addClass(_this.node_list_nav_nextPage, 'disabled');
                    } else {
                        removeClass(_this.node_list_nav_nextPage, 'disabled');
                    }

                    if (currentPage > 1) {
                        removeClass(_this.node_list_nav_prevPage, 'disabled');
                    } else {
                        addClass(_this.node_list_nav_prevPage, 'disabled');
                    }

                },
                function(error) {
                    _this.maxPages = 1;
                    addClass(_this.node_list_nav_nextPage, 'disabled');
                    addClass(_this.node_list_nav_nextPage, 'disabled');
                });
        },

        dispatchList: function(xhr) {

            xhr = isJSON(xhr);
            console.log(xhr);
            if (xhr && isset(xhr.details) && !empty(xhr.details)) {
                this.updateNav(xhr.details);
                this.uncheckAll();
                this.haveItems = true;
                this.insertArticles(xhr.details[0]);
            } else {
                this.noList();
            }

        },

        handleError: function() {
            this.haveItems = false;
            this.noList();
        },

        insertArticles: function(articles) {

            var list = this.node_list_list;
            var content = '<tbody>';
            var expanded = (this.expanded === true ? 'expanded' : '');

            undoLoading(list, true);


            this.node_list_items = $All('.js-item', this.node_list_listwrap);
            this.have_items = !empty(this.node_list_items);
            this.node_list_expandItem = $All('.js-expand-item', this.node_list_listwrap);
            this.node_list_collapseItems = $All('.js-collapse-item', this.node_list_listwrap);

            this.node_list_itemPublish = $All('.js-item-approve', this.node_list_listwrap);;
            this.node_list_itemUnpublish = $All('.js-item-spam', this.node_list_listwrap);;
            this.node_list_itemDeletes = $All('.js-item-delete', this.node_list_listwrap);;


            for (var i = 0; i < articles.length; i++) {
                var article = articles[i];
                var canExpand = article['excerpt'].length > 200;
                var showMore = canExpand ? '<a href="#" class="more js-expand-item">more</a>' : '';
                var showLess = canExpand ? '<a href="#" class="less js-collapse-item">less</a>' : '';
                var disabledPublish = article['status'] === 'published' ? 'disabled' : '';
                var disabledUnpublish = article['status'] === 'draft' ? 'disabled' : '';
                var tagsList = this.createTagsList(article['tags']);

                content += cleanInnerHTML([
                    '<tr class="article list-item js-item ' + article['status'] + ' ' + expanded + '" data-article-title="' + article['title'] + '" data-article-id="' + article['id'] + '" data-article-type="' + article['type'] + '">',
                    '<td>',
                    '<h5 class="title">',
                    '<a href="' + article['permalink'] + '" target="_blank">' + article['title'] + '</a>',
                    '</h5>',
                    '<div class="article-body">',
                    '<div class="article-meta">',
                    '<span class="article-author">',
                    '<span>By </span><a href="' + article['author']['permalink'] + '" target="_blank">' + article['author']['name'] + '</a>',
                    '</span>',
                    '<span class="bullet"> • </span>',
                    '<span class="article-category">',
                    '<span>In </span><a href="' + article['category']['permalink'] + '" target="_blank">' + article['category']['name'] + '</a>',
                    '</span>',
                    '<span class="bullet"> • </span>',
                    '<abbr class="time-ago">',
                    timeAgo(article['created']), ' ago',
                    '</abbr>',
                    '<span class="bullet"> • </span>',
                    '<span class="article-category">',
                    '<span>' + ucfirst(article['status']) + '</span>',
                    '</span>',
                    '<span class="bullet"> • </span>',
                    '<span class="article-type">',
                    '<span>' + ucfirst(article['type']) + '</span>',
                    '</span>',
                    '<div class="clearfix tagslist">',
                    tagsList,
                    '</div>',
                    '</div>',

                    '<div class="check-wrap js-item-select">',
                    '<input class="js-item-check" id="article-select-' + article['id'] + '" type="checkbox" name="article-select-' + article['id'] + '">',
                    '<label class="checkbox small mini" for="article-select-' + article['id'] + '"></label>',
                    '</div>',

                    '<div class="item-preview">',
                    strReduce(article['excerpt'], 200, '... '),
                    showMore,
                    '</div>',

                    '<div class="item-full">',
                    article['excerpt'] + ' ',
                    showLess,
                    '</div>',
                    '<ul class="article-actions js-article-actions">',
                    '<li class="js-item-publish publish ' + disabledPublish + '">',
                    '<a href="#">',
                    '<svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#checkmark"></use></svg>',
                    'Publish',
                    '</a>',
                    '</li>',
                    '<li class="js-item-unpublish unpublish ' + disabledUnpublish + '">',
                    '<a href="#">',
                    '<svg viewBox="0 0 500 500"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#file-text"></use></svg>',
                    'Draft',
                    '</a>',
                    '</li>',
                    '<li class="js-item-delete delete">',
                    '<a href="#">',
                    '<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#trash"></use></svg>',
                    'Delete',
                    '</a>',
                    '</li>',
                    '<li class="edit">',
                    '<a target="_blank" href="' + article['edit_permalink'] + '">',
                    '<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#pen"></use></svg>',
                    'Edit',
                    '</a>',
                    '</li>',
                    '</ul>',
                    '</div>',
                    '</td>',
                    '</tr>'
                ]);
            }

            content += '</tbody>';
            newNode('table', 'horizontal article-table', null, content, list);
        },

        createTagsList: function(tags) {
            var list = '';
            var length = tags.length;
            for (var i = 0; i < length; i++) {
                var tag = tags[i];
                list += cleanInnerHTML([
                    '<span class="tag">',
                    '<a href="' + tag['permalink'] + '" target="_blank">' + tag['name'] + '</a>',
                    '</span>',
                ]);
                if (i < length - 1 && length > 1) {
                    list += '<span class="bullet"> • </span>';
                }
            }
            return list;
        },


        noList: function() {
            var message = 'No articles to display. There are no articles that match this status or search.';
            undoLoading(this.node_list_list, true);
            addClass(this.node_list_nav_nextPage, 'disabled');
            addClass(this.node_list_nav_prevPage, 'disabled');
            insertMessage('plain', message, this.node_list_list, 'small', true);
        },

        refreshList: function() {

            var form = this.getListForm();
            var _this = this;

            form['ajaxRequest'] = this.node_list_list.dataset.listName;

            makeLoading(this.node_list_list, true, 300);

            if (GLOBAL_AJAX_ENABLED) {

                Ajax.post(GLOBAL_AJAX_URL, form, function(success) {
                        _this.dispatchList(success);
                        _this.addDynamicListeners();
                    },
                    function(error) {
                        _this.handleError();
                    });
            } else {
                this.handleError();
            }
        },

        confirmItemAction: function(form, action, articleNames) {

            if (GLOBAL_AJAX_ENABLED) {

                var _this = this;
                var plural = count(form.article_ids);
                var number = plural > 1 ? plural + ' ' : '';
                var message = 'Are you POSITIVE you want to ' + action + ' the following ' + number + pluralize('article', plural) + ':<br>' + implode(articleNames, '• ', '<br>');

                pushCallBackNotification(
                    'info',
                    message,
                    ucfirst(action) + ' ' + pluralize('Article', plural),
                    _this.actionArticle, [form, action, _this],
                    null,
                    null
                );
            } else {
                pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
            }

        },

        actionArticle: function() {

            var form = arguments[0][0];
            var action = arguments[0][1];
            var _this = arguments[0][2];

            var plural = count(form.article_ids);

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    xhr = isJSON(success);

                    if (action === 'publish') action = 'published';
                    if (action === 'draft') action = 'changed to draft';
                    if (action === 'delete') action = 'deleted';

                    if (xhr) {
                        pushNotification('success', plural + ' ' + pluralize('article', plural) + 'were successfully ' + action);
                        _this.refreshList();
                    } else {
                        pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                    }

                },
                function(error) {
                    pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                });
        },

    };

    var articlesListWrap = $('.js-ajax-list-wrap.js-articles-wrap');

    if (nodeExists(articlesListWrap)) ajaxArticles(articlesListWrap).init();

}());

// ##############################################################################
// FILE: Ajax/tags.js
// ##############################################################################

// ##############################################################################
// AJAX TAGS AND CATEGORIES LISTS
// ##############################################################################
(function() {

    var ajaxTags = function(listWrap) {
        if (!(this instanceof ajaxTags)) {
            return new ajaxTags(listWrap)
        }

        this.currentPage = 1;
        this.maxPages = 1;
        this.sortBy = 'name';
        this.expanded = false;
        this.search = false;
        this.haveItems = false;

        this.node_list_listwrap = listWrap;
        this.node_list_list = $('.js-ajax-list', this.node_list_listwrap);
        this.node_list_itemClears = [];
        this.node_list_itemEdits = [];
        this.node_list_itemDeletes = [];
        this.node_list_itemSaveEdits = [];
        this.node_list_itemCancelEdits = [];
        this.node_list_itemSlugInputs = [];

        this.node_list_powersWrap = $('.js-list-powers', this.node_list_listwrap);
        this.node_list_powers_checkAll = $('.js-check-all', this.node_list_powersWrap);
        this.node_list_powers_searchInput = $('.js-search-input', this.node_list_powersWrap);
        this.node_list_powers_cancelSearch = $('.js-cancel-search', this.node_list_powersWrap);
        this.node_list_powers_expandList = $('.js-expand-list', this.node_list_powersWrap);
        this.node_list_powers_sortOptions = $All('.js-sort-list .drop a', this.node_list_powersWrap);
        this.node_list_powers_delete = $('.js-delete', this.node_list_powersWrap);
        this.node_list_powers_clear = $('.js-clear', this.node_list_powersWrap);

        this.node_list_nav_navWrap = $('.js-list-nav', this.node_list_listwrap);
        this.node_list_nav_pageInput = $('.js-current-page', this.node_list_nav_navWrap);
        this.node_list_nav_maxPages = $('.js-max-pages', this.node_list_nav_navWrap);
        this.node_list_nav_nextPage = $('.js-next', this.node_list_nav_navWrap);
        this.node_list_nav_prevPage = $('.js-prev', this.node_list_nav_navWrap);

        this.node_list_items = [];
        this.have_items = !empty(this.node_list_items);

        this.node_list_expandItem = [];
        this.node_list_collapseItems = [];

        return this;
    };

    ajaxTags.prototype = {

        init: function() {

            var _this = this;

            var form = this.getListForm();

            makeLoading(this.node_list_list, true, 300);

            queueAjax(GLOBAL_AJAX_URL, 'POST', form, function(success) {
                    _this.dispatchList(success);
                    _this.addDynamicListeners();
                },
                function(error) {
                    _this.handleError();
                });

            this.initializeListeners();
        },

        getListForm: function() {
            this.currentPage = parseInt(this.node_list_nav_pageInput.value.trim());
            this.search = (this.node_list_powers_searchInput.value.trim() === '' ? false : this.node_list_powers_searchInput.value.trim());
            return {
                search: this.search,
                page: this.currentPage,
                sortBy: this.sortBy,
                ajaxRequest: this.node_list_list.dataset.listName,
                public_key: GLOBAL_PUBLIC_KEY,
                referer: window.location.href
            };
        },

        getItemActionForm: function(action, list) {
            return {
                entries: list,
                ajaxRequest: action,
                public_key: GLOBAL_PUBLIC_KEY,
                referer: window.location.href
            };
        },

        getCheckedItems: function() {
            var list = [];
            if (this.have_items) {
                var checks = $All('.js-item-check', this.node_list_list);
                for (var a = 0; a < checks.length; a++) {
                    var check = checks[a];
                    if (check.checked == true) {
                        var item = parentUntillClass(check, 'js-item');
                        list.push({
                            name: item.dataset.tagName,
                            type: item.dataset.tagType,
                            id: item.dataset.tagId
                        });
                    }
                }
            }
            return list;
        },

        initializeListeners: function() {

            var _this = this;

            // Mask page-number-input to only numbers
            VMasker(this.node_list_nav_pageInput).maskNumber();

            // Listener on page-number-input 
            this.node_list_nav_pageInput.addEventListener('keyup', function(e) {
                _this.skipToPage(e);
            });

            // Listener on check-all
            this.node_list_powers_checkAll.addEventListener('change', function() {
                var isChecked = event.target.checked;
                if (isChecked && _this.haveItems) {
                    _this.checkAll();
                } else {
                    _this.uncheckAll();
                }
            });

            // Listener on Search enter
            this.node_list_powers_searchInput.addEventListener('keyup', function(e) {
                _this.initSearch(e);
            });

            // Listener on search blur
            this.node_list_powers_searchInput.addEventListener('blur', function(e) {
                if (_this.node_list_powers_searchInput.value.trim() === '' && _this.search !== false) _this.clearSearch();
            });

            // Listener on close search
            this.node_list_powers_cancelSearch.addEventListener('click', function(e) {
                e.preventDefault();
                _this.node_list_powers_searchInput.value = '';
                if (_this.search !== false) _this.clearSearch();
            });

            // Listener on sort change
            for (var j = 0; j < this.node_list_powers_sortOptions.length; j++) {
                var option = this.node_list_powers_sortOptions[j];
                option.addEventListener('click', function() {
                    var sortBy = event.target.dataset.sort;
                    if (sortBy !== _this.sortBy) {
                        _this.sortBy = sortBy;
                        _this.refreshList();
                    }
                });
            }

            // Listener on expand-list
            this.node_list_powers_expandList.addEventListener('click', function(e) {
                e.preventDefault();
                var items = $All('.js-item', _this.node_list_list);
                if (!empty(items)) {
                    if (_this.expanded === false) {
                        _this.expandList(items);
                        _this.expanded = true;
                        _this.node_list_powers_expandList.innerText = 'Collapse';
                    } else {
                        _this.collapseList(items);
                        _this.expanded = false;
                        _this.node_list_powers_expandList.innerText = 'Expand';
                    }
                }
            });

            // Delete checked items
            this.node_list_powers_delete.addEventListener('click', function() {
                event.preventDefault();
                var list = _this.getCheckedItems();
                if (!empty(list)) {
                    var form = _this.getItemActionForm('admin_delete_tags', list);
                    _this.confirmItemAction(form, 'delete', list);
                }
            });

            // clear checked items
            this.node_list_powers_clear.addEventListener('click', function() {
                event.preventDefault();
                var list = _this.getCheckedItems();
                if (!empty(list)) {
                    var form = _this.getItemActionForm('admin_clear_tags', list);
                    _this.confirmItemAction(form, 'clear', list);
                }
            });

            // Next page
            this.node_list_nav_nextPage.addEventListener('click', function() {
                event.preventDefault();
                _this.skipToPage(_this.currentPage + 1);

            });

            // Prvious page
            this.node_list_nav_prevPage.addEventListener('click', function() {
                event.preventDefault();
                _this.skipToPage(_this.currentPage - 1);
            });

        },


        addDynamicListeners: function() {

            var _this = this;

            this.node_list_items = $All('.js-item', this.node_list_listwrap);
            this.have_items = !empty(this.node_list_items);
            this.node_list_expandItem = $All('.js-expand-item', this.node_list_listwrap);
            this.node_list_collapseItems = $All('.js-collapse-item', this.node_list_listwrap);

            this.node_list_itemDeletes = $All('.js-item-delete', this.node_list_listwrap);
            this.node_list_itemClears = $All('.js-item-clear', this.node_list_listwrap);
            this.node_list_itemEdits = $All('.js-item-edit', this.node_list_listwrap);

            this.node_list_itemSaveEdits = $All('.js-save-edit', this.node_list_listwrap);
            this.node_list_itemCancelEdits = $All('.js-cancel-edit', this.node_list_listwrap);
            this.node_list_itemSlugInputs = $All('input.js-tag-slug', this.node_list_listwrap);

            // expand list items
            if (this.have_items) {
                for (var b = 0; b < this.node_list_expandItem.length; b++) {
                    var expander = this.node_list_expandItem[b];
                    expander.addEventListener('click', function() {
                        event.preventDefault();
                        var item = parentUntillClass(event.target, 'js-item');
                        addClass(item, 'expanded');
                    });
                }
            }

            // collapse list items
            if (this.have_items) {
                for (var c = 0; c < this.node_list_collapseItems.length; c++) {
                    var collapser = this.node_list_collapseItems[c];
                    collapser.addEventListener('click', function() {
                        event.preventDefault();
                        var item = parentUntillClass(event.target, 'js-item');
                        removeClass(item, 'expanded');
                    });
                }
            }

            // Clear list item
            if (this.have_items) {
                for (var d = 0; d < this.node_list_itemClears.length; d++) {
                    var button = this.node_list_itemClears[d];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        var item = parentUntillClass(event.target, 'js-item');
                        var list = [{
                            name: item.dataset.tagName,
                            type: item.dataset.tagType,
                            id: item.dataset.tagId
                        }];
                        var form = _this.getItemActionForm('admin_clear_tags', list);
                        if (!hasClass(link.parentNode, 'disabled')) _this.confirmItemAction(form, 'clear', list);
                    });
                }
            }

            // Delete list item
            if (this.have_items) {
                for (var e = 0; e < this.node_list_itemDeletes.length; e++) {
                    var button = this.node_list_itemDeletes[e];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        var item = parentUntillClass(event.target, 'js-item');
                        var list = [{
                            name: item.dataset.tagName,
                            type: item.dataset.tagType,
                            id: item.dataset.tagId
                        }];
                        var form = _this.getItemActionForm('admin_delete_tags', list);
                        if (!hasClass(link.parentNode, 'disabled')) _this.confirmItemAction(form, 'delete', list);
                    });
                }
            }

            // Edit list item
            if (this.have_items) {
                for (var f = 0; f < this.node_list_itemEdits.length; f++) {
                    var button = this.node_list_itemEdits[f];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var item = parentUntillClass(event.target, 'js-item');
                        if (hasClass(item, 'edit')) {
                            removeClass(item, 'edit');
                        } else {
                            addClass(item, 'edit');
                        }
                    });
                }
            }

            // Save Edits
            if (this.have_items) {
                for (var g = 0; g < this.node_list_itemSaveEdits.length; g++) {
                    var button = this.node_list_itemSaveEdits[g];
                    button.addEventListener('click', function() {
                        var item = parentUntillClass(event.target, 'js-item');
                        var list = [{
                            name: $('.js-tag-name', item).value.trim(),
                            slug: $('.js-tag-slug', item).value.trim(),
                            type: item.dataset.tagType,
                            id: item.dataset.tagId
                        }];
                        var form = _this.getItemActionForm('admin_edit_tag', list);
                        if (list[0].name === item.dataset.tagName && list[0].slug === item.dataset.tagSlug) return;
                        _this.ajaxEditTag(form, item);
                    });
                }
            }

            // Cacnel Edits
            if (this.have_items) {
                for (var h = 0; h < this.node_list_itemCancelEdits.length; h++) {
                    var button = this.node_list_itemCancelEdits[h];
                    button.addEventListener('click', function() {
                        var item = parentUntillClass(event.target, 'js-item');
                        $('.js-tag-name', item).value = item.dataset.tagName;
                        $('.js-tag-slug', item).value = item.dataset.tagSlug;
                        removeClass(item, 'edit');
                    });
                }
            }

            // Mask Slug intputs
            if (this.have_items) {
                for (var j = 0; j < this.node_list_itemSlugInputs.length; j++) {
                    var input = this.node_list_itemSlugInputs[j];
                    VMasker(input).maskAlphaNumDash();
                }
            }

        },

        checkAll: function() {
            var itemChecks = $All('.js-item-select input[type=\'checkbox\']', this.node_list_list);
            if (!empty(itemChecks)) {
                for (var i = 0; i < itemChecks.length; i++) {
                    itemChecks[i].checked = true;
                }
            }
        },

        uncheckAll: function() {
            var itemChecks = $All('.js-item-select input[type=\'checkbox\']', this.node_list_list);
            if (!empty(itemChecks) && this.haveItems) {
                for (var i = 0; i < itemChecks.length; i++) {
                    itemChecks[i].checked = false;
                }
            }
            this.node_list_powers_checkAll.checked = false;
        },

        expandList: function(items) {
            for (var i = 0; i < items.length; i++) {
                addClass(items[i], 'expanded');
            }
        },

        collapseList: function(items) {
            for (var i = 0; i < items.length; i++) {
                removeClass(items[i], 'expanded');
            }
        },

        initSearch: function(e) {

            if (typeof e === 'string') {
                var query = e;
                this.node_list_powers_searchInput.value = query;
                addClass(this.node_list_powers_searchInput.parentNode, 'active');
                this.search = query;
                this.currentPage = 1;
                this.maxPages = 1;
                this.node_list_nav_pageInput.value = 1;
                triggerEvent(this.node_list_tabClickers[0], 'click');
                return;
            }

            var query = this.node_list_powers_searchInput.value.trim();

            if (query === '' && e.keyCode == 13 && this.search !== false) {
                this.clearSearch();
                return;
            }

            addClass(this.node_list_powers_searchInput.parentNode, 'active');
            if (e.keyCode == 13) {
                this.search = query;
                this.refreshList();
            }

        },

        clearSearch: function() {
            removeClass(this.node_list_powers_searchInput.parentNode, 'active');
            this.search = false;
            this.refreshList();
        },

        skipToPage: function(e) {

            var requestedPage = false;

            if (typeof e === 'number') {
                requestedPage = e;
            } else if (e.keyCode && e.keyCode == 13) {
                requestedPage = this.node_list_nav_pageInput.value.trim();
                requestedPage = (requestedPage === '' ? 0 : requestedPage);
                requestedPage = parseInt(requestedPage);
            }

            if (requestedPage !== false) {

                if (requestedPage > this.maxPages || requestedPage < 1 || requestedPage === this.currentPage) {
                    this.node_list_nav_pageInput.value = this.currentPage;
                } else {
                    this.currentPage = requestedPage;
                    this.node_list_nav_pageInput.value = requestedPage;
                    this.refreshList();
                }
            }
        },

        updateNav: function(items) {

            var currentPage = this.currentPage;
            this.maxPages = count(items);
            this.node_list_nav_maxPages.innerHTML = 'of ' + this.maxPages;

            if (currentPage >= this.maxPages) {
                addClass(this.node_list_nav_nextPage, 'disabled');
            } else {
                removeClass(this.node_list_nav_nextPage, 'disabled');
            }

            if (currentPage > 1) {
                removeClass(this.node_list_nav_prevPage, 'disabled');
            } else {
                addClass(this.node_list_nav_prevPage, 'disabled');
            }
        },

        dispatchList: function(xhr) {

            xhr = isJSON(xhr);

            if (xhr && isset(xhr.details[this.currentPage - 1]) && !empty(xhr.details[this.currentPage - 1])) {
                this.updateNav(xhr.details);
                this.uncheckAll();
                this.haveItems = true;
                this.insertTags(xhr.details[this.currentPage - 1]);
            } else {
                this.noList();
            }

        },

        handleError: function() {
            this.haveItems = false;
            this.noList();
        },

        insertTags: function(tags) {

            var list = this.node_list_list;
            var content = '<tbody>';
            var expanded = (this.expanded === true ? 'expanded' : '');

            undoLoading(list, true);

            this.node_list_items = $All('.js-item', this.node_list_listwrap);
            this.have_items = !empty(this.node_list_items);
            this.node_list_expandItem = $All('.js-expand-item', this.node_list_listwrap);
            this.node_list_collapseItems = $All('.js-collapse-item', this.node_list_listwrap);

            this.node_list_itemClears = $All('.js-item-clear', this.node_list_listwrap);;
            this.node_list_itemEdits = $All('.js-item-edit', this.node_list_listwrap);;
            this.node_list_itemDeletes = $All('.js-item-delete', this.node_list_listwrap);;


            for (var i = 0; i < tags.length; i++) {
                var tag = tags[i];
                var canExpand = count(tag['posts']) > 2;
                var showMore = canExpand ? '<a href="#" class="more js-expand-item">more</a>' : '';
                var showLess = canExpand ? '<a href="#" class="less js-collapse-item">less</a>' : '';
                var postList = this.createPostsList(tag['posts']);
                var reducedPostList = this.createPostsList(arrReduce(tag['posts'], 10));

                content += cleanInnerHTML([
                    '<tr class="tag list-item js-item ' + expanded + '" data-tag-id="' + tag['id'] + '" data-tag-name="' + tag['name'] + '" data-tag-slug="' + tag['slug'] + '" data-tag-type="' + tag['type'] + '">',
                    '<td>',
                    '<h5 class="title">',
                    '<a href="' + tag['permalink'] + '#tag-' + tag['id'] + '" target="_blank">' + tag['name'] + '</a>',
                    '</h5>',
                    '<div class="tag-body">',
                    '<span class="tag-meta">',
                    '<span class="label">@ </span>',
                    '<a href="' + tag['permalink'] + '" >' + tag['slug'] + '</a>',
                    '<span class="bullet"> • </span>',
                    '<span class="label">Under </span>',
                    '<strong>' + pluralize(ucfirst(tag['type'])) + '</strong>',
                    '<span class="bullet"> • </span>',
                    '<span class="label">With </span>',
                    '<strong>' + count(tag['posts']) + '</strong> ' + pluralize('article', count(tag['posts'])),
                    '</span>',
                    '<div class="item-preview articles-list">',
                    reducedPostList + ' ', ,
                    showMore,
                    '</div>',

                    '<div class="item-full articles-list">',
                    postList + ' ',
                    showLess,
                    '</div>',
                    '<div class="edit-tag js-edit-tag">',
                    '<div class="input-wrap clearfix">',
                    '<label>Name:</label>',
                    '<input class="input-default small js-tag-name" value="' + tag['name'] + '" />',
                    '</div>',
                    '<div class="input-wrap clearfix">',
                    '<label>Slug:</label>',
                    '<input class="input-default small js-tag-slug" value="' + tag['slug'] + '" />',
                    '</div>',
                    '<div class="input-wrap">',
                    '<button class="button small submit save-edit js-save-edit">Save</button>',
                    '<button class="button small cancel js-cancel-edit">Cancel</button>',
                    '</div>',
                    '</div>',
                    '<div class="check-wrap js-item-select">',
                    '<input class="js-item-check" id="' + tag['type'] + '-select-' + tag['id'] + '" type="checkbox" name="' + tag['type'] + '-select-' + tag['id'] + '">',
                    '<label class="checkbox small mini" for="' + tag['type'] + '-select-' + tag['id'] + '"></label>',
                    '</div>',

                    '<ul class="tag-actions js-tag-actions">',
                    '<li class="clear-item js-item-clear">',
                    '<a href="#">',
                    '<svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#checkmark"></use></svg>',
                    'Clear',
                    '</a>',
                    '</li>',
                    '<li class="delete js-item-delete">',
                    '<a href="#">',
                    '<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#trash"></use></svg>',
                    'Delete',
                    '</a>',
                    '</li>',
                    '<li class="edit js-item-edit">',
                    '<a href="#">',
                    '<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#pen"></use></svg>',
                    'Edit',
                    '</a>',
                    '</li>',
                    '</ul>',
                    '</div>',
                    '</td>',
                    '</tr>'
                ]);
            }

            content += '</tbody>';
            newNode('table', 'horizontal tag-table', null, content, list);
        },

        createPostsList: function(posts) {
            var list = '<span class="label">Articles: </span>';
            var length = posts.length;
            if (length === 0) return '';
            for (var i = 0; i < length; i++) {
                var post = posts[i];
                list += cleanInnerHTML([
                    '<span class="article">',
                    '<span class="bullet"> • </span><a href="' + post['permalink'] + '" target="_blank">' + post['name'] + '</a>',
                    '</span>',
                ]);
            }
            return list;
        },


        noList: function() {
            var message = 'No tags or categories to display. There are no tags or categories that match this status or search.';
            undoLoading(this.node_list_list, true);
            addClass(this.node_list_nav_nextPage, 'disabled');
            addClass(this.node_list_nav_prevPage, 'disabled');
            insertMessage('plain', message, this.node_list_list, 'small', true);
        },

        refreshList: function() {

            var form = this.getListForm();
            var _this = this;

            form['ajaxRequest'] = this.node_list_list.dataset.listName;

            makeLoading(this.node_list_list, true, 300);

            if (GLOBAL_AJAX_ENABLED) {

                Ajax.post(GLOBAL_AJAX_URL, form, function(success) {
                        _this.dispatchList(success);
                        _this.addDynamicListeners();
                    },
                    function(error) {
                        _this.handleError();
                    });
            } else {
                this.handleError();
            }
        },

        confirmItemAction: function(form, action, list) {

            if (action === 'delete') {
                for (var i = 0; i < list.length; i++) {
                    if (list[i].id === '1') {
                        pushNotification('error', 'The tag "Untagged" and the category "Uncategorized" cannot be deleted. Make sure they\'re unchecked and try again.');
                        return;
                    }
                }
            }



            if (GLOBAL_AJAX_ENABLED) {

                var _this = this;
                var tagNames = this.getTagNames(list);
                var catNames = this.getCatNames(list);
                var message = 'Are you POSITIVE you want to ' + action + ' the following ';
                var confirm = ucfirst(action);

                if (count(tagNames) > 0) {
                    var plural = count(tagNames);
                    var number = plural > 1 ? plural + ' ' : '';
                    message += number + pluralize('tag', plural) + ':<br>' + implode(tagNames, '• ', '<br>');
                }
                if (count(catNames) > 0) {
                    if (count(tagNames) > 0) message += '<br>and the following ';
                    var plural = count(catNames);
                    var number = plural > 1 ? plural + ' ' : '';
                    message += number + pluralize('category', plural) + ':<br>' + implode(catNames, '• ', '<br>');
                }

                pushCallBackNotification(
                    'info',
                    message,
                    ucfirst(action) + ' ' + pluralize('Tag', plural),
                    _this.actionTag, [form, action, _this],
                    null,
                    null
                );
            } else {
                pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
            }
        },

        getTagNames: function(list) {
            var names = [];
            for (var i = 0; i < list.length; i++) {
                if (list[i].type === 'tag') names.push(list[i].name);
            }
            return names;
        },

        getCatNames: function(list) {
            var names = [];
            for (var i = 0; i < list.length; i++) {
                if (list[i].type === 'category') names.push(list[i].name);
            }
            return names;
        },

        actionTag: function() {

            var form = arguments[0][0];
            var action = arguments[0][1];
            var _this = arguments[0][2];
            form.entries = JSON.stringify(form.entries);
            var plural = count(form.entries);

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    xhr = isJSON(success);

                    if (action === 'clear') message = 'cleared';
                    if (action === 'delete') action = 'deleted';

                    if (xhr) {
                        pushNotification('success', plural + ' ' + pluralize('item', plural) + 'were successfully ' + action + '.');
                        _this.refreshList();
                    } else {
                        pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                    }

                },
                function(error) {
                    pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                });
        },

        ajaxEditTag: function(form, item) {

            var _this = this;
            var type = form.entries[0]['type'];
            form.entries = JSON.stringify(form.entries);

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    xhr = isJSON(success);

                    if (xhr && xhr.details === true) {
                        pushNotification('success', 'Your edits to this ' + type + ' were successfully saved.');
                        _this.refreshList();
                    } else if (xhr && xhr.details === 'slug_exists') {
                        pushNotification('error', 'Unable to change ' + type + ' slug. A ' + type + ' already exists with that slug.');

                    } else if (xhr && xhr.details === 'name_exists') {
                        pushNotification('error', 'Unable to change ' + type + ' name. A ' + type + ' already exists with that name.');

                    } else {
                        pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                    }

                },
                function(error) {
                    pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                });

        },

    };

    var tagsListWrap = $('.js-ajax-list-wrap.js-tags-wrap');

    if (nodeExists(tagsListWrap)) ajaxTags(tagsListWrap).init();

}());

// ##############################################################################
// FILE: Ajax/comments.js
// ##############################################################################

// ##############################################################################
// AJAX COMMENTS LISTS
// ##############################################################################
(function() {

    var ajaxComments = function(listWrap) {
        if (!(this instanceof ajaxComments)) {
            return new ajaxComments(listWrap)
        }

        this.currentPage = 1;
        this.maxPages = 1;
        this.sortBy = 'newest';
        this.expanded = false;
        this.search = false;
        this.haveItems = false;
        this.commentClass = false;

        this.node_list_listwrap = listWrap;
        this.node_list_list = $('.tab-panel.active.js-ajax-list', this.node_list_listwrap);
        this.node_list_tabClickers = $All('.js-tabs-wrap ul > li > a', this.node_list_listwrap);
        this.node_list_itemApproves = [];
        this.node_list_itemSpams = [];
        this.node_list_itemDeletes = [];
        this.node_list_itemSearchIp = [];


        this.node_list_powersWrap = $('.js-list-powers', this.node_list_listwrap);
        this.node_list_powers_checkAll = $('.js-check-all', this.node_list_powersWrap);
        this.node_list_powers_searchInput = $('.js-search-input', this.node_list_powersWrap);
        this.node_list_powers_cancelSearch = $('.js-cancel-search', this.node_list_powersWrap);
        this.node_list_powers_expandList = $('.js-expand-list', this.node_list_powersWrap);
        this.node_list_powers_sortOptions = $All('.js-sort-list .drop a', this.node_list_powersWrap);
        this.node_list_powers_approve = $('.js-approve', this.node_list_powersWrap);
        this.node_list_powers_delete = $('.js-delete', this.node_list_powersWrap);
        this.node_list_powers_spam = $('.js-spam', this.node_list_powersWrap);

        this.node_list_nav_navWrap = $('.js-list-nav', this.node_list_listwrap);
        this.node_list_nav_pageInput = $('.js-current-page', this.node_list_nav_navWrap);
        this.node_list_nav_maxPages = $('.js-max-pages', this.node_list_nav_navWrap);
        this.node_list_nav_nextPage = $('.js-next', this.node_list_nav_navWrap);
        this.node_list_nav_prevPage = $('.js-prev', this.node_list_nav_navWrap);


        this.node_list_items = [];
        this.have_items = !empty(this.node_list_items);

        this.node_list_expandItem = [];
        this.node_list_collapseItems = [];

        this.node_sidebar_wrap = $('.js-comment-extras');
        this.node_sidebar_infoWrap = $('.js-comment-extras .js-comment-info');
        this.node_sidebar_closeInfo = $('.js-close-comment-info', this.node_sidebar_infoWrap);
        this.node_sidebar_content = $('.js-comment-content', this.node_sidebar_infoWrap);
        this.node_sidebar_commentStatus = $('.js-comment-status', this.node_sidebar_infoWrap);

        this.node_sidebar_searchUser = $('.js-search-user', this.node_sidebar_infoWrap);
        this.node_sidebar_searchEmail = $('.js-search-email', this.node_sidebar_infoWrap);
        this.node_sidebar_searchIp = $('.js-search-ip', this.node_sidebar_infoWrap);

        this.node_sidebar_blacklist = $('.js-blacklist', this.node_sidebar_infoWrap).children[0];
        this.node_sidebar_whitelist = $('.js-whitelist', this.node_sidebar_infoWrap).children[0];

        this.node_sidebar_avatar = $('.js-avatar', this.node_sidebar_infoWrap);
        this.node_sidebr_name = $('.js-name', this.node_sidebar_infoWrap);
        this.node_sidebar_email = $('.js-email', this.node_sidebar_infoWrap);

        this.node_sidebar_repIcon = $('.js-rep-icon', this.node_sidebar_infoWrap);
        this.node_sidebar_reputation = $('.js-reputation', this.node_sidebar_infoWrap);
        this.node_sidebar_fCommentUnit = $('.js-first-comment-unit ', this.node_sidebar_infoWrap);
        this.node_sidebar_fCommentTime = $('.js-first-comment-time', this.node_sidebar_infoWrap);
        this.node_sidebar_ipAddress = $('.js-ip-address', this.node_sidebar_infoWrap);
        this.node_sidebar_commentCount = $('.js-comment-count', this.node_sidebar_infoWrap);
        this.node_sidebar_spamCount = $('.js-spam-count', this.node_sidebar_infoWrap);
        this.node_sidebar_commentLink = $('.js-link-to-comment', this.node_sidebar_infoWrap);

        this.node_sidebar_replyEditWrap = $('.js-comment-edit-reply-wrap', this.node_sidebar_infoWrap);
        this.node_sidebar_replyEditInput = $('.js-edit-reply-input', this.node_sidebar_infoWrap);
        this.node_sidebar_open_reply = $('.js-reply', this.node_sidebar_infoWrap);
        this.node_sidebar_open_save = $('.js-edit', this.node_sidebar_infoWrap);

        this.node_sidebar_cancelEdit = $('.js-cancel-edit', this.node_sidebar_infoWrap);
        this.node_sidebar_saveReply = $('.js-save-reply', this.node_sidebar_infoWrap);
        this.node_sibebar_saveEdit = $('.js-save-edit', this.node_sidebar_infoWrap);

        return this;
    };

    ajaxComments.prototype = {

        init: function() {

            var _this = this;

            var form = this.getListForm();

            makeLoading(this.node_list_list, true, 300);

            queueAjax(GLOBAL_AJAX_URL, 'POST', form, function(success) {
                    _this.dispatchList(success);
                    _this.addDynamicListeners();
                },
                function(error) {
                    _this.handleError();
                });

            this.initializeListeners();
        },

        getListForm: function() {
            this.currentPage = parseInt(this.node_list_nav_pageInput.value.trim());
            this.search = (this.node_list_powers_searchInput.value.trim() === '' ? false : this.node_list_powers_searchInput.value.trim());
            return {
                search: this.search,
                page: this.currentPage,
                sortBy: this.sortBy,
                ajaxRequest: this.node_list_list.dataset.listName,
                public_key: GLOBAL_PUBLIC_KEY,
                referer: window.location.href
            };
        },

        getItemActionForm: function(action, ids) {
            return {
                comment_ids: ids,
                ajaxRequest: action,
                public_key: GLOBAL_PUBLIC_KEY,
                referer: window.location.href
            };
        },

        getCheckedItems: function() {
            var ids = [];
            if (this.have_items) {
                var checks = $All('.js-item-check', this.node_list_list);
                for (var a = 0; a < checks.length; a++) {
                    var check = checks[a];
                    if (check.checked == true) {
                        ids.push(parentUntillClass(check, 'js-item').dataset.commentId);
                    }
                }
            }
            return ids;
        },

        initializeListeners: function() {

            var _this = this;

            // Mask page-number-input to only numbers
            VMasker(this.node_list_nav_pageInput).maskNumber();

            // Listener on page-number-input 
            this.node_list_nav_pageInput.addEventListener('keyup', function(e) {
                _this.skipToPage(e);
            });

            // Listener on check-all
            this.node_list_powers_checkAll.addEventListener('change', function() {
                var isChecked = event.target.checked;
                if (isChecked && _this.haveItems) {
                    _this.checkAll();
                } else {
                    _this.uncheckAll();
                }
            });

            // Listener on Search enter
            this.node_list_powers_searchInput.addEventListener('keyup', function(e) {
                _this.initSearch(e);
            });

            // Listener on search blur
            this.node_list_powers_searchInput.addEventListener('blur', function(e) {
                if (_this.node_list_powers_searchInput.value.trim() === '' && _this.search !== false) _this.clearSearch();
            });

            // Listener on close search
            this.node_list_powers_cancelSearch.addEventListener('click', function(e) {
                e.preventDefault();
                _this.node_list_powers_searchInput.value = '';
                if (_this.search !== false) _this.clearSearch();
            });

            // Listener on sort change
            for (var j = 0; j < this.node_list_powers_sortOptions.length; j++) {
                var option = this.node_list_powers_sortOptions[j];
                option.addEventListener('click', function() {
                    var sortBy = event.target.dataset.sort;
                    if (sortBy !== _this.sortBy) {
                        _this.sortBy = sortBy;
                        _this.refreshList();
                    }
                });
            }

            // Listener on expand-list
            this.node_list_powers_expandList.addEventListener('click', function(e) {
                e.preventDefault();
                var items = $All('.js-item', _this.node_list_list);
                if (!empty(items)) {
                    if (_this.expanded === false) {
                        _this.expandList(items);
                        _this.expanded = true;
                        _this.node_list_powers_expandList.innerText = 'Collapse';
                    } else {
                        _this.collapseList(items);
                        _this.expanded = false;
                        _this.node_list_powers_expandList.innerText = 'Expand';
                    }
                }
            });

            // Tabbed lists       
            for (var i = 0; i < this.node_list_tabClickers.length; i++) {
                this.node_list_tabClickers[i].addEventListener('click', function() {
                    _this.node_list_list = $('#' + event.target.dataset.tab, _this.node_list_listwrap);
                    _this.currentPage = 1;
                    _this.maxPages = 1;
                    _this.node_list_nav_pageInput.value = 1;
                    _this.refreshList();
                });
            }

            // close comment info
            this.node_sidebar_closeInfo.addEventListener('click', function() {
                event.preventDefault();
                removeClass(_this.node_sidebar_wrap, 'active');
            });

            // Reply to comment
            this.node_sidebar_open_reply.addEventListener('click', function() {
                event.preventDefault();
                addClass(_this.node_sidebar_replyEditWrap, 'reply');
                removeClass(_this.node_sidebar_replyEditWrap, 'edit');
                _this.node_sidebar_replyEditInput.value = '';
                _this.node_sidebar_replyEditInput.focus();
            });

            // Edit a comment
            this.node_sidebar_open_save.addEventListener('click', function() {
                event.preventDefault();
                addClass(_this.node_sidebar_replyEditWrap, 'edit');
                removeClass(_this.node_sidebar_replyEditWrap, 'reply');
                _this.node_sidebar_replyEditInput.value = _this.node_sidebar_content.dataset.content;
                _this.node_sidebar_replyEditInput.focus();
            });

            // Cancel an edit/reply
            this.node_sidebar_cancelEdit.addEventListener('click', function() {
                event.preventDefault();
                removeClass(_this.node_sidebar_replyEditWrap, 'edit');
                removeClass(_this.node_sidebar_replyEditWrap, 'reply');
            });

            // Save reply to comment
            this.node_sidebar_saveReply.addEventListener('click', function() {
                _this.saveReply();
            });

            // Save edit to comment
            this.node_sibebar_saveEdit.addEventListener('click', function() {
                _this.saveEdit();
            });

            // Approve checked items
            this.node_list_powers_approve.addEventListener('click', function() {
                event.preventDefault();
                var checkedItems = _this.getCheckedItems();
                if (!empty(checkedItems)) {
                    var form = _this.getItemActionForm('admin_approve_comments', checkedItems);
                    _this.confirmItemAction(form, 'approve');
                }
            });

            // Delete checked items
            this.node_list_powers_delete.addEventListener('click', function() {
                event.preventDefault();
                var checkedItems = _this.getCheckedItems();
                if (!empty(checkedItems)) {
                    var form = _this.getItemActionForm('admin_delete_comments', checkedItems);
                    _this.confirmItemAction(form, 'delete');
                }
            });

            // Spam checked items
            this.node_list_powers_spam.addEventListener('click', function() {
                event.preventDefault();
                var checkedItems = _this.getCheckedItems();
                if (!empty(checkedItems)) {
                    var form = _this.getItemActionForm('admin_spam_comments', checkedItems);
                    _this.confirmItemAction(form, 'spam');
                }
            });

            // Search all from username
            this.node_sidebar_searchUser.addEventListener('click', function() {
                _this.initSearch('user:' + _this.node_sidebar_searchUser.dataset.search);
            });

            // Search all from IP
            this.node_sidebar_searchIp.addEventListener('click', function() {
                _this.initSearch('ip:' + _this.node_sidebar_searchIp.dataset.search);
            });

            // Search all from email
            this.node_sidebar_searchEmail.addEventListener('click', function() {
                _this.initSearch('email:' + _this.node_sidebar_searchEmail.dataset.search);
            });

            // Blacklist
            this.node_sidebar_blacklist.addEventListener('click', function() {
                event.preventDefault();
                if (!hasClass(_this.node_sidebar_blacklist, 'active')) {
                    _this.confirmModerateIpAddress('blacklist');
                } else {
                    _this.confirmModerateIpAddress('nolist');
                }
            });

            // Whitelist
            this.node_sidebar_whitelist.addEventListener('click', function() {
                event.preventDefault();
                if (!hasClass(_this.node_sidebar_whitelist, 'active')) {
                    _this.confirmModerateIpAddress('whitelist');
                } else {
                    _this.confirmModerateIpAddress('nolist');
                }
            });

            // Next page
            this.node_list_nav_nextPage.addEventListener('click', function() {
                event.preventDefault();
                _this.skipToPage(_this.currentPage + 1);

            });
            // Prvious page
            this.node_list_nav_prevPage.addEventListener('click', function() {
                event.preventDefault();
                _this.skipToPage(_this.currentPage - 1);
            });

        },


        addDynamicListeners: function() {

            var _this = this;

            this.node_list_items = $All('.js-item', this.node_list_listwrap);
            this.have_items = !empty(this.node_list_items);
            this.node_list_expandItem = $All('.js-expand-item', this.node_list_listwrap);
            this.node_list_collapseItems = $All('.js-collapse-item', this.node_list_listwrap);

            this.node_list_itemApproves = $All('.js-item-approve', this.node_list_listwrap);;
            this.node_list_itemSpams = $All('.js-item-spam', this.node_list_listwrap);;
            this.node_list_itemDeletes = $All('.js-item-delete', this.node_list_listwrap);;
            this.node_list_itemSearchIp = $All('.js-item-search-ip', this.node_list_listwrap);;

            // Get comment info on item click
            if (this.have_items) {
                for (var a = 0; a < this.node_list_items.length; a++) {
                    var item = this.node_list_items[a];
                    item.addEventListener('click', function() {
                        _this.requestInfo(event.target);
                    });
                }
            }

            // expand list items
            if (this.have_items) {
                for (var b = 0; b < this.node_list_expandItem.length; b++) {
                    var expander = this.node_list_expandItem[b];
                    expander.addEventListener('click', function() {
                        event.preventDefault();
                        var item = parentUntillClass(event.target, 'js-item');
                        addClass(item, 'expanded');
                    });
                }
            }

            // collapse list items
            if (this.have_items) {
                for (var c = 0; c < this.node_list_collapseItems.length; c++) {
                    var collapser = this.node_list_collapseItems[c];
                    collapser.addEventListener('click', function() {
                        event.preventDefault();
                        var item = parentUntillClass(event.target, 'js-item');
                        removeClass(item, 'expanded');
                    });
                }
            }

            // approve list item
            if (this.have_items) {
                for (var d = 0; d < this.node_list_itemApproves.length; d++) {
                    var button = this.node_list_itemApproves[d];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        var item = parentUntillClass(event.target, 'js-item');
                        var form = _this.getItemActionForm('admin_approve_comments', [item.dataset.commentId]);
                        if (!hasClass(link.parentNode, 'disabled')) _this.confirmItemAction(form, 'approve');
                    });
                }
            }

            // Spam list item
            if (this.have_items) {
                for (var e = 0; e < this.node_list_itemSpams.length; e++) {
                    var button = this.node_list_itemSpams[e];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        var item = parentUntillClass(event.target, 'js-item');
                        var form = _this.getItemActionForm('admin_spam_comments', [item.dataset.commentId]);
                        if (!hasClass(link.parentNode, 'disabled')) _this.confirmItemAction(form, 'spam');
                    });
                }
            }

            // Delete list item
            if (this.have_items) {
                for (var f = 0; f < this.node_list_itemDeletes.length; f++) {
                    var button = this.node_list_itemDeletes[f];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        var item = parentUntillClass(event.target, 'js-item');
                        var form = _this.getItemActionForm('admin_delete_comments', [item.dataset.commentId]);
                        if (!hasClass(link.parentNode, 'disabled')) _this.confirmItemAction(form, 'delete');
                    });
                }
            }

            // Item search by ip
            if (this.have_items) {
                for (var g = 0; g < this.node_list_itemSearchIp.length; g++) {
                    var button = this.node_list_itemSearchIp[g];
                    button.addEventListener('click', function() {
                        event.preventDefault();
                        var link = closest(event.target, 'a');
                        _this.initSearch('ip:' + link.innerHTML.trim());
                    });
                }
            }
        },

        checkAll: function() {
            var itemChecks = $All('.js-item-select input[type=\'checkbox\']', this.node_list_list);
            if (!empty(itemChecks)) {
                for (var i = 0; i < itemChecks.length; i++) {
                    itemChecks[i].checked = true;
                }
            }
        },

        uncheckAll: function() {
            var itemChecks = $All('.js-item-select input[type=\'checkbox\']', this.node_list_list);
            if (!empty(itemChecks) && this.haveItems) {
                for (var i = 0; i < itemChecks.length; i++) {
                    itemChecks[i].checked = false;
                }
            }
            this.node_list_powers_checkAll.checked = false;
        },

        expandList: function(items) {
            for (var i = 0; i < items.length; i++) {
                addClass(items[i], 'expanded');
            }
        },

        collapseList: function(items) {
            for (var i = 0; i < items.length; i++) {
                removeClass(items[i], 'expanded');
            }
        },

        initSearch: function(e) {

            if (typeof e === 'string') {
                var query = e;
                this.node_list_powers_searchInput.value = query;
                addClass(this.node_list_powers_searchInput.parentNode, 'active');
                this.search = query;
                this.currentPage = 1;
                this.maxPages = 1;
                this.node_list_nav_pageInput.value = 1;
                triggerEvent(this.node_list_tabClickers[0], 'click');
                return;
            }

            var query = this.node_list_powers_searchInput.value.trim();

            if (query === '' && e.keyCode == 13 && this.search !== false) {
                this.clearSearch();
                return;
            }

            addClass(this.node_list_powers_searchInput.parentNode, 'active');
            if (e.keyCode == 13) {
                this.search = query;
                this.refreshList();
            }

        },

        clearSearch: function() {
            removeClass(this.node_list_powers_searchInput.parentNode, 'active');
            this.search = false;
            this.refreshList();
        },

        skipToPage: function(e) {

            var requestedPage = false;

            if (typeof e === 'number') {
                requestedPage = e;
            } else if (e.keyCode && e.keyCode == 13) {
                requestedPage = this.node_list_nav_pageInput.value.trim();
                requestedPage = (requestedPage === '' ? 0 : requestedPage);
                requestedPage = parseInt(requestedPage);
            }

            if (requestedPage !== false) {

                if (requestedPage > this.maxPages || requestedPage < 1 || requestedPage === this.currentPage) {
                    this.node_list_nav_pageInput.value = this.currentPage;
                } else {
                    this.currentPage = requestedPage;
                    this.node_list_nav_pageInput.value = requestedPage;
                    this.refreshList();
                }
            }
        },

        updateNav: function(items) {

            var currentPage = this.currentPage;
            this.maxPages = count(items);
            this.node_list_nav_maxPages.innerHTML = 'of ' + this.maxPages;

            if (currentPage >= this.maxPages) {
                addClass(this.node_list_nav_nextPage, 'disabled');
            } else {
                removeClass(this.node_list_nav_nextPage, 'disabled');
            }

            if (currentPage > 1) {
                removeClass(this.node_list_nav_prevPage, 'disabled');
            } else {
                addClass(this.node_list_nav_prevPage, 'disabled');
            }
        },

        dispatchList: function(xhr) {

            xhr = isJSON(xhr);

            if (xhr && isset(xhr.details[this.currentPage - 1]) && !empty(xhr.details[this.currentPage - 1])) {
                this.updateNav(xhr.details);
                this.uncheckAll();
                this.haveItems = true;
                this.insertComments(xhr.details[this.currentPage - 1]);
            } else {
                this.noList();
            }

        },

        handleError: function() {
            this.haveItems = false;
            this.noList();
        },

        insertComments: function(comments) {

            var list = this.node_list_list;
            var content = '<tbody>';
            var expanded = (this.expanded === true ? 'expanded' : '');

            undoLoading(list, true);

            for (var i = 0; i < comments.length; i++) {
                var comment = comments[i];
                var canExpand = comment['content'].length > 200;
                var showMore = canExpand ? '<a href="#" class="more js-expand-item">more</a>' : '';
                var showLess = canExpand ? '<a href="#" class="less js-collapse-item">less</a>' : '';
                var disabledApprove = comment['status'] === 'approved' ? 'disabled' : '';
                var disabledSpam = comment['status'] === 'spam' ? 'disabled' : '';
                var disabledDelete = comment['status'] === 'deleted' ? 'disabled' : '';

                content += cleanInnerHTML([
                    '<tr class="comment list-item js-item ' + comment['status'] + ' ' + expanded + '" data-comment-id="' + comment['id'] + '">',
                    '<td>',
                    '<h5 class="title">',
                    '<a href="' + comment['permalink'] + '#comment-' + comment['id'] + '" target="_blank">' + comment['title'] + '</a>',
                    '</h5>',
                    '<div class="avatar">',
                    '<img src="' + comment['avatar'] + '" width="42" height="42" />',
                    '</div>',
                    '<div class="comment-body">',
                    '<div class="comment-meta">',
                    '<span class="comment-name">',
                    comment['name'],
                    '</span>',
                    '<span class="bullet">•</span>',
                    '<a href="' + comment['permalink'] + '#comment-' + comment['id'] + '" target="_blank">',
                    '<abbr class="time-ago">',
                    timeAgo(comment['date']), ' ago',
                    '</abbr>',
                    '</a>',
                    '<span class="right">',
                    '<span class="comment-author-info">' + comment['email'] + '</span>',
                    '<span class="bullet">•</span>',
                    '<span><a class="js-item-search-ip" href="#">' + comment['ip_address'] + '</a></span>',
                    '</span>',
                    '</div>',

                    '<div class="check-wrap js-item-select">',
                    '<input class="js-item-check" id="comment-select-' + comment['id'] + '" type="checkbox" name="comment-select-' + comment['id'] + '">',
                    '<label class="checkbox small mini" for="comment-select-' + comment['id'] + '"></label>',
                    '</div>',

                    '<div class="item-preview">',
                    strReduce(comment['html_content'], 200, '...'),
                    showMore,
                    '</div>',

                    '<div class="item-full">',
                    comment['html_content'],
                    showLess,
                    '</div>',
                    '<ul class="comment-actions js-comment-actions">',
                    '<li class="js-item-approve approve ' + disabledApprove + '">',
                    '<a href="#">',
                    '<svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#checkmark"></use></svg>',
                    'Approve',
                    '</a>',
                    '</li>',
                    '<li class="js-item-spam spam ' + disabledSpam + '">',
                    '<a href="#">',
                    '<svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#blocked"></use></svg>',
                    'Spam',
                    '</a>',
                    '</li>',
                    '<li class="js-item-delete delete ' + disabledDelete + '">',
                    '<a href="#">',
                    '<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#trash"></use></svg>',
                    'Delete',
                    '</a>',
                    '</li>',
                    '</ul>',
                    '</div>',
                    '</td>',
                    '</tr>'
                ]);
            }

            content += '</tbody>';
            newNode('table', 'horizontal comments-table', null, content, list);
        },

        noList: function() {
            var message = 'No comments to display. There are no comments that match this status or search.';
            undoLoading(this.node_list_list, true);
            addClass(this.node_list_nav_nextPage, 'disabled');
            addClass(this.node_list_nav_prevPage, 'disabled');
            insertMessage('plain', message, this.node_list_list, 'small', true);
        },

        refreshList: function() {

            var form = this.getListForm();
            var _this = this;

            form['ajaxRequest'] = this.node_list_list.dataset.listName;

            makeLoading(this.node_list_list, true, 300);

            if (GLOBAL_AJAX_ENABLED) {

                Ajax.post(GLOBAL_AJAX_URL, form, function(success) {
                        _this.dispatchList(success);
                        _this.addDynamicListeners();
                    },
                    function(error) {
                        _this.handleError();
                    });
            } else {
                this.handleError();
            }
        },

        saveReply: function() {

            var _this = this;

            if (GLOBAL_AJAX_ENABLED) {

                var form = {
                    comment_id: this.node_sidebar_content.dataset.commentId,
                    content: this.node_sidebar_replyEditInput.value,
                    ajaxRequest: 'admin_reply_comment',
                    public_key: GLOBAL_PUBLIC_KEY,
                    referer: window.location.href
                };

                showGlobalSpinner();

                Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                        xhr = isJSON(success);

                        if (xhr) {
                            _this.refreshList();
                            pushNotification('success', 'Your comment was successfully posted.');
                            removeClass(_this.node_sidebar_replyEditWrap, 'edit');
                            removeClass(_this.node_sidebar_replyEditWrap, 'reply');
                        } else {
                            pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                        }
                        hideGlobalSpinner();

                    },
                    function(error) {
                        hideGlobalSpinner();
                        pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                    });
            } else {
                pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
            }


        },

        saveEdit: function() {

            var _this = this;

            if (GLOBAL_AJAX_ENABLED) {

                var form = {
                    comment_id: this.node_sidebar_content.dataset.commentId,
                    content: this.node_sidebar_replyEditInput.value,
                    ajaxRequest: 'admin_edit_comment',
                    public_key: GLOBAL_PUBLIC_KEY,
                    referer: window.location.href
                };

                showGlobalSpinner();

                Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                        xhr = isJSON(success);

                        if (xhr) {
                            _this.updateComment(xhr.details, _this);
                            pushNotification('success', 'Edit was successfully saved.');
                            removeClass(_this.node_sidebar_replyEditWrap, 'edit');
                            removeClass(_this.node_sidebar_replyEditWrap, 'reply');
                        } else {
                            pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                        }
                        hideGlobalSpinner();

                    },
                    function(error) {
                        hideGlobalSpinner();
                        pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                    });
            } else {
                pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
            }


        },

        updateComment: function(newContent, _this) {
            _this.node_sidebar_content.innerHTML = newContent;
            _this.node_sidebar_content.dataset.content = _this.node_sidebar_replyEditInput.value;
            _this.refreshList();
        },

        requestInfo: function(target) {

            if (isNodeType(target, 'a')) return;
            var item = parentUntillClass(target, 'js-item');
            var _this = this;

            if (GLOBAL_AJAX_ENABLED) {

                var form = this.getInfoForm(item);

                Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                        xhr = isJSON(success);

                        if (xhr) {
                            _this.displayCommentInfo(xhr.details, item);
                        } else {
                            // error
                        }

                    },
                    function(error) {
                        // error
                    });
            } else {
                // error
            }
        },

        displayCommentInfo: function(authorInfo, comment) {

            var commentID = comment.dataset.commentId;
            var commentLink = $('.title a', comment).getAttribute('href');
            var TimAgo = timeAgo(authorInfo['first_date'], true);

            this.node_sidebar_content.innerHTML = authorInfo['html_content'];
            this.node_sidebar_content.dataset.content = authorInfo['content'];
            this.node_sidebar_replyEditInput.value = authorInfo['content'];
            this.node_sidebar_content.dataset.commentId = commentID;
            this.node_sidebar_commentStatus.innerHTML = 'This comment is ' + authorInfo['status'];
            this.node_sidebar_searchUser.innerHTML = 'View all from ' + authorInfo['name'];
            this.node_sidebar_searchEmail.innerHTML = 'View all from ' + authorInfo['email'];
            this.node_sidebar_searchIp.innerHTML = 'View all from ' + authorInfo['ip_address'];
            this.node_sidebar_avatar.innerHTML = '<img src="' + authorInfo['avatar'] + '" width="32" height="32" />'
            this.node_sidebr_name.innerHTML = authorInfo['name'];
            this.node_sidebar_email.innerHTML = authorInfo['email'];
            this.node_sidebar_fCommentUnit.innerHTML = TimAgo['unit'] + ' ago';
            this.node_sidebar_fCommentTime.innerHTML = TimAgo['time'];
            this.node_sidebar_ipAddress.innerHTML = authorInfo['ip_address'];
            this.node_sidebar_commentCount.innerHTML = authorInfo['posted_count'];
            this.node_sidebar_spamCount.innerHTML = authorInfo['spam_count'];
            this.node_sidebar_commentLink.setAttribute('href', commentLink);
            this.node_sidebar_searchEmail.dataset.search = authorInfo['email'];
            this.node_sidebar_searchIp.dataset.search = authorInfo['ip_address'];
            this.node_sidebar_searchUser.dataset.search = authorInfo['name'];

            bool(authorInfo['blacklisted']) === true ? addClass(this.node_sidebar_blacklist, 'active') : removeClass(this.node_sidebar_blacklist, 'active');
            bool(authorInfo['whitelisted']) === true ? addClass(this.node_sidebar_whitelist, 'active') : removeClass(this.node_sidebar_whitelist, 'active');

            var reputation = authorInfo['reputation'];

            removeClass(this.node_sidebar_repIcon, ['bad', 'good', 'average']);
            removeClass(this.node_sidebar_commentStatus, ['deleted', 'approved', 'pending', 'spam']);

            if (reputation < 0) {
                addClass(this.node_sidebar_repIcon, 'bad');
                this.node_sidebar_reputation.innerHTML = 'Low';
            } else if (reputation > 0 && reputation < 2) {
                addClass(this.node_sidebar_repIcon, 'average');
                this.node_sidebar_reputation.innerHTML = 'Average';
            } else if (reputation > 2) {
                addClass(this.node_sidebar_repIcon, 'good');
                this.node_sidebar_reputation.innerHTML = 'Good';
            }
            addClass(this.node_sidebar_commentStatus, authorInfo['status']);
            addClass(this.node_sidebar_wrap, 'active');

        },

        getInfoForm: function(item) {
            return {
                comment_id: item.dataset.commentId,
                ajaxRequest: 'admin_comment_info',
                public_key: GLOBAL_PUBLIC_KEY,
                referer: window.location.href
            };
        },

        confirmModerateIpAddress: function(blackOrWhite) {

            if (GLOBAL_AJAX_ENABLED) {

                var _this = this;
                var msg;
                var form = {
                    ip_address: this.node_sidebar_ipAddress.innerHTML.trim(),
                    action: blackOrWhite,
                    ajaxRequest: 'admin_black_whitelist_ip',
                    public_key: GLOBAL_PUBLIC_KEY,
                    referer: window.location.href
                };

                if (blackOrWhite === 'nolist') {
                    blackOrWhite = 'remove';
                    msg = 'Are you POSITIVE you want to remove users commenting from the IP address ' + form.ip_address + ' from all lists?';
                } else {
                    msg = 'Are you POSITIVE you want to ' + blackOrWhite + ' users commenting from the IP address ' + form.ip_address + ' ?';
                }

                pushCallBackNotification(
                    'info',
                    msg,
                    ucfirst(blackOrWhite) + ' IP',
                    _this.moderateIpAddress, [form, _this],
                    null,
                    null
                );
            } else {
                pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
            }

        },

        moderateIpAddress: function() {

            var form = arguments[0][0];
            var _this = arguments[0][1];

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    xhr = isJSON(success);

                    if (xhr) {
                        pushNotification('success', 'The IP address ' + form.ip_address + ' was successfully ' + form.action + 'ed.');
                        if (form.action === 'whitelist') {
                            addClass(_this.node_sidebar_whitelist, 'active');
                            removeClass(_this.node_sidebar_blacklist, 'active');
                        } else if (form.action === 'blacklist') {
                            removeClass(_this.node_sidebar_whitelist, 'active');
                            addClass(_this.node_sidebar_blacklist, 'active');
                        } else if (form.action === 'nolist') {
                            removeClass(_this.node_sidebar_whitelist, 'active');
                            removeClass(_this.node_sidebar_blacklist, 'active');
                        }
                    } else {
                        pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                    }

                },
                function(error) {
                    pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                });
        },

        confirmItemAction: function(form, action) {
            if (GLOBAL_AJAX_ENABLED) {

                var _this = this;
                var plural = count(form.comment_ids);

                pushCallBackNotification(
                    'info',
                    'Are you POSITIVE you want to ' + action + ' ' + plural + ' ' + pluralize('comment', plural) + '?',
                    ucfirst(action) + ' ' + pluralize('Comment', plural),
                    _this.actionComment, [form, action, _this],
                    null,
                    null
                );
            } else {
                pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
            }

        },

        actionComment: function() {

            var form = arguments[0][0];
            var action = arguments[0][1];
            var _this = arguments[0][2];

            var plural = count(form.comment_ids);

            Ajax.post(GLOBAL_AJAX_URL, form, function(success) {

                    xhr = isJSON(success);

                    if (action === 'approve') action = 'approved';
                    if (action === 'delete') action = 'deleted';

                    if (xhr) {
                        pushNotification('success', plural + ' ' + pluralize('comment', plural) + ' ' + pluralize('was', plural) + ' successfully marked as ' + action);
                        _this.refreshList();
                        triggerEvent(_this.node_sidebar_closeInfo, 'click');
                    } else {
                        pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                    }

                },
                function(error) {
                    pushNotification('error', 'There was an error processing your request. Try again in a few moments or try refreshing the page.');
                });
        },

    };

    var commentsListWrap = $('.js-ajax-list-wrap.js-comments-wrap');

    if (nodeExists(commentsListWrap)) ajaxComments(commentsListWrap).init();

}());

// ##############################################################################
// FILE: UI/dropDowns.js
// ##############################################################################

/* Header dropdown menu */
(function() {

    var trigger = $('.header .drop-down > a');
    if (nodeExists(trigger)) {
        trigger.addEventListener('click', toggleDD);
        document.addEventListener('click', removeDD);
    }

    function toggleDD() {
        event.preventDefault();
        if (hasClass(trigger.parentNode, 'active')) {
            removeClass(trigger.parentNode, 'active');
        } else {
            addClass(trigger.parentNode, 'active');
        }
    }


    function removeDD() {
        if (event.target === trigger || trigger === closest(event.target, 'a')) return;
        removeClass(trigger.parentNode, 'active');
    }

}());

/* Dropdown buttons - generic */
(function() {

    var dropTriggers = $All('.js-button-down .button');

    if (nodeExists($('.js-button-down .button'))) {
        initDropDowns();
        document.addEventListener('click', removeDDS);
    }

    function initDropDowns() {
        for (var i = 0; i < dropTriggers.length; i++) {
            dropTriggers[i].addEventListener('click', toggleDRD);
            initValueChange(dropTriggers[i]);
        }
    }

    function initValueChange(button) {
        var drop = $('.drop div', button.parentNode);
        drop.addEventListener('click', changeValue);
    }

    function changeValue() {
        event.preventDefault();
        var target = closest(event.target, 'a');
        var wrap = parentUntillClass(target, 'js-button-down');
        var btn = $('.button', wrap);
        var icon = $('svg', btn);
        icon = (typeof icon === 'undefined' ? null : icon);
        btn.innerHTML = target.textContent;
        if (icon) btn.appendChild(icon);
    }


    function toggleDRD() {
        event.preventDefault();
        var button = closest(event.target, 'a');
        var wrap = button.parentNode;
        if (hasClass(wrap, 'active')) {
            button.blur();
            removeClass(wrap, 'active');
        } else {
            button.blur();
            addClass(wrap, 'active');
        }
    }

    function removeDDS() {

        var clicked = closest(event.target, 'a');

        if (clicked && hasClass(clicked.parentNode, 'js-button-down')) {
            return;
        }

        for (var i = 0; i < dropTriggers.length; i++) {
            dropTriggers[i].blur();
            removeClass(dropTriggers[i].parentNode, 'active');
        }
    }

}());

// ##############################################################################
// FILE: UI/notifiactions.js
// ##############################################################################

// ##############################################################################
// Notifications
// ##############################################################################
var activeNotifs = [];

function pushNotification(type, message) {
    var notifWrap = $('.js-nofification-wrap');
    var content = '<div class="message-icon"><svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#' + type + '"></use></svg></div><div class="message-body"><p>' + message + '</p></div>';
    var id = makeid(20);
    var notif = newNode('div', type + ' message flipInX animated', null, content, notifWrap);
    activeNotifs.push({
        node: notif,
        id: id,
        timeout: setTimeout(function() {
            removeNotif(notif);
        }, 6000),
    });
    notif.addEventListener('click', function() {
        removeNotif(notif);
    });
}

function removeNotif(node) {
    for (var i = 0; i < activeNotifs.length; i++) {
        if (node === activeNotifs[i].node) {
            clearTimeout(activeNotifs[i].timeout);
            fadeOutAndRemove(activeNotifs[i].node);
            activeNotifs.splice(i, 1);
        }
    }
}

function pushCallBackNotification(type, message, confirmType, confirmCallback, confirmArgs, cacnelCallback, cancelArgs) {
    var notifWrap = $('.js-nofification-wrap');
    var content = '<div class="message-icon"><svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#' + type + '"></use></svg></div><div class="message-body"><p>' + message + '</p><div class="row"><button class="button cancel small js-cancel">Cancel</button><button class="button small confirm red js-confirm">' + confirmType + '</button></div></div>';
    var notif = newNode('div', type + ' message flipInX animated message-confirm', null, content, notifWrap);
    var cancel = $('.js-cancel', notif);
    var confirm = $('.js-confirm', notif);

    cancel.addEventListener('click', function() {
        if (isCallable(cacnelCallback)) cacnelCallback(cancelArgs);
        event.preventDefault();
        fadeOutAndRemove(notif);
    });

    confirm.addEventListener('click', function() {
        event.preventDefault();
        if (isCallable(confirmCallback)) confirmCallback(confirmArgs);
        fadeOutAndRemove(notif);
    });

}

// ##############################################################################
// GLOBAL SPINNER
// ##############################################################################

function showGlobalSpinner() {
    addClass($('.js-global-spinner'), 'active');
}

function hideGlobalSpinner() {
    removeClass($('.js-global-spinner'), 'active');
}

// ##############################################################################
// FILE: UI/tabs.js
// ##############################################################################

(function() {

    var triggers = $All('.js-tabs-wrap a');

    if (nodeExists($('.js-tabs-wrap a'))) {
        for (var i = 0; i < triggers.length; i++) {
            triggers[i].addEventListener('click', toggleTabs);
        }
    }

    function toggleTabs() {
        event.preventDefault();
        var clicked = event.target;
        if (hasClass(clicked, 'active')) {
            return;
        }
        var tabsWrap = parentUntillClass(clicked, 'js-tabs-wrap');
        var activeTab = $('a.active', tabsWrap);
        var activePanel = $('#' + activeTab.dataset.tab);
        var newPanel = $('#' + clicked.dataset.tab);
        if (nodeExists(activePanel)) {
            removeClass(activePanel, 'active');
        }
        if (nodeExists(newPanel)) {
            addClass(newPanel, 'active');
        }
        removeClass(activeTab, 'active');
        addClass(clicked, 'active');
        if (hasClass(tabsWrap, 'js-url-tabs')) {
            var title = clicked.dataset.tabTitle;
            var slug = clicked.dataset.tabUrl;
            URLtabber(title, slug);
        }
    }

    function URLtabber(title, url) {
        var baseURL = rtrim(window.location.href, '/');
        baseURL = baseURL.split("/");
        baseURL.pop();
        baseURL = baseURL.join('/');

        //prevents browser from storing history with each change:
        if (window.history.replaceState) {
            var statedata = title;
            window.history.replaceState(statedata, title, baseURL + "/" + url + "/");
        }
        document.title = title;
    }

}());

// ##############################################################################
// FILE: UI/loading.js
// ##############################################################################

/* Insert a loading spinner into a div */
function makeLoading(el, clearEl, height) {

    clearEl = (typeof clearEl === 'undefined' ? false : clearEl);

    height = (typeof height === 'undefined' ? 300 : height);

    var actualHeight = el.style.height || el.clientHeight || el.offsetHeight;

    actualHeight = parseInt(actualHeight);

    height = (height < actualHeight || actualHeight === 0 ? height : actualHeight);

    el.style.height = height + 'px';
    el.style.position = 'relative';
    if (clearEl) {
        el.innerHTML = '<div class="div-spinner active"><span class="spinner1"></span><span class="spinner2"></span><svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo-simple"></use></svg></div>';
    } else {
        var loader = document.createElement('div');
        loader.innerHTML = '<span class="spinner1"></span><span class="spinner2"></span><svg viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo-simple"></use></svg>';
        loader.className = 'div-spinner active';
        el.appendChild(loader);
    }

}

/* Remove a loading spinner from a dig */
function undoLoading(el, clearEl) {

    clearEl = (typeof clearEl === 'undefined' ? false : clearEl);

    el.style.removeProperty('height');
    el.style.removeProperty('position');

    if (clearEl) {
        el.innerHTML = '';
    } else {
        removeFromDOM($('.div-spinner', el));
    }
}

// ##############################################################################
// FILE: UI/messages.js
// ##############################################################################

function insertMessage(type, content, target, size, clearEl) {

    clearEl = (typeof clearEl === 'undefined' ? false : clearEl);
    size = (typeof size === 'undefined' ? '' : size);

    var icon = (type !== 'plain' ? type : 'info');
    var message = document.createElement('div');
    message.className = 'row';
    message.innerHTML = cleanInnerHTML([
        '<div class="message ' + type + ' ' + size + '">',
        '<div class="message-icon">',
        '<svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#' + icon + '"></use></svg>',
        '</div>',
        '<div class="message-body">',
        '<p>' + content + '</p>',
        '</div>',
        '</div>'
    ]);
    if (clearEl) target.innerHTML = '';
    target.appendChild(message);
}

// ##############################################################################
// FILE: Pages/Account/login.js
// ##############################################################################

(function() {

    var loginForm = $('.login.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(loginForm)) {
        inputs = $All('input', loginForm);
        submitBtn = $('.submit', loginForm)
        submitBtn.addEventListener('click', submitLogin);
    }

    function submitLogin() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(loginForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_login');

        if (!validator.validForm) {
            showAjaxInputErrors(inputs, loginForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {

                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        window.location.href = GLOBAL_AJAX_URL + 'articles/';
                    } else {
                        showAjaxFormResult(loginForm, 'error');
                        return;
                    }
                },
                function(error) {
                    showAjaxFormResult(loginForm, 'error');
                    return;
                });
        } else {
            showAjaxFormResult(loginForm, 'error');
        }

    }

}());

// ##############################################################################
// FILE: Pages/Account/register.js
// ##############################################################################

(function() {


    var registerFrom = $('.js-register-form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(registerFrom)) {
        console.log(registerFrom);

        inputs = $All('input', registerFrom);
        submitBtn = $('.submit', registerFrom)
        submitBtn.addEventListener('click', submitLogin);
    }

    function submitLogin() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(registerFrom);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_register');

        if (!validator.validForm) {
            showAjaxInputErrors(validator.invalids, registerFrom);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    var responseObj = isJSON(success);
                    if (responseObj && responseObj.details === 'valid') {
                        window.location.href = GLOBAL_AJAX_URL + 'settings/account';
                    } else {
                        showAjaxFormResult(registerFrom, 'error', responseObj.details);
                        return;
                    }
                },
                function(error) {
                    showAjaxFormResult(registerFrom, 'error', 'There was an error processing your request.');
                    return;
                });
        } else {
            showAjaxFormResult(registerFrom, 'error', 'There was an error processing your request.');
        }

    }

}());

// ##############################################################################
// FILE: Pages/Account/forgotPassword.js
// ##############################################################################

(function() {


    var forgotPassForm = $('.forgot-password.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(forgotPassForm)) {
        inputs = $All('input', forgotPassForm);
        submitBtn = $('.submit', forgotPassForm)
        submitBtn.addEventListener('click', submitForgotPassword);
    }

    function submitForgotPassword() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(forgotPassForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_forgot_password');

        if (!validator.validForm) {
            showAjaxInputErrors(inputs, forgotPassForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    showAjaxFormResult(forgotPassForm, 'info');
                },
                function(error) {
                    showAjaxFormResult(forgotPassForm, 'info');
                    return;
                });
        } else {
            showAjaxFormResult(forgotPassForm, 'info');
        }

    }

}());

// ##############################################################################
// FILE: Pages/Account/forgotUsername.js
// ##############################################################################

(function() {


    var forgotUsernameForm = $('.forgot-username.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(forgotUsernameForm)) {
        inputs = $All('input', forgotUsernameForm);
        submitBtn = $('.submit', forgotUsernameForm)
        submitBtn.addEventListener('click', submitForgotPassword);
    }

    function submitForgotPassword() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(forgotUsernameForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_forgot_username');

        if (!validator.validForm) {
            showAjaxInputErrors(inputs, forgotUsernameForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    showAjaxFormResult(forgotUsernameForm, 'info');
                },
                function(error) {
                    showAjaxFormResult(forgotUsernameForm, 'info');
                    return;
                });
        } else {
            showAjaxFormResult(forgotUsernameForm, 'info');
        }

    }

}());

// ##############################################################################
// FILE: Pages/Account/resetPassword.js
// ##############################################################################


(function() {


    var restPasswordForm = $('.reset-password.setup-panel form');
    var submitBtn;
    var inputs;

    // initialization
    if (nodeExists(restPasswordForm)) {
        inputs = $All('input', restPasswordForm);
        submitBtn = $('.submit', restPasswordForm)
        submitBtn.addEventListener('click', submitForgotPassword);
    }

    function submitForgotPassword() {

        // dont submit when loading
        if (hasClass(submitBtn, 'active')) return;

        // remove errors and add spinner on submit button
        clearAjaxInputErrors(restPasswordForm);

        // validate the form
        var validator = formValidator(inputs).validateForm();
        validator.formAppend('ajaxRequest', 'admin_reset_password');

        if (!validator.validForm) {
            showAjaxInputErrors(inputs, restPasswordForm);
            return;
        }

        if (GLOBAL_AJAX_ENABLED) {

            Ajax.post(GLOBAL_AJAX_URL, validator.getForm(), function(success) {
                    var responseObj = isJSON(success);

                    if (responseObj && responseObj.details === 'valid') {
                        showAjaxFormResult(restPasswordForm, 'success');
                        return;
                    } else {
                        showAjaxFormResult(restPasswordForm, 'error');
                        return;
                    }
                },
                function(error) {
                    showAjaxFormResult(restPasswordForm, 'error');
                    return;
                });
        } else {
            showAjaxFormResult(restPasswordForm, 'error');
        }

    }

}());

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

        inputs = getFormInputs(kansoSettingsForm);

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
