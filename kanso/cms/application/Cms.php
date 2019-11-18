<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application;

use kanso\framework\ioc\Container;

/**
 * CMS initializer.
 *
 * @author Joe J. Howard
 */
class Cms
{
	/**
	 * IoC container instance.
	 *
	 * @var \kanso\framework\ioc\Container
	 */
	private $container;

    /**
     * Constructor.
     *
     * @param \kanso\framework\ioc\Container $container IoC container
     */
    public function __construct(Container $container)
    {
    	$this->container = $container;

    	$this->boot();
    }

    /**
     * Boot the CMS.
     */
    private function boot(): void
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
	 * Validate the incoming request with the access conditions.
	 */
	private function precheckAccess(): void
	{
		if ($this->container->Access->ipBlockEnabled() && !$this->container->Access->isIpAllowed())
		{
			$this->container->Access->block();
		}
	}

	/**
	 * Apply the CMS routes.
	 */
	private function applyRoutes(): void
	{
		include_once 'services/Routes.php';
	}

    /**
     * Registers includes on all view renders.
     */
    private function registerViewIncludes(): void
    {
    	$this->container->View->includes(
    		[
    			$this->container->Config->get('cms.themes_path') . '/' . $this->container->Config->get('cms.theme_name') . '/functions.php',
    			KANSO_DIR . '/cms/query/Includes.php',
    		]
    	);
    }

    /**
     * Boot the installer.
     */
    private function bootInstaller(): void
    {
    	// Make sure Kanso is installed
		if (!$this->container->Installer->isInstalled())
		{
			$this->container->Router->get('/', [&$this->container->Installer, 'run']);

			$this->container->Router->get('/', [&$this->container->Installer, 'display']);
		}
    }

	/**
	 * Handle 404 not found on for the CMS.
	 */
	private function notFoundHandling(): void
	{
		// 404 get displayed the theme 404 template
		$this->container->ErrorHandler->handle('\kanso\framework\http\response\exceptions\NotFoundException', function($exception): void
		{
			// Only show the template if it exists, not ajax request and not displaying errors
			// Otherwise we fallback to applications default error handling
			$template = $this->container->Config->get('cms.themes_path') . DIRECTORY_SEPARATOR . $this->container->Config->get('cms.theme_name') . DIRECTORY_SEPARATOR . '404.php';

			if (file_exists($template) && !$this->container->Request->isAjax() && !$this->container->ErrorHandler->display_errors())
			{
				$this->container->Response->status()->set(404);

				$this->container->Response->body()->set($this->container->View->display($template));

				$this->container->Response->send();

				// Stop handling this error
				// return false;
			}

		});
	}
}
