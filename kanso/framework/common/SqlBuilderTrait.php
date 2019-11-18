<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\common;

use kanso\framework\database\query\Builder;
use kanso\Kanso;

/**
 * SQL Builder Trait.
 *
 * @author Joe J. Howard
 */
trait SqlBuilderTrait
{
    /**
     * SQL query builder instance.
     *
     * @var \kanso\framework\database\query\Builder|null
     */
    protected $sql = null;

    /**
     * Instantiate and/or return a query builder instance.
     *
     * @return \kanso\framework\database\query\Builder
     */
    protected function sql(): Builder
    {
        if (is_null($this->sql))
        {
            $this->sql = Kanso::instance()->Database->connection()->builder();
        }

        return $this->sql;
    }
}
