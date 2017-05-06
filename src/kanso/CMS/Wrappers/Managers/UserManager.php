<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers\Managers;

use Kanso\CMS\Wrappers\Managers\Manager;
use Kanso\CMS\Wrappers\Providers\UserProvider;
use Kanso\Framework\Database\Query\Builder;
use Kanso\Framework\Http\Cookie\Cookie;
use Kanso\Framework\Http\Session\Session;
use Kanso\Framework\Http\Request\Environment;
use Kanso\Framework\Config\Config;
use Kanso\Framework\Security\Crypto;
use Kanso\Framework\Utility\Str;
use Kanso\Framework\Utility\UUID;
use Kanso\CMS\Email\Email;

/**
 * User manager
 *
 * @author Joe J. Howard
 */
class UserManager extends Manager
{
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
     * Cookie manager
     * 
     * @var \Kanso\Framework\Http\Cookie\Cookie
     */ 
    private $cookie;

    /**
     * Session manager
     * 
     * @var \Kanso\Framework\Http\Session\Session
     */ 
    private $session;

    /**
     * Encryption manager
     * 
     * @var \Kanso\Framework\Security\Crypto
     */
    private $crypto;

    /**
     * Request environment
     * 
     * @var \Kanso\Framework\Http\Request\Environment
     */
    private $environment;

    /**
     * Config 
     * 
     * @var \Kanso\Framework\Config\Config
     */
    private $config;

    /**
     * Mailer utility 
     * 
     * @var \Kanso\CMS\Email\Email
     */
    private $email;

	/**
     * Override inherited constructor
     *
     * @access public
     * @param  \Kanso\Framework\Database\Query\Builder   $SQL          Query builder instance
     * @param  \Kanso\CMS\Auth\UserProvider              $provider     User provider instance
     * @param  \Kanso\Framework\Security\Crypto          $crypto       Encryption manager
     * @param  \Kanso\Framework\Http\Cookie\Cookie       $cookie       Cookie manager
     * @param  \Kanso\Framework\Http\Session\Session     $session      Session manager
     * @param  \Kanso\Framework\Http\Request\Environment $environment  Request environment
     * @param  \Kanso\Framework\Config\Config            $config       Config
     * @param  \Kanso\CMS\Email\Email                    $email        Mailer utility
     */
    public function __construct(Builder $SQL, UserProvider $provider, Crypto $crypto, Cookie $cookie, Session $session, Config $config, Environment $environment, Email $email)
    {
    	$this->SQL = $SQL;

    	$this->provider = $provider;

        $this->crypto = $crypto;

        $this->environment = $environment;

        $this->config = $config;

        $this->cookie = $cookie;

        $this->session = $session;

        $this->email = $email;
    }

    /**
     * Creates a new user
     *
     * @access public
     * @param  string $email    User email address
     * @param  string $username User username
     * @param  string $password User password
     * @param  string $role     User role
     * @param  bool   $activate Activate the user immediately
     * @return mixed
     */
    public function create(string $email, string $username, string $password, string $role = 'guest', bool $activate = false)
    {
        $password = utf8_encode($this->crypto->password()->hash($password));

        $status = !$activate ? 'pending' : 'confirmed';
        
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        $username = Str::alphaNumDash($username);

        $slug = Str::slug($username);

        $token = UUID::v4();

        $key = !$activate ? UUID::v4() : null;

        # username, email and slug must be unique
        if ($this->SQL->SELECT('id')->FROM('users')->WHERE('username', '=', $username)->ROW())
        {
            return self::USERNAME_EXISTS;
        }
        else if ($this->SQL->SELECT('id')->FROM('users')->WHERE('slug', '=', $slug)->ROW())
        {
            return self::SLUG_EXISTS;
        }
        else if ($this->SQL->SELECT('id')->FROM('users')->WHERE('email', '=', $email)->ROW())
        {
            return self::EMAIL_EXISTS;
        }
        
        return $this->provider->create([
            'email'    => $email,
            'username' => $username,
            'hashed_pass' => $password,
            'slug'     => $slug,
            'status'   => $status,
            'role'     => $role,
            'access_token' => $token,
            'kanso_register_key' => $key,
        ]);      
    }

    /**
     * Registers a new admin user for the CMS.
     *
     * @access public
     * @param  string $email     Valid email address
     * @param  string $role      'administrator' or 'writer'
     * @param  bool   $sendEamil Should we send the user an email with username and password ? (optional) (default true)
     * @return mixed
     */
    public function createAdmin(string $email, string $role = 'administrator', bool $sendEamil = true)
    {
        # Create a unique username based on their email
        $username = $this->uniqueUserName(Str::slug(Str::getBeforeFirstChar($email, '@')));

        # Generate a random password
        $password = Str::random(10);

        # Create the user
        $user = $this->create($email, $username, $password, $role, true);

        # Validate the user was created
        if ($user === self::USERNAME_EXISTS || $user === self::SLUG_EXISTS || $user === self::EMAIL_EXISTS || !$user)
        {
            return $user;
        }

        # Should we send an email with their username and password
        if ($sendEamil)
        {
            # username and password for email
            $emailData =
            [
                'username'    => $user->username, 
                'password'    => $password,
                'websiteName' => $this->environment->DOMAIN_NAME,
                'websiteUrl'  => $this->environment->HTTP_HOST,
                'loginURL'    => $this->environment->HTTP_HOST.'/admin/login/',
            ];
           
            # Email credentials
            $senderName   = $this->config->get('cms.site_title');
            $senderEmail  = 'no-reply@'.$this->environment->DOMAIN_NAME;
            $emailSubject = 'Welcome to '.$this->config->get('cms.site_title');
            $emailContent = $this->email->html($emailSubject, $this->email->preset('new-admin', $emailData));
            $emailTo      = $user->email;

            $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);
        }
       
