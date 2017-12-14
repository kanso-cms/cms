<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\database\connection;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use Exception;
use kanso\framework\database\connection\Cache;
use kanso\framework\database\query\Builder;

/**
 * Database connection
 *
 * @author Joe J. Howard
 */
class Connection
{
	/**
	 * Database name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Database host
	 *
	 * @var string.
	 */
	protected $host;

	/**
	 * Database username
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Database username password
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Table prefix
	 *
	 * @var string
	 */
	protected $tablePrefix;

	/**
	 * Connection DSN.
	 *
	 * @var string
	 */
	protected $dsn;

	/**
	 * PDO options.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * PDO object.
	 *
	 * @var \PDO
	 */
	protected $pdo;

	/**
	 * Query log.
	 *
	 * @var array
	 */
	protected $log = [];

	/**
	 * Parameters for currently executing query statement
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
	 * Constructor.
	 *
	 * @access public
	 * @param  array $config Connection configuration
	 * @throws RuntimeException If connection type is not supported
	 */
	public function __construct(array $config, string $type = 'mysql')
	{
		if (isset($config['dsn']))
		{
			$this->dsn = $config['dsn'];
		}
		else if ($type === 'mysql')
		{
			$this->dsn = "mysql:dbname=$config[name];host=$config[host]";
		}
		else if ($type === 'sqlite')
		{
			$this->dsn = "sqlite:sqlite:$config[path]";
		}
		else if ($type === 'oci' || 'oracle')
		{
			$this->dsn = "dbname=//$config[host]:$config[port]/$config[name]";
		}
		else
		{
			throw new RuntimeException("The provided database connection was either not provided or is not supported.");
		}

		$this->host = $config['host'] ?? null;

		$this->name = $config['name'] ?? null;

		$this->username = $config['username'] ?? null;

		$this->password = $config['password'] ?? null;

		$this->options = $config['options'] ?? null;

		$this->tablePrefix = $config['table_prefix'] ?? '';

		$this->cache = new cache;
		
		$this->pdo = $this->connect();
	}

	/**
	 * Creates a PDO instance.
	 *
	 * @access protected
	 * @return \PDO
	 */
	protected function connect(): PDO
	{
		try
		{
			$pdo = new PDO($this->dsn, $this->username, $this->password, $this->getConnectionOptions());
		}
		catch(PDOException $e)
		{
			throw new PDOException(vsprintf("%s(): Failed to connect to the [ %s ] database. %s", [__METHOD__, $this->name, $e->getMessage()]));
		}
		
		return $pdo;
	}

	/**
	 * Creates a new PDO instance.
	 *
	 * @access public
	 */
	public function isConnected()
	{
		return !is_null($this->pdo);
	}

	/**
	 * Creates a new PDO instance.
	 *
	 * @access public
	 */
	public function reconnect()
	{
		$this->pdo = $this->connect();
	}

