<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\http\session;

use Mockery;
use tests\TestCase;
use kanso\framework\http\session\Token;

/**
 * @group unit
 */
class TokenTest extends TestCase
{
	/**
	 *
	 */
	public function testDefault()
	{
		$token = new Token;

		$this->assertEquals('', $token->get());
	}

	/**
	 *
	 */
	public function testSet()
	{
		$token = new Token;

		$token->set('fobar');

		$this->assertEquals('fobar', $token->get());
	}

	/**
	 *
	 */
	public function testRegenerate()
	{
		$token = new Token;

		$token->set('fobar');

		$this->assertEquals('fobar', $token->get());

		$token->regenerate();

		$this->assertFalse($token->get() === 'foobar');
	}

	/**
	 *
	 */
	public function testVerify()
	{
		$token = new Token;

		$token->set('fobar');

		$this->assertEquals('fobar', $token->get());

		$this->assertTrue($token->verify('fobar'));
	}
}
