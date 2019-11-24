<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

/**
 * Filter posts on the blog homepage.
 *
 * @author Joe J. Howard
 */
class HomePage extends FilterBase implements FilterInterface
{
    /**
     * The request type.
     *
     * @var string
     */
    protected $requestType = 'home-page';

    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $queryStr  = "post_status = published : post_type = post : orderBy = post_created, DESC : limit = {$this->offset}, {$this->perPage}";
        $posts     = $this->parseQueryStr($queryStr);
        $postCount = count($posts);

        if ($postCount === 0)
        {
            return false;
        }

        $this->Query->requestType = $this->requestType();
        $this->Query->queryStr    = $queryStr;
        $this->Query->posts       = $posts;
        $this->Query->postCount   = $postCount;

        return true;
    }
}
