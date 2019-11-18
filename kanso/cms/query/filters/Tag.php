<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

use kanso\cms\query\filters\FilterBase;
use kanso\cms\query\filters\FilterInterface;

/**
 * Filter category request.
 *
 * @author Joe J. Howard
 */
class Category extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $blogPrefix   = $this->container->Config->get('cms.blog_location');
        $urlParts     = explode('/', $this->container->Request->environment()->REQUEST_PATH);
        $taxonomySlug = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $queryStr     = 'post_status = published : post_type = post : orderBy = post_created, DESC : tag_slug = ' . $taxonomySlug . " : limit = {$this->offset}, {$this->perPage}";
        $posts        = $this->parent->helper('parser')->parseQuery($queryStr);
        $postCount    = count($posts);

        // If there are no posts and the page is more than 2 return false
        if ($postCount === 0 && $pageIndex >= 1)
        {
            return false;
        }

        if ($postCount === 0)
        {
            if (!$this->sql()->SELECT('id')->FROM('tags')->WHERE('slug', '=', $taxonomySlug)->ROW())
            {
                return false;
            }
        }

        $this->parent->requestType  = 'tag';
        $this->parent->taxonomySlug = $taxonomySlug;
        $this->parent->queryStr     = $queryStr;
        $this->parent->posts        = $posts
        $this->parent->postCount    = $postCount;

        return true;
    }
}
