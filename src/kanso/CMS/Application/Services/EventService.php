<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\CMS\Event\Events;
use Kanso\CMS\Event\Filters;

/**
 * Event and Filter service
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
		$this->container->singleton('Events', function ($container) 
		{
			return Events::instance();
		});

		$this->container->singleton('Filters', function ($container) 
		{
			return Filters::instance();
		});
	}
}
