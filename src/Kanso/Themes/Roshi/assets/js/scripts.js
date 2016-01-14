/*************************************************************
 ** SCROLL TO A COMMENT
 **************************************************************/
(function() {

    var toComment = window.location.href.split("#").pop();

    if (typeof toComment !== 'undefined' && toComment !== window.location.href) {

        var id = toComment.replace('comment-', '');
        if (isNumeric(id) == true) {

            var comment = Helper.$('[data-comment-id="' + id + '"]');

            if (Helper.nodeExists(comment)) {
                comment.id = id;
                smoothScroll.animateScroll(null, '#' + id, {
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

/*************************************************************
 ** SNTAX HIGHLIGHTING
 **************************************************************/
(function() {

    var codeEls = Helper.$All('pre > code');

    if (Helper.nodeExists(Helper.$('pre > code'))) {
        for (var i = 0; i < codeEls.length; i++) {
            hljs.highlightBlock(codeEls[i]);
        }
    }

}());

/*************************************************************
 ** COMMENT FORM SUBMIT
 **************************************************************/
(function() {

    var COMMENTS_AJAX_URL = window.location.origin + '/comments/';

    /* New comment initializer */
    var commentSubmit = Helper.$('form.comment-form button[type="submit"]');

    if (Helper.nodeExists(commentSubmit)) {
        commentSubmit.addEventListener("click", function() {
            event.preventDefault();
            submitComment(commentSubmit);
        });
    }

    /* Submit comment event */
    function submitComment(trigger) {

        if (Helper.hasClass(trigger, 'active')) return;

        // Build form object
        var formObj = {};
        var form = Helper.parentUntillClass(trigger, 'comment-form');
        var inputs = Helper.$All('input, textarea', form);
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].type === 'checkbox') {
                var checked = inputs[i].checked ? 'true' : 'false';
                formObj[inputs[i].name] = checked;
            } else {
                formObj[inputs[i].name] = inputs[i].value;
            }
        }

        // Remove error classes
        var inputWraps = Helper.$All('.input-wrap', form);
        for (var j = 0; j < inputWraps.length; j++) {
            Helper.removeClass(inputWraps[j], 'error');
        }
        Helper.removeClass(form, 'error');
        Helper.removeClass(form, 'success');
        Helper.$('.form-result', form).innerHTML = '';


        // Validate fields
        if (!validateEmpty(formObj.name)) {
            Helper.addClass(Helper.$('.comment-form-name', form), 'error');
            return;
        }
        if (!validatePlainText(formObj.name)) {
            Helper.addClass(Helper.$('.comment-form-name', form), 'error');
            return;
        }
        if (!validateEmpty(formObj.email)) {
            Helper.addClass(Helper.$('.comment-form-email', form), 'error');
            return;
        }
        if (!validateEmail(formObj.email)) {
            Helper.addClass(Helper.$('.comment-form-email', form), 'error');
            return;
        }
        if (!validateEmpty(formObj.content)) {
            Helper.addClass(Helper.$('.comment-form-content', form), 'error');
            return;
        }

        Helper.addClass(form, 'active');
        Helper.addClass(trigger, 'active');

        // Ajax post comment
        Ajax.post(COMMENTS_AJAX_URL, formObj,
            function(success) {
                console.log(success);

                Helper.removeClass(form, 'active');
                Helper.removeClass(trigger, 'active');

                var responseObj = Helper.isJSON(success);
                if (responseObj && responseObj.details === 'approved') {
                    insertComment(formObj, form);
                    showFormResult('success', 'Your comment was successfully posted.', form, inputs);
                } else if (responseObj && responseObj.details === 'spam') {
                    showFormResult('error', 'Your comment has been marked as spam.', form, inputs);
                } else if (responseObj && responseObj.details === 'pending') {
                    showFormResult('alert', 'Your comment was posted but is pending approval from the site moderator.', form, inputs);
                } else {
                    showFormResult('error', 'There was en error posting your comment. Please try again later.', form, inputs);
                }
            },
            function(error) {
                Helper.removeClass(form, 'active');
                Helper.removeClass(trigger, 'active');
                showFormResult('error', 'There was en error posting your comment. Please try again later.', form, inputs);
                console.log(error);
            }
        );

    }

    /* Show the form result */
    function showFormResult(className, msg, form, inputs) {

        var result = '<div class="' + className + ' message flipInX animated"><div class="message-icon"></div><div class="message-body"><p>' + msg + '</p></div></div>';
        Helper.addClass(Helper.$('.form-result', form), className);
        Helper.$('.form-result', form).innerHTML = result;
        if (className === 'success') {
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].value = '';
            }
        }

    }

    /* Insert new comment into DOM */
    function insertComment(formObj, formEl) {
        var formParent = formEl.parentNode;

        var markDown = window.markdownit({
            html: true,
            xhtmlOut: false,
            breaks: true,
            langPrefix: '',
            linkify: false,
        });

        var HTTP_PROTOCAL = window.location.href.split(":")[0];
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        var content = markDown.render(formObj.content);
        var avatar = HTTP_PROTOCAL + '://www.gravatar.com/avatar/' + md5(formObj.email) + '?s=40&d=mm';
        var d = new Date();
        var date = months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();

        var commentHTML = '<div class="comment-author-wrap"> <div class="comment-avatar-wrap"> <img alt="" src="' + avatar + '" class="comment-avatar-img" width="40" height="40"> </div><p class="comment-author-name">' + formObj.name + '</p></div><div class="comment-comment-body"> <div class="comment-comment-content"> ' + content + ' </div></div><div class="comment-comment-footer"> <time class="comment-comment-time" datetime="">' + date + '</time> • <a class="comment-comment-link" href="#">#</a></div><div class="comment-comment-chidren comment-no-children"> </div>';

        if (Helper.hasClass(formParent, 'comment-comment-chidren')) Helper.removeFromDOM(formEl);
        Helper.newNode('div', 'comment-comment-wrap', null, commentHTML, formParent);

    }


    /* Comment reply click initailizer */
    var replyClickers = Helper.$All('.comment-reply-link');

    if (Helper.nodeExists(Helper.$('.comment-reply-link'))) initReplyClickers();

    function initReplyClickers() {
        for (var i = 0; i < replyClickers.length; i++) {
            replyClickers[i].addEventListener("click", clickReply);
        }
    }

    /* Click reply event */
    function clickReply() {

        var clicked = event.target;

        event.preventDefault();

        var clickedComment = Helper.parentUntillClass(clicked, 'comment-comment-wrap');
        var childrenWrap = Helper.$('.comment-comment-chidren', clickedComment);

        var childrenWrapChilds = children(childrenWrap);
        if (!Helper.empty(childrenWrapChilds)) {
            for (var i = 0; i < childrenWrapChilds.length; i++) {
                if (Helper.hasClass(childrenWrapChilds[i], 'comment-form')) return;
            }
        }

        var formEl = Helper.$('.comments-wrap .comment-form').cloneNode(true);
        Helper.$('input[name="replyID"]', formEl).value = clickedComment.dataset.commentId;

        var formResult = Helper.$('.form-result', formEl);
        Helper.removeClass(formResult, 'error');
        Helper.removeClass(formResult, 'success');
        Helper.removeClass(formResult, 'alert');
        formResult.innerHTML = '';

        var inputs = Helper.$All('input, textarea', formEl);

        for (var j = 0; j < inputs.length; j++) {
            if (inputs[j].name === 'postID') continue;
            inputs[j].value = '';
        }

        if (Helper.hasClass(childrenWrap, 'comment-no-children')) {
            childrenWrap.appendChild(formEl);
            Helper.removeClass(childrenWrap, 'comment-no-children');
        } else {
            childrenWrap.insertBefore(formEl, childrenWrap.childNodes[0]);
        }

        var commentSubmit = Helper.$('button[type="submit"]', formEl);
        commentSubmit.addEventListener("click", function() {
            event.preventDefault();
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

    function md5(r) {
        var n, t, e, o, u, f, i, a, c, l, d = function(r, n) {
                return r << n | r >>> 32 - n
            },
            h = function(r, n) {
                var t, e, o, u, f;
                return o = 2147483648 & r, u = 2147483648 & n, t = 1073741824 & r, e = 1073741824 & n, f = (1073741823 & r) + (1073741823 & n), t & e ? 2147483648 ^ f ^ o ^ u : t | e ? 1073741824 & f ? 3221225472 ^ f ^ o ^ u : 1073741824 ^ f ^ o ^ u : f ^ o ^ u
            },
            g = function(r, n, t) {
                return r & n | ~r & t
            },
            s = function(r, n, t) {
                return r & t | n & ~t
            },
            C = function(r, n, t) {
                return r ^ n ^ t
            },
            v = function(r, n, t) {
                return n ^ (r | ~t)
            },
            m = function(r, n, t, e, o, u, f) {
                return r = h(r, h(h(g(n, t, e), o), f)), h(d(r, u), n)
            },
            w = function(r, n, t, e, o, u, f) {
                return r = h(r, h(h(s(n, t, e), o), f)), h(d(r, u), n)
            },
            A = function(r, n, t, e, o, u, f) {
                return r = h(r, h(h(C(n, t, e), o), f)), h(d(r, u), n)
            },
            S = function(r, n, t, e, o, u, f) {
                return r = h(r, h(h(v(n, t, e), o), f)), h(d(r, u), n)
            },
            y = function(r) {
                for (var n, t = r.length, e = t + 8, o = (e - e % 64) / 64, u = 16 * (o + 1), f = new Array(u - 1), i = 0, a = 0; t > a;) n = (a - a % 4) / 4, i = a % 4 * 8, f[n] = f[n] | r.charCodeAt(a) << i, a++;
                return n = (a - a % 4) / 4, i = a % 4 * 8, f[n] = f[n] | 128 << i, f[u - 2] = t << 3, f[u - 1] = t >>> 29, f
            },
            E = function(r) {
                var n, t, e = "",
                    o = "";
                for (t = 0; 3 >= t; t++) n = r >>> 8 * t & 255, o = "0" + n.toString(16), e += o.substr(o.length - 2, 2);
                return e
            },
            R = [],
            U = 7,
            _ = 12,
            b = 17,
            p = 22,
            L = 5,
            j = 9,
            k = 14,
            q = 20,
            x = 4,
            z = 11,
            B = 16,
            D = 23,
            F = 6,
            G = 10,
            H = 15,
            I = 21;
        for (r = utf8_encode(r), R = y(r), i = 1732584193, a = 4023233417, c = 2562383102, l = 271733878, n = R.length, t = 0; n > t; t += 16) e = i, o = a, u = c, f = l, i = m(i, a, c, l, R[t + 0], U, 3614090360), l = m(l, i, a, c, R[t + 1], _, 3905402710), c = m(c, l, i, a, R[t + 2], b, 606105819), a = m(a, c, l, i, R[t + 3], p, 3250441966), i = m(i, a, c, l, R[t + 4], U, 4118548399), l = m(l, i, a, c, R[t + 5], _, 1200080426), c = m(c, l, i, a, R[t + 6], b, 2821735955), a = m(a, c, l, i, R[t + 7], p, 4249261313), i = m(i, a, c, l, R[t + 8], U, 1770035416), l = m(l, i, a, c, R[t + 9], _, 2336552879), c = m(c, l, i, a, R[t + 10], b, 4294925233), a = m(a, c, l, i, R[t + 11], p, 2304563134), i = m(i, a, c, l, R[t + 12], U, 1804603682), l = m(l, i, a, c, R[t + 13], _, 4254626195), c = m(c, l, i, a, R[t + 14], b, 2792965006), a = m(a, c, l, i, R[t + 15], p, 1236535329), i = w(i, a, c, l, R[t + 1], L, 4129170786), l = w(l, i, a, c, R[t + 6], j, 3225465664), c = w(c, l, i, a, R[t + 11], k, 643717713), a = w(a, c, l, i, R[t + 0], q, 3921069994), i = w(i, a, c, l, R[t + 5], L, 3593408605), l = w(l, i, a, c, R[t + 10], j, 38016083), c = w(c, l, i, a, R[t + 15], k, 3634488961), a = w(a, c, l, i, R[t + 4], q, 3889429448), i = w(i, a, c, l, R[t + 9], L, 568446438), l = w(l, i, a, c, R[t + 14], j, 3275163606), c = w(c, l, i, a, R[t + 3], k, 4107603335), a = w(a, c, l, i, R[t + 8], q, 1163531501), i = w(i, a, c, l, R[t + 13], L, 2850285829), l = w(l, i, a, c, R[t + 2], j, 4243563512), c = w(c, l, i, a, R[t + 7], k, 1735328473), a = w(a, c, l, i, R[t + 12], q, 2368359562), i = A(i, a, c, l, R[t + 5], x, 4294588738), l = A(l, i, a, c, R[t + 8], z, 2272392833), c = A(c, l, i, a, R[t + 11], B, 1839030562), a = A(a, c, l, i, R[t + 14], D, 4259657740), i = A(i, a, c, l, R[t + 1], x, 2763975236), l = A(l, i, a, c, R[t + 4], z, 1272893353), c = A(c, l, i, a, R[t + 7], B, 4139469664), a = A(a, c, l, i, R[t + 10], D, 3200236656), i = A(i, a, c, l, R[t + 13], x, 681279174), l = A(l, i, a, c, R[t + 0], z, 3936430074), c = A(c, l, i, a, R[t + 3], B, 3572445317), a = A(a, c, l, i, R[t + 6], D, 76029189), i = A(i, a, c, l, R[t + 9], x, 3654602809), l = A(l, i, a, c, R[t + 12], z, 3873151461), c = A(c, l, i, a, R[t + 15], B, 530742520), a = A(a, c, l, i, R[t + 2], D, 3299628645), i = S(i, a, c, l, R[t + 0], F, 4096336452), l = S(l, i, a, c, R[t + 7], G, 1126891415), c = S(c, l, i, a, R[t + 14], H, 2878612391), a = S(a, c, l, i, R[t + 5], I, 4237533241), i = S(i, a, c, l, R[t + 12], F, 1700485571), l = S(l, i, a, c, R[t + 3], G, 2399980690), c = S(c, l, i, a, R[t + 10], H, 4293915773), a = S(a, c, l, i, R[t + 1], I, 2240044497), i = S(i, a, c, l, R[t + 8], F, 1873313359), l = S(l, i, a, c, R[t + 15], G, 4264355552), c = S(c, l, i, a, R[t + 6], H, 2734768916), a = S(a, c, l, i, R[t + 13], I, 1309151649), i = S(i, a, c, l, R[t + 4], F, 4149444226), l = S(l, i, a, c, R[t + 11], G, 3174756917), c = S(c, l, i, a, R[t + 2], H, 718787259), a = S(a, c, l, i, R[t + 9], I, 3951481745), i = h(i, e), a = h(a, o), c = h(c, u), l = h(l, f);
        var J = E(i) + E(a) + E(c) + E(l);
        return J.toLowerCase()
    }

    function utf8_encode(r) {
        if (null === r || "undefined" == typeof r) return "";
        var n, t, e = r + "",
            o = "",
            u = 0;
        n = t = 0, u = e.length;
        for (var f = 0; u > f; f++) {
            var i = e.charCodeAt(f),
                a = null;
            if (128 > i) t++;
            else if (i > 127 && 2048 > i) a = String.fromCharCode(i >> 6 | 192, 63 & i | 128);
            else if (55296 != (63488 & i)) a = String.fromCharCode(i >> 12 | 224, i >> 6 & 63 | 128, 63 & i | 128);
            else {
                if (55296 != (64512 & i)) throw new RangeError("Unmatched trail surrogate at " + f);
                var c = e.charCodeAt(++f);
                if (56320 != (64512 & c)) throw new RangeError("Unmatched lead surrogate at " + (f - 1));
                i = ((1023 & i) << 10) + (1023 & c) + 65536, a = String.fromCharCode(i >> 18 | 240, i >> 12 & 63 | 128, i >> 6 & 63 | 128, 63 & i | 128)
            }
            null !== a && (t > n && (o += e.slice(n, t)), o += a, n = t = f + 1)
        }
        return t > n && (o += e.slice(n, u)), o
    }

    /* Get first level child nodes */
    function children(el) {
        var cass_path = cssPath(el);

        if (Helper.$(cass_path) === el) {

            return Helper.$All(cass_path + ' > *');

        }

        return false;
    }

    /* Get the css path of an element */
    function cssPath(el) {

        var names = [];
        while (el.parentNode) {
            if (el.id) {
                names.unshift('#' + el.id);
                break;
            } else {
                if (el == el.ownerDocument.documentElement) names.unshift(el.tagName);
                else {
                    for (var c = 1, e = el; e.previousElementSibling; e = e.previousElementSibling, c++);
                    names.unshift(el.tagName + ":nth-child(" + c + ")");
                }
                el = el.parentNode;
            }
        }
        return names.join(" > ");

    }

}());
