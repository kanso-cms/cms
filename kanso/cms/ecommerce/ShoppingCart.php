<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use kanso\cms\ecommerce\cart\items\CartBundleBogo;
use kanso\cms\ecommerce\cart\items\CartBundleGroup;
use kanso\cms\ecommerce\cart\items\CartDiscount;
use kanso\cms\ecommerce\cart\items\CartFee;
use kanso\cms\ecommerce\cart\items\CartItem;
use kanso\cms\ecommerce\cart\Shipping;
use kanso\framework\http\session\Session;
use kanso\framework\utility\Money;

/**
 * Shopping cart.
 *
 * @author Joe J. Howard
 */
class ShoppingCart
{
    /**
     * Configuration options.
     *
     * @var array
     */
    private $options =
    [
        'id'               => 'kanso_shopping_cart',
        'multiple_coupons' => false,
        'tax'              => 10,
        'currency'         => 'AUD',
    ];

    /**
     * Items in the cart.
     *
     * @var array
     */
    private $items = [];

    /**
     * Fees applied to the cart.
     *
     * @var array
     */
    private $fees = [];

    /**
     * Discounts applied to the cart.
     *
     * @var array
     */
    private $discounts = [];

    /**
     * Session utility.
     *
     * @var \kanso\framework\http\session\Session
     */
    private $session;

    /**
     * Shipping utility.
     *
     * @var \kanso\cms\ecommerce\cart\Shipping
     */
    private $shipping;

    /**
     * Are we currently reading from the session?
     *
     * @var bool
     */
    private $reading = false;

    /**
     * Constructor.
     *
     * @param \kanso\cms\ecommerce\cart\Shipping    $shipping Shipping calculator
     * @param \kanso\framework\http\session\Session $session  Session instance
     * @param array                                 $options  Array of configuration options (optional) (default [])
     */
    public function __construct(Shipping $shipping, Session $session, array $options = [])
    {
        $this->session = $session;

        $this->shipping = $shipping;

        foreach ($options as $k => $v)
        {
            $this->options[$k] = $v;
        }

        $this->readSession();

    }

    /**
     * Retrieve the cart id.
     *
     * @return string
     */
    public function id(): string
    {
        return $this->options['id'];
    }

