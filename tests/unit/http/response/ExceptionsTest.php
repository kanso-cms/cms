<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\http\response;

use tests\TestCase;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\NotFoundException;
use kanso\framework\http\response\exceptions\MethodNotAllowedException;
use kanso\framework\http\response\exceptions\ForbiddenException;
use kanso\framework\http\response\exceptions\Stop;

/**
 * @group unit
 */
class ExceptionTest extends TestCase
{
	/**
	 * @expectedException \kanso\framework\http\response\exceptions\RequestException
	 */
	public function testRequest()
	{
		throw new RequestException(500, 'foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\InvalidTokenException
	 */
	public function testToken()
	{
		throw new InvalidTokenException('foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\NotFoundException
	 */
	public function testNotFound()
	{
		throw new NotFoundException('foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\MethodNotAllowedException
	 */
	public function testMethod()
	{
		throw new MethodNotAllowedException(['POST', 'GET'], 'foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\ForbiddenException
	 */
	public function testForbidden()
	{
		throw new ForbiddenException('foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\Stop
	 */
	public function testStop()
	{
		throw new Stop;
	}
}