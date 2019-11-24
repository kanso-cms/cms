<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\services;

use kanso\framework\ioc\Container;

/**
 * Service provider base class.
 *
 * @author Joe J. Howard
 */
abstract class Service
{
	/**
	 * IoC container instance.
	 *
	 * @var \kanso\framework\ioc\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param \kanso\framework\ioc\Container $container IoC container instance
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Registers the service.
	 */
	abstract public function register();
}
