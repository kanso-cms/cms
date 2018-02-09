<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\http\response;

use tests\TestCase;
use kanso\framework\http\response\Format;

/**
 * @group unit
 */
class FormatTest extends TestCase
{
	/**
	 *
	 */
	public function testSet()
	{
		$format = new Format;

		$format->set('text/html');

		$this->assertEquals('text/html', $format->get());
	}

	/**
	 *
	 */
	public function testExt()
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
	public function testEncoding()
	{
		$format = new Format;

		$format->setEncoding('UTF-8');

		$this->assertEquals('UTF-8', $format->getEncoding());
	}	
}
