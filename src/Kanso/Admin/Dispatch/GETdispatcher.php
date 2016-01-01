<?php

namespace Kanso\Admin\Dispatch;

/**
 * GET Dispatcher
 *
 * The Admin GET Dispatcher servers as a controller for all GET requests to
 * Kanso's Admin panel. It is initialized directly from Kanso's router,
 * with a variable indicating what kind of request was made. The router
 * will then call the appropriate validation method.
 *
 * This works a little different from a traditional MVC. Rather than having a 
 * bunch of controllers, models and views for each seperate request, there is a 
 * single controller which loads the appropriate functions/includes/variables into
 * a single view.
 *
 */
class GETdispatcher
{

	/**
	 * @var \Kanso\Kanso
	 */
	protected $Kanso;

	/**
	 * @var \Kanso\Database\CRUD
	 */
	protected $CRUD;
	/**
	 * @var bool
	 */
	protected $isLoggedIn;

	/**
	 * @var string
	 */
	protected $pageRequest;

	/**
	 * @var string
	 */
	protected $adminHomepage;

	/**
	 * @var mixed
	 */
	protected $writerEntry;

	/**
	 * @var \Kanso\Admin\Dispatch\GETdispatcher
	 */
	private static $instance;


	/********************************************************************************
	* PUBLIC INITIALIZATION
	*******************************************************************************/

	/**
	 * Dispatcher
	 *
	 * This method is invoked directly from the router, with the parameter
	 * of what page should be attempted to load.
	 *
	 * It will also create/return a new instance if one hasn't been
	 * invoked already.
	 *
	 * @param string    $pageRequest    The page type needs to be dispatched    
	 */
	public static function dispatch($pageRequest)
	{

		# Create a new instance
		if (!isset(self::$instance)) {
			self::$instance = new GETdispatcher($pageRequest);
		}

		return self::$instance;
	}

