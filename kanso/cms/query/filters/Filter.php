<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

use kanso\framework\utility\Str;

/**
 * CMS Query filter methods.
 *
 * @author Joe J. Howard
 */
class Filter extends Helper
{
    /**
     * Apply a query for a custom string.
     *
     * @param string $queryStr    Query string to parse
     * @param string $requestType Request type (optional) (default 'custom')
     */
    public function applyQuery(string $queryStr, $requestType = 'custom'): void
    {
        $this->reset();

        $this->parent->queryStr = trim($queryStr);

        $this->parent->posts = $this->parent->helper('parser')->parseQuery($this->parent->queryStr);

        $this->parent->postCount = count($this->parent->posts);

        $this->parent->requestType = $requestType;

        if (isset($this->parent->posts[0]))
        {
            $this->parent->post = $this->parent->posts[0];
        }
    }

    /**
     * Filter the posts by the request type.
     *
     * Note this method is used from the router/CMS core to filter posts based
     * on the matched route.
     *
     * @param string $requestType The requested page type
     */
    public function filterPosts(string $requestType): bool
    {
        // Reset the internal properties
        $this->reset();

        // Reset the response to 200
        $this->container->Response->status()->set(200);

        // Get the filter
        $postFilter = $this->getPostsFilter($requestType);

        // Filter the posts
        if (!$postFilter->filter())
        {
            $this->reset();

            $this->container->Response->status()->set(404);
        }

        // Set the_post so we're looking at the first item
        if (isset($this->parent->posts[0]))
        {
            $this->parent->post = $this->parent->posts[0];
        }
    }

    /**
     * Reset the internal properties to default.
     */
    public function reset(): void
    {
        $pageIndex = $this->container->Request->fetch('page');

        $this->parent->pageIndex    = 0;
        $this->parent->postIndex    = -1;
        $this->parent->postCount    = 0;
        $this->parent->posts        = [];
        $this->parent->requestType  = null;
        $this->parent->queryStr     = null;
        $this->parent->post         = null;
        $this->parent->taxonomySlug = null;
        $this->parent->searchQuery  = null;
        $this->parent->pageIndex    = $pageIndex === 1 || $pageIndex === 0 ? 0 : $pageIndex-1;
    }

    /**
     * Returns the post filter by request type.
     *
     * @param  string                              $requestType Request type keyword
     * @throws \Exception
     * @return \kanso\cms\query\filters\FilterBase
     */
    private function getPostsFilter(string $requestType): FilterBase
    {
        if ($requestType === 'home' || $requestType === 'home-page')
        {
            return new Home($this->container, $requestType);
        }
        elseif ($requestType === 'tag')
        {
            return new Tag($this->container, $requestType);
        }
        elseif ($requestType === 'category')
        {
            return new Category($this->container, $requestType);
        }
        elseif ($requestType === 'author')
        {
            return new Author($this->container, $requestType);
        }
        elseif ($requestType === 'single' || Str::getBeforeFirstChar($requestType, '-') === 'single')
        {
            return new Single($this->container, $requestType);
        }
        elseif ($requestType === 'page')
        {
            return new Page($this->container, $requestType);
        }
        elseif ($requestType === 'search')
        {
            return new Search($this->container, $requestType);
        }
        elseif ($requestType === 'attachment')
        {
            return new Attachment($this->container, $requestType);
        }
        elseif ($requestType === 'sitemap')
        {
            return new Sitemap($this->container, $requestType);
        }
        elseif ($requestType === 'products')
        {
            return new Products($this->container, $requestType);
        }

        throw new Exception('Invalid request type. The request type "' . $requestType . '" does not have a filter class.');
    }
}
