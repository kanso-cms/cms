<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\access;

use kanso\cms\access\Access;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class AccessTest extends TestCase
{
	/**
	 *
	 */
	public function testEnabled(): void
	{
		$request    = Mockery::mock('\kanso\framework\http\request\Request');
		$response   = Mockery::mock('\kanso\framework\http\response\Response');
		$env        = Mockery::mock('\kanso\framework\http\request\Environment');
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$this->assertTrue($access->ipBlockEnabled());
	}

	/**
	 *
	 */
	public function testDisabled(): void
	{
		$request    = Mockery::mock('\kanso\framework\http\request\Request');
		$response   = Mockery::mock('\kanso\framework\http\response\Response');
		$env        = Mockery::mock('\kanso\framework\http\request\Environment');
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, false, $whiteList);

		$this->assertFalse($access->ipBlockEnabled());
	}

	/**
	 *
	 */
	public function testIpAllowed(): void
	{
		$request    = Mockery::mock('\kanso\framework\http\request\Request');
		$response   = Mockery::mock('\kanso\framework\http\response\Response');
		$env        = Mockery::mock('\kanso\framework\http\request\Environment');
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$this->assertTrue($access->isIpAllowed());
	}

	/**
	 *
	 */
	public function testIpNotAllowed(): void
	{
		$request    = Mockery::mock('\kanso\framework\http\request\Request');
		$response   = Mockery::mock('\kanso\framework\http\response\Response');
		$env        = Mockery::mock('\kanso\framework\http\request\Environment');
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$whiteList  = ['192.168.1.2'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$this->assertFalse($access->isIpAllowed());
	}

	/**
	 * @expectedException \kanso\framework\http\response\exceptions\ForbiddenException
	 */
	public function testBlock(): void
	{
		$request    = Mockery::mock('\kanso\framework\http\request\Request');
		$response   = Mockery::mock('\kanso\framework\http\response\Response');
		$env        = Mockery::mock('\kanso\framework\http\request\Environment');
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$access->block();
	}

	/**
	 *
	 */
	public function testSaveRobots(): void
	{
		$request    = Mockery::mock('\kanso\framework\http\request\Request');
		$response   = Mockery::mock('\kanso\framework\http\response\Response');
		$env        = Mockery::mock('\kanso\framework\http\request\Environment');
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$filesystem->shouldReceive('putContents')->with('/foo/bar/robots.txt', "User-agent: *\nDisallow:");

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$access->saveRobots($access->defaultRobotsText());
	}

	/**
	 *
	 */
	public function testDeleteRobots(): void
	{
		$request    = Mockery::mock('\kanso\framework\http\request\Request');
		$response   = Mockery::mock('\kanso\framework\http\response\Response');
		$env        = Mockery::mock('\kanso\framework\http\request\Environment');
		$filesystem = Mockery::mock('\kanso\framework\file\Filesystem');
		$whiteList  = ['192.168.1.1'];

		$env->DOCUMENT_ROOT = '/foo/bar';

		$env->REMOTE_ADDR = '192.168.1.1';

		$request->shouldReceive('environment')->andReturn($env);

		$filesystem->shouldReceive('exists')->with('/foo/bar/robots.txt')->andReturn(true);

		$filesystem->shouldReceive('delete')->with('/foo/bar/robots.txt');

		$access = new Access($request, $response, $filesystem, true, $whiteList);

		$access->deleteRobots();
	}
}
