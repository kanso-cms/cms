<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\models;

/**
 * Filter search request.
 *
 * @author Joe J. Howard
 */
class Search extends FilterBase implements FilterInterface
{
    /**
     * The request type.
     *
     * @var string
     */
    protected $requestType = 'search';

    /**
     * {@inheritdoc}
     */
    public function filter(): bool
    {
        $searchTerm  = $this->getSearchTerm();
        $queryStr    = '';
        $posts       = [];
        $postCount   = 0;

        // Validate the query exists
        if ($searchTerm !== '')
        {
            $queryStr    = "post_status = published : post_type != page : orderBy = post_created, DESC : post_title LIKE $searchTerm : limit = {$this->offset}, {$this->perPage}";
            $posts       = $this->parseQueryStr($queryStr);
            $postCount   = count($posts);
            $searchQuery = $searchTerm;
        }

        // If there are no posts and the page is more than 2 return false
        if ($postCount === 0 && $this->Query->pageIndex >= 1)
        {
            return false;
        }

        $this->Query->queryStr    = $queryStr;
        $this->Query->posts       = $posts;
        $this->Query->postCount   = $postCount;
        $this->Query->searchQuery = $searchTerm;
        $this->Query->requestType = $this->requestType();

        return true;
    }

    /**
     * Get the search term.
     *
     * @return string
     */
    private function getSearchTerm(): string
    {
        $searchTerm = $this->Request->queries('q');

        if (!$searchTerm || $searchTerm === '' || trim($searchTerm) === '')
        {
            return '';
        }

        return htmlspecialchars($searchTerm);
    }
}
