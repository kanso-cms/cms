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
