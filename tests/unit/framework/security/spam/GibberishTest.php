<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\security\spam;

use kanso\framework\security\spam\gibberish\Gibberish;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class GibberishTest extends TestCase
{
	/**
	 *
	 */
	public function testGibberish(): void
	{
		$gibberish = new Gibberish(dirname(__FILE__) . '/Gibberish.txt');

		$this->assertFalse($gibberish->test('Hello world this is real text.'));

		$this->assertTrue($gibberish->test('worfsdfald fasdfreal.'));
	}
}
