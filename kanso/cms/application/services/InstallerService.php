<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\install\Installer;
use kanso\framework\application\services\Service;

/**
 * CMS Installer.
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
		$this->container->singleton('Installer', function($container)
		{
			return new Installer($container->Config, $container->Database, $container->Access, KANSO_DIR);
		});
	}
}
