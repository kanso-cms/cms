<?php

namespace Kanso\Admin\Dispatch;

/**
 * GET Dispatcher
 *
 * The POST Admin Dispatcher servers as a controller for all POST requests to
 * Kanso's Admin panel. It is initialized directly from Kanso's router,
 * with a variable indicating what kind of request was made. The router
 * will then call the validation method.
 *
 * @package Kanso
 * @author  Joe Howard
 */
class POSTdispatcher
{

    /**
     * @var \Kanso\Kanso
     */
    protected $Kanso;

    /**
     * @var bool
     */
    protected $isLoggedIn;

    /**
     * @var array
     */
    protected $postVars;

    /**
     * @var \Kanso\Admin\Security\GUMP
     */
    protected $GUMP;

     /**
     * @var \Kanso\Database\CRUD
     */
    protected $CRUD;

    /**
     * constructor
     *
     */
    public function __construct()
    {
        $this->Kanso           = \Kanso\Kanso::getInstance();
        $this->GUMP            = new \Kanso\Utility\GUMP();
        $this->CRUD            = $this->Kanso->CRUD();
        \Kanso\Admin\Security\sessionManager::init(false, false, $this->Kanso->Environment['REQUEST_URL']);
        $this->isLoggedIn      = \Kanso\Admin\Security\sessionManager::isLoggedIn();
    }

    /********************************************************************************
    * INSTANTIATION AND VALIDATION
    *******************************************************************************/

    /**
     * Validate the request 
     *
     * Note this function is envoked directly from the router for all POST
     * requests to the admin panel. It validates the request with the refferer
     * and public key signature (if applicatible) and calls the main dispatcher
     *
     * @return mixed
     */
    public function validate() 
    {

        # Set the request to false
        $validRequest = false;

        # Only ajax requests are allowed, with a valid HTTP ajax header
        if (!$this->Kanso->Request->isAjax()) $validRequest = false;

        # Get the post variables
        $this->postVars = $this->Kanso->Request->fetch();

        # Ajax requests all carry the same key/value of "ajaxRequest", which
        # indicates what to dispatch
        if (!isset($this->postVars['ajaxRequest'])) $validRequest = false;

        # Validate that the request came from the admin panel
        # All ajax request must have both a refferer and a reffer
        # in the clients session
        if (!$this->validateRefferer())  $validRequest = false;

        # If this is a request for a public key we can serv the client
        # their key/salt
        if ($this->postVars['ajaxRequest'] === 'public_key') $validRequest = true;

        # If the request has a valid public key, we can dispatch their request
        if ($this->validateKeySignature()) $validRequest = true;

        # If the request was invalid, respond with a 404.
        if (!$validRequest) {
            $this->Kanso->Response->setStatus(404);
            return;
        }

        # Dispatch the request
        $response = $this->dispatchRequest();
        
        # If the request was processed, return a valid JSON object
        if ($response || is_array($response)) {
            $this->Kanso->Response->setBody( json_encode( ['response' => 'processed', 'details' => $response] ) );
            $this->Kanso->Response->setheaders(['Content-Type' => 'application/json']);
            return;
        }

        # 404 on fallback
        $this->Kanso->Response->setStatus(404);
       
    }

    /**
     * Dispatch the request to the appropriate function
     *
     * @return mixed
     */
    private function dispatchRequest() 
    {

        $ajaxRequest = $this->postVars['ajaxRequest'];

        # Key Validation
        if ($ajaxRequest === 'public_key') return $this->sendPublicKey();

        # Gatekeeper
        if ($ajaxRequest === 'admin_login') return $this->login();
        if ($ajaxRequest === 'admin_register') return $this->register();
        if ($ajaxRequest === 'admin_forgot_password') return $this->forgotPassword();
        if ($ajaxRequest === 'admin_forgot_username') return $this->forgotUsername();
        if ($ajaxRequest === 'admin_reset_password') return $this->resetPassword();

        # User settings
        if ($ajaxRequest === 'admin_update_settings') return $this->updateAccountSettings();
        if ($ajaxRequest === 'admin_update_author') return $this->updateAuthorInfo();
        if ($ajaxRequest === 'admin_update_kanso') return $this->updateKansoSettings();
        if ($ajaxRequest === 'admin_invite_user') return $this->inviteNewUser();
        if ($ajaxRequest === 'admin_delete_user') return $this->deleteUser();
        if ($ajaxRequest === 'admin_change_user_role') return $this->changeUserRole();
        if ($ajaxRequest === 'admin_clear_cache') return $this->clearKansoCache();

        # Tools
        if ($ajaxRequest === 'admin_import_articles') return $this->importArticles();
        if ($ajaxRequest === 'admin_restore_kanso') return $this->restorKansoDefaults();
        if ($ajaxRequest === 'admin_batch_image') return $this->batchUploadImages();

        # Articles
        if ($ajaxRequest === 'admin_delete_article') return $this->deleteArticle();
        if ($ajaxRequest === 'admin_delete_tag') return $this->deleteTag();

        # Writer
        if ($ajaxRequest === 'writer_publish_article') return $this->publishArticle();
        if ($ajaxRequest === 'writer_save_existing_article') return $this->saveArticle(false);
        if ($ajaxRequest === 'writer_save_new_article') return $this->saveArticle(true);


        # Images
        if ($ajaxRequest === 'admin_author_image') return  $this->uploadImage(true);
        if ($ajaxRequest === 'writer_image_upload') return $this->uploadImage(false);

        # Comments
        if ($ajaxRequest === 'admin_all_comments') return  $this->loadComments('all');
        if ($ajaxRequest === 'admin_approved_comments') return  $this->loadComments('approved');
        if ($ajaxRequest === 'admin_pending_comments') return  $this->loadComments('pending');
        if ($ajaxRequest === 'admin_spammed_comments') return  $this->loadComments('spam');
        if ($ajaxRequest === 'admin_deleted_comments') return  $this->loadComments('deleted');
        if ($ajaxRequest === 'admin_comment_info') return  $this->loadCommentInfo();
        if ($ajaxRequest === 'admin_edit_comment') return  $this->editComment();
        if ($ajaxRequest === 'admin_reply_comment') return  $this->replyComment();
        if ($ajaxRequest === 'admin_black_whitelist_ip') return $this->moderateIpAddress();
        if ($ajaxRequest === 'admin_delete_comments') return $this->actionComments('deleted');
        if ($ajaxRequest === 'admin_spam_comments') return $this->actionComments('spam');
        if ($ajaxRequest === 'admin_approve_comments') return $this->actionComments('approved');

        # Articles
        if ($ajaxRequest === 'admin_all_articles') return $this->loadArticles();
        if ($ajaxRequest === 'admin_publish_articles') return $this->changeArticleStatus('published');
        if ($ajaxRequest === 'admin_unpublish_articles') return $this->changeArticleStatus('draft');
        if ($ajaxRequest === 'admin_delete_articles') return $this->deleteArticles();

        # Tags and categories
        if ($ajaxRequest === 'admin_all_tags_categories') return $this->loadTagCats();
        if ($ajaxRequest === 'admin_delete_tags') return $this->deleteTagOrCategories();
        if ($ajaxRequest === 'admin_clear_tags') return $this->clearTagOrCategories();
        if ($ajaxRequest === 'admin_edit_tag') return $this->editTagOrCategory();

        return false;
    }

