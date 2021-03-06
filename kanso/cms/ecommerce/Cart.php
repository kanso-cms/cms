<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

/**
 * Shopping Cart Utility Model.
 *
 * @author Joe J. Howard
 */
class Cart extends UtilityBase
{
    /**
     * Get products and offers from cart
     * organized by quantity.
     *
     * @return array
     */
    public function items(): array
    {
        // Items to return
        $items = [];

        // Get entries from DB or session
        if (!$this->Gatekeeper->isLoggedIn())
        {
            $entries = $this->Session->get('shopping_cart_items');
        }
        else
        {
            $entries = $this->sql()->SELECT('*')->FROM('shopping_cart_items')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->FIND_ALL();
        }

        // Empty cart
        if ($entries)
        {
            // Get posts
            foreach ($entries as $entry)
            {
                $product = $this->PostManager->byId($entry['product_id']);

                if (!$product)
                {
                    $this->remove($entry['product_id'], $entry['offer_id']);

                    continue;
                }

                $offer = $this->Ecommerce->products()->offer($product->id, $entry['offer_id']);

                if (!$offer)
                {
                    $this->remove($entry['product_id'], $entry['offer_id']);

                    continue;
                }

                // Offer was added when it was instock but is now out of stock
                if (!$offer['instock'])
                {
                    $this->remove($entry['product_id'], $entry['offer_id']);

                    continue;
                }

                $items[] =
                [
                    'product'  => $product->id,
                    'offer'    => $offer,
                    'quantity' => $entry['quantity'],
                ];
            }
        }

        return $items;
    }

    /**
     * Is the shopping cart empty ?
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        // Get entries from DB or session
        if (!$this->Gatekeeper->isLoggedIn())
        {
            $entries = $this->Session->get('shopping_cart_items');
        }
        else
        {
            $entries = $this->sql()->SELECT('*')->FROM('shopping_cart_items')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->FIND_ALL();
        }

        return empty($entries);
    }

    /**
     * Calculate the count of total items in the cart.
     *
     * @return int
     */
    public function count(): int
    {
        $count = 0;

        // Get entries from DB or session
        if (!$this->Gatekeeper->isLoggedIn())
        {
            $entries = $this->Session->get('shopping_cart_items');
        }
        else
        {
            $entries = $this->sql()->SELECT('*')->FROM('shopping_cart_items')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->FIND_ALL();
        }

        // Empty cart
        if ($entries)
        {
            // Get posts
            foreach ($entries as $entry)
            {
                $count += $entry['quantity'];
            }
        }

        return $count;
    }

    /**
     * Clear the user's cart.
     *
     * @return bool
     */
    public function clear(): bool
    {
        if ($this->Gatekeeper->isLoggedIn())
        {
            $this->sql()->DELETE_FROM('shopping_cart_items')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->QUERY();
        }

        $this->Session->remove('shopping_cart_items');

        return true;
    }

    /**
     * Add a product offer to the user's db/session.
     *
     * @param int    $productId Product post id
     * @param string $offerId   Product offer id
     */
    public function add(int $productId, string $offerId): void
    {
        $row =
        [
            'product_id' => $productId,
            'offer_id'   => $offerId,
            'user_id'    => $this->Gatekeeper->isLoggedIn() ? $this->Gatekeeper->getUser()->id : null,
            'quantity'   => 1,
        ];

        if ($this->Gatekeeper->isLoggedIn())
        {
            $entry = $this->sql()->SELECT('*')->FROM('shopping_cart_items')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->AND_WHERE('product_id', '=', $productId)->AND_WHERE('offer_id', '=', $offerId)->ROW();

            if ($entry)
            {
                $row['quantity'] = ($entry['quantity'] + 1);

                $this->sql()->UPDATE('shopping_cart_items')->SET($row)->WHERE('id', '=', $entry['id'])->QUERY();
            }
            else
            {
                $this->sql()->INSERT_INTO('shopping_cart_items')->VALUES($row)->QUERY();
            }
        }
        else
        {
            $items = $this->Session->get('shopping_cart_items');
            $items = !$items ? [] : $items;
            $found = false;

            foreach ($items as $i => $item)
            {
                if ($item['offer_id'] === $offerId && $item['product_id'] === $productId)
                {
                    $row['quantity'] = ($item['quantity'] + 1);
                    $items[$i]       = $row;
                    $found           = true;
                    break;
                }
            }
            if (!$found)
            {
                $items[] = $row;
            }

            $this->Session->put('shopping_cart_items', $items);
        }
    }

