<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\database\connection;

use PDO;

/**
 * Database connection handler.
 *
 * @author Joe J. Howard
 */
class ConnectionHandler
{
	/**
	 * Query log.
	 *
	 * @var array
	 */
	protected $log = [];

	/**
	 * Parameters for currently executing query statement.
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * @var PDO statement object returned from \PDO::prepare()
	 *
	 * \PDOStatement
	 */
	private $pdoStatement;

	/**
	 *  Database query cache.
	 *
	 * @var \kanso\framework\database\connection\Cache
	 */
	private $cache;

	/**
	 *  Database connection.
	 *
	 * @var \kanso\framework\database\connection\Connection
	 */
	private $connection;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param \kanso\framework\database\connection\Connection $connection PDO connection
	 * @param \kanso\framework\database\connection\Cache      $cache      Connection cache
	 */
	public function __construct(Connection $connection, Cache $cache)
	{
		$this->connection = $connection;

		$this->cache = $cache;
	}

	/**
	 * Returns the cache.
	 *
	 * @access public
	 * @return \kanso\framework\database\connection\Cache
	 */
	public function cache()
	{
		return $this->cache;
	}

	/**
	 * All SQL queries pass through this method.
	 *
	 * @access private
	 * @param string $query      SQL query statement
	 * @param array  $parameters Array of parameters to bind (optional) (default [])
	 */
	private function parseQuery(string $query, array $_params = [])
	{
		// Start time
		$start = microtime(true);

		// Prepare query
		$this->pdoStatement = $this->connection->pdo()->prepare($query);

		// Add parameters to the parameter array
		$this->bindMore($_params);

		// Bind parameters
		if (!empty($this->parameters))
		{
			foreach($this->parameters as $param)
			{
				$params = explode("\x7F", $param);

				$this->pdoStatement->bindParam($params[0], $params[1]);
			}
		}

		// Execute SQL
		$this->pdoStatement->execute();

		// Log
		$this->log($query, $this->parameters, $start);

		// Reset the parameters
		$this->parameters = [];
	}

	/**
	 * Add the parameter to the parameter array.
	 *
	 * @access public
	 * @param string $column Column key name
	 * @param string $value  Value to bind
	 */
	public function bind(string $column, $value)
	{
		$this->parameters[count($this->parameters)] = ':' . $column . "\x7F" . utf8_encode($value);
	}

	/**
	 * Add more parameters to the parameter array.
	 *
	 * @access public
	 * @param array $parray Array of column => value
	 */
	public function bindMore(array $parray = [])
	{
		if (empty($this->parameters) && is_array($parray) && !empty($parray))
		{
			$columns = array_keys($parray);

			foreach($columns as $i => &$column)
			{
				$this->bind($column, $parray[$column]);
			}
		}
	}

	/**
	 * If the SQL query contains a SELECT or SHOW statement it
	 * returns an array containing all of the result set row.
	 * If the SQL statement is a DELETE, INSERT, or UPDATE statement
	 * it returns the number of affected rows.
	 *
	 * @access public
	 * @param  string $query     The query to execute
	 * @param  array  $params    Assoc array of parameters to bind (optional) (default [])
	 * @param  int    $fetchmode PHP PDO::ATTR_DEFAULT_FETCH_MODE constant or integer
	 * @return mixed
	 */
	public function query(string $query, $params = [], int $fetchmode = PDO::FETCH_ASSOC)
	{
		if ($this->queryIsCachable($query))
		{
			$result = $this->loadQueryFromCache($query, array_merge($this->parameters, $params));

			if ($result === false)
			{
				$this->parseQuery($query, $params);

				$result = $this->pdoStatement->fetchAll($fetchmode);

				$this->cache->put($result);
			}
		}
		else
		{
			$queryType = $this->getQueryType($query);

			$this->parseQuery($query, $params);

			if ($queryType === 'select' || $queryType === 'show')
			{
				$result = $this->pdoStatement->fetchAll($fetchmode);
			}
			else
			{
				$result = $this->pdoStatement->rowCount();

				$this->cache->setQuery($query, array_merge($this->parameters, $params));

				$this->cache->clear();
			}
		}

		// Reset parameters incase "parseQuery" was not called
		$this->parameters = [];

		return $result;
	}

