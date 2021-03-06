<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\providers;

use kanso\framework\database\query\Builder;

/**
 * Provider base class.
 *
 * @author Joe J. Howard
 */
abstract class Provider
{
    /**
     * SQL query builder.
     *
     * @var \kanso\framework\database\query\Builder
     */
    protected $SQL;

    /**
     * Constructor.
     *
     * @param \kanso\framework\database\query\Builder $SQL SQL query builder
     */
    public function __construct(Builder $SQL)
    {
        $this->SQL = $SQL;
    }

    /**
     * Create an item.
     *
     * @return mixed
     */
    abstract public function create(array $row);

	/**
	 * Return an item by id.
	 *
	 * @param  int   $id Row id
	 * @return mixed
	 */
	abstract public function byId(int $id);

	/**
	 * Deletes the row item.
	 *
	 * @param  string $key   Column name
	 * @param  mixed  $value Column value
	 * @return mixed
	 */
	abstract public function byKey(string $key, $value, bool $single = false);
}