    /**
     * Validate the public key sent from the client
     *
     * @return bool
     */
    private function validateKeySignature() 
    {
        if (!isset($this->postVars['public_key'])) return false;
        return \Kanso\Admin\Security\keyManager::authenticateSignature($this->postVars['public_key']);
    }

    /**
    * Send public key/salt to the client
     *
     * @return array|false
     */
    private function sendPublicKey() 
    {

        $clientKeys = \Kanso\Admin\Security\keyManager::getPublicKeys();
        if (!\Kanso\Utility\Arr::issets(['KANSO_PUBLIC_KEY','KANSO_PUBLIC_SALT','KANSO_KEYS_TIME'], $clientKeys)) return false;
        return ['k' => $clientKeys['KANSO_PUBLIC_KEY'], 's' => $clientKeys['KANSO_PUBLIC_SALT']];

    }

    /**
     * Validate the refferal came from the admin panel
     *
     * @return bool
     */
    private function validateRefferer() 
    {
        $KASNO_HTTP_REFERER = \Kanso\Admin\Security\sessionManager::get('KASNO_HTTP_REFERER');
        if (!$KASNO_HTTP_REFERER) $KASNO_HTTP_REFERER = $_SERVER['HTTP_REFERER'];
        if (strpos($KASNO_HTTP_REFERER, $this->Kanso->Environment['KANSO_ADMIN_URI']) !== false) return true;
        return false;
    }

    /********************************************************************************
    * RESPONSE PROCESSING
    *******************************************************************************/

    /********************************************************************************
    * ACCOUNT ACCESS
    *******************************************************************************/

