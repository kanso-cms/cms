<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\http;

use kanso\framework\http\route\Router;
use Mockery;
use tests\TestCase;

/**
 * @group unit
 */
class RouterTest extends TestCase
{
	/**
	 *
	 */
	public function testMethods()
	{
		$request = Mockery::mock('\kanso\framework\http\request\Request');
		$onion   = Mockery::mock('\kanso\framework\onion\Onion');
		$router  = new Router($request, $onion);

		$router->get('/foo', 'FooController::fooAction');
		$router->post('/foo', 'FooController::fooAction');
		$router->put('/foo', 'FooController::fooAction');
		$router->patch('/foo', 'FooController::fooAction');
		$router->delete('/foo', 'FooController::fooAction');
		$router->head('/foo', 'FooController::fooAction');
		$router->options('/foo', 'FooController::fooAction');

		$routes = $router->getRoutes();

		$this->assertEquals(['uri' => 'foo', 'method' => 'GET', 'callback' => 'FooController::fooAction', 'args' => null], $routes[0]);
		$this->assertEquals(['uri' => 'foo', 'method' => 'POST', 'callback' => 'FooController::fooAction', 'args' => null], $routes[1]);
		$this->assertEquals(['uri' => 'foo', 'method' => 'PUT', 'callback' => 'FooController::fooAction', 'args' => null], $routes[2]);
		$this->assertEquals(['uri' => 'foo', 'method' => 'PATCH', 'callback' => 'FooController::fooAction', 'args' => null], $routes[3]);
		$this->assertEquals(['uri' => 'foo', 'method' => 'DELETE', 'callback' => 'FooController::fooAction', 'args' => null], $routes[4]);
		$this->assertEquals(['uri' => 'foo', 'method' => 'HEAD', 'callback' => 'FooController::fooAction', 'args' => null], $routes[5]);
		$this->assertEquals(['uri' => 'foo', 'method' => 'OPTIONS', 'callback' => 'FooController::fooAction', 'args' => null], $routes[6]);
	}

	/**
	 *
	 */
	public function testDispatch()
	{
		$request = Mockery::mock('\kanso\framework\http\request\Request');
		$onion   = Mockery::mock('\kanso\framework\onion\Onion');
		$router  = new Router($request, $onion);

		$router->get('/foobar/', '\directory\ClassName@exampleMethod', 'foobar');

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('foobar/');

		$onion->shouldReceive('addLayer')->withArgs(['\directory\ClassName@exampleMethod', 'foobar']);

		$router->dispatch();
	}

	/**
	 *
	 */
	public function testRegex()
	{
		$regex =
		[
			'(:any)'      => 'barfdf-343423-fsd#$@43/',
			'(:num)'      => '4324',
			'(:all)'      => 'fsdfp/fdsfs/fasd/?fdfs=3242',
			'(:year)'     => '2003',
			'(:month)'    => '11',
			'(:day)'      => '22',
			'(:hour)'     => '33',
			'(:minute)'   => '55',
			'(:second)'   => '34',
			'(:postname)' => 'fdfso-fsdfs-fsf423/',
			'(:category)' => 'fdfso-fsdfs-f43sf/',
			'(:author)'   => 'fdfso-fsdfs-fs432f/',
		];

		foreach ($regex as $regex => $url)
		{
			$request = Mockery::mock('\kanso\framework\http\request\Request');
			$onion   = Mockery::mock('\kanso\framework\onion\Onion');
			$router  = new Router($request, $onion);

			$router->get('/foobar/' . $regex . '/', '\directory\ClassName@exampleMethod', 'foobar');

			$request->shouldReceive('getMethod')->andReturn('GET');

			$request->shouldReceive('path')->andReturn('foobar/' . $url);

			$onion->shouldReceive('addLayer')->withArgs(['\directory\ClassName@exampleMethod', 'foobar']);

			$router->dispatch();
		}
	}

	/**
	 * @expectedException kanso\framework\http\response\exceptions\NotFoundException
	 */
	public function testNotFound()
	{
		$request = Mockery::mock('\kanso\framework\http\request\Request');
		$onion   = Mockery::mock('\kanso\framework\onion\Onion');
		$router  = new Router($request, $onion);

		$router->get('/foobar/', '\directory\ClassName@exampleMethod', 'foobar');

		$request->shouldReceive('getMethod')->andReturn('GET');

		$request->shouldReceive('path')->andReturn('foobaz/');

		$router->dispatch();
	}

	/**
	 * @expectedException kanso\framework\http\response\exceptions\MethodNotAllowedException
	 */
	public function testInvalidMethod()
	{
		$request = Mockery::mock('\kanso\framework\http\request\Request');
		$onion   = Mockery::mock('\kanso\framework\onion\Onion');
		$router  = new Router($request, $onion);

		$router->get('/foobar/', '\directory\ClassName@exampleMethod', 'foobar');

		$request->shouldReceive('getMethod')->andReturn('POST');

		$request->shouldReceive('path')->andReturn('foobar/');

		$router->dispatch();
	}
}
