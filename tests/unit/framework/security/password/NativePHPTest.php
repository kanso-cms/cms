<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\security\password;

use tests\TestCase;
use kanso\framework\security\password\encrypters\NativePHP;

/**
 * @group unit
 */
class RouterTest extends TestCase
{
	/**
	 *
	 */
	public function testHash()
	{
		$password = new NativePHP;

		$hashed = $password->hash('f43423o$#@$!!$!GEWPG{"__+)_)o');

		$this->assertTrue($password->verify('f43423o$#@$!!$!GEWPG{"__+)_)o', $hashed));
	}

	/**
	 *
	 */
	public function testAlgos()
	{
		$algos = 
		[
			PASSWORD_BCRYPT,
			PASSWORD_DEFAULT
		];

		foreach ($algos as $algo)
		{
			$password = new NativePHP($algo);

			$hashed = $password->hash('f43423o$#@$!!$!GEWPG{"__+)_)o');

			$this->assertTrue($password->verify('f43423o$#@$!!$!GEWPG{"__+)_)o', $hashed));
		}
	}
}