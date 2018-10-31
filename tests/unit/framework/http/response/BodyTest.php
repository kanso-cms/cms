<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\response;

use kanso\framework\http\response\Body;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class BodyTest extends TestCase
{
	/**
	 *
	 */
	public function testSet()
	{
		$body = new Body;

		$body->set('foo');

		$this->assertEquals('foo', $body->get());
	}

	/**
	 *
	 */
	public function testClear()
	{
		$body = new Body;

		$body->set('foo');

		$body->clear();

		$this->assertEquals('', $body->get());
	}

	/**
	 *
	 */
	public function testAppend()
	{
		$body = new Body;

		$body->set('foo');

		$body->append(' bar');

		$this->assertEquals('foo bar', $body->get());
	}

	/**
	 *
	 */
	public function testLength()
	{
		$body = new Body;

		$body->set('foo');

		$this->assertEquals(3, $body->length());
	}
}
