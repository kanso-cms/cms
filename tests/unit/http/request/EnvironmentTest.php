<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\response;

use tests\TestCase;
use kanso\framework\http\request\Environment;

/**
 * @group unit
 */
class EnvironmentTest extends TestCase
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
	public function testEnvironment()
	{
		$server = $this->getServerData();

		$env = new Environment($server);

		$this->assertEquals('index.php', $env->SCRIPT_NAME);

		$server['SCRIPT_NAME'] = '/var/www/app.php';

		$env->reload($server);
			
		$this->assertEquals('app.php', $env->SCRIPT_NAME);
	}

}