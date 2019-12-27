<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\exception;

use ErrorException;
use InvalidArgumentException;
use kanso\framework\exception\ErrorHandler;
use kanso\tests\TestCase;
use Throwable;

/**
 * @group unit
 * @group framework
 */
class ErrorHandlerTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testWebHandler(): void
    {
        $handler    = new ErrorHandler(true, E_ALL | E_STRICT);
        $logger     = $this->mock('\kanso\framework\exception\ErrorLogger');
        $webHandler = $this->mock('\kanso\framework\exception\handlers\WebHandler');

        $handler->handle(ErrorException::class, function($exception) use ($handler, $logger, $webHandler)
        {
            // Logger
            $handler->setLogger($logger);

            // Handle
            return $webHandler->handle($handler->display_errors());
        });

        $logger->shouldReceive('write');

        $webHandler->shouldReceive('handle')->withArgs([true])->andReturn(true);

        $handler->handler(new ErrorException);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDifferentError(): void
    {
        $handler    = new ErrorHandler(true, E_ALL | E_STRICT);
        $logger     = $this->mock('\kanso\framework\exception\ErrorLogger');
        $webHandler = $this->mock('\kanso\framework\exception\handlers\WebHandler');

        $handler->handle(Throwable::class, function($exception) use ($handler, $logger, $webHandler)
        {
            // Logger
            $handler->setLogger($logger);

            // Handle
            return $webHandler->handle($handler->display_errors());
        });

        $logger->shouldReceive('write');

        $webHandler->shouldReceive('handle')->withArgs([true])->andReturn(true);

        $handler->handler(new InvalidArgumentException);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDisableLogging(): void
    {
        $handler    = new ErrorHandler(true, E_ALL | E_STRICT);
        $logger     = $this->mock('\kanso\framework\exception\ErrorLogger');
        $webHandler = $this->mock('\kanso\framework\exception\handlers\WebHandler');

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
    public function testDisplayErrors(): void
    {
        $handler    = new ErrorHandler(false, E_ALL | E_STRICT);
        $logger     = $this->mock('\kanso\framework\exception\ErrorLogger');
        $webHandler = $this->mock('\kanso\framework\exception\handlers\WebHandler');

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
