<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

use kanso\framework\utility\Markdown;
use kanso\framework\utility\Str;

/**
 * Product generator.
 *
 * @author Joe J. Howard
 */
class Product extends JsonGenerator implements JsonInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(): array
    {
        if (the_page_type() === 'single-product')
        {
            return
            [
                '@type'           => 'Product',
                '@id'             => the_canonical_url() . '#product',
                'productID'       => the_post_id(),
                'sku'             => the_sku()['sku'],
                'mpn'             => Str::slug(the_title() . the_post_id()),
                'name'            => the_title(),
                'description'     => the_meta_description(),
                'category'        => htmlspecialchars(the_categories_list(the_post_id(), ' > ')),
                'offers'          => $this->offers(),
                'review'          => $this->reviews(),
                'aggregateRating' => $this->aggregateRating(),
                'brand'           =>
                [
                    '@id' => 'https://vebena.com.au/#brand',
                ],
                'logo'        =>
                [
                    '@id' => 'https://vebena.com.au/#logo',
                ],
                'image' =>
                [
                    '@id' => the_canonical_url() . '#primaryimage',
                ],
                'mainEntityOfPage' =>
                [
                    '@id' => the_canonical_url() . '#webpage',
                ],
            ];
        }

        return [];
    }

    /**
     * Returns aggregateRating.
     *
     * @return array
     */
    private function aggregateRating(): array
    {
        $rating = $this->Ecommerce->reviews()->ratings(the_post_id());

        return
        [
            '@type'        =>  'AggregateRating',
            '@id'          =>  the_canonical_url() . '#rating',
            'ratingValue'  =>  $rating['average'],
            'reviewCount'  =>  $rating['count'],
            'bestRating'   => '5',
            'worstRating'  => '1',
            'url'          =>  the_canonical_url() . '#reviews',
        ];
    }

    /**
     * Returns offers.
     *
     * @return array
     */
    private function offers(): array
    {
        $result = [];
        $skus   = the_skus();

        foreach ($skus as $i => $sku)
        {
            $result[] =
            [
                '@type'           => 'Offer',
                '@id'             => the_canonical_url() . '#offer' . $i,
                'price'           => $sku['sale_price'],
                'priceCurrency'   => 'AUD',
                'priceValidUntil' => date('c', strtotime('+ 30 days')),
                'sku'             => $sku['sku'],
                'mpn'             => Str::slug(the_title() . the_post_id() . $i),
                'url'             => the_canonical_url(),
                'availability'    => 'http://schema.org/InStock',
                'itemCondition'   => 'http://schema.org/NewCondition',
                'image'           =>
                [
                    '@id' => the_canonical_url() . '#primaryimage',
                ],
                'seller'          =>
                [
                    '@id' => the_canonical_url() . '#organization',
                ],
                'offeredBy'       =>
                [
                    '@id' => the_canonical_url() . '#organization',
                ],
            ];
        }

        return $result;
    }

    /**
     * Returns reviews.
     *
     * @return array
     */
    private function reviews(): array
    {
        $response = [];
        $reviews  = $this->Ecommerce->reviews()->all(the_post_id());

        foreach ($reviews as $i => $review)
        {
            if ($review->parent > 0)
            {
                continue;
            }

            $rating    = $this->Ecommerce->reviews()->rating($review->id);
            $content   = explode("\n", $review->content);
            $headline  = array_shift($content);
            $content   = trim(implode("\n", $content));

            $response[] =
            [
                '@type'         => 'Review',
                '@id'           => the_canonical_url() . '#review' . $i,
                'dateCreated'   => date('c', $review->date),
                'datePublished' => date('c', $review->date),
                'name'          => trim($headline),
                'description'   => trim($headline),
                'reviewBody'    => trim(Markdown::plainText(Str::mysqlDecode($content))),
                'author'        =>
                [
                    '@type' => 'Person',
                    'name'  => $review->name,
                ],
                'reviewRating'  =>
                [
                    '@type'       => 'Rating',
                    '@id'         => the_canonical_url() . '#reviewRating' . $i,
                    'ratingValue' => $rating,
                ],
            ];
        }

        return $response;
    }

    /**
     * Returns isRelatedTo.
     *
     * @return array
     */
    private function isRelatedTo(): array
    {
        $response = [];

        foreach (relatedProducts() as $i => $product)
        {
            $response[] =
            [
                '@type'       => 'Product',
                'url'         => the_permalink($product->id),
            ];
        }

        return $response;
    }
}
