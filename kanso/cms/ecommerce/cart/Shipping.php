<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\cart;

use kanso\cms\ecommerce\cart\items\CartBundleBogo;
use kanso\cms\ecommerce\cart\items\CartBundleGroup;
use kanso\framework\utility\Money;

/**
 * Cart Shipping Calculator.
 *
 * @author Joe J. Howard
 */
class Shipping
{
    /**
     * Shipping options.
     *
     * @var array
     */
    private $options =
    [
        'is_free'        => false,
        'is_flat_rate'   => false,
        'free_threshold' => 99.99,
        'flat_rate'      => 9.95,
        'weight_rates'   =>
        [
            [
                'max_weight' => 500,
                'price'      => 9.95,
            ],
            [
                'max_weight' => 1000,
                'price'      => 13.95,
            ],
            [
                'max_weight' => 3000,
                'price'      => 16.95,
            ],
            [
                'max_weight' => 1000000000,
                'price'      => 19.95,
            ],
        ],
    ];

    /**
     * Constructor.
     *
     * @param array $options Array of configuration options (optional) (default [])
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $k => $v)
        {
            $this->options[$k] = $v;
        }
    }

    /**
     * Returns the free shipping threshold.
     *
     * @return float
     */
    public function freeThreshold(): float
    {
        return $this->options['free_threshold'];
    }

    /**
     * Calculates and returns the shipping cost.
     *
     * @param  array $items     Array of cart items
     * @param  float $cartTotal The cart subtotal (including discounts)
     * @return float
     */
    public function cost(array $items, float $cartTotal): float
    {
        if ($this->options['is_free'] === true || empty($items) || $cartTotal === 0)
        {
            return 0.00;
        }

        $itemsShipFree = true;

        foreach ($items as $item)
        {
            if (!isset($item->free_shipping) || $item->free_shipping === null || $item->free_shipping === false)
            {
                $itemsShipFree = false;
            }
        }

        if ($itemsShipFree === true)
        {
            return 0.00;
        }

        if ($this->options['is_flat_rate'] === true)
        {
            if ($cartTotal > $this->options['free_threshold'])
            {
                return 0.00;
            }

            return Money::float($this->options['flat_rate']);
        }

        $weight = 0;

        foreach ($items as $item)
        {
            if ($item instanceof CartBundleGroup || $item instanceof CartBundleBogo)
            {
                $weight += $item->weight();
            }
            if (isset($item->weight) && is_int($item->weight))
            {
                $weight += $item->weight;
            }
        }

        foreach ($this->options['weight_rates'] as $rate)
        {
            if ($weight < $rate['max_weight'])
            {
                return $rate['price'];
            }
        }

        return $this->options['weight_rates'][0]['price'];
    }
}
