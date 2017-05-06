<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\CMS\Auth\Gatekeeper;

/**
 * CMS Gatekeeper
 *
 * @author Joe J. Howard
 */
class GatekeeperService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Gatekeeper', function ($container) 
		{
			return new Gatekeeper(
				$container->Database->connection()->builder(),
				$container->UserProvider,
				$container->Crypto,
				$container->Cookie,
				$container->Session,
				$container->Config,
				$container->Request->environment(),
				$container->Email
			);
		});
	}
}
