<?php

namespace Kanso\Auth;

/**
 * GateKeeper
 *
 * This class serves as the main point of security and validation
 * for managing Kanso Users
 *
 */
class Gatekeeper 
{

	/**
     * @var Kanso\Auth\Helper\User
     */
	private $user;

	/**
     * Constructor
     */
    public function __construct()
    {
    	# Get the user from the session and crete a new user object
        # Note this user may or may not be logged in
        $this->user = \Kanso\Kanso::getInstance()->Session->get();
    }

    /********************************************************************************
    * GETTERS
    *******************************************************************************/

    /**
     * Get the current user object
     * 
     * @return \Kanso\Auth\Helper\User
     */
    public function getUser()
    {       
        return $this->user;
    }

    /********************************************************************************
    * VALIDATORS
    *******************************************************************************/

    /**
     * Validate that a user is logged in to Kanso
     *
     * @return Bool 
     */
    public function isLoggedIn()
    {
        $session = \Kanso\Kanso::getInstance()->Session;
        if ($session->get('sessionLastActive') < strtotime('-12 hours')) $session->clear();
        return $session->get('sessionIsLoggedIn') === true && $session->get('KANSO_ADMIN_DATA') !== null;
    }

    /**
	 * Is the current user a guest (i.e not logged in)
	 *
	 * @return boolean
	 */
    public function isGuest()
    {
    	return $this->isLoggedIn() === false;
    }

    /**
     * Is the current user activated
     *
     * @return boolean
     */
    public function isActivated()
    {
        # Validate the user is logged in first
        if ($this->isGuest()) return false;

        return $this->user['status'] === 'confirmed';

        return false;
    }

    /**
     * Is the user a an admin (i.e not logged in)
     *
     * @return boolean
     */
    public function isAdmin()
    {
        # Validate the user is logged in first
        if ($this->isGuest()) return false;

        # Check if they're an admin user
        return $this->user['role'] === 'administrator';
    }

    /**
     * Validate a the current user's ajax token
     *
     * @return boolean
     */
    public function verifyAjaxToken($token)
    {
        # Get the keys from their session
        $keys = \Kanso\Kanso::getInstance()->Session->getAjaxTokens();
        
        # If the user is logged get the keys directly
        # From the database and make sure theyre the same
        if (!$this->isGuest()) {

            $entry = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('id', '=', $this->user['id'])->ROW();
            
            if ($entry['kanso_public_key'] !== $keys['key']) return false;
        }
        
        # Decrypt and verify
        return Helper\Token::verify($token, $keys['key'], $keys['salt']);
    }


    /********************************************************************************
    * LOGIN/LOGOUT
    *******************************************************************************/

    /**
     * Try to log the current user in by email and password
     * 
     * @param  string    $username
     * @param  string    $password
     * 
     * @return false|Kanso\Auth\Helper\User
     */
    public function login($username, $password)
    {
        # Get the user's row by the username
        $user = Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->ROW();

        # Validate the user exists
        if (!$user || empty($user)) return false;

        # Validate the user is activated
        if (!$this->isActivated()) return false;

        # Save the hashed password
        $hashedPass = utf8_decode($user['hashed_pass']);
        
        # Compare the hashed password to the provided password
        if (\Kanso\Security\Encrypt::verify($password, $hashedPass)) {

        	$this->logClientIn($user);

        	return $user;
        }

        return false;
    }

    /**
     * Log the current user out
     * 
     * @return null
     */
    public function logout()
    {
    	# Destroy the session
    	$this->logClientOut();

    	# Remove the user object
    	$this->user = null;
    }

    /********************************************************************************
    * USER MANAGEMENT
    *******************************************************************************/

