<?php

namespace Kanso\Auth;

/**
 * GateKeeper
 *
 * This class serves as the main point of security and validation
 * for Kanso users for both the admin panel and other users.
 *
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
     * Status code for users that already exists by email.
     *
     * @var int
     */
    const EMAIL_EXISTS = 104;

    /**
     * Status code for users that already exists by username.
     *
     * @var int
     */
    const USERNAME_EXISTS = 105;

    /**
     * Status code for users that already exists by username.
     *
     * @var int
     */
    const SLUG_EXISTS = 106;

	/**
     * @var Kanso\Auth\Adapters\User|NULL
     */
	private $user = NULL;

    /**
     * @var \Kanso\Kanso::getInstance()->Database()->Builder()
     */
    private $SQL;

    /**
     * @var \Kanso\Auth\Adapters\UserProvider
     */
    private $userProvider;

	/**
     * Constructor
     *
     */
    public function __construct()
    {
        # Get and SQL builder
        $this->SQL = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Hard check for logged in
        $this->isLoggedIn(true);
    }


    /********************************************************************************
    * USER MANAGEMENT
    *******************************************************************************/

    /**
     * Create a new user
     * 
     * @return Kanso\Auth\Adapters\User|false
     *
     */
    public function createUser($email, $username, $password, $role = 'guest', $activate = false)
    {
        # Initial sanitation
        $password = utf8_encode(\Kanso\Security\Encrypt::hash($password));
        $status   = !$activate ? 'pending' : 'confirmed';
        $email    = filter_var($email, FILTER_SANITIZE_EMAIL);
        $username = \Kanso\Utility\Str::alphaNumeric($username);
        $slug     = \Kanso\Utility\Str::slugFilter($username);
        $token    = \Kanso\Utility\Str::generateRandom(16, true);
        $key      = !$activate ? \Kanso\Utility\Str::generateRandom(40, true) : NULL;

        # username, email and slug must be unique
        if ($this->SQL->SELECT('id')->FROM('users')->WHERE('username', '=', $username)->ROW()) {
            return self::USERNAME_EXISTS;
        }
        else if ($this->SQL->SELECT('id')->FROM('users')->WHERE('slug', '=', $slug)->ROW()) {
            return self::SLUG_EXISTS;
        }
        else if ($this->SQL->SELECT('id')->FROM('users')->WHERE('email', '=', $email)->ROW()) {
            return self::EMAIL_EXISTS;
        }
        
        # Create the new user and save
        $user = new \Kanso\Auth\Adapters\User([
            'email'    => $email,
            'username' => $username,
            'password' => $password,
            'slug'     => $slug,
            'status'   => $status,
            'role'     => $role,
            'access_token' => $token,
            'kanso_register_key' => $key,
        ]);
        if ($user->save()) return $user;

        return false;        
    }

    /**
     * Get the current user if they are logged in
     * 
     * @return Kanso\Auth\Adapters\User|NULL
     *
     */
    public function getUser()
    {
        if (is_null($this->user)) {
            $id = \Kanso\Kanso::getInstance()->Cookie->get('id');
            if ($id) $this->user = new \Kanso\Auth\Adapters\User($id);
        }

        return $this->user;
    }

    /**
     * Get the user provider
     * 
     * @return Kanso\Auth\Adapters\User|NULL
     *
     */
    public function getUserProvider()
    {
        if (!$this->userProvider) $this->userProvider = new \Kanso\Auth\Adapters\UserProvider;
        return $this->userProvider;
    }

    /**
     * Refresh the current user
     * 
     */
    public function refreshUser()
    {
        if ($this->isLoggedIn()) {

            $freshUser = new \Kanso\Auth\Adapters\User($this->user->id);

            $this->logClientIn($freshUser);
        }
    }


    /********************************************************************************
    * VALIDATORS
    *******************************************************************************/

    /**
     * Validate that a user is logged in to Kanso
     *
     * @param  boolean    $runFresh    Refresh the result (optional defaults to FALSE)
     * @return boolean
     *
     */
    public function isLoggedIn($runFresh = false)
    {
        # If we are not hard checking
        if (!$runFresh) return \Kanso\Kanso::getInstance()->Cookie->isLoggedIn();

        # If we are not logged in
        if (!\Kanso\Kanso::getInstance()->Cookie->isLoggedIn()) return false;

        # If there is no user id in the cookie
        if (!\Kanso\Kanso::getInstance()->Cookie->get('id')) return false;

        # Get the current user
        if (!$this->getUser()) return false;

        # Compare the two access tokens
        $cookie_token = \Kanso\Kanso::getInstance()->Cookie->getToken();
        $db_token     = $this->user->access_token;

        # If the tokens don't match their cookie should be destroyed
        # and they should be logged out immediately.
        # as they have logged into a different machine
        if ($cookie_token !== $db_token) {
            $this->user = NULL;
            \Kanso\Kanso::getInstance()->Cookie->clear();
            return false;
        }
        return true;
    }

    /**
	 * Is the current user a guest - i.e not allowed inside the admin panel
	 *
	 * @return boolean
     *
	 */
    public function isGuest()
    {
    	# Validate the user is logged in first
        if (!$this->isLoggedIn()) return false;

        # Check if they're an admin user
        return $this->user->role !== 'administrator' && $this->user->role !== 'writer';
    }

    /**
     * Is the user a an admin (i.e allowed into the admin panel)
     *
     * @return boolean
     *
     */
    public function isAdmin()
    {
        # Validate the user is logged in first
        if (!$this->isLoggedIn()) return false;

        return $this->user->role === 'administrator' || $this->user->role === 'writer';
    }

    /**
     * Validate a the current user's access token
     * Checks if the user's token matches the one in the 
     * cookie as well as the DB
     *
     * @param  string    $token
     * @return boolean
     *
     */
    public function verifyToken($token)
    {
        # Get the cookie token
        $cookie_token = \Kanso\Kanso::getInstance()->Cookie->getToken();

        # Logged in users must compare 3 tokens - DB, Cookie, Argument
        if ($this->isLoggedIn()) {
            return $token === $cookie_token && $cookie_token === $this->user->access_token;
        }

        # Other users just compare 2 - cookie, argument
        return $token === $cookie_token;
    }

    /********************************************************************************
    * LOGIN/LOGOUT
    *******************************************************************************/

    /**
     * Try to log the current user in by email and password
     * 
     * @param  string    $username
     * @param  string    $password
     * @return true|self::LOGIN_INCORRECT|self::LOGIN_ACTIVATING|self::LOGIN_LOCKED|self::LOGIN_BANNED
     *
     */
    public function login($username, $password)
    {
        # Get the user's row by the username
        $user = $this->SQL->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->ROW();

        # Validate the user exists
        if (!$user || empty($user)) return self::LOGIN_INCORRECT;

        # Users must be a writer or administrator
        if ($user['role'] !== 'administrator' && $user['role'] !== 'writer') {
            return self::LOGIN_INCORRECT;
        }

        # Pending users
        if ($user['status'] === 'pending') {
            return self::LOGIN_ACTIVATING;
        }

        # Locked users
        else if ($user['status'] === 'locked') {
            return self::LOGIN_LOCKED;
        }

        # Banned users
        else if ($user['status'] === 'banned') {
            return self::LOGIN_BANNED;
        }

        # Save the hashed password
        $hashedPass = utf8_decode($user['hashed_pass']);
        
        # Compare the hashed password to the provided password
        if (\Kanso\Security\Encrypt::verify($password, $hashedPass)) {

            # Log the client in
            $user = new \Kanso\Auth\Adapters\User($user['id']);
        	$this->logClientIn($user);

            # Fire the event
            \Kanso\Events::fire('login', $this->user);

        	return true;
        }

        return self::LOGIN_INCORRECT;
    }

    /**
     * Log the current user out
     * 
     * @return null
     *
     */
    public function logout()
    {
        # Fire the event
        \Kanso\Events::fire('logout', $this->user);

        # Clear the Cookie
        \Kanso\Kanso::getInstance()->Cookie->clear();

    	# Remove the user object
    	$this->user = NULL;
    }

    /********************************************************************************
    * USER MANAGEMENT
    *******************************************************************************/

    /**
     * Create a new admin user
     * 
     * This method creates a new user as an admin
     * meaning they are allowed to login to the admin panel.
     *
     * @param  string    $email        Valid email address
     * @param  string    $role         'administrator' or 'writer'
     * @param  bolean    $sendEamil    Should we send the user an email with username and password ? (optional defaults to true)
     * @return Kanso\Auth\Adapters\User|false
     *
     */
    public function registerAdmin($email, $role, $sendEamil = true)
    {
        # Create a unique username based on their email
        $username = $this->uniqueUserName(\Kanso\Utility\Str::slugFilter(\Kanso\Utility\Str::getBeforeFirstChar($email, '@')));

        # Generate a random password
        $password = \Kanso\Utility\Str::generateRandom(10);

        # Create the user
        $user = $this->createUser($email, $username, $password, $role, true);

        # Validate the user was created
        if ($user === self::USERNAME_EXISTS || $user === self::SLUG_EXISTS || $user === self::EMAIL_EXISTS || !$user) {
            return $user;
        }

        # Should we send an email with they're username and password
        if ($sendEamil) {

            # username and password for email
            $env       = \Kanso\Kanso::getInstance()->Environment;
            $config    = \Kanso\Kanso::getInstance()->Config;
            $emailData = [
                'username'    => $user->username, 
                'password'    => $password,
                'websiteName' => $env['KANSO_WEBSITE_NAME'],
                'loginURL'    => $env['KANSO_ADMIN_URI'].'/login/'
            ];
           
            # Email credentials
            $emailFrom        = $config['KANSO_SITE_TITLE'];
            $emailAddressFrom = 'no-reply@'.$env['KANSO_WEBSITE_NAME'];
            $emailSubject     = 'Welcome to '.$config['KANSO_SITE_TITLE'];
            $emailMsg         = \Kanso\Templates\Templater::getTemplate('EmailNewAdmin', $emailData);
            $emailTo          = $user->email;

            # Send email
            \Kanso\Utility\Mailer::sendHTMLEmail($emailTo, $emailFrom, $emailAddressFrom, $emailSubject, $emailMsg);

        }
       
        return true;
    }

    /**
     * Create a new regular user
     *
     * @param  string    $email        Valid email address
     * @param  string    $username     Username
     * @param  string    $password     Password string
     * @param  string    $name         Users name
     * @param  string    $role         User role
     * @param  bolean    $activate     Activate the user straight away (optional defaults to false)
     * @param  bolean    $activate     Should we send the user an email with username and password ? (optional defaults to true)
     * @return Kanso\Auth\Adapters\User|false
     *
     */
    public function registerUser($email, $username = '', $password = '', $name = '', $role = 'guest', $activate = false, $sendEamil = true)
    {

        # Create a unique username based on the email if one
        # wasnt provided
        if (empty($username)) {
            $username = $this->uniqueUserName(\Kanso\Utility\Str::slugFilter(\Kanso\Utility\Str::getBeforeFirstChar($email, '@')));
        }

        # Generate a random password if one wasn't provided
        if (empty($password)) {
            $password = \Kanso\Utility\Str::generateRandom(10);
        }       

        # Create the user
        $user = $this->createUser($email, $username, $password, $role, $activate);

        # Validate the user was created
        if ($user === self::USERNAME_EXISTS || $user === self::SLUG_EXISTS || $user === self::EMAIL_EXISTS || !$user) {
            return $user;
        }

        # Send the email verification email
        if (!$activate && $sendEamil) {

            # username and password for email
            $env       = \Kanso\Kanso::getInstance()->Environment;
            $config    = \Kanso\Kanso::getInstance()->Config;
            $emailData = [
                'name'        => $user->name, 
                'username'    => $user->username, 
                'confirmURL'  => $env['HTTP_HOST'].'/confirm-account/?token='.$user->kanso_register_key,
                'websiteName' => $env['KANSO_WEBSITE_NAME'],
                'loginURL'    => $env['HTTP_HOST'].'/login/',
            ];

            # Email credentials
            $emailFrom        = $config['KANSO_SITE_TITLE'];
            $emailAddressFrom = 'no-reply@'.$env['KANSO_WEBSITE_NAME'];
            $emailSubject     = 'Please verify your email address';
            $emailMsg         = \Kanso\Templates\Templater::getTemplate('EmailNewAdmin', $emailData);
            $emailTo          = $user->email;

            # Send email
            \Kanso\Utility\Mailer::sendHTMLEmail($emailTo, $emailFrom, $emailAddressFrom, $emailSubject, $emailMsg);

        }

        return true;
    }

    /**
     * Activate an existing user
     *
     * @param  string    $token        Verification token from DB
     * @return boolean
     *
     */
    public function activateUser($token)
    {
        # Validate the user exists
        $userRow = $this->SQL->SELECT('*')->FROM('users')->WHERE('kanso_register_key', '=', $token)->ROW();
        if (!$userRow) return false;

    	$user = new \Kanso\Auth\Adapters\User($userRow);

        $user->kanso_register_key = '';
        $user->status = 'confirmed';
        if ($user->save()) return true;
        
        return false;
    }

    /**
     * Forgot password
     *
     * @param  string     $username    Username for user to reset password
     * @param  boolean    $sendEamil   Send the user an email (optional defaults to true)
     * @return boolean
     *
     */
    public function forgotPassword($username, $sendEamil = true) 
    {
        # Validate the user exists
        $userRow = $this->SQL->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->ROW();
        if (!$userRow) return false;

        # Create a token for them
        $user = new \Kanso\Auth\Adapters\User($userRow);
        $user->kanso_password_key = \Kanso\Utility\Str::generateRandom(40, true);
        if (!$user->save()) return false;

        if ($sendEamil) {

            # username and password for email
            $env       = \Kanso\Kanso::getInstance()->Environment;
            $config    = \Kanso\Kanso::getInstance()->Config;
            $resetUrl  = $env['HTTP_HOST'].'/reset-password/?token='.$user->kanso_password_key;
            if ($user->role === 'administrator' || $user->role === 'writer') {
                $resetUrl  = $env['KANSO_ADMIN_URI'].'/reset-password/?token='.$user->kanso_password_key;
            }

            $emailData = [
                'name'        => $user->name, 
                'resetUrl'    => $resetUrl,
                'websiteName' => $env['KANSO_WEBSITE_NAME'],
            ];

            # Email credentials
            $emailFrom        = $config['KANSO_SITE_TITLE'];
            $emailAddressFrom = 'no-reply@'.$env['KANSO_WEBSITE_NAME'];
            $emailSubject     = 'Request to reset your password';
            $emailMsg         = \Kanso\Templates\Templater::getTemplate('EmailForgotPassword', $emailData);
            $emailTo          = $user->email;

            # Send email
            \Kanso\Utility\Mailer::sendHTMLEmail($emailTo, $emailFrom, $emailAddressFrom, $emailSubject, $emailMsg);
        }

        return true;
    }

    /**
     * Reset password
     *
     * @param  string     $password    New password
     * @param  string     $token       Reset token from the database
     * @return boolean
     *
     */
    public function resetPassword($password, $token)
    {
        # Validate the user exists
        $userRow = $this->SQL->SELECT('*')->FROM('users')->WHERE('kanso_password_key', '=', $token)->ROW();
        if (!$userRow) return false;

        # Create a token for them
        $user = new \Kanso\Auth\Adapters\User($userRow);
        $user->kanso_password_key = '';
        $user->password = utf8_encode(\Kanso\Security\Encrypt::hash($password));
        if ($user->save) return true;

        return false;
    }


    /**
     * Forgot username
     *
     * @param  string     $email    Email for user reminder to be sent
     * @return boolean
     *
     */
    public function forgotUsername($email)
    {
        # Validate the user exists
        $userRow = $this->SQL->SELECT('*')->FROM('users')->WHERE('email', '=', $email)->ROW();
        if (!$userRow) return false;

        # email variables
        $env       = \Kanso\Kanso::getInstance()->Environment;
        $config    = \Kanso\Kanso::getInstance()->Config;
        $emailData = [
            'name'        => $user->name, 
            'username'    => $user->username,
            'websiteName' => $env['KANSO_WEBSITE_NAME'],
        ];

        # Email credentials
        $emailFrom        = $config['KANSO_SITE_TITLE'];
        $emailAddressFrom = 'no-reply@'.$env['KANSO_WEBSITE_NAME'];
        $emailSubject     = 'Username reminder at '.$env['KANSO_WEBSITE_NAME'];
        $emailMsg         = \Kanso\Templates\Templater::getTemplate('EmailForgotPassword', $emailData);
        $emailTo          = $user->email;

        # Send email
        \Kanso\Utility\Mailer::sendHTMLEmail($emailTo, $emailFrom, $emailAddressFrom, $emailSubject, $emailMsg);

        return true;
    }
   
    /**
     * Delete a user
     *
     * This function removes a user from the database.
     *
     * @param  int     $id    The id of the user to remove
     * @return boolean
     *
     */
    public function deleteUser($id) 
    {
        # Validate the user exists
        $row = $this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', intval($id))->ROW();
        if (!$row) return false;

        # Get the user by id
        $user = new \Kanso\Auth\Adapters\User($row);

        # Delete the user
        if ($user->delete()) return true;

        return false;
    }

    /**
     * Change a user's role
     *
     * @param  $id             int       The id of the user to change
     * @param  $role           string    The role to change to
     * @return boolean
     *
     */
    public function changeUserRole($id, $role) 
    {
        # Validate the user exists
        $userRow = $this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', $id)->ROW();
        if (!$userRow) return false;

        # Create a token for them
        $user = new \Kanso\Auth\Adapters\User($userRow);
        $user->role = $role;
        $save = $user->save();
        
        if ($save) return true;

        return false;
    }

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    private function uniqueUserName($username)
    {
        $baseName = $username;
        $count    = 1;
        $exists   = $this->SQL->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->ROW();
        while (!empty($exists)) {
            $username = $baseName.$count;
            $exists   = $this->SQL->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->ROW();
            $count++;
        }
        return $username;
    }

    /**
     * Log client in
     *
     * This is responsible for logging a client into the 
     * admin panel.
     *
     * @param  Kanso\Auth\Adapters\User
     */
    public function logClientIn($_user) 
    {
        $Cookie = \Kanso\Kanso::getInstance()->Cookie;
        
        # Create a fresh cookie
        $Cookie->clear();

        # Add the user credentials
        $Cookie->putMultiple([
            'id'    => $_user->id,
            'email' => $_user->email,
            'name'  => $_user->name,
        ]);

        #Update the user's access token in the DB
        # to match the newly created one
        $this->SQL
            ->UPDATE('users')->SET(['access_token' => $Cookie->getToken()])
            ->WHERE('id', '=', $_user->id)
            ->QUERY();

        # Log the client in
        $Cookie->login();

        # Save the user
        $this->user = $_user;
    }

}