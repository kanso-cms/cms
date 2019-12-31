<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\database\connection;

use kanso\framework\database\query\Builder;
use kanso\framework\database\query\Query;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Database connection.
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
	 * Database host.
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Database username.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Database username password.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Table prefix.
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
	 * @var \PDO|null
	 */
	protected $pdo;

	/**
	 *  Connection handler.
	 *
	 * @var \kanso\framework\database\connection\ConnectionHandler
	 */
	private $handler;

	/**
	 * Constructor.
	 *
	 * @param  array            $config Connection configuration
	 * @throws RuntimeException If connection type is not supported
	 */
	public function __construct(array $config, string $type = 'mysql')
	{
		if (isset($config['dsn']))
		{
			$this->dsn = $config['dsn'];
		}
		elseif ($type === 'mysql')
		{
			$this->dsn = "mysql:dbname=$config[name];host=$config[host]";
		}
		elseif ($type === 'sqlite')
		{
			$this->dsn = "sqlite:sqlite:$config[path]";
		}
		elseif ($type === 'oci' || 'oracle')
		{
			$this->dsn = "dbname=//$config[host]:$config[port]/$config[name]";
		}
		else
		{
			throw new RuntimeException('The provided database connection was either not provided or is not supported.');
		}

		$this->host = $config['host'] ?? null;

		$this->name = $config['name'] ?? null;

		$this->username = $config['username'] ?? null;

		$this->password = $config['password'] ?? null;

		$this->options = $config['options'] ?? null;

		$this->tablePrefix = $config['table_prefix'] ?? '';

		$this->pdo = $this->connect();
	}

	/**
	 * Creates a PDO instance.
	 *
	 * @return PDO
	 */
	protected function connect(): PDO
	{
		try
		{
			$this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->getConnectionOptions());
		}
		catch(PDOException $e)
		{
			throw new PDOException(vsprintf('%s(): Failed to connect to the [ %s ] database. %s', [__METHOD__, $this->name, $e->getMessage()]));
		}

		return $this->pdo;
	}

	/**
	 * Creates a new PDO instance.
	 */
	public function isConnected()
	{
		return !is_null($this->pdo);
	}

	/**
	 * Creates a new PDO instance.
	 */
	public function reconnect()
	{
		$this->pdo = $this->connect();

		return $this->pdo;
	}

	/**
	 * Creates a new PDO instance.
	 */
	public function pdo()
	{
		if (!$this->isConnected())
		{
			return $this->connect();
		}

		return $this->pdo;
	}

	/**
	 * Get the table prefix.
	 */
	public function tablePrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Checks if the connection is alive.
	 *
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
 	 * Close the current connection.
 	 */
 	public function close(): void
 	{
 		$this->pdo = null;
 	}

	/**
	 * Return a new Query builder instance.
	 *
	 * @return \kanso\framework\database\query\Builder
	 */
	public function builder(): Builder
	{
		return new Builder($this->handler(), new Query($this->handler()));
	}

	/**
	 * Return a new Query builder instance.
	 *
	 * @return \kanso\framework\database\connection\ConnectionHandler
	 */
	public function handler(): ConnectionHandler
	{
		if (!$this->handler)
		{
			$this->handler = new ConnectionHandler($this, new Cache);
		}

		return $this->handler;
	}

	/**
	 * Returns the connection options.
	 *
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
}
