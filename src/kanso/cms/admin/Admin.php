<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin;

use kanso\Kanso;
use kanso\framework\ioc\ContainerAwareTrait;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Str;
use kanso\framework\utility\Humanizer;

/**
 * Admin access
 *
 * @author Joe J. Howard
 */
class Admin
{
    use ContainerAwareTrait;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->loadContainer();
    }

    /**
     * Loads the container into the container aware trait
     *
     * @access private
     */
    private function loadContainer()
    {
        $this->setContainer(Kanso::instance()->container());
    }

    /**
     * Register a custom post type
     *
     * @access public
     */
    public function registerPostType($title, $type, $icon, $route)
    {
        # Sanitize the type
        $slug = Str::slug($type);

        # Sanitize the route
        $route = trim($route, '/');

        # Is this page being requested in the admin panel ?
        # Is this page being requested ?
        $requestSlug = Str::getAfterLastChar(trim(Str::getBeforeFirstChar($this->Request->environment()->REQUEST_URI, '?'), '/'), '/');
        $isPage      = $slug === $requestSlug;

        # Add the admin panel route
        $this->Router->get("/admin/$slug/", '\kanso\cms\admin\controllers\Dashboard@customPostType', '\kanso\cms\admin\models\Posts');
        $this->Router->get("/admin/$slug/(:all)", '\kanso\cms\admin\controllers\Dashboard@customPostType','\kanso\cms\admin\models\Posts');
        $this->Router->post("/admin/$slug/", '\kanso\cms\admin\controllers\Dashboard@customPostType', '\kanso\cms\admin\models\Posts');
        $this->Router->post("/admin/$slug/(:all)", '\kanso\cms\admin\controllers\Dashboard@customPostType', '\kanso\cms\admin\models\Posts');

        # Add the front-end routes
        $this->Router->get('/'.$route,              '\kanso\cms\application\Application::applyRoute',  'single-'.$slug);
        $this->Router->get('/'.$route.'feed/',      '\kanso\cms\application\Application::loadRssFeed', 'single-'.$slug);
        $this->Router->get('/'.$route.'feed/rss/',  '\kanso\cms\application\Application::loadRssFeed', 'single-'.$slug);
        $this->Router->get('/'.$route.'feed/atom/', '\kanso\cms\application\Application::loadRssFeed', 'single-'.$slug);
        $this->Router->get('/'.$route.'feed/rdf/',  '\kanso\cms\application\Application::loadRssFeed', 'single-'.$slug);

        # Add the custom post type to the config
        # So that when the post is saved, the CMS knows what permalink structure to use
        $custom_types   = $this->Config->get('cms.custom_posts');
        $custom_posts[$type] = str_replace(['(', ':', ')'], '', $route);

        $this->Config->set('cms.custom_posts', $custom_posts);

        # Add the menu to the sidebar
        $this->Filters->on('adminSidebar', function($sidebar) use($title, $slug, $icon)
        {
            return Arr::insertAt($sidebar, 
                [ "$slug" => 
                    [
                        'link'     => '/admin/'.$slug.'/',
                        'text'     => $title,
                        'icon'     => $icon,
                        'children' => [],
                    ]
                ],
            4);
        });

        if ($isPage)
        {
            # Filter the page title
            $this->Filters->on('adminPageTitle', function($_title) use($title, $isPage)
            {
                return Humanizer::pluralize(ucfirst($title)).' | Kanso';
            });
            # Add the custom post type to the model
            $this->Filters->on('adminCustomPostType', function() use($isPage, $slug)
            {
                return $slug;
            });
            $this->Filters->on('adminPageTitle', function($_title) use($title, $isPage)
            {
                return Humanizer::pluralize(ucfirst($title)).' | Kanso';
            });
            $this->Filters->on('adminCustomPostType', function() use($isPage, $slug)
            {
                return $slug;
            });
            # Filter the request name
            $this->Filters->on('adminRequestName', function($requestName) use($slug, $isPage)
            {
                return $slug;
            });
        }

        # Add the custom post type to the dropdown in 
        # The admin panel
        $this->Filters->on('adminPostTypes', function($types) use($title, $slug)
        {
            $types[$title] = $slug;

            return $types;
        });
    }
    
    /**
     * Adds a custom page to the Admin Panel
     *
     * @access public
     * @param  string $title   The page title
     * @param  string $slug    The page slug
     * @param  string $icon    The icon in the sidebar to use
     * @param  string $model   The model to use for loading
     * @param  string $view    Absolute file path to include for page content
     * @param  array  $styles  Any custom styles to add into the page <head> (optional) (default null)
     * @param  array  $scripts Anything to go before the closing <body> tag (optional) (default null)
     */
    public function addPage(string $title, string $slug, string $icon, string $model, string $view, array $styles = null, array $scripts = null)
    {
        # Add the route
        $this->Router->get("/admin/$slug/", '\kanso\cms\admin\controllers\Dashboard@blankPage', $model);
        $this->Router->get("/admin/$slug/(:all)", '\kanso\cms\admin\controllers\Dashboard@blankPage', $model);
        $this->Router->post("/admin/$slug/", '\kanso\cms\admin\controllers\Dashboard@blankPage', $model);
        $this->Router->post("/admin/$slug/(:all)", '\kanso\cms\admin\controllers\Dashboard@blankPage', $model);

        # Is this page being requested ?
        $requestSlug = Str::getAfterLastChar(trim(Str::getBeforeFirstChar($this->Request->environment()->REQUEST_URI, '?'), '/'), '/');
        $isPage      = $slug === $requestSlug;

        # Add the menu to the sidebar
        $this->Filters->on('adminSidebar', function($sidebar) use($title, $slug, $icon)
        {
            return Arr::insertAt($sidebar, 
                [ "$slug" => 
                    [
                        'link'     => '/admin/'.$slug.'/',
                        'text'     => $title,
                        'icon'     => $icon,
                        'children' => [],
                    ]
                ],
            8);
        });

        # Filter the request name
        $this->Filters->on('adminRequestName', function($requestName) use($slug, $isPage)
        {
            if ($isPage)
            {
                return $slug;
            }

            return $requestName;
        });

        # Filter the title
        $this->Filters->on('adminPageTitle', function($_title) use($title, $isPage)
        {
            if ($isPage)
            {
                return ucfirst($title).' | Kanso';
            }

            return $_title;
        });

        # Filter the admin page to include
        $this->Filters->on('adminPageTemplate', function($requestName) use($isPage, $view)
        {
            if ($isPage)
            {
                return $view;
            }

            return $requestName;
        });

        # Add stylesheets and JS scripts to admin panel
        if ($styles)
        {
            $this->Filters->on('adminHeaderScripts', function($CSS) use($styles)
            {
                $CSS[] = $styles;

                return $CSS;
            });
        }

        # Add stylesheets and JS scripts to admin panel
        if ($scripts)
        {
            $this->Filters->on('adminHeaderScripts', function($JS) use($scripts)
            {
                $JS[] = $scripts;

                return $JS;
            });
        }
    }

}