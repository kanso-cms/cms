<?php

namespace kanso\cms\admin\models;

use kanso\framework\common\SqlBuilderTrait;
use kanso\framework\mvc\model\Model;

/**
 * Model base class.
 *
 * @author Joe J. Howard
 */
abstract class BaseModel extends Model
{
	use SqlBuilderTrait;

	/**
	 * Identifying name of the requested page.
	 *
	 * @var string
	 */
	protected $requestName;

	/**
	 * POST variables.
	 *
	 * @var array
	 */
	protected $post;

	/**
	 * On HTTP POST.
	 */
	abstract public function onPOST();

	/**
	 * On HTTP AJAX.
	 */
	abstract public function onAJAX();

	/**
	 * On HTTP GET.
	 */
	abstract public function onGET();

	/**
	 * Initialize internal vars.
	 */
	public function init(string $requestName): void
	{
		$this->requestName = $requestName;

    	$this->post = $this->Request->fetch();
	}

	/**
	 * Is the current client logged in ?
	 *
	 * @return bool
	 */
	protected function isLoggedIn(): bool
	{
		return $this->Gatekeeper->isLoggedIn() && $this->Gatekeeper->isAdmin();
	}

	/**
	 * Returns the values required to display a POST
	 * response message.
	 *
	 * @param  string $class HTML message classname
	 * @param  string $msg   Text to go inside the message element
	 * @return array
	 */
	protected function postMessage(string $class, string $msg): array
	{
		$icon = '';

		if ($class === 'danger')
		{
			$icon = 'times';
		}
		elseif ($class === 'success')
		{
			$icon = 'check';
		}
		elseif ($class === 'info')
		{
			$icon = 'info-circle';
		}
		elseif ($class === 'warning')
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
