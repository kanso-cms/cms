<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\query\Query;
use kanso\framework\application\services\Service;

/**
 * CMS Query.
 *
 * @author Joe J. Howard
 */
class QueryService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Query', function($container)
		{
			return new Query($container);
		});
	}
}
