<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

/**
 * Filter tag request.
 *
 * @author Joe J. Howard
 */
class Tag extends FilterBase implements FilterInterface
{
    /**
     * The request type
     *
     * @var string
     */
    protected $requestType = 'tag';

    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $taxonomySlug = !empty($this->blogLocation) ? $this->urlParts[2] : $this->urlParts[1];
        $tag          = $this->TagManager->provider()->byKey('slug', $taxonomySlug, true);

        // Make sure the tag exists
        if (!$tag)
        {
            return false;
        }

        $queryStr     = "post_status = published : post_type = post : orderBy = post_created, DESC : tag_slug = $tag->slug : limit = {$this->offset}, {$this->perPage}";
        $posts        = $this->parseQueryStr($queryStr);
        $postCount    = count($posts);

        // If there are no posts and the page is more than 2 return false
        if ($postCount === 0 && $this->Query->pageIndex >= 1)
        {
            return false;
        }

        $this->Query->requestType  = $this->requestType();
        $this->Query->taxonomySlug = $tag->slug;
        $this->Query->queryStr     = $queryStr;
        $this->Query->posts        = $posts;
        $this->Query->postCount    = $postCount;

        return true;
    }
}
