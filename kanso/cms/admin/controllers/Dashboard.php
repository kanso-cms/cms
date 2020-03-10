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
	 */
	public function posts(): void
	{
		$this->init('posts');

		$this->model->setPostType('post');

		$this->dispatch();
	}

	/**
	 * Dispatch pages request.
	 */
	public function pages(): void
	{
		$this->init('pages');

		$this->model->setPostType('page');

		$this->dispatch();
	}

	/**
	 * Dispatch tags request.
	 */
	public function tags(): void
	{
		$this->init('tags');

		$this->dispatch();
	}

	/**
	 * Dispatch categories request.
	 */
	public function categories(): void
	{
		$this->init('categories');

		$this->dispatch();
	}

	/**
	 * Dispatch comments request.
	 */
	public function comments(): void
	{
		$this->init('comments');

		$this->dispatch();
	}

	/**
	 * Dispatch commentUsers request.
	 */
	public function commentUsers(): void
	{
		$this->init('commentUsers');

		$this->dispatch();
	}

	/**
	 * Dispatch mediaLibrary request.
	 */
	public function mediaLibrary(): void
	{
		$this->init('mediaLibrary');

		$this->dispatch();
	}

	/**
	 * Dispatch writer request.
	 */
	public function writer(): void
	{
		$this->init('writer');

		$this->dispatch();
	}

	/**
	 * Dispatch settings request.
	 */
	public function settings(): void
	{
		$this->init('settings');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsAccount request.
	 */
	public function settingsAccount(): void
	{
		$this->init('settingsAccount');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsAuthor request.
	 */
	public function settingsAuthor(): void
	{
		$this->init('settingsAuthor');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsKanso request.
	 */
	public function settingsKanso(): void
	{
		$this->init('settingsKanso');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsAccess request.
	 */
	public function settingsAccess(): void
	{
		$this->init('settingsAccess');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsUsers request.
	 */
	public function settingsUsers(): void
	{
		$this->init('settingsUsers');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsErrors request.
	 */
	public function settingsErrors(): void
	{
		$this->init('settingsErrors');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsTools request.
	 */
	public function settingsTools(): void
	{
		$this->init('settingsTools');

		$this->dispatch();
	}

	/**
	 * Dispatch settingsAnalytics request.
	 */
	public function settingsAnalytics(): void
	{
		$this->init('settingsAnalytics');

		$this->dispatch();
	}

	/**
	 * Dispatch errorLogs request.
	 */
	public function errorLogs(): void
	{
		$this->init('errorLogs');

		$this->dispatch();
	}

	/**
	 * Dispatch emailLogs request.
	 */
	public function emailLogs(): void
	{
		$this->init('emailLogs');

		$this->dispatch();
	}

	/**
	 * Dispatch emailPreview request.
	 */
	public function emailPreview(): void
	{
		$this->init('emailPreview');

		$this->dispatch();
	}

	/**
	 * Dispatch custom page.
	 */
	public function invoice(): void
	{
		$this->init('invoice');

		$this->dispatch();
	}

	/**
	 * Dispatch leads request.
	 */
	public function leads(): void
	{
		$this->init('leads');

		$this->dispatch();
	}

	/**
	 * Dispatch lead request.
	 */
	public function lead(): void
	{
		$this->init('lead');

		$this->dispatch();
	}

	/**
	 * Dispatch custom page.
	 */
	public function blankPage(): void
	{
		$this->init('customPage');

		$this->dispatch();
	}

	/**
	 * Dispatch add bundle page.
	 */
	public function addBundle(): void
	{
		$this->init('addBundle');

		$this->dispatch();
	}

	/**
	 * Dispatch custom post-type.
	 */
	public function customPostType(): void
	{
		$this->init('customposts');

		$this->model->setPostType($this->Filters->apply('adminCustomPostType', null));

		$this->dispatch();
	}
}
