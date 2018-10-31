<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\utility;

use kanso\framework\utility\Callback;
use kanso\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class CallbackTester
{
	public $var;

	public function __construct($var)
	{
		$this->var = $var;
	}

	public static function testStaticMethod($foo)
	{
		return $foo;
	}

	public function testMethod()
	{
		return $this->var;
	}

	public static function testStaticMethods($foo, $bar)
	{
		return $foo . $bar;
	}
}

class CallbackTesters
{
	public $foo;

	public $bar;

	public function __construct($foo, $bar)
	{
		$this->foo = $foo;

		$this->bar = $bar;
	}

	public function testMethods()
	{
		return $this->foo . $this->bar;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 * @group framework
 */
class CallbackTest extends TestCase
{
	/**
	 *
	 */
	public function testCallbacks()
	{
		$this->assertEquals('foo', Callback::apply('\kanso\tests\unit\framework\utility\CallbackTester@testMethod', 'foo'));

		$this->assertEquals('foo', Callback::apply('\kanso\tests\unit\framework\utility\CallbackTester::testStaticMethod', 'foo'));

		$this->assertEquals('foobar', Callback::apply('\kanso\tests\unit\framework\utility\CallbackTester::testStaticMethods', ['foo', 'bar']));

		$this->assertEquals('foobar', Callback::apply('\kanso\tests\unit\framework\utility\CallbackTesters@testMethods', ['foo', 'bar']));

		$this->assertEquals('foobar', Callback::apply(function($foo, $bar)
		{
			return $foo . $bar;

		}, ['foo', 'bar']));
	}
}
