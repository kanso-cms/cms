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
     * @var array
     */
    protected $flashData = [];

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

        # Get the session data if it exists
        if (isset($_SESSION['kanso_flash'])) $this->flashData = unserialize($_SESSION['kanso_flash']);
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
        if (!isset($_SESSION['kanso'])) $this->clear();

        # Regenerate the session ID every 20 requests
        if ($this->get('sessionGenCount') + 1  >= 20) {
            
            $this->put('sessionGenCount',  1);
            
            $this->regenerateId(true);
        }
        else {
            $this->put('sessionGenCount',  $this->get('sessionGenCount') + 1);
        }

        # Clean and validate the flash data
        $this->iterateFlash();

        # Get the gatekeeper
        $Gatekeeper = \Kanso\Kanso::getInstance()->Gatekeeper;

        # If the session is more than 12 hours old, destroy it
        if ($this->get('sessionLastActive') < strtotime('-24 hours')) $this->clear();

        # If this is a GET request, store the last visited page
        if ($this->isGETrequest && $this->requestURL) $this->setReferrer($this->requestURL);

        # If the user is NOT logged in, and this is a GET request, 
        # validate that they have a public key, and it's not 12 hours old,
        # otherwise generate it and save it to the SESSION
        if ($this->isGETrequest && $this->requestURL) {
            
            if (!$this->get('kanso_public_key') || ($this->get('kanso_keys_time') && $this->get('kanso_keys_time') < strtotime('-24 hours')) ) {
                $this->regenerateToken();
            }
        }

        $this->instantiated = true;
    }

    /********************************************************************************
    * SESSION ID MANAGEMENT
    *******************************************************************************/

    /**
     * Get the session id
     *
     * @return  integer
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Regenerate the session id
     *
     * @param   boolean $keepData
     * @return  boolean
     */
    public function regenerateId($keepData = false)
    {
        return session_regenerate_id($keepData);
    }

    /********************************************************************************
    * HTTP REFFERRAL MANAGEMENT
    *******************************************************************************/

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
        $this->save();
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
        $this->save();
    }

    /**
     * Remove a key-value from the Session data
     *
     * @param  string   $key
     */
    public function remove($key)
    {
        if (isset($this->sessionData[$key])) unset($this->sessionData[$key]);
        $this->save();
    }

    /**
     * Refresh a logged in user's session data
     *
     */
    public function refresh()
    {
        $adminData = $this->get('KANSO_ADMIN_DATA');
        if (!empty($adminData)) {
        
            $id = $adminData['id'];

            $row = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('id', '=', $id)->ROW();

            $this->put('KANSO_ADMIN_DATA', $row);

        }
    }

    /**
     * Destroy the session and start a new one
     *
     */
    public function clear()
    {
        # Clear the data
        $this->sessionData = [];

        # Clear the flash data
        $this->flashData = [];

        # Create a fresh session
        $this->freshSession();
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
        $this->saveFlash();
    }

    /**
     * Remove a key-value from the flash data
     *
     * @param  string   $key
     */
    public function removeFlash($key)
    {
        if (isset($this->flashData[$key])) unset($this->flashData[$key]);
        $this->saveFlash();
    }

    /**
     * Clear the sessions flash data
     *
     */
    public function clearFlash()
    {
        $this->flashData = [];
        $this->saveFlash();
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
        return [
            'kanso_public_key'  => $this->get('kanso_public_key'),
            'kanso_public_salt' => $this->get('kanso_public_salt'),
            'kanso_keys_time'   => $this->get('kanso_keys_time'),
        ];
    }

    /**
     * Verify the ajax token
     *
     * @return  boolean
     */
    public function validateToken($token)
    {

        # Get the keys from their session
        $keys = $this->getToken();
        
        # If the user is logged get the keys directly
        # From the database and make sure theyre the same
        if (!\Kanso\Kanso::getInstance()->Gatekeeper->isGuest()) {
            $id = $this->get('KANSO_ADMIN_DATA')['id'];

            $entry = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('id', '=', $id)->ROW();
            
            if ($entry['kanso_public_key'] !== $keys['kanso_public_key']) return false;
        }
        
        # Decrypt and verify
        return Token::verify($token, $keys['kanso_public_key'], $keys['kanso_public_salt']);
    }

    /**
     * Regenerate the ajax token
     *
     * @return boolean
     */
    public function regenerateToken()
    {
        # Generate the keys
        $keys = Token::generate();
        $keys = [
            'kanso_public_key'  => $keys['key'],
            'kanso_public_salt' => $keys['salt'],
            'kanso_keys_time'   => time(),
        ];

        # Save to the session
        $this->putMultiple($keys);

        # If the user is logged in, save the keys to their admin 
        # data as well
        $adminData = $this->get('KANSO_ADMIN_DATA');
        if (!empty($adminData)) {
        
            $id = $adminData['id'];
            \Kanso\Kanso::getInstance()->Database()->Builder()->UPDATE('users')->SET($keys)->WHERE('id', '=', $id)->QUERY();

            $this->put('KANSO_ADMIN_DATA', array_merge($adminData, $keys));

        }

        return true;
    }

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Save the session data
     *
     * @param  string   $key
     * @param  mixed    $value
     */
    private function save() 
    {
        $_SESSION['kanso'] = serialize($this->sessionData);
    }

    /**
     * Save the session flash data
     *
     */
    private function saveFlash() 
    {
        $_SESSION['kanso_flash'] = serialize($this->flashData);
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
        $this->saveFlash();
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

        # Append the an empty flash storage
        $_SESSION['kanso_flash'] = serialize([]);
    }

}