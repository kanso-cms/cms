<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

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
        // Get url parts
        $urlParts = $this->filterUrlParts();
        $lastCat  = $this->container->CategoryManager->provider()->byKey('slug', array_slice($urlParts, -1)[0], true);

        // Make sure the category exists
        if (!$lastCat)
        {
            return false;
        }

        // Make sure the path to a nested category is correct
        if (!$this->parent->the_category_slug($lastCat->id) === implode('/', $urlParts))
        {
            return false;
        }

        $this->parent->requestType  = 'category';
        $this->parent->taxonomySlug = $lastCat;
        $this->parent->queryStr     = 'post_status = published : post_type = post : orderBy = post_created, DESC : category_slug = ' . $this->parent->taxonomySlug . " : limit = {$this->offset}, {$this->perPage}";
        $this->parent->posts        = $this->parent->helper('parser')->parseQuery($this->parent->queryStr);
        $this->parent->postCount    = count($this->parent->posts);

        // If there are no posts and the page is more than 2 return false
        if ($this->parent->postCount === 0 && $this->parent->pageIndex >= 1)
        {
            return false;
        }

        return true;
    }

    /**
     * Filters and sanitizes URL into pieces.
     *
     * @return array
     */
    private function filterUrlParts(): array
    {
        $blogPrefix   = $this->container->Config->get('cms.blog_location');
        $urlParts     = explode('/', $this->container->Request->environment()->REQUEST_PATH);
        $isPage       = in_array('page', $urlParts);
        $isFeed       = in_array('feed', $urlParts);

        // Remove the blog prefix
        if ($blogPrefix)
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
