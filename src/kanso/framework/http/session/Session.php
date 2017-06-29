<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session;

use kanso\framework\utility\Arr;
use kanso\framework\common\ArrayAccessTrait;

/**
 * Session Manager
 *
 * @author Joe J. Howard
 */
class Session
{
    use ArrayAccessTrait;

    /**
     * The session flash data
     *
     * @var array
     */
    private $flash;

    /**
     * CSRF token
     *
     * @var array
     */
    private $token;

    /**
     * The key that is used to save data to $_SESSION
     *
     * @var string
     */
    private $sessionKey;

    /**
     * The key that is used to store flash data inside $_SESSION[$sessionKey]
     *
     * @var string
     */
    private $flashKey = 'kanso_flash';

    /**
     * The key that is used to store the CSRF token inside $_SESSION[$sessionKey]
     *
     * @var string
     */
    private $tokenKey = 'kanso_token';

    /**
     * The key that is used to store the key/values inside $_SESSION[$sessionKey]
     *
     * @var string
     */
    private $dataKey = 'kano_data';

    /**
     * Time when the cookie expires
     *
     * @var int
     */
    private $cookieExpires;

    /**
     * Constructor
     *
     * @access public
     * @param  $configuration array Array of configuration options
     */
    public function __construct(Store $store, Token $token, Flash $flash, array $configuration)
    {
        $this->token = $token;

        $this->store = $store;

        $this->flash = $flash;

        $this->configure($configuration);

        $this->initializeSession();
    }

    /**
     * Set cookie the configuration
     *
     * @access public
     * @param  $configuration array Array of configuration options
     */
    public function configure(array $configuration)
    {
        session_name($configuration['cookie_name']);

        session_set_cookie_params(
            $configuration['expire'],
            $configuration['path'],
            $configuration['domain'],
            $configuration['secure'],
            $configuration['httponly']
        );

        $this->sessionKey = $configuration['session_key'];

        $this->cookieExpires = $configuration['expire'];
    }

    /**
     * Save the session so PHP can send it
     *
     * @access public
     */
    public function save()
    {
        $data = 
        [
            $this->dataKey => $this->get(),

            $this->flashKey => $this->flash->getRaw(),

            $this->tokenKey => $this->token->get(),
        ];

        $this->store->write($this->sessionKey, $data);
    }

    /**
     * Initialize the session
     *
     * @access private
     */
    private function initializeSession()
    {
        $this->start();

        $this->loadData();

        $this->flash->iterate();

        $this->validateExpiry();

        if (empty($this->token->get()))
        {
            $this->token->regenerate();
        }
    }

    /**
     * Start the PHP session
     *
     * @access private
     */
    private function start()
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
     * Load the data from the session
     *
     * @access private
     */
    private function loadData()
    {
        $data = $this->store->read($this->sessionKey);

        if ($data && is_array($data))
        {
            if (isset($data[$this->dataKey]))
            {
                $this->overwrite($data[$this->dataKey]);
            }

            if (isset($data[$this->flashKey]))
            {
                $this->flash->clear();
                
                $this->flash->putRaw($data[$this->flashKey]);
            }

            if (isset($data[$this->tokenKey]))
            {
                $this->token->set($data[$this->tokenKey]);
            }
        }

        if (!$this->get('last_active'))
        {
            $this->set('last_active', time());
        }
    }

    /**
     * Iterate and validate the expire time
     *
     * @access private
     */
    private function validateExpiry()
    {
        $lastActive = $this->get('last_active');

        if ((($this->cookieExpires - time()) + $this->get('last_active')) < time())
        {
            $this->destroy();
        }

        $this->set('last_active', time());
    }

    /**
     * Get the access token
     *
     * @access public
     * @return string
     */
    public function token(): Token
    {
        return $this->token;
    }

    /**
     * Get the access token
     *
     * @access public
     * @return string
     */
    public function flash(): Flash
    {
        return $this->flash;
    }

    /**
     * Clear the session
     * @access public
     */
    public function destroy()
    {
        $this->clear();

        # Clear the flash data
        $this->flash->clear();

        # Generate a new access token
        $this->token->regenerate();

        # Append Kanso session data
        $this->set('last_active', time());
    }
}
