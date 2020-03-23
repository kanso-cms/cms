<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\schema\json;

/**
 * ItemList generator.
 *
 * @author Joe J. Howard
 */
class ItemList extends JsonGenerator implements JsonInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function generate(): array
	{
		$response = [];

        // Single product|blog
        if (the_page_type() === 'single-product' || is_single())
        {
            return $response;
        }
        // List of products or blogs
        elseif (is_blog() || is_category() || is_tag() || is_author() || is_page('products') || is_page('products/(:any)') || is_home())
        {
            if (is_home())
            {
                $posts = products();
            }
            elseif(is_page('products') || is_page('products/(:any)'))
            {
                $posts = currCategoryProducts();
            }
            else
            {
                $posts = the_posts();
            }

        	$response =
        	[
        		'@type'            => 'ItemList',
	            '@id'              => the_canonical_url() . '#list',
	            'itemListElement'  => [],
        	];

        	foreach ($posts as $i => $post)
        	{
        		$response['itemListElement'][] =
        		[
        			'@type'    => 'ListItem',
        			'@id'      => the_canonical_url() . '#listItem' . ($i + 1),
        			'position' => ($i + 1),
        			'url'      => the_permalink($post->id),
        		];
        	}

        	$response['numberOfItems'] = count($response['itemListElement']);
        }

        return $response;
	}
}
