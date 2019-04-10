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