<?php

namespace Kanso\Admin\Models;

/**
 * GET/POST Model for the settings pages
 *
 * This model is responsible for validating and parsing all
 * GET and POST requests made to the admin panel settings page.
 *
 * The class is instantiated by the respective controller
 */
class Settings
{

    /**
     * @var string
     */
    private $tab;

    /**
     * @var \Kanso\Utility\GUMP
     */
    private $validation;

    /**
     * @var \Kanso\Kanso::getInstance()->Database()->Builder()
     */
    private $SQL;

    /********************************************************************************
    * PUBLIC INITIALIZATION
    *******************************************************************************/

    /**
     * Constructor
     *
     * @param string    $tab    The current tab to load
     */
    public function __construct($tab = 'account')
    {
        $this->tab        = $tab;
        $this->validation = \Kanso\Kanso::getInstance()->Validation;
        $this->SQL        = \Kanso\Kanso::getInstance()->Database()->Builder();
    }

    /**
     * Parse the $_GET request variables and filter the categories for the requested page.
     *
     * This method parses the requested URL and serves the variables for the view
     * e.g /admin/settings/tools/
     * 
     * @return array
     */
	public function parseGet()
	{
        $env     = \Kanso\Kanso::getInstance()->Environment;
        $config  = \Kanso\Kanso::getInstance()->Config;
        $_themes = array_filter(glob($env['KANSO_THEMES_DIR'].'/*'), 'is_dir');
        $themes  = [];
        foreach ($_themes as $i => $_theme) {
            $themes[] = substr($_theme, strrpos($_theme, '/') + 1);
        }

        $thumnails = '';
        foreach ($config['KANSO_THUMBNAILS'] as $i => $size) {
            if (is_array($size)) {
                $thumnails .= $size[0].' '.$size[1].', ';
            }
            else {
                $thumnails .= $size.', ';
            }
        }
        $thumnails = rtrim($thumnails, ', ');

        # Get all the authors
        $allAuthors = $this->SQL->SELECT('*')->FROM('users')
        ->WHERE('role', '=', 'administrator')
        ->OR_WHERE('role', '=', 'writer')
        ->FIND_ALL();

        return [
            'active_tab'  => $this->tab,
            'themes'      => $themes,
            'thumbnails'  => $thumnails,
            'all_authors' => $allAuthors,
        ];
    }

    /**
     * Parse and validate the $_POST request variables from any submitted forms
     * 
     * @return array|false
     */
    public function parsePost()
    {
        # Get the POST variables
        $postVars = \Kanso\Kanso::getInstance()->Request->fetch();

        if (isset($postVars['form_name'])) {
            if ($postVars['form_name'] === 'account_settings') {
                return $this->accountSettings($postVars);
            }
            else if ($postVars['form_name'] === 'author_settings') {
                return $this->authorSettings($postVars);
            }
            else if ($postVars['form_name'] === 'kanso_settings') {
                return $this->kansoSettings($postVars);
            }
            else if ($postVars['form_name'] === 'restore_kanso') {
                return $this->restoreKanso();
            }
            else if ($postVars['form_name'] === 'invite_user') {
                return $this->inviteUser($postVars);
            }
            else if ($postVars['form_name'] === 'change_user_role') {
                return $this->changeUserRole($postVars);
            }
             else if ($postVars['form_name'] === 'delete_user') {
                return $this->deleteUser($postVars);
            }
        }

        return false;
    }

    /********************************************************************************
    * POST MESSAGE RESPONSE
    *******************************************************************************/

    private function response($msg, $type)
    {   
        $icon = '';
        if ($type === 'info')    $icon = 'info-circle';
        if ($type === 'success') $icon = 'check';
        if ($type === 'warning') $icon = 'exclamation-triangle';
        if ($type === 'danger')  $icon = 'times';

        return [
            'class' => $type,
            'icon'  => $icon,
            'msg'   => $msg,
        ];
    }

    /********************************************************************************
    * POST VALIDATION
    *******************************************************************************/

