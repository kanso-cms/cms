<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce\helpers;

use kanso\cms\query\helpers\Helper;
use kanso\framework\utility\Money;
use kanso\framework\utility\Str;

/**
 * CMS Query ecommerce methods.
 *
 * @author Joe J. Howard
 */
class Helpers extends Helper
{
    /**
     * Returns the currency.
     *
     * @return string
     */
    public function the_currency(): string
    {
        return $this->container->Ecommerce->cart()->currency();
    }

    /**
     * Checks if the cart is empty.
     *
     * @return bool
     */
    public function cart_is_empty(): bool
    {
        return $this->container->Ecommerce->cart()->isEmpty();
    }

    /**
     * Checks if the cart contains any discounts.
     *
     * @return bool
     */
    public function cart_has_discount(): bool
    {
        return $this->container->Ecommerce->cart()->hasDiscount();
    }

    /**
     * Checks if the cart contains any bundle.
     *
     * @return bool
     */
    public function cart_has_bundle(): bool
    {
        return $this->container->Ecommerce->cart()->hasBundle();
    }

    /**
     * Returns any array of all items in the cart.
     *
     * @return array
     */
    public function cart_items(): array
    {
        return $this->container->Ecommerce->cart()->items();
    }

    /**
     * Returns all fees.
     *
     * @return array
     */
    public function cart_fees(): array
    {
        return $this->container->Ecommerce->cart()->fees();
    }

    /**
     * Returns all discounts.
     *
     * @return array
     */
    public function cart_discounts(): array
    {
        return $this->container->Ecommerce->cart()->discounts();
    }

    /**
     * Get the total number of items in the cart.
     *
     * @return int
     */
    public function cart_total_items(): int
    {
        return $this->container->Ecommerce->cart()->totalItems();
    }

    /**
     * Get the total number of unique items in the cart.
     *
     * @return int
     */
    public function cart_total_unique_items(): int
    {
        return $this->container->Ecommerce->cart()->totalUniqueItems();
    }

    /**
     * Gets the cart grand total.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_total(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->total($format);
    }

    /**
     * Gets the total inclusive tax on the cart.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_tax(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->tax($format);
    }

    /**
     * Gets the cart grand total.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_total_excluding_tax(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->totalExcludingTax($format);
    }

    /**
     * Gets the subtotal of all the items in the cart (without any fees or discounts applied).
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_subtotal(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->subtotal($format);
    }

    /**
     * Gets the subtotal of all the items in the cart (without any fees or discounts applied
     * and before bundled pricing is calculated).
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_subtotal_before_bundles(bool $format = false)
    {
        // No bundles
        if (!$this->cart_has_bundle())
        {
            return $this->cart_subtotal();
        }

        $total = 0.00;

        foreach ($this->cart_items() as $item)
        {
            $total += ($this->the_price($item->product_id, $item->sku) * $item->quantity);
        }

        return !$format ? Money::float($total) : Money::format($total);
    }

    /**
     * Gets the subtotal of all the items in the cart (with discount).
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_subtotal_with_discounts(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->subtotalWithDiscounts($format);
    }

    /**
     * Returns the total savings from bundled products.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_bundle_savings(bool $format = false)
    {
        if (!$this->cart_has_bundle())
        {
            return !$format ? Money::float(0.00) : Money::format(0.00);
        }

        $amount = $this->cart_subtotal_before_bundles() - $this->cart_subtotal();

        return !$format ? Money::float($amount) : Money::format($amount);
    }

    /**
     * Returns the total savings from bundled products.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_total_savings(bool $format = false)
    {
        if (!$this->cart_has_bundle() || !$this->cart_has_discount())
        {
            return !$format ? Money::float(0.00) : Money::format(0.00);
        }

        $amount = $this->cart_bundle_savings() + $this->cart_total_discounts();

        return !$format ? Money::float($amount) : Money::format($amount);
    }

    /**
     * Gets the total fees applied to the cart.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_total_fees(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->totalFees($format);
    }

    /**
     * Gets the total discounts applied to the cart.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_total_discounts(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->totalDiscounts($format);
    }

    /**
     * Calculates and returns shipping cost.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_shipping_cost(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->shippingCost($format);
    }

    /**
     * Returns the free shipping threshold.
     *
     * @param  bool         $format Format the amount as a string (optional) (default false)
     * @return string|float
     */
    public function cart_free_shipping_threshold(bool $format = false)
    {
        return $this->container->Ecommerce->cart()->freeShippingThreshold($format);
    }

