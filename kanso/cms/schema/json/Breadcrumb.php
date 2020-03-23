<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * Breadcrumb generator.
 *
 * @author Joe J. Howard
 */
class Breadcrumb extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
        // Blog category
        if (is_category() && (!is_page('products') && !is_page('products/(:any)')))
        {
            $breadcrumbs = $this->categoryCrumbs();
        }
        // Blog tag or author
        elseif(is_tag() || is_author())
        {
            $breadcrumbs = $this->taxonomyCrumbs();
        }
        // Single product
        elseif (the_page_type() === 'single-product')
        {
            $breadcrumbs = $this->productCrumbs();
        }
        // Product category
        elseif (is_page('products') || is_page('products/(:any)'))
        {
            $breadcrumbs = $this->productCategoryCrumbs();
        }
        // Single Blog Post
        elseif (is_single())
        {
           $breadcrumbs = $this->singleCrumbs();
        }
        // Shopping cart login/register/forgotpassword/checkout
        elseif (is_page('cart/(:any)'))
        {
            $breadcrumbs = $this->cartCrumbs();
        }
        else
        {
            return [];
        }

        // Actual JSON data
        return $this->breadCrumbsToSchema($breadcrumbs);
	}

    /**
     * Breadcrumbs for a category listing.
     *
     * @return array
     */
    private function categoryCrumbs(): array
    {
        // Base crumbs
        $breadcrumbs =
        [
            [$this->Request->environment()->HTTP_HOST       => 'Home'],
            [$this->Request->environment()->HTTP_HOST . '/blog/' => 'Blog'],
        ];

        // Split URL into parts
        $urlParts = array_filter(explode('/', $this->Request->environment()->REQUEST_PATH));

        // Remove 'blog/category'
        array_shift($urlParts);
        array_shift($urlParts);

        // Remove '/page/number/'
        if (in_array('page', $urlParts))
        {
            array_pop($urlParts);
            array_pop($urlParts);
        }

        // Nested categories
        foreach ($urlParts as $_slug)
        {
            $category      = $this->CategoryManager->bySlug($_slug);
            $breadcrumbs[] = [the_category_url($category->id) => $category->name];
        }

        return $breadcrumbs;
    }

    /**
     * Breadcrumbs for a product category.
     *
     * @return array
     */
    private function productCategoryCrumbs(): array
    {
        // Base crumbs
        $breadcrumbs =
        [
            [$this->Request->environment()->HTTP_HOST => 'Home'],
        ];

        // For a product category
        if (is_page('products/(:any)'))
        {
            $breadcrumbs[] = [$this->Request->environment()->HTTP_HOST . '/products/' => 'Products'];
        }

        return $breadcrumbs;
    }

    /**
     * Breadcrumbs for a taxonomy listing (author or tag).
     *
     * @return array
     */
    private function taxonomyCrumbs(): array
    {
        return
        [
            [$this->Request->environment()->HTTP_HOST       => 'Home'],
            [$this->Request->environment()->HTTP_HOST . '/blog/' => 'Blog'],
        ];
    }

    /**
     * Breadcrumbs for a single product.
     *
     * @return array
     */
    private function productCrumbs(): array
    {
        // Base crumbs
        $breadcrumbs =
        [
            [$this->Request->environment()->HTTP_HOST           => 'Home'],
            [$this->Request->environment()->HTTP_HOST . '/products/' => 'Products'],
        ];

        $categories = the_categories(the_post_id());

        foreach ($categories as $category)
        {
            $breadcrumbs[] = [str_replace('/blog/category/', '/products/', the_category_url($category->id)) => $category->name];
        }

        return $breadcrumbs;
    }

    /**
     * Breadcrumbs for a single product.
     *
     * @return array
     */
    private function singleCrumbs(): array
    {
        // Base crumbs
        $breadcrumbs =
        [
            [$this->Request->environment()->HTTP_HOST       => 'Home'],
            [$this->Request->environment()->HTTP_HOST . '/blog/' => 'Blog'],
        ];

        $categories = the_categories(the_post_id());

        foreach ($categories as $category)
        {
            $breadcrumbs[] = [the_category_url($category->id) => $category->name];
        }

        return $breadcrumbs;
    }

    /**
     * Breadcrumbs for a single product.
     *
     * @return array
     */
    private function cartCrumbs()
    {
        return
        [
            [$this->Request->environment()->HTTP_HOST       => 'Home'],
            [$this->Request->environment()->HTTP_HOST . '/cart/' => 'Shopping Cart'],
        ];
    }

    /**
     * Breadcrumbs for a single product.
     *
     * @param  array $breadcrumbs Array of breadcrumbs as [URL => Title]
     * @return array
     */
    private function breadCrumbsToSchema(array $breadcrumbs): array
    {
        $schemaCrumbs =
        [
            '@type'           => 'BreadcrumbList',
            '@id'             =>  the_canonical_url() . '#breadcrumb',
            'itemListElement' => [],
        ];

        foreach ($breadcrumbs as $i => $crumb)
        {
            $title = reset($crumb);
            $url   = key($crumb);

            $schemaCrumbs['itemListElement'][] =
            [
                '@type'    => 'ListItem',
                'position' => ($i + 1),
                'item' =>
                [
                    '@type' => 'WebPage',
                    '@id'   => $url,
                    'url'   => $url,
                    'name'  => $title,
                ],
            ];
        }

        return $schemaCrumbs;
    }
}
