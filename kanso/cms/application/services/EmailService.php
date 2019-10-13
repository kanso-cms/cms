<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application\services;

use kanso\cms\email\Email;
use kanso\cms\email\utility\Log;
use kanso\cms\email\utility\Queue;
use kanso\cms\email\utility\Sender;
use kanso\framework\application\services\Service;
use PHPMailer\PHPMailer\PHPMailer;

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
			return new Email($container->Filesystem, $this->logger(), $this->sender(), $this->queue(), $container->Config->get('email.theme'));
		});
	}

	/**
	 * Loads the email queue.
	 *
	 * @access private
	 * @return \kanso\cms\email\utility\Queue
	 */
	private function queue(): Queue
	{
		return new Queue($this->container->Filesystem, $this->sender(), $this->container->Config->get('email.log_dir'), $this->container->Config->get('email.queue'));
	}

	/**
	 * Loads the email logger.
	 *
	 * @access private
	 * @return \kanso\cms\email\utility\Log
	 */
	private function logger(): Log
	{
		return new Log($this->container->Filesystem, $this->container->Config->get('email.log_dir'));
	}

	/**
	 * Loads the email sender.
	 *
	 * @access private
	 * @return \kanso\cms\email\utility\Sender
	 */
	private function sender(): Sender
	{
		$useSmtp    = $this->container->Config->get('email.use_smtp');
		$smtpConfig = $this->container->Config->get('email.smtp_settings');

		if ($useSmtp && isset($smtpConfig['hashed_pass']))
		{
			$smtpConfig['password'] = $this->container->Crypto->decrypt($smtpConfig['hashed_pass']);
		}

		return new Sender(new PHPMailer, $useSmtp, $smtpConfig);
	}
}
