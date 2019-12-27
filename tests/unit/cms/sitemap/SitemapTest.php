<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\sitemap;

use kanso\cms\sitemap\SiteMap;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group cms
 */
class SitemapTest extends TestCase
{
	/**
	 *
	 */
	public function testRss(): void
	{
		$request  = $this->mock('\kanso\framework\http\request\Request');
		$response = $this->mock('\kanso\framework\http\response\Response');
		$format   = $this->mock('\kanso\framework\http\response\Format');
		$body     = $this->mock('\kanso\framework\http\response\Body');
		$view     = $this->mock('\kanso\framework\mvc\view\View');
		$status   = $this->mock('\kanso\framework\http\response\Status');

		$sitemap = new SiteMap($request, $response, true, true, true, true, ['foo']);

		$response->shouldReceive('format')->once()->andReturn($format);

		$response->shouldReceive('body')->once()->andReturn($body);

		$response->shouldReceive('status')->once()->andReturn($status);

		$response->shouldReceive('disableCaching');

		$response->shouldReceive('view')->once()->andReturn($view)->times(9);

		$format->shouldReceive('set')->once()->with('xml');

		$body->shouldReceive('set')->once();

		$status->shouldReceive('set')->once()->with(200);

		$view->shouldReceive('display')->times(9);

		$sitemap->display();
	}
}
