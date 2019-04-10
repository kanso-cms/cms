<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

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
     * Cart utility instance.
     *
     * @var \kanso\cms\ecommerce\Cart
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
     * Constructor.
     *
     * @access public
     * @param \kanso\cms\ecommerce\BrainTree $braintree BrainTree utility
     * @param \kanso\cms\ecommerce\Checkout  $checkout  Checkout utility
     * @param \kanso\cms\ecommerce\Products  $products  Products utility
     * @param \kanso\cms\ecommerce\Cart      $cart      Shopping cart utility
     * @param \kanso\cms\ecommerce\Rewards   $rewards   Rewards utility
     * @param \kanso\cms\ecommerce\Coupons   $coupons   Coupons utility
     * @param \kanso\cms\ecommerce\Reviews   $reviews   Reviews utility
     */
    public function __construct(BrainTree $braintree, Checkout $checkout, Products $products, Cart $cart, Rewards $rewards, Coupons $coupons, Reviews $reviews)
    {
        $this->braintree = $braintree;

        $this->checkout = $checkout;

        $this->products = $products;

        $this->cart = $cart;

        $this->rewards = $rewards;

        $this->coupons = $coupons;

        $this->reviews = $reviews;

        $this->addRoutes();

        $this->registerPostType();

        $this->customizeAdminPanel();
    }

    /**
     * Returns braintree instance.
     *
     * @access public
     * @return \kanso\cms\ecommerce\BrainTree
     */
    public function braintree(): BrainTree
    {
        return $this->braintree;
    }

    /**
     * Returns checkout instance.
     *
     * @access public
     * @return \kanso\cms\ecommerce\Checkout
     */
    public function checkout(): Checkout
    {
        return $this->checkout;
    }

    /**
     * Returns products instance.
     *
     * @access public
     * @return \kanso\cms\ecommerce\Products
     */
    public function products(): Products
    {
        return $this->products;
    }

    /**
     * Returns cart instance.
     *
     * @access public
     * @return \kanso\cms\ecommerce\Cart
     */
    public function cart(): Cart
    {
        return $this->cart;
    }

    /**
     * Returns rewards instance.
     *
     * @access public
     * @return \kanso\cms\ecommerce\Rewards
     */
    public function rewards(): Rewards
    {
        return $this->rewards;
    }

    /**
     * Returns coupons instance.
     *
     * @access public
     * @return \kanso\cms\ecommerce\Coupons
     */
    public function coupons(): Coupons
    {
        return $this->coupons;
    }

    /**
     * Returns reviews instance.
     *
     * @access public
     * @return \kanso\cms\ecommerce\Reviews
     */
    public function reviews(): Reviews
    {
        return $this->reviews;
    }

    /**
     * Apply custom routes.
     *
     * @access private
     */
    private function addRoutes()
    {
        $this->Router->get('/admin/invoices/(:any)/', '\app\controllers\get\Admin@invoice', '\kanso\cms\admin\models\ecommerce\Invoice');
    }

    /**
     * Registers product post type.
     *
     * @access private
     */
    private function registerPostType()
    {
        $this->Admin->registerPostType('Product', 'product', 'shopping-cart', '/products/(:category)/(:postname)/');
    }

    /**
     * Add e-commerce page.
     *
     * @access private
     */
    private function customizeAdminPanel()
    {
        $this->Admin->addPage('E-commerce', 'e-commerce', 'shopping-basket', '\kanso\cms\admin\models\ecommerce\Orders', KANSO_DIR . '/cms/admin/views/dash-ecommerce-orders.php', null, true);

        $this->Admin->addPage('Orders', 'orders', 'truck', '\kanso\cms\admin\models\ecommerce\Orders', KANSO_DIR . '/cms/admin/views/dash-ecommerce-orders.php', 'e-commerce', true);

        $this->Admin->addPage('Customers', 'customers', 'user', '\kanso\cms\admin\models\ecommerce\Customers', KANSO_DIR . '/cms/admin/views/dash-ecommerce-customers.php', 'e-commerce', true);

        $this->Admin->addPage('Coupons', 'coupons', 'ticket', '\kanso\cms\admin\models\ecommerce\Coupons', KANSO_DIR . '/cms/admin/views/dash-ecommerce-coupons.php', 'e-commerce', true);

        //$this->Admin->addPage('Configuration', 'configuration', 'cog', '\kanso\cms\admin\models\ecommerce\Config', KANSO_DIR.'/cms/admin/views/dash-ecommerce-config.php', 'e-commerce', true);
    }
}
