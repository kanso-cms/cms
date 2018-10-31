<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\config;

use kanso\framework\config\Loader;
use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group framework
 */
class LoaderTest extends TestCase
{
    /**
     *
     */
    public function getFilesystem()
    {
        return Mockery::mock('\kanso\framework\file\Filesystem');
    }

    /**
     *
     */
    public function testLoad()
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello']);

        $loader = new Loader($fileSystem, '/app/config');

        $this->assertEquals(['greeting' => 'hello'], $loader->load('settings'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadNonExistingFile()
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(false);

        $loader = new Loader($fileSystem, '/app/config');

        $loader->load('settings');
    }

    /**
     *
     */
    public function testLoadPackage()
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/packages/baz/settings.php')->andReturn(false);

        $fileSystem->shouldReceive('exists')->once()->with('/app/packages/baz/config/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->once()->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello']);

        $loader = new Loader($fileSystem, '/app/config');

        $loader->registerNamespace('baz', '/app/packages/baz/config');

        $this->assertEquals(['greeting' => 'hello'], $loader->load('baz::settings'));
    }

    /**
     *
     */
    public function testLoadPackageOverride()
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('exists')->with('/app/packages/baz/config/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello']);

        $fileSystem->shouldReceive('exists')->with('/app/config/packages/baz/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->with('/app/config/packages/baz/settings.php')->andReturn(['greeting' => 'hello']);

        $loader = new Loader($fileSystem, '/app/config');

        $loader->registerNamespace('baz', '/app/packages/baz/config');

        $this->assertEquals(['greeting' => 'hello'], $loader->load('baz::settings'));
    }

    /**
     *
     */
    public function testLoadEvironmentOverride()
    {
        $fileSystem = $this->getFilesystem();

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello', 'goodbye' => 'sayonara']);

        $fileSystem->shouldReceive('exists')->once()->with('/app/config/dev/settings.php')->andReturn(true);

        $fileSystem->shouldReceive('include')->once()->with('/app/config/dev/settings.php')->andReturn(['greeting' => 'konnichiwa']);

        $loader = new Loader($fileSystem, '/app/config');

        $this->assertEquals(['greeting' => 'konnichiwa', 'goodbye' => 'sayonara'], $loader->load('settings', 'dev'));
    }
}