        return true;
    }

    /**
     * Create a new regular user
     *
     * @access public
     * @param  string $email     Valid email address
     * @param  string $username  Username (optional) (default '')
     * @param  string $password  Password string (optional) (default '')
     * @param  string $name      Users name  (optional) (default '')
     * @param  string $role      User role  (optional) (default 'guest')
     * @param  bool   $activate  Activate the user straight away (optional) (default false)
     * @param  bool   $activate  Should we send the user an email with username and password ? (optional) (default true)
     * @return mixed
     */
    public function createUser(string $email, string $username = '', string $password = '', string $name = '', string $role = 'guest', bool $activate = false, bool $sendEamil = true)
    {

        # Create a unique username based on the email if one
        # wasnt provided
        if (empty($username))
        {
            $username = $this->uniqueUserName(Str::slug(Str::getBeforeFirstChar($email, '@')));
        }

        # Generate a random password if one wasn't provided
        if (empty($password))
        {
            $password = Str::random(10);
        }       

        # Create the user
        $user = $this->create($email, $username, $password, $role, $activate);

        # Validate the user was created
        if ($user === self::USERNAME_EXISTS || $user === self::SLUG_EXISTS || $user === self::EMAIL_EXISTS || !$user)
        {
            return $user;
        }

        # Send the email verification email
        if (!$activate && $sendEamil)
        {
            $emailData =
            [
                'name'        => $user->name, 
                'confirmURL'  => $this->environment->HTTP_HOST.'/confirm-account/?token='.$user->kanso_register_key,
                'websiteName' => $this->environment->DOMAIN_NAME,
                'websiteUrl'  => $this->environment->HTTP_HOST,
            ];
           
            # Email credentials
            $senderName   = $this->config->get('cms.site_title');
            $senderEmail  = 'no-reply@'.$this->environment->DOMAIN_NAME;
            $emailSubject = 'Please verify your email address';
            $emailContent = $this->email->html($emailSubject, $this->email->preset('confirm-account', $emailData));
            $emailTo      = $user->email;

            $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);
        }

        return true;
    }

    /**
     * Activate an existing user
     *
     * @access public
     * @param  string $token Verification token from DB
     * @return bool
     */
    public function activate(string $token): bool
    {
        # Validate the user exists
        $user = $this->provider->byKey('kanso_register_key', $token, true);

        if ($user)
        {
            $user->kanso_register_key = null;
            
            $user->status = 'confirmed';
            
            if ($user->save())
            {
                return true;
            }
        }

        return false;
    }

	/**
     * Deletes an existing user
     * 
     * @access public
     * @param  mixed $usernameIdorEmail Username, id or email
     * @return bool
     */
	public function delete($usernameIdorEmail): bool
	{
		$user = false;

		if (is_integer($usernameIdorEmail))
		{
			$user = $this->byId($usernameIdorEmail);
		}
		else if (filter_var($usernameIdorEmail, FILTER_VALIDATE_EMAIL))
		{
			$user = $this->byEmail($usernameIdorEmail);
		}
		else
		{
			$user = $this->byUsername($usernameIdorEmail);
		}

		if ($user)
		{
			return $user->delete();
		}
		
		return false;	
	}

    /**
     * {@inheritdoc}
     */
    public function provider(): UserProvider
	{
        return $this->provider;
	}

	/**
     * Gets a user by id
     * 
     * @access public
     * @param  int    $id Tag id
     * @return mixed
     */
	public function byId(int $id)
	{
		return $this->provider->byId($id);
	}

	/**
     * Gets a user by email
     * 
     * @access public
     * @param  string $email User email
     * @return mixed
     */
	public function byEmail(string $email)
	{
		return $this->provider->byKey('email', $email, true);
	}

	/**
     * Gets a user by username
     * 
     * @access public
     * @param  string $username Username
     * @return mixed
     */
	public function byUsername(string $username)
	{
		return $this->provider->byKey('username', $username, true);
	}

	/**
     * Gets a user by access token
     * 
     * @access public
     * @param  string $token User access token
     * @return mixed
     */
	public function byToken(string $token)
	{
		return $this->provider->byKey('access_token', $token, true);
	}

	 /**
     * Create a unique username
     *
     * @access private
     * @param  string $username The username
     * @return string
     */
    private function uniqueUserName(string $username): string
    {
        $baseName = $username;
        $count    = 1;
        $exists   = $this->byUsername($username);
        
        while (!empty($exists))
        {
            $username = $baseName.$count;
            $exists   = $this->byUsername($username);
            $count++;
        }

        return $username;
    }
}
