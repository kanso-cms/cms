<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\mvc\view\View;

/**
 * MVC Service.
 *
 * @author Joe J. Howard
 */
class MVCService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('View', function()
		{
			return new View;
		});
	}
}
