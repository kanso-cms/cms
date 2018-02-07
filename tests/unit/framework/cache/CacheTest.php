<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace mako\tests\unit\cache;

use Mockery;
use tests\TestCase;
use kanso\framework\cache\Cache;

/**
 * @group unit
 */
class CacheTest extends TestCase
{
	/**
	 *
	 */
	public function getStore()
	{
		return Mockery::mock('\kanso\framework\cache\stores\FileStore');
	}

	/**
	 *
	 */
	public function testGet()
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('get')->once()->with('foobar')->andReturn('loaded from cache');

		$this->assertEquals('loaded from cache', $cache->get('foobar'));
	}

	/**
	 *
	 */
	public function testPut()
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('put')->once()->with('foobar', 'loaded from cache');

		$store->shouldReceive('get')->once()->with('foobar')->andReturn('loaded from cache');

		$cache->put('foobar', 'loaded from cache');

		$this->assertEquals('loaded from cache', $cache->get('foobar'));
	}

	/**
	 *
	 */
	public function testHas()
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('put')->once()->with('foo', 'loaded from cache');

		$store->shouldReceive('has')->with('foo')->once()->andReturn(true);

		$store->shouldReceive('has')->with('bar')->once()->andReturn(false);

		$cache->put('foo', 'loaded from cache');

		$this->assertTrue($cache->has('foo'));

		$this->assertFalse($cache->has('bar'));
	}

	/**
	 *
	 */
	public function testDelete()
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('put')->once()->with('foo', 'loaded from cache');

		$store->shouldReceive('delete')->with('foo')->once();

		$store->shouldReceive('has')->with('foo')->once()->andReturn(false);

		$cache->put('foo', 'loaded from cache');

		$cache->delete('foo');

		$this->assertFalse($cache->has('foo'));
	}

	/**
	 *
	 */
	public function testExpired()
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('expired')->with('foo', strtotime('-1 week'))->once()->andReturn(true);

		$this->assertTrue($cache->expired('foo'));
	}

	/**
	 *
	 */
	public function testClear()
	{
		$store = $this->getStore();

		$cache = new Cache(strtotime('-1 week'), $store);

		$store->shouldReceive('clear')->once();

		$cache->clear();
	}
}
