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
