<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\exception;

use Mockery;
use tests\TestCase;
use Throwable;
use ErrorException;
use InvalidArgumentException;
use kanso\framework\exception\ErrorHandler;

/**
 * @group unit
 */
class ErrorHandlerTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testWebHandler()
    {
        $handler    = new ErrorHandler(true, E_ALL | E_STRICT);
        $logger     = Mockery::mock('\kanso\framework\exception\ErrorLogger');
        $webHandler = Mockery::mock('\kanso\framework\exception\handlers\WebHandler');

        $handler->handle(ErrorException::class, function($exception) use ($handler, $logger, $webHandler)
        {
            # Logger
            $handler->setLogger($logger);

            # Handle
            return $webHandler->handle($handler->display_errors());
        });

        $logger->shouldReceive('write');

        $webHandler->shouldReceive('handle')->withArgs([true])->andReturn(true);

        $handler->handler(new ErrorException);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDifferentError()
    {
        $handler    = new ErrorHandler(true, E_ALL | E_STRICT);
        $logger     = Mockery::mock('\kanso\framework\exception\ErrorLogger');
        $webHandler = Mockery::mock('\kanso\framework\exception\handlers\WebHandler');

        $handler->handle(Throwable::class, function($exception) use ($handler, $logger, $webHandler)
        {
            # Logger
            $handler->setLogger($logger);

            # Handle
            return $webHandler->handle($handler->display_errors());
        });

        $logger->shouldReceive('write');

        $webHandler->shouldReceive('handle')->withArgs([true])->andReturn(true);

        $handler->handler(new InvalidArgumentException);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDisableLogging()
    {
        $handler    = new ErrorHandler(true, E_ALL | E_STRICT);
        $logger     = Mockery::mock('\kanso\framework\exception\ErrorLogger');
        $webHandler = Mockery::mock('\kanso\framework\exception\handlers\WebHandler');
        
        $handler->handle(Throwable::class, function($exception) use ($handler, $logger, $webHandler)
        {
            return $webHandler->handle($handler->display_errors());
        });

        $handler->setLogger($logger);
        
        $handler->disableLoggingFor(ErrorException::class);

        $webHandler->shouldReceive('handle')->withArgs([true])->andReturn(true);

        $handler->handler(new ErrorException);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDisplayErrors()
    {
        $handler    = new ErrorHandler(false, E_ALL | E_STRICT);
        $logger     = Mockery::mock('\kanso\framework\exception\ErrorLogger');
        $webHandler = Mockery::mock('\kanso\framework\exception\handlers\WebHandler');
        
        $handler->handle(Throwable::class, function($exception) use ($handler, $logger, $webHandler)
        {
            return $webHandler->handle($handler->display_errors());
        });

        $handler->setLogger($logger);
        
        $handler->disableLoggingFor(ErrorException::class);

        $webHandler->shouldReceive('handle')->withArgs([false])->andReturn(true);

        $handler->handler(new ErrorException);
    }
}
