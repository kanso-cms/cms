<?php

namespace Kanso\Storage;

/**
* Session manager
*
*/
class Session
{
    /**
     * @var array 
     */
    private $sessionData = [];

    /**
     * @var array 
     */
    private $flashData = [];

    /**
     * @var string 
     */
    private $token;

    /**
     * @var string
     */
    private $cookieName = 'kanso_session';

    /**
     * @var string
     */
    private $sessionKey = 'kanso_session';

    /**
     * @var string
     */
    private $flashKey = 'kanso.flash';

    /**
     * @var string
     */
    private $tokenKey = 'kanso.token';

    /**
     * @var string
     */
    private $lifetime = '1 week';

    /**
     * @var boolean
     */
    private $started = false;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        # Initialize
        $this->init();
    }

    public function init()
    {
        # Don't run twice
        if ($this->started) return;

        $this->started = true;

        # Set the cookie
        $this->setcookie();

        # Start the session
        $this->start();

        # Read the session data
        $data = $this->read();

        # Get the session data if it exists
        if ($data && is_array($data)) $this->sessionData = $data;

        # Get the flash data if it exists
        if (isset($this->sessionData[$this->flashKey])) {
            $this->flashData = $this->sessionData[$this->flashKey];
        }

        # Get the token if it exists
        if (isset($this->sessionData[$this->tokenKey])) {
            $this->token = $this->sessionData[$this->tokenKey];
        }

        # Iterate the flash data
        $this->iterateFlash();

        # If the session is more than 1 month, destroy it
        $this->iterateTimestamp();

        # Create new token if one doesn't already exist
        if (!$this->token) $this->regenerateToken();

        # Let's set the referrer for get requests
        if (\Kanso\Kanso::getInstance()->Request->isGet()) {
            $this->put('referrer', \Kanso\Kanso::getInstance()->Environment['REQUEST_URL']);
        }
    }

    /**
     * Write data to session superglobal
     *
     */
    public function save()
    {
        $this->write();
    }

    /********************************************************************************
    * PUBLIC ACCCES 
    *******************************************************************************/

    /**
     * Regenerate the token
     *
     */
    public function regenerateToken()
    {
        $this->token = hash('sha256', random_bytes(16));
        return $this->token;
    }

    /**
     * Get the access token
     *
     * @return  array
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Clear the session
     *
     */
    public function clear()
    {
        # Clear the data
        $this->sessionData = [];

        # Clear the flash data
        $this->flashData = [];

        # Generate a new access token
        $this->regenerateToken();

        # Append Kanso session data
        $this->put('last_active', time());
    }

    /**
     * Verify a user's access token
     *
     * @param   string   $token
     * @return  boolean
     */
    public function verifyToken($token)
    {   
        return $token !== $this->token;
    }

    public function getReferrer()
    {
        return $this->get('referrer');
    }

    
    /********************************************************************************
    * SESSION DATA MANAGEMENT
    *******************************************************************************/

    /**
     * Get a key from the Session data or the entire Session data
     *
     * @param  string   $key   (optional)
     * @return null|array 
     */
    public function get($key = null) 
    {
        if (!$key) return $this->sessionData;
        if ($key && isset($this->sessionData[$key])) return $this->sessionData[$key];
        return null;
    }

    /**
     * Check if a key-value exists in the Session data
     *
     * @param  string   $key
     */
    public function has($key)
    {
        return isset($this->sessionData[$key]);
    }

    /**
     * Save a key/value pair to the Session data
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    public function put($key, $value) 
    {

        $this->sessionData[$key] = $value;
    }

    /**
     * Save an array of key/vals to the Session data
     *
     * @param  array    $data
     */
    public function putMultiple(array $data) 
    {
        foreach ($data as $key => $value) {
            $this->sessionData[$key] = $value;
        }
    }

    /**
     * Remove a key-value from the Session data
     *
     * @param  string   $key
     */
    public function remove($key)
    {
        if (isset($this->sessionData[$key])) unset($this->sessionData[$key]);
    }

    /********************************************************************************
    * FLASH DATA MANAGEMENT
    *******************************************************************************/

    /**
     * Get a key from the flash data or the entire flash data
     *
     * @param  string   $key   (optional)
     * @return null|array 
     */
    public function getFlash($key = null) 
    {
        if (!$key) {
            $data = [];
            foreach ($this->flashData as $key => $flash) {
                $data[$key] = $flash['key'];
            }
            return $data;
        }

        if (isset($this->flashData[$key]['key'])) return $this->flashData[$key]['key'];
    }

    /**
     * Check if a key-value exists in the flash data
     *
     * @param  string   $key
     */
    public function hasFlash($key)
    {
        return isset($this->flashData[$key]['key']);
    }

    /**
     * Save a key/value pair to the flash data
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    public function putflash($key, $value) 
    {
        $this->flashData[$key]['key']   = $value;
        $this->flashData[$key]['count'] = 0;
    }

    /**
     * Remove a key-value from the flash data
     *
     * @param  string   $key
     */
    public function removeFlash($key)
    {
        if (isset($this->flashData[$key])) unset($this->flashData[$key]);
    }

    /**
     * Clear the sessions flash data
     *
     */
    public function clearFlash()
    {
        $this->flashData = [];
    }

    /**
     * Loop over the flash data and remove old data
     *
     */
    private function iterateFlash()
    {
        if (empty($this->flashData)) return;
        foreach ($this->flashData as $key => $data) {
            $count = $data['count'];
            if ($data['count'] + 1 > 1 ) {
                unset($this->flashData[$key]);
            }
            else {
                $this->flashData[$key]['count'] = $count + 1;
            }
        }
    }

    /********************************************************************************
    * PRIVATE HELPERS
    *******************************************************************************/

    /**
     * Iterate and set the timestamp
     *
     */
    private function iterateTimestamp()
    {
        $timestamp = $this->get('last_active');
        if ($timestamp && $timestamp < strtotime('-'.$this->lifetime)) {
            $this->clear();            
        }
        # Last active is now
        $this->put('last_active', time());
    }

    /**
     * Set the session cookie name
     *
     */
    private function setcookie()
    {
        session_name($this->cookieName);
    }

    /**
     * Start the session
     *
     */
    private function start()
    {
        # Start a PHP session
        if ( session_id() == '' || !isset($_SESSION)) {
            if (!headers_sent()) { 
                session_start();
            }
        }
    }
   
    /**
     * Read the session
     *
     */
    private function read()
    {
        $data = [];
        if (isset($_SESSION[$this->sessionKey])) {
            $data = unserialize($_SESSION[$this->sessionKey]);
        }
        return $data;
    }

    /**
     * Write the session
     *
     */
    private function write()
    {
        # Overwrite
        $data = $this->sessionData;
        $data[$this->flashKey] = $this->flashData;
        $data[$this->tokenKey] = $this->token;

        # Save to the session
        $_SESSION[$this->sessionKey] = serialize($data);

        # Save locally
        $this->sessionData = $data;
    }


}