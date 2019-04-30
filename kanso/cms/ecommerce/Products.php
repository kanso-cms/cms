<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

/**
 * Products utility.
 *
 * @author Joe J. Howard
 */
class Products extends UtilityBase
{
    /**
     * Get an offer from a product by id.
     *
     * @access public
     * @param  int         $productId Product post_id
     * @param  string      $offerId   Offer id (sku)
     * @return array|false
     */
    public function offer(int $productId, string $offerId)
    {
        // Get all offers
        $offers = $this->offers($productId);

        foreach ($offers as $offer)
        {
            if ($offer['offer_id'] === $offerId)
            {
                return $offer;
            }
        }

        return false;
    }

    /**
     * Get all of a product's offers.
     *
     * @access public
     * @param  int   $productId Product post_id
     * @return array
     */
    public function offers(int $productId): array
    {
        $product = $this->PostManager->byId($productId);

        if ($product)
        {
            $postMeta = $product->meta;

            return isset($postMeta['offers']) ? $postMeta['offers'] : [];
        }

        return [];
    }
}
