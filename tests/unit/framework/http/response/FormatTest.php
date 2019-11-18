<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\response;

use kanso\framework\http\response\Format;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class FormatTest extends TestCase
{
	/**
	 *
	 */
	public function testSet(): void
	{
		$format = new Format;

		$format->set('text/html');

		$this->assertEquals('text/html', $format->get());
	}

	/**
	 *
	 */
	public function testExt(): void
	{
		$format = new Format;

		$format->set('html');

		$this->assertEquals('text/html', $format->get());

		$format->set('json');

		$this->assertEquals('application/json', $format->get());

		$format->set('png');

		$this->assertEquals('image/png', $format->get());
	}

	/**
	 *
	 */
	public function testEncoding(): void
	{
		$format = new Format;

		$format->setEncoding('UTF-8');

		$this->assertEquals('UTF-8', $format->getEncoding());
	}
}
