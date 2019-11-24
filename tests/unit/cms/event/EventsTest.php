<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\event;

use kanso\cms\event\Events;
use kanso\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class EventsCallbackTester
{
	public $var;

	public $_this;

	public function __construct($_this, $var)
	{
		$this->var = $var;

		$this->_this = $_this;
	}

	public static function testStaticMethod($_this, $var): void
	{
		$_this->assertEquals('bar', $var);
	}

	public function testMethod(): void
	{
		$this->_this->assertEquals('bar', $this->var);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 * @group cms
 */
class EventsTest extends TestCase
{
	/**
	 *
	 */
	public function testCallbacks(): void
	{
		$_this = $this;

		$events = Events::instance();

		$events->on('foo1', '\kanso\tests\unit\cms\event\EventsCallbackTester@testMethod');

		$events->on('foo2', '\kanso\tests\unit\cms\event\EventsCallbackTester::testStaticMethod');

		$events->on('foo3', function($_this, $foo): void
		{
			$_this->assertEquals('bar', $foo);

		});

		$events->fire('foo1', [$_this, 'bar']);

		$events->fire('foo2', [$_this, 'bar']);

		$events->fire('foo3', [$_this, 'bar']);
	}
}
