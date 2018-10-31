<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\controllers;

/**
 * Admin panel access pages controller.
 *
 * @author Joe J. Howard
 */
class Accounts extends BaseController
{
	/**
	 * Dispatch login request.
	 *
	 * @access public
	 */
	public function login()
	{
		$this->init('login');

		$this->dispatch();
	}

	/**
	 * Dispatch logout request.
	 *
	 * @access public
	 */
	public function logout()
	{
		$this->init('logout');

		$this->dispatch();
	}

	/**
	 * Dispatch forgotpassword request.
	 *
	 * @access public
	 */
	public function forgotPassword()
	{
		$this->init('forgotpassword');

		$this->dispatch();
	}

	/**
	 * Dispatch forgotUsername request.
	 *
	 * @access public
	 */
	public function forgotUsername()
	{
		$this->init('forgotusername');

		$this->dispatch();
	}

	/**
	 * Dispatch resetPassword request.
	 *
	 * @access public
	 */
	public function resetPassword()
	{
		$this->init('resetpassword');

		$this->dispatch();
	}
}
