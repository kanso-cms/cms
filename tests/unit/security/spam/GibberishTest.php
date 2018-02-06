<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\security\spam;

use tests\TestCase;
use kanso\framework\security\spam\gibberish\Gibberish;

/**
 * @group unit
 */
class GibberishTest extends TestCase
{
	/**
	 *
	 */
	public function testGibberish()
	{
		$gibberish = new Gibberish(dirname(__FILE__).'/Gibberish.txt');

		$this->assertFalse($gibberish->test('Hello world this is real text.'));

		$this->assertTrue($gibberish->test('worfsdfald fasdfreal.'));
	}	
}
