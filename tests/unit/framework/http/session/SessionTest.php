<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\session;

use kanso\framework\http\session\Session;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class SessionTest extends TestCase
{
	private function getConfig()
	{
		return
		[
			'cookie_name'  => 'kanso_session',
			'expire'       => '+1 month',
			'path'         => '/',
			'domain'       => '',
			'secure'       => false,
			'httponly'     => true,
		];
	}

	/**
	 *
	 */
	private function mockSession()
	{
		$token = $this->mock('kanso\framework\http\session\Token');

		$flash = $this->mock('kanso\framework\http\session\Flash');

		$store = $this->mock('kanso\framework\http\session\storage\NativeSessionStorage');

		$store->shouldReceive('session_name')->withArgs(['kanso_session'])->andReturn('kanso_session');

		$store->shouldReceive('session_set_cookie_params')->withArgs([$this->getConfig()]);

		$store->shouldReceive('session_start');

		$store->shouldReceive('read')->andReturn(false);

		$flash->shouldReceive('iterate');

		$token->shouldReceive('get')->andReturn('foobar');

		return new Session($token, $flash, $store, $this->getConfig());
	}

	/**
	 *
	 */
	public function testConstructor(): void
	{
		$this->mockSession();
	}

	/**
	 *
	 */
	public function testIteration(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$i = 0;

		foreach ($session as $key => $value)
		{
			$i++;
		}

		$this->assertEquals(1, $i);
	}

	/**
	 *
	 */
	public function testSet(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$this->assertEquals('bar', $session->get('foo'));
	}

	/**
	 *
	 */
	public function testSetMultiple(): void
	{
		$session = $this->mockSession();

		$session->setMultiple([
		    'foo' => 'bar',
		    'bar' => 'foo',
		]);

		$this->assertEquals('bar', $session->get('foo'));

		$this->assertEquals('foo', $session->get('bar'));
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$this->assertTrue($session->has('foo'));
	}

	/**
	 *
	 */
	public function testHasNot(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$this->assertFalse($session->has('bar'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$this->assertEquals('bar', $session->get('foo'));

		$this->assertNull($session->get('bar'));
	}

	/**
	 *
	 */
	public function testGetAll(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$session->set('bar', 'foo');

		$this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $session->get());
	}

	/**
	 *
	 */
	public function testAsArray(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$session->set('bar', 'foo');

		$this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $session->asArray());
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$session->remove('foo');

		$this->assertNull($session->get('foo'));
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$session = $this->mockSession();

		$session->set('foo', 'bar');

		$session->clear();

		$this->assertEquals([], $session->get());
	}

	/**
	 *
	 */
	public function testSave(): void
	{
		$token = $this->mock('kanso\framework\http\session\Token');

		$flash = $this->mock('kanso\framework\http\session\Flash');

		$store = $this->mock('kanso\framework\http\session\storage\NativeSessionStorage');

		$store->shouldReceive('session_name')->withArgs(['kanso_session'])->andReturn('kanso_session');

		$store->shouldReceive('session_set_cookie_params')->withArgs([$this->getConfig()]);

		$store->shouldReceive('session_start');

		$store->shouldReceive('read')->andReturn(false);

		$flash->shouldReceive('iterate');

		$token->shouldReceive('get')->andReturn('foobartoken');

		$session = new Session($token, $flash, $store, $this->getConfig());

		$session->set('foo', 'bar');

		$flash->shouldReceive('getRaw')->andReturn(['flash' => 'bar']);

		$token->shouldReceive('get')->andReturn(['foobartoken']);

		$store->shouldReceive('write')->withArgs([[
			'kanso_data'  => ['foo' => 'bar'],
			'kanso_flash' => ['flash' => 'bar'],
			'kanso_token' => 'foobartoken',
		]]);

		$store->shouldReceive('send');

		$session->save();
	}

	/**
	 *
	 */
	public function testConfigure(): void
	{
		$token = $this->mock('kanso\framework\http\session\Token');

		$flash = $this->mock('kanso\framework\http\session\Flash');

		$store = $this->mock('kanso\framework\http\session\storage\NativeSessionStorage');

		$store->shouldReceive('session_name')->once()->withArgs(['kanso_session'])->andReturn('kanso_session');

		$store->shouldReceive('session_set_cookie_params')->withArgs([$this->getConfig()]);

		$store->shouldReceive('session_start');

		$store->shouldReceive('read')->andReturn(false);

		$flash->shouldReceive('iterate');

		$token->shouldReceive('get')->andReturn('foobartoken');

		$session = new Session($token, $flash, $store, $this->getConfig());

		$config = $this->getConfig();

		$config['cookie_name'] = 'foobar_cookie_name';

		$store->shouldReceive('session_name')->withArgs(['foobar_cookie_name']);

		$store->shouldReceive('session_set_cookie_params')->withArgs([$config]);

		$session->configure($config);
	}

	/**
	 *
	 */
	public function testDestroy(): void
	{
		$token = $this->mock('kanso\framework\http\session\Token');

		$flash = $this->mock('kanso\framework\http\session\Flash');

		$store = $this->mock('kanso\framework\http\session\storage\NativeSessionStorage');

		$store->shouldReceive('session_name')->withArgs(['kanso_session'])->andReturn('kanso_session');

		$store->shouldReceive('session_set_cookie_params')->withArgs([$this->getConfig()]);

		$store->shouldReceive('session_start');

		$store->shouldReceive('read')->andReturn(false);

		$flash->shouldReceive('iterate');

		$token->shouldReceive('get')->andReturn('foobar');

		$session = new Session($token, $flash, $store, $this->getConfig());

		$token->shouldReceive('regenerate');

		$flash->shouldReceive('clear');

		$session->set('foo', 'bar');

		$session->destroy();

		$this->assertEquals([], $session->get());
	}
}
