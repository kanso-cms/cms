<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

use kanso\cms\query\Query;
use kanso\framework\common\SqlBuilderTrait;
use kanso\framework\ioc\Container;

/**
 * CMS Query object.
 *
 * @author Joe J. Howard
 */
abstract class Helper
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
     * Constructor.
     *
     * @param \kanso\framework\ioc\Container $container IoC container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
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
}
