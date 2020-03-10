<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\cart\items;

/**
 * Shopping cart fee item.
 *
 * @author Joe J. Howard
 */
class CartFee extends Item implements \ArrayAccess
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
    ];

    /**
     * Returns the fee amount.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}
