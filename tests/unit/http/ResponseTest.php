<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\response;

use Mockery;
use tests\TestCase;
use kanso\framework\http\response\Protocol;
use kanso\framework\http\response\Format;
use kanso\framework\http\response\Body;
use kanso\framework\http\response\Status;
use kanso\framework\http\response\Headers as ResponseHeaders;
use kanso\framework\http\response\CDN;
use kanso\framework\http\response\Cache;
use kanso\framework\http\response\Response;
use kanso\framework\http\cookie\Cookie;
use kanso\framework\http\cookie\storage\NativeCookieStorage;
use kanso\framework\http\session\Session;
use kanso\framework\http\session\storage\NativeSessionStorage;
use kanso\framework\http\session\storage\FileSessionStorage;
use kanso\framework\http\session\Flash;
use kanso\framework\http\session\Token;
use kanso\framework\security\Key;
use kanso\framework\security\Crypto;
use kanso\framework\security\password\encrypters\NativePHP;
use kanso\framework\security\crypto\Signer;
use kanso\framework\security\crypto\encrypters\OpenSSL;
use kanso\framework\mvc\view\View;

/**
 * @group unit
 */
class ResponseTest extends TestCase
{
	/**
	 *
	 */
	public function getHeaders()
	{
		return
		[
			'X-Foo-Bar' => 'foo bar',
			'X-Baz-Bax' => 'baz bax',
		];
	}

	/**
	 *
	 */
	public function getServerData()
	{
		return
		[
			'REQUEST_METHOD'  => 'GET',
			'SCRIPT_NAME'     => 'index.php',
			'SERVER_NAME'     => 'example.com',
			'SERVER_PORT'     => '8888',
			'HTTP_PROTOCOL'   => 'http',
			'DOCUMENT_ROOT'   => '/usr/name/httpdocs',
			'HTTP_HOST'       => 'http://example.com',
			'DOMAIN_NAME'     => 'example.com',
			'REQUEST_URI'     => '/foobar',
			'REQUEST_URL'     => 'http://example.com/foobar',
			'QUERY_STRING'    => '?foo=bar',
			'REMOTE_ADDR'     => '192.168.1.1',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17',
		];
	}

	/**
	 *
	 */
	public function cookie()
	{
		$config = 
		[
			'name'         => 'kanso_cookie',
			'expire'       => strtotime('+1 month'),
			'path'         => '/',
			'domain'       => '',
			'secure'       => false,
			'httponly'     => true,
			'storage'      =>
			[
				'type' => 'native',
			],
		];

		return new Cookie(new NativeCookieStorage($this->crypto(), $config), $config['name'], $config['expire']);
	}

	/**
	 *
	 */
	public function session()
	{
		$config = 
		[
			'cookie_name'  => 'kanso_session',
			'expire'       => strtotime('+1 month'),
			'path'         => '/',
			'domain'       => '',
			'secure'       => false,
			'httponly'     => true,
			'storage' =>
			[
				'type' => 'native',
			],
		];

		return new Session(New Token, new Flash, new NativeSessionStorage($this->crypto(), $config), $config);
	}

	/**
	 *
	 */
	public function response()
	{

		return new Response(new Protocol('https'), new Format, new Body, new Status, new ResponseHeaders, $this->cookie(), $this->session(), $this->cache(), $this->cdn(), new view);

	}

	public function cache()
	{
		return new Cache(Mockery::mock('\kanso\framework\cache\Cache'), 'foobar_key', false);
	}

	/**
	 *
	 */
	public function crypto()
	{
		return new Crypto(new Signer('foobar_hash'), new OpenSSL(Key::decode('foobar_key'), 'AES-256-ECB'), new NativePHP(PASSWORD_DEFAULT));
	}

	/**
	 *
	 */
	public function cdn()
	{
		return new CDN('https://foo.com', 'https://cdn.foo.com', false);
	}

	/**
	 *
	 */
	public function testBody()
	{
		$response = $this->response();
		$response->body()->set('Hello, world!');
		$this->assertEquals('Hello, world!', $response->body()->get());
	}

	/**
	 *
	 */
	public function testClearBody()
	{
		$response = $this->response();
		$response->body()->set('Hello, world!');
		$response->body()->clear('');
		$this->assertEquals('', $response->body()->get());
	}

