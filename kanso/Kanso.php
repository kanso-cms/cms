<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso;

use kanso\framework\application\Application;
use kanso\framework\ioc\Container;
use kanso\framework\ioc\ContainerAwareTrait;

/**
 * Kanso instantiation
 *
 * @author Joe J. Howard
 */
class Kanso 
{
	use ContainerAwareTrait;

	/**
	 * Kanso application version
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Singleton instance of self
	 *
	 * @var \kanso\Kanso
	 */
	protected static $instance;

	/**
	 * Application instance
	 *
	 * @var \kanso\framework\application\Application
	 */
	protected $application;
	
	/**
	 * Constructor. Boots application
	 *
	 * @access public
	 */
	protected function __construct()
	{
		$this->application = Application::instance();

		$this->setContainer($this->application->container());
	}

	/**
	 * Get the global Kanso instance
	 *
	 * @access public
	 * @return \kanso\Kanso
	 */
	public static function instance(): Kanso
	{
		if(!empty(static::$instance))
		{
			return static::$instance;
		}

		return static::$instance = new static;
	}

	/**
	 * Returns the application container
	 *
	 * @access public
	 * @return \kanso\framework\ioc\Container
	 */
	public function container(): Container
	{
		return $this->application->container();
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
}
