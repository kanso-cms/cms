<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\cookie;

use kanso\framework\http\cookie\storage\NativeCookieStorage;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
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
	public function testRead(): void
	{
		$_COOKIE = [];

		$_COOKIE['foobar_cookie'] = 's:0{fdf[$@#$!sd23fs==}';

		$crypto = $this->mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('decrypt')->withArgs(['s:0{fdf[$@#$!sd23fs==}'])->andReturn(serialize(['foo' => 'bar']));

		$this->assertEquals(['foo' => 'bar'], $store->read('foobar_cookie'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testReadInvalid(): void
	{
		$_COOKIE = [];

		$crypto = $this->mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('decrypt')->withArgs(['s:0{fdf[$@#$!sd23fs==}'])->andReturn(false);

		$this->assertEquals(false, $store->read('foobar_cookie'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testWrite(): void
	{
		$_COOKIE = [];

		$crypto = $this->mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('encrypt')->withArgs([serialize(['foo' => 'bar'])])->andReturn('s:0{fdf[$@#$!sd23fs==}');

		$this->assertEquals(true, $store->write('foobar_cookie', ['foo' => 'bar']));
	}
}
