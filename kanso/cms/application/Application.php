<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application;

use kanso\framework\ioc\Container;
use kanso\cms\application\LoaderTrait;

/**
 * CMS main class
 *
 * @author Joe J. Howard
 */
class Application 
{
	use LoaderTrait;

	/**
	 * IoC container instance
	 *
	 * @var \kanso\framework\application\Container
	 */
	private $container;

	/**
	 * Instance of self
	 *
	 * @var \kanso\framework\application\Container
	 */
	private static $instance;

	/**
     * Constructor
     *
     * @access private
     * @param  \kanso\framework\application\Container $container IoC container
     */
    private function __construct(Container $container)
    {
    	$this->container = $container;
    }

    /**
	 * Starts and/or returns the instance of the application
	 *
	 * @access public
	 * @param  \kanso\framework\application\Container $container IoC container (optional) (default null)
	 * @return \kanso\cms\CMS
	 */
	public static function instance($container = null): Application
	{
		if (is_null(static::$instance))
		{
			static::$instance = new static($container);
		}

		return static::$instance;
	}

    /**
     * Boot the CMS
     *
     * @access public
     */
    public function boot()
    {
    	$this->precheckAccess();

    	$this->registerViewIncludes();

    	$this->bootInstaller();

		$this->notFoundHandling();

		if ($this->container->Installer->isInstalled())
		{
			$this->applyRoutes();
		}
    }

    /**
	 * Validate the incoming request with the access conditions
	 *
	 * @access private
	 */
	private function precheckAccess()
	{
		if ($this->container->Access->ipBlockEnabled() && !$this->container->Access->isIpAllowed())
		{
			$this->container->Access->block();
		}
	}

    /**
	 * Apply the CMS routes
	 *
	 * @access private
	 */
	private function applyRoutes()
	{
		include_once 'services/Routes.php';
	}

    /**
     * Registers includes on all view renders
     *
     * @access private
     */
    private function registerViewIncludes()
    {
    	$this->container->View->includes(
    		[
    			$this->container->Config->get('cms.themes_path').'/'.$this->container->Config->get('cms.theme_name').'/functions.php',
    			KANSO_DIR.'/cms/query/Includes.php'
    		]
    	);
    }

    /**
     * Boot the installer
     *
     * @access private
     */
    private function bootInstaller()
    {
    	# Make sure Kanso is installed
		if (!$this->container->Installer->isInstalled())
		{
			$this->container->Router->get('/', [&$this->container->Installer, 'run']);

			$this->container->Router->get('/', [&$this->container->Installer, 'display']);
		}
    }
}
