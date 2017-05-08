<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\ioc;

use RuntimeException;
use kanso\framework\ioc\Container;

/**
 * Container aware trait
 *
 * @author Joe J. Howard
 */
trait ContainerAwareTrait
{
	/**
	 * IoC container instance.
	 *
	 * @var \kanso\framework\ioc\Container
	 */
	protected $container;

	/**
	 * Sets the container instance.
	 *
	 * @access public
	 * @param \kanso\framework\ioc\Container $container IoC container instance
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Resolves item from the container using overloading.
	 *
	 * @access public
	 * @param  string $key Key
	 * @return mixed
	 */
	public function __get(string $key)
	{
		return $this->container->get($key);
	}
}