    /**
     * Create a new unconfirmed user
     * 
     * This function will create a new unconfirmed Kanso user
     * Note this user will NOT be able to login or user Kanso
     * untill they confirm their email address.
     *
     * @return Kanso\Auth\Helper\User|false
     */
    public function registerUser($email, $role, $inviter)
    {
        # Get the databse instance
        $Database = \Kanso\Kanso::getInstance()->Database;

        # Get a new Query Builder
        $Query = $Database->Builder();

        # Get the Environment
        $env = \Kanso\Kanso::getInstance()->Environment;
            
        # Validate the member doesn't already exist
        $user = Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('email', '=', $email)->ROW();
        if ($user && $user['status'] === 'confirmed') return false;

        # Generate a random string for the register link
        $registerKey = \Kanso\Utility\Str::generateRandom(85, true);

        # If the user is not already invited add them to the database
        if (!$user) {
            $newUser = [
                'email'              => $email,
                'kanso_register_key' => $registerKey,
                'role'               => $role,
                'status'             => 'pending',
            ];
            $Query->INSERT_INTO('users')->VALUES($newUser)->QUERY();
        }
        # If the user is already invited, change their role and update with a new register key
        else {
            $values = [];
            $values['kanso_register_key'] = $registerKey;
            $values['role']               = $role;
            $values['status']             = 'pending';
            $Query->UPDATE('users')->SET($values)->WHERE('id', '=', $user['id']))->QUERY();
        }

        # Create array of data for email template
        $website   = $env['KANSO_WEBSITE_NAME'];
        $emailData = [
            'name'    => $inviter['name'],
            'website' => $website,
            'key'     => $registerKey,
            'link'    => $env['KANSO_ADMIN_URI'].'/register?'.$registerKey,
        ];

        # Get the email template
        $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailInviteNewUser');

        # Send email
        \Kanso\Utility\Mailer::sendHTMLEmail($email, $inviter['name'], 'no-reply@'.$website, 'Authorship Invite to '.$website, $msg);

