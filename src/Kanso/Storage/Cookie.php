<?php

namespace Kanso\Storage;

/**
* Cookie manager
*
*
*/
class Cookie
{
    /**
     * @var array 'ks_session'
     */
    protected $cookieData = [];

    /**
     * @var array 'ks_flash'
     */
    protected $flashData = [];

    /**
     * @var string 'ks_access'
     */
    protected $token;

    /**
     * @var string 'ks_login'
     */
    protected $login = 'no';

    /**
     * @var Kanso\Storage\Cookie\Cookie
     */
    private  $singer;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->singer = new \Kanso\Storage\Cookie\Cookie;
        $user  = $this->singer->fetch('ks_session');
        $flash = $this->singer->fetch('ks_flash');
        $token = $this->singer->fetch('ks_access');
        $login = isset($_COOKIE['ks_login']) ? $_COOKIE['ks_login'] : 'no';

        # Get the session data if it exists
        if ($user) $this->cookieData = unserialize($user);

        # Get the session flash data if it exists
        if ($flash) $this->flashData = unserialize($flash);

        # Get the session flash data if it exists
        if ($token) $this->token = $token;

        # Does the user have a login cookie?
        $this->login = $login === 'yes' ? 'yes' : 'no';

        # If this is a GET request start the Kanso cookie
        $this->initCookies();
    }

    /********************************************************************************
    * PUBLIC ACCCES 
    *******************************************************************************/

    /**
     * Cookie start 
     *
     * This function is called on all requests. It's used to store 
     * variables in the client's cookie for a number of security measures.
     *
     */
    private function initCookies() 
    {   
        # If no Kanso cookie data is present we need a fresh cookie
        if (empty($this->cookieData) || !$this->cookieData) $this->clear();

        # Iterate the flash data
        $this->iterateFlash();

        # If the cookie is more than 1 month, destroy it
        if ($this->get('last_active') < strtotime('-1 week')) $this->clear();

        # Last active is now
        $this->put('last_active', time());

        # If there is no validation token - create one
        if (empty($this->token) || !$this->token) $this->regenerateToken();

        # Let's set the referrer for get requests
        if (\Kanso\Kanso::getInstance()->Request->isGet()) {
            $this->put('referrer', \Kanso\Kanso::getInstance()->Environment['REQUEST_URL']);
        }
    }

    /**
     * Is the user currently logged in
     *
     * @return  boolean
     */
    public function isLoggedIn() 
    {
        return $this->login === 'yes';
    }

    /**
     * Log the client in
     *
     */
    public function login()
    {
        # Set as logged in
        $this->login = 'yes';
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

    /**
     * Get cookies
     *
     * This method returns an array of the user's cookies
     * in specific order [session, flash, token, loggedin]
     *
     * @return  array
     */
    public function cookies() 
    {
        # Login cookies don't get encrypted
        $login    = $this->singer->store('ks_login', $this->login);
        $login[1] = $this->login;

        # Cookies to send to the client
        return [
            $this->singer->store('ks_session', serialize($this->cookieData)),
            $this->singer->store('ks_flash',   serialize($this->flashData)),
            $this->singer->store('ks_access',  $this->token),
            $login,
        ];
    }

    /**
     * Clear the user's session
     *
     * This completely clears the users session
     * as if they have never visited the site before.
     *
     */
    public function clear()
    {
        # Clear the data
        $this->cookieData = [];

        # Clear the flash data
        $this->flashData = [];

        # The user is not logged in
        $this->login = 'no';

        # Generate a new access token
        $this->regenerateToken();

        # Append Kanso session data
        $this->put('last_active', time());
    }

    /**
     * Get the session referrer
     *
     * This returns the referrer if it is
     * set either via the HTTP request or
     * the cookie data.
     *
     * @return  string|false
     */
    public function getReferrer()
    {
        if ($this->has('referrer')) return $this->get('referrer');
        return false;
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
        if (!$key) return $this->cookieData;
        if ($key && isset($this->cookieData[$key])) return $this->cookieData[$key];
        return null;
    }

    /**
     * Check if a key-value exists in the Session data
     *
     * @param  string   $key
     */
    public function has($key)
    {
        return isset($this->cookieData[$key]);
    }

    /**
     * Save a key/value pair to the Session data
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    public function put($key, $value) 
    {

        $this->cookieData[$key] = $value;
    }

    /**
     * Save an array of key/vals to the Session data
     *
     * @param  array    $data
     */
    public function putMultiple(array $data) 
    {
        foreach ($data as $key => $value) {
            $this->cookieData[$key] = $value;
        }
    }

    /**
     * Remove a key-value from the Session data
     *
     * @param  string   $key
     */
    public function remove($key)
    {
        if (isset($this->cookieData[$key])) unset($this->cookieData[$key]);
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
        if (!$key) return $this->flashData;
        if ($key && isset($this->flashData[$key][$key])) return $this->flashData[$key][$key];
        return null;
    }

    /**
     * Check if a key-value exists in the flash data
     *
     * @param  string   $key
     */
    public function hasFlash($key)
    {
        return isset($this->flashData[$key][$key]);
    }

    /**
     * Save a key/value pair to the flash data
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    public function putflash($key, $value) 
    {
        $this->flashData[$key][$key]    = $value;
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
    * AJAX TOKEN MANAGEMENT
    *******************************************************************************/

    /**
     * Get the ajax token
     *
     * @return  array
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Regenerate the ajax token
     *
     * @return boolean
     */
    public function regenerateToken()
    {
        $this->token = \Kanso\Utility\Str::generateRandom(16, true);
    }

}