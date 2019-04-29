<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\application;

use Closure;
use kanso\cms\rss\Feed;
use kanso\cms\sitemap\SiteMap;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\utility\Str;

/**
 * Container aware trait.
 *
 * @author Joe J. Howard
 */
trait LoaderTrait
{
	/**
	 * IoC container instance.
	 *
	 * @var \kanso\framework\ioc\Container
	 */
	protected $container;

	/**
	 * Instance of self.
	 *
	 * @var \kanso\cms\application\Application
	 */
	protected static $instance;

	abstract public static function instance();

    /**
     * Apply route to filter posts and load theme templates.
     *
     * @access public
     * @param \kanso\framework\http\request\Request   $request  Framework Request instance
     * @param \kanso\framework\http\response\Response $response Framework Response instance
     * @param \Closure                                $next     Next middleware layer
     * @param string                                  $pageType The page type being loaded
     */
    public static function applyRoute(Request $request, Response $response, Closure $next, string $pageType)
    {
    	$_this = static::instance();

		$_this->container->Query->filterPosts($pageType);

		$template = $_this->getTemplate($pageType);

		// Disable cache for non page/single/custom post types
		if ($pageType !== 'page' && $pageType !== 'single' && Str::getBeforeFirstChar($pageType, '-') !== 'single')
		{
			$_this->container->Response->cache()->disable();
		}

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
	 * Load an RSS feed.
	 *
	 * @access public
	 * @param \kanso\framework\http\request\Request   $request  Framework Request instance
	 * @param \kanso\framework\http\response\Response $response Framework Response instance
	 * @param \Closure                                $next     Next middleware layer
	 * @param string                                  $pageType The page type being loaded
	 */
	public static function loadRssFeed(Request $request, Response $response, Closure $next, string $pageType)
	{
		// Get the Kanso Object instance
		$_this = static::instance();

		$_this->container->Query->filterPosts($pageType);

		// If the status was set to 404 here by the query, stop rendering
		// Note this does not send a 404 straight away. If you have a custom route
		// and wanted to display a template, you could still change the the status/response
		// between now and when kanso sends a response.
		if ($response->status()->get() !== 404)
		{
			$format = array_filter(explode('/', Str::queryFilterUri($_this->container->Request->environment()->REQUEST_URI)));

			// Load the RSS module and render
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
	 * Load and render the XML sitemap.
	 *
	 * @access public
	 * @param \kanso\framework\http\request\Request   $request  Framework Request instance
	 * @param \kanso\framework\http\response\Response $response Framework Response instance
	 * @param \Closure                                $next     Next middleware layer
	 */
	public static function loadSiteMap(Request $request, Response $response, Closure $next)
	{
		// Get the Kanso Object instance
		$_this = static::instance();

		// Current theme dir
		$template = $_this->container->Config->get('cms.themes_path') . '/' . $_this->container->Config->get('cms.theme_name') . '/sitemap.php';

		// Filter posts basic
		$_this->container->Query->filterPosts('sitemap');

		if (file_exists($template))
		{
			$_this->container->Response->cache()->disable();

			$response->body()->set($response->view()->display($template));
		}
		else
		{
			$sitemap = new SiteMap(
				$request,
				$response,
				$_this->container->Config->get('cms.route_tags'),
				$_this->container->Config->get('cms.route_categories'),
				$_this->container->Config->get('cms.route_authors'),
				$_this->container->Config->get('cms.route_attachments'),
				$_this->container->Config->get('cms.custom_posts')
			);

			$sitemap->display();
		}
	}

	/**
	 * Handle 404 not found on for the CMS.
	 *
	 * @access protected
	 */
	protected function notFoundHandling()
	{
		// Disable logging 404s
		// $this->container->ErrorHandler->disableLoggingFor('kanso\framework\http\response\exceptions\NotFoundException');

		// 404 get displayed the theme 404 template
		$this->container->ErrorHandler->handle('\kanso\framework\http\response\exceptions\NotFoundException', function($exception)
		{
			// Only show the template if it exists, not ajax request and not displaying errors
			// Otherwise we fallback to applications default error handling
			$template = $this->container->Config->get('cms.themes_path') . DIRECTORY_SEPARATOR . $this->container->Config->get('cms.theme_name') . DIRECTORY_SEPARATOR . '404.php';

			if (file_exists($template) && !$this->container->Request->isAjax() && !$this->container->ErrorHandler->display_errors())
			{
				$this->container->Response->status()->set(404);

				$this->container->Response->cache()->disable();

				$this->container->Response->body()->set($this->container->View->display($template));

				$this->container->Response->send();

				// Stop handling this error
				// return false;
			}

		});
	}

	/**
	 * Determine what template to use.
	 *
	 * @access protected
	 * @param  string       $pageType The pagetype to use
	 * @return string|false
	 */
	protected function getTemplate(string $pageType)
	{
		// Waterfall of pages
		$waterfall =  [];

		// Current theme dir
		$templateBase = $this->container->Config->get('cms.themes_path') . '/' . $this->container->Config->get('cms.theme_name');

		// Explode request url
		$urlParts = array_filter(explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI)));

		// 404s never get a template
		if ($this->container->Response->status()->get() === 404)
		{
			return false;
		}

		if ($pageType === 'home')
		{
			$waterfall[] = 'homepage';
			$waterfall[] = 'index';
		}
		elseif ($pageType === 'home-page')
		{
			$waterfall[] = 'home-' . array_shift($urlParts);
			$waterfall[] = 'blog';
			$waterfall[] = 'index';
		}
		elseif ($pageType === 'page')
		{
			$waterfall[] = 'page-' . array_shift($urlParts);
			$waterfall[] = 'page';
		}
		elseif (Str::getBeforeFirstChar($pageType, '-') === 'single')
		{
			$waterfall[] = 'single-' . array_pop($urlParts);

			if ($this->container->Query->have_posts())
			{
				$waterfall[] = 'single-' . $this->container->Query->the_post_type();
			}

			$waterfall[] = 'single';
		}
		elseif ($pageType === 'single')
		{
			$waterfall[] = 'single-' . array_pop($urlParts);
			$waterfall[] = 'single';
		}
		elseif ($pageType === 'tag')
		{
			$waterfall[] = 'tag-' . $this->container->Query->the_taxonomy()->slug;
			$waterfall[] = 'taxonomy-tag';
			$waterfall[] = 'tag';
			$waterfall[] = 'taxonomy';
		}
		elseif ($pageType === 'category')
		{
			$waterfall[] = 'category-' . $this->container->Query->the_taxonomy()->slug;
			$waterfall[] = 'taxonomy-category';
			$waterfall[] = 'category';
			$waterfall[] = 'taxonomy';
		}
		elseif ($pageType === 'author')
		{
			$waterfall[] = 'author-' . $this->container->Query->the_taxonomy()->slug;
			$waterfall[] = 'taxonomy-author';
			$waterfall[] = 'author';
			$waterfall[] = 'taxonomy';
		}
		elseif ($pageType === 'search')
		{
			$waterfall[] = 'search';
			$waterfall[] = 'index';
		}
		elseif ($pageType === 'attachment')
		{
			$waterfall[] = 'attachment-' . array_pop($urlParts);
			$waterfall[] = 'attachment';
		}
		elseif ($pageType === 'products')
		{
			$waterfall[] = 'products';
			$waterfall[] = 'index';
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
			$template = APP_DIR . '/views/attachment.php';

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
