<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\http\session\storage;

use kanso\framework\http\session\storage\NativeSessionStorage;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class NativeStorageTest extends TestCase
{
	private function getSessionConfig()
	{
		return
		[
			'cookie_name'  => 'kanso_session',
			'expire'       => strtotime('+1 month'),
			'path'         => '/',
			'domain'       => '',
			'secure'       => false,
			'httponly'     => false,
		];
	}

	/**
	 * @runInSeparateProcess
	 */
	private function mockStorage()
	{
		return new NativeSessionStorage($this->getSessionConfig(), session_save_path());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSavePath(): void
	{
		$storage = $this->mockStorage();

		$savePath = $storage->session_save_path();

		$storage->session_save_path('foo/bar');

		$this->assertEquals('foo/bar', $storage->session_save_path());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testWrite(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$this->assertEquals('bar', $storage->read()['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRead(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$this->assertEquals('bar', $storage->read()['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testDestroy(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$storage->session_destroy();

		$this->assertEquals(null, $storage->read());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionId(): void
	{
		$storage = $this->mockStorage();

		$storage->session_id(md5('foo'));

		$this->assertEquals(md5('foo'), $storage->session_id());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionName(): void
	{
		$storage = $this->mockStorage();

		$storage->session_name('foo');

		$this->assertEquals('foo', $storage->session_name());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRegenId(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$oldId = $storage->session_id();

		$storage->session_regenerate_id();

		$this->assertFalse($oldId === $storage->session_id());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionParams(): void
	{
		$storage = $this->mockStorage();

		$params = $this->getSessionConfig();

		$storage->session_set_cookie_params($params);

		$expected =
		[
			'lifetime' => strtotime('+1 month'),
			'path'     => '/',
			'domain'   => '',
			'secure'   => false,
			'httponly' => false,
		];

		$this->assertEquals($expected, $storage->session_get_cookie_params());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testGc(): void
	{
		$storage = $this->mockStorage();

		$storage->session_start();

		$gc = $storage->session_gc();

		$this->assertTrue(is_int($gc));
	}
}
