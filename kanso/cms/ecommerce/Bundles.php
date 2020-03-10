<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use InvalidArgumentException;

/**
 * Bundles Utility Model.
 *
 * @author Joe J. Howard
 */
class Bundles extends UtilityBase
{
	private $allowedTypes =
	[
		'group',
		'combo',
		'bogo',
	];

    /**
     * Get all of a products.
     *
     * @param  bool  $published Return only published bundles (optional) (default true)
     * @return array
     */
    public function all(bool $published = true): array
    {
        return $this->PostManager->provider()->byKey('type', 'bundle', false, $published);
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

        if ($single === true && $posts !== null && $posts->type === 'bundle')
        {
            return $posts;
        }
        elseif(is_array($posts))
        {
            foreach ($posts as $post)
            {
                if ($post->type === 'bundle')
                {
                    $response[] = $post;
                }
            }
        }

        return $response;
    }

    /**
     * Get all bundles by type.
     *
     * @param  string $type      The bundle type (group|combo|bogo)
     * @param  bool   $published Return only published bundles (optional) (default true)
     * @return array
     */
    public function byType(string $type, bool $published = true): array
    {
    	if (!in_array($type, $this->allowedTypes))
    	{
    		throw new InvalidArgumentException('The provided bundle type "' . $type . '" is unsupported.');
    	}

    	$results = [];
    	$bundles = $this->all($published);

        foreach ($bundles as $bundle)
        {
        	$postMeta = $bundle->meta;

            if ($postMeta && isset($postMeta['bundle_configuration']) && $postMeta['bundle_configuration']['type'] === $type)
            {
            	$results[] = $bundle;

            }
        }

        return $results;
    }

}
