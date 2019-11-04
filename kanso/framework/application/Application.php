<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application;

use kanso\framework\autoload\AliasLoader;
use kanso\framework\config\Config;
use kanso\framework\config\Loader;
use kanso\framework\file\Filesystem;
use kanso\framework\ioc\Container;

/**
 * Kanso framework main class file.
 *
 * @author Joe J. Howard
 */
class Application
{
    /**
     * Singleton instance of self.
     *
     * @var \kanso\framework\application\Application
     */
    private static $instance;

    /**
     * IoC container instance.
     *
     * @var \kanso\framework\ioc\Container
     */
    protected $container;

    /**
     * Booted packages.
     *
     * @var array
     */
    protected $packages = [];

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * Starts the application and returns a singleton instance of the application.
     *
     * @access public
     * @return \kanso\framework\application\Application
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
     * Run the application.
     *
     * @access public
     */
    public function run()
    {
        $this->container->Router->dispatch();

        $response = $this->container->Onion->peel();

        if ($this->container->Config->get('application.send_response') === true)
        {
            $this->container->Response->send();
        }

        $this->container->ErrorHandler->restore();
    }

    /**
     * Returns the IOC container
     *
     * @access public
     * @return \kanso\framework\ioc\Container
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Returns the Kanso environment.
     *
     * @access public
     * @return string|null
     */
    public function environment()
    {
        if (defined('KANSO_ENV'))
        {
            return KANSO_ENV;
        }

        return null;
    }

    /**
     * Boot the application dependencies.
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
        $this->registerContainer();

        $this->registerConfig();

        $this->registerFilesystem();
    }

    /**
     * Register the IOC container
     *
     * @access protected
     */
    protected function registerContainer()
    {
        $this->container = Container::instance();

        $this->container->setInstance('Application', $this);
    }

    /**
     * Register the Filesystem
     *
     * @access protected
     */
    protected function registerFilesystem()
    {
        $this->container->singleton('Filesystem', function()
        {
            return new Filesystem;
        });
    }

    /**
     * Register the config 
     *
     * @access protected
     */
    protected function registerConfig()
    {
        $this->container->singleton('Config', function()
        {
            return $this->configFactory();
        });
    }

    /**
     * Configure application basics.
     *
     * @access protected
     */
    protected function configure()
    {
        mb_language('uni');

        mb_regex_encoding($this->container->Config->get('application.charset'));

        mb_internal_encoding($this->container->Config->get('application.charset'));

        date_default_timezone_set($this->container->Config->get('application.timezone'));

        ini_set('date.timezone', $this->container->Config->get('application.timezone'));
    }

    /**
     * Builds a configuration instance.
     *
     * @access protected
     * @return \kanso\framework\config\Config
     */
    protected function configFactory(): Config
    {
        return new Config(new Loader(new Filesystem, $this->configurationPath()), $this->environment());
    }

    /**
     * Returns the configuration path.
     *
     * @access protected
     * @return string
     */
    protected function configurationPath(): string
    {
        return APP_DIR . DIRECTORY_SEPARATOR . 'configurations';
    }

    /**
     * Is the application running in the CLI?
     *
     * @return bool
     */
    protected function isCommandLine(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Register required services.
     *
     * @access protected
     */
    protected function registerServices()
    {
        if (!$this->isCommandLine())
        {
            $this->registerWebServices();
        }
        else
        {
            $this->registerClisServices();
        }
    }

    /**
     * Register default services.
     *
     * @access protected
     */
    protected function registerWebServices()
    {
        foreach (array_keys($this->container->Config->get('application.services')) as $package)
        {
            if ($package === 'cli')
            {
                continue;
            }

            $this->registerPackage($package);
        }
    }

    /**
     * Register cli services.
     *
     * @access protected
     */
    protected function registerClisServices()
    {
        $this->registerPackage('framework');

        $this->registerPackage('cli');
    }

    /**
     * Registers services in the IoC container.
     *
     * @access protected
     * @param string $name Service name
     */
    protected function registerPackage(string $name)
    {
        foreach ($this->container->Config->get('application.services.' . $name) as $service)
        {
            $this->registerService($service);
        }
    }

    /**
     * Registers services in the IoC container.
     *
     * @access protected
     * @param string $service Service name
     */
    protected function registerService(string $service)
    {
        (new $service($this->container))->register();
    }

    /**
     * Registers class aliases.
     *
     * @access protected
     */
    protected function registerClassAliases()
    {
        $aliases = $this->container->Config->get('application.class_aliases');

        if (!empty($aliases))
        {
            $aliasLoader = new AliasLoader($aliases);

            spl_autoload_register([$aliasLoader, 'load']);
        }
    }
}
