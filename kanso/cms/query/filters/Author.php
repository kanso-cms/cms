<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

use kanso\cms\query\filters\FilterBase;
use kanso\cms\query\filters\FilterInterface;

/**
 * Filter author request.
 *
 * @author Joe J. Howard
 */
class Author extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $blogPrefix   = $this->container->Config->get('cms.blog_location');
        $urlParts     = explode('/', $this->container->Request->environment()->REQUEST_PATH);
        $taxonomySlug = !empty($blogPrefix) ? $urlParts[2] : $urlParts[1];
        $queryStr     = 'post_status = published : post_type = post : orderBy = post_created, DESC: author_slug = ' . $taxonomySlug . ": limit = {$this->offset}, {$this->perPage}";
        $posts        = $this->parent->helper('parser')->parseQuery($queryStr);
        $postCount    = count($posts);

        // Double check if the author exists
        // and that they are an admin or writer
        $role = $this->sql()->SELECT('role')->FROM('users')->WHERE('slug', '=', $taxonomySlug)->ROW();

        if ($role)
        {
            if ($role['role'] !== 'administrator' && $role['role'] !== 'writer')
            {
                return false;
            }
        }
        else
        {
            return false;
        }

        $this->parent->requestType  = 'author';
        $this->parent->taxonomySlug = $taxonomySlug;
        $this->parent->queryStr     = $queryStr;
        $this->parent->posts        = $posts
        $this->parent->postCount    = $postCount;

        return true;
    }
}
