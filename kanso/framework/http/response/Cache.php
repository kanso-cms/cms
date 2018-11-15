<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

use kanso\framework\cache\Cache as FrameworkCache;

/**
 * Cache storage.
 *
 * @author Joe J. Howard
 */
class Cache
{
    /**
     * Cache manager.
     *
     * @var \kanso\framework\cache\Cache
     */
    private $cache;

    /**
     * The current request key.
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
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\cache\Cache $cache   Framework caching utility
     * @param string                       $key     The key to cache the current request under
     * @param bool                         $enabled Enable of disable the cache (optional) (default false)
     */
    public function __construct(FrameworkCache $cache, string $key, bool $enabled = false)
    {
        $this->key = $key;

        $this->enabled = $enabled;

        $this->cache = $cache;
    }

    /**
     * Disable caching.
     *
     * @access public
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Enable caching.
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
     * Load the response body.
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
     * Save the response body.
     *
     * @access public
     * @param string $data Data to store
     */
    public function put(string $data)
    {
        $this->cache->put($this->key, $data);
    }

    /**
     * Check if the response body exists.
     *
     * @access public
     * @return bool
     */
    public function has(): bool
    {
        if ($this->expired())
        {
            $this->delete();

            return false;
        }

        return $this->cache->has($this->key);
    }

    /**
     * Remove the response body.
     *
     * @access public
     */
    public function delete()
    {
        $this->cache->delete($this->key);
    }

    /**
     * Checks if the response body expired.
     *
     * @access public
     */
    public function expired()
    {
        return $this->cache->expired($this->key);
    }
}
