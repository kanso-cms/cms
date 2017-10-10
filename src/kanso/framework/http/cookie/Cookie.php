<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\cookie;

use kanso\framework\http\cookie\storage\Store;
use kanso\framework\common\ArrayAccessTrait;

/**
 * Cookie utility
 *
 * @author Joe J. Howard
 */
class Cookie
{
    use ArrayAccessTrait;

    /**
     * Logged in user 'yes'|'no'
     *
     * @var string
     */
    protected $login = 'no';

    /**
     * Cookie storage implementation
     *
     * @var object
     */
    private  $store;

    /**
     * The cookie name
     *
     * @var string
     */
    private $cookieName;

    /**
     * Cookie expiry unix timestamp
     *
     * @var int
     */
    private $cookieExpires;

    /**
     * Have the cookies been sent ?
     *
     * @var bool
     */
    private $sent = false;

    /**
     * Constructor
     *
     * @access public
     * @param  object $store         Cookie storage implementation
     * @param  string $cookieName    Name of the cookie
     * @param  int    $cookieExpires Date the cookie will expire (unix timestamp)
     */
    public function __construct($store, string $cookieName, int $cookieExpires)
    {
        $this->store = $store;

        $this->cookieName = $cookieName;

        $this->cookieExpires = $cookieExpires;
        
        $this->readCookie(); 
        
        $this->validateExpiry();
    }

    /**
     * Read the cookie sent from the browser
     *
     * @access private
     */
    private function readCookie()
    {
        $data = $this->store->read($this->cookieName);

        if ($data && is_array($data))
        {
            $this->overwrite($data);
        }

        $login = $this->store->read($this->cookieName.'_login');

        if ($login)
        {
            $this->login = $login;
            $this->login = $login === 'yes' ? 'yes' : 'no';
        }

        if (!$this->get('last_active'))
        {
            $this->set('last_active', time());
        }
    }

    /**
     * Checks if the cookie is expired - destroys it if it is
     *
     * @access private
     */
    private function validateExpiry() 
    {
        if ((($this->cookieExpires - time()) + $this->get('last_active')) < time())
        {
            $this->destroy();
        }
    }

    /**
     * Is the user currently logged in
     *
     * @access public
     * @return boolean
     */
    public function isLoggedIn(): bool
    {
        return $this->login === 'yes';
    }

    /**
     * Log the client in
     *
     * @access public
     */
    public function login()
    {
        # Set as logged in
        $this->login = 'yes';
    }

    /**
     * Log the client in
     *
     * @access public
     */
    public function logout()
    {
        # Set as logged in
        $this->login = 'no';
    }

    /**
     * Send the cookies
     *
     * @access public
     */
    public function send() 
    {
        if (!$this->sent())
        {
            $this->store->write($this->cookieName, $this->get());

            $this->store->write($this->cookieName.'_login', $this->login);
        }   
    }

    /**
     * Send the cookies
     *
     * @access public
     */
    public function sent(): bool 
    {
        return $this->sent;
    }

    /**
     * Destroy the cookie
     *
     * @access public
     */
    public function destroy()
    {
        $this->clear();

        $this->login = 'no';

        $this->set('last_active', time());
    }
}
