<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\ioc;

use tests\TestCase;
use kanso\framework\ioc\Container;
use kanso\framework\ioc\ContainerAwareTrait;

class ContainerAwareCallback
{
	use ContainerAwareTrait;

	private $foobaz = 'foobaz';

	public $foobarz = 'foobarz';

	public function __construct()
    { 
    }

    public function getPrivate()
    {
    	return $this->foobaz;
    }
}

/**
 * @group unit
 */
class ContainerAwareTest extends TestCase
{
	/**
	 *
	 */
	public function testGetFromContainer()
	{
		$container = Container::instance();

		$container->set('foo', 'bar');

		$container->set('bar', 'foo');

		$class = new ContainerAwareCallback;

		$this->assertEquals('foo', $class->bar);

		$this->assertEquals('bar', $class->container()->get('foo'));

		$this->assertEquals(null, $class->foobar);

		$container->clear();
	}

	/**
	 *
	 */
	public function testGetPrivate()
	{
		$container = Container::instance();

		$container->set('foo', 'bar');

		$class = new ContainerAwareCallback;

		$this->assertEquals('foobaz', $class->getPrivate());

		$container->clear();
	}

	/**
	 *
	 */
	public function testGetPublic()
	{
		$container = Container::instance();

		$container->set('foo', 'bar');

		$class = new ContainerAwareCallback;

		$this->assertEquals('foobarz', $class->foobarz);

		$container->clear();
	}
}