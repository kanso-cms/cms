<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\application\services\Service;
use kanso\framework\utility\GUMP;

/**
 * Utility services
 *
 * @author Joe J. Howard
 */
class UtilityService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->set('Validation', function ()
		{
			return new GUMP;
		});
	}
}
