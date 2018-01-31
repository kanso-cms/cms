<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\response;

use tests\TestCase;
use kanso\framework\http\request\Environment;
use kanso\framework\http\request\Headers;
use kanso\framework\http\request\Request;

/**
 * @group unit
 */
class RequestTest extends TestCase
{
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
	public function testGetMethod()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertEquals('GET', $request->getMethod());

		$server['REQUEST_METHOD'] = 'POST';
		$request->environment()->reload($server);
		$this->assertEquals('POST', $request->getMethod());
	}

	/**
	 *
	 */
	public function testIsSecure()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isSecure());

		$server['HTTP_PROTOCOL'] = 'https';
		$server['SERVER_PORT']   = '443';
		$server['HTTPS']         = 'on';
		$request->environment()->reload($server);
		$this->assertTrue($request->isSecure());
	}

	/**
	 *
	 */
	public function testIsGet()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertTrue($request->isGet());

		$server['REQUEST_METHOD'] = 'POST';
		$request->environment()->reload($server);
		$this->assertFalse($request->isGet());
	}

	/**
	 *
	 */
	public function testIsPost()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isPost());

		$server['REQUEST_METHOD'] = 'POST';
		$request->environment()->reload($server);
		$this->assertTrue($request->isPost());
	}

	/**
	 *
	 */
	public function testIsPut()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isPut());

		$server['REQUEST_METHOD'] = 'PUT';
		$request->environment()->reload($server);
		$this->assertTrue($request->isPut());
	}

	/**
	 *
	 */
	public function testIsPatch()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isPatch());

		$server['REQUEST_METHOD'] = 'PATCH';
		$request->environment()->reload($server);
		$this->assertTrue($request->isPatch());
	}

	/**
	 *
	 */
	public function testIsDelete()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isDelete());

		$server['REQUEST_METHOD'] = 'DELETE';
		$request->environment()->reload($server);
		$this->assertTrue($request->isDelete());
	}

	/**
	 *
	 */
	public function testIsHead()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isHead());

		$server['REQUEST_METHOD'] = 'HEAD';
		$request->environment()->reload($server);
		$this->assertTrue($request->isHead());
	}

	/**
	 *
	 */
	public function testIsOptions()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isOptions());

		$server['REQUEST_METHOD'] = 'OPTIONS';
		$request->environment()->reload($server);
		$this->assertTrue($request->isOptions());
	}

	/**
	 *
	 */
	public function testIsFileGet()
	{
		$server  = $this->getServerData();

		$server['REQUEST_URI']  = 'foobar.jpg';
		$server['QUERY_STRING'] = '';

		$request = new Request(new Environment($server), new Headers($server));
		$this->assertTrue($request->isFileGet());
	}

	/**
	 *
	 */
	public function testIsAjax()
	{
		$server  = $this->getServerData();
		$server['REQUEST_METHOD'] = 'POST';
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isAjax());

		$server['HTTP_REQUESTED_WITH'] = 'XMLHttpRequest';
		$request->environment()->reload($server);
		$request->headers()->reload($server);
		$this->assertTrue($request->isAjax());
	}

	/**
	 *
	 */
	public function testFetch()
	{
		$server  = $this->getServerData();
		$server['REQUEST_METHOD'] = 'POST';
		$server['REQUEST_URI']    = '/foobar.html?foo=bar&bar=foo';
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertEquals('http', $request->fetch('scheme'));
		$this->assertEquals('http', $request->fetch('host'));
		$this->assertEquals('//example.com/foobar.html', $request->fetch('path'));
		$this->assertEquals('foo=bar&bar=foo', $request->fetch('query'));
		$this->assertEquals(0, $request->fetch('page'));
	}

	/**
	 *
	 */
	public function testQueries()
	{
		$server  = $this->getServerData();
		$server['REQUEST_METHOD'] = 'POST';
		$server['REQUEST_URI']    = '/foobar.html?foo=bar&bar=foo';
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertEquals('bar', $request->fetch('foo'));
		$this->assertEquals('foo', $request->fetch('bar'));
		$this->assertEquals('foo', $request->fetch()['bar']);
		$this->assertEquals('bar', $request->fetch()['foo']);
	}

	/**
	 *
	 */
	public function testMimeType()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->mimeType());

		$server['REQUEST_URI'] = '/foobar.png';
		$request->environment()->reload($server);
		$this->assertEquals('image/png', $request->mimeType());
	}

	/**
	 *
	 */
	public function testIsBot()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertFalse($request->isBot());

		$server['HTTP_USER_AGENT'] = 'Googlebot-Image/1.0';
		$request->environment()->reload($server);
		$request->headers()->reload($server);
		$this->assertTrue($request->isBot());
	}
	
	/**
	 *
	 */
	public function testScriptName()
	{
		$server  = $this->getServerData();
		$request = new Request(new Environment($server), new Headers($server));
		$this->assertEquals('index.php', $request->environment()->SCRIPT_NAME);

		$server['SCRIPT_NAME'] = '/var/www/app.php';
		$request->environment()->reload($server);
		$this->assertEquals('app.php', $request->environment()->SCRIPT_NAME);
	}
}