	/**
	 * Private Constructor
	 *
	 * Called from dispatch(), this will initialize the current class
	 * variables and other dependancies. It then dispatches the request
	 * to the appropriate method or 404 if the method doesn't exist.
	 *
	 * @param string    $pageRequest    The page type needs to be dispatched    
	 */
	private function __construct($pageRequest)
	{

		# Get the Kanso instance
		$this->Kanso = \Kanso\Kanso::getInstance();

		$this->Kanso->is_admin = true;

		# Set the page type
		$this->pageRequest = $pageRequest;
		
		# Initialize the session manager
		\Kanso\Admin\Security\sessionManager::init(true, $pageRequest === 'assets', $this->Kanso->Environment['REQUEST_URL']);

		# Save the clients login boolean
		$this->isLoggedIn    = \Kanso\Admin\Security\sessionManager::isLoggedIn();

		# Save the default homepage URL for redirecting
		$this->adminHomepage = $this->Kanso->Environment['HTTP_HOST'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'articles'.DIRECTORY_SEPARATOR;

		# Dispatch the method if it callable
		$method = [$this, $pageRequest];

		$this->CRUD = $this->Kanso->CRUD();

		if (is_callable($method)) return call_user_func($method);

		# 404 on error

	}

	/********************************************************************************
	* PRIVATE DISPATCHERS
	*******************************************************************************/

	/**
	 * Load the login page
	 */
	private function logIn() 
	{

		if ($this->isLoggedIn) {
			$this->Kanso->redirect($this->adminHomepage);
			return;
		}
		
		$this->renderPage();
	  
	}

	/**
	 * Log the client out 
	 */
	private function logOut() 
	{

		if ($this->isLoggedIn) {
			\Kanso\Admin\Security\sessionManager::logClientOut();
			$this->Kanso->redirect($this->Kanso->Environment['HTTP_HOST']);
		}
		else {
			$this->Kanso->notFound();
		}

	}

	/**
	 * Load the register page or 404
	 *
	 */
	private function register() 
	{
		if ($this->validateRegisterRequest()) $this->renderPage();
		else $this->Kanso->notFound();
	}

	/**
	 * Load the forgot username page
	 *
	 */
	private function forgotUserName() 
	{
		if ($this->isLoggedIn) $this->Kanso->redirect($this->adminHomepage);
		else $this->renderPage();
	}

	/**
	 * Load the forgot password page
	 *
	 */
	private function forgotPassword() 
	{

		if ($this->isLoggedIn) $this->Kanso->redirect($this->adminHomepage);
		else $this->renderPage();
	}

	/**
	 * Load the reset password page
	 *
	 */
	private function resetPassword() 
	{
		if (!$this->isLoggedIn && $this->validateResetPasswordRequest()) $this->renderPage();
		else $this->Kanso->notFound();
	}

	/**
	 * Load the settings page
	 *
	 */
	private function settings() 
	{
		if ($this->isLoggedIn && $this->validateSettingsRequest()) $this->renderPage();
		else  $this->Kanso->notFound();
	}

	/**
	 * Load the articles page
	 *
	 */
	private function articles() 
	{
		if ($this->isLoggedIn) $this->renderPage();
		else  $this->Kanso->notFound();
	}

	/**
	 * Load the tags page
	 *
	 */
	private function taxonomy() 
	{
		if ($this->isLoggedIn) $this->renderPage();
		else  $this->Kanso->notFound();
	}

	 /**
	 * Load the comments page
	 *
	 */
	private function comments() 
	{
		if ($this->isLoggedIn) $this->renderPage();
		else  $this->Kanso->notFound();
	}

	/**
	 * Load the writer page
	 *
	 */
	private function writer() 
	{

		if ($this->validateWriterRequest()) {
			$this->renderPage();
		}
		else {
			$this->Kanso->notFound();
		}
		
	}

	/********************************************************************************
	* PRIVATE REQUEST VALIDATION
	*******************************************************************************/

	private function validateSettingsRequest()
	{

		# Valid tabs 
		$availableTabs = ['account', 'author', 'kanso', 'users', 'tools'];

		# Save the url locally
		$url = $this->Kanso->Environment['REQUEST_URL'];

		# Is this a request for an article edit or a new article?
		$request = str_replace($this->Kanso->Environment['HTTP_HOST'].'/admin/settings/', "", $url);

		# Get the slug
		$slug = trim($request, '/');

		# Validate the requested tab
		return in_array($slug, $availableTabs);

	}

	/**
	 * Register Request Validator
	 * 
	 * Register requests need a refferal key. Check if it exists and is valid
	 * @return bool
	 */
	private function validateWriterRequest() 
	{

		# Is the the user logged in
		if (!$this->isLoggedIn) return false;

		# Save the url locally
		$url = $this->Kanso->Environment['REQUEST_URL'];
		
		# Is this a request for an article edit or a new article?
		$request = str_replace($this->Kanso->Environment['HTTP_HOST'].'/admin/write', "", $url);
		
		# Validate the request
		if ($request === '' || $request === '/') return true;

		# Get the slug
		$slug = trim($request, '/').'/';

		# Get the article based on the slug
		$articleRow = $this->CRUD->SELECT('*')->FROM('posts')->WHERE('slug', '=', $slug)->FIND();
		
		# Validate the article exists
		if (!$articleRow || empty($articleRow)) return false;

		# Get the content
		$content = $this->CRUD->SELECT('*')->FROM('content_to_posts')->WHERE('post_id', '=', (int)$articleRow['id'])->FIND();
		
		# Get the category
		$category = $this->CRUD->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$articleRow['category_id'])->FIND();

		# Get the tags
		$tags = $this->CRUD->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id' )->WHERE('post_id', '=', (int)$articleRow['id'])->FIND_ALL();
		
		# List the tags as comma seperated list
		$articleRow['tags'] = '';
		foreach ($tags as $tag) {
			$articleRow['tags'] .= $tag['name'].', ';
		}
		$articleRow['tags'] =  trim($articleRow['tags'], ', ');

		# Append the category
		$articleRow['category'] = $category['name'];

		# Append the content
		$articleRow['content'] = $content['content'];

		# Save the writer entry
		$this->writerEntry = $articleRow;
	   
		return true;

	}

	/**
	 * Register Request Validator
	 * 
	 * Register requests need a refferal key. Check if it exists and is valid
	 * @return bool
	 */
	private function validateRegisterRequest() 
	{

		# If the user is logged in they can't register 
		if ($this->isLoggedIn) return false;

		# Get the key in the url
		$key = $this->Kanso->Request->fetch('query');

		# If no key was given 404
		if (!$key) return false;

		# Find the key in the database
		$keyRow = $this->CRUD->SELECT('*')->FROM('authors')->WHERE('kanso_register_key', '=', $key)->FIND();

		# Validate the key exists
		if (!$keyRow || empty($keyRow)) return false;

		# Add the key to client's session
		\Kanso\Admin\Security\sessionManager::put('KANSO_REGISTER_KEY', $key);

		return true;
		
	}

