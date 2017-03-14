<?php

namespace Kanso\Auth;

/**
* Session manager
*
*
*/
class session
{

    /**
     * @var array 'ks_session'
     */
    protected $sessionData = [];

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
     * @var boolean
     */
    protected $instantiated = false;

    /**
     * Constructor
     *
     * @param    bool          $isGetRequest   is this a GET request?
     * @param    bool          $isFileRequest  is this a FILE request? 
     * @param    string        $requestURL     Request URI
     */
    public function __construct()
    {
        $user  = \Kanso\Kanso::getInstance()->Cookie->fetch('ks_session');
        $flash = \Kanso\Kanso::getInstance()->Cookie->fetch('ks_flash');
        $token = \Kanso\Kanso::getInstance()->Cookie->fetch('ks_access');
        $login = isset($_COOKIE['ks_login']) ? $_COOKIE['ks_login'] : 'no';

        # Get the session data if it exists
        if ($user) $this->sessionData = unserialize($user);

        # Get the session flash data if it exists
        if ($flash) $this->flashData = unserialize($flash);

        # Get the session flash data if it exists
        if ($token) $this->token = $token;

        # Does the user have a login cookie?
        $this->login = $login === 'yes' ? 'yes' : 'no';
    }

    /********************************************************************************
    * PUBLIC ACCCES 
    *******************************************************************************/

    /**
     * Initialize the Kanso Session
     *
     * This method is / and can only be called once. It is
     * called when Kanso is first instantiated and immediately
     * runs the custom Kanso sessionSart method; 
     *
     */
    public function init()
    {
        # If the session has already been instantiated don't proceed
        if ($this->instantiated === true) return;

        # If this is a GET request start the Kanso session
        $this->sessionStart();

        # Session done
        $this->instantiated = true;

        # Is the user loggedin
        $this->isLoggedIn(true);
    }

    /**
     * Session start 
     *
     * This function is called on all requests. It's used to store 
     * variables in the client's cookie for a number of security measures.
     *
     */
    public function sessionStart() 
    {   
        # If no Kanso session data is present we need a fresh session
        if (empty($this->sessionData) || !$this->sessionData) $this->clear();

        # Iterate the flash data
        $this->iterateFlash();

        # If the session is more than 1 month, destroy it
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
     * This method checks if the user is logged in
     * If $runfresh is true and the user is logged in
     * It will check from the database if the user's token matches the
     * one from the session.
     *
     * @param   boolean   $runFresh
     * @return  boolean
     */
    public function isLoggedIn($runFresh = false) 
    {
        # If we are not hard checking
        if (!$runFresh) return $this->login === 'yes';

        # Definitely not logged in if the cookie !== yes
        if ($this->login !== 'yes') return false;

        # If there is no user ID we are not logged in
        if (!$this->get('id')) return false;

        # Lets get the user's row from the database
        # based on the id and token from the cookie
        $row = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')
        ->WHERE('id', '=', $this->get('id'))
        ->AND_WHERE('access_token', '=', $this->token)
        ->ROW();

        # If the row doesn't exist but the user has an id
        # Their cookie should be destroyed as they most likely logged in 
        # From another machine
        if (!$row) {
            $this->clear();
            return false;
        }

        # If the user exists they're not logged in
        if ($row) return true;

        # Otherwise they are not logged in
        return false;
    }

    /**
     * Log the client in
     *
     */
    public function login()
    {
        # Set as logged in
        $this->login = 'yes';

        # Create a fresh access token
        # This will log the user out from other machines so they can
        # only be logged into one browser at a time
        $this->regenerateToken();

        # Update their access token
        if ($this->get('id')) {
            \Kanso\Kanso::getInstance()->Database()->Builder()
            ->UPDATE('users')->SET(['access_token' => $this->token])
            ->WHERE('id', '=', $this->get('id'))
            ->QUERY();
            return true;
        }

    }

    /**
     * Verify a user's access token
     *
     * This method checks if the token provided
     * from a ajax request matches they're token in 
     * the session and the database.
     *
     * @param   string   $token
     * @return  boolean
     */
    public function validateToken($token)
    {        
        $access_token = $this->token;

        if (!$access_token) return false;
        if ($token !== $access_token) return false;
        
        # If the user is logged get the keys directly
        # From the database and make sure theyre the same
        if ($this->isLoggedIn()) {
            $entry = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('access_token')->FROM('users')
            ->WHERE('id', '=', $this->get('id'))
            ->AND_WHERE('email', '=', $this->get('email'))
            ->AND_WHERE('name', '=', $this->get('name'))
            ->ROW();
            if (!$entry) return false;
            return $entry['access_token'] === $token;
        }
        
        return true;
    }

    /**
     * Refresh a logged in user's cookie data
     *
     * This method refreshes a logged in user's 
     * data. This just refreshes they're session data
     * 
     *
     */
    public function refresh()
    {
        $user     = $this->get();
        $loggedIn = $this->isLoggedIn();
        $this->clear();
        
        if ($loggedIn) {
            $entry = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')
            ->WHERE('id', '=', $user['id'])->ROW();
            $this->putMultiple([
                'id'    => $entry['id'],
                'email' => $entry['email'],
                'name'  => $entry['name'],
            ]);
            $this->login();
        }
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
        # Cookie manager
        $cookie = \Kanso\Kanso::getInstance()->Cookie;
        $login  = $cookie->store('ks_login', $this->login);
        
        # Login cookies don't get encrypted
        $login[1] = $this->login;

        # Cookies to send to the client
        return [
            $cookie->store('ks_session', serialize($this->sessionData)),
            $cookie->store('ks_flash',   serialize($this->flashData)),
            $cookie->store('ks_access',  $this->token),
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
        $this->sessionData = [];

        # Clear the flash data
        $this->flashData = [];

        # The user is not logged in
        $this->login = 'no';

        # Create a fresh session
        $this->freshSession();
    }

    /**
     * Get the session referrer
     *
     * This returns the refferer if it is
     * set either via the HTTP request or
     * the cookie data.
     *
     * @return  string|false
     */
    public function getReferrer()
    {
        if (isset($_SERVER['HTTP_REFERER'])) return $_SERVER['HTTP_REFERER'];
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
        $token = \Kanso\Utility\Str::generateRandom(16, true);
        
        if ($this->get('id')) {
            \Kanso\Kanso::getInstance()->Database()->Builder()
            ->UPDATE('users')->SET(['access_token' => $token])
            ->WHERE('id', '=', $this->get('id'))
            ->QUERY();
        }

        $this->token = $token;

        return true;
    }

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Create a fresh Kanso session
     *
     */
    private function freshSession() 
    {

        # Clear the SESSION
        $_SESSION = [];

        # Unset the session
        session_unset();

        # Destroy the session
        session_destroy();

        # Start a new session  
        session_start();

        # Append Kanso session data
        $this->put('last_active', time());

        # Generate a new access token
        $this->regenerateToken();
    }

}