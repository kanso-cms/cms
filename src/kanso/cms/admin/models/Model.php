<?php

namespace kanso\cms\admin\models;

use Closure;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\database\query\Builder;
use kanso\framework\utility\GUMP;
use kanso\cms\auth\Gatekeeper;
use kanso\cms\wrappers\managers\UserManager;

/**
 * Model base class
 *
 * @author Joe J. Howard
 */
abstract class Model
{
	/**
	 * Request instance
	 *
	 * @var \kanso\framework\http\request\Request
	 */
	protected $request;

	/**
	 * Response instance
	 *
	 * @var \kanso\framework\http\response\Response
	 */
	protected $response;

	/**
	 * Gatekeeper instance
	 *
	 * @var \kanso\cms\auth\Gatekeeper
	 */
	protected $gatekeeper;

	/**
	 * User manager instance
	 *
	 * @var \kanso\cms\wrappers\managers\UserManager
	 */
	protected $userManager;

	/**
	 * SQL query builder
	 *
	 * @var \kanso\framework\database\query\Builder
	 */
	protected $SQL;

	/**
	 * POST validator
	 *
     * @var kanso\framework\utility\GUMP
     */
    protected $validation;

	/**
	 * Is the client logged in
	 *
	 * @var bool
	 */
	protected $isLoggedIn;

	/**
	 * Array of $_POST
	 *
	 * @var array
	 */
	protected $post;

	/**
	 * The name of the request
	 *
	 * @var string
	 */
	protected $requestName;

	/**
     * Constructor
     *
     * @param \kanso\framework\http\request\Request    $request     Request object instance
     * @param \kanso\framework\http\response\Response  $response    Response object instance
     * @param \kanso\cms\auth\Gatekeeper               $gatekeeper  CMS Gatekeeper instance
     * @param \kanso\cms\wrappers\managers\UserManager $userManager CMS User manager instance
     * @param \kanso\framework\database\query\Builder  $SQL         SQL query builder instance
     * @param \kanso\framework\utility\GUMP            $validation  GUMP validatior
     * @param bool   								   $isLoggedIn  Is the HTTP client logged in to the admin panel ?
     * @param string                                   $requestName The request name (trigger) from the router
     */
    public function __construct(Request $request, Response $response, Gatekeeper $gatekeeper, UserManager $userManager, Builder $SQL, GUMP $validation, bool $isLoggedIn, string $requestName)
    {
    	$this->request = $request;

    	$this->response = $response;

    	$this->gatekeeper = $gatekeeper;

    	$this->SQL = $SQL;

    	$this->validation = $validation;

    	$this->isLoggedIn = $isLoggedIn;

    	$this->userManager = $userManager;

    	$this->requestName = $requestName;

    	$this->post = $this->request->fetch();
    }

    /**
	 * Load the model
	 *
	 * @access protected
	 */
	abstract public function onPOST();

	/**
	 * Load the model
	 *
	 * @access protected
	 */
	abstract public function onAJAX();

	/**
	 * Load the model
	 *
	 * @access protected
	 */
	abstract public function onGET();

	/**
	 * Returns the values required to display a POST
	 * response message
	 *
	 * @access protected
	 * @param  string    $class HTML message classname
	 * @param  string    $msg   Text to go inside the message element
	 * @return array
	 */
	protected function postMessage(string $class, string $msg): array
	{
		if ($class === 'danger')
		{
			$icon = 'times';
		}
		else if ($class === 'success')
		{
			$icon = 'check';
		}
		else if ($class === 'info')
		{
			$icon = 'info-circle';
		}
		else if ($class === 'warning')
		{
			$icon = 'exclamation-triangle';
		}

		return
		[
			'class' => $class, 
			'icon'  => $icon, 
			'msg'   => $msg,
		];
	}
}
