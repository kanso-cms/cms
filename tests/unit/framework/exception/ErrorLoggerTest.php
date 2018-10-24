<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\exception;

use ErrorException;
use kanso\framework\exception\ErrorLogger;
use Mockery;
use tests\TestCase;

/**
 * @group unit
 */
class ErrorLoggerTest extends TestCase
{
    /**
     *
     */
    public function testWebHandler()
    {
        $path        = dirname(__FILE__);
        $environment = Mockery::mock('\kanso\framework\http\request\Environment');
        $fileSystem  = Mockery::mock('\kanso\framework\file\Filesystem');
        $logger      = new ErrorLogger(new ErrorException, $fileSystem, $environment, $path);

        $environment->shouldReceive('__get')->withArgs(['REQUEST_URL'])->andReturn('http:/foo.com/bar');
        $environment->shouldReceive('__get')->withArgs(['REMOTE_ADDR'])->andReturn('1.0.0.0');
        $environment->shouldReceive('__get')->withArgs(['HTTP_USER_AGENT'])->andReturn('mozilla');
        $fileSystem->shouldReceive('appendContents');

        $logger->write();
    }
}
