<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\onion;

use Closure;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\onion\Onion;
use kanso\tests\TestCase;

class OnionCallbackTest
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
 * @group unit
 * @group framework
 */
class OnionTest extends TestCase
{
	/**
	 *
	 */
	public function testAddLayer(): void
	{
		$callback = '\directory\ClassName::method';

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$this->assertEquals(1, count($onion->layers()));

		$this->assertEquals($callback, $onion->layers()[0]->getCallback());
	}

	/**
	 *
	 */
	public function testAddLayerInner(): void
	{
		$callbackOne = '\directory\ClassName::method';

		$callbackTwo = '\directory\ClassName::methodTwo';

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callbackOne, 'foo');

		$onion->addLayer($callbackTwo, 'bar', true);

		$this->assertEquals($callbackTwo, $onion->layers()[0]->getCallback());
	}

	/**
	 *
	 */
	public function testStaticLayer(): void
	{
		ob_start();

		$callback = '\kanso\tests\unit\framework\onion\OnionCallbackTest@normalMethod';

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testNonStaticLayer(): void
	{
		ob_start();

		$callback = '\kanso\tests\unit\framework\onion\OnionCallbackTest::staticFunc';

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, ['foo', 'bar']);

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testClosure(): void
	{
		ob_start();

		$callback = function(Request $request, Response $response, $next, $foo): void
		{
			echo $foo;
		};

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, 'foo');

		$onion->peel();

		$this->assertEquals('foo', ob_get_clean());
	}

	/**
	 *
	 */
	public function testCallNext(): void
	{
		ob_start();

		$callbackOne = function(Request $request, Response $response, $next, $foo): void
		{
			echo $foo;

			$next();
		};

		$callbackTwo = function(Request $request, Response $response, $next, $bar): void
		{
			echo $bar;
		};

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$onion = new Onion($request, $response);

		$onion->addLayer($callbackOne, 'foo');

		$onion->addLayer($callbackTwo, 'bar');

		$onion->peel();

		$this->assertEquals('foobar', ob_get_clean());
	}

	/**
	 *
	 */
	public function testPeeledEmpty(): void
	{
		$callback = function(Request $request, Response $response, $next, $foo): void
		{
			$next();
		};

		$request = $this->mock('\kanso\framework\http\request\Request');

		$response = $this->mock('\kanso\framework\http\response\Response');

		$status = $this->mock('\kanso\framework\http\response\status');

		$onion = new Onion($request, $response);

		$onion->addLayer($callback, 'foo');

		$response->shouldReceive('status')->andReturn($status);

		$status->shouldReceive('get')->andReturn(404);

		$response->shouldReceive('notFound');

		$onion->peel();
	}
}
