<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\config;

use kanso\framework\config\Config;
use Mockery;
use tests\TestCase;

/**
 * @group unit
 */
class ConfigTest extends TestCase
{
	/**
	 *
	 */
	public function getLoader()
	{
		return Mockery::mock('\kanso\framework\config\Loader');
	}

	/**
	 *
	 */
	public function testBasic()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn(['greeting' => 'hello']);

		$config = new Config($loader);

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$this->assertNull($config->get('settings.world'));

		$this->assertFalse($config->get('settings.world', false));

		$this->assertEquals(['settings' => ['greeting' => 'hello']], $config->getLoadedConfiguration());
	}

	/**
	 *
	 */
	public function testBasicWithEnvironment()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', 'foo')->andReturn(['greeting' => 'hello']);

		$config = new Config($loader, 'foo');

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$this->assertNull($config->get('settings.world'));

		$this->assertFalse($config->get('settings.world', false));

		$this->assertEquals(['settings' => ['greeting' => 'hello']], $config->getLoadedConfiguration());
	}

	/**
	 *
	 */
	public function testSet()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn([]);

		$config = new Config($loader);

		$this->assertNull($config->get('settings.greeting'));

		$config->set('settings.greeting', 'hello');

		$this->assertEquals('hello', $config->get('settings.greeting'));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn(['greeting' => 'hello']);

		$config = new Config($loader);

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$config->remove('settings.greeting');

		$this->assertNull($config->get('settings.greeting'));
	}

	/**
	 *
	 */
	public function testGetLoader()
	{
		$loader = $this->getLoader();

		$config = new Config($loader);

		$this->assertInstanceOf('\kanso\framework\config\Loader', $config->getLoader());
	}
}