	/**
	 * Reset Password Request Validator
	 * 
	 * Reset Password requests need a refferal key. Check if it exists and is valid
	 * @return bool
	 */
	private function validateResetPasswordRequest() 
	{

		# Logged in users can't reset their password
		if ($this->isLoggedIn) return false;

		 # Get the key in the url
		$key = $this->Kanso->Request->fetch('query');

		# If no key was given 404
		if (!$key || trim($key) === '' || $key === 'null' ) return false;

		# Find the key in the database
		$keyRow = $this->CRUD->SELECT('*')->FROM('authors')->WHERE('kanso_password_key', '=', $key)->FIND();

		# Validate the key exists
		if (!$keyRow || empty($keyRow)) return false;

		# Add the key to client's sessions
		\Kanso\Admin\Security\sessionManager::put('KANSO_PASSWORD_KEY', $key);
		
		return true;

	}

	/********************************************************************************
	* PAGE RENDERING
	*******************************************************************************/

	/**
	 * Render the admin page
	 */
	private function renderPage()
	{ 

		# Convert the page request to lowercase
		$this->pageRequest = strtolower($this->pageRequest);

		$vars = [
			'ADMIN_PAGE_TYPE'    => $this->pageRequest,
			'ADMIN_PAGE_TITLE'   => $this->getThePageTitle(),
			'ADMIN_USER_DATA'    => \Kanso\Admin\Security\sessionManager::get('KANSO_ADMIN_DATA'),
			'ADMIN_IS_DASHBOARD' => $this->pageRequest === 'settings' || $this->pageRequest === 'articles' || $this->pageRequest === 'taxonomy' || $this->pageRequest === 'comments' || $this->pageRequest === 'writer',
			'ADMIN_IS_WRITER'    => $this->pageRequest === 'writer',
			'KANSO_ADMIN_URI'    => $this->Kanso->Environment['KANSO_ADMIN_URI'],
			'KANSO_ADMIN_ASSETS' => str_replace($this->Kanso->Environment['DOCUMENT_ROOT'], $this->Kanso->Environment['HTTP_HOST'], $this->Kanso->Environment['KANSO_ADMIN_DIR']).'/assets/',
			'ADMIN_INCLUDES_DIR' => $this->Kanso->Environment['KANSO_ADMIN_DIR'].DIRECTORY_SEPARATOR.'Includes'.DIRECTORY_SEPARATOR,
			'KANSO_VERSION'      => $this->Kanso->Version,
		];
		if ($this->pageRequest === 'settings') $vars = array_merge($vars, $this->buildSettingsVars($vars['ADMIN_USER_DATA']));
		if ($this->pageRequest === 'writer')   $vars = array_merge($vars, $this->buildWriterVars());
		$this->Kanso->render($this->Kanso->Environment['KANSO_ADMIN_DIR'].DIRECTORY_SEPARATOR.'Admin.php', $vars);

	}

	/**
	 * Build default page variables
	 */
	private function buildSettingsVars($ADMIN_USER_DATA) 
	{
		$vars                     = $this->Kanso->Config;
		$vars['thumbnailSizes']   = $this->stringifyThumbnails($vars['KANSO_THUMBNAILS']);
		$vars['themeRadios']      = $this->buildThemeRadios();
		$vars['KansoPages']       = implode(', ', $vars['KANSO_STATIC_PAGES']);
		$vars['authorImg']        = $ADMIN_USER_DATA['thumbnail'] === '' ? '' : '<img src="'.$this->Kanso->Environment['KANSO_IMGS_URL'].$ADMIN_USER_DATA['thumbnail'].'" />';
		$vars['allAuthors']       = $this->CRUD->SELECT("*")->FROM('authors')->FIND_ALL();

		return $vars;
	}

