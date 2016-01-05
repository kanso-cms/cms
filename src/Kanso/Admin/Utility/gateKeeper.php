<?php

namespace Kanso\Admin\Utility;

/**
 * Admin GateKeeper
 *
 * This class serves as the main point of security and validation
 * between the client and the admin panel.
 *
 */
class gateKeeper
{

    /**
     * @var \Kanso\Kanso
     */
    protected static $Kanso;

    /**
     * Login credentials validation
     *
     * When a POST request is sent for a login request, this function
     * looks up the database, encrypts the password sent from client
     * and compares it to the db version. The password is never
     * decrytped
     *
     * @param  string    $username
     * @param  string    $password
     * @return bool|array
     */
    public static function validateLoginCredentials($username, $password) 
    {

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get the user's row 
        $userRow = self::$Kanso->Database()->Builder()->SELECT('*')->FROM('authors')->WHERE('username', '=', $username)->AND_WHERE('status', '=', 'confirmed')->FIND();

        # Validate the user exists
        if (!$userRow || empty($userRow)) return false;

        # Save the hashed password
        $hashedPass   = utf8_decode($userRow['hashed_pass']);
        
        # Compare the hashed password to the provided password
        if (\Kanso\Security\Encrypt::verify($password, $hashedPass)) return $userRow;

        return false;
    }

    /**
     * Forgot passowrd validation
     *
     * When a POST request is sent for a forgot password, this function
     * looks up the database, finds the user based on the username
     * and generates a random key so they can reset their password.
     * The link is then emailed to the client.
     *
     * @param  string    $username
     * @return bool 
     */
    public static function validateForgotPassword($username) 
    {

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get the user's row 
        $userRow = self::$Kanso->Database()->Builder()->SELECT('*')->FROM('authors')->WHERE('username', '=', $username)->FIND();

        # Validate the user exists
        if (!$userRow || empty($userRow)) return false;

        # Create and save a new reset-password key 
        $newKey   = \Kanso\Utility\Str::generateRandom(85, true);
        $savedRow = self::$Kanso->Database()->Builder()->UPDATE('authors')->SET(['kanso_password_key' => $newKey])->WHERE('username', '=', $username)->QUERY();

        if ($savedRow) {
            
            # Create array of data for email template
            $website   = self::$Kanso->Environment['KANSO_WEBSITE_NAME'];
            $emailData = [
                'name'    => $savedRow['name'],
                'website' => $website,
                'key'     => $newKey,
            ];

            # Get the email template
            $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailForgotPassword');

            # Send email
            return  \Kanso\Utility\Mailer::sendHTMLEmail($userRow['email'], $website, 'no-reply@'.$website, 'A reset password request has been made', $msg);
        }

        return false;
    }

    /**
     * Forgot username validation
     *
     * When a POST request is sent for a forgot username, this function
     * looks up the database, finds the user based on the email address
     * and emails the client their username
     *
     * @param  string    $email
     * @return bool 
     */
    public static function validateForgotUsername($email) 
    {

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get the user's row 
        $userRow = self::$Kanso->Database()->Builder()->SELECT('*')->FROM('authors')->WHERE('email', '=', $email)->FIND();

        # Validate the user exists
        if (!$userRow || empty($userRow)) return false;

        # Create array of data for email template
        $website   = self::$Kanso->Environment['KANSO_WEBSITE_NAME'];
        $emailData = [
            'name'     => $userRow['name'],
            'username' => $userRow['username'],
            'website'  => $website,
        ];

        # Get the email template
        $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailForgotUsername');
        
        # Send the email
        return  \Kanso\Utility\Mailer::sendHTMLEmail($userRow->get('email'), $website, 'no-reply@'.$website, 'A username reminder has been requested', $msg);
    
    }

