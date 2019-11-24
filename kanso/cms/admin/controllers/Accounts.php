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
	 */
	public function login(): void
	{
		$this->init('login');

		$this->dispatch();
	}

	/**
	 * Dispatch logout request.
	 */
	public function logout(): void
	{
		$this->init('logout');

		$this->dispatch();
	}

	/**
	 * Dispatch forgotpassword request.
	 */
	public function forgotPassword(): void
	{
		$this->init('forgotpassword');

		$this->dispatch();
	}

	/**
	 * Dispatch forgotUsername request.
	 */
	public function forgotUsername(): void
	{
		$this->init('forgotusername');

		$this->dispatch();
	}

	/**
	 * Dispatch resetPassword request.
	 */
	public function resetPassword(): void
	{
		$this->init('resetpassword');

		$this->dispatch();
	}
}
