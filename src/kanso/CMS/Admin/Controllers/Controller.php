<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Admin\Controllers;

use Closure;
use Kanso\Kanso;
use Kanso\Framework\Http\Request\Request;
use Kanso\Framework\Http\Response\Response;
use Kanso\Framework\Utility\Callback;
use Kanso\Framework\Database\Query\Builder;
use Kanso\Framework\Utility\GUMP;
use Kanso\CMS\Auth\Gatekeeper;
use Kanso\CMS\Wrappers\Managers\UserManager;

/**
 * Admin panel base controller
 *
 * @author Joe J. Howard
 */
abstract class Controller
{
	/**
	 * The name of the request
	 *
	 * @var string
	 */
	protected $requestName;

	/**
	 * Request instance
	 *
	 * @var \Kanso\Framework\Http\Request\Request
	 */
	protected $request;

	/**
	 * Response instance
	 *
	 * @var \Kanso\Framework\Http\Response\Response
	 */
	protected $response;

	/**
	 * Response instance
	 *
	 * @var \Kanso\Framework\Http\Response\Response
	 */
	protected $gateKeeper;

	/**
	 * Next middleware layer
	 *
	 * @var \Closure
	 */
	protected $next;

	/**
	 * Is logged in
	 *
	 * @var bool
	 */
	protected $isLoggedIn;

	/**
	 * Model
	 *
	 * @var mixed
	 */
	protected $model;

	/**
	 * Variables to be passed to the view
	 *
	 * @var array
	 */
	protected $viewVars = [];

	/**
     * Constructor
     *
     * @access public
     */
    public function __construct(Request $request, Response $response, Closure $next, string $requestName)
    {
    	$this->request = $request;

    	$this->response = $response;

    	$this->next = $next;

    	$this->requestName = $requestName;

    	$this->isLoggedIn = $this->gatekeeper()->isLoggedIn() && $this->gatekeeper()->isAdmin();

    	$this->model = $this->loadModel($this->getModelClass(), $this->getModelArgs());
    }

    protected function gatekeeper(): Gatekeeper
    {
    	return Kanso::instance()->Gatekeeper;
    }

    protected function userManager(): UserManager
    {
    	return Kanso::instance()->UserManager;
    }

    protected function SQL(): Builder
    {
    	return Kanso::instance()->Database->connection()->builder();
    }

    protected function validation(): GUMP
    {
    	return new GUMP;
    }

    /**
	 * Returns the model class instance
	 *
	 * @access protected
	 * @param  string    $modelClass Full namespaced class name of the model
	 * @return mixed
	 */
	protected function loadModel(string $class, array $args)
	{
		return Callback::newClass($class, $args);
	}

	/**
	 * Returns the args passed to the model constructor
	 *
	 * @access protected
	 * @return array
	 */
	protected function getModelArgs(): array
	{
		return [
			$this->request,
			$this->response,
			$this->gatekeeper(),
			$this->userManager(),
			$this->SQL(),
			$this->validation(),
			$this->isLoggedIn,
			$this->requestName
		];
	}

    /**
	 * Load the model
	 *
	 * @access protected
	 */
	abstract protected function getModelClass(): string;

	/**
	 * Saves the row item
	 *
	 * @access public
     * @return bool
	 */
	public function dispatch()
	{
		$this->response->cache()->disable();

		$this->response->CDN()->disable();
		
		# Make sure this is a valid Ajax request
		if ($this->request->isAjax())
		{
			$this->response->format()->set('application/json');

			$this->response->body()->clear();

			$onAJAX = [$this->model, 'onAJAX'];

			if (is_callable($onAJAX))
			{
				$response = call_user_func($onAJAX);

				if ($response !== false)
				{
					$this->response->status()->set(200);

					$this->response->body()->set(json_encode(['response' => $response]));

					return;
				}
			}

			return $this->response->notFound();
		}

		# Regular POST requests
		else if ($this->request->isPost())
		{
			$onPOST = [$this->model, 'onPOST'];

			if (is_callable($onPOST))
			{
				$response = call_user_func($onPOST);

				if ($response === false)
				{
					return $this->response->notFound();
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
				return $this->response->notFound();
			}

			$this->viewVars = array_merge($this->viewVars, $response);

			return $this->render();
		}

		return $this->response->notFound();

	}

	/**
     * Render the admin panel
     *
     * @access protected
     */
    protected function render()
    {
    	$vars = $this->viewVars;

    	$vars['USER'] = $this->gatekeeper()->getUser();

    	$vars['ADMIN_PAGE_TYPE'] = $this->requestName;

    	$vars['ACCESS_TOKEN'] = $this->response->session()->token()->get();

        $template = KANSO_DIR.DIRECTORY_SEPARATOR.'CMS'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Views'.DIRECTORY_SEPARATOR.'admin.php';

        $this->response->body()->set($this->response->view()->display($template, $vars));
    }

}