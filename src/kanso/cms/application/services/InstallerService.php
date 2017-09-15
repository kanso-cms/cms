<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\framework\application\services\Service;
use kanso\cms\install\Installer;

/**
 * CMS Installer
 *
 * @author Joe J. Howard
 */
class InstallerService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Installer', function ($container) 
		{
			return new Installer($container->Config, $container->Database, $container->Access, KANSO_DIR);
		});
	}
}