    /**
     * Checks if the cart is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Checks if the cart contains any discounts.
     *
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return !empty($this->discounts);
    }

    /**
     * Checks if the cart contains any bundle.
     *
     * @return bool
     */
    public function hasBundle(): bool
    {
        foreach ($this->items as $item)
        {
            if ($item instanceof CartBundleBogo || $item instanceof CartBundleGroup)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns any array of all items in the cart.
     *
     * @return array
     */
    public function items(): array
    {
        $items = [];

        foreach ($this->items as $item)
        {
            if ($item instanceof CartItem)
            {
                $items = $this->mergeCartItems($items, clone $item);
            }
            elseif ($item instanceof CartBundleBogo || $item instanceof CartBundleGroup)
            {
                foreach ($item->items() as $item)
                {
                    $items = $this->mergeCartItems($items, clone $item);
                }
            }
        }

        usort($items, function($a, $b)
        {
            return strcmp($a->name, $b->name);

        });

        return $items;
    }

    /**
     * Returns all fees.
     *
     * @return array
     */
    public function fees(): array
    {
        return $this->fees;
    }

    /**
     * Returns all discounts.
     *
     * @return array
     */
    public function discounts(): array
    {
        return $this->discounts;
    }

    /**
     * Remove all items from the cart.
     */
    public function clear(): void
    {
        $this->items = [];

        $this->discounts = [];

        $this->fees = [];

        $this->session->remove($this->options['id']);
    }

    /**
     * Add an item to the cart.
     *
     * @param  string                                   $name         Name of the item (e.g "Black T shirt")
     * @param  string                                   $variant      Variant description (e.g "Large")
     * @param  int                                      $productId    The product unique id (e.g 13)
     * @param  string                                   $sku          Item SKU ID (e.g "119203")
     * @param  int                                      $qty          Item Quantity
     * @param  float                                    $price        Item price (of 1)
     * @param  int                                      $weight       Item weight (optional) (default 0)
     * @param  bool                                     $freeShipping Does the item ship free? (optional) (default false)
     * @param  array                                    $options      Array of options (optional) (default)
     * @return \kanso\cms\ecommerce\cart\items\CartItem
     */
    public function add(string $name, string $variant, int $productId, string $sku, int $qty, float $price, int $weight = 0, bool $freeShipping = false, array $options = []): CartItem
    {
        return $this->addItem(new CartItem(
        [
            'name'        => $name,
            'variant'     => $variant,
            'product_id'  => $productId,
            'sku'         => $sku,
            'quantity'    => $qty,
            'price'       => $price,
            'tax'         => $this->options['tax'],
            'options'     => $options,
        ]));
    }

    /**
     * Add a group bundle to the cart.
     *
     * @param  array                                           $items          Array of items to add. Should be an array CartItem or an array of ['name','sku','quantity','price', 'options']
     * @param  string                                          $name           Group bundle name
     * @param  float                                           $price          Fixed price. Can be set to 0.00 if using a discount percentage
     * @param  int                                             $discount       Discount as percentage to apply
     * @param  int                                             $override_cents Override cents when using a discount percentage
     * @param  int                                             $quantity       Quantity to add (optional) (default 1)
     * @param  bool                                            $freeShipping   Does the item ship free? (optional) (default false)
     * @return \kanso\cms\ecommerce\cart\items\CartBundleGroup
     */
    public function addGroup(array $items, string $name, float $price, int $discount, int $override_cents, int $quantity = 1, bool $freeShipping = false): CartBundleGroup
    {
        $cartItems = [];

        foreach ($items as $item)
        {
            if ($item instanceof CartItem)
            {
                $cartItems[] = $item;

                continue;
            }

            $cartItems[] = new CartItem(
            [
                'name'       => $item['name'],
                'variant'    => $item['variant'],
                'product_id' => $item['product_id'],
                'sku'        => $item['sku'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'tax'        => $this->options['tax'],
                'options'    => $item['options'] ?? [],
            ]);
        }

        $bundle = new CartBundleGroup(
        [
            'name'           => $name,
            'quantity'       => $quantity,
            'price'          => $price,
            'tax'            => $this->options['tax'],
            'discount'       => $discount,
            'override_cents' => $override_cents,
            'items'          => $cartItems,
        ]);

        return $this->addItem($bundle);
    }

    /**
     * Add a BOGO bundle to the cart.
     *
     * @param  array                                          $itemsIn      Array of items to add. Should be an array CartItem or an array of ['name','sku','quantity','price', 'options']
     * @param  array                                          $itemsOut     Array of free items to add. Should be an array CartItem or an array of ['name','sku','quantity','price', 'options']
     * @param  string                                         $name         Bundle name
     * @param  int                                            $quantity     Quantity to add (optional) (default 1)
     * @param  bool                                           $freeShipping Does the item ship free? (optional) (default false)
     * @return \kanso\cms\ecommerce\cart\items\CartBundleBogo
     */
    public function addBogo(array $itemsIn, array $itemsOut, string $name, int $quantity = 1, bool $freeShipping = false): CartBundleBogo
    {
        $cartItemsIn = [];

        foreach ($itemsIn as $item)
        {
            if ($item instanceof CartItem)
            {
                $cartItemsIn[] = $item;

                continue;
            }
            $cartItemsIn[] = new CartItem(
            [
                'name'       => $item['name'],
                'variant'    => $item['variant'],
                'product_id' => $item['product_id'],
                'sku'        => $item['sku'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'tax'        => $this->options['tax'],
                'options'    => $item['options'] ?? [],
            ]);
        }

        $cartItemsOut = [];

        foreach ($itemsOut as $item)
        {
            if ($item instanceof CartItem)
            {
                $cartItemsOut[] = $item;

                continue;
            }

            $cartItemsOut[] = new CartItem(
            [
                'name'       => $item['name'],
                'variant'    => $item['variant'],
                'product_id' => $item['product_id'],
                'sku'        => $item['sku'],
                'quantity'   => $item['quantity'],
                'options'    => $item['options'] ?? [],
                'price'      => 0.00,
                'tax'        => 0.00,
            ]);
        }

        $bundle = new CartBundleBogo(
        [
            'name'           => $name,
            'quantity'       => $quantity,
            'items_in'       => $cartItemsIn,
            'items_out'      => $cartItemsOut,
        ]);

        return $this->addItem($bundle);
    }

    /**
     * Add a fee to the cart.
     *
     * @param  float                                   $amount Fee amount
     * @param  string                                  $name   Fee name
     * @param  string                                  $desc   Free description
     * @return \kanso\cms\ecommerce\cart\items\CartFee
     */
    public function addFee(float $amount, string $name, string $desc): CartFee
    {
        return $this->addFeeItem(new CartFee(
        [
            'name'        => $name,
            'description' => $desc,
            'amount'      => $amount,
        ]));
    }

    /**
     * Add a CartFee to the cart.
     *
     * @param  \kanso\cms\ecommerce\cart\items\CartFee $fee The fee item to add
     * @return \kanso\cms\ecommerce\cart\items\CartFee
     */
    private function addFeeItem(CartFee $fee): CartFee
    {
        $feeId = $fee->id;

        if ($this->getFee($feeId))
        {
            $this->removeFee($feeId);
        }

        $this->fees[] = $fee;

        $this->save();

        return $fee;
    }

    /**
     * Add a discount to the cart.
     *
     * @param  float                                        $amount Discount amount (percentage or fixed)
     * @param  string                                       $name   Discount name
     * @param  string                                       $desc   Discount description
     * @param  string                                       $type   Discount type (optional) (default 'percentage') ('percentage'|'fixed')
     * @return \kanso\cms\ecommerce\cart\items\CartDiscount
     */
    public function addDiscount(float $amount, string $name, string $desc, string $type = 'percentage'): CartDiscount
    {
        return $this->addDiscountItem(new CartDiscount(
        [
            'name'        => $name,
            'description' => $desc,
            'amount'      => $amount,
            'type'        => $type,
        ]));
    }

    /**
     * Add a CartDiscount to the cart.
     *
     * @param  \kanso\cms\ecommerce\cart\items\CartDiscount $discount The discount item to add
     * @return \kanso\cms\ecommerce\cart\items\CartDiscount
     */
    public function addDiscountItem(CartDiscount $discount): CartDiscount
    {
        $discountId = $discount->id;

        if (!$this->options['multiple_coupons'])
        {
            $this->discounts = [$discount];
        }
        else
        {
            if ($this->getDiscount($discountId))
            {
                $this->removeDiscount($discountId);
            }

            $this->discounts[] = $discount;
        }

        $this->save();

        return $discount;
    }

    /**
     * Get a cart item by id.
     *
     * @param  string                                                                                                                                       $id Item id
     * @return null|\kanso\cms\ecommerce\cart\items\CartItem|\kanso\cms\ecommerce\cart\items\CartBundleGroup|\kanso\cms\ecommerce\cart\items\CartBundleBogo
     */
    public function get(string $id)
    {
        foreach ($this->items as $item)
        {
            if ($item->id === $id)
            {
                return $item;
            }

            if ($item instanceof CartBundleGroup || $item instanceof CartBundleBogo)
            {
                foreach ($item->items() as $_item)
                {
                    if ($_item->id === $id)
                    {
                        return $_item;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get a fee by id.
     *
     * @param  string                                       $id Fee item id
     * @return null|\kanso\cms\ecommerce\cart\items\CartFee
     */
    public function getFee(string $id)
    {
        foreach ($this->fees as $fee)
        {
            if ($id === $fee->id)
            {
                return $fee;
            }
        }

        return null;
    }

    /**
     * Get a discount by id.
     *
     * @param  string                                            $id Discount item id
     * @return null|\kanso\cms\ecommerce\cart\items\CartDiscount
     */
    public function getDiscount(string $id)
    {
        foreach ($this->discounts as $discount)
        {
            if ($id === $discount->id)
            {
                return $discount;
            }
        }

        return null;
    }

    /**
     * Remove an item from the cart.
     *
     * @param  string $id Item id
     * @return bool
     */
    public function remove(string $id): bool
    {
        foreach ($this->items as $i => $item)
        {
            if ($item->id === $id)
            {
                unset($this->items[$i]);

                $this->items = array_values($this->items);

                $this->save();

                return true;
            }

            if ($item instanceof CartBundleGroup || $item instanceof CartBundleBogo)
            {
                foreach ($item->items() as $_item)
                {
                    if ($_item->id === $id)
                    {
                        return $this->breakBundle($item->id, $_item->id);
                    }
                }
            }
        }

        return false;
    }

    /**
     * Remove a fee from the cart.
     *
     * @param  string $id Item id
     * @return bool
     */
    public function removeFee(string $id): bool
    {
        foreach ($this->fees as $i => $item)
        {
            if ($item->id === $id)
            {
                unset($this->fees[$i]);

                $this->fees = array_values($this->fees);

                $this->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Remove a discount from the cart.
     *
     * @param  string $id Item hash
     * @return bool
     */
    public function removeDiscount(string $id): bool
    {
        foreach ($this->discounts as $i => $item)
        {
            if ($item->id === $id)
            {
                unset($this->discounts[$i]);

                $this->discounts = array_values($this->discounts);

                $this->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the cart contains an item.
     *
     * @param  string $id The item's id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->get($id) === null ? false : true;
    }

    /**
     * Update an item in the cart.
     *
     * @param  string                                                                                                                                           $id    Item id
     * @param  string                                                                                                                                           $key   Property name to update
     * @param  mixed                                                                                                                                            $value The value to set
     * @return ?\kanso\cms\ecommerce\cart\items\CartBundleBogo|?\kanso\cms\ecommerce\cart\items\CartBundleGroup|?\kanso\cms\ecommerce\cart\items\CartItem|false
     */
    public function update(string $id, string $key, $value)
    {
        $item = $this->get($id);

        if (!$item)
        {
            return false;
        }

        $item->$key = $value;

        $newId = $item->id;

        if ($this->has($newId))
        {
            $origionalItem = $this->get($newId);

            $origionalItem->quantity += $item->quantity;

            $this->remove($id);

            $item = $origionalItem;
        }

        $this->save();

        return $item;
    }

    /**
     * Increment an item in the cart by 1.
     *
     * @param  string $id The item id
     * @return bool
     */
    public function increment(string $id): bool
    {
        $item = $this->get($id);

        if (!$item)
        {
            return false;
        }

        foreach ($this->items as $item)
        {
            if ($item->id === $id && $item instanceof CartItem)
            {
                $item->quantity++;

                $this->save();

                return true;
            }

            if ($item instanceof CartBundleBogo || $item instanceof CartBundleGroup)
            {
                foreach ($item->items() as $_item)
                {
                    if ($_item->id === $id)
                    {
                        $this->addItem(clone $_item);

                        return true;
                    }
                }
            }
        }
    }

    /**
     * Decrement an item in the cart by 1.
     *
     * @param  string $id Item id
     * @return bool
     */
    public function decrement(string $id): bool
    {
        $item = $this->get($id);

        if (!$item)
        {
            return false;
        }

        foreach ($this->items as $item)
        {
            if ($item->id === $id)
            {
                if ($item->quantity > 1)
                {
                    $item->quantity--;

                    $this->save();

                    return true;
                }
                else
                {
                    $this->remove($id);

                    return true;
                }
            }

            if ($item instanceof CartBundleBogo || $item instanceof CartBundleGroup)
            {
                foreach ($item->items() as $_item)
                {
                    if ($_item->id === $id)
                    {
                        $this->breakBundle($item->id, $_item->id);

                        $this->save();

                        return true;
                    }
                }
            }
        }
    }

    /**
     * Get the total number of items in the cart.
     *
     * @return int
     */
    public function totalItems(): int
    {
        $count = 0;

        foreach ($this->items as $item)
        {
            if ($item instanceof CartItem)
            {
                $count += $item->quantity;
            }
            elseif ($item instanceof CartBundleBogo || $item instanceof CartBundleGroup)
            {
                $count+= $item->totalItems();
            }
        }

        return $count;
    }

    /**
     * Get the total number of unique items in the cart.
     *
     * @return int
     */
    public function totalUniqueItems(): int
    {
        $count = 0;

        foreach ($this->items as $item)
        {
            if ($item instanceof CartItem)
            {
                $count++;
            }
            elseif ($item instanceof CartBundleBogo || $item instanceof CartBundleGroup)
            {
                $count+= $item->totalUniqueItems();
            }
        }

        return $count;
    }

    /**
     * Gets the cart grand total.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function total(bool $format = false)
    {
        $amount = $this->subtotal(false) + $this->totalFees(false) - $this->totalDiscounts(false);

        return !$format ? Money::float($amount) : Money::format($amount, $this->options['currency']);
    }

    /**
     * Gets the total inclusive tax on the cart.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function tax(bool $format = false)
    {
        $amount = ($this->options['tax'] / 100) * $this->total(false);

        return !$format ? Money::float($amount) : Money::format($amount, $this->options['currency']);
    }

    /**
     * Gets the cart grand total.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function totalExcludingTax(bool $format = false)
    {
        $amount = $this->total(false) - $this->tax(false);

        return !$format ? Money::float($amount) : Money::format($amount, $this->options['currency']);
    }

    /**
     * Gets the subtotal of all the items in the cart (without any fees or discounts applied).
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function subtotal(bool $format = false)
    {
        $amount = array_sum(array_map(function($item)
        {
            return $item->getTotalPrice();

        }, $this->items));

        return !$format ? Money::float($amount) : Money::format($amount, $this->options['currency']);
    }

    /**
     * Gets the subtotal of all the items in the cart (with discount).
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function subtotalWithDiscounts(bool $format = false)
    {
        $amount = $this->subtotal(false) - $this->totalDiscounts(false);

        return !$format ? Money::float($amount) : Money::format($amount, $this->options['currency']);
    }

    /**
     * Gets the total fees applied to the cart.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function totalFees(bool $format = false)
    {
        $amount = Money::float(array_sum(array_map(function(CartFee $item)
        {
            return $item->getAmount();

        }, $this->fees)));

        return !$format ? Money::float($amount) : Money::format($amount, $this->options['currency']);
    }

    /**
     * Gets the total discounts applied to the cart.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function totalDiscounts(bool $format = false)
    {
        $total   = $this->subtotal(false);
        $savings = 0;

        usort($this->discounts, function(CartDiscount $a, CartDiscount $b)
        {
            return strcmp($a->type, $b->type);
        });

        foreach ($this->discounts as $item)
        {
            if ($item->type === 'fixed')
            {
                $amount  =  $item->getAmount();
                $total   =  $total - $amount;
                $savings += $amount;
            }
            else
            {
                $savings += (($item->getAmount() / 100) * $total);
            }
        }

        return !$format ? Money::float($savings) : Money::format($savings, $this->options['currency']);
    }

    /**
     * Calculates and returns shipping cost.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function shippingCost(bool $format = false)
    {
        $amount = $this->shipping->cost($this->items, $this->subtotalWithDiscounts(false));

        return !$format ? Money::float($amount) : Money::format($amount, $this->options['currency']);
    }

    /**
     * Returns the free shipping threshold.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function freeShippingThreshold(bool $format = false)
    {
        $amount = $this->shipping->freeThreshold();

        return !$format ? Money::float($amount) : Money::format($amount, $this->options['currency']);
    }

    /**
     * Returns the currency.
     *
     * @return string
     */
    public function currency(): string
    {
        return $this->options['currency'];
    }

    /**
     * Adds a cart item to the cart.
     *
     * @param  \kanso\cms\ecommerce\cart\items\CartItem|\kanso\cms\ecommerce\cart\items\CartFee|\kanso\cms\ecommerce\cart\items\CartDiscount|\kanso\cms\ecommerce\cart\items\CartBundleGroup|\kanso\cms\ecommerce\cart\items\CartBundleBogo $item Item to add to cart
     * @return \kanso\cms\ecommerce\cart\items\CartItem|\kanso\cms\ecommerce\cart\items\CartFee|\kanso\cms\ecommerce\cart\items\CartDiscount|\kanso\cms\ecommerce\cart\items\CartBundleGroup|\kanso\cms\ecommerce\cart\items\CartBundleBogo
     */
    private function addItem($item)
    {
        foreach ($this->items as $_item)
        {
            if ($item->id === $_item->id && $item instanceof CartItem)
            {
                $_item->quantity += $item->quantity;

                $this->save();

                return $_item;
            }
        }

        $this->items[] = $item;

        $this->save();

        return $item;
    }

    /**
     * Export the cart as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'id'        => $this->options['id'],
            'items'     => array_map(function($item) { return $item->toArray(); }, $this->items),
            'fees'      => array_map(function($item) { return $item->toArray(); }, $this->fees),
            'discounts' => array_map(function($item) { return $item->toArray(); }, $this->discounts),
        ];
    }

    /**
     * Loads the cart from a saved export of the 'toArray' method.
     *
     * @param array $data      Array of cart data to load
     * @param bool  $overwrite Overwrite any existing data instead of merging (optional) (default true)
     */
    public function loadFromArray(array $data, bool $overwrite = true): void
    {
        if ($overwrite)
        {
            $this->clear();
        }

        foreach ($data['items'] as $item)
        {
            $class = $item['class'];

            $this->addItem(new $class($item['data']));
        }
        foreach ($data['fees'] as $fee)
        {
            $class = $fee['class'];

            $this->addFeeItem(new $class($fee['data']));
        }
        foreach ($data['discounts'] as $discount)
        {
            $class = $discount['class'];

            $this->addDiscountItem(new $class($discount['data']));
        }

        $this->save();
    }

    /**
     * Save the cart state.
     */
    public function save(): void
    {
        if (!$this->reading)
        {
            $this->applyShipping();
        }

        // Bundles come last so if items are incremented/decremented
        // They are done on individual items first
        usort($this->items, function($a, $b)
        {
            if ($a instanceof CartItem && $b instanceof CartItem)
            {
                return 0;
            }

            return ($a instanceof CartItem) ? -1 : 1;

        });

        $this->session->put($this->options['id'], $this->toArray());
    }

    /**
     * Calculates and applies the shipping fee.
     *
     * @return float
     */
    private function applyShipping(): float
    {
        $cost = $this->shippingCost(false);

        if (empty($this->fees))
        {
            $this->addFee($cost, 'Shipping', 'Shipping and handling.');

            return $cost;
        }
        else
        {
            foreach ($this->fees as $fee)
            {
                if (strtolower($fee->name) === 'shipping')
                {
                    $fee->amount = $cost;

                    return $cost;
                }
            }
        }

        return 0.00;
    }

    /**
     * Reads existing data from the session and loads it.
     */
    private function readSession(): void
    {
        $this->reading = true;

        $data = $this->session->get($this->options['id']);

        if ($data)
        {
            foreach ($data['items'] as $item)
            {
                $class = $item['class'];

                $this->addItem(new $class($item['data']));
            }
            foreach ($data['fees'] as $fee)
            {
                $class = $fee['class'];

                $this->addFeeItem(new $class($fee['data']));
            }
            foreach ($data['discounts'] as $discount)
            {
                $class = $discount['class'];

                $this->addDiscountItem(new $class($discount['data']));
            }
        }

        $this->reading = false;
    }

    /**
     * Remove an item from a bundle, which breaks the bundle
     * and moves the rest of the items over to the cart.
     *
     * @param string $bundleId    Bundle id
     * @param string $exclusionId The item being removed from the bundle
     */
    private function breakBundle(string $bundleId, string $exclusionId): bool
    {
        $bundle = $this->get($bundleId);
        $items  = $bundle->items();

        if ($bundle->quantity > 1)
        {
            $this->decrement($bundleId);
        }
        else
        {
            $this->remove($bundleId);
        }

        foreach ($items as $item)
        {
            if ($exclusionId !== $item->id && $item->price > 0)
            {
                $this->addItem(clone $item);
            }
        }

        return true;
    }

    /**
     * Merges items together in the cart if appropriate.
     *
     * @param  array                                         $cart Array of existing cart items
     * @return array|kanso\cms\ecommerce\cart\items\CartItem $item Cart item to merge
     * @return array
     */
    private function mergeCartItems(array $cart, CartItem $item): array
    {
        foreach ($cart as $i => $cartItem)
        {
            if ($cartItem->id === $item->id)
            {
                $cart[$i]->quantity += $item->quantity;

                return $cart;
            }
        }

        $cart[] = $item;

        return $cart;
    }
}
