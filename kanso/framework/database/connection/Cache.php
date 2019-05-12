<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\database\connection;

use Exception;

/**
 * Database Connection Cache.
 *
 * @author Joe J. Howard
 */
class Cache
{
    /**
     * Cached data by table.
     *
     * @var array
     */
    private $data = [];

    /**
     * Is the cache enabled?
     *
     * @var bool
     */
    private $enabled;

    /**
     * Constructor.
     *
     * @access public
     * @param bool $enabled Enable or disable the cahce
     */
    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * Is the cache enabled?
     *
     * @access public
     * @return bool
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the cache.
     *
     * @access public
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disable the cache.
     *
     * @access public
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Is the query cached ?
     *
     * @access public
     * @param  string $queryStr SQL query string
     * @param  array  $params   SQL query parameters
     * @return bool
     */
    public function has(string $queryStr, array $params): bool
    {
        $tableName = $this->getTableName($queryStr);
        $cacheKey  = $this->queryToKey($queryStr, $params);

        if (!$this->enabled)
        {
            return false;
        }
        elseif ($this->getQueryType($queryStr) !== 'select')
        {
            return false;
        }
        elseif (!isset($this->data[$tableName]))
        {
            return false;
        }

        return array_key_exists($cacheKey, $this->data[$tableName]);
    }

    /**
     * Get cached result.
     *
     * @access public
     * @param  string $queryStr SQL query string
     * @param  array  $params   SQL query parameters
     * @return mixed
     */
    public function get(string $queryStr, array $params)
    {
        if ($this->has($queryStr, $params))
        {
            return $this->data[$this->getTableName($queryStr)][$this->queryToKey($queryStr, $params)];
        }

        return false;
    }

    /**
     * Save a cached result.
     *
     * @access public
     * @param string $queryStr SQL query string
     * @param array  $params   SQL query parameters
     * @param mixed  $result   Data to cache
     */
    public function put(string $queryStr, array $params, $result)
    {
        if ($this->enabled)
        {
            $this->data[$this->getTableName($queryStr)][$this->queryToKey($queryStr, $params)] = $result;
        }
    }

    /**
     * Clear current table from results.
     *
     * @access public
     */
    public function clear(string $queryStr)
    {
        $tableName = $this->getTableName($queryStr);

        if (isset($this->data[$tableName]))
        {
            unset($this->data[$tableName]);
        }
    }

    /**
     * Returns the cache key based on query and params.
     *
     * @access private
     * @param  string $query  SQL query string
     * @param  array  $params SQL query parameters
     * @return string
     */
    private function queryToKey(string $query, array $params): string
    {
        $key = $query;

        foreach ($params as $i => $value)
        {
            if (is_null($value))
            {
                $value = 'NULL';
            }
            elseif (is_bool($value))
            {
                $value = $value === true ? 'TRUE' : 'FALSE';
            }
            elseif (is_array($value))
            {
                $value = implode('', $value);
            }

            $key .= $value;
        }

        return md5($key);
    }

    /**
     * Gets the table name based on the query string.
     *
     * @access private
     * @param  string $query SQL query string
     * @return string
     */
    private function getTableName(string $query): string
    {
        if (in_array($this->getQueryType($query), ['drop', 'create', 'show', 'alter', 'start', 'stop']))
        {
            return 'NULL';
        }

        preg_match("/(FROM|INTO|UPDATE)(\s+)(\w+)/i", $query, $matches);

        if (!$matches || !isset($matches[3]))
        {
            throw new Exception('Error retrieving database query table name. Query: "' . $query . '"');
        }

        return trim($matches[3]);
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
}
