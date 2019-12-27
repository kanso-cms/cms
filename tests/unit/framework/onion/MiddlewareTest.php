<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\onion;

use Closure;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\onion\Middleware;

class MiddleWareCallbackTest
{
	public function __construct(Request $request, Response $response, Closure $next, $arg1, $arg2)
    {
    	$this->var = $arg1 . $arg2;
    }

    public function normalMethod(): void
    {
    	echo $this->var;
    }

	public static function staticFunc(Request $request, Response $response, Closure $next, $arg1, $arg2): void
	{
		echo $arg1 . $arg2;
	}
}

/**
 * Callback tester.
 */
class MiddlewareTest
{
	/**
	 *
	 */
	public function testNonStaticClassMethod(): void
	{
		ob_start();

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest@normalMethod', ['foo', 'bar']);

		$next = function(): void
		{

		};

		$layer->execute($request, $response, $next);

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testStaticClassMethod(): void
	{
		ob_start();

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', ['foo', 'bar']);

		$next = function(): void
		{

		};

		$layer->execute($request, $response, $next);

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testColsure(): void
	{
		ob_start();

		$callback = function(Request $request, Response $response, $next, $foo): void
		{
			echo $foo;
		};

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$layer = new Middleware($callback, ['foo', 'bar']);

		$layer->execute($request, $response, $callback);

		$this->assertEquals('foo', ob_get_clean());
	}

	/**
	 *
	 */
	public function testGetCallback(): void
	{
		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', ['foo', 'bar']);

		$this->assertEquals('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', $layer->getCallback());
	}

	/**
	 *
	 */
	public function testGetArgs(): void
	{
		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', ['foo', 'bar']);

		$this->assertEquals(['foo', 'bar'], $layer->getArgs());

		$layer = new Middleware('\tests\unit\framework\onion\MiddleWareCallbackTest::staticFunc', 'foo');

		$this->assertEquals(['foo'], $layer->getArgs());
	}
}
