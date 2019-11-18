<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\auth\adapters\EmailAdapter;
use kanso\cms\auth\Gatekeeper;
use kanso\framework\application\services\Service;
use kanso\framework\utility\Str;

/**
 * CMS Gatekeeper.
 *
 * @author Joe J. Howard
 */
class GatekeeperService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Gatekeeper', function($container)
		{
			return new Gatekeeper(
				$container->Database->connection()->builder(),
				$container->UserProvider,
				$container->Crypto,
				$container->Cookie,
				$container->Session,
				$this->emailAdapter()
			);
		});
	}

	/**
	 * Loads the email adapter.
	 *
	 * @return \kanso\cms\auth\adapters\EmailAdapter
	 */
	private function emailAdapter(): EmailAdapter
	{
		return new EmailAdapter(
			$this->container->Email,
			$this->container->Request->environment()->HTTP_HOST,
			$this->container->Request->environment()->DOMAIN_NAME,
			trim(Str::getBeforeFirstChar($this->container->Config->get('cms.site_title'), '-')),
			$this->container->Config->get('email.urls')
		);
	}
}
