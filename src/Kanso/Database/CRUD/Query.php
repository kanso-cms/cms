<?php

namespace Kanso\Database\CRUD;

/**
 * Database Query
 *
 * This class acts as a way for CRUD to query the database
 * Each CRUD instance has it's own Query object for building
 * and executing SQL on the database.
 */

class Query
{

	/**
	 * @var string    SQL query string
	 */
	private $SQL;

	/**
	 * @var string    SQL query bindings
	 */
	private $SQLBindings;

	/**
	 * @var string    SQL query tabkle
	 */
	private $table;

	/**
	 * @var array    Pending data to use on query
	 */
	private $pending;

	/**
	 * @var string    Pending data to use on query
	 */
	private $operation;

	/**
	 * @var array    Values to use in a Query
	 */
	private $opValues;

	/**
	 * @var Kanso\Database\Database    Database instance
	 */
	private $Database;

	/**
     * Constructor
     *
     * @param \Kanso\Database\Database $Database
     */
	public function __construct($Database)
	{
		# Keep a reference to the database
		$this->Database = $Database;

		# Reset the pending functions
		$this->setPending();
	}

	/*******************************************************************************************************
	* PUBLIC METHODS FOR QUERYING TABLES
	*******************************************************************************************************/

	/**
     * Set the table to operate on
     *
     * @param str    $table
     */
	public function setTable($table)
	{
		# Set the table
		$this->table = $table;

		# Queries may be added before a table has been set
		# If no table was set, set the pending table
		if (isset($this->pending['column']['SPECIAL_T_B_C'])) {
			$this->pending['column'][$table] = $this->pending['column']['SPECIAL_T_B_C'];
			unset($this->pending['column']['SPECIAL_T_B_C']);
		}
	}

	/**
     * Set the current operation 
     *
     * @param str    $operation | SET | DELETE | SELECT FROM | INSERT
     * @param array  $values    Values to use on the query
     */
	public function setOperation($operation, $values = [])
	{
		# Set the operation
		$this->operation = $operation;

		# Don't change ids
		if (isset($values['id'])) unset($values['id']);

		# Set the values
		$this->opValues  = $values;
	}

	/**
     * Select a single column in a query
     *
     * @param str    $colum
     */
	public function column($column)
	{
		if ($column === '*') return true;
		$column = $column; 
		$table  = !$this->table ? 'SPECIAL_T_B_C' : $this->table;

		if (strpos($column, '.') !== FALSE) {
			$table  = substr($column, 0,strrpos($column, '.'));
			$column = substr($column, strrpos($column, '.') + 1);
		}

		$this->pending['column'][$table][$column] = $column;
	}

	/**
     * Set an SQL Select statement
     *
     * @param str    $columns
     */
	public function select($columns)
	{
		# A list of tables and columns
		# e.g table1_name(column1, column2), table2_name(column1) 
		if (strpos($columns, ')') !== FALSE) {
			$columns = array_filter(array_map('trim', explode(')', $columns)));
			foreach ($columns as $column) {
				$column     = trim(trim($column,',')).')';
				$table      = substr($column, 0,strrpos($column, '('));
				$tableCols  = trim(substr($column, strrpos($column, '(') + 1), ')');
				
				# e.g table1_name(column1, column2), table2_name(column1)
				if (strpos($tableCols, ',') !== FALSE) {
					$tableCols = array_filter(array_map('trim', explode(',', $tableCols)));
					foreach ($tableCols as $col) {
					   $this->column("$table.$col");
					}
				}
				# e.g table1_name(column1, column2)
				else {
					$this->column("$table.$tableCols");
				}
			}
		}
		# e.g column1, column2
		else if (strpos($columns, ',') !== FALSE) {
			$columns = array_filter(array_map('trim', explode(',', $columns)));
			foreach ($columns as $column) {
				$this->column($column);
			}
			return true;
		}
		# e.g column1
		else {
			return $this->column($columns);
		}
	}

