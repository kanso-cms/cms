<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\common;

use tests\TestCase;
use IteratorAggregate;
use kanso\framework\common\ArrayIterator;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestArrayIteratorTrait implements IteratorAggregate
{
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class ArrayIteratorTest extends TestCase
{
	/**
	 * @runInSeparateProcess
	 */
	public function testIterator()
	{
		$objectArray = new TestArrayIteratorTrait(['foo', 'bar', 'baz']);

		$count = 0;

		foreach ($objectArray as $key => $value)
		{
			$count++;
		}

		$this->assertEquals(3, $count);
	}
}