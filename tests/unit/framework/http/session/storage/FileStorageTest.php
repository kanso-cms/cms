<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\http\session\storage;

use Mockery;
use tests\TestCase;
use kanso\framework\http\session\storage\FileSessionStorage;

/**
 * @group unit
 */
class FileStorageTest extends TestCase
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
	 *
	 */
	private function mockStorage()
	{
		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		return new FileSessionStorage($crypto, $this->getSessionConfig(), sys_get_temp_dir());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSavePath()
	{
		$storage = $this->mockStorage();

		$savePath = $storage->session_save_path();

		$storage->session_save_path('foo/bar');

		$this->assertEquals('foo/bar', $storage->session_save_path());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testWrite()
	{
		$_COOKIE = [];

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$storage = new FileSessionStorage($crypto, $this->getSessionConfig(), sys_get_temp_dir());

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$this->assertEquals('bar', $storage->read()['foo']);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRead()
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'fdsfsaf#$@#==';

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$storage = new FileSessionStorage($crypto, $this->getSessionConfig(), sys_get_temp_dir());

		$crypto->shouldReceive('decrypt')->withArgs(['fdsfsaf#$@#=='])->andReturn('foobar_session_id');

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$this->assertEquals('bar', $storage->read()['foo']);
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function testDestroy()
	{
		$_COOKIE = [];

		$storage = $this->mockStorage();

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$storage->session_destroy();

		$this->assertEquals(null, $storage->read());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionId()
	{
		$_COOKIE = [];

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$storage = new FileSessionStorage($crypto, $this->getSessionConfig(), sys_get_temp_dir());

		$crypto->shouldReceive('encrypt')->withArgs(['foo'])->andReturn('fdsadf432==');

		$storage->session_id('foo');

		$this->assertEquals('foo', $storage->session_id());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionName()
	{
		$_COOKIE = [];

		$storage = $this->mockStorage();

		$storage->session_name('foo');

		$this->assertEquals('foo', $storage->session_name());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRegenId()
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'fdsfsaf#$@#==';

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$storage = new FileSessionStorage($crypto, $this->getSessionConfig(), sys_get_temp_dir());

		$crypto->shouldReceive('decrypt')->withArgs(['fdsfsaf#$@#=='])->andReturn('foobar_session_id');

		$storage->session_start();

		$oldId = $storage->session_id();

		$storage->session_regenerate_id();

		$this->assertFalse($oldId === $storage->session_id());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSessionParams()
	{
		$storage = $this->mockStorage();

		$params = $this->getSessionConfig();

		$storage->session_set_cookie_params($params);

		$this->assertEquals($params, $storage->session_get_cookie_params());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testGc()
	{
		$_COOKIE = [];

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$storage = new FileSessionStorage($crypto, $this->getSessionConfig(), sys_get_temp_dir());

		$storage->session_start();

		$gc = $storage->session_gc();

		$this->assertEquals(0, $gc);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSend()
	{
		$_COOKIE = [];

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$storage = new FileSessionStorage($crypto, $this->getSessionConfig(), sys_get_temp_dir());

		$crypto->shouldReceive('encrypt')->andReturn('fdsfsf$#@==');

		$storage->session_start();

		$storage->send();
	}
}
