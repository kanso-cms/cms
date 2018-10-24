<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\http\cookie;

use kanso\framework\http\cookie\storage\NativeCookieStorage;
use Mockery;
use tests\TestCase;

/**
 * @group unit
 */
class NativeStorageTest extends TestCase
{
	private function getCookieConfig()
	{
		return
		[
			'name'         => 'kanso_cookie',
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
	public function testRead()
	{
		$_COOKIE = [];

		$_COOKIE['foobar_cookie'] = 's:0{fdf[$@#$!sd23fs==}';

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('decrypt')->withArgs(['s:0{fdf[$@#$!sd23fs==}'])->andReturn(serialize(['foo' => 'bar']));

		$this->assertEquals(['foo' => 'bar'], $store->read('foobar_cookie'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testReadInvalid()
	{
		$_COOKIE = [];

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('decrypt')->withArgs(['s:0{fdf[$@#$!sd23fs==}'])->andReturn(false);

		$this->assertEquals(false, $store->read('foobar_cookie'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testWrite()
	{
		$_COOKIE = [];

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('encrypt')->withArgs([serialize(['foo' => 'bar'])])->andReturn('s:0{fdf[$@#$!sd23fs==}');

		$this->assertEquals(true, $store->write('foobar_cookie', ['foo' => 'bar']));
	}
}
