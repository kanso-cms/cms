<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\cms\event;

use Mockery;
use tests\TestCase;
use kanso\cms\event\Events;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class CallbackTester
{
	public $var;

	public $_this;

	public function __construct($_this, $var)
	{
		$this->var = $var;

		$this->_this = $_this;
	}

	public static function testStaticMethod($_this, $var)
	{
		$_this->assertEquals('bar', $var);
	}

	public function testMethod()
	{
		$this->_this->assertEquals('bar', $this->var);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class GatekeeperTest extends TestCase
{
	/**
	 *
	 */
	public function testCallbacks()
	{
		$_this = $this;

		$events = Events::instance();

		$events->on('foo1', 'tests\unit\cms\event\CallbackTester@testMethod');

		$events->on('foo2', 'tests\unit\cms\event\CallbackTester::testStaticMethod');

		$events->on('foo3', function($_this, $foo)
		{
			$_this->assertEquals('bar', $foo);

		});

		$events->fire('foo1', [$_this, 'bar']);

		$events->fire('foo2', [$_this, 'bar']);

		$events->fire('foo3', [$_this, 'bar']);
	}
}
