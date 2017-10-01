<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application;

use Closure;
use kanso\framework\utility\Str;
use kanso\framework\ioc\Container;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\cms\rss\Feed;
use kanso\cms\sitemap\Sitemap;

/**
 * CMS main class
 *
 * @author Joe J. Howard
 */
class Application 
{
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

    /**
	 * Handle 404 not found on for the CMS
	 *
	 * @access private
	 */
	private function notFoundHandling()
	{
		# 404 get displayed the theme 404 template
		$this->container->ErrorHandler->handle('\kanso\framework\http\response\exceptions\NotFoundException', function($exception)
		{
			# Only show the template if it exists, not ajax request and not displaying errors
			# Otherwise we fallback to applications default error handling
			$template = $this->container->Config->get('cms.themes_path').DIRECTORY_SEPARATOR.$this->container->Config->get('cms.theme_name').DIRECTORY_SEPARATOR.'404.php';

			if (file_exists($template) && !$this->container->Request->isAjax() && !$this->container->ErrorHandler->display_errors())
			{
				$this->container->Response->body()->set($this->container->View->display($template));

				$this->container->Response->status()->set(404);

				$this->container->Response->cache()->disable();

				$this->container->Response->send();

				# Stop handling this error
				return false;
			}
			
		});
	}

	/**
     * Apply route to filter posts and load theme templates
     *
     * @access public
     * @param  \kanso\framework\http\request\Request   $request  Framework Request instance
     * @param  \kanso\framework\http\response\Response $response Framework Response instance
     * @param  \Closure                                $next     Next middleware layer
     * @param  string                                  $pageType The page type being loaded
     */
    public static function applyRoute(Request $request, Response $response, Closure $next, string $pageType)
    {    	
    	$_this = static::instance();
		
		$_this->container->Query->filterPosts($pageType);

		$template = $_this->getTemplate($pageType);

		if ($response->status()->get() !== 404 && $template)
		{
			$response->body()->set($response->view()->display($template));
		}
		else
		{
			$_this->container->Query->reset();

			$next();
		}
    }

    /**
     * Load an RSS feed
     *
     * @access public
     * @param  \kanso\framework\http\request\Request   $request  Framework Request instance
     * @param  \kanso\framework\http\response\Response $response Framework Response instance
     * @param  \Closure                                $next     Next middleware layer
     * @param  string                                  $pageType The page type being loaded
     */
	public static function loadRssFeed(Request $request, Response $response, Closure $next, string $pageType)
	{		
		# Get the Kanso Object instance
		$_this = static::instance();
		
		$_this->container->Query->filterPosts($pageType);

		# If the status was set to 404 here by the query, stop rendering
		# Note this does not send a 404 straight away. If you have a custom route
		# and wanted to display a template, you could still change the the status/response
		# between now and when kanso sends a response.
		if ($response->status()->get() !== 404)
		{
			$format = array_filter(explode('/', $_this->container->Request->environment()->REQUEST_URL));

			# Load the RSS module and render
			$rss = new Feed($request, $response, array_pop($format));
			
			$rss->render();
		}
		else
		{
			$_this->container->Query->reset();

			$next();
		}
	}

	/**
     * Load and render the XML sitemap
     *
     * @access public
     * @param  \kanso\framework\http\request\Request   $request  Framework Request instance
     * @param  \kanso\framework\http\response\Response $response Framework Response instance
     * @param  \Closure                                $next     Next middleware layer
     */
	public static function loadSiteMap(Request $request, Response $response, Closure $next)
	{
		# Get the Kanso Object instance
		$_this = static::instance();
		
		$sitemap = new Sitemap($request, $response, $_this->container->Database->connection()->builder(), $_this->container->Config);

		$sitemap->display();
	}

    /**
	 * Determine what template to use
	 *
	 * @access private
	 * @param  string $pageType The pagetype to use
	 * @return string|false
	 */
	private function getTemplate(string $pageType)
	{
		# Waterfall of pages
		$waterfall =  [];
		
		# Current theme dir
		$templateBase = $this->container->Config->get('cms.themes_path').'/'.$this->container->Config->get('cms.theme_name');

		# Explode request url
		$urlParts = array_filter(explode('/', trim($this->container->Request->environment()->REQUEST_URI, '/')));
		
		# 404s never get a template
		if ($this->container->Response->status()->get() === 404) return false;

		if ($pageType === 'home')
		{
			$waterfall[] = 'homepage';
			$waterfall[] = 'index';
		}
		else if ($pageType === 'home-page')
		{
			$waterfall[] = 'home-'.array_shift($urlParts);
			$waterfall[] = 'blog';
			$waterfall[] = 'index';
		}
		else if ($pageType === 'page')
		{
			$waterfall[] = 'page-'.array_shift($urlParts);
			$waterfall[] = 'page';
		}
		else if (Str::getBeforeFirstChar($pageType, '-') === 'single')
		{
			$waterfall[] = 'single-'.array_pop($urlParts);
			if ($this->container->Query->have_posts())
			{
				$waterfall[] = 'single-'.$this->container->Query->the_post_type();
			}
			$waterfall[] = 'single';
		}
		else if ($pageType === 'single')
		{
			$waterfall[] = 'single-'.array_pop($urlParts);
			$waterfall[] = 'single';
		}
		else if ($pageType === 'tag')
		{
			$waterfall[] = 'tag-'.$this->container->Query->the_taxonomy()->slug;
			$waterfall[] = 'taxonomy-tag';
			$waterfall[] = 'tag';
			$waterfall[] = 'taxonomy';
		}
		else if ($pageType === 'category1' || $pageType === 'category2' || $pageType === 'category3')
		{
			if ($this->container->Query->blog_location())
			{
				array_shift($urlParts);
			}
			array_shift($urlParts);

			if ($pageType === 'category1')
			{
				$waterfall[] = 'category-'.$urlParts[0];
			}
			else if ($pageType === 'category2')
			{
				$waterfall[] = 'category-'.$urlParts[1];
			}
			else if ($pageType === 'category3')
			{
				$waterfall[] = 'category-'.$urlParts[2];
			}

			$waterfall[] = 'taxonomy-category';
			$waterfall[] = 'category';
			$waterfall[] = 'taxonomy';
		}
		else if ($pageType === 'author')
		{
			$waterfall[] = 'author-'.$this->container->Query->the_taxonomy()->slug;
			$waterfall[] = 'taxonomy-author';
			$waterfall[] = 'author';
			$waterfall[] = 'taxonomy';
		}
		else if ($pageType === 'search')
		{
			$waterfall[] = 'search';
			$waterfall[] = 'index';
		}
		else if ($pageType === 'attachment')
		{
			$waterfall[] = 'attachment-'.array_pop($urlParts);
			$waterfall[] = 'attachment';
		}
		
		foreach ($waterfall as $name)
		{
			$template = "$templateBase/$name.php";
			
			if (file_exists($template))
			{
				return $template;
			}
		}

		if ($pageType === 'attachment')
		{
			$template = APP_DIR.'/views/attachment.php';

			if (file_exists($template))
			{
				return $template;
			}
		}

		if (file_exists("$templateBase/$pageType.php"))
		{
			return "$templateBase/$pageType.php";
		}

		return false;
	}
}
