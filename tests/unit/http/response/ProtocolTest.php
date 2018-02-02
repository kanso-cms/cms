<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\response;

use tests\TestCase;
use kanso\framework\http\response\Protocol;

/**
 * @group unit
 */
class ProtocolTest extends TestCase
{
	/**
	 *
	 */
	public function testSet()
	{
		$protocol = new Protocol;

		$protocol->set('https');

		$this->assertEquals('https', $protocol->get());
	}

	/**
	 *
	 */
	public function testSecure()
	{
		$protocol = new Protocol;

		$this->assertFalse($protocol->isSecure());

		$protocol->set('https');
		
		$this->assertTrue($protocol->isSecure());
	}
}
