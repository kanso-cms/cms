<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\controllers;

use kanso\framework\mvc\controller\Controller;

/**
 * Admin panel base controller
 *
 * @author Joe J. Howard
 */
abstract class BaseController extends Controller
{
	/**
	 * The name of the request
	 *
	 * @var string
	 */
	protected $requestName;

	/**
	 * Variables to be passed to the view
	 *
	 * @var array
	 */
	protected $viewVars = [];

	/**
	 * Initialize the admin panel
	 *
	 * @access protected
	 * @param  string $name Identifying name of the requested page
	 */
	protected function init(string $name)
	{
		# Save the request name
		$this->requestName = $name;

		# Add to query
		$this->Query->requestType = 'admin';

		# Initialize the model
		$this->model->init($this->requestName);

		# Admin panel has been initialized
		$this->Events->fire('adminInit', $this->requestName);
	}

	/**
	 * Check if the client is logged in
	 *
	 * @access protected
	 * @return bool 
	 */
	protected function isLoggedIn(): bool
	{
		return $this->Gatekeeper->isLoggedIn() && $this->Gatekeeper->isAdmin();
	}

	/**
	 * Dispatch the request
	 *
	 * @access protected
	 */
	protected function dispatch()
	{
		# Disabled HTTP caching
		$this->Response->cache()->disable();

		# Disabled the CDN
		$this->Response->CDN()->disable();
		
		# Make sure this is a valid Ajax request
		if ($this->Request->isAjax())
		{
			$this->Response->format()->set('application/json');

			$this->Response->body()->clear();

			$onAJAX = [$this->model, 'onAJAX'];

			if (is_callable($onAJAX))
			{
				$response = call_user_func($onAJAX);

				if ($response !== false)
				{
					$this->Response->status()->set(200);

					$this->Response->body()->set(json_encode(['response' => $response]));

					return;
				}
			}

			return $this->Response->notFound();
		}

		# Regular POST requests
		else if ($this->Request->isPost())
		{
			$onPOST = [$this->model, 'onPOST'];

			if (is_callable($onPOST))
			{
				$response = call_user_func($onPOST);

				if ($response === false)
				{
					return $this->Response->notFound();
				}

				$this->viewVars['POST_RESPONSE'] = $response;
			}
		}

		$onGET = [$this->model, 'onGET'];

		if (is_callable($onGET))
		{
			$response = call_user_func($onGET);

			if ($response === false || !is_array($response))
			{
				return $this->Response->notFound();
			}

			$this->viewVars = array_merge($this->viewVars, $response);

			return $this->render();
		}

		return $this->Response->notFound();
	}

	/**
     * Render the admin panel
     *
     * @access protected
     */
    protected function render()
    {
    	$vars = $this->viewVars;

    	$vars['USER'] = $this->Gatekeeper->getUser();

    	$vars['ADMIN_PAGE_TYPE'] = $this->requestName;

    	$vars['ACCESS_TOKEN'] = $this->Response->session()->token()->get();

        $template = KANSO_DIR.DIRECTORY_SEPARATOR.'cms'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'admin.php';

        $this->Response->body()->set($this->Response->view()->display($template, $vars));
    }
}