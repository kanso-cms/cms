<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\database\connection;

/**
 * Database Connection Cache
 *
 * @author Joe J. Howard
 */
class Cache
{
    /**
     * Cached data by table
     *
     * @var array
     */
    private $data = [];

    /**
     * Current query string from connection
     *
     * @var string
     */
    private $queryStr;

    /**
     * Current query parameters from connection
     *
     * @var array
     */
    private $parameters;

    /**
     * Current table name from connection
     *
     * @var string
     */
    private $tableName;

    /**
     * Current query type name from connection
     *
     * @var string
     */
    private $queryType;

    /**
     * Current cache key for query
     *
     * @var string
     */
    private $cacheKey;

    /**
     * Constructor
     *
     * @access public
     */
	public function __construct()
	{
	}

    /**
     * Set the current query being executed
     *
     * @access public
     * @param  string $queryStr SQL query string
     * @param  array  $params   SQL query parameters
     */
    public function setQuery(string $queryStr, array $params)
    {
        $this->queryStr  = $queryStr;

        $this->params    = $params;

        $this->queryType = $this->getQueryType($queryStr);

        $this->tableName = $this->getTableName($queryStr);

        $this->cacheKey = $this->queryToKey($queryStr, $params);
    }

    /**
     * Is the query cached ?
     *
     * @access public
     * @return bool
     */
	public function has(): bool
    {
        if ($this->queryType !== 'select')
        {
            return false;
        }

        if (!isset($this->data[$this->tableName]))
        {
            return false;
        }

        return array_key_exists($this->cacheKey, $this->data[$this->tableName]);
    }

    /**
     * Get cached result
     *
     * @access public
     * @return  mixed
     */
    public function get()
    {
        if ($this->has())
        {            
            return $this->data[$this->tableName][$this->cacheKey];
        }

        return null;
    }

    /**
     * Save a cached result
     *
     * @access public
     * @param  mixed  $result Data to cache
     */
    public function put($result)
    {
        $this->data[$this->tableName][$this->cacheKey] = $result;
    }

    /**
     * Clear current table from results
     *
     * @access public
     */
    public function clear()
    {
        if (isset($this->data[$this->tableName]))
        {
            unset($this->data[$this->tableName]);
        }
    }

    /**
     * Returns the cache key based on query and params
     *
     * @access public
     * @param  string $queryStr SQL query string
     * @param  array  $params   SQL query parameters
     * @return string
     */
    private function queryToKey(string $query, array $params): string
    {
        $key = $query;

        foreach ($params as $i => $value)
        {
            $key .= $value;
        }

        return $key;
    }

    /**
     * Gets the table name based on the query string
     *
     * @access protected
     * @param  string    $query SQL query string
     * @return string
     */
    private function getTableName(string $query): string
    {
        preg_match("/(FROM|INTO|UPDATE)(\s+)(\w+)/i", $query, $matches);

        if (!$matches || !isset($matches[3]))
        {
            throw new Exception('Error retriving database query table name. Query: "'.$query.'"');
        }

        return trim($matches[3]);
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
