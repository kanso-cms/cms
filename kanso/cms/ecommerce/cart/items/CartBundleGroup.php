<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\cart\items;

use kanso\framework\utility\Money;

/**
 * Shopping cart group bundle item.
 *
 * @author Joe J. Howard
 */
class CartBundleGroup extends Item implements \ArrayAccess
{
    /**
     * Default data.
     *
     * @var array
     */
    protected $defaults =
    [
        'quantity'       => 1,
        'price'          => 0.00,
        'tax'            => 0.00,
        'discount'       => 0,
        'override_cents' => false,
        'free_shipping'  => false,
        'items'          => [],
    ];

    /**
     * All items in the bundle.
     *
     * @return array
     */
    public function items(): array
    {
        return array_values($this->items);
    }

    /**
     * Returns total amount of items in the bundle.
     *
     * @return int
     */
    public function totalUniqueItems(): int
    {
        return count($this->items);
    }

    /**
     * Returns total amount of unique items in the bundle.
     *
     * @return int
     */
    public function totalItems(): int
    {
        $count = array_sum(array_map(function(CartItem $item)
        {
            return $item->quantity;

        }, $this->items));

        return intval($count * $this->quantity);
    }

    /**
     * Returns the total savings of the bundle.
     *
     * @return float
     */
    public function getTotalDiscount(): float
    {
        $total = Money::float(array_sum(array_map(function(CartItem $item)
        {
            return $item->getTotalPrice();

        }, $this->items)) * $this->quantity);

        return Money::float($this->getTotalPrice() - $total);
    }

    /**
     * Returns the total price of the bundle(s).
     *
     * @return float
     */
    public function getTotalPrice(): float
    {
        if ($this->price > 0)
        {
            return Money::float($this->price * $this->quantity);
        }

        return Money::float($this->getSinglePrice() * $this->quantity);
    }

    /**
     * Returns the total price of the bundle(s) excluding tax.
     *
     * @return float
     */
    public function getTotalPriceExcludingTax(): float
    {
        return Money::float($this->getTotalPrice() - $this->getTotalTax());
    }

    /**
     * Returns the price of 1 bundle.
     *
     * @return float
     */
    public function getSinglePrice(): float
    {
        if ($this->price > 0)
        {
            return Money::float($this->price * $this->quantity);
        }

        $total = Money::float(array_sum(array_map(function(CartItem $item)
        {
            return $item->getTotalPrice();

        }, $this->items)));

        $total = ($total - ($this->discount / 100) * $total);

        if ($this->override_cents)
        {
            $total = Money::float(strtok(strval($total), '.') . '.' . $this->override_cents);
        }

        return $total;
    }

    /**
     * Returns the price of 1 bundle excluding tax.
     *
     * @return float
     */
    public function getSinglePriceExcludingTax(): float
    {
        return Money::float($this->getSinglePrice() - $this->getSingleTax());
    }

    /**
     * Returns the total tax on the bundle.
     *
     * @return float
     */
    public function getTotalTax(): float
    {
        return Money::float($this->getSingleTax() * $this->quantity);
    }

    /**
     * Returns the tax of 1 bundle.
     *
     * @return float
     */
    public function getSingleTax(): float
    {
        return Money::float(($this->tax / 100) * $this->getSinglePrice());
    }

    /**
     * Returns the total weight of the bundle.
     *
     * @return int
     */
    public function weight(): int
    {
        $weight = 0;

        foreach ($this->items() as $item)
        {
            $weight += $item->weight;
        }

        return $weight;
    }
}
