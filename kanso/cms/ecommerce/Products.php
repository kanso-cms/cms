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
     * @param  int   $productId Product post_id
     * @return array
     */
    public function offers(int $productId): array
    {
        $product = $this->PostManager->byId($productId);

        if ($product)
        {
            $postMeta = $product->meta;

            return $postMeta['offers'] ?? [];
        }

        return [];
    }

    /**
     * Get all of a products.
     *
     * @param  bool  $published Return only published bundles (optional) (default true)
     * @return array
     */
    public function all(bool $published = true): array
    {
        return $this->PostManager->provider()->byKey('type', 'product', false, $published);
    }

    /**
     * Get all products by key.
     *
     * @param  string                         $index     Column name
     * @param  mixed                          $value     Column value
     * @param  bool                           $single    Return the first single row (optional) (default false)
     * @param  bool                           $published Return only published posts
     * @return array|\kanso\cms\wrappers\Post
     */
    public function byKey(string $index, $value, bool $single = false, bool $published = true)
    {
        $response = [];
        $posts    = $this->PostManager->provider()->byKey($index, $value, $single, $published);

        if ($single === true && $posts !== null && $posts->type === 'product')
        {
            return $posts;
        }
        elseif(is_array($posts))
        {
            foreach ($posts as $post)
            {
                if ($post->type === 'product')
                {
                    $response[] = $post;
                }
            }
        }

        return $response;
    }
}
