<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\crm\Crm;
use kanso\framework\application\services\Service;

/**
 * Crm Service.
 *
 * @author Joe J. Howard
 */
class CrmService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Crm', function($container)
		{
			return new Crm($container->Request, $container->Response, $container->Gatekeeper, $container->LeadProvider, $container->Database->connection()->builder(), $container->UserAgent->isCrawler(), $container->Query->is_admin());
		});
	}
}
