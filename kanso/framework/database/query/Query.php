<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\database\query;

use PDOException;
use kanso\framework\database\connection\Connection;

/**
 * Database SQL builder
 *
 * This class acts as a way for Builder to query the database.
 * Each Builder instance has it's own Query object for building
 * and executing SQL on the database.
 * @author Joe J. Howard
 */
class Query
{
	/**
	 * SQL query string
	 *
	 * @var string
	 */
	private $SQL;

	/**
	 * SQL query bindings
	 *
	 * @var string
	 */
	private $SQL_bindings;

	/**
	 * SQL query table
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Pending data to use on query
	 *
	 * @var array
	 */
	private $pending;

	/**
	 * @var Current operation to run - SET | DELETE | SELECT FROM | INSERT
	 *
	 * @var string
	 */
	private $operation;

	/**
	 * @var Values to use in the Query
	 *
	 * @var array
	 */
	private $opValues;

	/**
	 * Database connection
	 *
	 * @var \kanso\framework\database\connection\Connection
	 */
	private $connection;

	/**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\database\connection\Connection $connection
     */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;

		# Reset the pending functions
		$this->setPending();
	}

	/*******************************************************************************************************
	* PUBLIC METHODS FOR QUERYING TABLES
	*******************************************************************************************************/

	/**
     * Set the table to operate on
     *
     * @access public
     * @param  str    $table Table name to set
     */
	public function setTable(string $table)
	{
		# Set the table
		$this->table = $table;

		# Queries may be added before a table has been set
		# If no table was set, set the pending table
		if (isset($this->pending['column']['SPECIAL_T_B_C']))
		{
			$this->pending['column'][$table] = $this->pending['column']['SPECIAL_T_B_C'];
			
			unset($this->pending['column']['SPECIAL_T_B_C']);
		}
	}

	/**
     * Set the current operation 
     *
     * @access public
     * @param  str    $operation Operation to set query to - SET | DELETE | SELECT FROM | INSERT
     * @param  array  $values    Values to use on the query (optional) (default [])
     */
	public function setOperation(string $operation, $values = [])
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
     * @access public
     * @param  str    $colum Column name
     */
	public function column(string $column)
	{
		if ($column === '*')
		{
			return true;
		}
		
		$column = $column; 
		
		$table  = !$this->table ? 'SPECIAL_T_B_C' : $this->table;

		if (strpos($column, '.') !== FALSE)
		{
			$table  = substr($column, 0,strrpos($column, '.'));
			$column = substr($column, strrpos($column, '.') + 1);
		}

		$this->pending['column'][$table][$column] = $column;
	}

	/**
     * Set an SQL Select statement
     *
     * @access public
     * @param  str    $columns Column name or names
     */
	public function select(string $columns)
	{
		# A list of tables and columns
		# e.g table1_name(column1, column2), table2_name(column1) 
		if (strpos($columns, ')') !== FALSE)
		{
			$columns = array_filter(array_map('trim', explode(')', $columns)));
			
			foreach ($columns as $column)
			{
				$column     = trim(trim($column,',')).')';
				$table      = substr($column, 0,strrpos($column, '('));
				$tableCols  = trim(substr($column, strrpos($column, '(') + 1), ')');
				
				# e.g table1_name(column1, column2), table2_name(column1)
				if (strpos($tableCols, ',') !== FALSE)
				{
					$tableCols = array_filter(array_map('trim', explode(',', $tableCols)));
					
					foreach ($tableCols as $col)
					{
					   $this->column("$table.$col");
					}
				}
				# e.g table1_name(column1, column2)
				else
				{
					$this->column("$table.$tableCols");
				}
			}
		}
		# e.g column1, column2
		else if (strpos($columns, ',') !== FALSE)
		{
			$columns = array_filter(array_map('trim', explode(',', $columns)));
			
			foreach ($columns as $column)
			{
				$this->column($column);
			}
			return true;
		}
		# e.g column1
		else
		{
			return $this->column($columns);
		}
	}

	/**
     * Set an SQL WHERE clases
     *
     * @access public
     * @param  string $column Column name to use
     * @param  string $op     Logical operator
     * @param  mixed  $value  Comparison value
     * @param  string $type   'and'|'or'
     */
	public function where(string $column, string $op, $value, string $type = 'and')
	{
		$table = $this->table;

		if (strpos($column, '.') !== FALSE)
		{
			$table  = substr($column, 0,strrpos($column, '.'));
			$column = substr($column, strrpos($column, '.') + 1);
		}

		$query = 
		[
			'table'  => $table,
			'type'   => $type,
			'column' => $column,
			'op'     => $op,
		];

		if (!is_array($value))
		{	
			$key = $this->queryFilter("$query[table]$query[type]$query[column]$value");

			$query['value'] = $key;

			$this->pending['where'][] = $query;

			$this->SQL_bindings[$key] = $value;
		}
		else
		{
			$query['value'] = [];

			foreach ($value as $val)
			{
				$key = $this->queryFilter("$query[table]$query[type]$query[column]".$val);
				
				$this->SQL_bindings[$key] = $val;

				$query['value'][] = $key;
			}
			
			$this->pending['where'][] = $query;
		}	
	}

	/**
     * Set an SQL AND WHERE statement
     *
     * @access public
     * @param  string $column Column name to use
     * @param  string $op     Logical operator
     * @param  mixed  $value  Comparison value
     */
	public function and_where(string $column, string $op, $value)
	{
		return $this->where($column, $op, $value);
	}

	/**
     * Set an SQL and OR WHERE statement
     *
     * @access public
     * @param  string $column Column name to use
     * @param  string $op     Logical operator
     * @param  mixed  $value  Comparison value
     */
	public function or_where(string $column, string $op, $value)
	{
		return $this->where($column, $op, $value, 'or');
	}

	/**
     * Join a table
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $columns   Column comparison e.g table1.id = table2.column_name
     */
	public function join(string $tableName, string $columns)
	{
		$this->pending['inner_join'][] = ['table' => $tableName, 'columns' => $columns];
		
		if (!isset($this->pending['column'][$tableName]))
		{
			$this->pending['column'][$tableName] = [];
		}
	}

	/**
     * Inner join a table
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $columns   Column comparison e.g table1.id = table2.column_name
     */
	public function inner_join(string $tableName, string $columns)
	{
		return $this->join($tableName, $columns);
	}

	/**
     * Left join a table
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $columns   Column comparison e.g table1.id = table2.column_name
     */
	public function left_join(string $tableName, string $columns)
	{
		$this->pending['left_join'][] = ['table' => $tableName, 'columns' => $columns];
		
		if (!isset($this->pending['column'][$tableName]))
		{
			$this->pending['column'][$tableName] = [];
		}
	}

	/**
     * Right join a table
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $columns   Column comparison e.g table1.id = table2.column_name
     */
	public function right_join(string $tableName, string $columns)
	{
		$this->pending['right_join'][] = ['table' => $tableName, 'columns' => $columns];
		
		if (!isset($this->pending['column'][$tableName]))
		{
			$this->pending['column'][$tableName] = [];
		}
	}

	/**
     * Outer join a table
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $columns   Column comparison e.g table1.id = table2.column_name
     */
	public function full_outer_join(string $tableName, string $columns)
	{
		$this->pending['full_outer_join'][] = ['table' => $tableName, 'columns' => $columns];
		
		if (!isset($this->pending['column'][$tableName]))
		{
			$this->pending['column'][$tableName] = [];
		}
	}

	/**
     * Set sort order of SQL results
     *
     * @access public
     * @param  string $column    The column name to use
     * @param  string $direction 'DESC'|'ASC' (optional) (default 'DESC')
     */
	public function order_by(string $column, string $direction = 'DESC')
	{

		$table  = $this->table;
		
		if (strpos($column, '.') !== FALSE)
		{
			$table  = substr($column, 0,strrpos($column, '.'));
			$column = substr($column, strrpos($column, '.') + 1);
		}

		if ($direction === 'ASC' || $direction === 'DESC')
		{
			$this->pending['orderBy'] = 
			[
				'table'     => $table,
				'column'    => $column,
				'direction' => $direction,
			]; 
		}
	}

	/**
     * Set group by
     *
     * @access public
     * @param  string $column Column name
     */
	public function group_by(string $column)
	{
		$this->pending['group_by'] = $column;
	}

	/**
     * Concatinate a SELECT group
     *
     * @access public
     * @param  string $keys Concat keys
     * @param  string $as   As value
     */
	public function group_concat(string $keys, string $as)
	{
		$this->pending['group_concat'][] = [$keys, $as];
	}

	/**
     * Limit/ offset results 
     *
     * @access public
     * @param  int $offset Offset to start at
     * @param  int $limit  Limit results (optional) (default null)
     */
	public function limit(int $offset, int $value = null)
	{
		if ($value)
		{
			$this->pending['limit'] = [$offset, $value];
		}
		else
		{
			$this->pending['limit'] = $offset;
		}
	}

	/**
     * Execute a SELECT query and limit to single row
     *
     * @access public
     * @return mixed
     */
	public function row()
	{
		return $this->find();
	}

	/**
     * Execute a SELECT query and limit to single row 
     * and/or find a single row by id
     *
     * @access public
     * @param  int   $id Row id to find (optional) (default null)
     * @return mixed
     */
	public function find(int $id = null)
	{
		if (!$this->tableLoaded())
		{
			throw new PDOException(vsprintf("%s(): A table has not been loaded into the Query via the Builder.", [__METHOD__]));
		}

		# If id filter by id
		if ($id) $this->and_where('id', '=', (int)$id);

		# limit results to 1 row
		$this->limit(1);

		return $this->find_all();
	}

	/**
     * Execute a SELECT query and find all results
     *
     * @access public
     * @return mixed
     */
	public function find_all()
	{
		if (!$this->tableLoaded())
		{
			throw new PDOException(vsprintf("%s(): A table has not been loaded into the Query via the Builder.", [__METHOD__]));
		}
		
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
     * @access public
     * @return mixed
     */
	public function query()
	{
		# Validate a table was loaded
		if (!$this->tableLoaded())
		{
			throw new PDOException(vsprintf("%s(): A table has not been loaded into the Query via the Builder.", [__METHOD__]));
		}
		
		# Validate a correct query is loaded
		if (!in_array($this->operation, ['DELETE', 'SET', 'INSERT INTO']))
		{
			throw new PDOException(vsprintf("%s(): Invalid query method. You must set the query to 'DELETE', 'SET', 'INSERT INTO'.", [__METHOD__]));
		}

		# Build the SQL query
		$this->buildQuery();

		# If we are setting values 
		if ($this->operation === 'SET')
		{
			# Filter the array keys based on their value
			$values    = implode(', ', array_map(function ($v, $k) {return $k . ' = :' . $k; }, $this->opValues, array_keys($this->opValues)));
			$this->SQL = "UPDATE $this->table SET $values ".trim($this->SQL);
		}
		# If we are deleting values 
		else if ($this->operation === 'DELETE')
		{
			$this->SQL = "DELETE FROM $this->table ".trim($this->SQL);
		}
		# If we are inserting values 
		else if ($this->operation === 'INSERT INTO')
		{
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
     * @access private
     */
	private function buildQuery()
	{
		# Build the select statement
		$SELECT = $this->operation === 'QUERY' ? $this->selectPending() : '';

		# Build the FROM statement
		$FROM = $this->operation === 'QUERY' ? "FROM $this->table" : '';

		# Build inner join
		$JOINS = $this->joinsPending();

		# Add weheres
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
     * @access private
     * @return mixed
     */
	private function execSQL()
	{
		# Execute the SQL
		$results = $this->connection->query(trim($this->SQL), $this->SQL_bindings);
		
		# If this was a row query - flatten and return only the first result
		if (!empty($results) && count($this->pending['limit']) === 1 && $this->pending['limit'] === 1 && $this->operation === 'QUERY')
		{
			return $results[0];
		}

		return $results;
	}

	/**
     * Add all the WHERE statements to current SQL query
     * 
     * @access private
     * @return string
     */
	private function wherePending(): string
	{
		# Has a join been specified ? i.e are we selecting from multiple tables
		$hasJoin = count($this->pending['column']) > 1;

		$wheres = [];

		if (!empty($this->pending['where']))
		{
			$count = 0;

			foreach ($this->pending['where'] as $clause)
			{
				if (is_array($clause['value']))
				{
					$_value = array_shift($clause['value']);

					$SQL = $hasJoin ? "$clause[table].$clause[column] $clause[op] :$_value" : "$clause[column] $clause[op] :$_value";

					foreach ($clause['value'] as $value)
					{
						$SQL .= $hasJoin ? " OR $clause[table].$clause[column] $clause[op] :$value" : " OR $clause[column] $clause[op] :$value";
					}

					$SQL = "($SQL)";
				}
				else
				{
					$SQL = $hasJoin ? "$clause[table].$clause[column] $clause[op] :$clause[value]" : "$clause[column] $clause[op] :$clause[value]";
				}
				
				if ($count > 0)
				{
					$SQL = strtoupper($clause['type'])." $SQL";
				}
				
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
     * @access private
     * @return string
     */
	private function groupByPending(): string
	{
		if (!empty($this->pending['group_by']))
		{
			return 'GROUP BY '.$this->pending['group_by'];
		}

		return '';
	}

	/**
     * Add the ORDER BY statement to the current SQL query if it exists 
     * 
     * @access private
     * @return string
     */
	private function orderByPending(): string
	{
		# Has a join been specified ? i.e are we selecting from multiple tables
		$hasJoin = count($this->pending['column']) > 1;

		if (!empty($this->pending['orderBy']))
		{
			if ($hasJoin)
			{
				return 'ORDER BY '.$this->pending['orderBy']['table'].'.'.$this->pending['orderBy']['column'].' '.$this->pending['orderBy']['direction'];
			}
			
			return 'ORDER BY '.$this->pending['orderBy']['column'].' '.$this->pending['orderBy']['direction'];
		}
		
		return '';
	}

	/**
     * Add the LIMIT statement to the current SQL query if it exists 
     * 
     * @access private
     * @return string
     */
	private function limitPending(): string
	{
		if (empty($this->pending['limit']))
		{
			return '';
		}
		
		if (is_array($this->pending['limit']))
		{
			return "LIMIT ".$this->pending['limit'][0].", ".$this->pending['limit'][1];
		}
		else
		{
			return "LIMIT ".$this->pending['limit'];
		}

		return '';
	}

	/**
     * Add the SELECT statement to the current SQL query if it exists 
     * 
     * @access private
     * @return string
     */
	private function selectPending(): string
	{
		# Has a join been specified ? i.e are we selecting from multiple tables
		$hasJoin = count($this->pending['column']) > 1;

		# Build the select statement
		$SELECT = '';

		# Reset the table name
		$this->setTable($this->table);

		# Loop the select statements
		if (!empty($this->pending['column']))
		{
			foreach ($this->pending['column'] as $table => $columns)
			{
				foreach ($columns as $col)
				{
					$SELECT .= $hasJoin ? " ".trim($table).".$col, " : " $col, ";
				}
			}

			$SELECT = 'SELECT '.rtrim($SELECT, ', ');
		}
		else
		{
			$SELECT = 'SELECT * ';
		}

		if (empty($SELECT) || $SELECT === 'SELECT ')
		{
			$SELECT = 'SELECT * ';
		}

		$GROUP_CONCAT = $this->groupConcatPending();

		if (empty($GROUP_CONCAT))
		{
			return trim($SELECT);
		}

		$SELECT = trim($SELECT).', '.$GROUP_CONCAT;

		return trim($SELECT);
	}

	/**
     * Add the GROUP_CONCAT statement to the current SQL query if it exists 
     * 
     * @access private
     * @return string
     */
	private function groupConcatPending(): string
	{
		$SQL = '';
		
		if (!empty($this->pending['group_concat']))
		{
			foreach ($this->pending['group_concat'] as $query)
			{
				$SQL .= "GROUP_CONCAT($query[0]) AS $query[1], ";
			} 
		}

		return rtrim($SQL, ', ');
	}

	/**
     * Add the join statements to the current SQL query if it exists 
     * 
     * @access private
     * @return string
     */
	private function joinsPending(): string
	{
		$SQL = [];
		
		$joins = 
		[
			'left_join'  => $this->pending['left_join'], 
			'inner_join' => $this->pending['inner_join'], 
			'right_join' => $this->pending['right_join'], 
			'full_outer_join' => $this->pending['full_outer_join']
		];

		foreach ($joins as $joinType => $joinTypeJoin)
		{
			if (!empty($joinTypeJoin))
			{
				foreach ($joinTypeJoin as $join)
				{
					$SQL[] = strtoupper(str_replace('_', ' ', $joinType))." $join[table] ON $join[columns]";
				}
			}
		}

		if (empty($SQL))
		{
			return '';
		}

		return implode(' ', $SQL);
	}

	/*******************************************************************************************************
	* PRIVATE HELPER MOTHODS
	*******************************************************************************************************/

	/**
	 * Reset the pending query parts to default
	 * @access private
     */
	private function setPending()
	{
		$this->pending =
		[
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
	private function queryFilter($str): string
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		
		$str = preg_replace('/[^A-Za-z]/', '', $str);
		
		while(isset($this->SQL_bindings[$str]))
		{
			$str .= $characters[rand(0, strlen($characters)-1)];
		}

		return $str;
	}
}
