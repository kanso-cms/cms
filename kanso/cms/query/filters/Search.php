<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

/**
 * Filter search request.
 *
 * @author Joe J. Howard
 */
class Search extends FilterBase implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $searchTerm  = $this->getSearchTerm();

        // Validate the query exists
        if (!$searchTerm || $searchTerm === '')
        {
            // Empty search results
            $this->parent->queryStr    = '';
            $this->parent->posts       = [];
            $this->parent->postCount   = count($this->parent->posts);
            $this->parent->searchQuery = '';
            $this->parent->requestType = 'search';

            return true;
        }

        // Filter the posts
        $this->parent->queryStr    = "post_status = published : post_type != page : orderBy = post_created, DESC : post_title LIKE $searchTerm : limit = {$this->offset}, {$this->perPage}";
        $this->parent->posts       = $this->parent->helper('parser')->parseQuery($this->parent->queryStr);
        $this->parent->postCount   = count($this->parent->posts);
        $this->parent->searchQuery = $searchTerm;
        $this->parent->requestType = 'search';

        return true;
    }

    /**
     * Get the search term.
     *
     * @return string
     */
    private function getSearchTerm(): string
    {
        $searchTerm = $this->container->Request->queries('q');

        if (!$searchTerm || $searchTerm === '')
        {
            return '';
        }

        return htmlspecialchars($searchTerm);
    }
}
