<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\application\services\Service;
use \kanso\framework\database\Database;

/**
 * Database services
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
		$this->container->singleton('Database', function ($container)
		{
			return new Database($container->Config->get('database'));
		});
	}
}
