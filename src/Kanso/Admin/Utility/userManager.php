<?php

namespace Kanso\Admin\Utility;

/**
 * Admin User Manager
 *
 * This class has as a number of utility helper functions
 * for managing users from within the admin panel.
 *
 */
class userManager
{

    /**
     * @var \Kanso\Kanso
     */
    protected static $Kanso;

    /**
     * Update administrator settings
     *
     * This function updates the user's administrator settings.
     * i.e username, email and password.
     *
     * @param  $username       string
     * @param  $email          string
     * @param  $password       string
     * @return string|boolean
     */
    public static function updateAccountDetails($username, $email, $password, $emailNotifications = true) 
    {

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new CRUD
        $CRUD = self::$Kanso->CRUD();

        # Grab the user's row from the session
        $sessionRow = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');

        # Validate that the username/ email doesn't exist already
        # only if the user has changed either value
        if ($email !== $sessionRow['email']) {
            $emailExists = $CRUD->SELECT('*')->FROM('authors')->WHERE('email', '=', $email)->FIND();
            if ($emailExists) return 'email_exists';
        }
        if ($username !== $sessionRow['username']) {
            $usernameExists = $CRUD->SELECT('*')->FROM('authors')->WHERE('username', '=', $username)->FIND();
            if ($usernameExists) return 'username_exists';
        }

        # Grab the user's row from the database
        $userRow = $CRUD->SELECT('*')->FROM('authors')->WHERE('username', '=', $sessionRow['username'])->AND_WHERE('email', '=', $sessionRow['email'])->AND_WHERE('status', '=', 'confirmed')->FIND();
        if (!$userRow || empty($userRow)) return false;

        # Sanitize email notifications
        if ($emailNotifications === 'true' || $emailNotifications === 1 || $emailNotifications === true) {
            $emailNotifications = true;
        }
        else {
            $emailNotifications = false;
        }

        # Update the username and email
        $row = [
            'username' => $username,
            'email'    => $email,
            'email_notifications' => $emailNotifications,
        ];

        # If they changed their password lets update it
        if ($password !== '' && !empty($password)) $row['hashed_pass'] = utf8_encode(\Kanso\Security\Encrypt::encrypt($password));

        # Save to the databse and refresh the client's session
        $CRUD->UPDATE('authors')->SET($row)->WHERE('id', '=', $userRow['id'])->QUERY();
        \Kanso\Admin\Security\sessionManager::logClientIn(array_merge($userRow, $row));
        return "valid";

    }

    /**
     * Update Author details
     *
     * @param  $name        string
     * @param  $slug        string
     * @param  $facebook    string
     * @param  $twitter     string
     * @param  $google      string
     * @return string|boolean
     */
    public static function updateAuthorDetails($name, $slug, $bio, $facebook, $twitter, $google) 
    {

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new CRUD
        $CRUD = self::$Kanso->CRUD();

        # Grab the user's row from the session
        $sessionRow = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');

        # Grab the Row and update settings
        $userRow   = $CRUD->SELECT('*')->FROM('authors')->WHERE('username', '=', $sessionRow['username'])->FIND();
        if (!$userRow) return false;

        # Change authors details
        $oldSlug  = $userRow['slug'];
        $userRow['name']        = $name;
        $userRow['slug']        = $slug;
        $userRow['facebook']    = $facebook;
        $userRow['twitter']     = $twitter;
        $userRow['gplus']       = $google;
        $userRow['description'] = $bio;

        # Save to the databse and refresh the client's session
        $CRUD->UPDATE('authors')->SET($userRow)->WHERE('id', '=', $userRow['id'])->QUERY();
        
        \Kanso\Admin\Security\sessionManager::logClientIn($userRow);
        
        self::removeAuthorSlug($oldSlug);

        self::addAuthorSlug($slug);

        return 'valid';

    }

