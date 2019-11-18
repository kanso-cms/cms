<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cache\stores;

/**
 * Cache file storage interface.
 *
 * @author Joe J. Howard
 */
interface StoreInterface
{
    /**
     * Load a key value.
     *
     * @param string $key Key to load
     */
    public function get(string $key);

    /**
     * Save a key value.
     *
     * @param string $key  Key to save the output
     * @param string $data Data to store
     */
    public function put(string $key, string $data);

    /**
     * Check if a key is stored.
     *
     * @param string $key Key to check
     */
    public function has(string $key): bool;

    /**
     * Remove a key value.
     *
     * @param string $key Key to delete
     */
    public function delete(string $key);

    /**
     * Checks is key value is expired.
     *
     * @param string $key    Key to check
     * @param int    $maxAge Unix timestamp of max expiry
     */
    public function expired(string $key, int $maxAge): bool;

    /**
     * Clear the entire cache.
     */
    public function clear();
}