	/**
	 *
	 */
	public function testType()
	{
		$response = $this->response();
		$this->assertEquals('text/html', $response->format()->get());
		$this->assertEquals('utf-8', $response->format()->getEncoding());

		$response->format()->set('application/json');
		$this->assertEquals('application/json', $response->format()->get());

		$response->format()->set('gif');
		$this->assertEquals('image/gif', $response->format()->get());

		$response->format()->set('json');
		$this->assertEquals('application/json', $response->format()->get());
	}

	/**
	 *
	 */
	public function testEncoding()
	{
		$response = $this->response();
		$this->assertEquals('text/html', $response->format()->get());
		$this->assertEquals('utf-8', $response->format()->getEncoding());
		
		$response->format()->setEncoding('iso-8859-1');
		$this->assertEquals('iso-8859-1', $response->format()->getEncoding());
	}

	/**
	 *
	 */
	public function testHeader()
	{
		$response = $this->response();
		
		foreach($this->getHeaders() as $header => $value)
		{
			$response->headers()->set($header, $value);
		}

		$headers = $response->headers()->get();
		$this->assertTrue(is_array($headers));
		$this->assertCount(2, $headers);
		$this->assertArrayHasKey('X-Foo-Bar', $headers);
		$this->assertArrayHasKey('X-Baz-Bax', $headers);
		$this->assertEquals('foo bar', $headers['X-Foo-Bar']);
		$this->assertEquals('baz bax', $headers['X-Baz-Bax']);
	}

	/**
	 *
	 */
	public function testHeaderSetMultiple()
	{
		$response = $this->response();
		
		$response->headers()->setMultiple($this->getHeaders());

		$headers = $response->headers()->get();
		$this->assertTrue(is_array($headers));
		$this->assertCount(2, $headers);
		$this->assertArrayHasKey('X-Foo-Bar', $headers);
		$this->assertArrayHasKey('X-Baz-Bax', $headers);
		$this->assertEquals('foo bar', $headers['X-Foo-Bar']);
		$this->assertEquals('baz bax', $headers['X-Baz-Bax']);
	}

	/**
	 *
	 */
	public function testHasHeader()
	{
		$response = $this->response();
		$response->headers()->set('foo', 'foo1');
		$this->assertTrue($response->headers()->has('foo'));
		$this->assertFalse($response->headers()->has('bar'));
	}

	/**
	 *
	 */
	public function testRemoveHeader()
	{
		$response = $this->response();
		foreach($this->getHeaders() as $header => $value)
		{
			$response->headers()->set($header, $value);
		}
		$response->headers()->remove('X-Foo-Bar');
		$headers = $response->headers()->get();
		$this->assertCount(1, $headers);
		$this->assertArrayHasKey('X-Baz-Bax', $headers);
	}

	/**
	 *
	 */
	public function testClearHeaders()
	{
		$response = $this->response();
		foreach($this->getHeaders() as $header => $value)
		{
			$response->headers()->set($header, $value);
		}
		$response->headers()->clear();
		$headers = $response->headers()->get();
		$this->assertCount(0, $headers);
	}

	/**
	 *
	 */
	public function testSetCookies()
	{
		$response = $this->response();
		$response->cookie()->set('foo', 'foo cookie');
		$response->cookie()->set('faa', 'faa cookie');
		$response->cookie()->set('bar', 'bar cookie');
		$response->cookie()->set('baz', 'baz cookie');
		$response->cookie()->set('bax', 'bax cookie');
		$response->cookie()->set('bam', 'bam cookie');
		
		$cookies = $response->cookie()->get();
		$this->assertTrue(is_array($cookies));
		$this->assertCount(7, $cookies);
		$this->assertArrayHasKey('foo', $cookies);
		$this->assertArrayHasKey('faa', $cookies);
		$this->assertArrayHasKey('bar', $cookies);
		$this->assertArrayHasKey('baz', $cookies);
		$this->assertArrayHasKey('bax', $cookies);
		$this->assertArrayHasKey('bam', $cookies);
		$this->assertArrayHasKey('last_active', $cookies);
	}

