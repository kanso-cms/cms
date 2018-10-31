<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query;

use kanso\cms\query\methods\Attachment;
use kanso\cms\query\methods\Author;
use kanso\cms\query\methods\CacheAccess;
use kanso\cms\query\methods\Category;
use kanso\cms\query\methods\Comment;
use kanso\cms\query\methods\Filter;
use kanso\cms\query\methods\Meta;
use kanso\cms\query\methods\Pagination;
use kanso\cms\query\methods\Post;
use kanso\cms\query\methods\PostIteration;
use kanso\cms\query\methods\Properties;
use kanso\cms\query\methods\Search;
use kanso\cms\query\methods\Tag;
use kanso\cms\query\methods\Templates;
use kanso\cms\query\methods\Urls;
use kanso\cms\query\methods\Validation;
use kanso\framework\ioc\ContainerAwareTrait;
use kanso\Kanso;

/**
 * CMS Query object.
 *
 * @author Joe J. Howard
 */
class Query
{
    /*
     * Class traits
     *
     */
    use Properties;
    use ContainerAwareTrait;
    use Validation;
    use Meta;
    use Urls;
    use Templates;
    use PostIteration;
    use Post;
    use Author;
    use Tag;
    use Category;
    use Attachment;
    use Comment;
    use Pagination;
    use Search;
    use CacheAccess;
    use Filter;

    /**
     * Constructor.
     *
     * @access public
     * @param \kanso\cms\query\QueryParser $queryParser Query parser
     * @param \kanso\cms\query\Cache       $cache       Method cache
     */
    public function __construct(QueryParser $queryParser, Cache $cache)
    {
        $this->loadDependencies($queryParser, $cache);

        $this->fetchPageIndex();
    }

    /**
     * Loads private dependencies.
     *
     * @access private
     * @param \kanso\cms\query\QueryParser $queryParser Query parser
     * @param \kanso\cms\query\Cache       $cache       Method cache
     */
    private function loadDependencies(QueryParser $queryParser, Cache $cache)
    {
        $this->SQL = $this->Database->connection()->builder();

        $this->queryParser = $queryParser;

        $this->cache = $cache;
    }

    /**
     * Create and return a new Query object.
     *
     * @access public
     * @param  string queryStr  Query to filter posts
     * @return \kanso\cms\Query
     */
    public function create(string $queryStr = ''): Query
    {
        $instance = clone $this;

        $instance->applyQuery($queryStr);

        return $instance;
    }
}
