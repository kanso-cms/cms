<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\http\response;

use kanso\framework\http\response\exceptions\ForbiddenException;
use kanso\framework\http\response\exceptions\InvalidTokenException;
use kanso\framework\http\response\exceptions\MethodNotAllowedException;
use kanso\framework\http\response\exceptions\NotFoundException;
use kanso\framework\http\response\exceptions\RequestException;
use kanso\framework\http\response\exceptions\Stop;
use kanso\tests\TestCase;

/**
 * @group unit
 * @group framework
 */
class ExceptionTest extends TestCase
{
	/**
	 * @expectedException \kanso\framework\http\response\exceptions\RequestException
	 */
	public function testRequest(): void
	{
		throw new RequestException(500, 'foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\InvalidTokenException
	 */
	public function testToken(): void
	{
		throw new InvalidTokenException('foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\NotFoundException
	 */
	public function testNotFound(): void
	{
		throw new NotFoundException('foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\MethodNotAllowedException
	 */
	public function testMethod(): void
	{
		throw new MethodNotAllowedException(['POST', 'GET'], 'foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\ForbiddenException
	 */
	public function testForbidden(): void
	{
		throw new ForbiddenException('foobar message');
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\Stop
	 */
	public function testStop(): void
	{
		throw new Stop;
	}
}
