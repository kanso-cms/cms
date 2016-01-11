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
    if (nodeExists(el)) el.classList.add(className);
}

/* Remove Class */
function removeClass(el, className) {
    if (nodeExists(el)) el.classList.remove(className);
}

/* Has Class */
function hasClass(el, className) {
    if (nodeExists(el)) return el.classList.contains(className);
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
        typeof exception === 'undefined' ? a.classList.add(className) : a.classList[a == exception ?  'remove' : 'add'](className);

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
    if (input.type == "select")   return input.options[input.selectedIndex].value;
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
    strict = !! argStrict;

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
        }
        catch(err) {
            return null;
        }
    }

    var matches = [];
    var matched = pattern.exec(subject);
    if (matched !== null) {
        var i = 0;
        while (matched = pattern.exec(subject)) {
            subject = str_split_index(subject, (matched.index + matched[0].length-1))[1];
            matched.index = i > 0 ? (matched.index + (matched[0].length-1)) : matched.index-1;
            matches.push(matched);
            i++;
        }
        return matches;
    }
    return null;
}

/* split string at index */
function str_split_index(value, index) {
    return [value.substring(0, index+1), value.substring(index+1)];
}

/* closest Number */
function closest_number(numbers, target) {
    var curr = numbers[0];
    for (var i = 0; i < numbers.length; i++) {
        var val = numbers[i];
        if ( Math.abs(target - val) < Math.abs(target - curr) ) curr = val;
    }
    return curr
}

/* Get the css path of an element */
function cssPath(el) {

  var names = [];
  while (el.parentNode){
    if (el.id){
      names.unshift('#'+el.id);
      break;
    }else{
      if (el==el.ownerDocument.documentElement) names.unshift(el.tagName);
      else{
        for (var c=1,e=el;e.previousElementSibling;e=e.previousElementSibling,c++);
        names.unshift(el.tagName+":nth-child("+c+")");
      }
      el=el.parentNode;
    }
  }
  return names.join(" > ");

}

/* Get first level child nodes */
function children(el) {
  var cass_path = cssPath(el);

  if ($(cass_path) === el) {

    return $All(cass_path + ' > *');

  }

  return false;
}

