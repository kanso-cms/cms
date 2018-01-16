<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace app\controllers;

use kanso\framework\mvc\controller\Controller;

/**
 * Add new comment controller
 *
 * @author Joe J. Howard
 */
class Comments extends Controller
{
	/**
	 * Dispatch the request
	 *
	 */
	public function addComment()
	{		
		if ($this->Request->isAjax())
		{
			$status = $this->model->validate();

			if ($status)
			{
				return $this->jsonResponse(['details' => $status]);
			}
		}

		$this->notFoundResponse();
	}
}
