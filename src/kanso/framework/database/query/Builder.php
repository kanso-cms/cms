<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\database\query;

use kanso\framework\database\connection\Connection;
use kanso\framework\database\query\Query;

/**
 * Database SQL builder
 *
 * @author Joe J. Howard
 */
class Builder
{
    /**
     * Connection
     *
     * @var \kanso\framework\database\connection\Connection;
     */ 
	private $connection;

    /**
     * Query
     * 
     * @var \kanso\framework\database\query\Query
     */ 
	private $Query;
	
    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\database\connection\Connection $connection Database connection
     */
	public function __construct(Connection $connection)
	{
        # Save the database access instance locally
		$this->connection = $connection;

        # create a new query object
        $this->Query = new Query($connection);
	}

    /**
     * Get the database connection
     *
     * @access public
     * @return \kanso\framework\database\connection\Connection
     */
    public function connection(): Connection
    {
        return $this->connection;
    }

	/********************************************************************************
    * PUBLIC ACCESS FOR TABLE MANAGEMENT
    *******************************************************************************/

    /**
     * Create a new table with given columns and paramters
     *
     * @access public
     * @param  string $tableName Table name to create
     * @param  array  $params    Table parameters
     * @return \kanso\framework\database\query\Builder
     */
    public function CREATE_TABLE(string $tableName, array $params): Builder
    {
        # Filter the tablename
        $tableName  = $this->indexFilter($tableName);
        
        # Reset the id field
        $params['id'] = ' INT | UNSIGNED | UNIQUE | AUTO_INCREMENT';
        
        # Build the SQL
        $SQL = ["CREATE TABLE `$tableName` ("];
        
        # Loop the columns
        foreach ($params as $name => $params)
        {
            $name  = strtolower(str_replace(' ', '_', $name));
            $SQL[] = "`$name` ".str_replace('|', '', $params).',';
        }

        # Set default table configuration
        $SQL[] = "PRIMARY KEY (id)\n) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;";
    
        # Execute the query
        $this->connection->query(implode(' ', $SQL));

        # Set the table in the query
        $this->Query->setTable($tableName);

        # Return Builder for chaining
        return $this;
    }

    /**
     * Drop an existing table
     *
     * @access public
     * @param  string $tableName Table name to use
     * @return \kanso\framework\database\query\Builder
     */
    public function DROP_TABLE(string $tableName): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $this->Query->setTable(null);
        
        $this->connection->query("DROP TABLE $tableName");
        
