(function()
{
    /**
     * JS Helper module
     *
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * Post type select element
     *
     * @var node
     */
    var typeSelect = Helper.$('.js-writer-form select[name="type"]');

    /**
     * Add new offer button
     *
     * @var node
     */
    var addOfferButton = Helper.$('.js-add-product-offer');

    /**
     * Product options wrapper
     *
     * @var node
     */
    var productOptionsWrapper = Helper.$('.js-product-options');

    /**
     * Product options wrapper
     *
     * @var node
     */
    var tabsPanelWrap = Helper.$('.js-product-options .js-tab-panels-wrap');

    /**
     * Product options wrapper
     *
     * @var node
     */
    var tabsButtonWrap = Helper.$('.js-product-options .js-tab-nav');

    /**
     * Product options wrapper
     *
     * @var node
     */
    var removeTabBtns = Helper.$All('.js-product-options .js-remove-offer');

    /**
     *
     * If the wrapper exists add listeners
     */
    if (Helper.nodeExists(productOptionsWrapper))
    {
        Helper.addEventListener(typeSelect, 'change', onPostTypeChange);

        Helper.addEventListener(addOfferButton, 'click', insertNewTab);

        addRemoveListeners();
    }

    /**
     * Toggle the product options visibility when post type is changed
     * 
     * @param e event Select change event|null
     */
    function onPostTypeChange(e)
    {
        e = e || window.event;
            
        var value = typeSelect.options[typeSelect.selectedIndex].value;

        if (value === 'product')
        {
            Helper.addClass(productOptionsWrapper, 'active');
        }
        else
        {
            Helper.removeClass(productOptionsWrapper, 'active');
        }
    }

    /**
     * Add listeners to remove tab buttons
     * 
     */
    function addRemoveListeners()
    {
        for (var i = 0; i < removeTabBtns.length; i++)
        {
            Helper.addEventListener(removeTabBtns[i], 'click', removeCurrTab);
        }
    }

    /**
     * Remove current tab after click
     * 
     * @param e event Select change event|null
     */
    function removeCurrTab(e)
    {
        e = e || window.event;

        var tabPanel = Helper.parentUntillClass(this, 'tab-panel');
        var tabLink  = Helper.$('li a[data-tab="' + tabPanel.dataset.tabPanel + '"]', tabsButtonWrap).parentNode;

        Helper.removeFromDOM(tabLink);
        Helper.removeFromDOM(tabPanel);

        reIndexTabs();

        var firstLink = Helper.$('li:first-child a', tabsButtonWrap);

        if (Helper.nodeExists(firstLink))
        {
            Helper.triggerEvent(firstLink, 'click');
        }
    }

    /**
     * Insert a new tab
     * 
     * @param e event Select change event|null
     */
    function insertNewTab(e)
    {
        var offerCount = Helper.count(Helper.$All('.tab-panel', tabsPanelWrap));

        var i = offerCount + 1;

        var link = '<a href="#" data-tab="offer-' + i + '">Offer ' + i + '</a>';

        var tabContent = Helper.clean_inner_html([
            '<div class="form-field row floor-xs">',
                '<label>ID</label><input type="text" name="product_offer_' + i + '_id" value="" autocomplete="off" placeholder="SKU001">',
            '</div>',
            '<div class="form-field row floor-xs">',
                '<label>Name</label><input type="text" name="product_offer_' + i + '_name" value="" autocomplete="off" placeholder="XXS">',
            '</div>',
            '<div class="form-field row floor-xs">',
                '<label>Price</label><input type="text" name="product_offer_' + i + '_price" value="" autocomplete="off" placeholder="19.95">',
            '</div>',
            '<div class="form-field row floor-xs">',
                '<label>Sale Price</label><input type="text" name="product_offer_' + i + '_sale_price" value="" autocomplete="off" placeholder="9.95">',
            '</div>',
            '<div class="form-field row floor-xs">',
                '<span class="checkbox checkbox-primary">',
                    '<input type="checkbox" name="product_offer_' + i + '_instock" id="product_offer_' + i + '_instock" checked>',
                    '<label for="product_offer_' + i + '_instock">In Stock</label>',
                '</span>',
            '</div>',
            '<button class="btn btn-danger js-remove-offer" type="button">Remove Offer</button>',
        ]);

        var newTab = Helper.newNode('DIV', 'tab-panel', null, tabContent, tabsPanelWrap);

        newTab.dataset.tabPanel = 'offer-' + i;

        var newButton = Helper.newNode('LI', null, null, link, tabsButtonWrap);

        Modules.refresh('TabNav');

        Helper.triggerEvent(Helper.$('li:last-of-type > a', tabsButtonWrap), 'click');

        Helper.addEventListener(Helper.$('.js-remove-offer', newTab), 'click', removeCurrTab);

        reIndexTabs();
    }

    /**
     * Insert a new tab
     * 
     * @param e event Select change event|null
     */
    function reIndexTabs()
    {
        var tabLinks  = Helper.$All('li a', tabsButtonWrap);
        var tabPanels = Helper.$All('.tab-panel', tabsPanelWrap);


        for (var i = 0; i < tabLinks.length; i++)
        {
            tabLinks[i].dataset.tab = 'offer-' + (i + 1);
            tabLinks[i].innerHTML = 'Offer ' + (i + 1);
        }

        for (var j = 0; j < tabPanels.length; j++)
        {
            tabPanels[j].dataset.tabPanel = 'offer-' + (j + 1);
        }
    }

}());

/**
 * Coupon add/remove
 * 
 * @var obj
 */
(function() {

    /**
     * JS Helper
     * 
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * If the wrapper exists add listeners
     */
    if (Helper.nodeExists(Helper.$('.js-add-coupon-btn')))
    {
        _initTriggers();
    }

    /**
     * Loop and initialize triggers
     *
     */
    function _initTriggers()
    {
        var addTrigger  = Helper.$('.js-add-coupon-btn');
        var rmvTriggers = Helper.$All('.js-rmv-coupon-code');

        for (var i = 0; i < rmvTriggers.length; i++)
        {
            Helper.addEventListener(rmvTriggers[i], 'click', _removeCouoponHandler);
        }

        Helper.addEventListener(addTrigger, 'click', _addCouponHandler);

    }

    /**
     * Remove post meta key/value handler
     *
     * @param event e JavaScript click event
     */
    function _removeCouoponHandler(e)
    {
        e = e || window.event;

        e.preventDefault();

        Helper.removeFromDOM(Helper.parentUntillClass(this, 'js-coupon-row'));
    }

    /**
     * Add new post meta key/value handler
     *
     * @param event e JavaScript click event
     */
    function _addCouponHandler(e)
    {
        e = e || window.event
        
        e.preventDefault();

        var container = Helper.$('.js-coupon-entries');
        var row       = document.createElement('DIV');
        row.className = 'row roof-xs js-coupon-row';
        row.innerHTML =
        [
           '<div class="form-field floor-xs">',
                '<label>Key</label>',
                '<input type="text" name="coupon_keys[]" value="" autocomplete="off" size="20">',
           '</div>&nbsp;&nbsp;&nbsp;<div class="form-field floor-xs">',
                '<label>Value</label>',
                '<input type="text" name="coupon_values[]" value="" autocomplete="off" size="60">',
           '</div>&nbsp;&nbsp;&nbsp;<button class="btn btn-danger js-rmv-coupon-code" type="button">Remove</button>',
           '<div class="row clearfix"></div>',
        ].join('');
            
        container.appendChild(row);

        Helper.addEventListener(Helper.$('.js-rmv-coupon-code', row), 'click', _removeCouoponHandler);
    }

}());