        return true;
    }

    /**
     * Activate an existing user
     * 
     * Activate an existing user based on their account
     * activation token from the database.
     * 
     * @return Kanso\Auth\Helper\User|false
     */
    public function activateUser($username, $email, $password, $token)
    {
    	# Get the databse instance
        $Database = \Kanso\Kanso::getInstance()->Database;

        # Get a new Query Builder
        $Query = $Database->Builder();

        # Get the user
        $user = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('kanso_register_key', '=', $token)->ROW();
        
        # Validate the user and is not already activated
        if (!$user || $user['status'] === 'confirmed') return false;

        # Validate the entry email address is same as the requested
        if ($user['email']) !== $email) return false;

        # Validate another user with the same username doeesn't already exist
        $userExists = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->ROW();
        if ($userExists) return false;

        # Activate the new user and update their 
        # database enties
        $row = [];
        $row['hashed_pass']  = utf8_encode(\Kanso\Security\Encrypt::encrypt($password));
        $row['slug']         = \Kanso\Utility\Str::slugFilter($username);
        $row['status']       = 'confirmed';
        $row['kanso_register_key']  = '';

        # Update the user's row in the database
        $userRow = $Query->UPDATE('users')->SET($row)->WHERE('id', '=', $user['id'])->QUERY();
        
        # Validate the user was created
        if (!$userRow) return false;
        
        # Add the author's slug into Kanso's config
        $this->addAuthorSlug($userRow['slug']);

        # Create array of data for email template
        $website   = \Kanso\Kanso::getInstance()->Environment['KANSO_WEBSITE_NAME'];
        $emailData = [
            'name'     => $userRow['name'],
            'username' => $userRow['username'],
            'website'  => $website,
        ];

        # Get the email template
        $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailConfirmNewUser');
        
        # Send the email
        \Kanso\Utility\Mailer::sendHTMLEmail($userRow['name'], $website, 'no-reply@'.$website, 'Welcome to '.$website, $msg);

        # Return the user row
        return $user;
    }

    /**
     * Delete a user
     *
     * This function removes a user from the database.
     * Note that the user's status is changed to 'deleted' so that their
     * authorship details are still available
     *
     * @param  $id             int       The id of the user to remove
     * @param  $deleter        array     The user's database entry doing the current operation
     * @return string|bool
     */
    public function deleteUser($id, $deleter = null) 
    {
        # Make sure the id is an int
        $id = (int)$id;

        # Noone can delete the first admin
        if ($id === 1) return false;

        # Get the databse instance
        $Database = \Kanso\Kanso::getInstance()->Database;

        # Get a new Query Builder
        $Query = $Database->Builder();

        # If the user was not provided get it from the session
        # Validate the user is an admin
        if ($deleter) {
            
            if ($deleter['role'] !== 'administrator') return false;

            # A user can't delete themself
            if ($id === $deleter['id']) return false;
        }

        # Validate the user exists
        $userRow = $Query->SELECT('*')->FROM('users')->where('id', '=', $id)->ROW();
        if (!$userRow) return false;

        # Set the user's status to deleted 
        # We need to keep them in the database for authorship purposes though
        $slug = $userRow['slug'];
        $userRow['status']              = 'deleted';
        $userRow['username']            = null;
        $userRow['hashed_pass']         = null;
        $userRow['slug']                = null;
        $userRow['kanso_register_key']  = null;
        $userRow['kanso_password_key']  = null;
        $userRow['kanso_public_key']    = null;
        $userRow['kanso_public_salt']   = null;
        $userRow['kanso_keys_time']     = null;

        $Query->UPDATE('users')->SET($userRow)->WHERE('id', '=', $userRow['id'])->QUERY();

        $this->removeAuthorSlug($slug);
            
        return true;
    }

    /**
     * Forgot password
     *
     * @param  $id             int       The id of the user to change
     * @param  $role           string    The role to change to
     * @param  $currentUser    array     The user's database entry doing the current operation
     * @return string|bool
     */
    public function forgotPassword($username) 
    {

        # Get a new Query Builder
        $Query = $Database->Builder();

        # Validate the user exists
        $user = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->ROW();
        if (!$user) return false;

        # generate a token
        $token = \Kanso\Utility\Str::generateRandom(85, true);

        if ($token) {
            
            # Create array of data for email template
            $website   = \Kanso\Kanso::getInstance()->Environment['KANSO_WEBSITE_NAME'];
            $emailData = [
                'name'    => $savedRow['name'],
                'website' => $website,
                'key'     => $token,
            ];

            # Get the email template
            $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailForgotPassword');

            # Send email
            return \Kanso\Utility\Mailer::sendHTMLEmail($userRow['email'], $website, 'no-reply@'.$website, 'A reset password request has been made', $msg);
        }
        
        return false;
    }


    public function forgotUsername($email)
    {
        # Get the user's row 
        $userRow = \Kanso\Kanso::getInstance()->Database->Builder()->SELECT('*')->FROM('users')->WHERE('email', '=', $email)->ROW();

        # Validate the user exists
        if (!$userRow || empty($userRow)) return false;

        # Create array of data for email template
        $website   = \Kanso\Kanso::getInstance()->Environment['KANSO_WEBSITE_NAME'];
        $emailData = [
            'name'     => $userRow['name'],
            'username' => $userRow['username'],
            'website'  => $website,
        ];

        # Get the email template
        $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailForgotUsername');
        
        # Send the email
        return \Kanso\Utility\Mailer::sendHTMLEmail($userRow->get('email'), $website, 'no-reply@'.$website, 'A username reminder has been requested', $msg);
    }

    public function resetPassword($password, $token)
    {
        # Get a new Query Builder
        $Query = $Database->Builder();

        # Validate the user exists
        $user = $Query->SELECT('*')->FROM('users')->WHERE('kanso_password_key', '=', $token)->ROW();
        if (!$user) return false;

        # Change the users password and remove the key from the database
        $row = [];
        $row['hashed_pass']        = utf8_encode(\Kanso\Security\Encrypt::encrypt($password));
        $row['kanso_password_key'] = null;
        
        $update = $Query->UPDATE('users')->SET($row)->WHERE('id', '=', $user['id'])->QUERY();

        if (!$update) return false; 

        # Remove the password key from the session
        \Kanso\Kanso::getInstance()->Session->delete('KANSO_PASSWORD_KEY');

        # Reset the user's session
        \Kanso\Kanso::getInstance()->Session->freshSession();

        # Create array of data for email template
        $website   = \Kanso\Kanso::getInstance()->Environment['KANSO_WEBSITE_NAME'];
        $emailData = [
            'name'     => $userRow['name'],
            'username' => $userRow['username'],
            'website'  => $website,
        ];

        # Get the email template
        $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailResetPassword');

        # Send the email
        \Kanso\Utility\Mailer::sendHTMLEmail($userRow['email'], $website, 'no-reply@'.$website, 'Your password was reset at '.$website, $msg);

        return true;
    }


    /**
     * Change a user's role
     *
     * @param  $id             int       The id of the user to change
     * @param  $role           string    The role to change to
     * @param  $currentUser    array     The user's database entry doing the current operation
     * @return string|bool
     */
    public function changeUserRole($id, $role, $changer = null) 
    {
        # Make sure the id is an int
        $id = (int)$id;

        # Noone can change the first admin
        if ($id === 1) return false;

        # Get a new Query Builder
        $Query = $Database->Builder();

        # If the operator was not provided get it from the session
        # Validate the user is an admin
        if ($changer) {
            
            if ($changer['role'] !== 'administrator') return false;
           
            # A user can't change their own role
            if ($id === $changer['id']) return false;
        }

        # Validate the user exists
        $userRow = $Query->SELECT('*')->FROM('users')->WHERE('id', '=', $id)->ROW();
        if (!$userRow) return false;

        # Change the authors role
        $update = $Query->UPDATE('users')->SET(['role' => $role])->WHERE('id', '=', $userRow['id'])->QUERY();
        
        if ($update) return true;

        return false;
    }



    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Add a slug to Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be added
     */
    private function addAuthorSlug($slug)
    {
        # Get the slugs
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        $slugs[] = $slug;

        \Kanso\Kanso::getInstance()->setConfigPair('KANSO_AUTHOR_SLUGS', array_unique(array_values($slugs)));
    }

    /**
     * Remove a slug from Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be removed
     */
    private function removeAuthorSlug($slug)
    {
        # Get the config
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        foreach ($slugs as $key => $value) {
            if ($slug === $value) unset($slugs[$key]);
        }

        \Kanso\Kanso::getInstance()->setConfigPair('KANSO_AUTHOR_SLUGS', array_values($slugs));
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
    public function logClientIn($clientEntry) 
    {
    	# Get the session
    	$session = \Kanso\Kanso::getInstance()->Session;

        # Create a fresh session
        $session->clear();

        # Save a new ajax keys to the databse
        $keys = Helper\Token::generate();
        $keys = [
            'kanso_public_key'  => $keys['key'],
            'kanso_public_salt' => $keys['salt'],
            'kanso_keys_time'   => time(),
        ];
        
        \Kanso\Kanso::getInstance()->Database()->Builder()->UPDATE('users')->SET($keys)->WHERE('id', '=', $clientEntry['id'])->QUERY();

        $clientEntry = array_merge($clientEntry, $keys);

        # Append login credentials to the session
        $session->putMultiple([
            'sessionLastActive' => time(),
            'sessionIsLoggedIn' => true,
            'KANSO_ADMIN_DATA'  => $clientEntry,
        ]);
        $session->putMultiple($keys);

        # Fire the event
        \Kanso\Events::fire('login', $clientEntry);

    }

    /**
     * Log client out
     *
     * This is responsible for logging a client out of the 
     * admin panel.
     *
     */
    private function logClientOut() 
    {
        # Fire the event
        \Kanso\Events::fire('logout', [$this->user->getRow()]);

        # Clear the session
        \Kanso\Kanso::getInstance()->Session->clear();
    }


}