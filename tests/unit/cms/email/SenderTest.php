<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\email;

use kanso\cms\email\utility\Sender;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class SenderTest extends TestCase
{
	/**
	 *
	 */
	public function testSendNormal(): void
	{
		$mailer     = $this->mock('\PHPMailer\PHPMailer\PHPMailer');
		$sender     = new Sender($mailer, false);
		$this->assertTrue($sender->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content'));
	}

	/**
	 *
	 */
	public function testSendNormalPlainTxt(): void
	{
		$mailer     = $this->mock('\PHPMailer\PHPMailer\PHPMailer');
		$sender     = new Sender($mailer, false);
		$this->assertTrue($sender->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content', 'plain text'));
	}

	/**
	 *
	 */
	public function testSendSmtpHtml(): void
	{
		$mailer     = $this->mock('\PHPMailer\PHPMailer\PHPMailer');
		$sender     = new Sender($mailer, true, $this->getSmtpSettings());

		$mailer->shouldReceive('isSMTP');
		$mailer->shouldReceive('setFrom')->with('bar@foo.com', 'Foo Bar');
		$mailer->shouldReceive('addReplyTo');
		$mailer->shouldReceive('clearAllRecipients');
		$mailer->shouldReceive('clearAttachments');
		$mailer->shouldReceive('clearCustomHeaders');
		$mailer->shouldReceive('addAddress')->with('foo@bar.com');
		$mailer->shouldReceive('isHTML')->with(true);
		$mailer->shouldReceive('msgHTML')->with('html content');
		$mailer->shouldReceive('send');

		$this->assertFalse($sender->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content'));
	}

	/**
	 *
	 */
	public function testSendSmtpPlainText(): void
	{
		$mailer     = $this->mock('\PHPMailer\PHPMailer\PHPMailer');
		$sender     = new Sender($mailer, true, $this->getSmtpSettings());

		$mailer->shouldReceive('isSMTP');
		$mailer->shouldReceive('setFrom')->with('bar@foo.com', 'Foo Bar');
		$mailer->shouldReceive('addAddress')->with('foo@bar.com');
		$mailer->shouldReceive('addReplyTo');
		$mailer->shouldReceive('clearAllRecipients');
		$mailer->shouldReceive('clearAttachments');
		$mailer->shouldReceive('clearCustomHeaders');
		$mailer->shouldReceive('isHTML')->with(false);
		$mailer->shouldReceive('send');

		$this->assertFalse($sender->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content', 'plain text'));
	}

	/**
	 *
	 */
	public function testSendSmtpOAuthHtml(): void
	{
		$mailer     = $this->mock('\PHPMailer\PHPMailer\PHPMailer');
		$config     = $this->getSmtpSettings();
		$sender     = new Sender($mailer, true, $this->getSmtpOauthSettings());

		$mailer->shouldReceive('isSMTP');
		$mailer->shouldReceive('AuthType')->with('XOAUTH2');
		$mailer->shouldReceive('setOAuth');
		$mailer->shouldReceive('setFrom')->with('bar@foo.com', 'Foo Bar');
		$mailer->shouldReceive('addReplyTo');
		$mailer->shouldReceive('clearAllRecipients');
		$mailer->shouldReceive('clearAttachments');
		$mailer->shouldReceive('clearCustomHeaders');
		$mailer->shouldReceive('addAddress')->with('foo@bar.com');
		$mailer->shouldReceive('isHTML')->with(true);
		$mailer->shouldReceive('msgHTML')->with('html content');
		$mailer->shouldReceive('send');

		$this->assertFalse($sender->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content'));
	}

	/**
	 *
	 */
	public function testSendSmtpOAutPlainText(): void
	{
		$mailer     = $this->mock('\PHPMailer\PHPMailer\PHPMailer');
		$config     = $this->getSmtpSettings();
		$sender     = new Sender($mailer, true, $this->getSmtpOauthSettings());

		$mailer->shouldReceive('isSMTP');
		$mailer->shouldReceive('AuthType')->with('XOAUTH2');
		$mailer->shouldReceive('setOAuth');
		$mailer->shouldReceive('clearAllRecipients');
		$mailer->shouldReceive('clearAttachments');
		$mailer->shouldReceive('clearCustomHeaders');
		$mailer->shouldReceive('setFrom')->with('bar@foo.com', 'Foo Bar');
		$mailer->shouldReceive('addReplyTo');
		$mailer->shouldReceive('addAddress')->with('foo@bar.com');
		$mailer->shouldReceive('isHTML')->with(false);
		$mailer->shouldReceive('msgHTML')->with('html content');
		$mailer->shouldReceive('send');

		$this->assertFalse($sender->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content', 'plaintext'));
	}

	/**
	 *
	 */
	private function getSmtpSettings()
	{
		return
	    [
	        'debug'       => 0,
	        'host'        => 'smtp.gmail.com',
	        'port'        => 587,
	        'auth'        => true,
	        'secure'      => 'tls',
	        'username'    => 'foobar@gmail.com',
	        'password'    => 'password',
	    ];
	}

	/**
	 *
	 */
	private function getSmtpOauthSettings()
	{
		return
	    [
	        'debug'          => 0,
	        'host'           => 'smtp.gmail.com',
	        'port'           => 587,
	        'auth'           => true,
	        'secure'         => 'tls',
	        'auth_type'      => 'XOAUTH2',
	        'username'       => 'foobar@gmail.com',
	        'client_id'      => 'fd562456467hfgdhdfu.apps.googleusercontent.com',
	        'client_secret'  => '42324sdr902h8g341r1',
	        'refresh_token'  => '1/4214545tjgdshgh78f98sdffas',
	    ];
	}
}