    /**
     * Get the price of product.
     *
     * @param  int|null   $post_id Post id or null for current post (optional) (Default NULL)
     * @param  string     $sku     Post sku or '' (optional) (default '')
     * @return float|null
     */
    public function the_price(int $post_id = null, string $sku = '')
    {
    	$post = !$post_id ? $this->parent->post : $this->container->PostManager->byId($post_id);

        if (!$post || $post->type === 'post')
        {
        	return null;
        }

    	if ($post->type === 'product')
    	{
    		if ($sku !== '')
    		{
    			foreach ($post->meta['skus'] as $sku)
    			{
    				if ($sku['sku'] === $sku)
    				{
    					if ($sku['sale_price'] < $sku['price'])
    					{
    						return Money::float($sku['sale_price']);
    					}

    					return Money::float($sku['price']);
    				}
    			}
    		}

    		if ($post->meta['skus'][0]['sale_price'] < $post->meta['skus'][0]['price'])
			{
				return Money::float($post->meta['skus'][0]['sale_price']);
			}

			return Money::float($post->meta['skus'][0]['price']);
    	}
    	elseif ($post->type === 'bundle')
    	{
    		$config = $post->meta['bundle_configuration'];

    		if (isset($config['price']) && $config['price'] > 0)
	        {
	            return Money::float($config['price']);
	        }

	    	$entries  = $config['type'] === 'bogo' ? $config['products_in'] : $config['products'];
			$total    = 0.00;

			foreach ($entries as $entry)
			{
				$total += ($this->the_price($entry['product_id'], $entry['sku']) * $entry['quantity']);
			}

			if (isset($config['discount']) && $config['discount'] > 0)
			{
				$total = ($total - ($config['discount'] / 100) * $total);
			}

			if (isset($config['override_cents']) && $config['override_cents'] > 0)
			{
				$total = Money::float(strtok(strval($total), '.') . '.' . $config['override_cents']);
	        }

	        return $total;
    	}

    	return 0.00;
    }

    /**
     * Get the price of product before sale.
     *
     * @param  int|null   $post_id Post id or null for current post (optional) (Default NULL)
     * @param  string     $sku     Post sku or '' (optional) (default '')
     * @return float|null
     */
    public function the_price_before_sale(int $post_id = null, string $sku = '')
    {
    	$post = !$post_id ? $this->parent->post : $this->container->PostManager->byId($post_id);

        if (!$post || $post->type === 'post')
        {
        	return null;
        }

    	if ($post->type === 'product')
    	{
    		if ($sku !== '')
    		{
    			foreach ($post->meta['skus'] as $sku)
    			{
    				if ($sku['sku'] === $sku)
    				{
    					return Money::float($sku['price']);
    				}
    			}
    		}

			return Money::float($post->meta['skus'][0]['price']);
    	}
    	elseif ($post->type === 'bundle')
    	{
    		$config = $post->meta['bundle_configuration'];
    		$price  = 0.00;

    		// Normal price + price of free items
    		if ($config['type'] === 'bogo')
    		{
    			foreach ($config['products_in'] as $entry)
    			{
					$price += ($this->the_price($entry['product_id'], $entry['sku']) * $entry['quantity']);
    			}

                foreach ($config['products_out'] as $entry)
                {
                    $price += ($this->the_price($entry['product_id'], $entry['sku']) * $entry['quantity']);
                }

    			return Money::float($price);
    		}

    		foreach ($config['products'] as $entry)
    		{
    			if (!isset($entry['product_id']) && isset($entry[0]))
				{
					$entry = array_values($entry)[0];
				}

				$price += ($this->the_price($entry['product_id'], $entry['sku']) * $entry['quantity']);
			}

	        return Money::float($price);
    	}

    	return 0.00;
    }

    /**
     * Check if a product or bundle is in stock.
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @param  string   $sku     Post sku or '' (optional) (default '')
     * @return bool
     */
    public function in_stock(int $post_id = null, string $sku = ''): bool
    {
    	$post = !$post_id ? $this->parent->post : $this->container->PostManager->byId($post_id);

        if (!$post || $post->type === 'post')
        {
        	return false;
        }

    	if ($post->type === 'product')
    	{
    		if ($sku !== '')
    		{
    			foreach ($post->meta['skus'] as $sku)
	    		{
	    			if ($sku['sku'] === $sku)
	    			{
	    				return Str::bool($sku['instock']);
	    			}
	    		}
    		}

    		return Str::bool($post->meta['skus'][0]['instock']);
    	}
    	elseif ($post->type === 'bundle')
    	{
    		foreach ($this->the_bundle_products($post_id) as $product)
    		{
    			foreach ($product->meta['skus'] as $sku)
                {
                    if (!Str::bool($sku['instock']))
                    {
                        return false;
                    }
    			}
    		}
    	}

    	return true;
    }

