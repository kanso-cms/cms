<?php

namespace kanso\cms\admin\models;

use kanso\framework\mvc\model\Model;

/**
 * Model base class
 *
 * @author Joe J. Howard
 */
abstract class BaseModel extends Model
{
	/**
	 * Identifying name of the requested page
	 *
	 * @var string
	 */
	protected $requestName;

	/**
	 * Identifying name of the requested page
	 *
	 * @var bool
	 */
	protected $isLoggedIn;

	/**
	 * Identifying name of the requested page
	 *
	 * @var \kanso\framework\database\query\Builder
	 */
	protected $SQL;

	/**
	 * Identifying name of the requested page
	 *
	 * @var kanso\framework\utility\GUMP
	 */
	protected $validation;

	/**
	 * POST variables
	 *
	 * @var array
	 */
	protected $post;

    /**
	 * On HTTP POST
	 *
	 * @access protected
	 */
	abstract public function onPOST();

	/**
	 * On HTTP AJAX
	 *
	 * @access protected
	 */
	abstract public function onAJAX();

	/**
	 * On HTTP GET
	 *
	 * @access protected
	 */
	abstract public function onGET();

	/**
	 * Initialize internal vars
	 *
	 * @access public
	 */
	public function init(string $requestName)
	{
		$this->requestName = $requestName;

		$this->isLoggedIn = $this->isLoggedIn();

    	$this->SQL = $this->Database->connection()->builder();

    	$this->validation = $this->Validation;

    	$this->post = $this->Request->fetch();
	}

	/**
	 * Is the current client logged in ?
	 *
	 * @access public
	 * @param  string $name Identifying name of the requested page
	 * @return bool
	 */
	protected function isLoggedIn(): bool
	{
		return $this->Gatekeeper->isLoggedIn() && $this->Gatekeeper->isAdmin();
	}

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
