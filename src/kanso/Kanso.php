<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso;

use Kanso\Framework\Application\Application;

/**
 * Kanso instantiation
 *
 * @author Joe J. Howard
 */
class Kanso 
{
	/**
	 * Kanso application version
	 *
	 * @var string
	 */
	const VERSION = '0.0.87';

	/**
	 * Singleton instance of self
	 *
	 * @var \Kanso\Kanso
	 */
	protected static $instance;

	/**
	 * Application instance
	 *
	 * @var \Kanso\Framework\Application\Application
	 */
	protected $application;
	
	/**
	 * Constructor. Boots application
	 *
	 * @access public
	 */
	public function __construct()
	{
		$this->application = Application::instance();
	}

	/**
	 * Application run
	 *
	 * @access public
	 */
	public function run()
	{
		$this->application->run();
	}

	/**
	 * Get the global Kanso instance
	 *
	 * @access public
	 * @return \Kanso\Kanso
	 */
	public static function instance()
	{
		if(!empty(static::$instance))
		{
			return static::$instance;
		}

		return static::$instance = new static;
	}

	/**
	 * Get a key from the IoC container if it exists
	 *
	 * @access public
	 * @param  string $name Key to get value from
	 * @return mixed
	 */
	public function __get(string $name)
	{
		return $this->application->container()->get($name);
	}

	/**
	 * Set a key in the IoC container
	 *
	 * @access public
	 * @param  string $name  Key to set value under
	 * @param  mixed  $value Value to set
	 */
	public function __set(string $name, $value)
	{
		$this->application->container()->set($name, $value);
	}

	/**
	 * Check if the IoC container has a key 
	 *
	 * @access public
	 * @param  string $name Key to search with
	 * @return bool
	 */
	public function __isset(string $name): bool
	{
		return $this->application->container()->has($name);
	}

	/**
	 * Remove a key/value from the IoC container
	 *
	 * @access public
	 * @param  string $name Key to get value from
	 */
	public function __unset(string $name)
	{
		$this->application->container()->remove($name);
	}
}
