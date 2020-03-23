<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Webpage generator.
 *
 * @author Joe J. Howard
 */
class Webpage extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
        // Monday, 22-Jan-2018 14:29:55 GMT+1100
        $datePublished = 1516591795;
        $dateModified  = strtotime('- 1 day');

        // Do we have posts ?
        if (!empty(the_posts()))
        {
            $datePublished = the_posts()[0]->created;
            $dateModified  = the_posts()[0]->modified;
        }

        $page =
        [
            '@type'         => $this->getPageType(),
            '@id'           => the_canonical_url() . '#webpage',
            'name'          => the_title(),
            'headline'      => the_title(),
            'description'   => the_meta_description(),
            'url'           => the_canonical_url(),
            'datePublished' => date('c', $datePublished),
            'dateModified'  => date('c', $dateModified),
            'inLanguage'    => 'en',
            'isPartOf' =>
            [
                '@id' => $this->Request->environment()->HTTP_HOST . '/#website',
            ],
            'image' =>
            [
                '@id' =>  the_canonical_url() . '#primaryimage',
            ],
            'primaryImageOfPage' =>
            [
                '@id' =>  the_canonical_url() . '#primaryimage',
            ],
            'breadcrumb' =>
            [
                '@id' => the_canonical_url() . '#breadcrumb',
            ],
        ];

        if (is_home())
        {
            $page['about'] =
            [
                '@id' => $this->Request->environment()->HTTP_HOST . '/#organization',
            ];
        }

        return $page;
	}

    /**
     * Get the page type.
     *
     * @return string
     */
    private function getPageType(): string
    {
        if (is_single())
        {
            return 'WebPage';
        }
        elseif (the_page_type() === 'single-product')
        {
            return 'ItemPage';
        }
        elseif (is_page('cart/checkout/'))
        {
            return 'CheckoutPage';
        }
        elseif(is_search())
        {
            return 'SearchResultsPage';
        }
        elseif(is_author())
        {
            return 'ProfilePage';
        }
        elseif(is_blog() || is_category() || is_tag() || is_page('products') || is_page('products/(:any)'))
        {
            return 'CollectionPage';
        }

        return 'WebPage';
    }
}
