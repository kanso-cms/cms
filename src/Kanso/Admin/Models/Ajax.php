<?php

namespace Kanso\Admin\Models;

/**
 * Admin User Manager
 *
 * This class has as a number of utility helper functions
 * for managing users from within the admin panel.
 *
 */
class Ajax
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

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
    public function updateAccountDetails($username, $email, $password, $emailNotifications = true) 
    {
        # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Grab the user's row from the session
        $sessionRow = \Kanso\Kanso::getInstance()->Session->get('KANSO_ADMIN_DATA');

        # Validate that the username/ email doesn't exist already
        # only if the user has changed either value
        if ($email !== $sessionRow['email']) {
            $emailExists = $Query->SELECT('*')->FROM('users')->WHERE('email', '=', $email)->FIND();
            if ($emailExists) return 'email_exists';
        }
        if ($username !== $sessionRow['username']) {
            $usernameExists = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->FIND();
            if ($usernameExists) return 'username_exists';
        }

        # Grab the user's row from the database
        $userRow = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $sessionRow['username'])->AND_WHERE('email', '=', $sessionRow['email'])->AND_WHERE('status', '=', 'confirmed')->FIND();
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
        if ($password !== '' && !empty($password)) $row['hashed_pass'] = utf8_encode(\Kanso\Security\Encrypt::hash($password));

        # Save to the databse and refresh the client's session
        $update = $Query->UPDATE('users')->SET($row)->WHERE('id', '=', $userRow['id'])->QUERY();

        # If updated
        if ($update) {

            # Relog the client in
            \Kanso\Kanso::getInstance()->Session->refresh();

            return "valid";
        }

        return false;
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
    public function updateAuthorDetails($name, $slug, $bio, $facebook, $twitter, $google) 
    {

        # Get a new Query Builder
        $Query = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Grab the user's row from the session
        $sessionRow = \Kanso\Kanso::getInstance()->Session->get('KANSO_ADMIN_DATA');

        # Grab the Row and update settings
        $userRow   = $Query->SELECT('*')->FROM('users')->WHERE('username', '=', $sessionRow['username'])->FIND();
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
        $update = $Query->UPDATE('users')->SET($userRow)->WHERE('id', '=', $userRow['id'])->QUERY();
        
        # Id updated
        if ($update) {

            # Relog the client in
            \Kanso\Kanso::getInstance()->Session->refresh();

            # Remove the old slug
            $this->removeAuthorSlug($oldSlug);

            # Add the new one
            $this->addAuthorSlug($slug);

            return 'valid';
        }

        return false;
    }

    /**
     * Add a slug to Kanso's author pages configuration (used internally)
     *
     * @param  string    $slug    The slug to be added
     */
    private function addAuthorSlug($slug)
    {
        # Get the slugs
        $slugs = \Kanso\Kanso::getInstance()->Config['KANSO_AUTHOR_SLUGS'];

        # Add the slug
        $slugs[] = $slug;

        \Kanso\Kanso::getInstance()->Settings->put('KANSO_AUTHOR_SLUGS', array_unique(array_values($slugs)));
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

        # Remove the slug
        foreach ($slugs as $i => $configSlug) {
            if ($configSlug === $slug) unset($slugs[$i]);
        }

        \Kanso\Kanso::getInstance()->Settings->put('KANSO_AUTHOR_SLUGS', array_values($slugs));
    }

}