function empty(mixed_var) {
  //  discuss at: http://phpjs.org/functions/empty/
  // original by: Philippe Baumann
  //    input by: Onno Marsman
  //    input by: LH
  //    input by: Stoyan Kyosev (http://www.svest.org/)
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Onno Marsman
  // improved by: Francesco
  // improved by: Marc Jansen
  // improved by: Rafal Kukawski
  //   example 1: empty(null);
  //   returns 1: true
  //   example 2: empty(undefined);
  //   returns 2: true
  //   example 3: empty([]);
  //   returns 3: true
  //   example 4: empty({});
  //   returns 4: true
  //   example 5: empty({'aFunc' : function () { alert('humpty'); } });
  //   returns 5: false

  var undef, key, i, len;
  var emptyValues = [undef, null, false, 0, '', '0'];

  for (i = 0, len = emptyValues.length; i < len; i++) {
    if (mixed_var === emptyValues[i]) {
      return true;
    }
  }

  if (typeof mixed_var === 'object') {
    for (key in mixed_var) {
      // TODO: should we check for own properties only?
      //if (mixed_var.hasOwnProperty(key)) {
      return false;
      //}
    }
    return true;
  }

  return false;
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


/**
 * SimpleAjax
 *
 * @fileOverview
 *    Cross browser ajax object for creating ajax calls.
 *    Released under MIT license.
 * @version 1.0.1
 * @author Victor Villaverde Laan
 * @link http://www.freelancephp.net/simpleajax-small-ajax-javascript-object/
 * @link https://github.com/freelancephp/SimpleAjax
 */
(function (window) {

/**
 * @namespace SimpleAjax
 */
var SimpleAjax = window.SimpleAjax = {

	/**
	 * @property {XMLHttpRequest|ActiveXObject}
	 */
	xhr: null,

	/**
	 * @property {Object} Default ajax settings
	 */
	settings: {
		url: '',
		type: 'GET',
		dataType: 'text', // text, html, json or xml
		async: true,
		cache: true,
		data: null,
		contentType: 'application/x-www-form-urlencoded',
		success: null,
		error: null,
		complete: null,
		accepts: {
			text: 'text/plain',
			html: 'text/html',
			xml: 'application/xml, text/xml',
			json: 'application/json, text/javascript'
		}
	},

	/**
	 * Ajax call
	 * @param {Object} [options] Overwrite the default settings (see ajaxSettings)
	 * @return {This}
	 */
	call: function (options) {
		var self = this,
			xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP'),
			opts = (function (s, o) {
				var opts = {};

				for (var key in s)
					opts[key] = (typeof o[key] == 'undefined') ? s[key] : o[key];

				return opts;
			})(this.settings, options),
			ready = function () {
				if(xhr.readyState == 4){
					if (xhr.status >= 200 && xhr.status < 300 || xhr.status === 304) {
						// set data
						var data = (opts.dataType == 'xml') ? xhr.responseXML : xhr.responseText;

						// parse json data
						if (opts.dataType == 'json')
							data = self.parseJSON(data);

						// success callback
						if (self.isFunction(opts.success))
							opts.success.call(opts, data, xhr.status, xhr);
					} else {
						// error callback
						if (self.isFunction(opts.error))
							opts.error.call(opts, xhr, xhr.status);
					}

					// complete callback
					if (self.isFunction(opts.complete))
						opts.complete.call(opts, xhr, xhr.status);
				}
			};

		this.xhr = xhr;

		// prepare options
		if (!opts.cache)
			opts.url += ((opts.url.indexOf('?') > -1) ? '&' : '?') + '_nocache='+ (new Date()).getTime();

		if (opts.data) {
			if (opts.type == 'GET') {
				opts.url += ((opts.url.indexOf('?') > -1) ? '&' : '?') + this.param(opts.data);
				opts.data = null;
			} else {
				opts.data = this.param(opts.data);
			}
		}

		// set request
		xhr.open(opts.type, opts.url, opts.async);
		xhr.setRequestHeader('Content-type', opts.contentType);
		xhr.setRequestHeader('X_REQUESTED_WITH', 'XMLHttpRequest');

		if (opts.dataType && opts.accepts[opts.dataType])
			xhr.setRequestHeader('Accept', opts.accepts[opts.dataType]);

		if (opts.async) {
			xhr.onreadystatechange = ready;
			xhr.send(opts.data);
		} else {
			xhr.send(opts.data);
			ready();
		}

		return this;
	},

	/**
	 * Ajax GET request
	 * @param {String} url
	 * @param {String|Object} [data] Containing GET values
	 * @param {Function} [success] Callback when request was succesfull
	 * @return {This}
	 */
	get: function (url, data, success) {
		if (this.isFunction(data)) {
			success = data;
			data = null;
		}

		return this.call({
			url: url,
			type: 'GET',
			data: data,
			success: success
		});
	},

	/**
	 * Ajax POST request
	 * @param {String} url
	 * @param {String|Object} [data] Containing POST values
	 * @param {Function} [success] Callback when request was succesfull
	 * @return {This}
	 */
	post: function (url, data, success, error) {
		if (this.isFunction(data)) {
			success = data;
			data = null;
		}

		return this.call({
			url: url,
			type: 'POST',
			data: data,
			success: success,
			error: error
		});
	},

	/**
	 * Set content loaded by an ajax call
	 * @param {DOMElement|String} el Can contain an element or the id of the element
	 * @param {String} url The url of the ajax call (include GET vars in querystring)
	 * @param {String} [data] The POST data, when set method will be set to POST
	 * @param {Function} [complete] Callback when loading is completed
	 * @return {This}
	 */
	load: function (el, url, data, complete) {
		if (typeof el == 'string')
			el = document.getElementById(el);

		return this.call({
			url: url,
			type: data ? 'POST' : 'GET',
			data: data || null,
			complete: complete || null,
			success: function (html) {
				try {
					el.innerHTML = html;
				} catch (e) {
					var ph = document.createElement('div');
					ph.innerHTML = html;

					// empty element content
					while (el.firstChild)
						el.removeChild(el.firstChild);

					// set new html content
					for(var x = 0, max = ph.childNodes.length; x < max; x++)
						el.appendChild(ph.childNodes[x]);
				}
			}
		});
	},

	/**
	 * Make querystring outof object or array of values
	 * @param {Object|Array} obj Keys/values
	 * @return {String} The querystring
	 */
	param: function (obj) {
		var s = [];

		for (var key in obj) {
			s.push(encodeURIComponent(key) +'='+ encodeURIComponent(obj[key]));
		}

		return s.join('&');
	},

	/**
	 * Parse JSON string
	 * @param {String} data
	 * @return {Object} JSON object
	 */
	parseJSON: function (data) {
		if (typeof data !== 'string' || !data)
			return null;

		return eval('('+ this.trim(data) +')');
	},

	/**
	 * Trim spaces
	 * @param {String} str
	 * @return {String}
	 */
	trim: function (str) {
		return str.replace(/^\s+/, '').replace(/\s+$/, '');
	},

	/**
	 * Check if argument is function
	 * @param {Mixed} obj
	 * @return {Boolean}
	 */
	isFunction: function (obj) {
		return Object.prototype.toString.call(obj) === '[object Function]';
	}

};

if (!window.Ajax) {
	/**
	 * Alias for SimpleAjax
	 * @namespace Ajax
	 */
	window.Ajax = SimpleAjax;
}

})(window);


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
/*! markdown-it 4.2.1 https://github.com//markdown-it/markdown-it @license MIT */ ! function(e) {
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
/*************************************************************
** SNTAX HIGHLIGHTING
**************************************************************/
(function() {
	
	var codeEls = $All('pre > code');
	
	if (nodeExists($('pre > code'))) {
		for (var i = 0; i < codeEls.length; i++) {
			hljs.highlightBlock(codeEls[i]);
		}
	}

}());
/*************************************************************
** COMMENTS
**************************************************************/
(function() {

	var COMMENTS_AJAX_URL     = window.location.origin+'/comments/';

	/* New comment initializer */
	var commentSubmit = $('form.comment-form button[type="submit"]');

	if (nodeExists(commentSubmit)) {
		commentSubmit.addEventListener("click", function(e) {
			e.preventDefault();
			submitComment(commentSubmit);
		});
	}

	/* Submit comment event */
	function submitComment(trigger)
	{
		
		if (hasClass(trigger, 'active')) return;
		
		// Build form object
		var formObj = {};
		var form 	= parentUntillClass(trigger, 'comment-form');
		var inputs = $All('input, textarea', form);
		for (var i = 0; i < inputs.length; i++) {
			if (inputs[i].type === 'checkbox') {
				var checked = inputs[i].checked ? 'true' : 'false';
				formObj[inputs[i].name] = checked;
			}
			else {
				formObj[inputs[i].name] = inputs[i].value;
			}
			
		}

		// Remove error classes
		var inputWraps = $All('.input-wrap', form);
		for (var j = 0; j < inputWraps.length; j++) {
			removeClass(inputWraps[j], 'error');
		}
		removeClass(form, 'error');
		removeClass(form, 'success');
		$('.form-result', form).innerHTML = '';


		// Validate fields
		if (!validateEmpty(formObj.name)) {
			addClass($('.comment-form-name', form), 'error');
			return;
		}
		if (!validatePlainText(formObj.name)) {
			addClass($('.comment-form-name', form), 'error');
			return;
		}
		if (!validateEmpty(formObj.email)) {
			addClass($('.comment-form-email', form), 'error');
			return;
		}
		if (!validateEmail(formObj.email)) {
			addClass($('.comment-form-email', form), 'error');
			return;
		}
		if (!validateEmpty(formObj.content)) {
			addClass($('.comment-form-content', form), 'error');
			return;
		}

		addClass(form, 'active');
		addClass(trigger, 'active');

		// Ajax post comment
		Ajax.post(COMMENTS_AJAX_URL, formObj, 
			function(success) {
				console.log(success);

				removeClass(form, 'active');
				removeClass(trigger, 'active');

				var responseObj = isJSON(success);
				if (responseObj && responseObj.details === 'valid') {
					insertComment(formObj, form);
					showFormResult('success', 'Your comment was successfully posted.', form, inputs);
				}
				else if (responseObj && responseObj.details === 'spam') {
					showFormResult('error', 'Your comment has been marked as spam.', form, inputs);
				}
				else if (responseObj && responseObj.details === 'pending') {
					showFormResult('error', 'Your comment was posted but is pending approval from the site moderator.', form, inputs);
				}
				else {
					showFormResult('error', 'There was en error posting your comment. Please try again later.', form, inputs);
				}
			},	
			function(error) {
				removeClass(form, 'active');
				removeClass(trigger, 'active');
				showFormResult('error', 'There was en error posting your comment. Please try again later.', form, inputs);
				console.log(error);
			}
		);

	}

	/* Show the form result */
	function showFormResult(className, msg, form, inputs) 
	{
		addClass(form, className);
		$('.form-result', form).innerHTML = '<p>'+msg+'</p>';
		for (var i = 0; i < inputs.length; i++) {
			input[i].value = '';
		}
	}

	/* Insert new comment into DOM */
	function insertComment(formObj, formEl) 
	{
		var formParent = formEl.parentNode;
	
		var markDown = window.markdownit({
			html: true,
			xhtmlOut: false,
			breaks: true,
			langPrefix: '',
			linkify: false,
		});

		var HTTP_PROTOCAL = window.location.href.split(":")[0];
		var months 		  = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

		var content = markDown.render(formObj.content);
		var avatar  = HTTP_PROTOCAL+'://www.gravatar.com/avatar/'+md5(formObj.email)+'?s=40&d=mm';
		var d = new Date();
		var date = months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();

		var commentHTML = '<div class="comment-author-wrap"> <div class="comment-avatar-wrap"> <img alt="" src="'+avatar+'" class="comment-avatar-img" width="40" height="40"> </div><p class="comment-author-name">'+formObj.name+'</p></div><div class="comment-comment-body"> <div class="comment-comment-content"> '+content+' </div></div><div class="comment-comment-footer"> <time class="comment-comment-time" datetime="">'+date+'</time> • <a class="comment-comment-link" href="#">#</a></div><div class="comment-comment-chidren comment-no-children"> </div>';

        if (hasClass(formParent, 'comment-comment-chidren')) removeFromDOM(formEl);
		newNode('div', 'comment-comment-wrap', null, commentHTML, formParent);

	}


	/* Comment reply click initailizer */
	var replyClickers = $All('.comment-reply-link');

	if (nodeExists($('.comment-reply-link'))) initReplyClickers();

	function initReplyClickers() {
		for (var i = 0; i < replyClickers.length; i++) {
			replyClickers[i].addEventListener("click", clickReply);
		}
	}

	/* Click reply event */
	function clickReply() {

		var clicked = event.target;
		
		event.preventDefault();

		var clickedComment = parentUntillClass(clicked, 'comment-comment-wrap');
		var childrenWrap   = $('.comment-comment-chidren', clickedComment);

		var childrenWrapChilds = children(childrenWrap);
		if (!empty(childrenWrapChilds)) {
			for (var i = 0; i < childrenWrapChilds.length; i++) {
				if (hasClass(childrenWrapChilds[i], 'comment-form')) return;
			}
		}

		var formEl = $('.comments-wrap .comment-form').cloneNode(true);
		$('input[name="replyID"]', formEl).value = clickedComment.dataset.commentId;

		var inputs = $All('input', formEl);

		for (var j = 0; j < inputs.length; j++) {
			inputs[j].value = '';
		}

		if (hasClass(childrenWrap, 'comment-no-children')) {
			childrenWrap.appendChild(formEl);
			removeClass(childrenWrap, 'comment-no-children');
		}
		else {
			childrenWrap.insertBefore(formEl, childrenWrap.childNodes[0]);
		}

		var commentSubmit = $('button[type="submit"]', formEl);
		commentSubmit.addEventListener("click", function(e) {
			e.preventDefault();
			submitComment(commentSubmit);
		});
	}

	/* Field validation helpers */
	function validateEmpty(value) {
		value = value.trim();
		var re = /^\s*$/;
		return re.test(value) ? false : true;
	}

	function validateEmail(value) {
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(value);
	}

	function validatePlainText(value) {
		var re = /^[A-z _-]+$/;
		return re.test(value);
	}

	/*-------------------------------------------------------------
	**  md5
	--------------------------------------------------------------*/
	//  discuss at: http://phpjs.org/functions/md5/
	// original by: Webtoolkit.info (http://www.webtoolkit.info/)
	// improved by: Michael White (http://getsprink.com)
	// improved by: Jack
	// improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	//    input by: Brett Zamir (http://brett-zamir.me)
	// bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	//  depends on: utf8_encode
	//   example 1: md5('Kevin van Zonneveld');
	//   returns 1: '6e658d4bfcb59cc13f96c14450ac40b9'

	function md5(r){var n,t,e,o,u,f,i,a,c,l,d=function(r,n){return r<<n|r>>>32-n},h=function(r,n){var t,e,o,u,f;return o=2147483648&r,u=2147483648&n,t=1073741824&r,e=1073741824&n,f=(1073741823&r)+(1073741823&n),t&e?2147483648^f^o^u:t|e?1073741824&f?3221225472^f^o^u:1073741824^f^o^u:f^o^u},g=function(r,n,t){return r&n|~r&t},s=function(r,n,t){return r&t|n&~t},C=function(r,n,t){return r^n^t},v=function(r,n,t){return n^(r|~t)},m=function(r,n,t,e,o,u,f){return r=h(r,h(h(g(n,t,e),o),f)),h(d(r,u),n)},w=function(r,n,t,e,o,u,f){return r=h(r,h(h(s(n,t,e),o),f)),h(d(r,u),n)},A=function(r,n,t,e,o,u,f){return r=h(r,h(h(C(n,t,e),o),f)),h(d(r,u),n)},S=function(r,n,t,e,o,u,f){return r=h(r,h(h(v(n,t,e),o),f)),h(d(r,u),n)},y=function(r){for(var n,t=r.length,e=t+8,o=(e-e%64)/64,u=16*(o+1),f=new Array(u-1),i=0,a=0;t>a;)n=(a-a%4)/4,i=a%4*8,f[n]=f[n]|r.charCodeAt(a)<<i,a++;return n=(a-a%4)/4,i=a%4*8,f[n]=f[n]|128<<i,f[u-2]=t<<3,f[u-1]=t>>>29,f},E=function(r){var n,t,e="",o="";for(t=0;3>=t;t++)n=r>>>8*t&255,o="0"+n.toString(16),e+=o.substr(o.length-2,2);return e},R=[],U=7,_=12,b=17,p=22,L=5,j=9,k=14,q=20,x=4,z=11,B=16,D=23,F=6,G=10,H=15,I=21;for(r=utf8_encode(r),R=y(r),i=1732584193,a=4023233417,c=2562383102,l=271733878,n=R.length,t=0;n>t;t+=16)e=i,o=a,u=c,f=l,i=m(i,a,c,l,R[t+0],U,3614090360),l=m(l,i,a,c,R[t+1],_,3905402710),c=m(c,l,i,a,R[t+2],b,606105819),a=m(a,c,l,i,R[t+3],p,3250441966),i=m(i,a,c,l,R[t+4],U,4118548399),l=m(l,i,a,c,R[t+5],_,1200080426),c=m(c,l,i,a,R[t+6],b,2821735955),a=m(a,c,l,i,R[t+7],p,4249261313),i=m(i,a,c,l,R[t+8],U,1770035416),l=m(l,i,a,c,R[t+9],_,2336552879),c=m(c,l,i,a,R[t+10],b,4294925233),a=m(a,c,l,i,R[t+11],p,2304563134),i=m(i,a,c,l,R[t+12],U,1804603682),l=m(l,i,a,c,R[t+13],_,4254626195),c=m(c,l,i,a,R[t+14],b,2792965006),a=m(a,c,l,i,R[t+15],p,1236535329),i=w(i,a,c,l,R[t+1],L,4129170786),l=w(l,i,a,c,R[t+6],j,3225465664),c=w(c,l,i,a,R[t+11],k,643717713),a=w(a,c,l,i,R[t+0],q,3921069994),i=w(i,a,c,l,R[t+5],L,3593408605),l=w(l,i,a,c,R[t+10],j,38016083),c=w(c,l,i,a,R[t+15],k,3634488961),a=w(a,c,l,i,R[t+4],q,3889429448),i=w(i,a,c,l,R[t+9],L,568446438),l=w(l,i,a,c,R[t+14],j,3275163606),c=w(c,l,i,a,R[t+3],k,4107603335),a=w(a,c,l,i,R[t+8],q,1163531501),i=w(i,a,c,l,R[t+13],L,2850285829),l=w(l,i,a,c,R[t+2],j,4243563512),c=w(c,l,i,a,R[t+7],k,1735328473),a=w(a,c,l,i,R[t+12],q,2368359562),i=A(i,a,c,l,R[t+5],x,4294588738),l=A(l,i,a,c,R[t+8],z,2272392833),c=A(c,l,i,a,R[t+11],B,1839030562),a=A(a,c,l,i,R[t+14],D,4259657740),i=A(i,a,c,l,R[t+1],x,2763975236),l=A(l,i,a,c,R[t+4],z,1272893353),c=A(c,l,i,a,R[t+7],B,4139469664),a=A(a,c,l,i,R[t+10],D,3200236656),i=A(i,a,c,l,R[t+13],x,681279174),l=A(l,i,a,c,R[t+0],z,3936430074),c=A(c,l,i,a,R[t+3],B,3572445317),a=A(a,c,l,i,R[t+6],D,76029189),i=A(i,a,c,l,R[t+9],x,3654602809),l=A(l,i,a,c,R[t+12],z,3873151461),c=A(c,l,i,a,R[t+15],B,530742520),a=A(a,c,l,i,R[t+2],D,3299628645),i=S(i,a,c,l,R[t+0],F,4096336452),l=S(l,i,a,c,R[t+7],G,1126891415),c=S(c,l,i,a,R[t+14],H,2878612391),a=S(a,c,l,i,R[t+5],I,4237533241),i=S(i,a,c,l,R[t+12],F,1700485571),l=S(l,i,a,c,R[t+3],G,2399980690),c=S(c,l,i,a,R[t+10],H,4293915773),a=S(a,c,l,i,R[t+1],I,2240044497),i=S(i,a,c,l,R[t+8],F,1873313359),l=S(l,i,a,c,R[t+15],G,4264355552),c=S(c,l,i,a,R[t+6],H,2734768916),a=S(a,c,l,i,R[t+13],I,1309151649),i=S(i,a,c,l,R[t+4],F,4149444226),l=S(l,i,a,c,R[t+11],G,3174756917),c=S(c,l,i,a,R[t+2],H,718787259),a=S(a,c,l,i,R[t+9],I,3951481745),i=h(i,e),a=h(a,o),c=h(c,u),l=h(l,f);var J=E(i)+E(a)+E(c)+E(l);return J.toLowerCase()}function utf8_encode(r){if(null===r||"undefined"==typeof r)return"";var n,t,e=r+"",o="",u=0;n=t=0,u=e.length;for(var f=0;u>f;f++){var i=e.charCodeAt(f),a=null;if(128>i)t++;else if(i>127&&2048>i)a=String.fromCharCode(i>>6|192,63&i|128);else if(55296!=(63488&i))a=String.fromCharCode(i>>12|224,i>>6&63|128,63&i|128);else{if(55296!=(64512&i))throw new RangeError("Unmatched trail surrogate at "+f);var c=e.charCodeAt(++f);if(56320!=(64512&c))throw new RangeError("Unmatched lead surrogate at "+(f-1));i=((1023&i)<<10)+(1023&c)+65536,a=String.fromCharCode(i>>18|240,i>>12&63|128,i>>6&63|128,63&i|128)}null!==a&&(t>n&&(o+=e.slice(n,t)),o+=a,n=t=f+1)}return t>n&&(o+=e.slice(n,u)),o}


}());

/*
smooth-scroll
https://github.com/cferdinandi/smooth-scroll

Copyright (c) Go Make Things, LLC

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/
!function(e,t){"function"==typeof define&&define.amd?define([],t(e)):"object"==typeof exports?module.exports=t(e):e.smoothScroll=t(e)}("undefined"!=typeof global?global:this.window||this.global,function(e){"use strict";var t,n,o,r,a={},u="querySelector"in document&&"addEventListener"in e,c={selector:"[data-scroll]",selectorHeader:"[data-scroll-header]",speed:500,easing:"easeInOutCubic",offset:0,updateURL:!0,callback:function(){}},i=function(){var e={},t=!1,n=0,o=arguments.length;"[object Boolean]"===Object.prototype.toString.call(arguments[0])&&(t=arguments[0],n++);for(var r=function(n){for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(t&&"[object Object]"===Object.prototype.toString.call(n[o])?e[o]=i(!0,e[o],n[o]):e[o]=n[o])};o>n;n++){var a=arguments[n];r(a)}return e},s=function(e){return Math.max(e.scrollHeight,e.offsetHeight,e.clientHeight)},l=function(e,t){var n,o,r=t.charAt(0),a="classList"in document.documentElement;for("["===r&&(t=t.substr(1,t.length-2),n=t.split("="),n.length>1&&(o=!0,n[1]=n[1].replace(/"/g,"").replace(/'/g,"")));e&&e!==document;e=e.parentNode){if("."===r)if(a){if(e.classList.contains(t.substr(1)))return e}else if(new RegExp("(^|\\s)"+t.substr(1)+"(\\s|$)").test(e.className))return e;if("#"===r&&e.id===t.substr(1))return e;if("["===r&&e.hasAttribute(n[0])){if(!o)return e;if(e.getAttribute(n[0])===n[1])return e}if(e.tagName.toLowerCase()===t)return e}return null},f=function(e){for(var t,n=String(e),o=n.length,r=-1,a="",u=n.charCodeAt(0);++r<o;){if(t=n.charCodeAt(r),0===t)throw new InvalidCharacterError("Invalid character: the input contains U+0000.");a+=t>=1&&31>=t||127==t||0===r&&t>=48&&57>=t||1===r&&t>=48&&57>=t&&45===u?"\\"+t.toString(16)+" ":t>=128||45===t||95===t||t>=48&&57>=t||t>=65&&90>=t||t>=97&&122>=t?n.charAt(r):"\\"+n.charAt(r)}return a},d=function(e,t){var n;return"easeInQuad"===e&&(n=t*t),"easeOutQuad"===e&&(n=t*(2-t)),"easeInOutQuad"===e&&(n=.5>t?2*t*t:-1+(4-2*t)*t),"easeInCubic"===e&&(n=t*t*t),"easeOutCubic"===e&&(n=--t*t*t+1),"easeInOutCubic"===e&&(n=.5>t?4*t*t*t:(t-1)*(2*t-2)*(2*t-2)+1),"easeInQuart"===e&&(n=t*t*t*t),"easeOutQuart"===e&&(n=1- --t*t*t*t),"easeInOutQuart"===e&&(n=.5>t?8*t*t*t*t:1-8*--t*t*t*t),"easeInQuint"===e&&(n=t*t*t*t*t),"easeOutQuint"===e&&(n=1+--t*t*t*t*t),"easeInOutQuint"===e&&(n=.5>t?16*t*t*t*t*t:1+16*--t*t*t*t*t),n||t},m=function(e,t,n){var o=0;if(e.offsetParent)do o+=e.offsetTop,e=e.offsetParent;while(e);return o=o-t-n,o>=0?o:0},h=function(){return Math.max(e.document.body.scrollHeight,e.document.documentElement.scrollHeight,e.document.body.offsetHeight,e.document.documentElement.offsetHeight,e.document.body.clientHeight,e.document.documentElement.clientHeight)},p=function(e){return e&&"object"==typeof JSON&&"function"==typeof JSON.parse?JSON.parse(e):{}},g=function(t,n){e.history.pushState&&(n||"true"===n)&&"file:"!==e.location.protocol&&e.history.pushState(null,null,[e.location.protocol,"//",e.location.host,e.location.pathname,e.location.search,t].join(""))},b=function(e){return null===e?0:s(e)+e.offsetTop};a.animateScroll=function(t,n,a){var u=p(t?t.getAttribute("data-options"):null),s=i(s||c,a||{},u);n="#"+f(n.substr(1));var l="#"===n?e.document.documentElement:e.document.querySelector(n),v=e.pageYOffset;o||(o=e.document.querySelector(s.selectorHeader)),r||(r=b(o));var y,O,S,I=m(l,r,parseInt(s.offset,10)),H=I-v,E=h(),L=0;g(n,s.updateURL);var j=function(o,r,a){var u=e.pageYOffset;(o==r||u==r||e.innerHeight+u>=E)&&(clearInterval(a),l.focus(),s.callback(t,n))},w=function(){L+=16,O=L/parseInt(s.speed,10),O=O>1?1:O,S=v+H*d(s.easing,O),e.scrollTo(0,Math.floor(S)),j(S,I,y)},C=function(){y=setInterval(w,16)};0===e.pageYOffset&&e.scrollTo(0,0),C()};var v=function(e){var n=l(e.target,t.selector);n&&"a"===n.tagName.toLowerCase()&&(e.preventDefault(),a.animateScroll(n,n.hash,t))},y=function(e){n||(n=setTimeout(function(){n=null,r=b(o)},66))};return a.destroy=function(){t&&(e.document.removeEventListener("click",v,!1),e.removeEventListener("resize",y,!1),t=null,n=null,o=null,r=null)},a.init=function(n){u&&(a.destroy(),t=i(c,n||{}),o=e.document.querySelector(t.selectorHeader),r=b(o),e.document.addEventListener("click",v,!1),o&&e.addEventListener("resize",y,!1))},a});

/*************************************************************
** SCROLL TO A COMMENT
**************************************************************/
(function() {
	
	/* Scroll Intialization */
	var toComment = window.location.href.split("#").pop();

	if (typeof toComment !== 'undefined' && toComment !== window.location.href ) {

		var id = toComment.replace('comment-', '');
		if (isNumeric(id) == true) {
			
			var comment = $('[data-comment-id="'+id+'"]');
			
			if (nodeExists(comment)) {
				comment.id = id;
				smoothScroll.animateScroll(null, '#'+id, {
					"speed": 0,
	                "easing": "",
	                "offset": 0,
	                "updateURL": false
             	});
			}
		}
	}

	/* Is numeric variable */
	function isNumeric(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
	}

}());



