<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use kanso\cms\ecommerce\helpers\Helpers;
use kanso\framework\mvc\model\Model;

/**
 * Ecommerce component core.
 *
 * @author Joe J. Howard
 */
class Ecommerce extends Model
{
    /**
     * BrainTree utility instance.
     *
     * @var \kanso\cms\ecommerce\BrainTree
     */
    private $braintree;

    /**
     * Checkout utility instance.
     *
     * @var \kanso\cms\ecommerce\Checkout
     */
    private $checkout;

    /**
     * Products utility instance.
     *
     * @var \kanso\cms\ecommerce\Products
     */
    private $products;

    /**
     * Bundles utility instance.
     *
     * @var \kanso\cms\ecommerce\Bundles
     */
    private $bundles;

    /**
     * Cart utility instance.
     *
     * @var \kanso\cms\ecommerce\ShoppingCart
     */
    private $cart;

    /**
     * Rewards utility instance.
     *
     * @var \kanso\cms\ecommerce\Rewards
     */
    private $rewards;

    /**
     * Coupons utility instance.
     *
     * @var \kanso\cms\ecommerce\Coupons
     */
    private $coupons;

    /**
     * Reviews utility instance.
     *
     * @var \kanso\cms\ecommerce\Reviews
     */
    private $reviews;

    /**
     * View include helper methods.
     *
     * @var \kanso\cms\ecommerce\helpers\Helpers
     */
    private $helpers;

    /**
     * Constructor.
     *
     * @param \kanso\cms\ecommerce\BrainTree       $braintree BrainTree utility
     * @param \kanso\cms\ecommerce\Checkout        $checkout  Checkout utility
     * @param \kanso\cms\ecommerce\Products        $products  Products utility
     * @param \kanso\cms\ecommerce\Bundles         $bundles   Bundles utility
     * @param \kanso\cms\ecommerce\ShoppingCart    $cart      Shopping cart utility
     * @param \kanso\cms\ecommerce\Rewards         $rewards   Rewards utility
     * @param \kanso\cms\ecommerce\Coupons         $coupons   Coupons utility
     * @param \kanso\cms\ecommerce\Reviews         $reviews   Reviews utility
     * @param \kanso\cms\ecommerce\helpers\Helpers $helpers   View include helper methods
     */
    public function __construct(BrainTree $braintree, Checkout $checkout, Products $products, Bundles $bundles, ShoppingCart $cart, Rewards $rewards, Coupons $coupons, Reviews $reviews, Helpers $helpers)
    {
        $this->braintree = $braintree;

        $this->checkout = $checkout;

        $this->products = $products;

        $this->bundles = $bundles;

        $this->cart = $cart;

        $this->rewards = $rewards;

        $this->coupons = $coupons;

        $this->reviews = $reviews;

        $this->helpers = $helpers;

        $this->registerPostType();

        $this->addRoutes();

        $this->customizeAdminPanel();

        $this->registerViewIncludes();
    }

    /**
     * Returns braintree instance.
     *
     * @return \kanso\cms\ecommerce\BrainTree
     */
    public function braintree(): BrainTree
    {
        return $this->braintree;
    }

    /**
     * Returns checkout instance.
     *
     * @return \kanso\cms\ecommerce\Checkout
     */
    public function checkout(): Checkout
    {
        return $this->checkout;
    }

    /**
     * Returns products instance.
     *
     * @return \kanso\cms\ecommerce\Products
     */
    public function products(): Products
    {
        return $this->products;
    }

    /**
     * Returns bundles instance.
     *
     * @return \kanso\cms\ecommerce\Bundles
     */
    public function bundles(): Bundles
    {
        return $this->bundles;
    }

    /**
     * Returns cart instance.
     *
     * @return \kanso\cms\ecommerce\ShoppingCart
     */
    public function cart(): ShoppingCart
    {
        return $this->cart;
    }

    /**
     * Returns rewards instance.
     *
     * @return \kanso\cms\ecommerce\Rewards
     */
    public function rewards(): Rewards
    {
        return $this->rewards;
    }

    /**
     * Returns coupons instance.
     *
     * @return \kanso\cms\ecommerce\Coupons
     */
    public function coupons(): Coupons
    {
        return $this->coupons;
    }

    /**
     * Returns reviews instance.
     *
     * @return \kanso\cms\ecommerce\Reviews
     */
    public function reviews(): Reviews
    {
        return $this->reviews;
    }

    /**
     * Returns helpers instance.
     *
     * @return \kanso\cms\ecommerce\helpers\Helpers
     */
    public function helpers(): Helpers
    {
        return $this->helpers;
    }

    /**
     * Apply custom routes.
     */
    private function addRoutes(): void
    {
        // Invoices for admin panel
        $this->Router->get('/admin/invoices/(:any)/', '\kanso\cms\admin\controllers\Dashboard@invoice', '\kanso\cms\admin\models\ecommerce\Invoice');

        // Products page
        $this->Router->get('/products/feed/rss/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Products');
        $this->Router->get('/products/feed/atom/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Products');
        $this->Router->get('/products/feed/rdf/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Products');
        $this->Router->get('/products/feed/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Products');
        $this->Router->get('/products/', '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Products');

        // Bundles page
        $this->Router->get('/bundles/feed/rss/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Bundles');
        $this->Router->get('/bundles/feed/atom/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Bundles');
        $this->Router->get('/bundles/feed/rdf/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Bundles');
        $this->Router->get('/bundles/feed/', '\kanso\cms\query\controllers\Rss@load', '\kanso\cms\query\models\Bundles');
        $this->Router->get('/bundles/', '\kanso\cms\query\controllers\Content@apply', '\kanso\cms\query\models\Bundles');
    }

    /**
     * Registers product post type.
     */
    private function registerPostType(): void
    {
        $this->Admin->registerPostType('Bundle', 'bundle', 'dropbox', '/bundles/(:postname)/');

        $this->Admin->registerPostType('Product', 'product', 'shopping-cart', '/products/(:category)/(:postname)/');
    }

    /**
     * Registers includes on all view renders.
     */
    private function registerViewIncludes(): void
    {
        $this->View->include(KANSO_DIR . '/cms/ecommerce/helpers/Includes.php');
    }

    /**
     * Add e-commerce page.
     */
    private function customizeAdminPanel(): void
    {
        $this->Admin->addPage('E-commerce', 'e-commerce', 'shopping-basket', '\kanso\cms\admin\models\ecommerce\Orders', KANSO_DIR . '/cms/admin/views/dash-ecommerce-orders.php', null, true);

        $this->Admin->addPage('Orders', 'orders', 'truck', '\kanso\cms\admin\models\ecommerce\Orders', KANSO_DIR . '/cms/admin/views/dash-ecommerce-orders.php', 'e-commerce', true);

        $this->Admin->addPage('Customers', 'customers', 'user', '\kanso\cms\admin\models\ecommerce\Customers', KANSO_DIR . '/cms/admin/views/dash-ecommerce-customers.php', 'e-commerce', true);

        $this->Admin->addPage('Coupons', 'coupons', 'ticket', '\kanso\cms\admin\models\ecommerce\Coupons', KANSO_DIR . '/cms/admin/views/dash-ecommerce-coupons.php', 'e-commerce', true);

        $this->Admin->addPage('Configuration', 'configuration', 'cog', '\kanso\cms\admin\models\ecommerce\Config', KANSO_DIR . '/cms/admin/views/dash-ecommerce-config.php', 'e-commerce', true);
    }
}