	/**
	 *
	 */
	public function testFinalize()
	{
		$response = $this->response();
		$response->finalize();
		$this->assertEquals(200, $response->headers()->get('Status'));
		$this->assertEquals('200 OK', $response->headers()->get('HTTP'));
		$this->assertEquals(0, $response->headers()->get('Content-length'));
		$this->assertEquals('text/html;utf-8', $response->headers()->get('Content-Type'));
		$this->assertEquals('', $response->body()->get());

		
		$response->headers()->set('Keep-Alive', 'timeout=5, max=100');
		$response->cookie()->set('foo', 'bar');
		$response->session()->set('foo', 'bar');
		$response->session()->token()->set('foo', 'bar');
		$response->session()->flash()->put('foo', 'bar');
		$response->body()->set(json_encode(['foo' => 'bar']));
		$response->format()->set('application/json');
		$response->format()->setEncoding('iso-8859-1');
		$response->protocol()->set('https');
		$response->finalize();

		$this->assertEquals('timeout=5, max=100', $response->headers()->get('Keep-Alive'));
		$this->assertEquals(200, $response->headers()->get('Status'));
		$this->assertEquals('200 OK', $response->headers()->get('HTTP'));
		$this->assertEquals(13, $response->headers()->get('Content-length'));
		$this->assertEquals('application/json;iso-8859-1', $response->headers()->get('Content-Type'));
		$this->assertEquals('{"foo":"bar"}', $response->body()->get());
		$this->assertEquals('bar', $response->cookie()->get('foo'));
		$this->assertEquals('bar', $response->session()->get('foo'));
		$this->assertEquals('bar', $response->session()->flash()->get('foo'));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSend()
	{
		ob_start();

		$response = $this->response();
		$response->send();
		$this->assertEquals(200, $response->headers()->get('Status'));
		$this->assertEquals('200 OK', $response->headers()->get('HTTP'));
		$this->assertEquals(0, $response->headers()->get('Content-length'));
		$this->assertEquals('text/html;utf-8', $response->headers()->get('Content-Type'));
		$this->assertEquals('', $response->body()->get());

		$response = $this->response();
		$response->headers()->set('Keep-Alive', 'timeout=5, max=100');
		$response->cookie()->set('foo', 'bar');
		$response->session()->set('foo', 'bar');
		$response->session()->token()->set('foo', 'bar');
		$response->session()->flash()->put('foo', 'bar');
		$response->body()->set(json_encode(['foo' => 'bar']));
		$response->format()->set('application/json');
		$response->format()->setEncoding('iso-8859-1');
		$response->protocol()->set('https');
		$response->send();

		$this->assertEquals('timeout=5, max=100', $response->headers()->get('Keep-Alive'));
		$this->assertEquals(200, $response->headers()->get('Status'));
		$this->assertEquals('200 OK', $response->headers()->get('HTTP'));
		$this->assertEquals(13, $response->headers()->get('Content-length'));
		$this->assertEquals('application/json;iso-8859-1', $response->headers()->get('Content-Type'));
		$this->assertEquals('{"foo":"bar"}', $response->body()->get());
		$this->assertEquals('bar', $response->cookie()->get('foo'));
		$this->assertEquals('bar', $response->session()->get('foo'));
		$this->assertEquals('bar', $response->session()->flash()->get('foo'));

		ob_end_clean();
	}

	/**
	 * @runInSeparateProcess
	 * @expectedException \kanso\framework\http\response\exceptions\Stop
	 */
	public function testRedirect()
	{
		$response = $this->response();
		$response->body()->set('Hello, world!');
		$response->redirect('foo/bar/');
	}

	/**
	 * @runInSeparateProcess
	 * @expectedException \kanso\framework\http\response\exceptions\NotFoundException
	 */
	public function testNotFound()
	{
		$response = $this->response();
		$response->body()->set('Hello, world!');
		$response->notFound();
	}

	/**
	 * @runInSeparateProcess
	 * @expectedException \kanso\framework\http\response\exceptions\ForbiddenException
	 */
	public function testForbidden()
	{
		$response = $this->response();
		$response->forbidden();
	}

	/**
	 * @runInSeparateProcess
	 * @expectedException \kanso\framework\http\response\exceptions\InvalidTokenException
	 */
	public function testInvalidToken()
	{
		$response = $this->response();
		$response->invalidToken();
	}

	/**
	 * @runInSeparateProcess
	 * @expectedException \kanso\framework\http\response\exceptions\MethodNotAllowedException
	 */
	public function testMethodNotAllowed()
	{
		$response = $this->response();
		$response->methodNotAllowed();
	}
}