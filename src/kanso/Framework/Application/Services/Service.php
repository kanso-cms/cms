<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Application\Services;

use Kanso\Framework\IoC\Container;

/**
 * Service provider base class
 *
 * @author Joe J. Howard
 */
abstract class Service
{
	/**
	 * IoC container instance
	 *
	 * @var \Kanso\Framework\IoC\Container
	 */
	protected $container;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param  \Kanso\Framework\IoC\Container $container IoC container instance
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Registers the service.
	 *
	 * @access public
	 */
	abstract public function register();
}
