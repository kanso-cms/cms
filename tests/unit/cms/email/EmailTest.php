<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\cms\email;

use Mockery;
use tests\TestCase;
use kanso\cms\email\Email;


/**
 * @group unit
 */
class EventsTest extends TestCase
{
	/**
	 *
	 */
	public function testPresets()
	{
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');

		$email = new Email($filesystem);

		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR.'/cms/email/templates/body.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR.'/cms/email/templates/comment.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR.'/cms/email/templates/confirm-account.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR.'/cms/email/templates/forgot-password.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR.'/cms/email/templates/new-admin.php', $email->theme())->once()->andReturn('foo');
		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR.'/cms/email/templates/reset-password.php', $email->theme())->once()->andReturn('foo');

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

		$email = new Email($filesystem);

		$theme = $email->theme();

		$vars = array_merge($theme, 
		[
			'subject'  => 'email subject', 
            'content' => 'additional html',
            'logoSrc' => $theme['logo_url'],
		]);

		$filesystem->shouldReceive('ob_read')->with(KANSO_DIR.'/cms/email/templates/body.php', $vars)->once()->andReturn('foo');

		$this->assertEquals('foo', $email->html('email subject', 'additional html'));
	}
}
