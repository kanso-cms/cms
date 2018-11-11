<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\framework\ioc\Container;
use kanso\framework\database\query\Builder;
use kanso\cms\query\QueryBase;

/**
 * CMS Query object.
 *
 * @author Joe J. Howard
 */
abstract class Helper
{
    /**
     * SQL query builder instance.
     *
     * @var \kanso\framework\database\query\Builder
     */
    protected $sql;

    /**
     * IoC container instance
     *
     * @var kanso\framework\ioc\Container
     */
    protected $container;

    /**
     * Query instance
     *
     * @var kanso\cms\query\QueryBase
     */
    protected $parent;

	/**
     * Constructor.
     *
     * @access public
     * @param  kanso\framework\ioc\Container $container IoC container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns query builder instance
     *
     * @access public
     * @param  kanso\cms\query\QueryBase $query Query instance
     */
    public function setParent(QueryBase $query)
    {
        $this->parent = $query;
    }

    /**
     * Returns query builder instance
     *
     * @access public
     * @param  kanso\framework\ioc\Container $container IoC container
     */
    protected function sql(): Builder
    {
        if (is_null($this->sql))
        {
            $this->sql = $this->container->get('Database')->connection()->builder();
        }

        return $this->sql;
    }
}
