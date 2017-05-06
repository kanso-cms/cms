<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Application\Services;

use Kanso\Framework\Application\Services\Service;
use Kanso\CMS\Email\Email;

/**
 * CMS Mailer
 *
 * @author Joe J. Howard
 */
class EmailService extends Service
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->container->singleton('Email', function ($container) 
		{
			return new Email($this->container->Config);
		});
	}
}
