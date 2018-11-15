<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\cms\query\Query;
use kanso\framework\database\query\Builder;
use kanso\framework\ioc\Container;

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
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\ioc\Container $container IoC container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns query builder instance.
     *
     * @access public
     * @param \kanso\cms\query\Query $query Query instance
     */
    public function setParent(Query $query)
    {
        $this->parent = $query;
    }

    /**
     * Returns query builder instance.
     *
     * @access public
     * @return \kanso\framework\database\query\Builder
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
