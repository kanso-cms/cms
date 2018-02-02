<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\cookie;

use Mockery;
use tests\TestCase;
use kanso\framework\http\cookie\storage\NativeCookieStorage;

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
			'httponly'     => true,
			'storage'      =>
			[
				'type' => 'native',
			],
		];
	}

	/**
	 *
	 */
	public function testRead()
	{
		$_COOKIE['foobar_cookie'] = 's:0{fdf[$@#$!sd23fs==}';

		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('decrypt')->withArgs(['s:0{fdf[$@#$!sd23fs==}'])->andReturn(serialize(['foo' => 'bar']));
		
		$this->assertEquals(['foo' => 'bar'], $store->read('foobar_cookie'));
	}

	/**
	 *
	 */
	public function testReadInvalid()
	{
		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('decrypt')->withArgs(['s:0{fdf[$@#$!sd23fs==}'])->andReturn(false);
		
		$this->assertEquals(false, $store->read('foobar_cookie'));
	}

	/**
	 * @runInSeparateProcess
	 * 
	 */
	public function testWrite()
	{
		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		$store = new NativeCookieStorage($crypto, $this->getCookieConfig());

		$crypto->shouldReceive('encrypt')->withArgs([serialize(['foo' => 'bar'])])->andReturn('s:0{fdf[$@#$!sd23fs==}');
		
		$store->write('foobar_cookie', ['foo' => 'bar']);

		$this->assertEquals('s:0{fdf[$@#$!sd23fs==}', $_COOKIE['foobar_cookie']);
	}
}