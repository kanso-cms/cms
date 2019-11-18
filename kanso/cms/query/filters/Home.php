<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

/**
 * Filter posts on the homepage.
 *
 * @author Joe J. Howard
 */
class Home extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $this->parent->requestType = $this->requestType;

        $this->parent->queryStr = "post_status = published : post_type = post : orderBy = post_created, DESC : limit = {$this->offset}, {$this->perPage}";

        $this->parent->posts = $this->parent->helper('parser')->parseQuery($this->parent->queryStr);

        $this->parent->postCount = count($this->parent->posts);

        if ($this->parent->postCount === 0)
        {
            $this->container->Response->status()->set(404);

            return false;
        }

        return true;
    }
}