    /**
     * Register validator
     *
     * When a POST request is sent for registration, this function
     * looks up the database, finds the user based on the key provided from
     * the GET request, and tries to create a new user.
     *
     * @param  string    $username
     * @param  string    $email
     * @param  string    $password
     * @param  string    $key
     * @return bool 
     */
    public static function validateRegister($username, $email, $password, $key) 
    {

        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get the user's row 
        $userRow = self::$Kanso->Database()->Builder()->SELECT('*')->FROM('authors')->WHERE('kanso_register_key', '=', $key)->AND_WHERE('status', '=', 'pending')->FIND();

        # Validate the user exists
        if (!$userRow || empty($userRow)) return false;

        # Validate the entered email address is same as the one that was invited
        if ($userRow['email'] !== $email) return 'The email you entered is not the email you were invited from.';

        # Validate another user with the same username doeesn't already exist
        $userExists = self::$Kanso->Database()->Builder()->SELECT('*')->FROM('authors')->WHERE('username', '=', $username)->AND_WHERE('status', '=', 'confirmed')->FIND();
        if ($userExists || !empty($userExists)) return 'Another user is already using that username.';

        # Create the new user
        $slug                  = \Kanso\Utility\Str::slugFilter($username);
        $userRow['username']     = $username;
        $userRow['email']        = $email;
        $userRow['hashed_pass']  = utf8_encode(\Kanso\Security\Encrypt::encrypt($password));
        $userRow['slug']         = $slug;
        $userRow['status']       = 'confirmed';
        $userRow['kanso_register_key']  = '';
        $userRow = self::$Kanso->Database()->Builder()->UPDATE('authors')->SET($userRow)->WHERE('id', '=', $userRow['id'])->QUERY();
        
        if ($userRow) {

            # Log the client in
            \Kanso\Admin\Security\sessionManager::logClientIn($userRow);
            
            # Add the author's slug into Kanso's config
            \Kanso\Admin\Utility\userManager::addAuthorSlug($slug);

            # Create array of data for email template
            $website   = self::$Kanso->Environment['KANSO_WEBSITE_NAME'];
            $emailData = [
                'name'     => $userRow['name'],
                'username' => $userRow['username'],
                'website'  => $website,
            ];

            # Get the email template
            $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailConfirmNewUser');
            
            # Send the email
            \Kanso\Utility\Mailer::sendHTMLEmail($userRow['name'], $website, 'no-reply@'.$website, 'Welcome to '.$website, $msg);

            return 'valid';
        }

        return false;

    }

    /**
     * Reset password validator
     *
     * When a POST request is sent for reset password, this function
     * looks up the database, finds the user based on the key provided from
     * the GET request, and tries to reset the user's password.
     * It then sends them an email telling them their password was reset.
     *
     * @param  string    $password
     * @param  string    $key
     * @return bool 
     */
    public static function validateResetPassword($password, $key)
    {


        # Get a Kanso instance
        if (!self::$Kanso) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get the user's row 
        $userRow = self::$Kanso->Database()->Builder()->SELECT('*')->FROM('authors')->WHERE('kanso_password_key', '=', $key)->FIND();

        # Validate the user exists
        if (!$userRow || empty($userRow)) return false;

        # Change the users password and remove the key from the database 
        $userRow['hashed_pass']        = utf8_encode(\Kanso\Security\Encrypt::encrypt($password));
        $userRow['kanso_password_key'] = null;
        $userRow = $userRow->save();

        # Remove the password key from the session
        \Kanso\Admin\Security\sessionManager::delete('KANSO_PASSWORD_KEY');

        # Reset the user's session
        \Kanso\Admin\Security\sessionManager::freshSession();

        # Create array of data for email template
        $website   = self::$Kanso->Environment['KANSO_WEBSITE_NAME'];
        $emailData = [
                'name'     => $userRow['name'],
                'username' => $userRow['username'],
                'website'  => $website,
        ];

        # Get the email template
        $msg = \Kanso\Templates\Templater::getTemplate($emailData, 'EmailResetPassword');

        # Send the email
        \Kanso\Utility\Mailer::sendHTMLEmail($userRow['email'], $website, 'no-reply@'.$website, 'Your password was reset at '.$website, $msg);

        return 'valid';

    }

}