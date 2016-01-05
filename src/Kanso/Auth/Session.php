<?php

namespace Kanso\Auth;

/**
* Session manager
*
* The session manager is a static singletion class responsible 
* for managing sessions throughout the Kanso admin panel. 
* It provides 3 layers of security to the admin panel:
* 
* 1. PHP NATIVE SESSIONS
*    The session manager uses PHP's native sessions as a basic layer
*    of security to ensure the cleint is actually logged in (used in
*    both POST and GET requests). The session only lasts 12 hours, by 
*    by which time the session is destoyed.
*
* 2. GET HTTP_REFERER
*    On all GET requests to the admin panel, the URL is stored in the
*    the cleint's session. This is used as more trusted version of PHP's
*    HTTP_REFERER to ensure a POST request is actually coming from the
*    page it should.
* 
* 3. PUBLIC KEYS WITH ENCRYPTION
*    On all GET requests to the admin panel, an additional POST request
*    is made from the client for their public key and salt. The key is
*    a random string, encrpyted with another public key (salt) stored in the 
*    clients's database entry.
*    
*    If the user is not logged in, (e.g register and login pages), the keys are 
*    simply stored in the SESSION.
*
*    The client must then decrypt and then re-encrypt their public key, 
*    with their public salt and sign all POST requests the newly generated key.
*    When the server receives the public key, it's compared to their existing
*    public key (which shouldn't be the same since they had to re-encrypt it),
*    The server then decrypts it for validation.
*    The keys are reset every 12 hourse on GET requests. 
*    NOTE that this encryption formula is completely seperate from clients's
*    password encryption.
*    Encrption and generation is handled throgh  \Kanso\Admin\Security\keyManager
* 
* 4. USERNAME AND IP BLACKLISTING - TODO
*    For all the account/setup pages (e.g login, register, forgot password etc...)
*    if a user makes a number of failed attempts to login, access a page, or reset a password
*    The account they're trying to access will shut-down for a specified amount of time
*    The suspects ip address will also be blacklistedfor a specified amount of time, 
*    disabling all GET and POST to the admin panel from that IP Address.
*
*/
class sessionManager
{

    /**
     * @var Kanso\Http\Request\isGet()
     */
    protected static $isGETrequest;

    /**
     * @var Kanso\Environment['REQUEST_URL']
     */
    protected static $requestURL;

    /**
     * @var array
     */
    protected static $sessionData = [];

     /**
     * @var array
     */
    protected $flashData = [];

    /**
     * Public Constructor
     *
     * @param    bool          $isGetRequest   is this a GET request?
     * @param    string        $requestURL     Request URI
     */

    public function __construct($isGETrequest, $requestURL)
    {

    	$this->isGETrequest = $isGETrequest;
    	$this->requestURL 	= $requestURL;

    	if (isset($_SESSION['kanso'])) $this->sessionData = unserialize($_SESSION['kanso']);
        
        if ($isGETrequest && $requestURL) {
            $this->sessionStart();
        }
    }

