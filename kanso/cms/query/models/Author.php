<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

/**
 * Filter author request.
 *
 * @author Joe J. Howard
 */
class Author extends FilterBase implements FilterInterface
{
    /**
     * The request type
     *
     * @var string
     */
    protected $requestType = 'author';

    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $authorSlug = !empty($this->blogLocation) ? $this->urlParts[2] : $this->urlParts[1];

        if ($this->authorExists($authorSlug))
        {
            return false;
        }

        $queryStr     = "post_status = published : post_type = post : orderBy = post_created, DESC: author_slug = {$authorSlug} : limit = {$this->offset}, {$this->perPage}";
        $posts        = $this->parseQueryStr($queryStr);
        $postCount    = count($posts);

        $this->Query->requestType  = $this->requestType();
        $this->Query->taxonomySlug = $authorSlug;
        $this->Query->queryStr     = $queryStr;
        $this->Query->posts        = $posts;
        $this->Query->postCount    = $postCount;

        return true;
    }

    /**
     * Checks if the give author is exists.
     */
    private function authorExists(string $slug): bool
    {
        $role = $this->sql()->SELECT('role')->FROM('users')->WHERE('slug', '=', $slug)->ROW();

        return $role || ($role && isset($role['role']) && $role['role'] !== 'administrator' && $role['role'] !== 'writer') ? false : true;
    }
}
