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
class Sitemap extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $this->parent->requestType = 'sitemap';
        $this->parent->queryStr    = 'post_status = published : post_type = post : orderBy = post_created';
        $this->parent->posts       = $this->parent->helper('parser')->parseQuery($this->parent->queryStr);
        $this->parent->postCount   = count($this->parent->posts);

        return true;
    }
}
