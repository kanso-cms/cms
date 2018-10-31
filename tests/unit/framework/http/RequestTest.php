<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\response;

use kanso\framework\http\request\Environment;
use kanso\framework\http\request\Files;
use kanso\framework\http\request\Headers;
use kanso\framework\http\request\Request;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
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
			'SERVER_NAME'     => 'localhost:8888',
			'SERVER_PORT'     => '8888',
			'HTTP_PROTOCOL'   => 'http',
			'DOCUMENT_ROOT'   => '/usr/name/httpdocs',
			'HTTP_HOST'       => 'http://localhost:8888',
			'DOMAIN_NAME'     => 'localhost:8888',
			'REQUEST_URI'     => '/foobar',
			'QUERY_STRING'    => '?foo=bar',
			'REMOTE_ADDR'     => '192.168.1.1',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17',
			'HTTP_CONNECTION' => 'keep-alive',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,foo/bar; q=0.1,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'HTTP_ACCEPT_CHARSET' => 'UTF-8,FOO-1; q=0.1,UTF-16;q=0.9',
			'HTTP_ACCEPT_ENCODING' => 'gzip,foobar;q=0.1,deflate,sdch',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,da;q=0.6,fr;q=0.4,foo; q=0.1,nb;q=0.2,sv;q=0.2',
			'PATH' => '/usr/local/bin:/usr/bin:/bin',
		];
	}

	/**
	 *
	 */
	public function testGetMethod()
	{
		$server  = $this->getServerData();

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$server['REQUEST_URI']  = '/foobar.jpg';

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

		$this->assertTrue($request->isFileGet());
	}

	/**
	 *
	 */
	public function testIsAjax()
	{
		$server  = $this->getServerData();

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

		$this->assertEquals('http', $request->fetch('scheme'));

		$this->assertEquals('localhost', $request->fetch('host'));

		$this->assertEquals('/foobar.html', $request->fetch('path'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

		$this->assertEquals('bar', $request->fetch('foo'));

		$this->assertEquals('foo', $request->fetch('bar'));

		$this->assertEquals('foo', $request->fetch()['bar']);

		$this->assertEquals('bar', $request->fetch()['foo']);
	}

	/**
	 *
	 */
	public function testFile()
	{
		$server = $this->getServerData();
		$server['REQUEST_METHOD'] = 'POST';
		$files =
		[
			'upload' =>
			[
				'name'     => 'foo',
				'tmp_name' => '/tmp/qwerty',
				'type'     => 'foo/bar',
				'size'     => 123,
				'error'    => 0,
			],
		];

		$request = new Request(new Environment($server), new Headers($server), new Files($files));

		$file   = $request->files()->get('upload');

		$this->assertTrue(is_array($file));
		$this->assertEquals('foo', $file['name']);
		$this->assertEquals('/tmp/qwerty', $file['tmp_name']);
		$this->assertEquals(123, $file['size']);
		$this->assertEquals(0, $file['error']);
	}

	/**
	 *
	 */
	public function testFileMultiUpload()
	{
		$server = $this->getServerData();
		$server['REQUEST_METHOD'] = 'POST';
		$files =
		[
			'upload' =>
			[
				'name'     => ['foo', 'bar'],
				'tmp_name' => ['/tmp/qwerty', '/tmp/azerty'],
				'type'     => ['foo/bar', 'foo/bar'],
				'size'     => [123, 456],
				'error'    => [0, 0],
			],
		];

		$request = new Request(new Environment($server), new Headers($server), new Files($files));

		// file 1
		$file = $request->files()->get('upload.0');
		$this->assertTrue(is_array($file));
		$this->assertEquals('foo', $file['name']);
		$this->assertEquals('/tmp/qwerty', $file['tmp_name']);
		$this->assertEquals(123, $file['size']);
		$this->assertEquals(0, $file['error']);

		// File 2
		$file = $request->files()->get('upload.1');
		$this->assertTrue(is_array($file));
		$this->assertEquals('bar', $file['name']);
		$this->assertEquals('/tmp/azerty', $file['tmp_name']);
		$this->assertEquals(456, $file['size']);
		$this->assertEquals(0, $file['error']);
	}

	/**
	 *
	 */
	public function testFileNone()
	{
		$server = $this->getServerData();

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(new Environment($server), new Headers($server), new Files);

		$this->assertEquals([], $request->files()->get());

		$this->assertNull($request->files()->get('foo'));
	}

	/**
	 *
	 */
	public function testMimeType()
	{
		$server  = $this->getServerData();

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

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

		$request = new Request(new Environment($server), new Headers($server), Mockery::mock('\kanso\framework\http\request\Files'));

		$this->assertFalse($request->isBot());

		$server['HTTP_USER_AGENT'] = 'Googlebot-Image/1.0';

		$request->environment()->reload($server);

		$request->headers()->reload($server);

		$this->assertTrue($request->isBot());
	}
}
