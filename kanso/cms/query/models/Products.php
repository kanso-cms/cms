<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

/**
 * Filter products page as category.
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
        $urlParts = $this->urlParts;
        $isFeed   = in_array('feed', $urlParts);

        // Remove /feed/rss
        if ($isFeed)
        {
            $last = array_slice($urlParts, -1)[0];

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

        $this->Query->requestType  = $this->requestType();
        $this->Query->queryStr     = 'post_status = published : post_type = product : orderBy = post_created, DESC';
        $this->Query->posts        = $this->parseQueryStr($this->Query->queryStr);
        $this->Query->postCount    = count($this->Query->posts);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function requestType(): string
    {
        return 'products';
    }
}
