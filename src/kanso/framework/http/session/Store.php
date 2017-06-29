<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session;

use kanso\framework\utility\Arr;

/**
 * Session storer
 *
 * @author Joe J. Howard
 */
class Store
{
    /**
     * Constructor
     *
     * @access public
     * @param  $configuration array Array of configuration options
     */
    public function __construct()
    {
    }

    /**
     * Read the session
     *
     * @access public
     * @return array
     */
    public function read(string $key): array
    {
        $data = [];
        
        if (isset($_SESSION[$key]))
        {
            $data = unserialize($_SESSION[$key]);
        }

        return $data;
    }

    /**
     * Write before it gets sent
     *
     * @access public
     */
    public function write(string $key, array $data)
    {
        $_SESSION[$key] = serialize($data);
    }
}
