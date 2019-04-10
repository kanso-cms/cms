<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use kanso\framework\database\query\Builder;
use kanso\framework\ioc\ContainerAwareTrait;

/**
 * Base Model.
 *
 * @author Joe J. Howard
 */
abstract class UtilityBase
{
    use ContainerAwareTrait;

    /**
     * SQL query builder instance.
     *
     * @var \kanso\framework\database\query\Builder
     */
    protected $sql;

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {

    }

    /**
     * Instantiate and/or return a query builder instance.
     *
     * @access private
     * @return \kanso\framework\database\query\Builder
     */
    protected function sql(): Builder
    {
        if (!$this->sql)
        {
            $this->sql = $this->Database->connection()->builder();
        }

        return $this->sql;
    }
}
