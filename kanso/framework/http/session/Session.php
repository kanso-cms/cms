<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\session;

use kanso\framework\common\ArrayAccessTrait;
use kanso\framework\http\session\Flash;
use kanso\framework\http\session\Token;

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
    private $dataKey = 'kanso_data';

    /**
     * Constructor
     *
     * @access public
     * @param  $configuration array Array of configuration options
     */
    public function __construct(Token $token, Flash $flash, $store, array $configuration)
    {
        $this->token = $token;

        $this->flash = $flash;

        $this->store = $store;

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
        $this->store->session_name($configuration['cookie_name']);

        $this->store->session_set_cookie_params($configuration);
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

        $this->store->write($data);

        $this->store->send();
    }

    /**
     * Initialize the session
     *
     * @access private
     */
    private function initializeSession()
    {
        $this->store->session_start();

        $this->loadData();

        $this->flash->iterate();

        if (empty($this->token->get()))
        {
            $this->token->regenerate();
        }
    }

    /**
     * Load the data from the session
     *
     * @access private
     */
    private function loadData()
    {
        $data = $this->store->read();

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
        # Clear the internal session data
        $this->clear();

        # Clear the flash data
        $this->flash->clear();

        # Generate a new access token
        $this->token->regenerate();
    }
}