	/**
     * Set an SQL WHERE statement
     *
     * @param  string    $column
     * @param  string    $op
     * @param  string    $value
     * @param  string    $type    and|or
     */
	public function where($column, $op, $value, $type = 'and')
	{

		$table = $this->table;

		if (strpos($column, '.') !== FALSE) {
			$table  = substr($column, 0,strrpos($column, '.'));
			$column = substr($column, strrpos($column, '.') + 1);
		}

		$query = [
			'table'  => $table,
			'type'   => $type,
			'column' => $column,
			'op'     => $op,
			'value'  => (string)$value,
		];

		$key = $this->queryFilter("$query[table]$query[type]$query[column]$query[value]");

		$query['value'] = $key;

		$this->pending['where'][] = $query;

		$this->SQL_bindings[$key] = $value;
	}

	/**
     * Set an SQL AND WHERE statement
     *
     * @param  string    $column
     * @param  string    $op
     * @param  string    $value
     */
	public function and_where($column, $op, $value)
	{
		return $this->where($column, $op, $value);
	}

	/**
     * Set an SQL and OR WHERE statement
     *
     * @param  string    $column
     * @param  string    $op
     * @param  string    $value
     */
	public function or_where($column, $op, $value)
	{
		return $this->where($column, $op, $value, 'or');
	}

	/**
     * Join a table
     *
     * @param  string    $table
     * @param  string    $columns
     */
	public function join($table, $columns)
	{
		$this->pending['inner_join'][] = ['table' => $table, 'columns' => $columns];
		if (!isset($this->pending['column'][$table])) $this->pending['column'][$table] = [];
	}

	/**
     * Inner join a table
     *
     * @param  string    $table
     * @param  string    $columns
     */
	public function inner_join($table, $columns)
	{
		return $this->join($table, $columns);
	}

	/**
     * Left join a table
     *
     * @param  string    $table
     * @param  string    $columns
     */
	public function left_join($table, $columns)
	{
		$this->pending['left_join'][] = ['table' => $table, 'columns' => $columns];
		if (!isset($this->pending['column'][$table])) $this->pending['column'][$table] = [];
	}

	/**
     * Right join a table
     *
     * @param  string    $table
     * @param  string    $columns
     */
	public function right_join($table, $columns)
	{
		$this->pending['right_join'][] = ['table' => $table, 'columns' => $columns];
		if (!isset($this->pending['column'][$table])) $this->pending['column'][$table] = [];
	}

	/**
     * Outer join a table
     *
     * @param  string    $table
     * @param  string    $columns
     */
	public function full_outer_join($table, $columns)
	{
		$this->pending['full_outer_join'][] = ['table' => $table, 'columns' => $columns];
		if (!isset($this->pending['column'][$table])) $this->pending['column'][$table] = [];
	}

	/**
     * Set sort order of SQL results
     *
     * @param  string    $column
     * @param  string    $direction   DESC|ASC
     */
	public function order_by($column, $direction = 'DESC')
	{

		$table  = $this->table;
		
		if (strpos($column, '.') !== FALSE) {
			$table  = substr($column, 0,strrpos($column, '.'));
			$column = substr($column, strrpos($column, '.') + 1);
		}

		if ($direction === 'ASC' || $direction === 'DESC') {
			$this->pending['orderBy'] = [
				'table'     => $table,
				'column'    => $column,
				'direction' => $direction,
			]; 
		}
	}

	/**
     * Set group by
     *
     * @param  string    $column
     * @param  string    $direction   DESC|ASC
     */
	public function group_by($key)
	{
		$this->pending['group_by'] = $key;
	}

	/**
     * Concatinate a SELECT group
     *
     * @param  string    $keys
     * @param  string    $as
     */
	public function group_concat($keys, $as)
	{
		$this->pending['group_concat'][] = [$keys, $as];
	}

	/**
     * Limit results to a number
     *
     * @param  int    $limit
     */
	public function limit($limit)
	{   
		$this->pending['limit'] = (int)$limit;
	}

	/**
     * Execute a SELECT query and limit to single row 
     */
	public function row()
	{
		return $this->find();
	}

	/**
     * Execute a SELECT query and limit to single row 
     * and/or find a single row by id
     *
     * @param  int    $id
     * @return \Kanso\Database\Database->query()
     */
	public function find($id = null)
	{

		if (!$this->tableLoaded()) throw new \Exception("A table has not been loaded");

		# If id filter by id
		if ($id) $this->and_where('id', '=', (int)$id);

		# limit results to 1 row
		$this->limit(1);

		return $this->find_all();

	}

