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