    /**
     * Remove a product offer from the user's db/session.
     *
     * @param int    $productId Product post id
     * @param string $offerId   Product offer id (sku)
     */
    public function remove(int $productId, string $offerId): void
    {
        if ($this->Gatekeeper->isLoggedIn())
        {
            $this->sql()->DELETE_FROM('shopping_cart_items')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->AND_WHERE('product_id', '=', $productId)->AND_WHERE('offer_id', '=', $offerId)->QUERY();
        }
        else
        {
            $items = $this->Session->get('shopping_cart_items');
            $items = !$items ? [] : $items;

            foreach ($items as $i => $item)
            {
                if ($item['offer_id'] === $offerId && $item['product_id'] === $productId)
                {
                    unset($items[$i]);
                }
            }

            $this->Session->put('shopping_cart_items', $items);
        }
    }

    /**
     * Minus a product quantity from the user's cart.
     *
     * @param int    $productId Product post id
     * @param string $offerId   Product offer id (sku)
     */
    public function minus(int $productId, string $offerId): void
    {
        $row =
        [
            'product_id' => $productId,
            'offer_id'   => $offerId,
            'user_id'    => $this->Gatekeeper->isLoggedIn() ? $this->Gatekeeper->getUser()->id : null,
            'quantity'   => 1,
        ];
        if ($this->Gatekeeper->isLoggedIn())
        {
            $entry = $this->sql()->SELECT('*')->FROM('shopping_cart_items')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->AND_WHERE('product_id', '=', $productId)->AND_WHERE('offer_id', '=', $offerId)->ROW();

            if ($entry)
            {
                $row['quantity'] = ($entry['quantity'] - 1);
                if ($row['quantity'] === 0)
                {
                    $this->sql()->DELETE_FROM('shopping_cart_items')->WHERE('id', '=', $entry['id'])->QUERY();
                }
                else
                {
                    $this->sql()->UPDATE('shopping_cart_items')->SET($row)->WHERE('id', '=', $entry['id'])->QUERY();
                }
            }
        }
        else
        {
            $items = $this->Session->get('shopping_cart_items');
            $items = !$items ? [] : $items;
            foreach ($items as $i => $item)
            {
                if ($item['offer_id'] === $offerId && $item['product_id'] === $productId)
                {
                    $row['quantity'] = ($item['quantity'] - 1);

                    if ($row['quantity'] === 0)
                    {
                        unset($items[$i]);
                    }
                    else
                    {
                        $items[$i] = $row;
                    }
                }
            }

            $this->Session->put('shopping_cart_items', $items);
        }
    }

    /**
     * Calculate the cart subtotal.
     *
     * @return float
     */
    public function subTotal(): float
    {
        $items    = $this->items();
        $subtotal = 0;

        // Get posts
        foreach ($items as $item)
        {
            $subtotal += ($item['quantity'] * $item['offer']['sale_price']);
        }

        return floatval($subtotal);
    }

    /**
     * Calculate the shipping cost.
     *
     * @return float
     */
    public function shippingCost(): float
    {
        // Calculate subtotal
        $subtotal = $this->subTotal();

        if ($subtotal >= $this->Config->get('ecommerce.free_shipping_threshold'))
        {
            return 0.00;
        }

        // Does the item(s) offer free shipping ?
        $freeShippingProducts = $this->Config->get('ecommerce.free_shipping_products');

        foreach ($this->items() as $item)
        {
            if (!in_array($item['product'], $freeShippingProducts))
            {
                return $this->Config->get('ecommerce.shipping_price');
            }
        }

        return 0.00;
    }

    /**
     * Calculate the inclusive GST.
     *
     * @return float
     */
    public function gst(): float
    {
        return (10 / 100) * $this->subTotal();
    }

    /**
     * Get logged in user's stored shipping addresses.
     *
     * @return array
     */
    public function addresses(): array
    {
        if ($this->Gatekeeper->isLoggedIn())
        {
            return $this->sql()->SELECT('*')->FROM('shipping_addresses')->WHERE('user_id', '=', $this->Gatekeeper->getUser()->id)->FIND_ALL();
        }

        return [];
    }

    /**
     * Get logged in user's stored credit cards from BT.
     *
     * @return array
     */
    public function cards(): array
    {
        return $this->Ecommerce->braintree()->cards();
    }
}
