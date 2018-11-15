<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\admin\Admin;
use kanso\framework\application\services\Service;

/**
 * Admin access service.
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
		$this->container->singleton('Admin', function($container)
		{
			return new Admin;
		});
	}
}
