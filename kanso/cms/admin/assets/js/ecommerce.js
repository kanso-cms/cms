/**
 * Writer product tabs
 *
 */
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
     * Bundle options wrapper
     *
     * @var node
     */
    var bundleOptionsWrapper = Helper.$('.js-bundle-options');

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

        Helper.triggerEvent(typeSelect, 'change');
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
            Helper.removeClass(bundleOptionsWrapper, 'active');
            Helper.addClass(productOptionsWrapper, 'active');
        }
        else if (value === 'bundle')
        {
            Helper.removeClass(productOptionsWrapper, 'active');
            Helper.addClass(bundleOptionsWrapper, 'active');
        }
        else
        {
            Helper.removeClass(bundleOptionsWrapper, 'active');
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

        if (tabPanel.dataset.tabPanel === 'offer-1' && Helper.count(Helper.$All('.tab-panel', tabsPanelWrap)) === 1)
        {
            Modules.require('Notifications',
            {
                type : 'warning',
                msg  : 'Your product must contain at least one offer.',
            });

            return;
        }

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
                '<label>SKU</label><input type="text" name="product_offer_' + i + '_id" value="" autocomplete="off" placeholder="SKU001">',
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
                '<label>Weight (g)</label><input type="text" name="product_offer_' + i + '_weight" value="" autocomplete="off" placeholder="900">',
            '</div>',
            '<div class="form-field row floor-xs">',
                '<span class="checkbox checkbox-primary">',
                    '<input type="checkbox" name="product_offer_' + i + '_free_shipping" id="product_offer_' + i + '_free_shipping">',
                    '<label for="product_offer_' + i + '_free_shipping">Free Shipping</label>',
                '</span>',
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

/**
 * Writer Bundles
 * 
 * @var obj
 */
(function()
{
    /**
     * JS Helper
     * 
     * @var obj
     */
    var Helper = Modules.get('JSHelper');

    /**
     * Has the module been initially loaded
     * 
     * @var bool
     */
    var loadedBundles = false;

    /**
     * Bundles Plugin Core
     * 
     * @var object
     */
    var Bundles = function()
    {
        // Basics
        this._bundleType             = 'group';
        this._bundleSelectEl         = Helper.$('.js-bundle-type-select');
        this._bundleTypeOptionsWraps = Helper.$All('.js-bundle-option');
        this._productsList           = '';

        // Bogo
        this._bogoOptionsWrap        = Helper.$('.js-bundle-option-bogo');
        this._bogoProductsTriggerIn  = Helper.$('.js-bogo-products-in-trigger');
        this._bogoProductsTriggerOut = Helper.$('.js-bogo-products-out-trigger');
        this._bogoProductsTableIn    = Helper.$('.js-bogo-products-in-table');
        this._bogoProductsTableOut   = Helper.$('.js-bogo-products-out-table');

        // Group
        this._groupOptionsWrap       = Helper.$('.js-bundle-option-group');
        this._groupProductsTrigger   = Helper.$('.js-group-products-trigger');
        this._groupProductsTable     = Helper.$('.js-group-products-table');
        this._groupTotalPriceEl      = Helper.$('.js-group-total-price');
        this._groupPercentDiscountEl = Helper.$('.js-group-discount-input');
        this._groupFixedPriceEl      = Helper.$('.js-group-fixed-price-input');
        this._groupCentsOverrideEl   = Helper.$('.js-group-cents-override-input');

        // Combo
        this._comboOptionsWrap       = Helper.$('.js-bundle-option-combo');
        this._comboProductsTriggers  = Helper.$All('.js-combo-products-trigger');
        this._comboProductsTables    = Helper.$All('.js-combo-products-table');
        this._comboAddTableEl        = Helper.$('.js-bundle-combo-selection-add-trigger');

        /**
         * If the wrapper exists add listeners
         */
        if (Helper.nodeExists(Helper.$('.js-bundle-options')))
        {
            this._boot();
        }
    };

    /**
     * Boot the bundles plugin
     *
     */
    Bundles.prototype._boot = function()
    {
        this._bundleType = this._bundleSelectEl.options[this._bundleSelectEl.selectedIndex].value;

        this._loadProductList();

        this._bindBundleTypeSelect();

        this._bindBogoBundle();

        this._bindGroupBundle();

        this._bindComboBundle();

        this._bindExistingTables();

        if (!loadedBundles)
        {
            this._triggerBootEvents();

            loadedBundles = true;
        }
    }

    /**
     * Trigger initial boot events
     *
     */
    Bundles.prototype._triggerBootEvents = function()
    {
        this._onBundleTypeChange(null);
    }

    /**
     * Trigger initial boot events
     *
     */
    Bundles.prototype._bindExistingTables = function()
    {
        var removeTriggers  = Helper.$All('.js-remove-product-row');
        var quantitySelects = Helper.$All('.js-product-qnty-select');
        var allTables       = Helper.$All('.js-combo-products-table, .js-group-products-table, .js-bogo-products-in-table, .js-bogo-products-out-table');
        var removeTableTriggers = Helper.$All('.js-combo-remove-trigger');

        for (var i = 0; i < removeTriggers.length; i++)
        {
            Helper.addEventListener(removeTriggers[i], 'click', this._removeTableRowHandler);
        }

        for (var j = 0; j < quantitySelects.length; j++)
        {
            Helper.addEventListener(quantitySelects[j], 'change', this._quanityAdjustmentHandler);
        }

        for (var q = 0; q < allTables.length; q++)
        {
            this._setTableTotalPrice(allTables[q]);
        }

        for (var u = 0; u < removeTableTriggers.length; u++)
        {
            Helper.addEventListener(removeTableTriggers[u], 'click', this._removeComboTableHandler);
        }
    }

    /**
     * Loads the list of products existing products/offers into the module 
     *
     */
    Bundles.prototype._loadProductList = function()
    {
        var products = document.createElement('DIV');
        var list     = Helper.$('.js-bundle-product-list').cloneNode(true);
        
        list.classList.remove('hidden');
        
        products.appendChild(list);
        
        this._productsList = products.innerHTML;
    }

    /**
     * Bind the bundle type <select> input
     *
     */
    Bundles.prototype._bindBundleTypeSelect = function()
    {
        Helper.addEventListener(this._bundleSelectEl, 'change', this._onBundleTypeChange);
    }

    /**
     * Bind elements for BOGO type bundle
     * 
     */
    Bundles.prototype._bindBogoBundle = function()
    {
        Helper.addEventListener(this._bogoProductsTriggerIn, 'click', this._showProductChooser);

        Helper.addEventListener(this._bogoProductsTriggerOut, 'click', this._showProductChooser);
    }

    /**
     * Bind elements for GROUP type bundle
     * 
     */
    Bundles.prototype._bindGroupBundle = function()
    {
        Helper.addEventListener(this._groupProductsTrigger, 'click', this._showProductChooser);

        Helper.addEventListener(this._groupPercentDiscountEl, 'input', this._updateGroupPrice);

        Helper.addEventListener(this._groupFixedPriceEl, 'input', this._updateGroupPrice);

        Helper.addEventListener(this._groupCentsOverrideEl, 'input', this._updateGroupPrice);
    }


    /**
     * Bind elements for COMBO type bundle
     * 
     */
    Bundles.prototype._bindComboBundle = function()
    {
        for (var i = 0; i < this._comboProductsTriggers.length; i++)
        {
            Helper.addEventListener(this._comboProductsTriggers[i], 'click', this._showProductChooser);
        }

        Helper.addEventListener(this._comboAddTableEl, 'click', this._addComboTable);
    }

    /**
     * Toggle the bundle options visibility when bundle type is changed
     * 
     * @param e event Select change event|null
     */
    Bundles.prototype._onBundleTypeChange = function(e)
    {
        e = e || window.event;

        var _this = !e ? this : Modules.get('WriterBundles');

        _this._bundleType = _this._bundleSelectEl.options[_this._bundleSelectEl.selectedIndex].value;
            
        for (var i = 0; i < _this._bundleTypeOptionsWraps.length; i++)
        {
            if (_this._bundleTypeOptionsWraps[i].dataset.bundleType === _this._bundleType)
            {
                Helper.addClass(_this._bundleTypeOptionsWraps[i], 'active');
            }
            else
            {
                Helper.removeClass(_this._bundleTypeOptionsWraps[i], 'active');
            }
        }
    }

    /**
     * Update the GROUP bundle "customers will pay $" value
     * 
     * @param e event Input change event|null
     */
    Bundles.prototype._updateGroupPrice = function(e)
    {        
        e = e || window.event;

        var _this = !e ? this : Modules.get('WriterBundles');

        if (Helper.hasClass(_this._groupProductsTable, 'empty-table'))
        {
            _this._groupTotalPriceEl.innerHTML = '0.00';

            return;
        }

        var overrideCents   = String(_this._groupCentsOverrideEl.value).trim();
        var price           = _this._getTableFullPrice(_this._groupProductsTable);
        var discountPercent = _this._groupPercentDiscountEl.value.trim();
        var fixedPrice      = _this._groupFixedPriceEl.value.trim();

        // No products
        if (price === 0)
        {
            _this._groupTotalPriceEl.innerHTML = '0.00';

            return;
        }

        // Fixed price
        if (fixedPrice !== '')
        {
            _this._groupTotalPriceEl.innerHTML = parseFloat(fixedPrice).toFixed(2);

            return;
        }

        // Discount percent
        if (discountPercent !== '')
        {
            discountPercent = parseInt(discountPercent);

            // Discount is 0
            if (discountPercent === 0)
            {
                _this._groupTotalPriceEl.innerHTML = price.toFixed(2);

                return;
            }

            var fraction = (100 - discountPercent) / 100;

            price = (price * fraction).toFixed(2);

            // override cents
            if (overrideCents !== '')
            {
                overrideCents = String(overrideCents).substring(0,2);

                price = String(price);

                price = price.substr(0, price.indexOf('.')) + '.' + overrideCents;
            }

            _this._groupTotalPriceEl.innerHTML = price;
        }

        // Not changed
        else
        {
            _this._groupTotalPriceEl.innerHTML = price.toFixed(2);
        }
    }

    /**
     * Returns the total price of a GROUP type bundle
     * 
     * @param  node  table The target table to get the price
     * @return float
     */
    Bundles.prototype._getTableFullPrice = function(table)
    {
        var price    = 0;
        var products = Helper.$All('.js-product-entry', table);

        for (var i = 0; i < products.length; i++)
        {
            var rowPrice = parseFloat(products[i].dataset.productSalePrice) * parseInt(products[i].dataset.quantity);

            price = price + rowPrice;
        }

        return price;
    }

    /**
     * Show product chooser
     * 
     */
    Bundles.prototype._showProductChooser = function()
    {
        var _this   = Modules.get('WriterBundles');
        var trigger = this;
        var table;
        
        // bogo-in
        if (trigger === _this._bogoProductsTriggerIn)
        {
            table = _this._bogoProductsTableIn;
        }

        // bogo-out 
        else if (trigger === _this._bogoProductsTriggerOut)
        {
            table = _this._bogoProductsTableOut;
        }

        // group
        else if (trigger === _this._groupProductsTrigger)
        {
            table = _this._groupProductsTable;
        }

        // combo
        else
        {
            table = Helper.$('.js-combo-products-table', Helper.previousUntillType(trigger, 'div'));
        }

        Modules.get('Modal',
        {
            type             : 'default bundle-selection-modal',
            title            : 'Make A Selection',
            message          : '',
            closeText        : 'Cancel',
            closeClass       : 'btn-default',
            confirmClass     : 'btn-success',
            confirmText      : 'Save Selection',
            overlay          : 'dark',
            extras           : _this._productsList,
            onRender         : function()
            {
                // this = modal
                _this._addProductListSelectionListeners(this);
                _this._setSelectedProductList(this, table);
            },
            validateConfirm  : function()
            {
                // this = modal
                return _this._validateModalConfirm(this, table);
            },
        });
    }

     /**
     * Add the click to select listeners to the product list in a modal
     * 
     * @param node modal The modal being displayed
     * @param node table The target table for the list
     */
    Bundles.prototype._setSelectedProductList = function(modal, table)
    {
        var products = Helper.$All('.js-product-entry', table);

        for (var i = 0; i < products.length; i++)
        {
            var li = Helper.$('li[data-sku="' + products[i].dataset.sku + '"][data-product-id="' + products[i].dataset.productId + '"]', modal);

            li.dataset.quantity = products[i].dataset.quantity;
            Helper.addClass(li, 'active');
        }
    }

    /**
     * Add the click to select listeners to the product list in a modal
     * 
     * @param nodd Modal The modal being displayed
     */
    Bundles.prototype._addProductListSelectionListeners = function(modal)
    {
        var lis = Helper.$All('li', modal);

        for (var i = 0; i < lis.length; i++)
        {
            Helper.addEventListener(lis[i], 'click', function toggleActive(e)
            {
                Helper.toggleClass(this, 'active');
            });
        }
    }

    /**
     * Validate there are products selected when the save modal button is clicked
     * 
     * @param node modal Modal that is being closed
     * @param node table Table to insert products into
     */
    Bundles.prototype._validateModalConfirm = function(modal, table)
    {
        var _this = Modules.get('WriterBundles');

        var products = _this._getSelectedProducts(modal);

        if (Helper.empty(products))
        {
            Modules.require('Notifications',
            {
                type : 'warning',
                msg  : 'You need to select at least one product.',
            });

            return false;
        }

        _this._insertTableProducts(products, table);

        return true;
    }

    /**
     * Get selected products in a modal
     *
     * @param node modal The modal being displayed
     */
    Bundles.prototype._getSelectedProducts = function(modal)
    {
        var products = [];
        var lis      = Helper.$All('li', modal);

        for (var i = 0; i < lis.length; i++)
        {
            var li = lis[i];

            if (Helper.hasClass(li, 'active'))
            {
                products.push({
                    quantity    : li.dataset.quantity,
                    product_id  : li.dataset.productId,
                    sku    : li.dataset.sku,
                    image       : li.dataset.productImage,
                    title       : li.dataset.productTitle,
                    offer_name  : li.dataset.productOffer,
                    price       : li.dataset.productPrice,
                    sale_price  : li.dataset.productSalePrice,
                });
            }
        }

        return products;
    }

    /**
     * Insert/Update table based on selected products from modal
     * 
     * @param array products Array of product objects
     * @param node  table    Table to insert products into
     */
    Bundles.prototype._insertTableProducts = function(products, table)
    {
        this._clearTable(table);

        Helper.removeClass(table, 'empty-table');

        var input_suffix = this._getInputSuffixForTable(table);
        var total        = 0;

        for (var i = 0; i < products.length; i++)
        {
            this._insertTableRow(products[i], table);
            
            total = total + parseFloat(products[i]['sale_price']);
        }

        this._insertSummaryRow(table, total);

        if (this._bundleType === 'group')
        {
            this._updateGroupPrice();
        }
    }

    /**
     * Insert the summary row into a table
     * 
     * @param  node  table The product data in object format
     * @param  node  table The target table to insert into
     */
    Bundles.prototype._insertTableRow = function(product, table)
    {
        var input_suffix = this._getInputSuffixForTable(table);
        var row          = document.createElement('tr');
        var price        = (parseInt(product['quantity']) * parseFloat(product['price'])).toFixed(2);
        var salePrice    = (parseInt(product['quantity']) * parseFloat(product['sale_price'])).toFixed(2);
        var select       = '<select name="bundle_product' + input_suffix + '_quantities[]" class="js-product-qnty-select">';
        row.className    = 'js-product-entry';
        
        for (var j = 1; j < 11; j++)
        {
            var selected =  parseInt(product['quantity']) === j ? 'selected' : '';
            select = select +  '<option value="' + j + '" ' + selected + '>' + j + '</option>';
        }

        row.innerHTML = 
        [
            '<th>',
                '<span class="form-field">',
                    select,
                '</span>',
            '</th>',
            '<th>',
                '<img width="100" height="100" src="' + product['image'] + '" alt="' + product['title'] + '">',
            '</th>',
            '<td><strong>' + product['title'] + '</strong> - ' + product['offer_name'] + '</td>',
            '<td><span class="color-gray"><del>$<span class="js-reg-price">' + price +'</span></del></span></td>',
            '<td>$<span class="js-sale-price">' + salePrice + '</span></td>',
            '<td>',
                '<button type="button" class="btn btn-outline btn-xs js-remove-product-row">',
                    '<span class="glyph-icon glyph-icon-minus"></span>',
                '</button>',
                '<input type="hidden" class="hidden" name="bundle_product' + input_suffix + '_ids[]" value="' + product['product_id'] + '">',
                '<input type="hidden" class="hidden" name="bundle_product_offer' + input_suffix + '_ids[]" value="' + product['sku'] + '">',
            '</td>'
        ].join('');

        row.dataset.quantity         = product['quantity'];
        row.dataset.productId        = product['product_id'];
        row.dataset.sku          = product['sku'];
        row.dataset.productTitle     = product['title'];
        row.dataset.productOffer     = product['offer_name'];
        row.dataset.productPrice     = product['price'];
        row.dataset.productSalePrice = product['sale_price'];

        Helper.$('tbody', table).appendChild(row);

        Helper.addEventListener(Helper.$('.js-remove-product-row', row), 'click', this._removeTableRowHandler);

        Helper.addEventListener(Helper.$('.js-product-qnty-select', row), 'change', this._quanityAdjustmentHandler);
    }

    /**
     * Insert the summary row into a table
     * 
     * @param  node  table The target table to get the suffix for
     * @return string
     */
    Bundles.prototype._getInputSuffixForTable = function(table)
    {
        var input_suffix = '';

        if (this._bundleType === 'bogo')
        {
            if (table === this._bogoProductsTableIn)
            {
                input_suffix = '_bogo_in';
            }
            else if (table === this._bogoProductsTableOut)
            {
                input_suffix = '_bogo_out';
            }
        }
        else if (this._bundleType === 'combo')
        {
            for (var j = 0; j < this._comboProductsTables.length; j++)
            {
                if (table === this._comboProductsTables[j])
                {
                    input_suffix = '_combo_' + (j + 1);

                    break;
                }
            }
        }

        return input_suffix;
    }

    /**
     * Insert the summary row into a table
     * 
     * @param node  table The target table to insert into
     * @param float total The total price of the products in the table
     */
    Bundles.prototype._insertSummaryRow = function(table, total)
    {
        var summary = document.createElement('tr');
        summary.className = 'price-summary';
        summary.innerHTML = 
        [
            '<td><strong>Total</strong></td>',
            '<td></td>',
            '<td></td>',
            '<td></td>',
            '<td><strong>$<span class="js-table-price-total">' + total.toFixed(2) + '</span></strong></td>',
            '<td></td>'
        ].join('');

        Helper.$('tbody', table).appendChild(summary);
    }

    /**
     * Clear rows from a product table
     * 
     * @param event e Javascript change event | null
     */
    Bundles.prototype._quanityAdjustmentHandler = function(e)
    {
        e = e || window.event;

        var _this     = Modules.get('WriterBundles');
        var row       = Helper.closest(this, 'tr');
        var table     = Helper.closest(this, 'table');
        var qnty      = parseInt(this.options[this.selectedIndex].value);
        var price     = parseFloat(row.dataset.productPrice);
        var salePrice = parseFloat(row.dataset.productSalePrice);

        row.dataset.quantity = qnty;

        Helper.$('.js-reg-price', row).innerHTML = (price * qnty).toFixed(2);
        Helper.$('.js-sale-price', row).innerHTML = (salePrice * qnty).toFixed(2);

        _this._setTableTotalPrice(table);

        if (_this._bundleType === 'group')
        {
            _this._updateGroupPrice();
        }
    }

    /**
     * Clear rows from a product table
     * 
     * @param node table The target table to clear rows from
     */
    Bundles.prototype._clearTable = function(table)
    {
        var rows = Helper.$All('tbody > tr', table);

        for (var i = 0; i < rows.length; i++)
        {
            Helper.removeFromDOM(rows[i]);
        }

        Helper.addClass(table, 'empty-table');
    }

    /**
     * Removes an individual row from a table
     * 
     * @param node row The target row to remove
     */
    Bundles.prototype._removeTableRowHandler = function(e)
    {
        e = e || window.event;

        var _this = Modules.get('WriterBundles');
        var row   = Helper.closest(this, 'tr');
        var table = Helper.closest(this, 'table');

        Helper.removeFromDOM(row);

        _this._setTableTotalPrice(table);

        if (_this._bundleType === 'group')
        {
            _this._updateGroupPrice();
        }
    }

    /**
     * Sets the tables total price
     * 
     * @param node table The target table to calculate
     */
    Bundles.prototype._setTableTotalPrice = function(table)
    {        
        var rows    = Helper.$All('.js-product-entry', table);
        var totalEl = Helper.$('.js-table-price-total', table);

        if (rows.length === 0)
        {
            this._clearTable(table);

            Helper.addClass(table, 'empty-table');

            return;
        }

        if (totalEl)
        {
            totalEl.innerHTML = this._getTableFullPrice(table).toFixed(2);
        }
    }

    /**
     * Remove a combo table handler
     * 
     * @param e event Input change event|null
     */
    Bundles.prototype._removeComboTableHandler = function(e)
    {
        var table = Helper.closest(this, 'div');

        Helper.removeFromDOM(table);
    }

    /**
     * Add a new combo table
     * 
     * @param e event Input change event|null
     */
    Bundles.prototype._addComboTable = function(e)
    {
        e = e || window.event;

        var _this = Modules.get('WriterBundles');
        var table = document.createElement('div');

        table.className = 'row floor-xs';
        table.innerHTML = 
        [
            '<p class="text-italic color-gray">Customers can select from <strong>any</strong> of these products.</p>',
            '<div class="form-field row floor-xs">',
                '<label>Selection Name</label>',
                    '<input type="text" name="bundle_combo_names[]" placeholder="Free Gift 1">',
            '</div>',
            '<div class="table-responsive"><table class="table empty-table js-combo-products-table"><thead><tr><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead><tbody></tbody></table></div>',
            '<button class="btn btn-danger js-combo-remove-trigger" type="button">Remove selection</button>&nbsp;&nbsp;&nbsp;',
            '<button class="btn js-combo-products-trigger" type="button">Select Products</button>'
        ].join('');

        _this._comboOptionsWrap.insertBefore(table, _this._comboAddTableEl.parentNode);

        _this._comboProductsTables = Helper.$All('.js-combo-products-table');

        Helper.addEventListener(Helper.$('.js-combo-products-trigger', table), 'click', _this._showProductChooser);

        Helper.addEventListener(Helper.$('.js-combo-remove-trigger', table), 'click', _this._removeComboTableHandler);
    }
   
    /**
     * Instantiate and boot
     *
     */
    Modules.singleton('WriterBundles', Bundles).get('WriterBundles');

})();