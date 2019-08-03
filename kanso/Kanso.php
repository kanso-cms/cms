<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso;

use kanso\framework\application\Application;
use kanso\framework\ioc\ContainerAwareTrait;

/**
 * Kanso instantiation.
 *
 * @author Joe J. Howard
 */
class Kanso
{
	use ContainerAwareTrait;

	/**
	 * Kanso application version.
	 *
	 * @var string
	 */
	const VERSION = '5.0.0';

	/**
	 * Singleton instance of self.
	 *
	 * @var \kanso\Kanso
	 */
	protected static $instance;

	/**
	 * Application instance.
	 *
	 * @var \kanso\framework\application\Application
	 */
	protected $application;

	/**
	 * Constructor. Boots application.
	 *
	 * @access protected
	 */
	protected function __construct()
	{
		$this->application = Application::instance();
	}

	/**
	 * Get the global Kanso instance.
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
	 * Application run.
	 *
	 * @access public
	 * @return \kanso\framework\application\Application
	 */
	public function application(): Application
	{
		return $this->application;
	}

	/**
	 * Application run.
	 *
	 * @access public
	 */
	public function run()
	{
		$this->application->run();
	}
}
