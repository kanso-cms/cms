<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\cookie;

use kanso\framework\common\ArrayAccessTrait;
use kanso\framework\common\ArrayIterator;
use kanso\framework\http\cookie\storage\StoreInterface;

/**
 * Cookie utility.
 *
 * @author Joe J. Howard
 */
class Cookie implements \IteratorAggregate
{
    use ArrayAccessTrait;

    /**
     * Logged in user 'yes'|'no'.
     *
     * @var string
     */
    protected $login = 'no';

    /**
     * Cookie storage implementation.
     *
     * @var object
     */
    private $store;

    /**
     * The cookie name.
     *
     * @var string
     */
    private $cookieName;

    /**
     * Cookie expiry unix timestamp.
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
     * Constructor.
     *
     * @param \kanso\framework\http\cookie\storage\StoreInterface $store         Cookie storage implementation
     * @param string                                              $cookieName    Name of the cookie
     * @param int                                                 $cookieExpires Date the cookie will expire (unix timestamp)
     */
    public function __construct(StoreInterface $store, string $cookieName, int $cookieExpires)
    {
        $this->store = $store;

        $this->cookieName = $cookieName;

        $this->cookieExpires = $cookieExpires;

        $this->readCookie();

        $this->validateExpiry();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Read the cookie sent from the browser.
     */
    private function readCookie(): void
    {
        $data = $this->store->read($this->cookieName);

        if ($data && is_array($data))
        {
            $this->overwrite($data);
        }

        $login = $this->store->read($this->cookieName . '_login');

        if ($login)
        {
            $this->login = $login === 'yes' ? 'yes' : 'no';
        }
    }

    /**
     * Checks if the cookie is expired - destroys it if it is.
     */
    private function validateExpiry(): void
    {
        if ((($this->cookieExpires - time()) + $this->get('last_active')) < time())
        {
            $this->destroy();
        }
        else
        {
            $this->set('last_active', time());
        }
    }

    /**
     * Is the user currently logged in.
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->login === 'yes';
    }

    /**
     * Log the client in.
     */
    public function login(): void
    {
        // Set as logged in
        $this->login = 'yes';
    }

    /**
     * Log the client in.
     */
    public function logout(): void
    {
        // Set as logged in
        $this->login = 'no';
    }

    /**
     * Send the cookies.
     */
    public function send(): void
    {
        if (!$this->sent())
        {
            $this->store->write($this->cookieName, $this->get());

            $this->store->write($this->cookieName . '_login', $this->login);

            $this->sent = true;
        }
    }

    /**
     * Send the cookies.
     */
    public function sent(): bool
    {
        return $this->sent;
    }

    /**
     * Destroy the cookie.
     */
    public function destroy(): void
    {
        $this->clear();

        $this->login = 'no';

        $this->set('last_active', time());
    }
}
