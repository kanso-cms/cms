<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\ecommerce\BrainTree;
use kanso\cms\ecommerce\Cart;
use kanso\cms\ecommerce\Checkout;
use kanso\cms\ecommerce\Coupons;
use kanso\cms\ecommerce\Ecommerce;
use kanso\cms\ecommerce\Products;
use kanso\cms\ecommerce\Reviews;
use kanso\cms\ecommerce\Rewards;
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
	public function register()
	{
		$this->container->setInstance('Ecommerce', new Ecommerce($this->getBrainTree(), $this->getCheckout(), $this->getProducts(), $this->getShoppingCart(), $this->getRewards(), $this->getCoupons(), $this->getReviews()));
	}

    /**
     * Create and return new checkout utility instance.
     *
     * @access private
     * @return \kanso\cms\ecommerce\BrainTree
     */
    private function getBrainTree(): BrainTree
    {
        return new BrainTree;
    }

    /**
     * Create and return new checkout utility instance.
     *
     * @access private
     * @return \kanso\cms\ecommerce\Checkout
     */
    private function getCheckout(): Checkout
    {
        return new Checkout;
    }

    /**
     * Create and return new products utility instance.
     *
     * @access private
     * @return \kanso\cms\ecommerce\Products
     */
    private function getProducts(): Products
    {
        return new Products;
    }

    /**
     * Create and return new shopping cart utility instance.
     *
     * @access private
     * @return \kanso\cms\ecommerce\Cart
     */
    private function getShoppingCart(): Cart
    {
        return new Cart;
    }

    /**
     * Create and return new rewards utility instance.
     *
     * @access private
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
     * @access private
     * @return \kanso\cms\ecommerce\Coupons
     */
    private function getCoupons(): Coupons
    {
        return new Coupons;
    }

    /**
     * Create and return new reviews utility instance.
     *
     * @access private
     * @return \kanso\cms\ecommerce\Reviews
     */
    private function getReviews(): Reviews
    {
        return new Reviews;
    }
}
