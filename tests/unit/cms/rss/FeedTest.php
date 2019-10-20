<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\rss;

use kanso\cms\rss\Feed;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class FeedTest extends TestCase
{
	/**
	 *
	 */
	public function testRss()
	{
		$request  = Mockery::mock('\kanso\framework\http\request\Request');
		$response = Mockery::mock('\kanso\framework\http\response\Response');
		$format   = Mockery::mock('\kanso\framework\http\response\Format');
		$body     = Mockery::mock('\kanso\framework\http\response\Body');
		$view     = Mockery::mock('\kanso\framework\mvc\view\View');
		$status   = Mockery::mock('\kanso\framework\http\response\Status');

		$feed = new Feed($request, $response, 'rss');

		$response->shouldReceive('format')->once()->andReturn($format);

		$response->shouldReceive('body')->once()->andReturn($body);

		$response->shouldReceive('status')->once()->andReturn($status);

		$response->shouldReceive('disableCaching');

		$response->shouldReceive('view')->once()->andReturn($view)->times(3);

		$format->shouldReceive('set')->once()->with('application/rss+xml, application/xml');

		$body->shouldReceive('set')->once();

		$status->shouldReceive('set')->once()->with(200);

		$view->shouldReceive('display')->times(3);

		$feed->render();
	}

	/**
	 *
	 */
	public function testAtom()
	{
		$request  = Mockery::mock('\kanso\framework\http\request\Request');
		$response = Mockery::mock('\kanso\framework\http\response\Response');
		$format   = Mockery::mock('\kanso\framework\http\response\Format');
		$body     = Mockery::mock('\kanso\framework\http\response\Body');
		$view     = Mockery::mock('\kanso\framework\mvc\view\View');
		$status   = Mockery::mock('\kanso\framework\http\response\Status');

		$feed = new Feed($request, $response, 'atom');

		$response->shouldReceive('format')->once()->andReturn($format);

		$response->shouldReceive('body')->once()->andReturn($body);

		$response->shouldReceive('status')->once()->andReturn($status);

		$response->shouldReceive('disableCaching');

		$response->shouldReceive('view')->once()->andReturn($view)->times(3);

		$format->shouldReceive('set')->once()->with('application/atom+xml, application/xml');

		$body->shouldReceive('set')->once();

		$status->shouldReceive('set')->once()->with(200);

		$view->shouldReceive('display')->times(3);

		$feed->render();
	}

	/**
	 *
	 */
	public function testRdf()
	{
		$request  = Mockery::mock('\kanso\framework\http\request\Request');
		$response = Mockery::mock('\kanso\framework\http\response\Response');
		$format   = Mockery::mock('\kanso\framework\http\response\Format');
		$body     = Mockery::mock('\kanso\framework\http\response\Body');
		$view     = Mockery::mock('\kanso\framework\mvc\view\View');
		$status   = Mockery::mock('\kanso\framework\http\response\Status');

		$feed = new Feed($request, $response, 'rdf');

		$response->shouldReceive('format')->once()->andReturn($format);

		$response->shouldReceive('body')->once()->andReturn($body);

		$response->shouldReceive('status')->once()->andReturn($status);

		$response->shouldReceive('disableCaching');

		$response->shouldReceive('view')->once()->andReturn($view)->times(3);

		$format->shouldReceive('set')->once()->with('application/rdf+xml, application/xml');

		$body->shouldReceive('set')->once();

		$status->shouldReceive('set')->once()->with(200);

		$view->shouldReceive('display')->times(3);

		$feed->render();
	}
}