    /**
     * Parse and validate the account settings POST
     * 
     * @param  $postVars    $_POST
     * @return array|false
     */
    private function accountSettings($postVars)
    {

        $postVars = $this->validation->sanitize($postVars);

        $this->validation->validation_rules([
            'username'            => 'required|alpha_dash|max_len,100|min_len,4',
            'email'               => 'required|valid_email',
            'password'            => 'max_len,100|min_len,6',
            'email_notifications' => 'boolean',
        ]);

        $this->validation->filter_rules([
            'username' => 'trim|sanitize_string',
            'email'    => 'trim|sanitize_email',
            'password' => 'trim',
            'email_notifications' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($postVars);

        if (!$validated_data) return false;

        $username = $validated_data['username'];
        $email    = $validated_data['email'];
        $password = $validated_data['password'];
        $emailNotifications = isset($validated_data['email_notifications']) ? true : false;

        # Grab the user's object
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();

        # Validate that the username/ email doesn't exist already
        # only if the user has changed either value
        if ($email !== $user->email) {
            $emailExists = $this->SQL->SELECT('*')->FROM('users')->WHERE('email', '=', $email)->FIND();
            if ($emailExists) return $this->response('Another user already exists with that email. Please try another email address.', 'warning');
        }
        if ($username !== $user->username) {
            $usernameExists = $this->SQL->SELECT('*')->FROM('users')->WHERE('username', '=', $username)->FIND();
            if ($usernameExists) return $this->response('Another user already exists with that username. Please try another username.', 'warning');
        }

        # Update the user
        $user->username = $username;
        $user->email    = $email;
        $user->email_notifications = $emailNotifications;

        # If they changed their password lets update it
        if ($password !== '' && !empty($password)) $user->password = utf8_encode(\Kanso\Security\Encrypt::hash($password));

        $user->save();
      
        return $this->response('Your account settings were successfully updated!', 'success');

    }

    /**
     * Parse and validate the account settings from the POST request
     * 
     * @param  $postVars    $_POST
     * @return array|false
     */
    private function authorSettings($postVars)
    {

        # Sanitize and validate the POST
        $postVars = $this->validation->sanitize($postVars);

        $this->validation->validation_rules([
            'name'         => 'required|alpha_space|max_len,50|min_len,3',
            'slug'         => 'required|alpha_dash|max_len,50|min_len,3',
            'description'  => 'max_len,255',
            'facebook'     => 'valid_url',
            'twitter'      => 'valid_url',
            'gplus'        => 'valid_url',
            'instagram'    => 'valid_url',
        ]);

        $this->validation->filter_rules([
            'name'         => 'trim|sanitize_string',
            'slug'         => 'trim|sanitize_string',
            'description'  => 'trim|sanitize_string',
            'facebook'     => 'trim|sanitize_string',
            'twitter'      => 'trim|sanitize_string',
            'gplus'        => 'trim|sanitize_string',
            'instagram'    => 'trim|sanitize_string',
            'thumbnail_id' => 'trim|sanitize_numbers',
        ]);

        # Validate POST
        $validated_data = $this->validation->run($postVars);
        if (!$validated_data) return false;

        # Grab the Row and update settings
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();

        # Change authors details
        $user->name         = $validated_data['name'];
        $user->slug         = $validated_data['slug'];
        $user->facebook     = $validated_data['facebook'];
        $user->twitter      = $validated_data['twitter'];
        $user->gplus        = $validated_data['gplus'];
        $user->instagram    = $validated_data['instagram'];
        $user->description  = $validated_data['description'];
        $user->thumbnail_id = empty($validated_data['thumbnail_id']) ? NULL : intval($validated_data['thumbnail_id']);
        $user->save();

        return $this->response('Your author information was successfully updated!', 'success');
    }

    /**
     * Parse and validate the Kanso settings from the POST request
     * 
     * @param  $postVars    $_POST
     * @return array|false
     */
    private function kansoSettings($postVars)
    {
        # Validate the user is an admin
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();
        if ($user->role !== 'administrator') return false;

        # Validate post variables
        $postVars = $this->validation->sanitize($postVars);

        $this->validation->validation_rules([
            'enable_authors '   => 'boolean',
            'enable_cats'       => 'boolean',
            'enable_tags'       => 'boolean',
            'enable_cdn'        => 'boolean',
            'enable_cache'      => 'boolean',
            'enable_comments'   => 'boolean',
            'posts_per_page'    => 'required|integer',
            'thumbnail_quality' => 'required|integer',
            'cdn_url'           => 'max_len,100',
            'cache_life'        => 'max_len,50',
            'site_title'        => 'required|max_len,100',
            'site_description'  => 'required|max_len,300',
            'sitemap_url'       => 'required|max_len,100',
            'theme'             => 'required|max_len,100',
            'thumbnail_sizes'   => 'required|max_len,100',
            'permalinks'        => 'required|max_len,50',
        ]);

        $this->validation->filter_rules([
            'posts_per_page'    => 'sanitize_numbers',
            'thumbnail_quality' => 'sanitize_numbers',
            'cdn_url'           => 'trim|sanitize_string|basic_tags',
            'cache_life'        => 'trim|sanitize_string|basic_tags',
            'site_title'        => 'trim|sanitize_string|basic_tags',
            'site_description'  => 'trim|sanitize_string|basic_tags',
            'sitemap_url'       => 'trim|sanitize_string|basic_tags',
            'theme'             => 'trim|sanitize_string|basic_tags',
            'thumbnail_sizes'   => 'trim|sanitize_string|basic_tags',
            'permalinks'        => 'trim|sanitize_string|basic_tags',
        ]);

        if (isset($postVars['clear_cache'])) {
            $cleared = \Kanso\Kanso::getInstance()->Cache->clear();
            if ($cleared)  return $this->response('Kanso\'s cache was successfully cleared !', 'success');
            return $this->response('There was an error clearing the cache.', 'danger');
        }

        $validated_data = $this->validation->run($postVars);

        if (!isset($validated_data['enable_authors']))  $validated_data['enable_authors']  = false;
        if (!isset($validated_data['enable_cats']))     $validated_data['enable_cats']     = false;
        if (!isset($validated_data['enable_tags']))     $validated_data['enable_tags']     = false;
        if (!isset($validated_data['enable_cdn']))      $validated_data['enable_cdn']      = false;
        if (!isset($validated_data['enable_cache']))    $validated_data['enable_cache']    = false;
        if (!isset($validated_data['enable_comments'])) $validated_data['enable_comments'] = false;

        if ($validated_data) {

            $config = [
                "KANSO_THEME_NAME"       => $validated_data['theme'],
                "KANSO_SITE_TITLE"       => $validated_data['site_title'],
                "KANSO_SITE_DESCRIPTION" => $validated_data['site_description'], 
                "KANSO_SITEMAP"          => $validated_data['sitemap_url'],
                "KANSO_PERMALINKS"       => $validated_data['permalinks'],
                "KANSO_POSTS_PER_PAGE"   => $validated_data['posts_per_page'] < 1 ? 10 : intval($validated_data['posts_per_page']),
                "KANSO_ROUTE_TAGS"       => \Kanso\Utility\Str::bool($validated_data['enable_tags']),
                "KANSO_ROUTE_CATEGORIES" => \Kanso\Utility\Str::bool($validated_data['enable_cats']),
                "KANSO_ROUTE_AUTHORS"    => \Kanso\Utility\Str::bool($validated_data['enable_authors']),
                "KANSO_THUMBNAILS"       => $validated_data['thumbnail_sizes'],
                "KANSO_IMG_QUALITY"      => intval($validated_data['thumbnail_quality']),
                "KANSO_USE_CDN"          => \Kanso\Utility\Str::bool($validated_data['enable_cdn']),
                "KASNO_CDN_URL"          => $validated_data['cdn_url'],
                "KANSO_USE_CACHE"        => \Kanso\Utility\Str::bool($validated_data['enable_cache']),
                "KANSO_CACHE_LIFE"       => $validated_data['cache_life'],
                "KANSO_COMMENTS_OPEN"    => \Kanso\Utility\Str::bool($validated_data['enable_comments']),
            ];

            $Settings = \Kanso\Kanso::getInstance()->Settings;
            foreach ($config as $key => $value) {
                $Settings->$key = $value;
            }
            $update = $Settings->save(true);

            if ($update === true) {
                return $this->response('Kanso settings were successfully updated!', 'success');
            }
            else if ($update === $Settings::INVALID_THEME) {
                return $this->response('The theme is not a valid Kanso theme.', 'warning');
            }
            else if ($update === $Settings::INVALID_PERMALINKS) {
                return $this->response('The permalinks value you entered is invalid. Please ensure you enter a valid permalink structure - e.g. "year/month/postname/".', 'warning');
            }
            else if ($update === $Settings::INVALID_IMG_QUALITY) {
                return $this->response('The image quality value you entered is invalid. Please enter a number between 0 and 100.', 'warning');
            }
            else if ($update === $Settings::INVALID_CDN_URL) {
                return $this->response('The CDN url you entered is invalid. Please provide a valid URL.', 'warning');
            }
            else if ($update === $Settings::INVALID_CACHE_LIFE) {
                return $this->response('The cache life value you entered is invalid. Please ensure you enter a cache lifetime - e.g. "1 month" or "3 days".', 'warning');
            }
            else if ($update === $Settings::INVALID_THUMBNAILS) {
                return $this->response('The thumbnail sizes you entered are invalid.', 'warning');
            }
            else if ($update === $Settings::INVALID_POSTS_PER_PAGE) {
                return $this->response('The posts per page you entered is invalid. You need to enter a number', 'warning');
            }
            else if ($update === $Settings::UNKNOWN_ERROR) {
                return $this->response('There was an unknown error. Please log an issue on GitHub.', 'warning');
            }
        }

        return false;
    }

    /**
     * Parse and validate restore kanso
     * 
     * @return array|false
     */
    private function restoreKanso()
    {
        # Validate the user is an admin
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();
        if ($user->role !== 'administrator') return false;

        # Login page
        $loginPage = \Kanso\Kanso::getInstance()->Environment['KANSO_ADMIN_URL'].'/login/';

        # Reinstall from defaults
        $installer = new \Kanso\Install\Installer();
        
        if ($installer->installKanso(true)) {

            \Kanso\Kanso::getInstance()->Cookie->clear();
            \Kanso\Kanso::getInstance()->Session->clear();
            \Kanso\Kanso::getInstance()->redirect($loginPage);
        }
    }

    /**
     * Parse and validate user invite
     * 
     * @return array|false
     */
    private function inviteUser($postVars)
    {
        # Validate the user is an admin
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();
        if ($user->role !== 'administrator') return false;

        $postVars = $this->validation->sanitize($postVars);

        $this->validation->validation_rules([
            'email' => 'required|valid_email',
            'role'  => 'required|contains, administrator writer',
        ]);

        $this->validation->filter_rules([
            'email' => 'trim|sanitize_email',
            'role'  => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($postVars);

        if (!$validated_data) return false;

        # User's cant invite themselves
        if ($validated_data['email'] === $user->email) return $this->response('Another user is already registered with that email address.', 'warning');

        # Get the user's row if it exists
        $userRow = $this->SQL->SELECT('*')->FROM('users')->WHERE('email', '=', $validated_data['email'])->ROW();

        # Validate they are not already confirmed
        if ($userRow && $userRow['status'] === 'confirmed') return $this->response('Another user is already registered with that email address.', 'warning');

        # If theyre deleted or pending re-invite them
        if (!$userRow || ($userRow && $userRow['status'] !== 'confirmed')) {
            
            \Kanso\Kanso::getInstance()->Gatekeeper->registerAdmin($validated_data['email'], $validated_data['role']);

            return $this->response('The user was successfully sent a registration invite.', 'success');
        }
    }

    /**
     * Parse and validate user role change
     * 
     * @return array|false
     */
    private function changeUserRole($postVars)
    {
        # Validate the user is an admin
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();
        if ($user->role !== 'administrator') return false;

        $postVars = $this->validation->sanitize($postVars);

        $this->validation->validation_rules([
            'user_id' => 'required|numeric',
            'role'    => 'required|contains, administrator writer',
        ]);

        $this->validation->filter_rules([
            'user_id' => 'trim|sanitize_numbers',
            'role'    => 'trim|sanitize_string',
        ]);

        $validated_data = $this->validation->run($postVars);

        if (!$validated_data) return false;

        # The user cannot change their own role
        if (intval($validated_data['user_id']) === intval($user->id)) return false;

        # You cannot change user id 1's role
        if (intval($validated_data['user_id']) === 1) return false;

        # Validate the user actually exists
        $userExists = $this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', intval($validated_data['user_id']))->FIND();
        if (empty($userExists)) return false;
        
        # Change their role
        \Kanso\Kanso::getInstance()->Gatekeeper->changeUserRole(intval($validated_data['user_id']), $validated_data['role']);

        return $this->response('The user\'s role was successfully updated.', 'success');

    }

    /**
     * Parse and validate delete user
     * 
     * @return array|false
     */
    private function deleteUser($postVars)
    {
        # Validate the user is an admin
        $user = \Kanso\Kanso::getInstance()->Gatekeeper->getUser();
        if ($user->role !== 'administrator') return false;

        $postVars = $this->validation->sanitize($postVars);

        $this->validation->validation_rules([
            'user_id' => 'required|numeric',
        ]);

        $this->validation->filter_rules([
            'user_id' => 'trim|sanitize_numbers',
        ]);

        $validated_data = $this->validation->run($postVars);

        if (!$validated_data) return false;

        # The user cannot change their own role
        if ((int)$validated_data['user_id'] === (int)$user->id) return false;

        # Nooe can change user id 1's role
        if ((int)$validated_data['user_id'] === 1) return false;

        # Validate the user actually exists
        $userExists = $this->SQL->SELECT('*')->FROM('users')->WHERE('id', '=', (int)$validated_data['user_id'])->FIND();
        if (empty($userExists)) return false;
        
        # Change their role
        \Kanso\Kanso::getInstance()->Gatekeeper->deleteUser($validated_data['user_id']);

        return $this->response('The user was successfully deleted.', 'success');
    }

    /********************************************************************************
    * PRIVATE HELPERS
    *******************************************************************************/

    /**
     * Is string a valid json
     *
     * @param  string    $string
     * @return bool
     */
    private function isJson($string) {

        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);

    }
}