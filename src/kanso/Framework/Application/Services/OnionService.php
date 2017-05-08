<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\application\services\Service;
use kanso\framework\onion\Onion;

/**
 * Onion/Middleware service
 *
 * @author Joe J. Howard
 */
class OnionService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Onion', function ($container)
		{
			return new Onion($container->Request, $container->Response);
		});
	}
}
