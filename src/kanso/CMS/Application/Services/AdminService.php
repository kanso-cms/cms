<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\CMS\Admin\Admin;

/**
 * Admin access service
 *
 * @author Joe J. Howard
 */
class AdminService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Admin', function ($container)
		{
			return new Admin($container->Router, $container->Request, $container->Response, $container->Config, $container->Filters, $container->Events);
		});
	}
}
