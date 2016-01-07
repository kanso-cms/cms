<?php

namespace Kanso\Auth;

/**
* Session manager
*
* The session manager is Kanso's main point for interacting with and
* managing sessions. All session manipulation should go through this class. 
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
class session
{

    /**
     * @var Kanso\Http\Request\isGet()
     */
    protected $isGETrequest;

    /**
     * @var Kanso\Environment['REQUEST_URL']
     */
    protected $requestURL;

    /**
     * @var array
     */
    protected $sessionData = [];

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
        # Is this a GET request
        $this->isGETrequest = \Kanso\Kanso::getInstance()->Request->isGet();

        # Store the request URL
        $this->requestURL = \Kanso\Kanso::getInstance()->Environment['REQUEST_URL'];

        # Get the session data if it exists
        if (isset($_SESSION['kanso'])) $this->sessionData = unserialize($_SESSION['kanso']);
    }

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

        # If this is a GET request start the kanso session
        if ($this->isGETrequest && $this->requestURL) $this->sessionStart();
    }

    /**
     * Session start 
     *
     * This function is called on all GET requests. It's used to store 
     * variables in the cleint's session for a number of security measures.
     *
     */
    public function sessionStart() 
    {
        # If no Kanso session data is present create a new Kanso session
        if (!isset($_SESSION['kanso'])) $this->freshSession();

        # Regenerate the session ID every 20 requests
        if ($this->get('sessionGenCount') + 1  >= 20) {
            
            $this->put('sessionGenCount',  1);
            
            session_regenerate_id(true);
        }
        else {
            $this->put('sessionGenCount',  $this->get('sessionGenCount') + 1);
        }

        # Get the gatekeeper
        $Gatekeeper = \Kanso\Kanso::getInstance()->Gatekeeper;

        # If the session is more than 12 hours old, destroy it
        if ($this->get('sessionLastActive') < strtotime('-12 hours')) $this->freshSession();

        # If this is a GET request, store the last visited page
        if ($this->isGETrequest && $this->requestURL) $this->setReferrer($this->requestURL);

        # If the user is NOT logged in, and this is a GET request, 
        # validate that they have a public key, and it's not 12 hours old,
        # otherwise generate it and save it to the SESSION
        if ($this->isGETrequest && $this->requestURL) {
            
            if (!$this->get('kanso_public_key') || ($this->get('kanso_keys_time') && $this->get('kanso_keys_time') < strtotime('-12 hours')) ) {
                
                $keys = Helper\Token::generate();
                $keys = [
                    'kanso_public_key'  => $keys['key'],
                    'kanso_public_salt' => $keys['salt'],
                    'kanso_keys_time'   => time(),
                ];
                $this->putMultiple($keys);

                # If the user IS logged in save the freshly generetaed keys to the databse
                if ($Gatekeeper->isLoggedIn()) {
                    \Kanso\Kanso::getInstance()->Database()->Builder()->UPDATE('users')->SET($keys)->WHERE('id', '=', $Gatekeeper->getUser()->getid())->QUERY();
                }
            }
        }

        $this->instantiated = true;
    }

    /**
     * Get the ajax tokens
     *
     * @return  string
     */
    public function getAjaxTokens()
    {
        return [
            'kanso_public_key'  => $this->get('kanso_public_key'),
            'kanso_public_salt' => $this->get('kanso_public_salt'),
            'kanso_keys_time'   => $this->get('kanso_keys_time'),
        ];
    }

    /**
     * Get the HTTP Referrer from the session
     *
     * @return  string
     */
    public function getReferrer()
    {
        return $this->get('sessionHttpReferrer');
    }

    /**
     * Set the HTTP Referrer from the session
     *
     * @param  string   $value
     */
    public function setReferrer($value)
    {
        return $this->put('sessionHttpReferrer', $value);
    }

    /**
     * Clear the session entirely
     *
     */
    public function clear()
    {
        # Clear the data
        $this->sessionData = [];

        # Create a fresh session
        $this->freshSession();
    }

    /**
     * Get a key from the SESSION or the entire SESSION
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
     * Save a key/value pair to the SESSION
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    public function put($key, $value) 
    {

        $this->sessionData[$key] = $value;
        $this->save();
    }

    /**
     * Save an array of key/vals to the SESSION
     *
     * @param  array    $data
     */
    public function putMultiple(array $data) 
    {
        foreach ($data as $key => $value) {
            $this->sessionData[$key] = $value;
        }
        $this->save();
    }

    /**
     * Delete a key-value from the SESSION
     *
     * @param  string   $key
     */
    public function delete($key)
    {
        if (isset($this->sessionData[$key])) unset($this->sessionData[$key]);
        $this->save();
    }

    /**
     * Save the session (used internally only)
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    private function save() 
    {
        $_SESSION['kanso'] = serialize($this->sessionData);
    }

    /**
     * Create a fresh Kanso session
     *
     */
    public function freshSession() 
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
        $this->put('sessionLastActive', time());

        # Append the session count
        $this->put('sessionGenCount', 0);
    }

}