	/**
     * Execute a SELECT query and find all results
     *
     * @return \Kanso\Database\Database->query()
     */
	public function find_all()
	{
		if (!$this->tableLoaded()) throw new \Exception("A table has not been loaded");
		
		# Build the SQL query
		$this->buildQuery();

		# Execute the SQL
		$results = $this->execSQL();

		# Reset any pending queryies
		$this->setPending();
	   
		return $results;
	}

	/**
     * Execute a SET|INSERT|DELETE query
     *
     * @return \Kanso\Database\Database->query()
     */
	public function query()
	{

		# Validate a table was loaded
		if (!$this->tableLoaded()) throw new \Exception("A table has not been loaded");
		
		# Validate a correct query is loaded
		if (!in_array($this->operation, ['DELETE', 'SET', 'INSERT INTO'])) throw new \Exception("No query loaded");

		# Build the SQL query
		$this->buildQuery();

		# If we are setting values 
		if ($this->operation === 'SET') {

			# Filter the array keys based on their value
			$values    = implode(', ', array_map(function ($v, $k) {return $k . ' = :' . $k; }, $this->opValues, array_keys($this->opValues)));
			$this->SQL = "UPDATE $this->table SET $values ".trim($this->SQL);
		}
		# If we are deleting values 
		else if ($this->operation === 'DELETE') {
			$this->SQL = "DELETE FROM $this->table ".trim($this->SQL);
		}
		# If we are inserting values 
		else if ($this->operation === 'INSERT INTO') {
			$values    = implode(', ', array_map(function ($v, $k) { return ":$k"; }, $this->opValues, array_keys($this->opValues)));
			$keys      = implode(', ', array_keys($this->opValues));
			$this->SQL = "INSERT INTO $this->table ($keys) VALUES($values)";
		}

		$this->SQL_bindings = array_merge($this->SQL_bindings, $this->opValues);

		# Execute the SQL
		$results = $this->execSQL();

		# Reset any pending queryies
		$this->setPending();
	   
		return $results;

	}

	/*******************************************************************************************************
	* PRIVATE SQL BUILDING FUNCTIONS
	*******************************************************************************************************/

	/**
     * Build and SQL SELECT statement
     *
     */
	private function buildQuery()
	{
		# Build the select statement
		$SELECT = $this->operation === 'QUERY' ? $this->selectPending() : '';

		# Build the FROM statement
		$FROM = $this->operation === 'QUERY' ? "FROM $this->table" : '';

		# Build inner join
		$JOINS = $this->joinsPending();

		$WHERE = $this->wherePending();

		# Build order
		$ORDERBY = $this->orderByPending();

		# Set limit
		$LIMIT = $this->limitPending();

		$GROUP = $this->groupByPending();

		# Build SQL statement
		$this->SQL = "$SELECT $FROM $JOINS $WHERE $GROUP $ORDERBY $LIMIT";
	}

	/**
     * Execute the current SQL query
     * 
     * @return string
     */
	private function execSQL()
	{
		# Execute the SQL
		$results = $this->Database->query(trim($this->SQL), $this->SQL_bindings);

		# If this was a row query - flatten and return only the first result
		if (!empty($results) && (int) $this->pending['limit'] === 1 && $this->operation === 'QUERY') return $results[0];

		return $results;
	}

	/**
     * Add all the WHERE statements to current SQL query
     * 
     * @return string
     */
	private function wherePending()
	{
		# Has a join been specified ? i.e are we selecting from multiple tables
		$hasJoin = count($this->pending['column']) > 1;

		$wheres = [];
		if (!empty($this->pending['where'])) {
			$count = 0;
			foreach ($this->pending['where'] as $clause) {
				$SQL = $hasJoin ? "$clause[table].$clause[column] $clause[op] :$clause[value]" : "$clause[column] $clause[op] :$clause[value]";
				if ($count > 0) $SQL = strtoupper($clause['type'])." $SQL";
				$wheres[] = $SQL;
				$count++;
			}
			return 'WHERE '.trim(implode(' ', array_map('trim', $wheres)));
		}
		return '';
	}

	/**
     * Add the GROUP BY statement to the current SQL query if it exists 
     * 
     * @return string
     */
	private function groupByPending()
	{
		if (!empty($this->pending['group_by'])) {
			return 'GROUP BY '.$this->pending['group_by'];
		}
		return '';
	}

