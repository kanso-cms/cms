<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\ecommerce\BrainTree;
use kanso\cms\ecommerce\Bundles;
use kanso\cms\ecommerce\cart\Shipping;
use kanso\cms\ecommerce\Checkout;
use kanso\cms\ecommerce\Coupons;
use kanso\cms\ecommerce\Ecommerce;
use kanso\cms\ecommerce\helpers\Helpers;
use kanso\cms\ecommerce\Products;
use kanso\cms\ecommerce\Reviews;
use kanso\cms\ecommerce\Rewards;
use kanso\cms\ecommerce\ShoppingCart;
use kanso\framework\application\services\Service;

/**
 * CMS Optional Ecommerce Service.
 *
 * @author Joe J. Howard
 */
class EcommerceService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->setInstance('Ecommerce', new Ecommerce($this->getBrainTree(), $this->getCheckout(), $this->getProducts(), $this->getBundles(), $this->getShoppingCart(), $this->getRewards(), $this->getCoupons(), $this->getReviews(), $this->getHelpers()));
	}

    /**
     * Create and return new checkout utility instance.
     *
     * @return \kanso\cms\ecommerce\cart\Shipping
     */
    private function getShipping(): Shipping
    {
        return new Shipping($this->container->Config->get('ecommerce.shipping'));
    }

    /**
     * Create and return new checkout utility instance.
     *
     * @return \kanso\cms\ecommerce\BrainTree
     */
    private function getBrainTree(): BrainTree
    {
        return new BrainTree;
    }

    /**
     * Create and return new checkout utility instance.
     *
     * @return \kanso\cms\ecommerce\Checkout
     */
    private function getCheckout(): Checkout
    {
        return new Checkout;
    }

    /**
     * Create and return new products utility instance.
     *
     * @return \kanso\cms\ecommerce\Products
     */
    private function getProducts(): Products
    {
        return new Products;
    }

    /**
     * Create and return new bundles utility instance.
     *
     * @return \kanso\cms\ecommerce\Bundles
     */
    private function getBundles(): Bundles
    {
        return new Bundles;
    }

    /**
     * Create and return new shopping cart utility instance.
     *
     * @return \kanso\cms\ecommerce\ShoppingCart
     */
    private function getShoppingCart(): ShoppingCart
    {
        return new ShoppingCart($this->getShipping(), $this->container->Session, $this->container->Config->get('ecommerce'));
    }

    /**
     * Create and return new rewards utility instance.
     *
     * @return \kanso\cms\ecommerce\Rewards
     */
    private function getRewards(): Rewards
    {
        $config = $this->container->Config->get('ecommerce');

        return new Rewards($config['dollars_to_points'], $config['points_to_discount']);
    }

    /**
     * Create and return new Coupons utility instance.
     *
     * @return \kanso\cms\ecommerce\Coupons
     */
    private function getCoupons(): Coupons
    {
        return new Coupons;
    }

    /**
     * Create and return new reviews utility instance.
     *
     * @return \kanso\cms\ecommerce\Reviews
     */
    private function getReviews(): Reviews
    {
        return new Reviews;
    }

    /**
     * Create and return new reviews utility instance.
     *
     * @return \kanso\cms\ecommerce\helpers\Helpers
     */
    private function getHelpers(): Helpers
    {
        return new Helpers($this->container, $this->container->Query);
    }
}
