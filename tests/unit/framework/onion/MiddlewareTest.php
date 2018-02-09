<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\onion;

use Mockery;
use Closure;
use tests\TestCase;
use kanso\framework\onion\Middleware;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;

class MiddleWareCallbackTest
{
	public function __construct(Request $request, Response $response, Closure $next, $arg1, $arg2)
    { 
    	$this->var = $arg1.$arg2;
    }

    public function normalMethod()
    {
    	echo $this->var;
    }

	public static function staticFunc(Request $request, Response $response, Closure $next, $arg1, $arg2)
	{
		echo $arg1.$arg2;
	}
}

/**
 * Callback tester
 */
class MiddlewareTest
{
	/**
	 *
	 */
	public function testNonStaticClassMethod()
	{
		ob_start();

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest@normalMethod', ['foo', 'bar']);

		$next = function()
		{

		};

		$layer->execute($request, $response, $next);

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testStaticClassMethod()
	{
		ob_start();

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', ['foo', 'bar']);

		$next = function()
		{

		};

		$layer->execute($request, $response, $next);

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testColsure()
	{
		ob_start();

		$callback = function(Request $request, Response $response, $next, $foo)
		{
			echo $foo;
		};

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$layer = new Middleware($callback, ['foo', 'bar']);

		$layer->execute($request, $response, $callback);

		$this->assertEquals('foo', ob_get_clean());
	}

	/**
	 *
	 */
	public function testGetCallback()
	{
		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', ['foo', 'bar']);

		$this->assertEquals('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', $layer->getCallback());
	}

	/**
	 *
	 */
	public function testGetArgs()
	{
		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', ['foo', 'bar']);

		$this->assertEquals(['foo', 'bar'], $layer->getArgs());

		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', 'foo');

		$this->assertEquals(['foo'], $layer->getArgs());
	}
}
