<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\filters;

use kanso\cms\query\Query;
use kanso\framework\common\SqlBuilderTrait;
use kanso\framework\ioc\Container;

/**
 * CMS Query object.
 *
 * @author Joe J. Howard
 */
abstract class FilterBase
{
    use SqlBuilderTrait;

    /**
     * IoC container instance.
     *
     * @var \kanso\framework\ioc\Container
     */
    protected $container;

    /**
     * Query instance.
     *
     * @var \kanso\cms\query\Query
     */
    protected $parent;

    /**
     * Posts per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * Posts per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * Current page offset.
     *
     * @var string
     */
    protected $requestType;

    /**
     * Constructor.
     *
     * @param \kanso\framework\ioc\Container $container   IoC container
     * @param string                         $requestType The request type
     */
    public function __construct(Container $container, string $requestType)
    {
        $this->container = $container;

        $this->perPage = $this->container->Config->get('cms.posts_per_page');

        $this->offset = $this->fetchPageIndex() * $perPage;

        $this->requestType = $requestType;
    }

    /**
     * Returns query builder instance.
     *
     * @param \kanso\cms\query\Query $query Query instance
     */
    public function setParent(Query $query): void
    {
        $this->parent = $query;
    }

    /**
     * Fetch and set the currently requested page.
     *
     * @return int
     */
    public function fetchPageIndex(): int
    {
        $pageIndex = $this->container->Request->fetch('page');

        return $pageIndex === 1 || $pageIndex === 0 ? 0 : $pageIndex-1;
    }
}
