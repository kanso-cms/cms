<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\cookie;

use kanso\framework\http\cookie\Cookie;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class CookieTest extends TestCase
{
	/**
	 * @runInSeparateProcess
	 */
	public function testConstructor(): void
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['foo' => 'bar']);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn(false);

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testIsLoggedIn(): void
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['foo' => 'bar']);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn(false);

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$this->assertFalse($cookie->isLoggedIn());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testLogin(): void
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['foo' => 'bar']);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('yes');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->login();

		$this->assertTrue($cookie->isLoggedIn());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testLogout(): void
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(['foo' => 'bar']);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('yes');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->logout();

		$this->assertFalse($cookie->isLoggedIn());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSend(): void
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
	 * @runInSeparateProcess
	 */
	public function testExpired(): void
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
	 * @runInSeparateProcess
	 */
	public function testSent(): void
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
	 * @runInSeparateProcess
	 */
	public function testDestroy(): void
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
	 * @runInSeparateProcess
	 */
	public function testSet(): void
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->set('foo', 'bar');

		$this->assertEquals('bar', $cookie->get('foo'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSetMultiple(): void
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
	 * @runInSeparateProcess
	 */
	public function testGetAll(): void
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->set('foo', 'bar');

		$this->assertEquals(['last_active' => time(), 'foo' => 'bar'], $cookie->get());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testHas(): void
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
	 * @runInSeparateProcess
	 */
	public function testRemove(): void
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
	 * @runInSeparateProcess
	 */
	public function testClear(): void
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->clear();

		$this->assertEquals([], $cookie->get());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testAsArray(): void
	{

		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn(null);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$this->assertEquals(['last_active' => time()], $cookie->asArray());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testIterator(): void
	{
		$store = Mockery::mock('kanso\framework\http\cookie\storage\NativeCookieStorage');

		$store->shouldReceive('read')->withArgs(['cookie_name'])->andReturn([]);

		$store->shouldReceive('read')->withArgs(['cookie_name_login'])->andReturn('no');

		$cookie = new Cookie($store, 'cookie_name', strtotime('+1 month'));

		$cookie->set('foo', 'bar');

		$count = 0;

		foreach ($cookie as $key => $value)
		{
			$count++;
		}

		$this->assertEquals(2, $count);
	}
}
