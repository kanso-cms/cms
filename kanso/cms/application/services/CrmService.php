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
	public function register()
	{
		$this->container->setInstance('Crm', new Crm);
	}
}
