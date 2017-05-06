<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\CMS\Install\Installer;

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
			return new Installer($container->Config, $container->Database, KANSO_DIR);
		});
	}
}
