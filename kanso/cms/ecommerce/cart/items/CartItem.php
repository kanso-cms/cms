<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\cart\items;

use kanso\framework\utility\Money;

/**
 * Shopping cart item.
 *
 * @author Joe J. Howard
 */
class CartItem extends Item implements \ArrayAccess
{
    protected $defaults =
    [
        'quantity'      => 1,
        'price'         => 0.00,
        'tax'           => 0.00,
        'options'       => [],
        'free_shipping' => false,
        'weight'        => 0,
    ];

    /**
     * Get the total price of the cart item including tax.
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return Money::float($this->price * $this->quantity);
    }

    /**
     * Get the total price of the cart item excluding tax.
     *
     * @return float
     */
    public function getTotalPriceExcludingTax()
    {
        return Money::float($this->getTotalPrice() - $this->getTotalTax());
    }

    /**
     * Get the single price of the cart item including tax.
     *
     * @return float
     */
    public function getSinglePrice()
    {
        return Money::float($this->price);
    }

    /**
     * Get the single price of the cart item excluding tax.
     *
     * @return float
     */
    public function getSinglePriceExcludingTax()
    {
        return Money::float($this->price - $this->getSingleTax());

    }

    /**
     * Get the total tax for the cart item.
     *
     * @return float
     */
    public function getTotalTax()
    {
        return Money::float($this->getSingleTax() * $this->quantity);
    }

    /**
     * Get the single tax value of the cart item.
     *
     * @return float
     */
    public function getSingleTax()
    {
        return Money::float(($this->tax / 100) * $this->price);
    }
}
