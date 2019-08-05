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
	public function testGetQueue()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\utility\Log');
		$queue      = Mockery::mock('\kanso\cms\email\utility\Queue');
		$sender     = Mockery::mock('\kanso\cms\email\utility\Sender');
		$email      = new Email($filesystem, $log, $sender, $queue);

		$this->assertEquals($queue, $email->queue());
	}

	/**
	 *
	 */
	public function testGetLog()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\utility\Log');
		$queue      = Mockery::mock('\kanso\cms\email\utility\Queue');
		$sender     = Mockery::mock('\kanso\cms\email\utility\Sender');
		$email      = new Email($filesystem, $log, $sender, $queue);

		$this->assertEquals($log, $email->log());
	}

	/**
	 *
	 */
	public function testGetSender()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\utility\Log');
		$queue      = Mockery::mock('\kanso\cms\email\utility\Queue');
		$sender     = Mockery::mock('\kanso\cms\email\utility\Sender');
		$email      = new Email($filesystem, $log, $sender, $queue);

		$this->assertEquals($sender, $email->sender());
	}

	/**
	 *
	 */
	public function testPresets()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\utility\Log');
		$queue      = Mockery::mock('\kanso\cms\email\utility\Queue');
		$sender     = Mockery::mock('\kanso\cms\email\utility\Sender');
		$email      = new Email($filesystem, $log, $sender, $queue);

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
		$log        = Mockery::mock('\kanso\cms\email\utility\Log');
		$queue      = Mockery::mock('\kanso\cms\email\utility\Queue');
		$sender     = Mockery::mock('\kanso\cms\email\utility\Sender');
		$email      = new Email($filesystem, $log, $sender, $queue);
		$theme      = $email->theme();

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
	public function testSendNoQueue()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\utility\Log');
		$queue      = Mockery::mock('\kanso\cms\email\utility\Queue');
		$sender     = Mockery::mock('\kanso\cms\email\utility\Sender');
		$email      = new Email($filesystem, $log, $sender, $queue);

		$log->shouldReceive('save')->with('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content', 'html');
		$sender->shouldReceive('send')->with('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content', 'html');
		$queue->shouldReceive('enabled')->andReturn(false);

		$this->assertTrue($email->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content'));
	}

	/**
	 *
	 */
	public function testSendWithQue()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$smtp       = Mockery::mock('\kanso\cms\email\phpmailer\PHPMailer');
		$log        = Mockery::mock('\kanso\cms\email\utility\Log');
		$queue      = Mockery::mock('\kanso\cms\email\utility\Queue');
		$sender     = Mockery::mock('\kanso\cms\email\utility\Sender');
		$email      = new Email($filesystem, $log, $sender, $queue);

		$log->shouldReceive('save')->with('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content', 'html');
		$queue->shouldReceive('enabled')->andReturn(true);
		$queue->shouldReceive('add');

		$this->assertTrue($email->send('foo@bar.com', 'Foo Bar', 'bar@foo.com', 'Foo Subject', 'html content'));
	}
}