    /**
     * Login Authentification
     *
     * @return string
     */
    private function login() 
    {

        # If the user is logged in return 404
        if ($this->isLoggedIn) return false;

        # Validate the POST variables
        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'username'    => 'required|alpha_dash|max_len,100|min_len,5',
            'password'    => 'required|max_len,100|min_len,6',
        ]);

        $this->GUMP->filter_rules([
            'username' => 'trim|sanitize_string',
            'password' => 'trim',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        # If the POST data was invalid return 404
        if (!$validated_data)  return 'invalid';

        # Validate the login credentials
        # returns false or the clients row from the database
        $validate = \Kanso\Admin\Utility\gateKeeper::validateLoginCredentials($validated_data['username'], $validated_data['password']);

        if ($validate) {
            
            # Log the client in
            \Kanso\Admin\Security\sessionManager::logClientIn($validate);

            return 'valid';

        }

        return 'invalid';
    }

    /**
     * Register Authentification
     *
     * @return bool
     */
    private function register() 
    {

        # Logged in user's can't register
        if ($this->isLoggedIn) return false;

        # Get the key from the user's session
        $sessionKey = \Kanso\Admin\Security\sessionManager::get('KANSO_REGISTER_KEY');

        # Get the key from the ajax request
        if (!isset($this->postVars['referer'])) return false;
        $ajaxKey = \Kanso\Utility\Str::getAfterLastChar($this->postVars['referer'], '?');

        # Get the HTTP REFERRER from the session
        $HTTPkey = \Kanso\Admin\Security\sessionManager::get('KASNO_HTTP_REFERER');
        if (!$HTTPkey) return false;
        $HTTPkey = \Kanso\Utility\Str::getAfterLastChar($HTTPkey, '?');

        # Validate all 3 keys are the same
        if (!\Kanso\Utility\Str::strcmpMulti($HTTPkey, $sessionKey, $ajaxKey)) return false;

        # Sanitize and validate the POST variables
        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'username'    => 'required|alpha_dash|max_len,100|min_len,5',
            'email'       => 'required|valid_email',
            'password'    => 'required|max_len,100|min_len,6',
        ]);
        $this->GUMP->filter_rules([
            'username' => 'trim|sanitize_string',
            'email'    => 'trim|sanitize_email',
            'password' => 'trim',
        ]);
        $validated_data = $this->GUMP->run($postVars);
        
        if (!$validated_data) return false;

        # Validate the request
        return \Kanso\Admin\Utility\gateKeeper::validateRegister($validated_data['username'], $validated_data['email'], $validated_data['password'], $ajaxKey); 

    }

    /**
     * Forgot Password Authentification
     *
     * Note that this function always returns true, regardless of whether the username
     * exists or not. This is for security reasons to prevent people trying to
     * figure out what usernames are registerd with Kanso.
     *
     * @return bool
    */
    private function forgotPassword() 
    {

        if ($this->isLoggedIn) return true;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'username'    => 'required|alpha_dash|max_len,100|min_len,5',
        ]);

        $this->GUMP->filter_rules([
            'username' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data)  \Kanso\Admin\Utility\gateKeeper::validateForgotPassword($validated_data['username']); 

        return true;

    }

    /**
     * forgot username authentification
     *
     * Note that this function always returns true, regardless of whether the email
     * exists or not. This is for security reasons to prevent people trying to
     * figure out what email addresses are registerd with Kanso.
     *
     * @return bool
     */
    private function forgotUsername() 
    {

        # Logged in user can't send a username reminder
        if ($this->isLoggedIn) return true;

        # Validate and sanitize the POST variables
        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'email'    => 'required|valid_email',
        ]);

        $this->GUMP->filter_rules([
            'email' => 'trim|sanitize_email',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        \Kanso\Admin\Utility\gateKeeper::validateForgotUsername($validated_data['email']); 

        return true;
    }

    /**
     * Reset Password authentification
     *
     * Note that this function always returns true, regardless of whether the email
     * exists or not. This is for security reasons to prevent people trying to
     * figure out what email addresses are registerd with Kanso.
     *
     * @return bool
    */
    private function resetPassword() 
    {

        # Logged in users can't reset their password
        if ($this->isLoggedIn) return false;

        # Get the key from the user's session
        $sessionKey = \Kanso\Admin\Security\sessionManager::get('KANSO_PASSWORD_KEY');

        # Get the key from the ajax request
        if (!isset($this->postVars['referer'])) return false;
        $ajaxKey = \Kanso\Utility\Str::getAfterLastChar($this->postVars['referer'], '?');

        # Get the HTTP REFERRER from the session
        $HTTPkey = \Kanso\Admin\Security\sessionManager::get('KASNO_HTTP_REFERER');
        if (!$HTTPkey) return false;
        $HTTPkey = \Kanso\Utility\Str::getAfterLastChar($HTTPkey, '?');

        # Validate all 3 keys are the same
        if (!\Kanso\Utility\Str::strcmpMulti($HTTPkey, $sessionKey, $ajaxKey)) return false;

        # Sanitize and validate the POST variables
        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'password'    => 'required|max_len,100|min_len,6',
        ]);

        $this->GUMP->filter_rules([
            'password' => 'trim',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        return \Kanso\Admin\Utility\gateKeeper::validateResetPassword($validated_data['password'], $ajaxKey); 

        return false;

    }

    /********************************************************************************
    * SETTINGS AND CONFIGURATION
    *******************************************************************************/

    /**
     * Update administrator settings validation
     *
     * @return bool
    */
    private function updateAccountSettings()
    {
        if (!$this->isLoggedIn) return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'username'    => 'required|alpha_dash|max_len,100|min_len,5',
            'email'       => 'required|valid_email',
            'password'    => 'max_len,100|min_len,6',
            'email_notifications' => 'required|boolean',
        ]);

        $this->GUMP->filter_rules([
            'username' => 'trim|sanitize_string',
            'email'    => 'trim|sanitize_email',
            'password' => 'trim',
            'email_notifications' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) return \Kanso\Admin\Utility\userManager::updateAccountDetails($validated_data['username'], $validated_data['email'], $validated_data['password'], $validated_data['email_notifications']); 

        return true;

    }

    /**
     * Update author settings validation
     *
     * @return bool
    */
    private function updateAuthorInfo() 
    {
        if (!$this->isLoggedIn) return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'name'    	 => 'required|alpha_space|max_len,50|min_len,5',
            'slug'       => 'required|alpha_dash|max_len,50|min_len,5',
            'bio'    	 => 'max_len,500',
            'facebook'   => 'valid_url',
            'twitter'    => 'valid_url',
            'google'     => 'valid_url',
        ]);

        $this->GUMP->filter_rules([
            'name'    	 => 'trim|sanitize_string',
            'slug'       => 'trim|sanitize_string',
            'bio'        => 'trim|sanitize_string',
            'facebook'   => 'trim|sanitize_string',
            'twitter'    => 'trim|sanitize_string',
            'google'     => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) return \Kanso\Admin\Utility\userManager::updateAuthorDetails($validated_data['name'], $validated_data['slug'], $validated_data['bio'], $validated_data['facebook'], $validated_data['twitter'], $validated_data['google']); 

        return true;
    }

    /**
     * Update Kanso's configuration validation
     *
     * @return bool
    */
    private function updateKansoSettings() 
    {
        # Validate the user is logged in
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $author            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($author['role'] !== 'administrator') return false;

        # Validate post variables
        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'route-authors'     => 'required|boolean',
            'route-categories'  => 'required|boolean',
            'route-tags'        => 'required|boolean',
            'use-CDN'           => 'required|boolean',
            'use-cache'         => 'required|boolean',
            'enable-comments'   => 'required|boolean',
            'posts-per-page'    => 'required|integer',
            'thumbnail-quality' => 'required|integer',
            'CDN-url'           => 'max_len,100',
            'cache-life'        => 'max_len,50',
            'site-title'        => 'required|max_len,100',
            'site-description'  => 'required|max_len,300',
            'sitemap-url'       => 'required|max_len,100',
            'theme'             => 'required|max_len,100',
            'thumbnail-sizes'   => 'required|max_len,100',
            'permalinks'        => 'required|max_len,50',
        ]);

        $this->GUMP->filter_rules([
            'posts-per-page'    => 'sanitize_numbers',
            'thumbnail-quality' => 'sanitize_numbers',
            'CDN-url'           => 'trim|sanitize_string|basic_tags',
            'cache-life'        => 'trim|sanitize_string|basic_tags',
            'site-title'        => 'trim|sanitize_string|basic_tags',
            'site-description'  => 'trim|sanitize_string|basic_tags',
            'sitemap-url'       => 'trim|sanitize_string|basic_tags',
            'theme'             => 'trim|sanitize_string|basic_tags',
            'thumbnail-sizes'   => 'trim|sanitize_string|basic_tags',
            'permalinks'        => 'trim|sanitize_string|basic_tags',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) return \Kanso\Admin\Utility\settingsManager::updateKansoSettings($validated_data); 

        return true;

    }

    /**
     * Clear Kanso's cache validation
     *
     * @return bool
    */
    private function clearKansoCache($slug = null) 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $cleared =  $this->Kanso->Cache->clearCache($slug);
        if ($cleared) return 'valid';
        return 'invalid';

    }

    /**
     * Restore Kanso to factory
     *
     * @return bool
     */
    private function restorKansoDefaults()
    {

        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        return \Kanso\Admin\Utility\settingsManager::restoreKanso();

        return false;

    }

    /********************************************************************************
    * USER MANAGEMENT
    *******************************************************************************/

    /**
     * Invite a new user to the application validation
     *
     * @return bool
     */
    private function inviteNewUser() 
    {

        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $user            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($user['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'email'       => 'required|valid_email',
            'role'        => 'required|contains,administrator writer',
        ]);

        $this->GUMP->filter_rules([
            'email'    => 'trim|sanitize_email',
            'role'     => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) {
            if ($validated_data['email'] === $user['email']) return "already_member";
            $email = $this->CRUD->SELECT('*')->FROM('authors')->WHERE('email', '=', $validated_data['email'])->FIND();
            if (!$email || empty($email) || ($email && $email['status'] === 'deleted')) return \Kanso\Admin\Utility\userManager::inviteNewUser($validated_data['email'], $validated_data['role'], $user);
            if ($email && $email['status'] === 'confirmed') return 'already_member';
        }

        return false;

    }

    /**
     * Delete an existing user from the application validation
     *
     * @return bool
     */
    private function deleteUser() 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $user            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($user['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'id'       => 'required|integer',
        ]);

        $this->GUMP->filter_rules([
            'id'      => 'sanitize_numbers',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) {
            if ($validated_data['id'] === $user['id']) return false;
            if ((int)$validated_data['id'] === 1) return false;
            $userExists = $this->CRUD->SELECT('*')->FROM('authors')->WHERE('id', '=', $validated_data['id'])->FIND();
            if (empty($userExists)) return false;
            return \Kanso\Admin\Utility\userManager::deleteUser($validated_data['id']); 
        }
        return false;
    }

    /**
     * Change a user's role validation
     *
     * @return bool
     */
    private function changeUserRole() 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'id'       => 'required|integer',
        ]);

        $this->GUMP->filter_rules([
            'id'      => 'sanitize_numbers',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) {
            if ((int)$validated_data['id'] === (int)$client['id']) return false;
            if ((int)$validated_data['id'] === 1) return false;
            $userExists = $this->CRUD->SELECT('*')->FROM('authors')->WHERE('id', '=', $validated_data['id'])->FIND();
            if (empty($userExists)) return false;
            return \Kanso\Admin\Utility\userManager::changeUserRole($validated_data['id'], $validated_data['role']); 
        }
        return false;

    }

    /********************************************************************************
    * IMAGES
    *******************************************************************************/
  
    /**
     * Upload an image
     *
     * This function uploads both author images as well as 
     * images for articles
     *
     * @param  bool    $isAuthor (optional)
     * @return string|false
     */
    private function uploadImage($isAuthor = null) 
    {

        if (!$this->isLoggedIn) return false;

        $postVars = $this->postVars;

        # Validate a file was sent
        if (!isset($postVars['file'])) return false;

        # Validate the file has a mime
        if (!isset($postVars['file']['type'])) return false;

        # Validate this is an image
        if ($postVars['file']['type'] !== 'image/png' && $postVars['file']['type'] !== 'image/jpeg') return false;

        # Convert the mime to an extension
        $mime = $this->Kanso->Request->mimeToExt($postVars['file']['type']);
        if ($mime !== 'jpg' && $mime !== 'png') return false;

        # Declare size suffixes for resizing
        $sizes  = ["small", "medium", "large"];

        # Grab our image processor
        $Imager = new \Kanso\Utility\Images($this->postVars['file']['tmp_name']);

        # Declare config sizes locally
        $configSizes = $this->Kanso->Config['KANSO_THUMBNAILS'];

        # If this is a author thumbnail crop to square
        $imgurl = '';
        $loop   = 3;
        if ($isAuthor)  $configSizes = [ ['150','150'], ['256','256'], ['512','512'] ];

        # Loop through config sizes - maximum is 3 thumbnails
        for ($i=0; $i < count($configSizes) && $i < $loop; $i++) {
            $size  = $configSizes[$i];

            # Sanitize the file name
            $name  = htmlentities(str_replace("/", "", stripslashes($this->postVars['file']['name']))); 

            # Get the extension
            $ext   = $mime;

            # Get the name minus the ext
            $name  = explode('.'.$ext, $name)[0];

            # Set the destination and quality
            $dst   = $this->Kanso->Environment['KANSO_UPLOADS_DIR'].'/Images/'.$name.'_'.$sizes[$i].'.'.$ext;
            $qual  = $this->Kanso->Config['KANSO_IMG_QUALITY'];

            $qual  = ($mime === 'png' ? ($qual/10) : $qual);

            # If sizes are declared with width & Height - resize to those dimensions
            # otherwise just resize to width;
            if (is_array($size)) {
                $Imager->crop($size[0], $size[1], true);
            }
            else {
                $Imager->resizeToWidth($size, true);
            }

            # Save the file
            $saved = $Imager->save($dst, false, $qual);

            if (!$saved) return false;

            $imgurl = str_replace($this->Kanso->Environment['DOCUMENT_ROOT'], $this->Kanso->Environment['HTTP_HOST'], $dst);

            \Kanso\Events::fire('imageUpload', [$dst]);
        }

        # If this was an author avatar, update the database
        if ($isAuthor) {
            $author = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
            $author['author'] = substr($imgurl, strrpos($imgurl, '/') + 1);
            $this->CRUD->UPDATE('authors')->SET(['thumbnail' => substr($imgurl, strrpos($imgurl, '/') + 1)])->WHERE('id', '=', $author['id'])->QUERY();
            \Kanso\Admin\Security\sessionManager::logClientIn($author);
        }

        return $imgurl;

    }

    /**
     * Batch upload multiple images at once
     *
     * @return string|false
     */
    private function batchUploadImages() 
    {

        # Validate the client is logged in
        if (!$this->isLoggedIn) return false;

        # Validate the client is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        # Declare the POST variables locally
        $postVars = $this->postVars;

        # Validate a file was sent
        if (!isset($postVars['file'])) return false;

        foreach ($postVars['file'] as $type => $attributes) {

            # Validate the mime
            if ($type === 'type') {
                foreach ($attributes as $mimeType) {
                    if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/gif'])) return 'invalid_mime';
                }
            }
            # Validate the sizes (max is 5mb)
            else if ($type === 'size') {
                foreach ($attributes as $size) {
                    if ($size > 5000000) return 'invalid_size';
                }
            }
        }

        # Declare size suffixes for resizing
        $sizes  = ["small", "medium", "large"];

        # Declare config sizes locally
        $configSizes = $this->Kanso->Config['KANSO_THUMBNAILS'];

        # Upload the images
        foreach ($postVars['file']['tmp_name'] as $f => $tmpFile) {

            # Loop through config sizes - maximum is 3 thumbnails
            for ($i=0; $i < 3; $i++) {

                # Declare the resize
                $size  = $configSizes[$i];

                # Grab our image processor
                $Imager = new \Kanso\Utility\Images($tmpFile);

                # Sanitize the file name
                $name  = str_replace('.jpeg', '.jpg', htmlentities(str_replace("/", "", stripslashes($postVars['file']['name'][$f]))));
                $ext   = substr($name, strrpos($name, '.') + 1);
                $name  = substr($name, 0,strrpos($name, '.'));

                # Set the destination and quality
                $dst   = $this->Kanso->Environment['KANSO_UPLOADS_DIR'].'/Images/'.$name.'_'.$sizes[$i].'.'.$ext;
                $qual  = $this->Kanso->Config['KANSO_IMG_QUALITY'];
                $qual  = $ext === 'png' ? ($qual/10) : $qual;

                # If sizes are declared with width & Height - resize to those dimensions
                # otherwise just resize to width;
                if (is_array($size)) {
                    $Imager->crop($size[0], $size[1], true);
                }
                else {
                    $Imager->resizeToWidth($size, true);
                }

                # Save the file
                $saved = $Imager->save($dst, false, $qual);

                # Return error if file couldnt be saved
                if (!$saved) return 'server_error';

                # Fire upload event
                \Kanso\Events::fire('imageUpload', [$dst]);

            }

        }

        return 'valid';

    }

    /********************************************************************************
    * ARTICLES
    *******************************************************************************/
  
    /**
     * Delete an article validation
     *
     * @return bool
     */
    private function deleteArticle() 
    {

        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'id'       => 'required|integer',
        ]);

        $this->GUMP->filter_rules([
            'id'      => 'sanitize_numbers',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) return \Kanso\Admin\Utility\articlesManager::deleteArticle($validated_data['id']);

        return false;
    }

    /**
     * Save a new or existing article
     *
     * @return bool
     */
    private function saveArticle($isNewArticle) 
    {

        # Only logged in users can write articles
        if (!$this->isLoggedIn) return false;

        # Sanitize and validate the POST variables
        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'type'      => 'required|contains,post page',
        ]);

        $this->GUMP->filter_rules([
            'title'      => 'trim|sanitize_string',
            'category'   => 'trim|sanitize_string',
            'tags'       => 'trim|sanitize_string',
            'type'       => 'trim|sanitize_string',
            'excerpt'    => 'trim|sanitize_string',
            'category'   => 'trim|sanitize_string',
            'thumbnail'  => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        # Existing articles must have an id
        if (!$isNewArticle && !isset($validated_data['id'])) return false;
        if (!$isNewArticle && isset($validated_data['id']) && $validated_data['id'] === 0) return false;

        # All new articles are draft by default
        if ($isNewArticle) $validated_data['status'] = 'draft';

        # If this is an existing article, get the status from the databse
        if (!$isNewArticle) {
            $status = $this->CRUD->SELECT('status')->FROM('posts')->WHERE('id', '=', (int)$validated_data['id'])->FIND();
            $validated_data['status'] = $status['status'];
        }

        # save the article
        return \Kanso\Admin\Utility\articlesManager::saveArticle($validated_data, $isNewArticle);

        return false;

    }

    /**
     * Publish an existing article
     *
     * @return bool
     */
    private function publishArticle() 
    {

        # Only logged in users can write articles
        if (!$this->isLoggedIn) return false;

        # Sanitize and validate the POST variables
        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'type'      => 'required|contains,post page',
        ]);

        $this->GUMP->filter_rules([
            'id'         => 'sanitize_numbers',
            'title'      => 'trim|sanitize_string',
            'category'   => 'trim|sanitize_string',
            'tags'       => 'trim|sanitize_string',
            'type'       => 'trim|sanitize_string',
            'excerpt'    => 'trim|sanitize_string',
            'category'   => 'trim|sanitize_string',
            'thumbnail'  => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        $validated_data['status'] = 'published';

        if (isset($validated_data['id']) && (int)$validated_data['id'] > 0) {
            $newArticle = false;
            $validated_data['id'] = (int)$validated_data['id'];
        }
        else {
            $newArticle = true;
            $validated_data['id'] = 0;
        }

        # save the article
        return \Kanso\Admin\Utility\articlesManager::saveArticle($validated_data, $newArticle);

        return false;
    }

    /**
     * Change an articles status
     *
     * @return bool
     */
    private function changeArticleStatus($status) 
    {
        if (!$this->isLoggedIn) return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'article_ids'  => 'required',
        ]);

        $this->GUMP->filter_rules([
            'article_ids' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        $articles = array_map('trim', explode(',', $validated_data['article_ids'])); 

        foreach ($articles as $id) {
            if (! \Kanso\Admin\Utility\articlesManager::changeArticleStatus( (int)$id, $status)) return false;
        }
        
        return true;

    }

    /**
     * Delete multiple articles at once
     *
     * @return bool
     */
    private function deleteArticles() 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'article_ids'  => 'required',
        ]);

        $this->GUMP->filter_rules([
            'article_ids' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        $articles = array_map('trim', explode(',', $validated_data['article_ids'])); 

        foreach ($articles as $id) {
            if (! \Kanso\Admin\Utility\articlesManager::deleteArticle( (int)$id) ) return false;
        }
        
        return true;
    }

    /**
     * Batch import articles from a JSON upload
     *
     * @return bool
     */
    private function importArticles() 
    {

        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        # Declare the POST variables locally
        $postVars = $this->postVars;

        # Validate a file was sent
        if (!isset($postVars['file'])) return false;

        # Validate the file has a mime
        if (!isset($postVars['file']['type'][0])) return false;

        # Validate this is an json file type
        if ($postVars['file']['type'][0] !== 'application/json') return 'invalid_json';

        # Convert the mime to an extension
        $mime = $this->Kanso->Request->mimeToExt($postVars['file']['type'][0]);
        if ($mime !== 'json') return 'invalid_json';

        # Validate the file is a valid json
        if (!$this->isJson(file_get_contents($postVars['file']['tmp_name'][0]))) return 'invalid_json';

        # Convert the JSON to an array
        $articles = json_decode(file_get_contents($postVars['file']['tmp_name'][0]), true);

        # Import the articles
        return \Kanso\Admin\Utility\articlesManager::batchImport($articles);

        return false;
    }

    /**
     * Load articles for ajax
     *
     * @return array|false
     */
    private function loadArticles() 
    {
        # Validate the user logged in
        if (!$this->isLoggedIn) return false;

        # Get the POST variables
        $postVars = $this->GUMP->sanitize($this->postVars);

        # Set validation rules
        $this->GUMP->validation_rules([
            'page'     => 'required|integer',
            'sortBy'   => 'required|contains,newest oldest title category tag drafts published type',
            'search'   => 'required',
        ]);

        # Set filter rules
        $this->GUMP->filter_rules([
            'page'     => 'sanitize_numbers',
            'sortBy'   => 'trim|sanitize_string',
            'search'   => 'trim|sanitize_string',
        ]);

        # Sanitize and validate the POST variables
        $validated_data = $this->GUMP->run($postVars);

        # Check if they pass validation
        if (!$validated_data) return false;
        
        # Default operation values
        $isSearch     = $validated_data['search'] !== 'false';
        $searchValue  = false;
        $searchKey    = false;
        $leftJoin     = false;
        $page         = ((int)$validated_data['page']) -1;
        $sort         = 'ASC';
        $sortKey      = 'created';
        
        # If this is a search, clean and santize the search keys
        if ($isSearch) {
            
            $searchValue = $validated_data['search'];

            $validKeys   = [
                'title'    => 'title',
                'author'   => 'author.name',
                'type'     => 'type',
                'status'   => 'status',
                'category' => 'category.name',
                'tags'     => 'tags.name',
            ];

            # Validate if the search is specific to a column
            if (\Kanso\Utility\Str::contains($searchValue, ':')) {
                
                $value    = trim(\Kanso\Utility\Str::getAfterFirstChar($searchValue, ':'));
                $key      = trim(\Kanso\Utility\Str::getBeforeFirstChar($searchValue, ':'));
                $key      = isset($validKeys[$key]) ? $validKeys[$key] : false;

                # Split comma seperated list of values into a search array
                if ($key) {
                    $searchKey   = $key;
                    $searchValue = [$value];
                    if (\Kanso\Utility\Str::contains($searchValue[0], ' ')) $searchValue = array_filter(array_map('trim', explode(' ', $searchValue[0])));
                }
                else {
                    // Key doesnt exist
                    return [];
                }
            }

        }

        # Filter and sanitize the sort order
        if ($validated_data['sortBy'] === 'newest' || $validated_data['sortBy'] === 'drafts') $sort = 'DESC';
        if ($validated_data['sortBy'] === 'oldest' || $validated_data['sortBy'] === 'published') $sort = 'ASC';

        if ($validated_data['sortBy'] === 'category')  $sortKey   = 'category.name';
        if ($validated_data['sortBy'] === 'tags')      $sortKey   = 'tags.name';
        if ($validated_data['sortBy'] === 'drafts')    $sortKey   = 'posts.status';
        if ($validated_data['sortBy'] === 'published') $sortKey   = 'posts.status';
        if ($validated_data['sortBy'] === 'type')      $sortKey   = 'posts.type';
        if ($validated_data['sortBy'] === 'title')     $sortKey   = 'posts.title';

        # Get all the articles
        $articles = $this->CRUD->getArticlesByIndex(null, null, null, ['tags', 'category', 'author']);

        # Pre validate there are actually some articles to process
        if (empty($articles)) return [];

        # Sort the articles by key
        $articles = \Kanso\Utility\Arr::sortMulti($articles, $sortKey, $sort);

        # Loop and filter the articles
        foreach ($articles as $i => $article) {

            // Search the article
            if ($isSearch && $searchKey && is_array($searchValue)) {
                foreach ($searchValue as $query) {
                    if (isset($article[$searchKey])) {
                        if (!preg_match($article[$searchKey], "%$query%")) unset($articles[$i]);
                    }
                }
            }

            // Search the 'content' key using regex match
            else if ($isSearch && !$searchKey && $searchValue) {
                if (!preg_match($article['excerpt'], "%$searchValue%") || !preg_match($article['title'], "%$searchValue%")) unset($articles[$i]);
            }

            if ( $article['status'] === 'draft') {
                $articles[$i]['permalink'] = rtrim($this->Kanso->Query->the_permalink($article['id']), '/').'?draft';
            }
            else {
                $articles[$i]['permalink'] = $this->Kanso->Query->the_permalink($article['id']);
            }

            $articles[$i]['edit_permalink'] = '/admin/write/'.$this->Kanso->Query->the_slug($article['id']);

            $articles[$i]['category']['permalink'] = $this->Kanso->Query->the_category_url($articles[$i]['category_id']);


            foreach ($article['tags'] as $t => $tag) {
                $articles[$i]['tags'][$t]['permalink'] = $this->Kanso->Query->the_tag_url($tag['id']);
            }
            
            $articles[$i]['author']['permalink'] = $this->Kanso->Query->the_author_url($article['author_id']);

        }

        # Pageinate the articles
        return \Kanso\Utility\Arr::paginate($articles, $page, 10);

    }

    /********************************************************************************
    * TAGS AND CATEGORIES
    *******************************************************************************/
  
    /**
     * Delete a tag or category validation
     *
     * @return bool
     */
    private function deleteTag() 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'id'       => 'required|integer',
            'type'     => 'required|contains,tag category',
        ]);

        $this->GUMP->filter_rules([
            'id'       => 'sanitize_numbers',
            'type'     => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) return \Kanso\Admin\Utility\articlesManager::deleteTag($validated_data['id'], $validated_data['type']);

        return false;
    }

    /**
     * Load tags and categories for ajax
     *
     * @return array|false
     */
    private function loadTagCats() 
    {
        if (!$this->isLoggedIn) return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'page'     => 'required|integer',
            'sortBy'   => 'required|contains,name posts type',
            'search'   => 'required',
        ]);

        $this->GUMP->filter_rules([
            'page'     => 'sanitize_numbers',
            'sortBy'   => 'trim|sanitize_string',
            'search'   => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;
            
        $categories   = $this->CRUD->SELECT('*')->FROM('categories')->FIND_ALL();
        $tags         = $this->CRUD->SELECT('*')->FROM('tags')->FIND_ALL();

        $isSearch     = $validated_data['search'] !== 'false';
        $searchValue  = false;
        $searchKey    = false;
        $page         = ((int)$validated_data['page']) -1;
        $sort         = 'DESC';
        $sortKey      = isset($validated_data['sortBy']) ? $validated_data['sortBy'] : 'name';

        foreach ($tags as $i => $tag) {
            $tags[$i]['permalink'] = $this->Kanso->Query->the_tag_url($tag['id']);
            $tagPosts = $this->CRUD->SELECT('posts.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('posts', 'tags_to_posts.post_id = posts.id')->WHERE('tags_to_posts.tag_id', '=', (int)$tag['id'])->FIND_ALL();
            $tags[$i]['posts'] = [];
            foreach ($tagPosts as $post) {
                $tags[$i]['posts'][] = [
                    'name'      => $post['title'],
                    'permalink' => $this->Kanso->Query->the_permalink($post['id']),
                ];
            }
            $tags[$i]['type'] = 'tag';
        }

        foreach ($categories as $i => $category) {
            $categories[$i]['permalink'] = $this->Kanso->Query->the_category_url($category['id']);
            $categoryPosts = $this->CRUD->SELECT('*')->FROM('posts')->WHERE('category_id', '=', (int)$category['id'])->FIND_ALL();
            $categories[$i]['posts'] = [];
            foreach ($categoryPosts as $post) {
                $categories[$i]['posts'][] = [
                    'name'      => $post['title'],
                    'permalink' => $this->Kanso->Query->the_permalink($post['id']),
                ];
            }
            $categories[$i]['type'] = 'category';
        }

        $list = array_merge($tags, $categories);

        if ($isSearch) {
            
            $searchValue = $validated_data['search'];

            $validKeys   = [
                'name'     => 'name',
                'type'     => 'type',
            ];

            if (\Kanso\Utility\Str::contains($searchValue, ':')) {
                
                $value    = trim(\Kanso\Utility\Str::getAfterFirstChar($searchValue, ':'));
                $key      = trim(\Kanso\Utility\Str::getBeforeFirstChar($searchValue, ':'));
                $key      = isset($validKeys[$key]) ? $validKeys[$key] : false;
                if ($key) {
                    $searchKey   = $key;
                    $searchValue = $value;
                    
                }
                else {
                    // Key doesnt exist
                    return [];
                }
            }

        }

        // Search a table with an array of key/value matches
        if ($isSearch && $searchKey && $searchValue) {
            foreach ($list as $i => $item) {
                if (is_string($item[$searchKey]) && strtolower($item[$searchKey]) !== strtolower($searchValue)) {
                    unset($list[$i]);
                }
            }
        }
        else if ($isSearch && !$searchKey && $searchValue) {
            foreach ($list as $i => $item) {
                if (strtolower($item['type']) === strtolower($searchValue) || strtolower($item['name']) === strtolower($searchValue)) {
                    continue;
                }
                else {
                    unset($list[$i]);
                }
            }
        }

        $list = \Kanso\Utility\Arr::sortMulti($list, $sortKey); 

        return  \Kanso\Utility\Arr::paginate($list, $page, 10);

        return false;
    }

    /**
     * Delete a tag or category
     *
     * @return bool
     */
    private function deleteTagOrCategories() 
    {

        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'entries'  => 'required',
        ]);

        $this->GUMP->filter_rules([
            'entries' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        if (!$this->isJson($this->postVars['entries'])) return false;

        $entries = json_decode($this->postVars['entries']);

        foreach ($entries as $entry) {
            if (!\Kanso\Admin\Utility\articlesManager::clearTag( (int)$entry->id, $entry->type, true)) return false; 
        }

        return true;

    }

    /**
     * Clear a tag or category
     *
     * @return bool
     */
    private function clearTagOrCategories() 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'entries'  => 'required',
        ]);

        $this->GUMP->filter_rules([
            'entries' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        if (!$this->isJson($this->postVars['entries'])) return false;

        $entries = json_decode($this->postVars['entries']);

        foreach ($entries as $entry) {
            if (! \Kanso\Admin\Utility\articlesManager::clearTag( (int)$entry->id, $entry->type)) return false; 
        }

        return true;

    }

    /**
     * Edit a tag or category
     *
     * @return bool
     */
    private function editTagOrCategory() 
    {
        
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        if (!$this->isJson($this->postVars['entries'])) return false;

        $entries = json_decode($this->postVars['entries'], true);

        $entries = $entries[0];

        $postVars = $this->GUMP->sanitize($entries);

        $this->GUMP->validation_rules([
            'name'  => 'required|max_len,50',
            'slug'  => 'required|alpha_dash|max_len,50',
            'type'  => 'required|contains,tag category',
            'id'    => 'required|numeric',
        ]);

        $this->GUMP->filter_rules([
            'name'  => 'trim|sanitize_string',
            'slug'  => 'trim|sanitize_string',
            'type'  => 'trim|sanitize_string',
            'id'    => 'trim|sanitize_numbers',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        return \Kanso\Admin\Utility\articlesManager::editTag($validated_data['id'], $validated_data['type'], $validated_data['slug'], $validated_data['name']);

        return false;

    }

    /********************************************************************************
    * COMMENTS
    *******************************************************************************/
  
  
    /**
     * Load comments for ajax
     *
     * @return array|false
     */
    private function loadComments($filter) {

        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'page'     => 'required|integer',
            'sortBy'   => 'required',
            'search'   => 'required',
        ]);

        $this->GUMP->filter_rules([
            'page'     => 'sanitize_numbers',
            'sortBy'   => 'trim|sanitize_string',
            'search'   => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) {
            
            $isSearch = $validated_data['search'] !== 'false';
            $page     = ((int)$validated_data['page']) -1;
            $comments = [];
            $sort     = $validated_data['sortBy'] === 'newest' ? 'DESC' : 'ASC' ;

            if ($isSearch) {

                $validKeys = [
                    'ip'     => 'ip_address',
                    'status' => 'status',
                    'user'   => 'name',
                    'email'  => 'email',
                ];

                $searchValue = $validated_data['search'];
                $searchKey   = false;

                if (\Kanso\Utility\Str::contains($searchValue, ':')) {
                    
                    $value    = \Kanso\Utility\Str::getAfterFirstChar($searchValue, ':');
                    $key      = \Kanso\Utility\Str::getBeforeFirstChar($searchValue, ':');
                    $key      = isset($validKeys[$key]) ? $validKeys[$key] : false;
                    if ($key) {
                        $searchKey   = $key;
                        $searchValue = $value; 
                    }
                }

                if ($searchKey) {
                    $comments = $this->CRUD->SELECT('*')->FROM('comments')->WHERE($searchKey, '=', $searchValue)->FIND_ALL();
                }
                else {
                    $comments = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('content', 'LIKE', "%$searchValue%")->FIND_ALL();
                }
            }
            else {
                if ($filter === 'all') {
                    $comments = $this->CRUD->SELECT('*')->FROM('comments')->ORDER_BY('date', $sort)->FIND_ALL();
                }
                if ($filter === 'approved') {
                    $comments = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('status', '=', 'approved')->ORDER_BY('date', $sort)->FIND_ALL();
                }
                if ($filter === 'spam') {
                    $comments = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('status', '=', 'spam')->ORDER_BY('date', $sort)->FIND_ALL();
                }
                if ($filter === 'pending') {
                    $comments = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('status', '=', 'pending')->ORDER_BY('date', $sort)->FIND_ALL();
                }
                if ($filter === 'deleted') {
                    $comments = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('status', '=', 'deleted')->ORDER_BY('date', $sort)->FIND_ALL();
                }
            }

            foreach ($comments as $key => $comment) {
                $comments[$key]['permalink'] = $this->Kanso->Query->the_permalink($comment['post_id']);
                $comments[$key]['title']     = $this->Kanso->Query->the_title($comment['post_id']);
                $comments[$key]['avatar']    = $this->Kanso->Query->get_avatar($comment['email'], 100, true);
            }

            $comments = \Kanso\Utility\Arr::paginate($comments, $page, 10);
            
            return $comments;
        }

        return false;

    }

    /**
     * Load comments info ajax
     *
     * @return array|false
     */
    private function loadCommentInfo() 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'comment_id'     => 'required|integer',
        ]);

        $this->GUMP->filter_rules([
            'comment_id'     => 'trim|sanitize_numbers',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) {
            
            $commentRow  = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('id', '=', (int)$validated_data['comment_id'])->FIND();

            # If it doesn't exist return false
            if (!$commentRow) return false;

            $ip_address   = $commentRow['ip_address'];
            $name         = $commentRow['name'];
            $email        = $commentRow['email'];

            # Get all the user's comments
            $userComments = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('ip_address', '=', $ip_address)->OR_WHERE('email', '=', $email)->OR_WHERE('name', '=', $name)->FIND_ALL();

            $response     = [
                'reputation'   => 0,
                'posted_count' => 0,
                'spam_count'   => 0,
                'first_date'   => 0,
                'blacklisted'  => false,
                'whitelisted'  => false,
                'ip_address'   => $ip_address,
                'name'         => $name,
                'email'        => $email,
                'avatar'       => $this->Kanso->Query->get_avatar($email, 150, true),
                'status'       => $commentRow['status'],
                'content'      => $commentRow['content'],
                'html_content' => $commentRow['html_content'],
            ];

            $blacklistedIps = \Kanso\Comments\Spam\SpamProtector::loadDictionary('blacklist_ip');
            $whiteListedIps = \Kanso\Comments\Spam\SpamProtector::loadDictionary('whitelist_ip');

            foreach ($userComments as $comment) {
               $response['reputation']   += $comment['rating'];
               $response['posted_count'] += 1;
               if ($comment['status'] === 'spam') $response['spam_count'] += 1;
               if ($comment['date'] < $response['first_date'] || $response['first_date'] === 0) $response['first_date'] = $comment['date'];
            }
            $response['reputation'] = $response['reputation']/ count($userComments);

            if (in_array($ip_address, $blacklistedIps)) $response['blacklisted'] = true;
            if (in_array($ip_address, $whiteListedIps)) $response['whitelisted'] = true;


            return $response;

        }

        return false;
    }

    /**
     * Edit an existing comment
     *
     * @return array|false
     */
    private function editComment() 
    {

        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'comment_id'     => 'required|integer',
            'content'        => 'required',

        ]);

        $this->GUMP->filter_rules([
            'comment_id'     => 'trim|sanitize_numbers',
            'content'        => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) {

            $commentRow  = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('id', '=', (int)$validated_data['comment_id'])->FIND();

            # If it doesn't exist return false
            if (!$commentRow) return false;
            $Parser                    = new \Kanso\Parsedown\ParsedownExtra();
            $HTMLContent                = $Parser->text($validated_data['content']);
            $commentRow['content']      = $validated_data['content'];
            $commentRow['html_content'] = $HTMLContent;
            $this->CRUD->UPDATE('comments')->SET(['content' => $validated_data['content'], 'html_content' => $HTMLContent])->WHERE('id', '=', $commentRow['id'])->QUERY();

            return $HTMLContent;
        }
        return false;
    }

    /**
     * Reply to an existing comment
     *
     * @return array|false
     */
    private function replyComment()
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'comment_id'     => 'required|integer',
            'content'        => 'required|max_len,2000|min_len,1',
        ]);

        $this->GUMP->filter_rules([
            'comment_id'     => 'trim|sanitize_numbers',
            'content'        => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if (!$validated_data) return false;

        $parentComment = $this->CRUD->SELECT('*')->FROM('comments')->WHERE('id', '=', (int)$validated_data['comment_id'])->FIND();

        # If it doesn't exist return false
        if (!$parentComment) return false;

        # Prep data for entry
        $postVars = [
            'postID'       => $parentComment['post_id'],
            'replyID'      => $parentComment['id'],
            'content'      => $validated_data['content'],
            'name'         => $client['name'],
            'email'        => $client['email'],
            'email-reply'  => true,
            'email-thread' => true,
        ];

        return  \Kanso\Comments\CommentManager::addComment($postVars, false);

        return false;

    }

    
    /**
     * Moderate an ip address from commenting
     *
     * @return bool
     */
    private function moderateIpAddress() 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'action'      => 'required|contains,blacklist whitelist nolist',
            'ip_address'  => 'required',
        ]);

        $this->GUMP->filter_rules([
            'action'     => 'trim|sanitize_string',
            'ip_address' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) {
            \Kanso\Comments\CommentManager::moderateIPAddress($validated_data['ip_address'], $validated_data['action']);
            return true;
        }
    }

    /**
     * Change a comments status
     *
     * @return bool
     */
    private function actionComments($status) 
    {
        if (!$this->isLoggedIn) return false;

        # Validate the user is an admin
        $client            = \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA');
        if ($client['role'] !== 'administrator') return false;

        $postVars = $this->GUMP->sanitize($this->postVars);

        $this->GUMP->validation_rules([
            'comment_ids' => 'required',
        ]);

        $this->GUMP->filter_rules([
            'comment_ids' => 'trim|sanitize_string',
        ]);

        $validated_data = $this->GUMP->run($postVars);

        if ($validated_data) {
            $comment_ids = array_map('intval', explode(',', $validated_data['comment_ids']));
            if (empty($comment_ids)) return false;
            foreach ($comment_ids as $id) {
                if (! \Kanso\Comments\CommentManager::changeCommentStatus($id, $status)) return false;
            }
            return true;
        }
        return false;
    }

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