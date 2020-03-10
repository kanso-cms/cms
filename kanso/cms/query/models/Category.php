<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

/**
 * Filter category request.
 *
 * @author Joe J. Howard
 */
class Category extends FilterBase implements FilterInterface
{
    /**
     * The request type.
     *
     * @var string
     */
    protected $requestType = 'category';

    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        // Get url parts
        $urlParts  = $this->filterUrlParts();
        $category  = $this->CategoryManager->provider()->byKey('slug', array_slice($urlParts, -1)[0], true);

        // Make sure the category exists
        if (!$category)
        {
            return false;
        }

        // Make sure the path to a nested category is correct
        if (!$this->Query->the_category_slug($category->id) === implode('/', $urlParts))
        {
            return false;
        }

        $queryStr  = "post_status = published : post_type = post : orderBy = post_created, DESC : category_slug = {$category->slug} : limit = {$this->offset}, {$this->perPage}";
        $posts     = $this->parseQueryStr($queryStr);
        $postCount = count($posts);

        // If there are no posts and the page is more than 2 return false
        if ($postCount === 0 && $this->Query->pageIndex >= 1)
        {
            return false;
        }

        $this->Query->requestType  = $this->requestType();
        $this->Query->taxonomySlug = $category->slug;
        $this->Query->queryStr     = $queryStr;
        $this->Query->posts        = $posts;
        $this->Query->postCount    = $postCount;

        return true;
    }

    /**
     * Filters and sanitizes URL into pieces.
     *
     * @return array
     */
    private function filterUrlParts(): array
    {
        $urlParts     = $this->urlParts;
        $isPage       = in_array('page', $urlParts);
        $isFeed       = in_array('feed', $urlParts);

        // Remove the blog prefix
        if (!empty($this->blogLocation))
        {
            array_shift($urlParts);
        }

        // remove category
        array_shift($urlParts);

        // Remove /page/number/
        if ($isPage)
        {
            array_pop($urlParts);
            array_pop($urlParts);
        }

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

        return array_values($urlParts);
    }
}
