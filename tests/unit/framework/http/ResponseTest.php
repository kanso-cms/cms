<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\http\response;

use kanso\framework\http\response\Response;
use Mockery;
use tests\TestCase;

/**
 * @group unit
 */
class ResponseTest extends TestCase
{
	/**
	 *
	 */
	private function mockResponse()
	{
		$protocol = Mockery::mock('\kanso\framework\http\response\Protocol');
		$format   = Mockery::mock('\kanso\framework\http\response\Format');
		$body     = Mockery::mock('\kanso\framework\http\response\Body');
		$status   = Mockery::mock('\kanso\framework\http\response\Status');
		$headers  = Mockery::mock('\kanso\framework\http\response\Headers');
		$cookie   = Mockery::mock('\kanso\framework\http\cookie\Cookie');
		$session  = Mockery::mock('\kanso\framework\http\session\Session');
		$cache    = Mockery::mock('\kanso\framework\http\response\Cache');
		$cdn      = Mockery::mock('\kanso\framework\http\response\CDN');
		$view     = Mockery::mock('\kanso\framework\mvc\view\View');

		$format->shouldReceive('set')->withArgs(['text/html']);
		$format->shouldReceive('setEncoding')->withArgs(['utf-8']);

		$response = new Response($protocol, $format, $body, $status, $headers, $cookie, $session, $cache, $cdn, $view);

		return
		[
			'protocol' => $protocol,
			'format'   => $format,
			'body'     => $body,
			'status'   => $status,
			'headers'  => $headers,
			'cookie'   => $cookie,
			'session'  => $session,
			'cache'    => $cache,
			'cdn'      => $cdn,
			'view'     => $view,
			'response' => $response,
		];
	}

	/**
	 *
	 */
	public function testSend()
	{
		$responseArr = $this->mockResponse();

		extract($responseArr);

		$format->shouldReceive('set')->withArgs(['text/html']);
		$format->shouldReceive('setEncoding')->withArgs(['utf-8']);

		$headers->shouldReceive('set')->withArgs(['Status', 200]);
		$status->shouldReceive('get')->andReturn(200);

		$headers->shouldReceive('set')->withArgs(['HTTP', '200 OK']);
		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('message')->andReturn('OK');

		$headers->shouldReceive('set')->withArgs(['Content-length', 0]);
		$body->shouldReceive('length')->andReturn(0);

		$headers->shouldReceive('set')->withArgs(['Content-Type', 'text/html;utf-8']);
		$format->shouldReceive('get')->andReturn('text/html');
		$format->shouldReceive('getEncoding')->andReturn('utf-8');

		$status->shouldReceive('isRedirect')->andReturn(false);
		$status->shouldReceive('isEmpty')->andReturn(false);

		$cache->shouldReceive('enabled')->andReturn(false);

		$cdn->shouldReceive('filter')->withArgs([''])->andReturn('');
		$body->shouldReceive('get')->andReturn('');

		$body->shouldReceive('set')->withArgs(['']);

		$session->shouldReceive('save');

		$headers->shouldReceive('send');

		$cookie->shouldReceive('send');

		$body->shouldReceive('get')->andReturn('');

		$response->send();
	}

}
