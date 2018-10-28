<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin\controllers;

/**
 * Admin panel dashboard pages controller.
 *
 * @author Joe J. Howard
 */
class Dashboard extends BaseController
{
	/**
	 * Dispatch posts request.
	 *
	 * @access public
	 */
	public function posts()
	{
		$this->init('posts');

		$this->model->setPostType('post');

		$this->dispatch();
	}

	/**
	 * Dispatch pages request.
	 *
	 * @access public
	 */
	public function pages()
	{
		$this->init('pages');

		$this->model->setPostType('page');

		$this->dispatch();
	}

	/**
	 * Dispatch tags request.
	 *
	 * @access public
	 */
	public function tags()
	{
		$this->init('tags');

		$this->dispatch();
	}

	/**
	 * Dispatch categories request.
	 *
	 * @access public
	 */
	public function categories()
	{
		$this->init('categories');

		$this->dispatch();
	}

	/**
	 * Dispatch comments request.
	 *
	 * @access public
	 */
	public function comments()
	{
		$this->init('comments');

		$this->dispatch();
	}

	/**
	 * Dispatch commentUsers request.
	 *
	 * @access public
	 */
	public function commentUsers()
	{
		$this->init('commentUsers');

		$this->dispatch();
	}

	/**
	 * Dispatch mediaLibrary request.
	 *
	 * @access public
	 */
	public function mediaLibrary()
	{
		$this->init('mediaLibrary');

		$this->dispatch();
	}

	/**
	 * Dispatch writer request.
	 *
	 * @access public
	 */
	public function writer()
	{
		$this->init('writer');

		$this->dispatch();
	}

	/**
	 * Dispatch settings request.
	 *
	 * @access public
	 */
	public function settings()
	{
		$this->init('settings');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsAccount request.
	 *
	 * @access public
	 */
	public function settingsAccount()
	{
		$this->init('settingsAccount');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsAuthor request.
	 *
	 * @access public
	 */
	public function settingsAuthor()
	{
		$this->init('settingsAuthor');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsKanso request.
	 *
	 * @access public
	 */
	public function settingsKanso()
	{
		$this->init('settingsKanso');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsAccess request.
	 *
	 * @access public
	 */
	public function settingsAccess()
	{
		$this->init('settingsAccess');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsUsers request.
	 *
	 * @access public
	 */
	public function settingsUsers()
	{
		$this->init('settingsUsers');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsErrors request.
	 *
	 * @access public
	 */
	public function settingsErrors()
	{
		$this->init('settingsErrors');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsTools request.
	 *
	 * @access public
	 */
	public function settingsTools()
	{
		$this->init('settingsTools');

		$this->dispatch();
	}

	/**
	 * Dispatch errorLogs request.
	 *
	 * @access public
	 */
	public function errorLogs()
	{
		$this->init('errorLogs');

		$this->dispatch();
	}

	/**
	 * Dispatch emailLogs request.
	 *
	 * @access public
	 */
	public function emailLogs()
	{
		$this->init('emailLogs');

		$this->dispatch();
	}

	/**
	 * Dispatch emailPreview request.
	 *
	 * @access public
	 */
	public function emailPreview()
	{
		$this->init('emailPreview');

		$this->dispatch();
	}

	/**
	 * Dispatch custom page.
	 *
	 * @access public
	 */
	public function blankPage()
	{
		$this->init('customPage');

		$this->dispatch();
	}

	/**
	 * Dispatch custom post-type.
	 *
	 * @access public
	 */
	public function customPostType()
	{
		$this->init('customposts');

		$this->model->setPostType($this->Filters->apply('adminCustomPostType', null));

		$this->dispatch();
	}
}
