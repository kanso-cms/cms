<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\ioc;

/**
 * Container aware trait.
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
	 * Sets and or gets the container.
	 *
	 * @return \kanso\framework\ioc\Container
	 */
	public function container(): Container
	{
		if (!$this->container)
		{
			$this->container = Container::instance();
		}

		return $this->container;
	}

	/**
	 * Resolves item from the container using overloading.
	 *
	 * @param  string $key Key
	 * @return mixed
	 */
	public function __get(string $key)
	{
		return $this->container()->get($key);
	}
}
