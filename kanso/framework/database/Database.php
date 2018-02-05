<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\database;

use kanso\framework\database\connection\Connection;
use kanso\framework\database\query\Builder;
use RuntimeException;

/**
 * Database manager
 *
 * @author Joe J. Howard
 */
class Database
{
	/**
	 * Name of the default configuration to use
	 * 
     * @var string
     */
	private $default;

	/**
	 * Array of configurations settings
	 * 
     * @var array
     */ 
	private $configurations;

	/**
	 * Array of database connections
	 * 
     * @var array
     */ 
	private $connections = [];
		
   	/**
     * Constructor
     *
     * @access public
     * @param  array $settings 
     */
	public function __construct(array $configurations)
	{
		$this->configurations = $configurations['configurations'];

		$this->default = $configurations['default'];
	}

	/**
     * Create a new database from the config
     *
     * @access public
     * @param  string $connectionName Name of the connection (optional) (default null)
	 * @return \kanso\framework\database\connection\Connection|false
	 * @throws \RuntimeException
     */
	public function create(string $connectionName = null)
	{
		$connectionName = !$connectionName ? $this->default : $connectionName;

		if (!isset($this->configurations[$connectionName]['name']))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the database configuration.", [__METHOD__, $connectionName]));
		}

		$config = $this->configurations[$connectionName];

		$databaseName = $config['name'];

		$config['dsn'] = "mysql:host=$config[host]";
		
		$connection = new Connection($config);

		$connection->Query("DROP DATABASE IF EXISTS $databaseName");

		$connection->Query("CREATE DATABASE $databaseName");

		return $this->connect($connectionName);
	}

	/**
     * Get a database connection by name
     *
     * @access public
     * @param  string $connectionName Name of the connection (optional) (default null)
     * @return \kanso\framework\database\connection\Connection
     */
	public function connection(string $connectionName = null): Connection
	{
		$connectionName = !$connectionName ? $this->default : $connectionName;

		return $this->connect($connectionName);
	}

	/**
     * Connect to a database by name
     *
     * @access private
     * @param  string $connectionName Name of the connection
     * @return \kanso\framework\database\connection\Connection
     * @throws \RuntimeException
     */
	private function connect(string $connectionName): Connection
	{
		if(!isset($this->configurations[$connectionName]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the database configuration.", [__METHOD__, $connectionName]));
		}

		if (isset($this->connections[$connectionName]))
		{
			return $this->connections[$connectionName];
		}

		$this->connections[$connectionName] = new Connection($this->configurations[$connectionName]);

		return $this->connections[$connectionName];
	}

	/**
     * Get a database builder by connection name
     *
     * @param  string $connectionName Name of the connection
     * @return \kanso\framework\database\query\Builder
     */
	public function builder(string $connectionName): Builder
	{
		return $this->connect($connectionName)->builder();
	}
}
