<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\cli\output\helpers;

use kanso\framework\cli\output\Output;
use kanso\framework\cli\output\Formatter;
use kanso\framework\cli\output\helpers\OrderedList;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class OrderedListTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicList()
	{
		$formatter = new Formatter;
		$output    = Mockery::mock(Output::class);
		$output->shouldReceive('formatter')->once()->andReturn($formatter);
		$list = new OrderedList($output);
		$expected  = '';
		$expected .= '1. one' . PHP_EOL;
		$expected .= '2. two' . PHP_EOL;
		$expected .= '3. three' . PHP_EOL;
		$this->assertSame($expected, $list->render(['one', 'two', 'three']));
	}
	/**
	 *
	 */
	public function testNestedLists()
	{
		$formatter = new Formatter;
		$output    = Mockery::mock(Output::class);
		$output->shouldReceive('formatter')->once()->andReturn($formatter);
		$list = new OrderedList($output);
		$expected  = '';
		$expected .= '1. one' . PHP_EOL;
		$expected .= '2. two' . PHP_EOL;
		$expected .= '3. three' . PHP_EOL;
		$expected .= '   1. one' . PHP_EOL;
		$expected .= '   2. two' . PHP_EOL;
		$expected .= '   3. three' . PHP_EOL;
		$expected .= '4. four' . PHP_EOL;
		$this->assertSame($expected, $list->render(['one', 'two', 'three', ['one', 'two', 'three'], 'four']));
	}
	/**
	 *
	 */
	public function testCustomMarker()
	{
		$formatter = new Formatter;
		$output    = Mockery::mock(Output::class);
		$output->shouldReceive('formatter')->once()->andReturn($formatter);
		$list = new OrderedList($output);
		$expected  = '';
		$expected .= '[1] one' . PHP_EOL;
		$expected .= '[2] two' . PHP_EOL;
		$expected .= '[3] three' . PHP_EOL;
		$this->assertSame($expected, $list->render(['one', 'two', 'three'], '[%s]'));
	}
}
