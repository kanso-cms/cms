<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\CMS\Application\Application;

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
