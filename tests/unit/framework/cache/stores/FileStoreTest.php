<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace mako\tests\unit\cache\stores;

use Mockery;
use tests\TestCase;
use kanso\framework\cache\stores\FileStore;

/**
 * @group unit
 */
class FileStoreTest extends TestCase
{
	/**
	 *
	 */
	public function getFileSystem()
	{
		return Mockery::mock('\kanso\framework\file\Filesystem');
	}

	/**
	 *
	 */
	public function testGet()
	{
		$filesystem = $this->getFileSystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->once()->with('/app/storage/cache/foo.cache')->andReturn(true);

		$filesystem->shouldReceive('getContents')->once()->with('/app/storage/cache/foo.cache')->andReturn('loaded from cache');

		$this->assertEquals('loaded from cache', $store->get('foo'));
	}

	/**
	 *
	 */
	public function testPut()
	{
		$filesystem = $this->getFileSystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('putContents')->once()->with('/app/storage/cache/foobar.cache', 'loaded from cache');

		$store->put('foobar', 'loaded from cache');
	}

	/**
	 *
	 */
	public function testHas()
	{
		$filesystem = $this->getFileSystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/foo.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/bar.cache')->once()->andReturn(false);

		$this->assertTrue($store->has('foo'));

		$this->assertFalse($store->has('bar'));
	}

	/**
	 *
	 */
	public function testDelete()
	{
		$filesystem = $this->getFileSystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/foo.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('delete')->with('/app/storage/cache/foo.cache')->once();

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/bar.cache')->once()->andReturn(false);

		$store->delete('foo');

		$store->delete('bar');
	}

	/**
	 *
	 */
	public function testExpired()
	{
		$filesystem = $this->getFileSystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/foo.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('lastModified')->with('/app/storage/cache/foo.cache')->once()->andReturn(strtotime('-2 months'));

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/bar.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('lastModified')->with('/app/storage/cache/bar.cache')->once()->andReturn(strtotime('-15 days'));

		$this->assertTrue($store->expired('foo', strtotime('+1 month')));

		$this->assertFalse($store->expired('bar', strtotime('+1 month')));
	}

	/**
	 *
	 */
	public function testClear()
	{
		$filesystem = $this->getFileSystem();

		$store = new FileStore($filesystem, '/app/storage/cache');

		$filesystem->shouldReceive('list')->with('/app/storage/cache')->once()->andReturn(['foo.cache', 'bar.cache']);

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/foo.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('exists')->with('/app/storage/cache/bar.cache')->once()->andReturn(true);

		$filesystem->shouldReceive('delete')->with('/app/storage/cache/foo.cache')->once();

		$filesystem->shouldReceive('delete')->with('/app/storage/cache/bar.cache')->once();

		$store->clear();
	}
}
