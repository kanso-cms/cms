<?php

namespace Kanso\Admin\Controllers;

/**
 * GET/POST Controller for custom dashboard pages
 *
 * This controller is responsible for managing all
 * GET and POST requests made to the admin panel custom dashboard pages
 * e.g if you add custom pages
 *
 * The class is instantiated directly by the router with 
 * the pagename in the constructor. dispatch() is then 
 * called by the router which will call the appropriate 
 * method based on the pagename variable.
 */
class Custom
{
	/**
	 * @var bool
	 */
	protected $isLoggedIn;

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
	 * @param string    $model    A class path as a string
	 *
	 */
	public function __construct($model)
	{
		# Set Kanso's is_admin
		\Kanso\Kanso::getInstance()->is_admin = true;

		# Set Kanso's Query object to 'admin'
        \Kanso\Kanso::getInstance()->Query->filterPosts('admin');

		# Save the clients login status to boolean
		$this->isLoggedIn = \Kanso\Kanso::getInstance()->Admin->isLoggedIn();

		# Set the page name for the public admin class
		\Kanso\Kanso::getInstance()->Admin->setPageName('custom');

		# Is this a POST request?
		$this->is_post = \Kanso\Kanso::getInstance()->Admin->isPost();

		# Set the model
		$this->loadModel($model);

		# Fire the adminInit event
		\Kanso\Events::fire('adminInit', 'custom');	
	}

	/**
	 * Dispatcher
	 *
	 * This method is called directly from the router after it
	 * instantiates this class with model.
	 * 
	 */
	public function dispatch()
	{

		# Not logged in
		if (!$this->isLoggedIn) {
			return \Kanso\Kanso::getInstance()->notFound();
		}

		# Ajax POST request
		if (\Kanso\Kanso::getInstance()->Request->isAjax() && $this->validateAjax()) {
			$method    = [$this->model, 'onAjax'];
			$_response = false;
			if (is_callable($method)) $_response = call_user_func($method);
			if ($_response) {
				$Response = \Kanso\Kanso::getInstance()->Response;
            	$Response->setheaders(['Content-Type' => 'application/json']);
            	$Response->setBody( json_encode( ['response' => 'processed', 'details' => $response] ) );
            	return;
			}
			else {
				return \Kanso\Kanso::getInstance()->Response->setStatus(404);        		
			}
		}

		# Regular POST requests
		else if ($this->is_post) {
			$method = [$this->model, 'onPost'];
			if (is_callable($method)) $vars['POST_RESPONSE'] = call_user_func($method);
		}

		# Default variables
		$vars = \Kanso\Kanso::getInstance()->Admin->getPageVars();

		# Template to include
		$vars['CUSTOM_TEMPLATE'] = $this->model->template();

		# GET request variables to pass to the view
		$getMethod = [$this->model, 'onGet'];
		if (is_callable($getMethod)) {
			$getVars = call_user_func($getMethod);
			if (is_array($getVars)) {
				$vars = array_merge($vars, $getVars);
			}
		}

		# Render the page
		\Kanso\Kanso::getInstance()->Admin->pageVars($vars)->render();
	}

	/**
     * Load the model class 
     *
     * @return class
     */
	private function loadModel($model)
	{
		# grab all parts based on a / separator 
		$parts = explode('/', $model);

		# collect the last index of the array
		$last = end($parts);

		# grab the controller name and method call
		$segments = explode('@', $last);

		# instantiate controller
		$this->model = new $segments[0]();
	}

	/**
     * Validate the request 
     *
     * Note this function is invoked directly from the router for all POST
     * requests to the admin panel. It validates the request.
     *
     * @return mixed
     */
    public function validateAjax() 
    {
        # A valid user must exist
        if (!\Kanso\Kanso::getInstance()->Gatekeeper->getUser()) return false;

        # Validate that the request came from the admin panel
        if (!$this->validatereferrer()) return false;

        return true;
    }

    /**
     * Validate the refferal came from the admin panel
     *
     * @return bool
     */
    private function validatereferrer() 
    {
        $referrer = \Kanso\Kanso::getInstance()->Cookie->getReferrer();
        if (!$referrer) return false;
        if (strpos($referrer, \Kanso\Kanso::getInstance()->Environment['KANSO_ADMIN_URI']) !== false) return true;
        return false;
    }
}