    /**
     * Session start 
     *
     * This function is called on all GET requests. It's used to store 
     * variables in the cleint's session
     *
     * @param  bool   $isGetRequest   is this a GET request?
     * @return bool 
    */
    public static function sessionStart() 
    {

    	# If no Kanso session array is present create a new Kanso session
        if (!isset($_SESSION['kanso'])) self::freshSession();

    	# Re-generate the id on every 20 requests
    	if ($this->has('kanso_last_regeneration') {
    		if (++$this->get('kanso_last_regeneration') >= 20) {
			   	$this->put('kanso_last_regeneration', 0);
			   	session_regenerate_id(true);
			}
    	}
    	else {
    		$this->put('kanso_last_regeneration', 0);
    	}

        # If the session is more than 12 hours old, destroy it
        if ($this->get('kanso_last_activity') < strtotime('-12 hours')) $this->logClientOut();

        # If this is a GET request, store the last visited page
        if ($this->isGETrequest && $this->requestURL) $this->put('kasno_http_referer', $this->requestURL);

        # If the user is NOT logged in, and this is a GET request, 
        # validate that they have a public key, and it's not 12 hours old,
        # otherwise generate it and save it to the SESSION
        if ($this->isGETrequest && $this->requestURL && !$this->isLoggedIn()) {
            if (!$this->get('KANSO_PUBLIC_KEY') || ($this->get('KANSO_KEYS_TIME') && $this->get('KANSO_KEYS_TIME') < strtotime('-12 hours')) ) {
                $keys = \Kanso\Admin\Security\keyManager::generateKeys();
                $this->put('KANSO_PUBLIC_KEY',  $keys['KANSO_PUBLIC_KEY']);
                $this->put('KANSO_PUBLIC_SALT', $keys['KANSO_PUBLIC_SALT']);
                $this->put('KANSO_KEYS_TIME',   $keys['KANSO_KEYS_TIME']);
            }
        }

        # If the user IS logged in, and this is a get request
        # validate that they have a public key, and it's not 12 hours old,
        # otherwise re-generate it and save it to the databse
        if ($this->isGETrequest && $this->requestURL && $this->isLoggedIn()) {
            $keys = \Kanso\Admin\Security\keyManager::getPublicKeys();
            if ($keys['KANSO_KEYS_TIME'] === null || (time() - $keys['KANSO_KEYS_TIME'] > 43200) ) {
                \Kanso\Admin\Security\keyManager::generateKeys(true);
            }
        }

    }

    /**
     * Get a key from the SESSION or the entire SESSION
     *
     * @param  string   $key   (optional)
     * @return null|array 
     */
    public static function has($key) 
    {
        return isset($this->sessionData[$key]);
    }


    /**
     * Get a key from the SESSION or the entire SESSION
     *
     * @param  string   $key   (optional)
     * @return null|array 
     */
    public static function get($key = null) 
    {

        if (!$key) return self::$sessionData;
        if ($key && isset(self::$sessionData[$key])) return self::$sessionData[$key];
        return null;

    }

    /**
     * Save a key/value pair to the SESSION
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    public static function put($key, $value) 
    {

        self::$sessionData[$key] = $value;
        self::save();

    }

    /**
    * Delete a key-value from the SESSION
    *
    * @param  string   $key
    * @param  mixed    $value
    */
    public static function delete($key)
    {

        if (isset(self::$sessionData[$key])) unset(self::$sessionData[$key]);
        self::save();
    }

    /**
     * Save the session (used internally only)
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    private static function save() 
    {
        $_SESSION['sessionData'] = serialize(self::$sessionData);
    }

    /**
    * Create a fresh Kanso session
    *
    */
    public static function freshSession() 
    {

        # Clear the SESSION
        $_SESSION = [];

        # Unset the session
        session_unset();

        # Destroy the session
        session_destroy();

        # Start a new session  
        session_start();

        # Re-generate the id
        session_regenerate_id(true);

        # Append Kanso last activity time
        self::put('lastActivity', time());
    }

    /**
	 * Store a flash value in the session.
	 *
	 * @access  public
	 * @param   string  $key    Flash key
	 * @param   mixed   $value  Flash data
	 * @return  mixed
	 */
	public function putFlash($key, $value)
	{
		$this->flashData[$key] = $value;
	}

	/**
	 * Returns TRUE if key exists in the session and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $key  Session key
	 * @return  boolean
	 */
	public function hasFlash($key)
	{
		return isset($this->sessionData['flashdata'][$key]);
	}
	/**
	 * Returns a flash value from the session.
	 *
	 * @access  public
	 * @param   string  $key      Session key
	 * @return  mixed
	 */
	public function getFlash($key)
	{
		return isset($this->sessionData['mako.flashdata'][$key]) ? $this->sessionData['mako.flashdata'][$key] : null;
	}
	/**
	 * Removes a value from the session.
	 *
	 * @access  public
	 * @param   string  $key  Session key
	 */
	public function removeFlash($key)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}
		unset($this->sessionData['mako.flashdata'][$key]);
	}
	/**
	 * Extends the lifetime of the flash data by one request.
	 *
	 * @access  public
	 * @param   array   $keys  Keys to preserve
	 */
	public function reflash(array $keys = [])
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}
		$flashData = isset($this->sessionData['mako.flashdata']) ? $this->sessionData['mako.flashdata'] : [];
		$flashData = empty($keys) ? $flashData : array_intersect_key($flashData, array_flip($keys));
		$this->flashData = array_merge($this->flashData, $flashData);
	}


    /**
    * Log client in
    *
    * This is responsible for logging a client into the 
    * admin panel.
    *
    * @param  array   $clientEntry   The clients Database entry
    * @return null 
    */
    public static function logClientIn($clientEntry) 
    {

        # Create a fresh session
        self::freshSession();

        # Append login credentials to the session
        self::put('KANSO_LAST_ACTIVITY', time());
        self::put('KANSO_LOGGED_IN', true);
        self::put('KANSO_ADMIN_DATA', $clientEntry);

        # Fire the event
        \Kanso\Events::fire('login', [self::get()]);

    }

    /**
     * Log client out
     *
     * This is responsible for logging a client out of the 
     * admin panel.
     *
    */
    public static function logClientOut() 
    {

        # Fire the event
        \Kanso\Events::fire('logout', [self::get()]);

        self::$sessionData = [];

        # Create a fresh session
        self::freshSession();

    }

    /**
     * Validate that a user is logged in
     *
     * @return Bool 
     */
    public static function isLoggedIn()
    {
        if (self::get('KANSO_LAST_ACTIVITY') < strtotime('-12 hours')) self::freshSession();
        return self::get('KANSO_LOGGED_IN') === true && self::get('KANSO_ADMIN_DATA') !== null;
    }


}