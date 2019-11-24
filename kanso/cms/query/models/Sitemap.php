<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

/**
 * Filter sitemap request.
 *
 * @author Joe J. Howard
 */
class Sitemap extends FilterBase implements FilterInterface
{
    /**
     * The request type.
     *
     * @var string
     */
    protected $requestType = 'sitemap';

    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $this->Query->requestType = 'sitemap';
        $this->Query->queryStr    = 'post_status = published : post_type = post : orderBy = post_created';
        $this->Query->posts       = $this->parseQueryStr($this->Query->queryStr);
        $this->Query->postCount   = count($this->Query->posts);

        return true;
    }
}