	/**
	 * Get the table prefix
	 *
	 * @access public
	 */
	public function tablePrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Checks if the connection is alive.
	 *
	 * @access public
	 * @return bool
	 */
	public function isAlive(): bool
	{
		try
		{
			$this->pdo->query('SELECT 1');
		}
		catch(PDOException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Close the current connection
	 *
	 * @access public
	 */
 	public function close()
 	{
 		$this->pdo = null;
 	}

 	/**
	 * Return a new Query builder instance
	 *
	 * @return \kanso\framework\database\query\Builder
	 */
	public function builder(): Builder
	{
		return new Builder($this);
	}

	/**
	 * Returns the connection options.
	 *
	 * @access protected
	 * @return array
	 */
	protected function getConnectionOptions(): array
	{
		return
		[
			PDO::ATTR_PERSISTENT         => $this->options['ATTR_PERSISTENT'] ?? false,
			PDO::ATTR_ERRMODE            => $this->options['ATTR_ERRMODE'] ?? PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => $this->options['ATTR_DEFAULT_FETCH_MODE'] ?? PDO::FETCH_ASSOC,
			PDO::MYSQL_ATTR_INIT_COMMAND => $this->options['MYSQL_ATTR_INIT_COMMAND'] ?? 'SET NAMES utf8',
			PDO::ATTR_STRINGIFY_FETCHES  => $this->options['ATTR_STRINGIFY_FETCHES'] ?? false,
			PDO::ATTR_EMULATE_PREPARES   => $this->options['ATTR_EMULATE_PREPARES'] ?? false,
		];
	}

	/**
	 * All SQL queries pass through this method
	 * 
	 * @access private
	 * @param  string  $query      SQL query statement
	 * @param  array   $parameters Array of parameters to bind (optional) (default [])
	 */	
	private function parseQuery(string $query, array $_params = [])
	{
		# Start time
		$start = microtime(true);	

		# If not connected, connect to the database.
		if (!$this->isConnected())
		{
			$this->reconnect();
		}

		# Prepare query
		$this->pdoStatement = $this->pdo->prepare($query);
		
		# Add parameters to the parameter array	
		$this->bindMore($_params);

		# Bind parameters
		if (!empty($this->parameters))
		{
			foreach($this->parameters as $param)
			{
				$params = explode("\x7F", $param);
				
				$this->pdoStatement->bindParam($params[0], $params[1]);
			}		
		}

		# Execute SQL
		$this->pdoStatement->execute();

		# Log
		$this->log($query, $this->parameters, $start);

		# Reset the parameters
		$this->parameters = [];
	}

    /**
	 * Add the parameter to the parameter array
	 * 
	 * @access public
	 * @param string $column Column key name
	 * @param string $value  Value to bind
	 */	
	public function bind(string $column, $value)
	{
		$this->parameters[sizeof($this->parameters)] = ":" . $column . "\x7F" . utf8_encode($value);
	}
   
    /**
	 * Add more parameters to the parameter array
	 *
	 * @access public
	 * @param  array  $parray Array of column => value
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
	 * it returns the number of affected rows
	 *
	 * @access public
	 * @param  string $query     The query to execute
	 * @param  array  $params    Assoc array of parameters to bind (optional) (default [])
	 * @param  int    $fetchmode PHP PDO::ATTR_DEFAULT_FETCH_MODE constant or integer
	 * @return mixed
	 */			
	public function query(string $query, $params = [], int $fetchmode = PDO::FETCH_ASSOC)
	{
		# Initiate the cache
		$this->cache->setQuery($query, array_merge($this->parameters, $params));

		# Get the statement type
		$queryType = $this->getQueryType($query);

		# If this is a SELECT we can test the cache
		if ($queryType === 'select')
		{
			if (!$this->cache->has())
			{
				$this->parseQuery($query, $params);
			}			
		}
		# Otherwise we can PDO
		else
		{
			$this->parseQuery($query, $params);
		}

		# Reset the parameters incase we didn't parse the query
		$this->parameters = [];

		# Return appropriate
		if ($queryType === 'select' || $queryType === 'show')
		{
			if ($this->cache->has())
			{				
				return $this->cache->get();
			}

			$result = $this->pdoStatement->fetchAll($fetchmode);

			$this->cache->put($result);

			return $result;
		}
		elseif ( $queryType === 'insert' ||  $queryType === 'update' || $queryType === 'delete' )
		{
			return $this->pdoStatement->rowCount();	
		}	
		else
		{
			return NULL;
		}
	}
		
    /**
	 *Returns an array which represents a column from the result set 
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
	 * Returns an array which represents a row from the result set 
	 *
	 * @access public
	 * @param  string $query     The query to execute
	 * @param  array  $params    Assoc array of parameters to bind (optional) (default [])
	 * @param  int    $fetchmode PHP PDO::ATTR_DEFAULT_FETCH_MODE constant or integer
	 * @return array
	 */	
	public function row(string $query, array $params = [], $fetchmode = PDO::FETCH_ASSOC)
	{				
		$this->parseQuery($query,$params);
		
		return $this->pdoStatement->fetch($fetchmode);			
	}
   
    /**
	 * Returns the value of one single field/column
	 *
	 * @access public
 	 * @param  string $query  The query to execute
	 * @param  array  $params Assoc array of parameters to bind (optional) (default [])
	 * @return string
	 */	
	public function single(string $query, array $params = [])
	{
		$this->parseQuery($query,$params);
		
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
		return $this->pdo->lastInsertId();
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
		return preg_replace_callback('/\?/', function($matches) use (&$params)
		{
			$param = array_shift($params);

			if(is_int($param) || is_float($param))
			{
				return $param;
			}
			elseif(is_bool(($param)))
			{
				return $param ? 'TRUE' : 'FALSE';
			}
			elseif(is_object($param))
			{
				return get_class($param);
			}
			elseif(is_null($param))
			{
				return 'NULL';
			}
			else
			{
				return $this->pdo->quote($param);
			}
		}, $query);
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
	 * Gets the query type from the query string
	 *
	 * @access protected
	 * @param  string    $query SQL query
	 * @return string
	 */
	protected function getQueryType(string $query): string
	{
		return strtolower(explode(" ", trim($query))[0]);
	}
}
