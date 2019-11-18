<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

/**
 * Filter search request.
 *
 * @author Joe J. Howard
 */
class Products extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $urlParts = explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI));
        $isFeed   = in_array('feed', $urlParts);

        // Remove /feed/rss
        if ($isFeed)
        {
            $last = array_values(array_slice($urlParts, -1))[0];

            if ($last === 'rss' || $last === 'rdf' || $last == 'atom')
            {
                array_pop($urlParts);
                array_pop($urlParts);
            }
            else
            {
                array_pop($urlParts);
            }
        }

        $this->parent->requestType  = 'products';
        $this->parent->queryStr     = 'post_status = published : post_type = product : orderBy = post_created, DESC';
        $this->parent->posts        = $this->parent->helper('parser')->parseQuery($this->parent->queryStr);
        $this->parent->postCount    = count($this->parent->posts);

        return true;
    }
}
