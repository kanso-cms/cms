<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers;

use Kanso\Framework\Database\Query\Builder;
use Kanso\Framework\Common\MagicArrayAccessTrait;

/**
 * Database wrapper base class
 *
 * @author Joe J. Howard
 */
abstract class Wrapper
{
	use MagicArrayAccessTrait;

    /**
     * SQL query builder
     * 
     * @var \Kanso\Framework\Database\Query\Builder
     */ 
    protected $SQL;

    /**
     * Database row as array
     * 
     * @var array
     */ 
    protected $data;

    /**
     * Constructor
     * 
     * @access public
     * @param \Kanso\Framework\Database\Query\Builder $SQL  SQL query builder
     * @param  array                                  $data Array row from Database
     */
    public function __construct(Builder $SQL, array $data = [])
    {
        $this->SQL = $SQL;

        $this->data = !empty($data) ? $data : [];
    }

	/**
	 * Saves the row item
	 *
	 * @access public
     * @return bool
	 */
	abstract public function save(): bool;

	/**
	 * Deletes the row item
	 *
	 * @access public
     * @return bool
	 */
	abstract public function delete(): bool;
}
