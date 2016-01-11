<?php

namespace Kanso\Admin\Controllers;

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
class GetController
{

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
	 * @var array|false
	 */
	protected $writerEntry;


	/********************************************************************************
	* PUBLIC INITIALIZATION
	*******************************************************************************/

	/**
	 * Private Constructor
	 *
	 * Called from dispatch(), this will initialize the current class
	 * variables and other dependancies. It then dispatches the request
	 * to the appropriate method or 404 if the method doesn't exist.
	 *
	 * @param string    $pageRequest    The page type needs to be dispatched    
	 */
	public function __construct($pageRequest)
	{

		# Set Kanso's is_admin
		\Kanso\Kanso::getInstance()->is_admin = true;

		# Fire the adminInit event
		\Kanso\Events::fire('adminInit');

		# Set the page request type
		$this->pageRequest = $pageRequest;

		# Save the clients login boolean
		$this->isLoggedIn = \Kanso\Kanso::getInstance()->Gatekeeper->isLoggedIn();

		# Save the default homepage URL for redirecting
		$this->adminHomepage = \Kanso\Kanso::getInstance()->Environment['HTTP_HOST'].DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'articles'.DIRECTORY_SEPARATOR;

	}

	public function dispatch()
	{
		# Dispatch the method if it is callable
		$method = [$this, $this->pageRequest];

		if (is_callable($method)) {

			call_user_func($method);
		}
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
			$this->redirect($this->adminHomepage);
		}
		else {
			$this->renderPage(); 
		}
	}

	/**
	 * Log the client out 
	 */
	private function logOut() 
	{
		if ($this->isLoggedIn) {
			\Kanso\Kanso::getInstance()->Gatekeeper->logout();
			$this->redirect(\Kanso\Kanso::getInstance()->Environment['HTTP_HOST']);
		}
		else {
			$this->notFound();
		}
	}

	/**
	 * Load the register page or 404
	 *
	 */
	private function register() 
	{
		if ($this->validateRegisterRequest()) {
			$this->renderPage();
		}
		else {
			$this->notFound();
		}
	}

	/**
	 * Load the forgot username page
	 *
	 */
	private function forgotUserName() 
	{
		if ($this->isLoggedIn) {
			$this->redirect($this->adminHomepage);
		}
		else {
			$this->renderPage();
		}
	}

	/**
	 * Load the forgot password page
	 *
	 */
	private function forgotPassword() 
	{

		if ($this->isLoggedIn) {
			$this->redirect($this->adminHomepage);
		}
		else {
			$this->renderPage();
		}
	}

	/**
	 * Load the reset password page
	 *
	 */
	private function resetPassword() 
	{
		if (!$this->isLoggedIn && $this->validateResetPasswordRequest()) {
			$this->renderPage();
		}
		else {
			$this->notFound();
		}
	}

	/**
	 * Load the settings page
	 *
	 */
	private function settings() 
	{
		if ($this->isLoggedIn && $this->validateSettingsRequest()) {
			$this->renderPage();
		}
		else {
			$this->notFound();
		}
	}

	/**
	 * Load the articles page
	 *
	 */
	private function articles() 
	{
		if ($this->isLoggedIn) {
			$this->renderPage();
		}
		else {
			$this->notFound();
		}
	}

	/**
	 * Load the tags page
	 *
	 */
	private function taxonomy() 
	{
		if ($this->isLoggedIn) {
			$this->renderPage();
		}
		else {
			$this->notFound();
		}
	}

	 /**
	 * Load the comments page
	 *
	 */
	private function comments() 
	{
		if ($this->isLoggedIn) {
			$this->renderPage();
		}
		else {
			$this->notFound();
		}
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
			$this->notFound();
		}
	}


	/********************************************************************************
	* PRIVATE RESPONSE HELPERS
	*******************************************************************************/

	private function redirect($url)
	{
		\Kanso\Kanso::getInstance()->redirect($url);
	}

	private function notFound()
	{
		\Kanso\Kanso::getInstance()->notFound();
	}


	/********************************************************************************
	* PRIVATE REQUEST VALIDATION
	*******************************************************************************/

	private function validateSettingsRequest()
	{
		# Save a local Environment
		$env = \Kanso\Kanso::getInstance()->Environment;

		# Valid tabs 
		$availableTabs = ['account', 'author', 'kanso', 'users', 'tools'];

		# Filter the tabs
		$availableTabs = \Kanso\Filters::apply('adminSettingsTabs', $availableTabs);

		# Save the url locally
		$url = $env['REQUEST_URL'];

		# Is this a request for an article edit or a new article?
		$request = str_replace($env['HTTP_HOST'].'/admin/settings/', "", $url);

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

		# Save a local query builder
		$query = \Kanso\Kanso::getInstance()->Database->Builder();

		# Save a local Environment
		$env = \Kanso\Kanso::getInstance()->Environment;

		# Save the url locally
		$url = $env['REQUEST_URL'];
		
		# Is this a request for an article edit or a new article?
		$request = str_replace($env['HTTP_HOST'].'/admin/write', "", $url);
		
		# Validate the request
		if ($request === '' || $request === '/') return true;

		# Get the slug
		$slug = trim($request, '/').'/';

		# Get the article based on the slug
		$articleRow = $query->SELECT('*')->FROM('posts')->WHERE('slug', '=', $slug)->FIND();
		
		# Validate the article exists
		if (!$articleRow || empty($articleRow)) return false;

		# Get the content
		$content = $query->SELECT('*')->FROM('content_to_posts')->WHERE('post_id', '=', (int)$articleRow['id'])->FIND();
		
		# Get the category
		$category = $query->SELECT('*')->FROM('categories')->WHERE('id', '=', (int)$articleRow['category_id'])->FIND();

		# Get the tags
		$tags = $query->SELECT('tags.*')->FROM('tags_to_posts')->LEFT_JOIN_ON('tags', 'tags.id = tags_to_posts.tag_id' )->WHERE('post_id', '=', (int)$articleRow['id'])->FIND_ALL();
		
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

		# Get the token in the url
		$token = \Kanso\Kanso::getInstance()->Request->fetch('query');

		# If no token was given 404
		if (!$token || trim($token) === '' || $token === 'null' ) return false;

		# Get the user based on their token
		$user = \Kanso\Kanso::getInstance()->Database->Builder()->SELECT('*')->FROM('users')->WHERE('kanso_register_key', '=', $token)->ROW();

		# Validate the token exists
		if (!$user) return false;

		# Add the token to client's session
		\Kanso\Kanso::getInstance()->Session->put('session_kanso_register_key', $token);

		return true;
		
	}

	/**
	 * Reset Password Request Validator
	 * 
	 * Reset Password requests need a refferal token. Check if it exists and is valid
	 * @return bool
	 */
	private function validateResetPasswordRequest() 
	{

		# Logged in users can't reset their password
		if ($this->isLoggedIn) return false;

		# Get the token in the url
		$token = \Kanso\Kanso::getInstance()->Request->fetch('query');

		# If no token was given 404
		if (!$token || trim($token) === '' || $token === 'null' ) return false;

		# Get the user based on their token
		$user = \Kanso\Kanso::getInstance()->Database->Builder()->SELECT('*')->FROM('users')->WHERE('kanso_password_key', '=', $token)->ROW();

		# Validate the user exists
		if (!$user) return false;

		# Add the token to client's session
		\Kanso\Kanso::getInstance()->Session->put('session_kanso_password_key', $token);
		
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

		# Render the page with variables
		$vars = [
			'ADMIN_PAGE_TYPE'    => $this->pageRequest,
			'ADMIN_WRITER_ENTRY' => $this->writerEntry,
		];

		\Kanso\Kanso::getInstance()->render(\Kanso\Kanso::getInstance()->Environment['KANSO_ADMIN_DIR'].DIRECTORY_SEPARATOR.'Admin.php', $vars);

	}
}