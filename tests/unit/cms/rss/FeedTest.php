<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\cms\rss;

use Mockery;
use tests\TestCase;
use kanso\cms\rss\Feed;

/**
 * @group unit
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
		$cache    = Mockery::mock('\kanso\framework\http\response\Cache');

		$feed = new Feed($request, $response, 'rss');

		$response->shouldReceive('format')->once()->andReturn($format);

		$response->shouldReceive('body')->once()->andReturn($body);

		$response->shouldReceive('status')->once()->andReturn($status);

		$response->shouldReceive('cache')->once()->andReturn($cache);

		$response->shouldReceive('view')->once()->andReturn($view)->times(3);

		$format->shouldReceive('set')->once()->with('application/rss+xml, application/xml');
		
		$body->shouldReceive('set')->once();

		$status->shouldReceive('set')->once()->with(200);

		$cache->shouldReceive('disable')->once();

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
		$cache    = Mockery::mock('\kanso\framework\http\response\Cache');

		$feed = new Feed($request, $response, 'atom');

		$response->shouldReceive('format')->once()->andReturn($format);

		$response->shouldReceive('body')->once()->andReturn($body);

		$response->shouldReceive('status')->once()->andReturn($status);

		$response->shouldReceive('cache')->once()->andReturn($cache);

		$response->shouldReceive('view')->once()->andReturn($view)->times(3);

		$format->shouldReceive('set')->once()->with('application/atom+xml, application/xml');
		
		$body->shouldReceive('set')->once();

		$status->shouldReceive('set')->once()->with(200);

		$cache->shouldReceive('disable')->once();

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
		$cache    = Mockery::mock('\kanso\framework\http\response\Cache');

		$feed = new Feed($request, $response, 'rdf');

		$response->shouldReceive('format')->once()->andReturn($format);

		$response->shouldReceive('body')->once()->andReturn($body);

		$response->shouldReceive('status')->once()->andReturn($status);

		$response->shouldReceive('cache')->once()->andReturn($cache);

		$response->shouldReceive('view')->once()->andReturn($view)->times(3);

		$format->shouldReceive('set')->once()->with('application/rdf+xml, application/xml');
		
		$body->shouldReceive('set')->once();

		$status->shouldReceive('set')->once()->with(200);

		$cache->shouldReceive('disable')->once();

		$view->shouldReceive('display')->times(3);

		$feed->render();
	}
}