	/**
	 * Build default page variables for the writer
	 */
	private function buildWriterVars() 
	{

		$vars['writer_content']   = $this->writerEntry ? $this->writerEntry['content'] : '';
		$vars['ajax_type']        = $this->writerEntry ? 'writer_save_existing_article' : 'writer_save_new_article';
		$vars['postID']           = $this->writerEntry ? (int)$this->writerEntry['id'] : null;
		$vars['selectedPost']     = !$this->writerEntry ? 'selected' : $this->writerEntry && $this->writerEntry['type'] === 'post' ? 'selected' : '';
		$vars['selectedPage']     = !$this->writerEntry ? '' : $this->writerEntry && $this->writerEntry['type'] === 'page' ? 'selected' : '';
		$vars['the_title']        = $this->writerEntry ? $this->writerEntry['title'] : '';
		$vars['the_category']     = $this->writerEntry ? $this->writerEntry['category']: '';
		$vars['the_tags']         = $this->writerEntry ? $this->writerEntry['tags'] : '';
		$vars['the_excerpt']      = $this->writerEntry ? $this->writerEntry['excerpt'] : '';
		$vars['article_img']      = $this->writerEntry ? $this->createThumbnail() : '';
		$vars['thumbnail']        = !isset($this->writerEntry['thumbnail']) || $this->writerEntry['thumbnail'] === '' ? '' : $this->Kanso->Environment['KANSO_IMGS_URL'].str_replace('_large', '_medium', $this->writerEntry['thumbnail']);
		$vars['hero_active']      = isset($this->writerEntry['thumbnail']) && $this->writerEntry['thumbnail'] !== '' ?  'dz-started' : '';
		$vars['enabledComments']  = isset($this->writerEntry['comments_enabled']) && $this->writerEntry['comments_enabled'] === true ? 'checked' : '';

		return $vars;

	}

	/**
	 * create the image thumbnail for DZ if the article has a hero
	 *
	 * @return string 
	 */
	private function createThumbnail()
	{
		if ($this->writerEntry['thumbnail'] === '') return '';
		$imgURL = $this->Kanso->Environment['KANSO_IMGS_URL'].str_replace('_large', '_medium', $this->writerEntry['thumbnail']);
		return '<div class="dz-preview dz-image-preview dz-processing dz-success"> <div class="dz-details"><img src="'.$imgURL.'"/></div></div>';
	}

	/**
	 * Build theme radio buttons 
	 *
	 * This function builds an HTML valid list of radio buttons
	 * for choosing the active theme for Kanso.
	 *
	 * @return string 
	 */
	private function buildThemeRadios() 
	{
		$radios = '';
		$themes = array_filter(glob($this->Kanso->Environment['KANSO_THEME_DIR'].'/*'), 'is_dir');
		if ($themes) {
			foreach ($themes as $i => $themeName) {
				$themeName = substr($themeName, strrpos($themeName, '/') + 1);
				$checked     = ($themeName === $this->Kanso->Config['KANSO_THEME_NAME'] ? 'checked' : '');
				$radios .= '
				<div class="radio-wrap">
					<input id="themRadio'.$i.'" class="js-kanso-theme" type="radio" name="theme" '.$checked.' value="'.$themeName.'">
					<label class="radio small" for="themRadio'.$i.'"></label>
					<p class="label">'.$themeName.'</p>
				</div>
				';
			}
		}
		return $radios;
	}

	/**
	 * Convert array of thumbnail sizes into a comma separated list
	 *
	 * @return string 
	 */
	private function stringifyThumbnails($thumnails) 
	{
		$thumnailList = '';
		foreach ($thumnails as $i => $size) {
			if (is_array($size)) {
				$thumnailList .= $size[0].' '.$size[1].', ';
			}
			else {
				$thumnailList .= $size.', ';
			}
		}
		return rtrim($thumnailList, ', ');
	}

	/**
	 * Get the page title
	 *
	 * @return string 
	 */
	private function getThePageTitle() 
	{
		$pageRequest = $this->pageRequest;
		if ($pageRequest === 'writer')               return $this->writerEntry ? 'Edit | '.$this->writerEntry['title'] : 'Write | New Article';
		else if ($pageRequest === 'tags')            return 'Tags & Categories | Kanso';
		else if ($pageRequest === 'articles')        return 'Articles | Kanso';
		else if ($pageRequest === 'settings')        return 'Settings | Kanso';
		else if ($pageRequest === 'reset_password')  return 'Reset Your Password | Kanso';
		else if ($pageRequest === 'forgot_username') return 'Forgot Your Username | Kanso';
		else if ($pageRequest === 'forgot_password') return 'Forgot Your Password | Kanso';
		else if ($pageRequest === 'login')           return 'Login | Kanso';
	}

}