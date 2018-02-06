<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\onion;

use Mockery;
use Closure;
use tests\TestCase;
use kanso\framework\onion\Onion;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;

class OnionCallbackTest
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
 * @group unit
 */
class OnionTest extends TestCase
{
	/**
	 *
	 */
	public function testAddLayer()
	{
		$callback = '\directory\ClassName::method';

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$this->assertEquals(1, count($onion->layers()));

		$this->assertEquals($callback, $onion->layers()[0]->getCallback());
	}

	/**
	 *
	 */
	public function testAddLayerInner()
	{
		$callbackOne = '\directory\ClassName::method';

		$callbackTwo = '\directory\ClassName::methodTwo';

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callbackOne, 'foo');

		$onion->addLayer($callbackTwo, 'bar', true);

		$this->assertEquals($callbackTwo, $onion->layers()[0]->getCallback());
	}

	/**
	 *
	 */
	public function testStaticLayer()
	{
		ob_start();

		$callback = '\tests\unit\framework\onion\OnionCallbackTest@normalMethod';

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testNonStaticLayer()
	{
		ob_start();

		$callback = '\tests\unit\framework\onion\OnionCallbackTest::staticFunc';

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testClosure()
	{
		ob_start();

		$callback = function(Request $request, Response $response, $next, $foo)
		{
			echo $foo;
		};

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, 'foo');

		$onion->peel();

		$this->assertEquals('foo', ob_get_clean());
	}

	/**
	 *
	 */
	public function testCallNext()
	{
		ob_start();

		$callbackOne = function(Request $request, Response $response, $next, $foo)
		{
			echo $foo;

			$next();
		};

		$callbackTwo = function(Request $request, Response $response, $next, $bar)
		{
			echo $bar;
		};

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callbackOne, 'foo');

		$onion->addLayer($callbackTwo, 'bar');

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testPeeledEmpty()
	{
		$callback = function(Request $request, Response $response, $next, $foo)
		{
			$next();
		};

		$request = Mockery::mock('\kanso\framework\http\request\Request');

		$response = Mockery::mock('\kanso\framework\http\response\Response');

		$status = Mockery::mock('\kanso\framework\http\response\status');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, 'foo');

		$response->shouldReceive('status')->andReturn($status);

		$status->shouldReceive('get')->andReturn(404);

		$response->shouldReceive('notFound');

		$onion->peel();
	}
}