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
                this.insertArticles(xhr.details[this.currentPage - 1]);
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
