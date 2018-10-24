<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\event\Events;
use kanso\cms\event\Filters;
use kanso\framework\application\services\Service;

/**
 * Event and Filter service.
 *
 * @author Joe J. Howard
 */
class EventService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Events', function($container)
		{
			return Events::instance();
		});

		$this->container->singleton('Filters', function($container)
		{
			return Filters::instance();
		});
	}
}
