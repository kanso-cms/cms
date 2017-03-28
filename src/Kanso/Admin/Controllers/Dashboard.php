<?php

namespace Kanso\Admin\Controllers;

/**
 * GET/POST Controller for dashboard pages
 *
 * This controller is responsible for managing all
 * GET and POST requests made to the admin panel dashboard pages
 * articles, tags, writer etc...
 *
 * The class is instantiated directly by the router with 
 * the pagename in the constructor. dispatch() is then 
 * called by the router which will call the appropriate 
 * method based on the pagename variable.
 */
class Dashboard
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
	 * @var class
	 */
	protected $model;

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
	 * Load the articles page
	 *
	 */
	private function articles() 
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->model = new \Kanso\Admin\Models\Articles;
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Load the pages page
	 *
	 */
	private function pages() 
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->model = new \Kanso\Admin\Models\Pages;
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Load the tags page
	 *
	 */
	private function tags() 
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->model = new \Kanso\Admin\Models\Tags;
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Load the categories page
	 *
	 */
	private function categories() 
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->model = new \Kanso\Admin\Models\Categories;
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Load the comments page
	 *
	 */
	private function comments() 
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->model = new \Kanso\Admin\Models\Comments;
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Load the comment users page
	 *
	 */
	private function commentUsers() 
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->model = new \Kanso\Admin\Models\CommentUsers;
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Load the writer page
	 *
	 */
	private function writer()
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->model = new \Kanso\Admin\Models\Writer;
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Load the settings pages
	 *
	 * @param string    $tab    The current tab (optional)
	 * 
	 */
	private function settings($tab = 'account') 
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->model = new \Kanso\Admin\Models\Settings($tab);
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
	}

	/**
	 * Load the account settings page
	 */
	private function settingsAccount() 
	{
		$this->settings('account');
	}

	/**
	 * Load the author settings page
	 */
	private function settingsAuthor() 
	{
		$this->settings('author');
	}

	/**
	 * Load the kanso settings page
	 */
	private function settingsKanso() 
	{
		# User must be admin
		if (!\Kanso\Kanso::getInstance()->Gatekeeper->isAdmin()) {
			return \Kanso\Kanso::getInstance()->notFound();
		}
		$this->settings('kanso');
	}

	/**
	 * Load the user settings page
	 */
	private function settingsUsers()
	{
		# User must be admin
		if (!\Kanso\Kanso::getInstance()->Gatekeeper->isAdmin()) {
			return \Kanso\Kanso::getInstance()->notFound();
		}
		$this->settings('users');
	}

	/**
	 * Load the tools settings page
	 */
	private function settingsTools()
	{
		# User must be admin
		if (!\Kanso\Kanso::getInstance()->Gatekeeper->isAdmin()) {
			return \Kanso\Kanso::getInstance()->notFound();
		}
		$this->settings('tools');
	}


	/**
	 * Load the media library
	 *
	 */
	private function media()
	{
		# If the user is logged in, load the model
		# and render the page
		if ($this->isLoggedIn) {
			$this->renderPage();
		}
		# 404
		else {
			\Kanso\Kanso::getInstance()->notFound();
		}
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
		$vars = [];

		# If this was a POST request, we can process the POST 
		# via the model and pass the response to the view (template).
		if ($this->is_post) {
			$parsePost = [$this->model, 'parsePost'];
			if (is_callable($parsePost)) {
				$vars['POST_RESPONSE'] = $this->model->parsePost();
			}
		}

		# Parse any GET/page requests and variables
		$parseGet = [$this->model, 'parseGet'];
		if (is_callable($parseGet)) {
			$getVars = $this->model->parseGet();
			if (is_array($getVars)) {
				$vars = array_merge($vars, $getVars);
			}
		}

		# Render the page
		\Kanso\Kanso::getInstance()->Admin->pageVars($vars)->render();

	}
}