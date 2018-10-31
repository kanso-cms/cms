<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\sitemap;

use kanso\cms\sitemap\SiteMap;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class SitemapTest extends TestCase
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

		$sitemap = new SiteMap($request, $response, true, true, true, true, ['foo']);

		$response->shouldReceive('format')->once()->andReturn($format);

		$response->shouldReceive('body')->once()->andReturn($body);

		$response->shouldReceive('status')->once()->andReturn($status);

		$response->shouldReceive('cache')->once()->andReturn($cache);

		$response->shouldReceive('view')->once()->andReturn($view)->times(9);

		$format->shouldReceive('set')->once()->with('xml');

		$body->shouldReceive('set')->once();

		$status->shouldReceive('set')->once()->with(200);

		$cache->shouldReceive('disable')->once();

		$view->shouldReceive('display')->times(9);

		$sitemap->display();
	}
}
