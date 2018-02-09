<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\framework\application\services\Service;
use kanso\cms\email\Email;

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
			return new Email($container->Filesystem, $container->Config->get('email.theme'));
		});
	}
}
