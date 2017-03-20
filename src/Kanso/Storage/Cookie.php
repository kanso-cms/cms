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
     * @var string 'kanso_login'
     */
    protected $login = 'no';

    /**
     * @var Kanso\Storage\Cookie\Cookie
     */
    private  $singer;

    /**
     * @var string 'ks_access'
     */
    private $cookieName = 'kanso_cookie';

    /**
     * @var string 'ks_access'
     */
    private $lifeTime = '1 month';

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        # Cookie signer
        $this->singer = new \Kanso\Storage\Cookie\Cookie;
        
        # Fetch cookies
        $data  = $this->singer->fetch($this->cookieName);
        $login = isset($_COOKIE['kanso_login']) ? $_COOKIE['kanso_login'] : 'no';

        # Get the session data if it exists
        if ($data) $this->cookieData = unserialize($data);

        # Does the user have a login cookie?
        $this->login = $login === 'yes' ? 'yes' : 'no';

        # Make sure last active is set
        if (!$this->get('last_active')) $this->put('last_active', time());

        # Validate the cookie
        $this->validateCookie();

        # Set the referrer
        if (\Kanso\Kanso::getInstance()->Request->isGet()) {
            $this->put('referrer', \Kanso\Kanso::getInstance()->Environment['REQUEST_URL']);
        }
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
    private function validateCookie() 
    {   
        # Double check cookie data is not corrupted
        if (!is_array($this->cookieData)) $this->clear();

        # If the cookie is more than 1 month, destroy it
        if ($this->get('last_active') < strtotime('-'.$this->lifeTime)) $this->clear();
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
     * Log the client in
     *
     */
    public function logout()
    {
        # Set as logged in
        $this->login = 'no';
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
        $login    = $this->singer->store('kanso_login', $this->login);
        $login[1] = $this->login;

        # Cookies to send to the client
        return [
            $this->singer->store($this->cookieName, serialize($this->cookieData)),
            $login,
        ];
    }

    /**
     * Clear the user's cookie
     *
     * This completely clears the users cookies
     * as if they have never visited the site before.
     *
     */
    public function clear()
    {
        # Clear the data
        $this->cookieData = [];

        # The user is not logged in
        $this->login = 'no';

        # Append Kanso session data
        $this->put('last_active', time());
    }

    
    /********************************************************************************
    * COOKIE DATA MANAGEMENT
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

   

}