    /**
     * Ivite a new user
     *
     * This function invites a new user to the Kanso installation
     *
     * @param  $email          string    The email of the invited user
     * @param  $role           string    The role of the invited user
     * @param  $invitee        array     The user's database entry who is inviting someone to join
     * @return string|bool
     */
    public static function inviteNewUser($email, $role, $invitee = null) 
    {
        
        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new CRUD
        $CRUD = self::$Kanso->CRUD();

        # If the invitee was not provided get it from the session
        # Validate the user is an admin
        if (!$invitee) $invitee = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($invitee['role'] !== 'administrator') return false;

        # A user can't invite themself
        if ($invitee['email'] === $email) return "already_member";
            
        # A user can't invite someone who is already confirmed member
        $userRow = $CRUD->SELECT('*')->FROM('authors')->WHERE('email', '=', $email)->FIND();
        if ($userRow && $userRow['status'] === 'confirmed') return "already_member";

        # Generate a random string for the register link
       
        $registerKey = \Kanso\Utility\Str::generateRandom(85, true);

        # If the user is not already invited add them to the database
        if (!$userRow) {
            $newUser = [
                'email'              => $email,
                'kanso_register_key' => $registerKey,
                'role'               => $role,
                'status'             => 'pending',
            ];
            $CRUD->INSERT_INTO('authors')->VALUES($newUser)->QUERY();
        }
        # If the user is already invited, change their role and update with a new register key
        else {
            $values = [];
            $values['kanso_register_key'] = $registerKey;
            $values['role']               = $role;
            $values['status']             = 'pending';
            $CRUD->UPDATE('authors')->SET($values)->WHERE('id', '=', $userRow['id'])->QUERY();
        }

        # Create array of data for email template
        $website   = self::$Kanso->Environment['KANSO_WEBSITE_NAME'];
        $emailData = [
            'name'    => $invitee['name'],
            'website' => $website,
            'key'     => $registerKey,
            'link'    => self::$Kanso->Environment['KANSO_ADMIN_URI'].'/register?'.$registerKey,
        ];

        # Get the email template
        $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailInviteNewUser');

        # Send email
        \Kanso\Utility\Mailer::sendHTMLEmail($email, $invitee['name'], 'no-reply@'.$website, 'Authorship Invite to '.$website, $msg);

        return 'valid';
    
    }

    /**
     * Delete a user
     *
     * This function removes a user from the database.
     * Note that the user's status is changed to 'deleted' so that their
     * authorship details are still available
     *
     * @param  $id             int       The id of the user to remove
     * @param  $currentUser    array     The user's database entry doing the current operation
     * @return string|bool
     */
    public static function deleteUser($id, $currentUser = null) 
    {

        # Make sure the id is an int
        $id = (int)$id;

        # Noone can delete the first admin
        if ($id === 1) return false;

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new CRUD
        $CRUD = self::$Kanso->CRUD();

        # If the user was not provided get it from the session
        # Validate the user is an admin
        if (!$currentUser) $currentUser = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($currentUser['role'] !== 'administrator') return false;

        # A user can't delete themself
        if ($id === $currentUser['id']) return false;

        # Validate the user exists
        $userRow = $CRUD->SELECT('*')->FROM('authors')->where('id', '=', $id)->FIND();
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

        $CRUD->UPDATE('authors')->SET($userRow)->WHERE('id', '=', $userRow['id'])->QUERY();

        self::removeAuthorSlug($slug);
            
        return 'valid';
        
    }

    /**
     * Change a user's role
     *
     * @param  $id             int       The id of the user to change
     * @param  $role           string    The role to change to
     * @param  $currentUser    array     The user's database entry doing the current operation
     * @return string|bool
     */
    public static function changeUserRole($id, $role, $currentUser = null) 
    {

        # Make sure the id is an int
        $id = (int)$id;

        # Noone can change the first admin
        if ($id === 1) return false;

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new CRUD
        $CRUD = self::$Kanso->CRUD();

        # If the operator was not provided get it from the session
        # Validate the user is an admin
        if (!$currentUser) $currentUser = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($currentUser['role'] !== 'administrator') return false;

        # A user can't change their own role
        if ($id === $currentUser['id']) return false;

        # Validate the user exists
        $userRow = $CRUD->SELECT('*')->FROM('authors')->WHERE('id', '=', $id)->FIND();
        if (!$userRow) return false;

        # Change the authors role
        $CRUD->UPDATE('authors')->SET(['role' => $role])->WHERE('id', '=', $userRow['id'])->QUERY();
        return 'valid';
        
    }

    /**
     * Remove a slug from Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be removed
     */
    public static function removeAuthorSlug($slug)
    {
        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        $slugs = self::$Kanso->Config['KANSO_AUTHOR_SLUGS'];

        if(($key = array_search($slug, $slugs)) !== false) {
            unset($slugs[$key]);
        }

        self::$Kanso->setConfigPair('KANSO_AUTHOR_SLUGS', array_values($slugs));

    }

    /**
     * Add a slug to Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be added
     */
    public static function addAuthorSlug($slug)
    {

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        $slugs = self::$Kanso->Config['KANSO_AUTHOR_SLUGS'];

        $slugs[count($slugs)] = $slug;

        self::$Kanso->setConfigPair('KANSO_AUTHOR_SLUGS', array_values($slugs));

    }
    
}