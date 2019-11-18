<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\analytics\Analytics;
use kanso\framework\application\services\Service;

/**
 * Analytics service.
 *
 * @author Joe J. Howard
 */
class AnalyticsService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->singleton('Analytics', function($container)
		{
			$config = $container->Config->get('analytics');

			return new Analytics($config['google']['analytics']['enabled'], $config['google']['analytics']['id'], $config['google']['adwords']['enabled'], $config['google']['adwords']['id'], $config['google']['adwords']['conversion'], $config['facebook']['enabled'], $config['facebook']['pixel']);
		});
	}
}
