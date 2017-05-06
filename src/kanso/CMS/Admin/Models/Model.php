<?php

namespace Kanso\CMS\Admin\Models;

use Closure;
use Kanso\Framework\Http\Request\Request;
use Kanso\Framework\Http\Response\Response;
use Kanso\Framework\Database\Query\Builder;
use Kanso\Framework\Utility\GUMP;
use Kanso\CMS\Auth\Gatekeeper;
use Kanso\CMS\Wrappers\Managers\UserManager;

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
	 * Gatekeeper instance
	 *
	 * @var \Kanso\CMS\Auth\Gatekeeper
	 */
	protected $gatekeeper;

	/**
	 * User manager instance
	 *
	 * @var \Kanso\CMS\Wrappers\Managers\UserManager
	 */
	protected $userManager;

	/**
	 * SQL query builder
	 *
	 * @var \Kanso\Framework\Database\Query\Builder
	 */
	protected $SQL;

	/**
	 * POST validator
	 *
     * @var Kanso\Framework\Utility\GUMP
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
     * @param \Kanso\Framework\Http\Request\Request    $request     Request object instance
     * @param \Kanso\Framework\Http\Response\Response  $response    Response object instance
     * @param \Kanso\CMS\Auth\Gatekeeper               $gatekeeper  CMS Gatekeeper instance
     * @param \Kanso\CMS\Wrappers\Managers\UserManager $userManager CMS User manager instance
     * @param \Kanso\Framework\Database\Query\Builder  $SQL         SQL query builder instance
     * @param \Kanso\Framework\Utility\GUMP            $validation  GUMP validatior
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
