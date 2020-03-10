<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\cart\items;

/**
 * Shopping cart discount item.
 *
 * @author Joe J. Howard
 */
class CartDiscount extends item implements \ArrayAccess
{
    /**
     * Default data.
     *
     * @var array
     */
    protected $defaults =
    [
        'amount'      => 0.00,
        'name'        => '',
        'description' => '',
        'type'        => 'percentage',
    ];

    /**
     * Returns the discount amount (ether percentage or fixed).
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}
