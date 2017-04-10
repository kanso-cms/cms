<?php

namespace Kanso\Admin\Controllers;

/**
 * Ajax POST Controller
 *
 * This controller is responsible for managing AJAX requests made to 
 * the admin panel and serving a JSON response to the client.
 *
 * The class is instantiated directly by the router with 
 * the pagename in the constructor. Dispatch is then 
 * called by the router which will call the appropriate 
 * method from the pagename variable.
 */
class Ajax
{

	/**
	 * @var bool
	 */
	private $isLoggedIn;

	/**
	 * @var class
	 */
	private $model;

	/**
	 * @var string
	 */
	private $requestName;

	/********************************************************************************
	* PUBLIC INITIALIZATION
	*******************************************************************************/

	/**
	 * Constructor
	 *
	 * Called from dispatch(), this will initialize the current class
	 * variables and other dependencies. It then dispatches the request
	 * to the appropriate method or 404 if the method doesn't exist.
	 *
	 * @param string    $requestName    The page type needs to be dispatched    
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

		# Fire the adminInit event
		\Kanso\Events::fire('adminInit');
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
		# Make sure this is a valid Ajax request
		if (!$this->isLoggedIn || !\Kanso\Kanso::getInstance()->Request->isAjax() || !$this->validatePOST()) {
			return \Kanso\Kanso::getInstance()->Response->setStatus(404);
		}

		# Dispatch the method if it is callable
		$method   = [$this, $this->requestName];
		$response = false;
		if (is_callable($method)) $response = call_user_func($method);
		
		# Send the response to the client
		$this->sendResponse($response);
	}

	/********************************************************************************
	* PRIVATE DISPATCHERS
	*******************************************************************************/
	
	private function writerAjax()
	{
		$model = new \Kanso\Admin\Models\WriterAjax;
		return $model->dispatch();
	}

	private function mediaLibrary()
	{
		$model = new \Kanso\Admin\Models\MediaLibrary;
		return $model->dispatch();
	}


	/********************************************************************************
	* Response
	*******************************************************************************/

	/**
	 * Render the admin page
	 */
	private function sendResponse($_response)
	{

        # If the request was processed, return a valid JSON object
        if ($_response || is_array($_response)) {
            $Response = \Kanso\Kanso::getInstance()->Response;
            $Response->setheaders(['Content-Type' => 'application/json']);
            $Response->setBody( json_encode( ['response' => 'processed', 'details' => $_response] ) );
            return;
        }

        # 404 on fallback
        \Kanso\Kanso::getInstance()->Response->setStatus(404);

	}

	/********************************************************************************
	* PRIVATE VALIDATORS
	*******************************************************************************/

	/**
     * Validate the request 
     *
     * Note this function is invoked directly from the router for all POST
     * requests to the admin panel. It validates the request.
     *
     * @return mixed
     */
    private function validatePOST() 
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
        $referrer = \Kanso\Kanso::getInstance()->Session->getReferrer();
        if (!$referrer) return false;
        if (strpos($referrer, \Kanso\Kanso::getInstance()->Environment['KANSO_ADMIN_URL']) !== false) return true;
        return false;
    }

	
  
	
}