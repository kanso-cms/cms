<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers\Managers;

use Kanso\Framework\Database\Query\Builder;

/**
 * Provider manager base class
 *
 * @author Joe J. Howard
 */
abstract class Manager
{
    /**
     * SQL query builder
     * 
     * @var \Kanso\Framework\Database\Query\Builder
     */ 
    protected $SQL;

    /**
     * Provider
     * 
     * @var mixed
     */ 
    protected $provider;

    /**
     * Default constructor
     * 
     * @access public
     * @param  \Kanso\Framework\Database\Query\Builder $SQL      SQL query builder
     * @param  mixed                                   $provider Provider manager
     */
    public function __construct(Builder $SQL, $provider)
    {
        $this->SQL = $SQL;

        $this->provider = $provider;
    }

    /**
     * Get the provider
     *
     * @access public
     * @return mixed
     */
    abstract public function provider();
}
