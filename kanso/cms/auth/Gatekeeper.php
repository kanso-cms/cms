<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\auth;

use kanso\cms\auth\adapters\EmailAdapter;
use kanso\cms\wrappers\providers\UserProvider;
use kanso\framework\database\query\Builder;
use kanso\framework\http\cookie\Cookie;
use kanso\framework\http\session\Session;
use kanso\framework\security\Crypto;
use kanso\framework\utility\UUID;

/**
 * CMS gatekeeper.
 *
 * @author Joe J. Howard
 */
class Gatekeeper
{
    /**
     * Status code for banned users.
     *
     * @var int
     */
    const LOGIN_BANNED = 100;

    /**
     * Status code for users who need to activate their account.
     *
     * @var int
     */
    const LOGIN_ACTIVATING = 101;

    /**
     * Status code for users who fail to provide the correct credentials.
     *
     * @var int
     */
    const LOGIN_INCORRECT = 102;

    /**
     * Status code for users that are temporarily locked.
     *
     * @var int
     */
    const LOGIN_LOCKED = 103;

    /**
     * SQL Query Builder.
     *
     * @var \kanso\framework\database\query\Builder
     */
    private $SQL;

    /**
     * CMS User Provider.
     *
     * @var \kanso\cms\wrappers\providers\UserProvider
     */
    private $provider;

    /**
     * The HTTP current user if one exists.
     *
     * @var \kanso\cms\wrappers\User|null
     */
    private $user = null;

    /**
     * Cookie manager.
     *
     * @var \kanso\framework\http\cookie\Cookie
     */
    private $cookie;

    /**
     * Session manager.
     *
     * @var \kanso\framework\http\session\Session
     */
    private $session;

    /**
     * Encryption manager.
     *
     * @var \kanso\framework\security\Crypto
     */
    private $crypto;

    /**
     * Mailer utility.
     *
     * @var \kanso\cms\auth\adapters\EmailAdapter
     */
    private $emailAdapter;

    /**
     * Constructor.
     *
     * @param \kanso\framework\database\query\Builder    $SQL          Query builder instance
     * @param \kanso\cms\wrappers\providers\UserProvider $provider     User provider instance
     * @param \kanso\framework\security\Crypto           $crypto       Encryption manager
     * @param \kanso\framework\http\cookie\Cookie        $cookie       Cookie manager
     * @param \kanso\framework\http\session\Session      $session      Session manager
     * @param \kanso\cms\auth\adapters\EmailAdapter      $emailAdapter Mailer utility
     */
    public function __construct(Builder $SQL, UserProvider $provider, Crypto $crypto, Cookie $cookie, Session $session, EmailAdapter $emailAdapter)
    {
        $this->SQL = $SQL;

        $this->provider = $provider;

        $this->crypto = $crypto;

        $this->cookie = $cookie;

        $this->session = $session;

        $this->emailAdapter = $emailAdapter;

        $this->isLoggedIn(true);
    }

    /**
     * Returns the current HTTP user if they are logged in.
     *
     * @return mixed
     */
    public function getUser()
    {
        if (is_null($this->user))
        {
            $id = $this->cookie->get('user_id');

            if ($id)
            {
                $this->user = $this->provider->byId($id);

                // Fallback user could not be found
                if (!$this->user)
                {
                    $this->logout();
                }
            }
        }

        return $this->user;
    }

