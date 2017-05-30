<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

use kanso\framework\cache\Cache as FrameworkCache;

/**
 * Cache storage
 *
 * @author Joe J. Howard
 */
Class Cache 
{
    /**
     * Cache manager
     *
     * @var kanso\framework\cache\Cache       
     */
    private $cache;

    /**
     * The current request key
     *
     * @var string
     */
    private $key;

    /**
     * Is caching enabled ?
     *
     * @var bool
     */
    private $enabled;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct(FrameworkCache $cache, string $key, bool $enabled)
    {
        $this->key = $key;

        $this->enabled = $enabled;

        $this->cache = $cache;
    }

    /**
     * Disable caching
     *
     * @access public
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Enable caching
     *
     * @access public
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Is caching enabled ?
     *
     * @access public
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Load the response body
     *
     * @access public
     */
    public function get()
    {
        if ($this->has())
        {
            return $this->cache->get($this->key);
        }
    }

    /**
     * Save the response body
     *
     * @access public
     * @param  string $data  Data to store
     */
    public function put(string $data)
    {
        $this->cache->put($this->key, $data);
    }

    /**
     * Check if the response body exists
     *
     * @access public
     * @param  string $key Key to check
     */
    public function has()
    {
        if ($this->expired())
        {
            $this->delete();

            return false;
        }

        return $this->cache->has($this->key);
    }

    /**
     * Remove the response body
     *
     * @access public
     * @param  string $key Key to delete
     */
    public function delete()
    {
        $this->cache->delete($this->key);
    }

    /**
     * Checks if the response body expired
     *
     * @access public
     * @param  string $key Key to check
     */
    public function expired()
    {
        return $this->cache->expired($this->key);
    }
}
