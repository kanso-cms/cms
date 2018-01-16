<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session\storage;

use kanso\framework\http\session\storage\StoreInterface;
use kanso\framework\utility\UUID;
use kanso\framework\security\Crypto;

/**
 * Session encrypt/decrypt
 *
 * @author Joe J. Howard
 */
class NativeSessionStorage implements StoreInterface
{
    /**
     * Has the garbage been collected
     *
     * @var kanso\framework\security\Crypto
     */
    private $crypto;

    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\security\Crypto $Crypto        Encryption service
     * @param  array                            $cookieParams  Assoc array of cookie configurations
     */
    public function __construct(Crypto $crypto, array $cookieParams = [], string $path = '')
    {
        $this->crypto = $crypto;

        if ($cookieParams)
        {
            $this->session_set_cookie_params($cookieParams);
        }

        if ($path)
        {
            $this->session_save_path($path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function session_save_path(string $path = '')
    {
        if ($path)
        {
            session_save_path($path);
        }

        return session_save_path();
    }
    
    /**
     * {@inheritdoc}
     */
    public function session_start()
    {
        # Start a PHP session
        if ( session_id() == '' || !isset($_SESSION))
        {
            if (!headers_sent())
            { 
                session_start();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function session_destroy()
    {
        session_destroy();
    }

    /**
     * {@inheritdoc}
     */
    public function session_id(string $id = null)
    {
        return session_id($id);
    }

    /**
     * {@inheritdoc}
     */
    public function session_name(string $name = null)
    {
        if ($name)
        {
            return session_name($name);
        }
        
        return session_name();
    }

    /**
     * {@inheritdoc}
     */
    public function session_regenerate_id(bool $deleteOldSession = false)
    {
        session_regenerate_id();
    }

    /**
     * {@inheritdoc}
     */
    public function session_set_cookie_params(array $params)
    {
        session_set_cookie_params(
            $params['expire'],
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    /**
     * Get the cookie parameters
     *
     * @access public
     * @return array
     */
    public function session_get_cookie_params()
    {
        return session_get_cookie_params();
    }

    /**
     * Collect garbage (delete expired sessions)
     *
     * @access public
     */
    public function session_gc()
    {
        session_gc();
    }

    /**
     * Read and return the session data
     *
     * @access public
     * @return array|null
     */
    public function read()
    {
        if (isset($_SESSION) && isset($_SESSION[$this->session_name()]))
        {
            return $_SESSION[$this->session_name()];
        }

        return null;
    }

    /**
     * Write the session data
     *
     * @access public
     * @param  array $data Data to write to session
     */
    public function write(array $data)
    {
        $_SESSION[$this->session_name()] = $data;
    }

    /**
     * Send the session cookie
     *
     * @access public
     */
    public function send()
    {

    }
}
