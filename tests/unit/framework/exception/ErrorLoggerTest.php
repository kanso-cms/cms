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
use kanso\framework\exception\ErrorLogger;

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
        $logger      = new ErrorLogger(new ErrorException, $environment, $path);

        $environment->shouldReceive('__get')->withArgs(['REQUEST_URL'])->andReturn('http:/foo.com/bar');
        $environment->shouldReceive('__get')->withArgs(['REMOTE_ADDR'])->andReturn('1.0.0.0');
        $environment->shouldReceive('__get')->withArgs(['HTTP_USER_AGENT'])->andReturn('mozilla');

        $logger->write();

        $this->assertTrue(file_exists($path.'/'. date('d_m_y') . '_all_errors.log'));
        $this->assertTrue(file_exists($path.'/'. date('d_m_y') . '_other_errors.log'));

        unlink($path.'/'. date('d_m_y') . '_all_errors.log');
        unlink($path.'/'. date('d_m_y') . '_other_errors.log');
    }
}
