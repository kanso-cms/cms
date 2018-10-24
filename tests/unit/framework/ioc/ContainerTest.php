<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace tests\unit\framework\ioc;

use kanso\framework\ioc\Container;
use tests\TestCase;

class ContainerCallbackTest
{
	private $var;

	public function __construct()
    {
    }

    public function setVar($var)
   	{
   		$this->var = $var;
   	}

    public function getVar()
   	{
   		return $this->var;
   	}
}

/**
 * @group unit
 */
class ContainerTest extends TestCase
{
	/**
	 *
	 */
	public function testSet()
	{
		$container = Container::instance();

		$container->clear();

		$container->set('foo', 'bar');

		$this->assertEquals('bar', $container->get('foo'));

		$container->clear();
	}

	/**
	 *
	 */
	public function testSetClass()
	{
		$container = Container::instance();

		$testClass = new ContainerCallbackTest;

		$container->set('TestClass', $testClass);

		$testClass->setVar('foo');

		$container->TestClass->setVar('bar');

		$this->assertEquals('bar', $testClass->getVar());

		$this->assertEquals('bar', $container->TestClass->getVar());

		$container->clear();
	}

	/**
	 *
	 */
	public function testSetReturnClass()
	{
		$container = Container::instance();

		$container->set('foo', function()
		{
			return new ContainerCallbackTest;
		});

		$instance1 = $container->get('foo');

		$instance2 = $container->get('foo');

		$instance1->setVar('foo');

		$instance2->setVar('bar');

		$this->assertEquals('foo', $instance1->getVar());

		$this->assertEquals('bar', $instance2->getVar());

		$container->clear();
	}

	/**
	 *
	 */
	public function testSingleton()
	{
		$container = Container::instance();

		$container->singleton('foo', function()
		{
			return new ContainerCallbackTest;
		});

		$instance1 = $container->get('foo');

		$instance2 = $container->get('foo');

		$instance1->setVar('foo');

		$instance2->setVar('bar');

		$this->assertEquals('bar', $instance1->getVar());

		$this->assertEquals('bar', $instance2->getVar());

		$container->clear();
	}

	/**
	 *
	 */
	public function testInstance()
	{
		$container = Container::instance();

		$container->setInstance('foo', new ContainerCallbackTest);

		$instance1 = $container->get('foo');

		$instance2 = $container->get('foo');

		$instance1->setVar('foo');

		$instance2->setVar('bar');

		$this->assertEquals('bar', $instance1->getVar());

		$this->assertEquals('bar', $instance2->getVar());

		$container->clear();
	}

	/**
	 *
	 */
	public function testIteration()
	{
		$container = Container::instance();

		$container->set('foo', new ContainerCallbackTest);

		$container->set('bar', new ContainerCallbackTest);

		$i = 0;

		foreach ($container as $k => $v)
		{
			if ($i === 0)
			{
				$this->assertEquals('foo', $k);
			}
			else
			{
				$this->assertEquals('bar', $k);
			}

			$i++;
		}

		$container->clear();
	}

	/**
	 *
	 */
	public function testArrayAccess()
	{
		$container = Container::instance();

		$container->set('foo', new ContainerCallbackTest);

		$this->assertTrue($container['foo'] instanceof ContainerCallbackTest);

		$container->clear();
	}

	/**
	 *
	 */
	public function testCount()
	{
		$container = Container::instance();

		$container->set('foo', new ContainerCallbackTest);

		$container->set('bar', new ContainerCallbackTest);

		$container->set('foobar', new ContainerCallbackTest);

		$this->assertEquals(3, count($container));

		$container->clear();
	}
}
