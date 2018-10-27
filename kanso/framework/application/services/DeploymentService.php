<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\deployment\Deployment;
use kanso\framework\deployment\webhooks\Github;
use kanso\framework\deployment\webhooks\WebhookInterface;

/**
 * Framework deployment service.
 *
 * @author Joe J. Howard
 */
class DatabaseService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Deployment', function()
		{
			return new Deployment($this->webhookInterface());
		});
	}

	/**
	 * Returns the deployment implementation.
	 *
	 * @access private
	 * @return \kanso\framework\deployment\webhooks\WebhookInterface
	 */
	private function webhookInterface(): WebhookInterface
	{
		if ($this->container->Config->get('application.deployment.implementation') === 'github')
		{
			return new Github($this->container->Request, $this->container->Response, $this->container->Config->get('application.deployment.token'));
		}
	}
}
