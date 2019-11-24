<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\application\Cms;
use kanso\framework\application\services\Service;

/**
 * Boots the CMS.
 *
 * @author Joe J. Howard
 */
class BootService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register(): void
	{
		$this->container->setInstance('CMS', new Cms($this->container));
	}
}
