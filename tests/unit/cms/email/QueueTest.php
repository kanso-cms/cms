<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\email;

use kanso\cms\email\utility\Queue;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class QueueTest extends TestCase
{
	/**
	 *
	 */
	public function testEnabled(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$enabled    = true;
		$queue      = new Queue($filesystem, $sender, $logDir, $enabled);

		$this->assertTrue($queue->enabled());
	}

	/**
	 *
	 */
	public function testNotEnabled(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$enabled    = false;
		$queue      = new Queue($filesystem, $sender, $logDir, $enabled);

		$this->assertFalse($queue->enabled());
	}

	/**
	 *
	 */
	public function testDisabled(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$enabled    = false;
		$queue      = new Queue($filesystem, $sender, $logDir, $enabled);

		$this->assertTrue($queue->disabled());
	}

	/**
	 *
	 */
	public function testNotDisabled(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$enabled    = true;
		$queue      = new Queue($filesystem, $sender, $logDir, $enabled);

		$this->assertFalse($queue->disabled());
	}

	/**
	 *
	 */
	public function testEnable(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);

		$queue->enable();

		$this->assertTrue($queue->enabled());
	}

	/**
	 *
	 */
	public function testDisable(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);

		$queue->disable();

		$this->assertFalse($queue->enabled());
	}

	/**
	 *
	 */
	public function testAddFirst(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);

		$filesystem->shouldReceive('exists')->with('/foo/bar/queue.txt')->andReturn(false);

		$filesystem->shouldReceive('touch')->with('/foo/bar/queue.txt')->andReturn(true);

		$filesystem->shouldReceive('prependContents')->with('/foo/bar/queue.txt')->with('/foo/bar/queue.txt', "foobarid\n");

		$queue->add('foobarid');

	}

	/**
	 *
	 */
	public function testAddSecond(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);

		$filesystem->shouldReceive('exists')->with('/foo/bar/queue.txt')->andReturn(true);

		$filesystem->shouldReceive('prependContents')->with('/foo/bar/queue.txt', "foobarid\n");

		$queue->add('foobarid');

	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);

		$filesystem->shouldReceive('exists')->with('/foo/bar/queue.txt')->andReturn(true);

		$filesystem->shouldReceive('getContents')->with('/foo/bar/queue.txt')->andReturn("foo\nbar\n");

		$this->assertEquals(['foo', 'bar'], $queue->get());
	}

	/**
	 *
	 */
	public function testGetEmpty(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);

		$filesystem->shouldReceive('exists')->with('/foo/bar/queue.txt')->andReturn(true);

		$filesystem->shouldReceive('getContents')->with('/foo/bar/queue.txt')->andReturn("\n");

		$this->assertEquals([], $queue->get());
	}

	/**
	 *
	 */
	public function testGetNoFile(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);

		$filesystem->shouldReceive('exists')->with('/foo/bar/queue.txt')->andReturn(false);

		$this->assertEquals([], $queue->get());
	}

	/**
	 *
	 */
	public function testProcess(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);
		$content    = 'htmlemailcontent';
		$info       =
		[
			'to_email'   => 'foo@bar.com',
			'from_name'  => 'foo bar',
			'from_email' => 'foo@bar.com',
			'subject'    => 'foobar',
			'format'     => 'html',
		];

		$filesystem->shouldReceive('exists')->with('/foo/bar/queue.txt')->andReturn(true);

		$filesystem->shouldReceive('getContents')->with('/foo/bar/queue.txt')->andReturn("foo\n");

		$filesystem->shouldReceive('exists')->with('/foo/bar/foo')->andReturn(true);

		$filesystem->shouldReceive('exists')->with('/foo/bar/foo_content')->andReturn(true);

		$filesystem->shouldReceive('getContents')->with('/foo/bar/foo')->andReturn(serialize($info));

		$filesystem->shouldReceive('getContents')->with('/foo/bar/foo_content')->andReturn($content);

		$filesystem->shouldReceive('putContents')->with('/foo/bar/queue.txt', '');

		$sender->shouldReceive('send')->with($info['to_email'], $info['from_name'], $info['from_email'], $info['subject'], $content, $info['format'])->andReturn(true);

		$queue->process();
	}

	/**
	 *
	 */
	public function testProcessEmpty(): void
	{
		$filesystem = $this->mock('\kanso\framework\file\Filesystem');
		$sender     = $this->mock('\kanso\cms\email\utility\Sender');
		$logDir     = '/foo/bar';
		$queue      = new Queue($filesystem, $sender, $logDir);
		$content    = 'htmlemailcontent';
		$info       =
		[
			'to_email'   => 'foo@bar.com',
			'from_name'  => 'foo bar',
			'from_email' => 'foo@bar.com',
			'subject'    => 'foobar',
			'format'     => 'html',
		];

		$filesystem->shouldReceive('exists')->with('/foo/bar/queue.txt')->andReturn(true);

		$filesystem->shouldReceive('getContents')->with('/foo/bar/queue.txt')->andReturn('');

		$queue->process();
	}

}
