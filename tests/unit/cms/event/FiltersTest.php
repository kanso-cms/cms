<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\event;

use kanso\cms\event\Filters;
use kanso\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class FilterCallbackTester
{
	public $var;

	public function __construct($var)
	{
		$this->var = $var;
	}

	public static function testStaticMethodFirst($var)
	{
		return 'foo' . $var;
	}

	public static function testStaticMethodSecond($var)
	{
		return $var . 'baz';
	}

	public function testMethodFirst()
	{
		return 'foo' . $this->var;
	}

	public function testMethodSecond()
	{
		return $this->var . 'baz';
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 * @group cms
 */
class FiltersTest extends TestCase
{
	/**
	 *
	 */
	public function testCallbacks(): void
	{
		$_this = $this;

		$filters = Filters::instance();

		$filters->on('foo1', '\kanso\tests\unit\cms\event\FilterCallbackTester::testStaticMethodFirst');

		$filters->on('foo1', '\kanso\tests\unit\cms\event\FilterCallbackTester::testStaticMethodSecond');

		$filters->on('foo2', '\kanso\tests\unit\cms\event\FilterCallbackTester@testMethodFirst');

		$filters->on('foo2', '\kanso\tests\unit\cms\event\FilterCallbackTester@testMethodSecond');

		$filters->on('foo3', function($var)
		{
			return 'foo' . $var;
		});

		$filters->on('foo3', function($var)
		{
			return $var . 'baz';
		});

		$this->assertEquals('foobarbaz', $filters->apply('foo1', 'bar'));

		$this->assertEquals('foobarbaz', $filters->apply('foo2', 'bar'));

		$this->assertEquals('foobarbaz', $filters->apply('foo3', 'bar'));
	}
}
