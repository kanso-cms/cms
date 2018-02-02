<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\session;

use Mockery;
use tests\TestCase;
use kanso\framework\http\session\storage\NativeSessionStorage;

/**
 * @group unit
 */
class NativeStorageTest extends TestCase
{
	private function getSessionConfig()
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

	private function mockStorage()
	{
		$crypto = Mockery::mock('kanso\framework\security\Crypto');

		return new NativeSessionStorage($crypto, $this->getSessionConfig(), '/foo/bar/storage');
	}

	public function testSavePath()
	{
		$storage = $this->mockStorage();

		$storage->session_save_path('foo/bar');

		$this->assertEquals('foo/bar', $storage->session_save_path());
	}
}