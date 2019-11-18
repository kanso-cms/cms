<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\framework\ioc;

use kanso\framework\ioc\Container;
use kanso\framework\ioc\ContainerAwareTrait;
use kanso\tests\TestCase;

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
 * @group framework
 */
class ContainerAwareTest extends TestCase
{
	/**
	 *
	 */
	public function testGetFromContainer(): void
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
	public function testGetPrivate(): void
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
	public function testGetPublic(): void
	{
		$container = Container::instance();

		$container->set('foo', 'bar');

		$class = new ContainerAwareCallback;

		$this->assertEquals('foobarz', $class->foobarz);

		$container->clear();
	}
}