        return $this;
    }

    /**
     * Truncate an existing table
     *
     * @access public
     * @param  string $tableName Table name to use
     * @return \kanso\framework\database\query\Builder
     */
    public function TRUNCATE_TABLE(string $tableName): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $this->Query->setTable($tableName);
        
        $this->connection->query("TRUNCATE TABLE $tableName"); 
        
        return $this;
    }

    /**
     * Initialize an alter statement
     *
     * @access public
     * @param  string    $tableName
     * @return \kanso\framework\database\query\Alter
     */
    public function ALTER_TABLE(string $tableName): Alter
    {
        $tableName = $this->indexFilter($tableName);
        
        $this->Query->setTable($tableName);
        
        return new Alter($this->connection, $tableName);
    }

    /********************************************************************************
    * PUBLIC ACCESS FOR ROW/DATA MANAGEMENT
    *******************************************************************************/

    /**
     * Set the query to query a given table
     *
     * @access public
     * @param  string $tableName The table name to use
     * @return \kanso\framework\database\query\Builder
     */
    public function FROM(string $tableName): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $this->Query->setTable($tableName);
        
        $this->Query->setOperation('QUERY');        
        
        return $this;
    }

    /**
     * Set the query to UPDATE a given table
     *
     * @access public
     * @param  string $tableName The table name to use
     * @return \kanso\framework\database\query\Builder
     */
    public function UPDATE(string $tableName): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $this->Query->setTable($tableName);
        
        return $this;
    }

    /**
     * Set the query to INSERT INTO a given table
     *
     * @access public
     * @param  string $tableName The table name to use
     * @return \kanso\framework\database\query\Builder
     */
    public function INSERT_INTO(string $tableName): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $this->Query->setTable($tableName);
        
        $this->Query->setOperation('INSERT INTO');
        
        return $this;
    }

    /**
     * Add the values to set
     *
     * @access public
     * @param  array $values The values to apply
     * @return \kanso\framework\database\query\Builder
     */
    public function VALUES(array $values): Builder
    {
        $this->Query->setOperation('INSERT INTO', $values);
        
        return $this;
    }

    /**
     * Set the query to SET and load values
     *
     * @access public
     * @param  array $values The values to apply
     * @return \kanso\framework\database\query\Builder
     */
    public function SET(array $values): Builder
    {
        $this->Query->setOperation('SET', $values);
        
        return $this;
    }

    /**
     * Set the query to DELETE and load table
     *
     * @access public
     * @param  string $table The table name to use
     * @return \kanso\framework\database\query\Builder
     */
    public function DELETE_FROM(string $tableName): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $this->Query->setTable($tableName);
        
        $this->Query->setOperation('DELETE');
        
        return $this;
    }

    /**
     * Execute an INSERT, DELETE, UPDATE, SET statement
     *
     * @access public
     * @return mixed Result from the SQL query
     */
    public function QUERY()
    {
        return $this->Query->query();
    }
    
    /********************************************************************************
    * PUBLIC ACCESS FOR QUERIES
    *******************************************************************************/

    /**
     * Select values from a table
     *
     * @access public
     * @param  string $columnNames Column names to select
     * @return \kanso\framework\database\query\Builder
     */
    public function SELECT(string $columnNames): Builder
    {
        $columnNames = $this->queryFilter($columnNames);
        
        $this->Query->select($columnNames);
        
        return $this;
    }

    /**
     * Set a where clause
     *
     * @access public
     * @param  string $column Column name
     * @param  string $op     Logical operator
     * @param  mixed  $value  Value
     * @return \kanso\framework\database\query\Builder
     */
    public function WHERE(string $column, string $op, $value): Builder
    {
        $column = $this->queryFilter($column);
        
        $this->Query->where($column, $op, $value);
        
        return $this;
    }

    /**
     * Set an and_where clause
     *
     * @access public
     * @param  string $column Column name
     * @param  string $op     Logical operator
     * @param  mixed  $value  Value
     * @return \kanso\framework\database\query\Builder
     */
    public function AND_WHERE(string $column, string $op, $value): Builder
    {
        $column = $this->queryFilter($column);
        
        $this->Query->and_where($column, $op, $value);
        
        return $this;
    }

    /**
     * Set an or_where clause
     *
     * @access public
     * @param  string $column Column name
     * @param  string $op     Logical operator
     * @param  mixed  $value  Value
     * @return \kanso\framework\database\query\Builder
     */
    public function OR_WHERE(string $column, string $op, $value): Builder
    {
        $column = $this->queryFilter($column);
        
        $this->Query->or_where($column, $op, $value);
        
        return $this;
    }   

    /**
     * Set an join clause
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $query     Column comparison e.g table1.id = table2.column_name
     * @return \kanso\framework\database\query\Builder
     */
    public function JOIN_ON(string $tableName, string $query): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $query = $this->queryFilter($query);
        
        $this->Query->join($tableName, $query);
        
        return $this;
    }

    /**
     * Set an inner join clause
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $query     Column comparison e.g table1.id = table2.column_name
     * @return \kanso\framework\database\query\Builder
     */
    public function INNER_JOIN_ON(string $tableName, string $query): Builder
    {
        $tableName = $this->indexFilter($tableName);

        $query = $this->queryFilter($query);

        $this->Query->join($tableName, $query);

        return $this;
    }

    /**
     * Set a left join clause
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $query     Column comparison e.g table1.id = table2.column_name
     * @return \kanso\framework\database\query\Builder
     */
    public function LEFT_JOIN_ON(string $tableName, string $query): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $query = $this->queryFilter($query);
        
        $this->Query->left_join($tableName, $query);
        
        return $this;
    }

    /**
     * Set a right join clause
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $query     Column comparison e.g table1.id = table2.column_name
     * @return \kanso\framework\database\query\Builder
     */
    public function RIGHT_JOIN_ON(string $tableName, string $query): Builder
    {
        $tableName = $this->indexFilter($tableName);
        
        $query = $this->queryFilter($query);
        
        $this->Query->right_join($tableName, $query);
        
        return $this;
    }

    /**
     * Set an outer join clause
     *
     * @access public
     * @param  string $tableName The table name to join
     * @param  string $query     Column comparison e.g table1.id = table2.column_name
     * @return \kanso\framework\database\query\Builder
     */
    public function OUTER_JOIN_ON(string $table, string $query): Builder
    {
        $table = $this->indexFilter($table);
        
        $query = $this->queryFilter($query);
        
        $this->Query->full_outer_join($table, $query);
        
        return $this;
    }

    /**
     * Set the orderby
     *
     * @access public
     * @param  string $key        The column name to use
     * @param  string $direction 'DESC'|'ASC' (optional) (default 'DESC')
     * @return \kanso\framework\database\query\Builder
     */
    public function ORDER_BY(string $key, string $direction = 'DESC'): Builder
    {
        $key = $this->queryFilter($key);
        
        $this->Query->order_by($key, $direction);
        
        return $this;
    }

    /**
     * Set group by
     *
     * @access public
     * @param  string $key The column name to group on
     * @return \kanso\framework\database\query\Builder
     */
    public function GROUP_BY(string $key): Builder
    {
        $key = $this->queryFilter($key);
        
        $this->Query->group_by($key);
        
        return $this;
    }

    /**
     * Add group concat
     *
     * @access public
     * @param  string $keys Concat keys
     * @param  string $as   As value
     * @return \kanso\framework\database\query\Builder
     */
    public function GROUP_CONCAT(string $keys, string $as): Builder
    {
        $keys = $this->queryFilter($keys);
        
        $this->Query->group_concat($keys, $as);
        
        return $this;
    }

    /**
     * Set the limit/offset
     *
     * @access public
     * @param  int $offset Offset to start at
     * @param  int $limit  Limit results (optional) (default null)
     * @return \kanso\framework\database\query\Builder
     */
    public function LIMIT(int $offset, int $limit = null): Builder
    {
        $this->Query->limit($offset, $limit);
        
        return $this;
    }

    /**
     * Execute a query and limit to single row
     *
     * @access public
     * @return mixed
     */
    public function ROW()
    {
        return $this->Query->row();
    }

    /**
     * Execute a query and limit to single row 
     * and/or find a single row by id
     *
     * @access public
     * @param  int   $id Row id to find (optional) (default null)
     * @return mixed
     */
    public function FIND(int $id = null)
    {
        return $this->Query->find($id);
    }

    /**
     * Execute a query and find all rows
     *
     * @access public
     * @return mixed
     */
    public function FIND_ALL()
    {
        return $this->Query->find_all();
    }

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Filter a column name to valid SQL
     *
     * @access private
     * @param  str $str Table index
     * @return str
     */
    private function indexFilter(string $str): string
    {
        # append the table prefix
        return $this->connection->tablePrefix().strtolower(str_replace(' ', '_', $str));
    }

    /**
     * Filter a column name to valid SQL
     *
     * @access private
     * @param  str $str Table index
     * @return str
     */
    private function queryFilter(string $query): string
    {
        # Check that the the query is using a dot notatation
        # on a column
        # e.g turn  posts.id -> kanso_posts.id
        if (strpos($query, '.') !== false)
        {
            return preg_replace('/(\w+\.)/', $this->connection->tablePrefix()."$1", $query);
        }

        # e.g turn  posts(id) -> kanso_posts(id)
        if (strpos($query, '(') !== false)
        {
            return preg_replace('/(\w+\()/', $this->connection->tablePrefix()."$1", $query);
        }
        
        return $query;
    }
}