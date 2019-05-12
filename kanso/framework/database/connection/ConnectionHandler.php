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
	private $log = [];

	/**
	 * Parameters for currently executing query statement.
	 *
	 * @var array
	 */
	private $parameters = [];

	/**
	 * PDO statement object returned from \PDO::prepare().
	 *
	 * @var \PDOStatement|\PDO
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
	public function cache(): Cache
	{
		return $this->cache;
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

	/**
	 * Add the parameter to the parameter array.
	 *
	 * @access public
	 * @param string $column Column key name
	 * @param string $value  Value to bind
	 */
	public function bind(string $column, $value)
	{
		$this->parameters[] = [$column, $this->sanitizeValue($value)];
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
	public function query(string $query, array $params = [], int $fetchmode = PDO::FETCH_ASSOC)
	{
		$start = microtime(true);

		$fromCache = false;

		// Query is either SELECT or SHOW
		if ($this->queryIsCachable($query))
		{
			$cacheParams = array_merge($this->parameters, $params);

			// Load from cache
			if ($this->cache->has($query, $cacheParams))
			{
				$fromCache = true;

				$result = $this->cache->get($query, $cacheParams);
			}
			// Execute query and cache the result
			else
			{
				$this->parseQuery($query, $params);

				$result = $this->pdoStatement->fetchAll($fetchmode);

				$this->cache->put($query, $cacheParams, $result);
			}
		}
		// Other queries e.g UPDATE, DELETE FROM, CREATE TABLE etc..
		else
		{
			$this->parseQuery($query, $params);

			$queryType = $this->getQueryType($query);

			$result = $queryType === 'select' || $queryType === 'show' ? $this->pdoStatement->fetchAll($fetchmode) : $this->pdoStatement->rowCount();

			if ($queryType === 'delete' || $queryType === 'update')
			{
				$this->cache->clear($query);
			}
		}

		// Log query
		$this->log($query, array_merge($this->parameters, $params), $start, $fromCache);

		// Reset parameters incase "parseQuery" was not called
		$this->parameters = [];

		return $result;
	}

	/**
	 * All SQL queries pass through this method.
	 *
	 * @access private
	 * @param string $query  SQL query statement
	 * @param array  $params Array of parameters to bind (optional) (default [])
	 */
	private function parseQuery(string $query, array $params = [])
	{
		// Prepare query
		$this->pdoStatement = $this->connection->pdo()->prepare($query);

		// Add parameters to the parameter array
		$this->bindMore($params);

		// Bind parameters
		if (!empty($this->parameters))
		{
			foreach($this->parameters as $_params)
			{
				$this->pdoStatement->bindParam(':' . $_params[0], $_params[1]);
			}
		}

		// Execute SQL
		$this->pdoStatement->execute();

		// Reset the parameters
		$this->parameters = [];
	}

	/**
	 * Tries to load the current query from the cache.
	 *
	 * @access private
	 * @param  string $query The type of query being executed e.g 'select'|'delete'|'update'
	 * @return bool
	 */
	private function queryIsCachable(string $query): bool
	{
		$queryType = $this->getQueryType($query);

		return $queryType === 'select' || $queryType === 'show';
	}

	/**
	 * Gets the query type from the query string.
	 *
	 * @access private
	 * @param  string $query SQL query
	 * @return string
	 */
	private function getQueryType(string $query): string
	{
		return strtolower(explode(' ', trim($query))[0]);
	}

	/**
	 * Sanitize a value.
	 *
	 * @access private
	 * @param  mixed $value A query value to sanitize
	 * @return mixed
	 */
	private function sanitizeValue($value)
	{
		if (is_int($value))
		{
			return $value;
		}
		elseif (is_bool($value))
		{
			return !$value ? 0 : 1;
		}
		elseif (is_string($value) && trim($value) === '' || is_null($value))
		{
			return null;
		}
		elseif (is_string($value))
		{
			return utf8_encode($value);
		}

		return $value;
	}

	/**
	 * Adds a query to the query log.
	 *
	 * @access private
	 * @param string $query     SQL query
	 * @param array  $params    Query parameters
	 * @param float  $start     Start time in microseconds
	 * @param bool   $fromCache Was the query loaded from the cache?
	 */
	private function log(string $query, array $params, float $start, bool $fromCache = false)
	{
		$time = microtime(true) - $start;

		$query = $this->prepareQueryForLog($query, $params);

		$this->log[] = ['query' => $query, 'time' => $time, 'from_cache' => $fromCache];
	}

	/**
	 * Prepares query for logging.
	 *
	 * @access private
	 * @param  string $query  SQL query
	 * @param  array  $params Query paramaters
	 * @return string
	 */
	private function prepareQueryForLog(string $query, array $params): string
	{
		foreach (array_reverse($params) as $k => $v)
		{
			$parentesis = '';

			if (is_null($v))
			{
				$v = 'NULL';
			}
			elseif (is_string($v))
			{
				$parentesis = '\'';
			}
			elseif (is_bool($v))
			{
				$v = $v === true ? 'TRUE' : 'FALSE';
			}
			elseif (is_array($v))
			{
				$v = implode('', $v);
			}

			$query = str_replace(":$k", $parentesis . $v . $parentesis, $query);
		}

		return $query;
	}
}
