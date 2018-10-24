<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\email\Email;
use kanso\cms\email\Log;
use kanso\cms\email\phpmailer\PHPMailer;
use kanso\framework\application\services\Service;

/**
 * CMS Mailer.
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
		$this->container->set('Email', function($container)
		{
			$useSmtp = $container->Config->get('email.use_smtp');

			$config = $container->Config->get('email.smtp_settings');

			$logDir = $container->Config->get('email.log_dir');

			if ($useSmtp && isset($config['hashed_pass']))
			{
				$config['password'] = $container->Crypto->decrypt($config['hashed_pass']);
			}

			return new Email($container->Filesystem, new PHPMailer, new Log($container->Filesystem, $logDir), $container->Config->get('email.theme'), $useSmtp, $config);
		});
	}
}
