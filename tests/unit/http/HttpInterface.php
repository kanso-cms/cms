<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http;

use kanso\framework\http\request\Environment;
use kanso\framework\http\request\Headers as RequestHeaders;
use kanso\framework\http\request\Request;
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

/**
 * @group unit
 */
class HttpInterface
{
	/**
	 * 
     */
	public function response(): Response
	{
		return new Response($this->protocol(), $this->format(), $this->body(), $this->status(), $this->responseHeaders(), $this->cookie(), $this->session(), $this->cache(), $this->cdn(), $this->view());
	}

	/**
	 * 
     */
	public function request()
	{
		return new Request(new Environment($this->getServerData()), new RequestHeaders);
	}

	/**
	 * 
     */
	public function body(): Body
	{
		return new Body;
	}

	/**
	 * 
     */
	public function status(): Status
	{
		return new Status;
	}

	/**
	 * 
     */
	public function responseHeaders(): ResponseHeaders
	{
		return new ResponseHeaders;
	}

	/**
	 * 
     */
	private function protocol(): Protocol
	{
		return new Protocol($this->request()->environment()->HTTP_PROTOCOL);
	}

	/**
	 * 
     */
	public function format(): Format
	{
		return new Format;
	}

	/**
	 * 
     */
	private function cdn(): CDN
	{
		return new CDN($this->request()->environment()->HTTP_HOST, '', false);
	}

	/**
	 * 
     */
	public function cookie()
	{
		$cookieConfiguration = 
		[
			'name'     => 'kanso_cookie',
			'expire'   => '+1 month',
			'path'     => '/',
			'domain'   => '',
			'secure'   => false,
			'httponly' => true,
			'storage'  =>
			[
				'type' => 'native',
			]
		];

		if (!is_numeric($cookieConfiguration['expire']))
		{
			$cookieConfiguration['expire'] = strtotime($cookieConfiguration['expire']);
		}

		$store = $this->loadCookieStore($cookieConfiguration);

		return new Cookie($store, $cookieConfiguration['name'], $cookieConfiguration['expire']);
	}

	/**
	 * 
     */
	private function cache(): Cache
	{
		return new Cache($this->Cache(), 'foobartest', false);
	}

	private function loadCookieStore(array $cookieConfiguration)
	{
		$storeConfig = $cookieConfiguration['storage'];

		if ($storeConfig['type'] === 'native')
		{
			return $this->nativeCookieStore($storeConfig, $cookieConfiguration);
		}
	}

	private function nativeCookieStore(array $storeConfig, array $cookieConfiguration): NativeCookieStorage
	{
		return new NativeCookieStorage($this->container->Crypto, $cookieConfiguration);
	}


	public function session()
	{
		$sessionConfiguration =
		[
			'cookie_name'  => 'kanso_session',
			'expire'       => '+1 month',
			'path'         => '/',
			'domain'       => '',
			'secure'       => false,
			'httponly'     => true,
			'storage' =>
			[
				'type' => 'file',
				'path' => APP_DIR.'/storage/session',
			],

		];

		if (!is_numeric($sessionConfiguration['expire']))
		{
			$sessionConfiguration['expire'] = strtotime($sessionConfiguration['expire']);
		}

		$store = $this->loadSessionStore($sessionConfiguration);

		return new Session(New Token, new Flash, $store, $sessionConfiguration);
	}

	private function loadSessionStore(array $cookieConfiguration)
	{
		$storeConfig = $cookieConfiguration['storage'];

		if ($storeConfig['type'] === 'native')
		{
			return $this->nativeSessionStore($storeConfig, $cookieConfiguration);
		}
		else if ($storeConfig['type'] === 'file')
		{
			return $this->fileSessionStore($storeConfig, $cookieConfiguration);
		}
	}

	private function nativeSessionStore(array $storeConfig, array $cookieConfiguration): NativeSessionStorage
	{
		return new NativeSessionStorage($this->container->Crypto, $cookieConfiguration);
	}

	private function fileSessionStore(array $storeConfig, array $cookieConfiguration): FileSessionStorage
	{
		return new FileSessionStorage($this->container->Crypto, $cookieConfiguration, $cookieConfiguration['storage']['path']);
	}

	private function getServerData()
	{
		return
		[
			'HTTP_HOST' => 'example.local',
			'HTTP_CONNECTION' => 'keep-alive',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,foo/bar; q=0.1,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36',
			'HTTP_DNT' => '1',
			'HTTP_ACCEPT_CHARSET' => 'UTF-8,FOO-1; q=0.1,UTF-16;q=0.9',
			'HTTP_ACCEPT_ENCODING' => 'gzip,foobar;q=0.1,deflate,sdch',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,da;q=0.6,fr;q=0.4,foo; q=0.1,nb;q=0.2,sv;q=0.2',
			'PATH' => '/usr/local/bin:/usr/bin:/bin',
			'SERVER_SIGNATURE' => '<address>Apache/2.4.6 (Ubuntu) Server at example.local Port 80</address>',
			'SERVER_SOFTWARE' => 'Apache/2.4.6 (Ubuntu)',
			'SERVER_NAME' => 'example.local',
			'SERVER_ADDR' => '10.17.2.9',
			'SERVER_PORT' => '80',
			'REMOTE_ADDR' => '10.17.12.209',
			'DOCUMENT_ROOT' => '/var/www',
			'REQUEST_SCHEME' => 'http',
			'CONTEXT_PREFIX' => '',
			'CONTEXT_DOCUMENT_ROOT' => '/var/www',
			'SERVER_ADMIN' => 'webmaster@localhost',
			'REMOTE_PORT' => '53058',
			'GATEWAY_INTERFACE' => 'CGI/1.1',
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'REQUEST_METHOD' => 'GET',
			'QUERY_STRING' => '',
			'REQUEST_TIME_FLOAT' => 1398338683.59,
			'REQUEST_TIME' => 1398338683,
			'REQUEST_URI' => '/index.php/test/',
			'SCRIPT_NAME' => '/index.php',
			'PHP_SELF' => '/index.php/test/',
			'SCRIPT_FILENAME' => '/var/www/index.php',
			'PATH_INFO' => '/test/',
		];
	}

}