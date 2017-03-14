<?php

namespace Kanso\Database;

/**
 * Database class
 *
 * The database class parses all database queries 
 * for Kanso. 
 */
class Database
{
	/**
     * @var \Kanso\Kanso->Config()
     */ 
	private $settings;

	/**
     * @var boolean
     */ 
	private $isConnected = false;

	/**
     * @var \PDO
     */ 
	private $pdo;

	/**
     * @var PDOStatement object returned from \PDO::prepare()
     */ 
	private $pdoStatement;

	/**
     * @var array 
     */ 
	private $parameters;

	/**
     * @var string
     */ 
	public $tablePrefix;
		
   	/**
     * Constructor
     *
     * @param array $settings (optional)
     */
	public function __construct($settings = null)
	{
		# Set the default settings
		if ($settings) {
			$this->settings = $settings;
		}
		else {
			$this->settings = \Kanso\Kanso::getInstance()->Config;
		}

		# Establish a DB connection	
		$this->Connect();

		# Clear the parameters
		$this->parameters  = [];

		# Set the table prefix
		$this->tablePrefix = $this->settings['table_prefix'];
	}

	/**
	 * Return a new Query builder instance
	 *
	 * @return Kanso\Database\Query\Builder
	 */
	public function Builder()
	{
		return new \Kanso\Database\Query\Builder($this);
	}

    /**
	 * Connect to the databse
	 *
	 * @throws PDOException
	 */
	private function Connect()
	{
		
		# Set the dsn
		$dsn = 'mysql:dbname='.$this->settings["dbname"].';host='.$this->settings["host"].'';
		
		# Read make a UTF8 database connection
		$this->pdo = new \PDO($dsn, $this->settings["user"], $this->settings["password"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		
		# We can now log any exceptions on Fatal error
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		
		# Disable emulation of prepared statements, use REAL prepared statements instead
		$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		
		# Connection succeeded, set the boolean to true
		$this->isConnected = true;

	}

	/*
	 * Close the current connection
	 *
	 */
 	public function CloseConnection()
 	{
 		$this->pdo = null;
 		$this->isConnected = false;
 	}
		
    /**
	 * All SQL queries pass through this method
	 * 
	 * @param string 	 $query         SQL query statement
	 * @param array      $parameters    Array of parameters to bind
	 */	
	private function Init($query, $parameters = "")
	{

		# If not connected, connect to the database.
		if (!$this->isConnected) $this->Connect();

		# Prepare query
		$this->pdoStatement = $this->pdo->prepare($query);
		
		# Add parameters to the parameter array	
		$this->bindMore($parameters);

		# Bind parameters
		if (!empty($this->parameters)) {
			foreach($this->parameters as $param) {
				$parameters = explode("\x7F",$param);
				$this->pdoStatement->bindParam($parameters[0], $parameters[1]);
			}		
		}

		# Execute SQL 
		$this->succes = $this->pdoStatement->execute();		

		# Reset the parameters
		$this->parameters = [];
	}

    /**
	 * Add the parameter to the parameter array
	 * 
	 * @param string    $para  
	 * @param string    $value 
	 */	
	public function bind($para, $value)
	{	
		$this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . utf8_encode($value);
	}
   
    /**
	 * Add more parameters to the parameter array
	 *
	 * @param array     $parray
	 */	
	public function bindMore($parray)
	{
		if (empty($this->parameters) && is_array($parray)) {
			$columns = array_keys($parray);
			foreach($columns as $i => &$column)	{
				$this->bind($column, $parray[$column]);
			}
		}
	}
   
    /**
	 * If the SQL query  contains a SELECT or SHOW statement it 
	 * returns an array containing all of the result set row.
	 * If the SQL statement is a DELETE, INSERT, or UPDATE statement 
	 * it returns the number of affected rows
	 *
	 * @param  string    $query
	 * @param  array     $params
	 * @param  int       $fetchmode
	 * @return mixed
	 */			
	public function query($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC)
	{
		# Trim the query
		$query = trim($query);

		# Run the query
		$this->Init($query, $params);

		# Explode the statement
		$rawStatement = explode(" ", $query);
		
		# Which SQL statement is used 
		$statement = strtolower($rawStatement[0]);
		
		# Return appropriate
		if ($statement === 'select' || $statement === 'show') {
			return $this->pdoStatement->fetchAll($fetchmode);
		}
		elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) {
			return $this->pdoStatement->rowCount();	
		}	
		else {
			return NULL;
		}
	}
	
    /**
     *  Returns the last inserted id.
     *
     *  @return mixed
     */	
	public function lastInsertId() 
	{
		return $this->pdo->lastInsertId();
	}	
		
    /**
	 *	Returns an array which represents a column from the result set 
	 *
	 * @param  string    $query
	 * @param  array     $params
	 * @return array
	 */	
	public function column($query, $params = null)
	{
		$this->Init($query,$params);
		$Columns = $this->pdoStatement->fetchAll(\PDO::FETCH_NUM);		
		
		$column = null;

		foreach($Columns as $cells) {
			$column[] = $cells[0];
		}

		return $column;
	}	

    /**
	 * Returns an array which represents a row from the result set 
	 *
	 * @param  string    $query
	 * @param  array     $params
	 * @param  int       $fetchmode
	 * @return array
	 */	
	public function row($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC)
	{				
		$this->Init($query,$params);
		return $this->pdoStatement->fetch($fetchmode);			
	}
   
    /**
	 * Returns the value of one single field/column
	 *
	 * @param  string    $query
	 * @param  array     $params
	 * @return string
	 */	
	public function single($query,$params = null)
	{
		$this->Init($query,$params);
		return $this->pdoStatement->fetchColumn();
	}
   		
}