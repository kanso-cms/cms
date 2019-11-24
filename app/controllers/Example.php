<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace app\controllers;

use kanso\framework\mvc\controller\Controller;

/**
 * Example controller.
 *
 * @author Joe J. Howard
 */
class Example extends Controller
{
	public function welcome(): void
	{
		if ($this->model->validate())
		{
			$this->Response->body()->set('Hello World!');
		}
		else
		{
			$this->notFoundResponse();
		}
	}
}
