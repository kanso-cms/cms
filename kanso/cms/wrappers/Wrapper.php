<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers;

use kanso\framework\common\MagicArrayAccessTrait;
use kanso\framework\database\query\Builder;

/**
 * Database wrapper base class.
 *
 * @author Joe J. Howard
 */
abstract class Wrapper
{
	use MagicArrayAccessTrait;

    /**
     * SQL query builder.
     *
     * @var \kanso\framework\database\query\Builder
     */
    protected $SQL;

    /**
     * Constructor.
     *
     * @param \kanso\framework\database\query\Builder $SQL  SQL query builder
     * @param array                                   $data Array row from Database
     */
    public function __construct(Builder $SQL, array $data = [])
    {
        $this->SQL = $SQL;

        $this->data = !empty($data) ? $data : [];
    }

	/**
	 * Saves the row item.
	 *
	 * @return bool
	 */
	abstract public function save(): bool;

	/**
	 * Deletes the row item.
	 *
	 * @return bool
	 */
	abstract public function delete(): bool;
}
