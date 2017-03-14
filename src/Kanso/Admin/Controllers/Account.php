<?php

namespace Kanso\Admin\Controllers;

/**
 * GET/POST Controller for account pages
 *
 * This controller is responsible for managing all
 * GET and POST requests made to the admin panel account pages
 * login, register, forgot password etc...
 *
 * The class is instantiated directly by the router with 
 * the pagename in the constructor. Dispatch is then 
 * called by the router which will call the appropriate 
 * method from the pagename variable.
 */
class Account
{

	/**
	 * @var boolean
	 */
	protected $isLoggedIn;

	/**
	 * @var string
	 */
	protected $requestName;

	/**
	 * @var string
	 */
	protected $adminHomepage;

	/**
	 * @var \Kanso\Admin\Models\Accounts
	 */
	protected $model;

	/**
	 * @var mixed
	 */
	private $postResponse = false;

	/**
	 * @var boolean
	 */
	private $is_post = false;


	/********************************************************************************
	* PUBLIC INITIALIZATION
	*******************************************************************************/

	/**
	 * Constructor
	 *
	 * Called from the router this will initialize the current class
	 * variables and other dependencies.
	 *
	 * @param string    $requestName    The name of the current request type
	 *
	 */
	public function __construct($requestName)
	{
		# Set Kanso's is_admin
		\Kanso\Kanso::getInstance()->is_admin = true;

		# Set Kanso's Query object to 'admin'
        \Kanso\Kanso::getInstance()->Query->filterPosts('admin');

		# Set the page request type
		$this->requestName = $requestName;

		# Save the clients login status to boolean
		$this->isLoggedIn = \Kanso\Kanso::getInstance()->Admin->isLoggedIn();

		# Save the default homepage URL for redirecting
		$this->adminHomepage = \Kanso\Kanso::getInstance()->Environment['KANSO_ADMIN_URI'].DIRECTORY_SEPARATOR.'articles'.DIRECTORY_SEPARATOR;
		
		# Load the model
		$this->model = new \Kanso\Admin\Models\Accounts($requestName);

		# Set the page name for the public admin class
		\Kanso\Kanso::getInstance()->Admin->setPageName($requestName);

		# Is this a POST request?
		$this->is_post = \Kanso\Kanso::getInstance()->Admin->isPost();

		# Fire the adminInit event
		\Kanso\Events::fire('adminInit', $requestName);
	}

	/**
	 * Dispatcher
	 *
	 * This method is called directly from the router after it
	 * instantiates this class with the request name.
	 * The $requestName variable must be the same 
	 * as the method that gets called.
	 * 
	 */
	public function dispatch()
	{
		# Create a callable method from the requestName
		$method = [$this, $this->requestName];

		# If the method is callable run it
		if (is_callable($method)) {
			return call_user_func($method);
		}

		# 404 on fallback
		\Kanso\Kanso::getInstance()->notFound();
	}

	/********************************************************************************
	* PRIVATE DISPATCHERS
	*******************************************************************************/

	/**
	 * Run the login validation
	 *
	 */
	private function login() 
	{
		# If the user is logged in already, redirect them to
		# the admin homepage
		if ($this->isLoggedIn) {
			return \Kanso\Kanso::getInstance()->redirect($this->adminHomepage);
		}

		# Otherwise if this a POST request, parse
		# the POST variables and validate the login request
		# If it's valid log the client in and redirect them
		else if ($this->is_post) {
			$this->postResponse = $this->model->login();
			if ($this->postResponse) {
				return \Kanso\Kanso::getInstance()->redirect($this->adminHomepage);
			}
		}

		# Render the page
		$this->renderPage();
	}

	/**
	 * Run the logout validation
	 *
	 */
	private function logout() 
	{
		# If the user is logged in, log them out
		# and redirect to website homepage
		if ($this->isLoggedIn) {
			\Kanso\Kanso::getInstance()->Gatekeeper->logout();
			\Kanso\Kanso::getInstance()->redirect(\Kanso\Kanso::getInstance()->Environment['HTTP_HOST']);
		}
		# Otherwise 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Run the forgot password validation
	 *
	 */
	private function forgotpassword() 
	{
		# If the user is logged in, redirect them
		# to the admin homepage
		if ($this->isLoggedIn) {
			return \Kanso\Kanso::getInstance()->redirect($this->adminHomepage);
		}
		# If this is a POST request parse and 
		# validate the POST variables
		else if ($this->is_post) {
			$this->postResponse = $this->model->forgotpassword();
		}
		# Render the page
		$this->renderPage();
	}

	/**
	 * Run the forgot username validation
	 *
	 */
	private function forgotusername() 
	{
		# If the user is logged in, redirect them
		# to the admin homepage
		if ($this->isLoggedIn) {
			return \Kanso\Kanso::getInstance()->redirect($this->adminHomepage);
		}
		# If this is a POST request parse and 
		# validate the POST variables
		else if ($this->is_post) {
			$this->postResponse = $this->model->forgotusername();
		}
		# Render the page
		$this->renderPage();
	}

	/**
	 * Run the register validation
	 *
	 */
	public function register()
	{
		# If the user is logged in they can't register 
		if ($this->isLoggedIn) {
			return \Kanso\Kanso::getInstance()->redirect($this->adminHomepage);
		}

		# Token 
		$token = '';

		# If this is a POST request get the token from the referrer
		if ($this->is_post) {
			# Get the token from the referrer
			$_token = \Kanso\Kanso::getInstance()->Session->getReferrer();
			if (!$_token) return \Kanso\Kanso::getInstance()->notFound();
			$_token = explode('token=', $_token);
			if (!isset($_token[1])) return \Kanso\Kanso::getInstance()->notFound();
			$token = $_token[1];
		}

		# Otherwise get it from the URL e.g example.com/register?token=4hjfds3
		else {
			# Get the token in the url
			$token = \Kanso\Kanso::getInstance()->Request->queries('token');
		}

		# If no token was given 404
		if (!$token || trim($token) === '' || $token === 'null' ) return \Kanso\Kanso::getInstance()->notFound();

		# Get the user based on their token
		$user = \Kanso\Kanso::getInstance()->Database->Builder()->SELECT('*')->FROM('users')->WHERE('kanso_register_key', '=', $token)->ROW();

		# Validate the token exists
		if (!$user) return \Kanso\Kanso::getInstance()->notFound();

		# Add the token to client's session
		\Kanso\Kanso::getInstance()->Session->put('session_kanso_register_key', $token);

		# If this is a post request, validate it
		# and redirect and login if the registration was successful
		if ($this->is_post) {
			$this->postResponse = $this->model->register($token);
			if ($this->postResponse === true) {
				return \Kanso\Kanso::getInstance()->redirect($this->adminHomepage);
			}
		}

		# Render the page
		$this->renderPage();
	}

    /********************************************************************************
	* PAGE RENDERING
	*******************************************************************************/

	/**
	 * Render the admin page
	 *
	 */
	private function renderPage()
	{

		# What are the response variables from the POST validation/parsing ?
		$vars = ['POST_RESPONSE' => $this->postResponse];
		
		\Kanso\Kanso::getInstance()->Admin->pageVars($vars)->render();
	}

}