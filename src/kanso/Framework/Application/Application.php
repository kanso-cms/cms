<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Application;

use Kanso\Framework\IoC\Container;
use Kanso\Framework\Config\Config;
use Kanso\Framework\Config\Loader;
use Kanso\Framework\Autoload\AliasLoader;

/**
 * Kanso framework main class file
 *
 * @author Joe J. Howard
 */
class Application
{
	/**
	 * Singleton instance of self.
	 *
	 * @var \Kanso\Framework\Application\Application
	 */
	private static $instance;

	/**
	 * IoC container instance
	 *
	 * @var \Kanso\Framework\Application\Container
	 */
	protected $container;

	/**
	 * Booted packages.
	 *
	 * @var array
	 */
	protected $packages = [];

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct()
	{
		$this->boot();
	}

	/**
	 * Starts the application and returns a singleton instance of the application.
	 *
	 * @access public
	 * @return \Kanso\Framework\Application\Application
	 */
	public static function instance()
	{
		if (is_null(static::$instance))
		{
			static::$instance = new static;
		}

		return static::$instance;	
	}

	/**
	 * Run the application
	 *
	 * @access public
	 */
	public function run()
	{
		$this->container->Router->dispatch();

		$response = $this->container->Onion->peel();

		$this->container->Response->send();

		$this->container->ErrorHandler->restore();
	}

	/**
	 * Boot the application dependencies
	 *
	 * @access protected
	 */
	protected function boot()
	{
		$this->initialize();

		$this->configure();

		$this->registerServices();

		$this->registerClassAliases();
	}

	/**
	 * Sets up the framework core.
	 *
	 * @access protected
	 */
	protected function initialize()
	{
		$this->container = new Container();

		$this->container->singleton('Config', function() 
		{
			return $this->configFactory();
		});
	}

	/**
	 * Returns the Kanso environment.
	 *
	 * @access public
	 * @return string|null
	 */
	public function environment()
	{
		if(defined('KANSO_ENV'))
		{
			return KANSO_ENV;
		}

		return null;
	}

	/**
	 * Returns the IoC container instance.
	 *
	 * @access public
	 * @return \Kanso\Framework\Application\Container
	 */
	public function container()
	{
		return $this->container;
	}

	/**
	 * Configure application basics
	 *
	 * @access protected
	 */
	protected function configure()
	{
		mb_language('uni');

		mb_regex_encoding($this->container->Config->get('application.charset'));

		mb_internal_encoding($this->container->Config->get('application.charset'));

		date_default_timezone_set($this->container->Config->get('application.timezone'));
	}

	/**
	 * Builds a configuration instance.
	 *
	 * @access protected
	 * @return \Kanso\Framework\Config\Config
	 */
	protected function configFactory(): Config
	{		
		return new Config( new Loader(CONFIG_DIR), $this->environment());
	}

	/**
	 * Register required services
	 *
	 * @access protected
	 */
	protected function registerServices()
	{
		foreach (array_keys($this->container->Config->get('application.services')) as $package)
		{
			# Register the core services
			$this->serviceRegistrar($package);
		}
	}

	/**
	 * Registers services in the IoC container.
	 *
	 * @access protected
	 * @param  string    $type Service type
	 */
	protected function serviceRegistrar(string $type)
	{
		foreach($this->container->Config->get('application.services.' . $type) as $service)
		{
			(new $service($this->container))->register();
		}
	}

	/**
	 * Registers class aliases.
	 *
	 * @access protected
	 */
	protected function registerClassAliases()
	{
		$aliases = $this->container->Config->get('application.class_aliases');

		if(!empty($aliases))
		{
			$aliasLoader = new AliasLoader($aliases);

			spl_autoload_register([$aliasLoader, 'load']);
		}
	}
}