	/**
	 * Tries to load the current query from the cache.
	 *
	 * @access public
	 * @param  string      $query The type of query being executed e.g 'select'|'delete'|'update'
	 * @return array|false
	 */
	private function queryIsCachable(string $query): bool
	{
		if (!$this->cache->enabled())
		{
			return false;
		}

		$queryType = $this->getQueryType($query);

		return $queryType === 'select' || $queryType === 'show';
	}

	/**
	 * Tries to load the current query from the cache.
	 *
	 * @access public
	 * @param  string      $query The type of query being executed e.g 'select'|'delete'|'update'
	 * @return array|false
	 */
	private function loadQueryFromCache(string $query, array $params)
	{
		$this->cache->setQuery($query, $params);

		if ($this->cache->has())
		{
			return $this->cache->get();
		}

		return false;
	}

	/**
	 * Returns an array which represents a column from the result set.
	 *
	 * @access public
	 * @param  string $query  The query to execute
	 * @param  array  $params Assoc array of parameters to bind (optional) (default [])
	 * @return array
	 */
	public function column(string $query, array $params = [])
	{
		$this->parseQuery($query, $params);

		$cols = $this->pdoStatement->fetchAll(PDO::FETCH_NUM);

		$result = [];

		foreach($cols as $cells)
		{
			$result[] = $cells[0];
		}

		return $result;
	}

	/**
	 * Returns the table prefix for the connection.
	 *
	 * @access public
	 * @return string
	 */
	public function tablePrefix(): string
	{
		return $this->connection->tablePrefix();
	}

	/**
	 * Returns an array which represents a row from the result set.
	 *
	 * @access public
	 * @param  string $query     The query to execute
	 * @param  array  $params    Assoc array of parameters to bind (optional) (default [])
	 * @param  int    $fetchmode PHP PDO::ATTR_DEFAULT_FETCH_MODE constant or integer
	 * @return array
	 */
	public function row(string $query, array $params = [], $fetchmode = PDO::FETCH_ASSOC)
	{
		$this->parseQuery($query, $params);

		return $this->pdoStatement->fetch($fetchmode);
	}

	/**
	 * Returns the value of one single field/column.
	 *
	 * @access public
	 * @param  string $query  The query to execute
	 * @param  array  $params Assoc array of parameters to bind (optional) (default [])
	 * @return string
	 */
	public function single(string $query, array $params = [])
	{
		$this->parseQuery($query, $params);

		return $this->pdoStatement->fetchColumn();
	}

	/**
	 *  Returns the last inserted id.
	 *
	 * @access public
	 * @return mixed
	 */
	public function lastInsertId()
	{
		return $this->connection->pdo()->lastInsertId();
	}

	/**
	 * Returns the connection query log.
	 *
	 * @access public
	 * @return array
	 */
	public function getLog(): array
	{
		return $this->log;
	}

	/**
	 * Prepares query for logging.
	 *
	 * @access protected
	 * @param  string $query  SQL query
	 * @param  array  $params Query paramaters
	 * @return string
	 */
	protected function prepareQueryForLog(string $query, array $params): string
	{
		foreach ($params as $key => $value)
		{
			$_params = explode("\x7F", $value);

			$k = $_params[0];

			$v = $_params[1];

			$query = preg_replace("/$k/", $v, $query);
		}

		return $query;
	}

	/**
	 * Adds a query to the query log.
	 *
	 * @access protected
	 * @param string $query  SQL query
	 * @param array  $params Query parameters
	 * @param float  $start  Start time in microseconds
	 */
	protected function log(string $query, array $params, float $start)
	{
		$time = microtime(true) - $start;

		$query = $this->prepareQueryForLog($query, $params);

		$this->log[] = ['query' => $query, 'time' => $time];
	}

	/**
	 * Gets the query type from the query string.
	 *
	 * @access protected
	 * @param  string $query SQL query
	 * @return string
	 */
	protected function getQueryType(string $query): string
	{
		return strtolower(explode(' ', trim($query))[0]);
	}

    /**
     * Safely format the query consistently.
     *
     * @access  public
     * @param  string $sql SQL query statement
     * @return string
     */
    public function cleanQuery(string $sql): string
    {
       return trim(preg_replace('/\s+/', ' ', $sql));
    }
}
