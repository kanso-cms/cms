(function(window) {

    // Library initializer
    var JSHelper = function() {

        this.version = "1.0.0";

        this.author = "Joe Howard";

        this.cssPrefixable = [

            // transitions
            'transition',
            'transition-delay',
            'transition-duration',
            'transition-property',
            'transition-timing-function',

            // trnasforms
            'transform',
            'transform-origin',
            'transform-style',
            'perspective',
            'perspective-origin',
            'backface-visibility',

            // misc
            'box-sizing',
            'calc',
        ];

        this.cssPrefixes = [
            '-webkit-',
            '-moz-',
            '-ms-',
            '-o-'
        ];

        this.cssEasings = {

            // Defaults
            ease: 'ease',
            linear: 'linear',
            easeIn: 'ease-in',
            easeOut: 'ease-out',
            easeInOut: 'easeInOut',

            // sine
            easeInSine: 'cubic-bezier(0.47, 0, 0.745, 0.715)',
            easeOutSine: 'cubic-bezier(0.39, 0.575, 0.565, 1)',
            easeInOutSine: 'cubic-bezier(0.445, 0.05, 0.55, 0.95)',

            // Quad
            easeInQuad: 'cubic-bezier(0.55, 0.085, 0.68, 0.53)',
            easeOutQuad: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
            easeInOutQuad: 'cubic-bezier(0.455, 0.03, 0.515, 0.955)',

            // Cubic
            easeInCubic: 'cubic-bezier(0.55, 0.055, 0.675, 0.19',
            easeOutCubic: 'cubic-bezier(0.215, 0.61, 0.355, 1)',
            easeInOutCubic: 'cubic-bezier(0.645, 0.045, 0.355, 1)',

            // Queart
            easeInQuart: 'cubic-bezier(0.895, 0.03, 0.685, 0.22)',
            easeOutQuart: 'cubic-bezier(0.165, 0.84, 0.44, 1)',
            easeInOutQuart: 'cubic-bezier(0.77, 0, 0.175, 1)',

            // Quint
            easeInQuint: 'cubic-bezier(0.755, 0.05, 0.855, 0.06)',
            easeOutQuint: 'cubic-bezier(0.23, 1, 0.32, 1',
            easeInOutQuint: 'cubic-bezier(0.86, 0, 0.07, 1)',

            // Expo
            easeInExpo: 'cubic-bezier(0.95, 0.05, 0.795, 0.035)',
            easeOutExpo: 'cubic-bezier(0.19, 1, 0.22, 1)',
            easeInOutExpo: 'cubic-bezier(1, 0, 0, 1)',

            // Circ
            easeInCirc: 'cubic-bezier(0.6, 0.04, 0.98, 0.335)',
            easeOutCirc: 'cubic-bezier(0.075, 0.82, 0.165, 1)',
            easeInOutCirc: 'cubic-bezier(0.785, 0.135, 0.15, 0.86)',

            // Back
            easeInBack: 'cubic-bezier(0.6, -0.28, 0.735, 0.045',
            easeOutBack: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            easeInBack: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',

        };

    }

    // reset the prototype
    JSHelper.prototype = {};

    /* Quick selctor all */
    JSHelper.prototype.$All = function(selector, context) {
        context = (typeof context === 'undefined' ? document : context);
        return Array.prototype.slice.call(context.querySelectorAll(selector));
    }

    /* Quck selector single */
    JSHelper.prototype.$ = function(selector, context) {
        context = (typeof context === 'undefined' ? document : context);
        return context.querySelector(selector)
    }

    /* Closest */
    JSHelper.prototype.closest = function(el, type) {
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
    JSHelper.prototype.parentUntillClass = function(el, clas) {
        if (this.hasClass(el, clas)) {
            return el;
        }
        if (this.hasClass(el.parentNode, clas)) {
            return el.parentNode;
        }
        var parent = el.parentNode;
        while (parent !== document.body) {
            parent = parent.parentNode;
            if (this.hasClass(parent, clas)) {
                return parent;
            }
        }
        return null;
    }

    /* Next untill type */
    JSHelper.prototype.nextUntillType = function(el, type) {
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

    /* New Node */
    JSHelper.prototype.newNode = function(type, classes, ID, content, target) {
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
    JSHelper.prototype.nodeExists = function(element) {
        if (typeof(element) !== "undefined" && element !== null) {
            if (typeof(element.parentNode) !== "undefined" && element.parentNode !== null) {
                return true;
            }
        }
        return false;
    }

    /* Remove From DOM */
    JSHelper.prototype.removeFromDOM = function(el) {
        if (this.nodeExists(el)) el.parentNode.removeChild(el);
    }

    /* Remove From DOM */
    JSHelper.prototype.removeStyle = function(el, prop) {

        prop = (typeof prop === 'undefined' ? 'style' : this.toCamelCase(prop));

        if (el.style.removeProperty) {
            el.style.removeProperty(prop);
        } else {
            el.style.removeAttribute(prop);
        }
    }

    /* Add class */
    JSHelper.prototype.addClass = function(el, className) {
        if (!this.nodeExists(el)) return;
        if (Object.prototype.toString.call(className) === '[object Array]') {
            for (var i = 0; i < className.length; i++) {
                el.classList.add(className[i]);
            }
            return;
        }
        el.classList.add(className);
    }

    /* Remove Class */
    JSHelper.prototype.removeClass = function(el, className) {
        if (!this.nodeExists(el)) return;
        if (Object.prototype.toString.call(className) === '[object Array]') {
            for (var i = 0; i < className.length; i++) {
                el.classList.remove(className[i]);
            }
            return;
        }
        el.classList.remove(className);
    }

    /* Toggle class */
    JSHelper.prototype.toggleClass = function(el, className) {
        if (!this.nodeExists(el)) return;
        if (this.hasClass(el, className)) {
            this.removeClass(el, className);
        } else {
            this.addClass(el, className);
        }
    }

    /* Has Class */
    JSHelper.prototype.hasClass = function(el, className) {
        if (!this.nodeExists(el)) return false;
        if (Object.prototype.toString.call(className) === '[object Array]') {
            for (var i = 0; i < className.length; i++) {
                if (el.classList.contains(className[i])) return true;
            }
            return false;
        }
        return el.classList.contains(className);
    }

    /* Is node type */
    JSHelper.prototype.isNodeType = function(el, NodeType) {
        return el.tagName.toUpperCase() === NodeType.toUpperCase();
    }

    /* Get Element Coordinates */
    JSHelper.prototype.getCoords = function(el) {
        var box = el.getBoundingClientRect();
        var body = document.body;
        var docEl = document.documentElement;
        var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
        var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;
        var clientTop = docEl.clientTop || body.clientTop || 0;
        var clientLeft = docEl.clientLeft || body.clientLeft || 0;
        var top = box.top + scrollTop - clientTop;
        var left = box.left + scrollLeft - clientLeft;
        var width = parseInt(getStyleVal(el, "width"));
        var height = parseInt(getStyleVal(el, "height"));
        return {
            top: Math.round(top),
            left: Math.round(left),
            right: Math.round(left + width),
            bottom: Math.round(top + height)
        }
    }

    /* Get style */
    JSHelper.prototype.getStyle = function(el, prop) {
        if (window.getComputedStyle) {
            return window.getComputedStyle(el, null).getPropertyValue(prop)
        } else {
            if (el.currentStyle) {
                return el.currentStyle[prop]
            }
        }
    }

    /* Trigger a native event */
    JSHelper.prototype.triggerEvent = function(el, type) {
        if ("createEvent" in document) {
            var evt = document.createEvent("HTMLEvents");
            evt.initEvent(type, false, true);
            el.dispatchEvent(evt)
        } else {
            el.fireEvent(type)
        }
    }

    /* Get all inputs from a form */
    JSHelper.prototype.getFormInputs = function(form) {
        var allInputs = $All('input, textarea, select', form);
        var i = allInputs.length;
        while (i--) {
            var input = allInputs[i];
            if (input.type == "radio" && input.checked !== true) allInputs.splice(i, 1);
        }

        return allInputs;
    }

    /* Get input value */
    JSHelper.prototype.getInputValue = function(input) {
        if (input.type == "checkbox") return input.checked;
        if (input.type == "select") return input.options[input.selectedIndex].value;
        return input.value;
    }

    JSHelper.prototype.css = function(el, property, value) {
        if (this.isset(this.cssEasings[value])) value = this.cssEasings[value];

        if (this.in_array(property, this.cssPrefixable)) {
            for (var i = 0; i < this.cssPrefixes.length; i++) {
                var prefix = this.cssPrefixes[i];
                var prop = this.toCamelCase(prefix + property);
                el.style[prop] = value;
            }
        } else {
            var prop = this.toCamelCase(property);
            el.style[prop] = value;
        }
    }

    /* Animate a CSS property */
    JSHelper.prototype.animate = function(el, cssProperty, from, to, time, easing) {

            /*
               Set defaults if values were not provided;
            */
            time = (typeof time === 'undefined' ? 300 : time);
            easing = (typeof easing === 'undefined' || !this.isset(this.cssEasings) ? 'ease-in' : this.cssEasings[easing]);

            /*
                Width and height need to use js to get the size
            */
            if ((cssProperty === 'height' || cssProperty === 'width') && from === 'auto') {
                if (cssProperty === 'height') {
                    from = el.clientHeight || el.offsetHeight;
                } else {
                    from = el.clientWidth || el.offsetWidth;
                }
            }

            /*
                Set the initial value
            */
            if (from !== 'initial') this.css(el, cssProperty, from);

            /*
                Set the css transition
            */
            this.css(el, 'transition', cssProperty + ' ' + time + 'ms ' + easing);

            /*
                Set the end property
            */
            this.css(el, cssProperty, to);

            /*
               Add an event listener to check when the transition has finished,
               remove any transition styles ans set the height to auto.
               Then remove the event listener
            */
            var _this = this;

            el.addEventListener('transitionend', function transitionEnd(event) {
                if (event.propertyName == cssProperty) {
                    _this.removeStyle(el, 'transition');
                    el.removeEventListener('transitionend', transitionEnd, false);
                }
            }, false);
        }
        /* Is JSON */
    JSHelper.prototype.isJSON = function(str) {
        var obj;
        try {
            obj = JSON.parse(str);
        } catch (e) {
            return false;
        }
        return obj;
    }

    /* Make random id */
    JSHelper.prototype.makeid = function(length) {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        for (var i = 0; i < length; i++) {
            text += possible.charAt(Math.floor(Math.random() * possible.length))
        }
        return text
    }

    /* Is numeric */
    JSHelper.prototype.is_numeric = function(mixed_var) {
        var whitespace =
            " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
        return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
            1)) && mixed_var !== '' && !isNaN(mixed_var);
    }

    /* parse a url */
    JSHelper.prototype.parse_url = function(str, component) {
        //       discuss at: http://phpjs.org/functions/parse_url/
        //      original by: Steven Levithan (http://blog.stevenlevithan.com)
        // reimplemented by: Brett Zamir (http://brett-zamir.me)
        //         input by: Lorenzo Pisani
        //         input by: Tony
        //      improved by: Brett Zamir (http://brett-zamir.me)
        //             note: original by http://stevenlevithan.com/demo/parseuri/js/assets/parseuri.js
        //             note: blog post at http://blog.stevenlevithan.com/archives/parseuri
        //             note: demo at http://stevenlevithan.com/demo/parseuri/js/assets/parseuri.js
        //             note: Does not replace invalid characters with '_' as in PHP, nor does it return false with
        //             note: a seriously malformed URL.
        //             note: Besides function name, is essentially the same as parseUri as well as our allowing
        //             note: an extra slash after the scheme/protocol (to allow file:/// as in PHP)
        //        example 1: parse_url('http://username:password@hostname/path?arg=value#anchor');
        //        returns 1: {scheme: 'http', host: 'hostname', user: 'username', pass: 'password', path: '/path', query: 'arg=value', fragment: 'anchor'}

        var query, key = ['source', 'scheme', 'authority', 'userInfo', 'user', 'pass', 'host', 'port',
                'relative', 'path', 'directory', 'file', 'query', 'fragment'
            ],
            ini = (this.php_js && this.php_js.ini) || {},
            mode = (ini['phpjs.parse_url.mode'] &&
                ini['phpjs.parse_url.mode'].local_value) || 'php',
            parser = {
                php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
            };

        var m = parser[mode].exec(str),
            uri = {},
            i = 14;
        while (i--) {
            if (m[i]) {
                uri[key[i]] = m[i];
            }
        }

        if (component) {
            return uri[component.replace('PHP_URL_', '')
                .toLowerCase()];
        }
        if (mode !== 'php') {
            var name = (ini['phpjs.parse_url.queryKey'] &&
                ini['phpjs.parse_url.queryKey'].local_value) || 'queryKey';
            parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
            uri[name] = {};
            query = uri[key[12]] || '';
            query.replace(parser, function($0, $1, $2) {
                if ($1) {
                    uri[name][$1] = $2;
                }
            });
        }
        delete uri.source;
        return uri;
    }

    /* Left trim */
    JSHelper.prototype.ltrim = function(str, charlist) {
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
    JSHelper.prototype.rtrim = function(str, charlist) {
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

    /* Trim */
    JSHelper.prototype.trim = function(str, charlist) {
        //  discuss at: http://phpjs.org/functions/trim/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: mdsjack (http://www.mdsjack.bo.it)
        // improved by: Alexander Ermolaev (http://snippets.dzone.com/user/AlexanderErmolaev)
        // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: Steven Levithan (http://blog.stevenlevithan.com)
        // improved by: Jack
        //    input by: Erkekjetter
        //    input by: DxGx
        // bugfixed by: Onno Marsman
        //   example 1: trim('    Kevin van Zonneveld    ');
        //   returns 1: 'Kevin van Zonneveld'
        //   example 2: trim('Hello World', 'Hdle');
        //   returns 2: 'o Wor'
        //   example 3: trim(16, 1);
        //   returns 3: 6

        var whitespace, l = 0,
            i = 0;
        str += '';

        if (!charlist) {
            // default list
            whitespace =
                ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
        } else {
            // preg_quote custom list
            charlist += '';
            whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
        }

        l = str.length;
        for (i = 0; i < l; i++) {
            if (whitespace.indexOf(str.charAt(i)) === -1) {
                str = str.substring(i);
                break;
            }
        }

        l = str.length;
        for (i = l - 1; i >= 0; i--) {
            if (whitespace.indexOf(str.charAt(i)) === -1) {
                str = str.substring(0, i + 1);
                break;
            }
        }

        return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
    }

    /* regex escape */
    JSHelper.prototype.preg_quote = function(str, delimiter) {
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
    JSHelper.prototype.str_replace = function(search, replace, subject, count) {
        count = typeof count === 'undefined' ? 1 : count;
        for (var i = 0; i < count; i++) {
            subject = subject.replace(search, replace);
        }
        return subject;
    }

    /* Preg match all */
    JSHelper.prototype.preg_match_all = function(pattern, subject) {

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
    JSHelper.prototype.str_split_index = function(value, index) {
        return [value.substring(0, index + 1), value.substring(index + 1)];
    }

    /* Capatalize first letter */
    JSHelper.prototype.ucfirst = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    /* Capatalize first letter of all words */
    JSHelper.prototype.ucwords = function(str) {
        //  discuss at: http://phpjs.org/functions/ucwords/
        // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
        // improved by: Waldo Malqui Silva
        // improved by: Robin
        // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // bugfixed by: Onno Marsman
        //    input by: James (http://www.james-bell.co.uk/)
        //   example 1: ucwords('kevin van  zonneveld');
        //   returns 1: 'Kevin Van  Zonneveld'
        //   example 2: ucwords('HELLO WORLD');
        //   returns 2: 'HELLO WORLD'

        return (str + '')
            .replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function($1) {
                return $1.toUpperCase();
            });
    }

    /* Reduce a string to a x words/letters with (optional) suffix */
    JSHelper.prototype.strReduce = function(string, length, suffix, toChar) {

        toChar = (typeof toChar === 'undefined' ? true : false);
        suffix = (typeof suffix === 'undefined' ? '' : suffix);

        if (toChar) return (string.length > length) ? string.substring(0, length) + suffix : string;

        var words = string.split(" ");

        if (count(words) > length) return fruits.slice(0, length).join(' ').suffix;

        return string;

    }

    /* Return human friendly time-ago */
    JSHelper.prototype.timeAgo = function(time, asArray) {
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

    /* Convert a string-date to a timestamp */
    JSHelper.prototype.strtotime = function(text) {
        return Math.round(new Date(text).getTime() / 1000);
    }

    /* String replace */
    JSHelper.prototype.str_replace = function(search, replace, subject, count) {
        //  discuss at: http://phpjs.org/functions/str_replace/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: Gabriel Paderni
        // improved by: Philip Peterson
        // improved by: Simon Willison (http://simonwillison.net)
        // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: Onno Marsman
        // improved by: Brett Zamir (http://brett-zamir.me)
        //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
        // bugfixed by: Anton Ongson
        // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // bugfixed by: Oleg Eremeev
        // bugfixed by: Glen Arason (http://CanadianDomainRegistry.ca)
        // bugfixed by: Glen Arason (http://CanadianDomainRegistry.ca) Corrected count
        //    input by: Onno Marsman
        //    input by: Brett Zamir (http://brett-zamir.me)
        //    input by: Oleg Eremeev
        //        note: The count parameter must be passed as a string in order
        //        note: to find a global variable in which the result will be given
        //   example 1: str_replace(' ', '.', 'Kevin van Zonneveld');
        //   returns 1: 'Kevin.van.Zonneveld'
        //   example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name}, lars');
        //   returns 2: 'hemmo, mars'
        //   example 3: str_replace(Array('S','F'),'x','ASDFASDF');
        //   returns 3: 'AxDxAxDx'
        //   example 4: str_replace(['A','D'], ['x','y'] , 'ASDFASDF' , 'cnt');
        //   returns 4: 'xSyFxSyF' // cnt = 0 (incorrect before fix)
        //   returns 4: 'xSyFxSyF' // cnt = 4 (correct after fix)

        var i = 0,
            j = 0,
            temp = '',
            repl = '',
            sl = 0,
            fl = 0,
            f = [].concat(search),
            r = [].concat(replace),
            s = subject,
            ra = Object.prototype.toString.call(r) === '[object Array]',
            sa = Object.prototype.toString.call(s) === '[object Array]';
        s = [].concat(s);

        if (typeof(search) === 'object' && typeof(replace) === 'string') {
            temp = replace;
            replace = new Array();
            for (i = 0; i < search.length; i += 1) {
                replace[i] = temp;
            }
            temp = '';
            r = [].concat(replace);
            ra = Object.prototype.toString.call(r) === '[object Array]';
        }

        if (count) {
            this.window[count] = 0;
        }

        for (i = 0, sl = s.length; i < sl; i++) {
            if (s[i] === '') {
                continue;
            }
            for (j = 0, fl = f.length; j < fl; j++) {
                temp = s[i] + '';
                repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
                s[i] = (temp)
                    .split(f[j])
                    .join(repl);
                if (count) {
                    this.window[count] += ((temp.split(f[j]))
                        .length - 1);
                }
            }
        }
        return sa ? s : s[0];
    }

    JSHelper.prototype.str_split = function(string, split_length) {
        //  discuss at: http://phpjs.org/functions/str_split/
        // original by: Martijn Wieringa
        // improved by: Brett Zamir (http://brett-zamir.me)
        // bugfixed by: Onno Marsman
        //  revised by: Theriault
        //  revised by: Rafał Kukawski (http://blog.kukawski.pl/)
        //    input by: Bjorn Roesbeke (http://www.bjornroesbeke.be/)
        //   example 1: str_split('Hello Friend', 3);
        //   returns 1: ['Hel', 'lo ', 'Fri', 'end']

        if (split_length === null) {
            split_length = 1;
        }
        if (string === null || split_length < 1) {
            return false;
        }
        string += '';
        var chunks = [],
            pos = 0,
            len = string.length;
        while (pos < len) {
            chunks.push(string.slice(pos, pos += split_length));
        }

        return chunks;
    }

    JSHelper.prototype.toCamelCase = function(str) {
        return str.toLowerCase()
            .replace(/['"]/g, '')
            .replace(/\W+/g, ' ')
            .replace(/ (.)/g, function($1) {
                return $1.toUpperCase();
            })
            .replace(/ /g, '');
    }


    JSHelper.prototype.explode = function(delimiter, string, limit) {
            //  discuss at: http://phpjs.org/functions/explode/
            // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            //   example 1: explode(' ', 'Kevin van Zonneveld');
            //   returns 1: {0: 'Kevin', 1: 'van', 2: 'Zonneveld'}

            if (arguments.length < 2 || typeof delimiter === 'undefined' || typeof string === 'undefined') return null;
            if (delimiter === '' || delimiter === false || delimiter === null) return false;
            if (typeof delimiter === 'function' || typeof delimiter === 'object' || typeof string === 'function' || typeof string ===
                'object') {
                return {
                    0: ''
                };
            }
            if (delimiter === true) delimiter = '1';

            // Here we go...
            delimiter += '';
            string += '';

            var s = string.split(delimiter);

            if (typeof limit === 'undefined') return s;

            // Support for limit
            if (limit === 0) limit = 1;

            // Positive limit
            if (limit > 0) {
                if (limit >= s.length) return s;
                return s.slice(0, limit - 1)
                    .concat([s.slice(limit - 1)
                        .join(delimiter)
                    ]);
            }

            // Negative limit
            if (-limit >= s.length) return [];

            s.splice(s.length + limit);
            return s;
        }
        /* In array */
    JSHelper.prototype.in_array = function(needle, haystack, argStrict) {

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
    JSHelper.prototype.clean_inner_html = function(array) {
        return array.join('');
    }

    JSHelper.prototype.array_reduce = function(array, count) {
        return this.array_slice(array, 0, count);
    }

    JSHelper.prototype.implode = function(array, prefix, suffix) {
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

    JSHelper.prototype.array_slice = function(arr, offst, lgth, preserve_keys) {
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

    JSHelper.prototype.paginate = function(array, page, limit) {
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

    JSHelper.prototype.foreach = function(obj, callback, args) {
        var value, i = 0,
            length = obj.length,
            isArray = Object.prototype.toString.call(obj) === '[object Array]';

        if (args) {
            if (isArray) {
                for (; i < length; i++) {
                    value = callback.apply(obj[i], args);

                    if (value === false) {
                        break;
                    }
                }
            } else {
                for (i in obj) {
                    value = callback.apply(obj[i], args);

                    if (value === false) {
                        break;
                    }
                }
            }

            // A special, fast, case for the most common use of each
        } else {
            if (isArray) {
                for (; i < length; i++) {
                    value = callback.call(obj[i], i, obj[i]);

                    if (value === false) {
                        break;
                    }
                }
            } else {
                for (i in obj) {
                    value = callback.call(obj[i], i, obj[i]);

                    if (value === false) {
                        break;
                    }
                }
            }
        }

        return obj;
    }

    /* Clone an object */
    JSHelper.prototype.cloneObj = function(src) {
        var clone = {};
        for (var prop in src) {
            if (src.hasOwnProperty(prop)) clone[prop] = src[prop];
        }
        return clone;
    }

    JSHelper.prototype.implode = function(array, prefix, suffix) {
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

    /* Is numberic */
    JSHelper.prototype.is_numeric = function(mixed_var) {
        var whitespace =
            " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
        return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) === -
            1)) && mixed_var !== '' && !isNaN(mixed_var);
    }

    /* Is callback */
    JSHelper.prototype.isCallable = function(obj) {
        return Object.prototype.toString.call(obj) === '[object Function]';
    }

    /* Count an object or string */
    JSHelper.prototype.count = function(mixed_var, mode) {
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

    /* Bool */
    JSHelper.prototype.bool = function(value) {

        value = (typeof value === 'undefined' ? false : value);

        if (typeof value === 'boolean') return value;

        if (typeof value === 'number') return value > 0;

        if (typeof value === 'string') {
            if (value.toLowerCase() === 'false') return false;
            if (value.toLowerCase() === 'true') return true;
            if (value.toLowerCase() === 'on') return true;
            if (value.toLowerCase() === 'off') return false;
            if (value.toLowerCase() === 'undefined') return false;
            if (this.is_numeric(value)) return Number(value) > 0;
            if (value === '') return false;
        }

        return false;
    }

    JSHelper.prototype.intval = function(mixed_var, base) {
        //  discuss at: http://phpjs.org/functions/intval/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: stensi
        // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // bugfixed by: Brett Zamir (http://brett-zamir.me)
        // bugfixed by: Rafał Kukawski (http://kukawski.pl)
        //    input by: Matteo
        //   example 1: intval('Kevin van Zonneveld');
        //   returns 1: 0
        //   example 2: intval(4.2);
        //   returns 2: 4
        //   example 3: intval(42, 8);
        //   returns 3: 42
        //   example 4: intval('09');
        //   returns 4: 9
        //   example 5: intval('1e', 16);
        //   returns 5: 30

        var tmp;

        var type = typeof mixed_var;

        if (type === 'boolean') {
            return +mixed_var;
        } else if (type === 'string') {
            tmp = parseInt(mixed_var, base || 10);
            return (isNaN(tmp) || !isFinite(tmp)) ? 0 : tmp;
        } else if (type === 'number' && isFinite(mixed_var)) {
            return mixed_var | 0;
        } else {
            return 0;
        }
    }

    /* Isset */
    JSHelper.prototype.isset = function() {
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

    /* Empty */
    JSHelper.prototype.empty = function(value) {

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

    JSHelper.prototype.is_object = function(mixed_var) {
        //  discuss at: http://phpjs.org/functions/is_object/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: Legaev Andrey
        // improved by: Michael White (http://getsprink.com)
        //   example 1: is_object('23');
        //   returns 1: false
        //   example 2: is_object({foo: 'bar'});
        //   returns 2: true
        //   example 3: is_object(null);
        //   returns 3: false

        if (Object.prototype.toString.call(mixed_var) === '[object Array]') {
            return false;
        }
        return mixed_var !== null && typeof mixed_var === 'object';
    }

    JSHelper.prototype.isNodeList = function(nodes) {
        return nodes == '[object NodeList]';
    }


    // Initialize a local instance
    var JSHelperInstance = new JSHelper();

    // Set the global instance to a single local one
    if (!window.Helper) window.Helper = JSHelperInstance;

})(window);