    /**
     * Reload the current user's data from the database.
     */
    public function refreshUser(): void
    {
        if ($this->isLoggedIn())
        {
            $this->logClientIn($this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', $this->user->id)->ROW());
        }
    }

    /**
     * Returns the CSRF token.
     *
     * @return string
     */
    public function token()
    {
        if ($this->isLoggedIn())
        {
            return $this->user->access_token;
        }
        else
        {
            return $this->session->token()->get();
        }
    }

    /**
     * Validate the current HTTP client is logged in.
     *
     * @param  bool $runFresh Don't use the cached result
     * @return bool
     */
    public function isLoggedIn(bool $runFresh = false): bool
    {
        // If we are not hard checking
        if (!$runFresh)
        {
            return $this->cookie->isLoggedIn();
        }

        // If we are not logged in
        if (!$this->cookie->isLoggedIn() || !$this->cookie->get('user_id') || !$this->getUser())
        {
            return false;
        }

        // Compare access tokens.
        // If the tokens don't match their cookie should be destroyed
        // and they should be logged out immediately.
        // as they have logged into a different machine
        if ($this->session->token()->get() !== $this->user->access_token)
        {
            $this->user = null;

            $this->cookie->destroy();

            $this->session->destroy();

            $this->session->start();

            return false;
        }

        return true;
    }

    /**
     * Is the current user a guest - i.e not allowed inside the admin panel.
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        // Validate the user is logged in first
        if ($this->isLoggedIn())
        {
            return $this->user->role !== 'administrator' && $this->user->role !== 'writer';
        }

        return true;
    }

    /**
     * Is the user a an admin (i.e allowed into the admin panel).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        // Validate the user is logged in first
        if ($this->isLoggedIn())
        {
            return $this->user->role === 'administrator' || $this->user->role === 'writer';
        }

        return false;
    }

    /**
     * Validate a the current user's access token
     * Checks if the user's token matches the one in the
     * cookie as well as the DB.
     *
     * @param  string $token User's access token
     * @return bool
     */
    public function verifyToken(string $token): bool
    {
        // Get the cookie token
        $session_token = $this->session->token()->get();

        // Logged in users must compare 3 tokens - DB, Cookie, Argument
        if ($this->isLoggedIn())
        {
            return $token === $session_token && $session_token === $this->user->access_token;
        }

        // Other users just compare 2 - cookie, argument
        return $token === $session_token;
    }

    /**
     * Try to log the current user in by email and password.
     *
     * @param  string   $username Username or email address
     * @param  string   $password Raw passowrd
     * @param  bool     $force    Login a user without their password needed (optional) (default false)
     * @return bool|int
     */
    public function login(string $username, string $password, bool $force = false)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL))
        {
            // Get the user's row by the email
            $user = $this->SQL->SELECT('*')->FROM('users')->WHERE('email', '=', $username)->ROW();
        }
        else
        {
            // Get the user's row by the username
            $user = $this->SQL->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->ROW();
        }

        // Validate the user exists
        if (!$user || empty($user))
        {
            return self::LOGIN_INCORRECT;
        }

        // Forced login
        if ($force === true)
        {
            // Log the client in
            $this->logClientIn($user);

            return true;
        }

        // Pending users
        if ($user['status'] === 'pending')
        {
            return self::LOGIN_ACTIVATING;
        }

        // Locked users
        elseif ($user['status'] === 'locked')
        {
            return self::LOGIN_LOCKED;
        }

        // Banned users
        elseif ($user['status'] === 'banned')
        {
            return self::LOGIN_BANNED;
        }

        // Compare the hashed password to the provided password
        if ($this->crypto->password()->verify($password, utf8_decode($user['hashed_pass'])))
        {
            // Log the client in
            $this->logClientIn($user);

            return true;
        }

        return self::LOGIN_INCORRECT;
    }

    /**
     * Log the current user out.
     */
    public function logout(): void
    {
        // Keep the cookie but set as not logged in
        $this->cookie->logout();

        // Remove the user object
        $this->user = null;
    }

    /**
     * Forgot password.
     *
     * @param  string $username  Username or email address for user to reset password
     * @param  bool   $sendEamil Send the user an email (optional) (default true)
     * @return bool
     */
    public function forgotPassword(string $username, bool $sendEamil = true): bool
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL))
        {
            $user = $this->provider->byKey('email', $username, true);
        }
        else
        {
            $user = $this->provider->byKey('username', $username, true);
        }

        if (!$user)
        {
            return false;
        }

        // Create a token for them
        $user->kanso_password_key = UUID::v4();

        $user->save();

        if ($sendEamil)
        {
            $this->emailAdapter->forgotPassword($user);
        }

        return true;
    }

    /**
     * Reset password.
     *
     * @param  string $password  New password
     * @param  string $token     Reset token from the database
     * @param  bool   $sendEamil Reset token from the database
     * @return bool
     */
    public function resetPassword(string $password, string $token, bool $sendEamil = true): bool
    {
        // Validate the user exists
        $user = $this->provider->byKey('kanso_password_key', $token, true);

        if (!$user)
        {
            return false;
        }

        $user->kanso_password_key = '';
        $user->hashed_pass = utf8_encode($this->crypto->password()->hash($password));
        $user->save();

        if ($sendEamil)
        {
            $this->emailAdapter->resetPassword($user);
        }

        return true;
    }

    /**
     * Forgot username.
     *
     * @param  string $email Email for user reminder to be sent
     * @return bool
     */
    public function forgotUsername(string $email): bool
    {
        // Validate the user exists
        $user = $this->provider->byKey('email', $email, true);

        if (!$user)
        {
            return false;
        }

        $this->emailAdapter->forgotUsername($user);

        return true;
    }

    /**
     * Log client in.
     *
     * @param array $_user Row from database
     */
    private function logClientIn(array $_user): void
    {
        // Create a fresh cookie
        $this->cookie->destroy();

        // Create a fresh session
        $this->session->destroy();
        $this->session->start();

        // Get the new access token
        $token = $this->session->token()->get();
        $_user['access_token'] = $token;

        // Add the user credentials
        $this->cookie->setMultiple([
            'user_id' => $_user['id'],
            'email'   => $_user['email'],
        ]);

        // Save everything to session
        $this->session->setMultiple($_user);

        // Update the user's access token in the DB
        // to match the newly created one
        $this->SQL
            ->UPDATE('users')->SET(['access_token' => $token])
            ->WHERE('id', '=', $_user['id'])
            ->QUERY();

        // Log the client in
        $this->cookie->login();

        // Save the user
        $this->user = $this->provider->byId($_user['id']);
    }
}
