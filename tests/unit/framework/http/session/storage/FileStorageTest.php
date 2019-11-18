<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\session\storage;

use kanso\framework\http\session\storage\FileSessionStorage;
use kanso\framework\utility\UUID;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
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
	private function mockFilesystem()
	{
		return Mockery::mock('\kanso\framework\file\Filesystem');
	}

	/**
	 *
	 */
	private function mockCrypto()
	{
		return Mockery::mock('\kanso\framework\security\Crypto');
	}

	/**
	 *
	 */
	public function testStart(): void
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'encrypted session id';

		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storageDir = sys_get_temp_dir();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), $storageDir);

		$crypto->shouldReceive('decrypt')->with('encrypted session id')->once()->andReturn('7d5934e6-3984-4ee9-9e56-2555af59948f');

		$filesystem->shouldReceive('exists')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f')->once()->andReturn(true);

		$storage->session_start();

		$_COOKIE = [];
	}

	/**
	 *
	 */
	public function testRead(): void
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'encrypted session id';

		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storageDir = sys_get_temp_dir();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), $storageDir);

		$crypto->shouldReceive('decrypt')->with('encrypted session id')->once()->andReturn('7d5934e6-3984-4ee9-9e56-2555af59948f');

		$filesystem->shouldReceive('exists')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f')->twice()->andReturn(true);

		$filesystem->shouldReceive('getContents')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f')->once()->andReturn(serialize(['foo' => 'bar']));

		$storage->session_start();

		$this->assertEquals(['foo' => 'bar'], $storage->read());

		$_COOKIE = [];
	}

	/**
	 *
	 */
	public function testWrite(): void
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'encrypted session id';

		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storageDir = sys_get_temp_dir();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), $storageDir);

		$crypto->shouldReceive('decrypt')->with('encrypted session id')->once()->andReturn('7d5934e6-3984-4ee9-9e56-2555af59948f');

		$filesystem->shouldReceive('exists')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f')->once()->andReturn(true);

		$filesystem->shouldReceive('putContents')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f', serialize(['foo' => 'bar']))->once();

		$storage->session_start();

		$storage->write(['foo' => 'bar']);

		$_COOKIE = [];
	}

	/**
	 *
	 */
	public function testSavePath(): void
	{
		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), sys_get_temp_dir());

		$storage->session_save_path('foo/bar');

		$this->assertEquals('foo/bar', $storage->session_save_path());

		$_COOKIE = [];
	}

	/**
	 *
	 */
	public function testDestroy(): void
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'encrypted session id';

		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storageDir = sys_get_temp_dir();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), $storageDir);

		$crypto->shouldReceive('decrypt')->with('encrypted session id')->once()->andReturn('7d5934e6-3984-4ee9-9e56-2555af59948f');

		$filesystem->shouldReceive('exists')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f')->once()->andReturn(true);

		$filesystem->shouldReceive('delete')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f')->once();

		$storage->session_start();

		$storage->session_destroy();

		$_COOKIE = [];
	}

	/**
	 *
	 */
	public function testGetSessionId(): void
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'encrypted session id';

		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storageDir = sys_get_temp_dir();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), $storageDir);

		$crypto->shouldReceive('decrypt')->with('encrypted session id')->once()->andReturn('7d5934e6-3984-4ee9-9e56-2555af59948f');

		$filesystem->shouldReceive('exists')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f')->once()->andReturn(true);

		$this->assertEquals(null, $storage->session_id());

		$storage->session_start();

		$this->assertEquals('7d5934e6-3984-4ee9-9e56-2555af59948f', $storage->session_id());

		$_COOKIE = [];
	}

	/**
	 *
	 */
	public function testSetSessionId(): void
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'old encrypted session id';

		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storageDir = sys_get_temp_dir();

		$newid = UUID::v4();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), $storageDir);

		$crypto->shouldReceive('decrypt')->once()->andReturn($newid);

		$filesystem->shouldReceive('exists')->with($storageDir . '/' . $newid)->once()->andReturn(true);

		$this->assertEquals($newid, $storage->session_id($newid));

		$storage->session_start();

		$_COOKIE = [];
	}

	/**
	 *
	 */
	public function testSetSessionName(): void
	{
		$_COOKIE = [];

		$_COOKIE['foobar'] = 'encrypted session id';

		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storageDir = sys_get_temp_dir();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), $storageDir);

		$crypto->shouldReceive('decrypt')->with('encrypted session id')->once()->andReturn('7d5934e6-3984-4ee9-9e56-2555af59948f');

		$filesystem->shouldReceive('exists')->with($storageDir . '/7d5934e6-3984-4ee9-9e56-2555af59948f')->once()->andReturn(true);

		$storage->session_name('foobar');

		$storage->session_start();

		$_COOKIE = [];
	}

	/**
	 *
	 */
	public function testRegenerateId(): void
	{
		$_COOKIE = [];

		$_COOKIE['kanso_session'] = 'old encrypted session id';

		$crypto = $this->mockCrypto();

		$filesystem = $this->mockFilesystem();

		$storageDir = sys_get_temp_dir();

		$storage = new FileSessionStorage($crypto, $filesystem, $this->getSessionConfig(), $storageDir);

		$storage->session_regenerate_id();

		$this->assertFalse($_COOKIE['kanso_session'] === $storage->session_id());

		$_COOKIE = [];
	}

}
