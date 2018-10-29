<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\crawler\CrawlerDetect;
use kanso\framework\crawler\fixtures\Exclusions;
use kanso\framework\crawler\fixtures\Inclusions;

/**
 * UserAgent Crawler Service.
 *
 * @author Joe J. Howard
 */
class CrawlerService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('UserAgent', function($container)
		{
			return new CrawlerDetect($container->Request->headers(), new Inclusions, new Exclusions);
		});
	}
}
