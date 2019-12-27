<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\response;

use kanso\framework\http\response\Response;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class ResponseTest extends TestCase
{
	/**
	 *
	 */
	private function mockResponse()
	{
		$protocol = $this->mock('\kanso\framework\http\response\Protocol');
		$format   = $this->mock('\kanso\framework\http\response\Format');
		$body     = $this->mock('\kanso\framework\http\response\Body');
		$status   = $this->mock('\kanso\framework\http\response\Status');
		$headers  = $this->mock('\kanso\framework\http\response\Headers');
		$cookie   = $this->mock('\kanso\framework\http\cookie\Cookie');
		$session  = $this->mock('\kanso\framework\http\session\Session');
		$cdn      = $this->mock('\kanso\framework\http\response\CDN');
		$view     = $this->mock('\kanso\framework\mvc\view\View');
		$request  = $this->mock('\kanso\framework\http\request\Request');
		$rHeaders = $this->mock('\kanso\framework\http\request\Headers');

		$format->shouldReceive('set')->withArgs(['text/html']);
		$format->shouldReceive('setEncoding')->withArgs(['utf-8']);

		$response = new Response($protocol, $format, $body, $status, $headers, $cookie, $session, $cdn, $view, $request, false);

		return
		[
			'protocol' => $protocol,
			'format'   => $format,
			'body'     => $body,
			'status'   => $status,
			'headers'  => $headers,
			'cookie'   => $cookie,
			'session'  => $session,
			'cdn'      => $cdn,
			'view'     => $view,
			'request'  => $request,
			'response' => $response,
			'rHeaders' => $rHeaders,
		];
	}

	/**
	 *
	 */
	public function testSend(): void
	{
		$responseArr = $this->mockResponse();

		extract($responseArr);

		$request->shouldReceive('getMethod')->andReturn('GET');

		$format->shouldReceive('set')->withArgs(['text/html']);
		$format->shouldReceive('setEncoding')->withArgs(['utf-8']);
		$format->shouldReceive('get')->andReturn('text/html');
		$format->shouldReceive('getEncoding')->andReturn('utf-8');

		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('message')->andReturn('OK');
		$status->shouldReceive('isRedirect')->andReturn(false);
		$status->shouldReceive('isEmpty')->andReturn(false);
		$status->shouldReceive('isNotModified')->andReturn(false);

		$headers->shouldReceive('set')->withArgs(['Status', 200]);
		$headers->shouldReceive('set')->withArgs(['Content-length', 0]);
		$headers->shouldReceive('set')->withArgs(['HTTP', '200 OK']);
		$headers->shouldReceive('set')->withArgs(['Content-Type', 'text/html;utf-8']);
		$headers->shouldReceive('set')->withArgs(['Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0']);
		$headers->shouldReceive('send');

		$body->shouldReceive('length')->andReturn(0);
		$body->shouldReceive('set')->withArgs(['']);
		$body->shouldReceive('get')->andReturn('');
		$body->shouldReceive('get')->andReturn('');

		$session->shouldReceive('save');
		$cookie->shouldReceive('send');
		$cdn->shouldReceive('filter')->withArgs([''])->andReturn('');

		$response->send();
	}

	/**
	 *
	 */
	public function testSendCacheEnabled(): void
	{
		ob_start();

		$hash = '"' . hash('sha256', 'foobar') . '"';

		$responseArr = $this->mockResponse();

		extract($responseArr);

		$request->shouldReceive('getMethod')->andReturn('GET');

		$format->shouldReceive('set')->withArgs(['text/html']);
		$format->shouldReceive('setEncoding')->withArgs(['utf-8']);
		$format->shouldReceive('get')->andReturn('text/html');
		$format->shouldReceive('getEncoding')->andReturn('utf-8');

		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('get')->andReturn(200);
		$status->shouldReceive('message')->andReturn('OK');
		$status->shouldReceive('isRedirect')->andReturn(false);
		$status->shouldReceive('isEmpty')->andReturn(false);
		$status->shouldReceive('isNotModified')->andReturn(false);

		$headers->shouldReceive('get')->withArgs(['Cache-Control'])->andReturn(false);
		$headers->shouldReceive('set')->withArgs(['Status', 200]);
		$headers->shouldReceive('set')->withArgs(['Content-length', 6]);
		$headers->shouldReceive('set')->withArgs(['HTTP', '200 OK']);
		$headers->shouldReceive('set')->withArgs(['Content-Type', 'text/html;utf-8']);
		$headers->shouldReceive('set')->withArgs(['Cache-Control', 'private, max-age=3600']);
		$headers->shouldReceive('set')->withArgs(['ETag', $hash]);
		$headers->shouldReceive('send');

		$request->shouldReceive('headers')->andReturn($rHeaders);
		$rHeaders->HTTP_IF_NONE_MATCH = '3434r23rrfjf';

		$body->shouldReceive('length')->andReturn(6);
		$body->shouldReceive('set')->withArgs(['foobar']);
		$body->shouldReceive('get')->andReturn('foobar');

		$session->shouldReceive('save');
		$cookie->shouldReceive('send');
		$cdn->shouldReceive('filter')->withArgs(['foobar'])->andReturn('foobar');

		$response->enableCaching();
		$response->send();

		ob_end_clean();
	}
}
