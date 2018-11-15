<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cache;

use kanso\framework\cache\stores\StoreInterface;

/**
 * Cache storage.
 *
 * @author Joe J. Howard
 */
class Cache
{
    /**
     * Unix timestamp of max cache lifetime.
     *
     * @var int
     */
    private $lifetime;

    /**
     * Storage implementation.
     *
     * @var \kanso\framework\cache\stores\StoreInterface
     */
    private $store;

    /**
     * Constructor.
     *
     * @access public
     * @param int                                          $lifetime Date the cache will expire (unix timestamp)
     * @param \kanso\framework\cache\stores\StoreInterface $store    Storage impementation
     */
    public function __construct(int $lifetime, StoreInterface $store)
    {
        $this->store = $store;

        $this->lifetime = $lifetime;
    }

    /**
     * Load a key value.
     *
     * @access public
     * @param string $key Key to load
     */
    public function get(string $key): string
    {
        return $this->store->get($key);
    }

    /**
     * Save a key value.
     *
     * @access public
     * @param string $key  Key to save the output
     * @param string $data Data to store
     */
    public function put(string $key, string $data)
    {
        $this->store->put($key, $data);
    }

    /**
     * Check if a key is stored.
     *
     * @access public
     * @param string $key Key to check
     */
    public function has(string $key): bool
    {
        return $this->store->has($key);
    }

    /**
     * Remove a key value.
     *
     * @access public
     * @param string $key Key to delete
     */
    public function delete(string $key)
    {
        $this->store->delete($key);
    }

    /**
     * Checks is key value is expired.
     *
     * @access public
     * @param string $key Key to check
     */
    public function expired(string $key): bool
    {
        return $this->store->expired($key, $this->lifetime);
    }

    /**
     * Clear the entire cache.
     *
     * @access public
     */
    public function clear()
    {
        $this->store->clear();
    }
}