	/**
     * Add the ORDER BY statement to the current SQL query if it exists 
     * 
     * @return string
     */
	private function orderByPending()
	{
		# Has a join been specified ? i.e are we selecting from multiple tables
		$hasJoin = count($this->pending['column']) > 1;

		if (!empty($this->pending['orderBy'])) {
			if ($hasJoin) return 'ORDER BY '.$this->pending['orderBy']['table'].'.'.$this->pending['orderBy']['column'].' '.$this->pending['orderBy']['direction'];
			return 'ORDER BY '.$this->pending['orderBy']['column'].' '.$this->pending['orderBy']['direction'];
		}
		return '';
	}

	/**
     * Add the LIMIT statement to the current SQL query if it exists 
     * 
     * @return string
     */
	private function limitPending()
	{
		if (!empty($this->pending['limit'])) return "LIMIT ". (int) $this->pending['limit'];
		return '';
	}

	/**
     * Add the SELECT statement to the current SQL query if it exists 
     * 
     * @return string
     */
	private function selectPending()
	{

		# Has a join been specified ? i.e are we selecting from multiple tables
		$hasJoin = count($this->pending['column']) > 1;

		# Build the select statement
		$SELECT = '';

		# Reset the table name
		$this->setTable($this->table);

		# Loop the select statements
		if (!empty($this->pending['column'])) {
			foreach ($this->pending['column'] as $table => $columns) {
				foreach ($columns as $col) {
					$SELECT .= $hasJoin ? " ".trim($table).".$col, " : " $col, ";
				}
			}
			$SELECT = 'SELECT '.rtrim($SELECT, ', ');
		}
		else {
			$SELECT = 'SELECT * ';
		}

		if (empty($SELECT) || $SELECT === 'SELECT ') $SELECT = 'SELECT * ';

		$GROUP_CONCAT = $this->groupConcatPending();

		if (empty($GROUP_CONCAT)) return trim($SELECT);

		$SELECT =  trim($SELECT).', '.$GROUP_CONCAT;

		return trim($SELECT);
	}

	/**
     * Add the GROUP_CONCAT statement to the current SQL query if it exists 
     * 
     * @return string
     */
	private function groupConcatPending()
	{
		$SQL = '';
		if (!empty($this->pending['group_concat'])) {
			foreach ($this->pending['group_concat'] as $query) {
				$SQL .= "GROUP_CONCAT($query[0]) AS $query[1], ";
			} 
		}
		return rtrim($SQL, ', ');
	}

	/**
     * Add the join statements to the current SQL query if it exists 
     * 
     * @return string
     */
	private function joinsPending()
	{

		$SQL     = [];
		$joins   = [
					'left_join'  => $this->pending['left_join'], 
					'inner_join' => $this->pending['inner_join'], 
					'right_join' => $this->pending['right_join'], 
					'full_outer_join' => $this->pending['full_outer_join']
				];

		foreach ($joins as $joinType => $joinTypeJoin) {
			if (!empty($joinTypeJoin)) {
				foreach ($joinTypeJoin as $join) {
					$SQL[] = strtoupper(str_replace('_', ' ', $joinType))." $join[table] ON $join[columns]";
				}
			}
		}

		if (empty($SQL)) return '';

		return implode(' ', $SQL);
	}

	/*******************************************************************************************************
	* PRIVATE HELPER MOTHODS
	*******************************************************************************************************/

	/**
	 * Reset the pending query parts to default 
	 */
	private function setPending()
	{
		$this->pending = [
			'where'             => [],
			'inner_join'        => [],
			'left_join'         => [],
			'right_join'        => [],
			'full_outer_join'   => [],
			'orderBy'           => [],
			'group_by'          => [],
			'group_concat'      => [],
			'limit'             => [],
			'column'            => [],
		];
		$this->table        = null;
		$this->SQL          = null;
		$this->SQL_bindings = [];
		$this->operation    = 'QUERY';
	}

	/**
	 * Validate a table has been loaded to query
	 * @return boolean
	 */
	private function tableLoaded()
	{
		return $this->table != null;
	}

	/**
	 * Filter a column or table name
	 * @param  string $str
	 * @return string
	 */
	private function queryFilter($str)
	{
		return preg_replace('/[^A-Za-z]/', '', $str);
	}
}