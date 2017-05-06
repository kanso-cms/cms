<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Application\Services;

use Kanso\Framework\Application\Services\Service;
use \Kanso\Framework\Database\Database;

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
