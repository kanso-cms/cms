<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Application\Services;

use Kanso\Framework\Application\Services\Service;
use \Kanso\Framework\View\View;

/**
 * View service
 *
 * @author Joe J. Howard
 */
class ViewService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('View', function ()
		{
			return new View;
		});
	}
}
