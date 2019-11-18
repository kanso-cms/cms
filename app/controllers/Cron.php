<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace app\controllers;

use kanso\framework\mvc\controller\Controller;

/**
 * Cron job controller.
 *
 * @author Joe J. Howard
 */
class Cron extends Controller
{
	/**
	 * Handle GET request to run database maintenance.
	 */
	public function dbMaintenance()
	{
		if ($this->model->validate())
		{
			$this->model->dbMaintenance();

			return true;
		}

		$this->notFoundResponse();
	}

	/**
	 * Handle GET request to process email queue.
	 */
	public function emailQueue()
	{
		if ($this->model->validate())
		{
			$this->model->emailQueue();

			return true;
		}

		$this->notFoundResponse();
	}
}
