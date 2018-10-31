<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\email;

use kanso\cms\email\Email;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class EmailTest extends TestCase
{
	/**
	 *
	 */
	public function testPresets()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\Log');
		$email      = new Email($filesystem, $smtp, $log);

		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR . '/cms/email/templates/body.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR . '/cms/email/templates/comment.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR . '/cms/email/templates/confirm-account.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR . '/cms/email/templates/forgot-password.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR . '/cms/email/templates/new-admin.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR . '/cms/email/templates/reset-password.php', $email->theme())->once()->andReturn('foo');

		$this->assertEquals('foo', $email->preset('body'));
		$this->assertEquals('foo', $email->preset('comment'));
		$this->assertEquals('foo', $email->preset('confirm-account'));
		$this->assertEquals('foo', $email->preset('forgot-password'));
		$this->assertEquals('foo', $email->preset('new-admin'));
		$this->assertEquals('foo', $email->preset('reset-password'));
	}

	/**
	 *
	 */
	public function testHtml()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\Log');
		$email      = new Email($filesystem, $smtp, $log);

		$theme = $email->theme();

		$vars = array_merge($theme,
		[
			'subject' => 'email subject',
            'content' => 'additional html',
            'logoSrc' => $theme['logo_url'],
		]);

		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR . '/cms/email/templates/body.php', $vars)->once()->andReturn('foo');

		$this->assertEquals('foo', $email->html('email subject', 'additional html'));
	}

	/**
	 *
	 */
	public function testSendSmtp()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\Log');
		$smtpConfig = $this->getSmtpSettings();
		$email      = new Email($filesystem, $smtp, $log, [], true, $smtpConfig);

		$smtp->shouldReceive('isSMTP');
		$smtp->shouldReceive('setFrom')->with('bar@foo.com', 'Foo Bar');
		$smtp->shouldReceive('addAddress')->with('foo@bar.com');
		$smtp->shouldReceive('isHTML')->with(true);
		$smtp->shouldReceive('msgHTML')->with('html content');
		$smtp->shouldReceive('send');
		$log->shouldReceive('save');

		$this->assertTrue($email->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content'));
	}

	/**
	 *
	 */
	public function testSendSmtpPlainText()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\Log');
		$smtpConfig = $this->getSmtpSettings();
		$email      = new Email($filesystem, $smtp, $log, [], true, $smtpConfig);

		$smtp->shouldReceive('isSMTP');
		$smtp->shouldReceive('setFrom')->with('bar@foo.com', 'Foo Bar');
		$smtp->shouldReceive('addAddress')->with('foo@bar.com');
		$smtp->shouldReceive('isHTML')->with(false);
		$smtp->shouldReceive('send');
		$log->shouldReceive('save');

		$this->assertTrue($email->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content', false));
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
}
