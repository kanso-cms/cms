<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */
use kanso\Kanso;

/**
 * Ecommerce view includes.
 *
 * @author Joe J. Howard
 */
function cart_is_empty()
{
    return Kanso::instance()->Ecommerce->helpers()->cart_is_empty();
}

function cart_has_discount()
{
    return Kanso::instance()->Ecommerce->helpers()->cart_has_discount();
}

function cart_has_bundle()
{
    return Kanso::instance()->Ecommerce->helpers()->cart_has_bundle();
}

function cart_items()
{
    return Kanso::instance()->Ecommerce->helpers()->cart_items();
}

function cart_fees()
{
    return Kanso::instance()->Ecommerce->helpers()->cart_fees();
}

function cart_discounts()
{
    return Kanso::instance()->Ecommerce->helpers()->cart_discounts();
}

function cart_total_items()
{
    return Kanso::instance()->Ecommerce->helpers()->cart_total_items();
}

function cart_total_unique_items()
{
    return Kanso::instance()->Ecommerce->helpers()->cart_total_unique_items();
}

function cart_total(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_total($format);
}

function cart_tax(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_tax($format);
}

function cart_total_excluding_tax(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_total_excluding_tax($format);
}

function cart_subtotal(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_subtotal($format);
}

function cart_subtotal_before_bundles(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_subtotal_before_bundles($format);
}

function cart_subtotal_with_discounts(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_subtotal_with_discounts($format);
}

function cart_total_savings(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_total_savings($format);
}

function cart_total_fees(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_total_fees($format);
}

function cart_bundle_savings(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_bundle_savings($format);
}

function cart_total_discounts(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_total_discounts($format);
}

function cart_shipping_cost(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_shipping_cost($format);
}

function cart_free_shipping_threshold(bool $format = false)
{
    return Kanso::instance()->Ecommerce->helpers()->cart_free_shipping_threshold($format);
}

function the_price(int $post_id = null, string $sku = '')
{
	return Kanso::instance()->Ecommerce->helpers()->the_price($post_id, $sku);
}

function the_price_before_sale(int $post_id = null, string $sku = '')
{
	return Kanso::instance()->Ecommerce->helpers()->the_price_before_sale($post_id, $sku);
}

function in_stock(int $post_id = null, string $sku = '')
{
	return Kanso::instance()->Ecommerce->helpers()->in_stock($post_id, $sku);
}

function free_shipping(int $post_id = null, string $sku = '')
{
	return Kanso::instance()->Ecommerce->helpers()->free_shipping($post_id, $sku);
}

function the_skus(int $post_id = null)
{
	return Kanso::instance()->Ecommerce->helpers()->the_skus($post_id);
}

function the_bundle_products(int $post_id = null)
{
	return Kanso::instance()->Ecommerce->helpers()->the_bundle_products($post_id);
}

function the_bundle_type(int $post_id = null)
{
	return Kanso::instance()->Ecommerce->helpers()->the_bundle_type($post_id);
}
