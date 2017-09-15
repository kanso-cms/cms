<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\framework\application\services\Service;
use kanso\cms\access\Access;

/**
 * Admin access service
 *
 * @author Joe J. Howard
 */
class AccessService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Access', function ($container)
		{
			return new Access($container->Request, $container->Response, $container->Config->get('cms.security.ip_blocked'), $container->Config->get('cms.security.ip_whitelist'));
		});
	}
}
