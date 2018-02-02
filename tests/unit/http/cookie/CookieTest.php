<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\cookie;

use Mockery;
use tests\TestCase;
use kanso\framework\http\cookie\Cookie;

/**
 * @group unit
 */
class CacheTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructor()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['foo' => 'bar']);
		
		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn(false);

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));
	}

	/**
	 *
	 */
	public function testIsLoggedIn()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['foo' => 'bar']);
		
		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn(false);

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$this->assertFalse($cookie->isLoggedIn());
	}

	/**
	 *
	 */
	public function testLogin()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['foo' => 'bar']);
		
		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('yes');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->login();

		$this->assertTrue($cookie->isLoggedIn());
	}

	/**
	 *
	 */
	public function testLogout()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['foo' => 'bar']);
		
		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('yes');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->logout();

		$this->assertFalse($cookie->isLoggedIn());
	}

	/**
	 *
	 */
	public function testSend()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['last_active' => strtotime('-1 hour'), 'foo' => 'bar']);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$store->shouldReceive('write')->withArgs(['cookie_name', ['last_active' => time(), 'foo' => 'bar']]);

		$store->shouldReceive('write')->withArgs(['cookie_name_login', 'no']);

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->send();
	}

	/**
	 *
	 */
	public function testExpired()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['last_active' => strtotime('-34 days'), 'foo' => 'bar']);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$store->shouldReceive('write')->withArgs(['cookie_name', ['last_active' => time()]]);

		$store->shouldReceive('write')->withArgs(['cookie_name_login', 'no']);

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->send();
	}

	/**
	 *
	 */
	public function testSent()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$store->shouldReceive('write')->withArgs(['cookie_name', ['last_active' => time()]]);

		$store->shouldReceive('write')->withArgs(['cookie_name_login', 'no']);

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$this->assertFalse($cookie->sent());

		$cookie->send();

		$this->assertTrue($cookie->sent());
	}

	/**
	 *
	 */
	public function testDestroy()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['last_active' => strtotime('-1 hour'), 'foo' => 'bar']);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('yes');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$this->assertEquals(['last_active' => time(), 'foo' => 'bar'], $cookie->get());

		$cookie->destroy();

		$this->assertFalse($cookie->isLoggedIn());

		$this->assertEquals(['last_active' => time()], $cookie->get());
	}

	/**
	 *
	 */
	public function testSet()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->set('foo', 'bar');

		$this->assertEquals('bar', $cookie->get('foo'));
	}

	/**
	 *
	 */
	public function testSetMultiple()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->setMultiple(['foo' => 'bar', 'bar' => 'foo']);

		$this->assertEquals('bar', $cookie->get('foo'));

		$this->assertEquals('foo', $cookie->get('bar'));
	}

	/**
	 *
	 */
	public function testGetAll()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->set('foo', 'bar');

		$this->assertEquals(['last_active' => time(), 'foo' => 'bar'], $cookie->get());
	}

	/**
	 *
	 */
	public function testHas()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$this->assertFalse($cookie->has('foo'));

		$cookie->set('foo', 'bar');

		$this->assertTrue($cookie->has('foo'));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->set('foo', 'bar');

		$this->assertTrue($cookie->has('foo'));

		$cookie->remove('foo');

		$this->assertFalse($cookie->has('foo'));
	}

	/**
	 *
	 */
	public function testClear()
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->clear();

		$this->assertEquals([], $cookie->get());
	}

	/**
	 *
	 */
	public function testAsArray()
	{

		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$this->assertEquals(['last_active' => time()], $cookie->asArray());
	}
}
