<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\framework\application\services\Service;
use kanso\cms\application\Application;

/**
 * Boots the CMS
 *
 * @author Joe J. Howard
 */
class BootService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('CMS', function ($container)
		{
			return Application::instance($container);
		})
		->CMS->boot();
	}
}
