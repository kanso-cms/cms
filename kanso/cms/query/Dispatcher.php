<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query;

use Closure;
use kanso\cms\rss\Feed;
use kanso\cms\sitemap\SiteMap;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\utility\Str;

/**
 * CMS Query Dispatcher
 *
 * @author Joe J. Howard
 */
class Dispatcher
{
	/**
	 * IoC container instance.
	 *
	 * @var \kanso\framework\ioc\Container
	 */
	private $container;

	/**
	 * Path to theme directory
	 *
	 * @var string
	 */
	private $themeDir;

	/**
	 * The page type being loaded
	 *
	 * @var string
	 */
	private $pageType;

	/**
     * Constructor.
     *
     * @access public
     * @param \kanso\framework\http\request\Request   $request   Framework Request instance
     * @param \kanso\framework\http\response\Response $response  Framework Response instance
     * @param \Closure                                $next      Next middleware layer
     * @param \kanso\framework\ioc\Container          $container IoC container
     * @param string|null                             $pageType  The page type being loaded
     */
    public function __construct(Request $request, Response $response, Closure $next, Container $container, string $pageType = '')
    {
    	$this->container = $container;

    	$this->themeDir = $this->container->Config->get('cms.themes_path') . '/' . $this->container->Config->get('cms.theme_name');

    	$this->pageType = $pagetype;
    }

    /**
     * Apply route to filter posts and load theme templates.
     *
     * @access public
     */
    public function applyRoute()
    {
		$this->container->Query->filterPosts($this->pageType);

		$template = $this->getTemplate();

		// Disable HTTP cache for non page/single/custom post types
		if ($this->pageType !== 'page' && $this->pageType !== 'single' && Str::getBeforeFirstChar($this->pageType, '-') !== 'single')
		{
			$this->container->Response->disableCaching();
		}

		if ($response->status()->get() !== 404 && $template)
		{
			$response->body()->set($response->view()->display($template));
		}
		else
		{
			$this->container->Query->reset();

			$next();
		}
    }

	/**
	 * Load an RSS feed.
	 *
	 * @access public
	 */
	public function loadRssFeed()
	{
		$this->container->Query->filterPosts($this->pageType);

		// If the status was set to 404 here by the query, stop rendering
		// Note this does not send a 404 straight away. If you have a custom route
		// and wanted to display a template, you could still change the the status/response
		// between now and when kanso sends a response.
		if ($response->status()->get() !== 404)
		{
			$format = array_filter(explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI)));

			// Load the RSS module and render
			$rss = new Feed($request, $response, array_pop($format));

			$rss->render();
		}
		else
		{
			$this->container->Query->reset();

			$next();
		}
	}

	/**
	 * Load and render the XML sitemap.
	 *
	 * @access public
	 */
	public function loadSiteMap()
	{
		// Current theme dir
		$template = $this->container->Config->get('cms.themes_path') . '/' . $this->container->Config->get('cms.theme_name') . '/sitemap.php';

		// Filter posts basic
		$this->container->Query->filterPosts('sitemap');

		if (file_exists($template))
		{
			$response->body()->set($response->view()->display($template));
		}
		else
		{
			$sitemap = new SiteMap(
				$request,
				$response,
				$this->container->Config->get('cms.route_tags'),
				$this->container->Config->get('cms.route_categories'),
				$this->container->Config->get('cms.route_authors'),
				$this->container->Config->get('cms.route_attachments'),
				$this->container->Config->get('cms.custom_posts')
			);

			$sitemap->display();
		}
	}

	/**
	 * Determine what template to use.
	 *
	 * @access private
	 */
	private function getTemplate()
	{
		// Waterfall of pages
		$waterfall =  [];

		// Explode request url
		$urlParts = array_filter(explode('/', Str::queryFilterUri($this->container->Request->environment()->REQUEST_URI)));

		// 404s never get a template
		if ($this->container->Response->status()->get() === 404)
		{
			return false;
		}

		if ($this->pageType === 'home')
		{
			$waterfall[] = 'homepage';
			$waterfall[] = 'index';
		}
		elseif ($this->pageType === 'home-page')
		{
			$waterfall[] = 'home-' . array_shift($urlParts);
			$waterfall[] = 'blog';
			$waterfall[] = 'index';
		}
		elseif ($this->pageType === 'page')
		{
			$waterfall[] = 'page-' . array_shift($urlParts);
			$waterfall[] = 'page';
		}
		elseif (Str::getBeforeFirstChar($this->pageType, '-') === 'single')
		{
			$waterfall[] = 'single-' . array_pop($urlParts);

			if ($this->container->Query->have_posts())
			{
				$waterfall[] = 'single-' . $this->container->Query->the_post_type();
			}

			$waterfall[] = 'single';
		}
		elseif ($this->pageType === 'single')
		{
			$waterfall[] = 'single-' . array_pop($urlParts);
			$waterfall[] = 'single';
		}
		elseif ($this->pageType === 'tag')
		{
			$waterfall[] = 'tag-' . $this->container->Query->the_taxonomy()->slug;
			$waterfall[] = 'taxonomy-tag';
			$waterfall[] = 'tag';
			$waterfall[] = 'taxonomy';
		}
		elseif ($this->pageType === 'category')
		{
			$waterfall[] = 'category-' . $this->container->Query->the_taxonomy()->slug;
			$waterfall[] = 'taxonomy-category';
			$waterfall[] = 'category';
			$waterfall[] = 'taxonomy';
		}
		elseif ($this->pageType === 'author')
		{
			$waterfall[] = 'author-' . $this->container->Query->the_taxonomy()->slug;
			$waterfall[] = 'taxonomy-author';
			$waterfall[] = 'author';
			$waterfall[] = 'taxonomy';
		}
		elseif ($this->pageType === 'search')
		{
			$waterfall[] = 'search';
			$waterfall[] = 'index';
		}
		elseif ($this->pageType === 'attachment')
		{
			$waterfall[] = 'attachment-' . array_pop($urlParts);
			$waterfall[] = 'attachment';
		}
		elseif ($this->pageType === 'products')
		{
			$waterfall[] = 'page-products';
			$waterfall[] = 'products';
			$waterfall[] = 'index';
		}

		foreach ($waterfall as $name)
		{
			$template = "{$this->themeDir}/$name.php";

			if (file_exists($template))
			{
				return $template;
			}
		}

		if ($this->pageType === 'attachment')
		{
			$template = APP_DIR . '/views/attachment.php';

			if (file_exists($template))
			{
				return $template;
			}
		}

		if (file_exists("{$this->themeDir}/$this->pageType.php"))
		{
			return "{$this->themeDir}/$this->pageType.php";
		}

		return false;
	}
}
