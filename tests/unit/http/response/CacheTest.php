<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\response;

use Mockery;
use tests\TestCase;
use kanso\framework\http\response\Cache;
use kanso\framework\cache\Cache as FrameworkCache;


/**
 * @group unit
 */
class CacheTest extends TestCase
{
	/**
	 *
	 */
	public function testEnable()
	{
		$cache = new Cache(Mockery::mock('kanso\framework\cache\Cache'), 'foobar_key');

		$this->assertFalse($cache->enabled());

		$cache->enable();

		$this->assertTrue($cache->enabled());
	}

	/**
	 *
	 */
	public function testDisable()
	{
		$cache = new Cache(Mockery::mock('kanso\framework\cache\Cache'), 'foobar_key', true);

		$this->assertTrue($cache->enabled());

		$cache->disable();

		$this->assertFalse($cache->enabled());
	}

	/**
	 *
	 */
	public function testGetHave()
	{
		$frameworkCache = Mockery::mock('kanso\framework\cache\Cache');
		
		$cache          = new Cache($frameworkCache, 'foobar_key', true);
		
		$frameworkCache->shouldReceive('expired')->withArgs(['foobar_key'])->andReturn(false);
		
		$frameworkCache->shouldReceive('has')->withArgs(['foobar_key'])->andReturn(true);
		
		$frameworkCache->shouldReceive('get')->withArgs(['foobar_key'])->andReturn('returned from cache');
		
		$this->assertEquals('returned from cache', $cache->get());
	}

	/**
	 *
	 */
	public function testGetNotHave()
	{
		$frameworkCache = Mockery::mock('kanso\framework\cache\Cache');
		
		$cache = new Cache($frameworkCache, 'foobar_key', true);
		
		$frameworkCache->shouldReceive('expired')->withArgs(['foobar_key'])->andReturn(false);
		
		$frameworkCache->shouldReceive('has')->withArgs(['foobar_key'])->andReturn(false);
		
		$this->assertEquals(null, $cache->get());
	}

	/**
	 *
	 */
	public function testGetExpired()
	{
		$frameworkCache = Mockery::mock('kanso\framework\cache\Cache');
		
		$cache = new Cache($frameworkCache, 'foobar_key', true);
		
		$frameworkCache->shouldReceive('expired')->withArgs(['foobar_key'])->andReturn(true);
		
		$frameworkCache->shouldReceive('delete')->withArgs(['foobar_key'])->andReturn(true);
		
		$this->assertEquals(null, $cache->get());
	}

	/**
	 *
	 */
	public function testPut()
	{
		$frameworkCache = Mockery::mock('kanso\framework\cache\Cache');
		
		$cache = new Cache($frameworkCache, 'foobar_key', true);

		$frameworkCache->shouldReceive('put')->withArgs(['foobar_key', 'cache this text']);

		$cache->put('cache this text');
		
		$frameworkCache->shouldReceive('expired')->withArgs(['foobar_key'])->andReturn(false);
		
		$frameworkCache->shouldReceive('has')->withArgs(['foobar_key'])->andReturn(true);
		
		$frameworkCache->shouldReceive('get')->withArgs(['foobar_key'])->andReturn('cache this text');

		$this->assertEquals('cache this text', $cache->get());
	}

	/**
	 *
	 */
	public function testHasTrue()
	{
		$frameworkCache = Mockery::mock('kanso\framework\cache\Cache');
		
		$cache = new Cache($frameworkCache, 'foobar_key', true);

		$frameworkCache->shouldReceive('expired')->withArgs(['foobar_key'])->andReturn(false);

		$frameworkCache->shouldReceive('has')->withArgs(['foobar_key'])->andReturn(true);


		$this->assertTrue($cache->has());
	}

	/**
	 *
	 */
	public function testHasFalse()
	{
		$frameworkCache = Mockery::mock('kanso\framework\cache\Cache');
		
		$cache = new Cache($frameworkCache, 'foobar_key', true);

		$frameworkCache->shouldReceive('expired')->withArgs(['foobar_key'])->andReturn(false);

		$frameworkCache->shouldReceive('has')->withArgs(['foobar_key'])->andReturn(false);;

		$this->assertFalse($cache->has());
	}

	/**
	 *
	 */
	public function testDelete()
	{
		$frameworkCache = Mockery::mock('kanso\framework\cache\Cache');
		
		$cache = new Cache($frameworkCache, 'foobar_key', true);

		$frameworkCache->shouldReceive('delete')->withArgs(['foobar_key'])->andReturn(true);

		$cache->delete();
	}
}