    /**
     * Check if a product or bundle has free shipping.
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @return bool
     */
    public function free_shipping(int $post_id = null, string $sku = ''): bool
    {
    	$post = !$post_id ? $this->parent->post : $this->container->PostManager->byId($post_id);

        if (!$post || $post->type === 'post')
        {
        	return false;
        }

    	if ($post->type === 'product')
    	{
    		if ($sku !== '')
    		{
    			foreach ($post->meta['skus'] as $sku)
	    		{
	    			if ($sku['sku'] === $sku)
	    			{
	    				return Str::bool($sku['free_shipping']);
	    			}
	    		}
    		}

    		return Str::bool($post->meta['skus'][0]['free_shipping']);
    	}
    	elseif ($post->type === 'bundle')
    	{
    		foreach ($this->the_bundle_products($post_id) as $product)
    		{
                foreach ($product->meta['skus'] as $sku)
                {
                    if (!Str::bool($sku['free_shipping']))
                    {
                        return false;
                    }
                }
    		}
    	}

		return true;
    }

    /**
     * Gets all of a products SKUs.
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @return array
     */
    public function the_skus(int $post_id = null): array
    {
    	$post = !$post_id ? $this->parent->post : $this->container->PostManager->byId($post_id);

        if (!$post)
        {
            return [];
        }

        return $this->container->ProductProvider->skus($post->id);
    }

    /**
     * Get a product's sku by SKU or the first SKU
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @return array|false
     */
    public function the_sku(int $post_id = null, string $sku = '')
    {
        $post = !$post_id ? $this->parent->post : $this->container->PostManager->byId($post_id);

        if (!$post)
        {
            return [];
        }

        return $this->container->ProductProvider->sku($post->id, $sku);
    }

    /**
     * Gets all of a bundles products.
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @return array
     */
    public function the_bundle_products(int $post_id = null): array
    {
    	$post = !$post_id ? $this->parent->post : $this->container->BundleManager->byId($post_id);

        if (!$post)
        {
        	return [];
        }

    	$config   = $post->meta['bundle_configuration'];
    	$entries  = $config['type'] === 'bogo' ? $config['products_in'] : $config['products'];
		$products = [];

		foreach ($entries as $entry)
		{
			if (!isset($entry['product_id']) && isset($entry[0]))
			{
				foreach ($entry as $product)
				{
					$products[] = $this->get_bundle_product($product['product_id'], $product['sku'], 1);
				}
			}
			else
			{
				$products[] = $this->get_bundle_product($entry['product_id'], $entry['sku'], $entry['quantity']);
			}
		}

    	if ($config['type'] === 'bogo')
		{
			foreach ($config['products_out'] as $entry)
			{
				$products[] = $this->get_bundle_product($entry['product_id'], $entry['sku'], $entry['quantity'], true);
			}
		}

		return array_values(array_filter($products));
    }

    /**
     * Returns the bundle type on bundle post types.
     *
     * @return string|null
     */
    public function the_bundle_type(int $post_id = null)
    {
    	$post = !$post_id ? $this->parent->post : $this->container->PostManager->byId($post_id);

        if (!$post || $post->type !== 'bundle')
        {
        	return null;
        }

    	return $post->meta['bundle_configuration']['type'];
    }

    /**
     * Creates and retrieves a product post that is part of a bundle.
     *
     * @param  int                            $post_id  The post id to retrieve
     * @param  string                         $sku      The post entry_id
     * @param  int                            $quantity The product quantity in the bundle
     * @param  bool                           $isFree   Is this product free? (optional) (default false)
     * @return \kanso\cms\wrappers\Post|false
     */
    private function get_bundle_product(int $post_id, string $sku, int $quantity, bool $isFree = false)
    {
    	$post = $this->container->PostManager->byId($post_id);

    	if ($post)
    	{
			$meta = $post->meta;
			$post->meta = [];

			foreach ($meta['skus'] as $i => $_sku)
			{
				if ($_sku['sku'] !== $sku)
				{
					unset($meta['skus'][$i]);
				}
				elseif ($isFree)
				{
					$meta['skus'][$i]['price']      = 0.00;
					$meta['skus'][$i]['sale_price'] = 0.00;
				}
			}

			$meta['skus'] = array_values($meta['skus']);

            $meta['quantity'] = $quantity;

			$post->meta = $meta;

			return $post;
		}

		return false;
    }

}
