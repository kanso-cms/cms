<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\managers;

use kanso\framework\database\query\Builder;

/**
 * Provider manager base class.
 *
 * @author Joe J. Howard
 */
abstract class Manager
{
    /**
     * SQL query builder.
     *
     * @var \kanso\framework\database\query\Builder
     */
    protected $SQL;

    /**
     * Provider.
     *
     * @var mixed
     */
    protected $provider;

    /**
     * Default constructor.
     *
     * @param \kanso\framework\database\query\Builder $SQL      SQL query builder
     * @param mixed                                   $provider Provider manager
     */
    public function __construct(Builder $SQL, $provider)
    {
        $this->SQL = $SQL;

        $this->provider = $provider;
    }

    /**
     * Get the provider.
     *
     * @return mixed
     */
    abstract public function provider();
}
