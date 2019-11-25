<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin;

use kanso\framework\ioc\ContainerAwareTrait;
use kanso\framework\utility\Arr;
use kanso\framework\utility\Humanizer;
use kanso\framework\utility\Str;

/**
 * Admin access.
 *
 * @author Joe J. Howard
 */
class Admin
{
    use ContainerAwareTrait;

    /**
     * Register a custom post type.
     *
     * @param string $title Custom post type title
     * @param string $type  Custom post type
     * @param string $icon  Icon to be used in admin panel sidebar
     * @param string $route Route for front end
     */
    public function registerPostType($title, $type, $icon, $route): void
    {
        // Sanitize the type
        $slug = Str::slug($type);

        // Sanitize the route
        $route = trim($route, '/');

        // Is this page being requested in the admin panel ?
        // Is this page being requested ?
        $requestSlug = Str::getAfterLastChar($this->Request->environment()->REQUEST_PATH, '/');
        $isPage      = $slug === $requestSlug;

        // Add the admin panel route
        $this->Router->get("/admin/{$slug}/", '\kanso\cms\admin\controllers\Dashboard@customPostType', '\kanso\cms\admin\models\Posts');
        $this->Router->get("/admin/{$slug}/(:all)", '\kanso\cms\admin\controllers\Dashboard@customPostType', '\kanso\cms\admin\models\Posts');
        $this->Router->post("/admin/{$slug}/", '\kanso\cms\admin\controllers\Dashboard@customPostType', '\kanso\cms\admin\models\Posts');
        $this->Router->post("/admin/{$slug}/(:all)", '\kanso\cms\admin\controllers\Dashboard@customPostType', '\kanso\cms\admin\models\Posts');

        // Add the front-end routes
        $this->Router->get('/' . $route . '/feed/rss/', '\kanso\cms\query\controllers\CustomPost@rss', ["single-{$slug}", 'kanso\cms\query\models\SingleCustom']);
        $this->Router->get('/' . $route . '/feed/atom/', '\kanso\cms\query\controllers\CustomPost@rss', ["single-{$slug}", 'kanso\cms\query\models\SingleCustom']);
        $this->Router->get('/' . $route . '/feed/rdf/', '\kanso\cms\query\controllers\CustomPost@rss', ["single-{$slug}", 'kanso\cms\query\models\SingleCustom']);
        $this->Router->get('/' . $route . '/feed/', '\kanso\cms\query\controllers\CustomPost@rss', ["single-{$slug}", 'kanso\cms\query\models\SingleCustom']);
        $this->Router->get('/' . $route, '\kanso\cms\query\controllers\CustomPost@load', ["single-{$slug}", 'kanso\cms\query\models\SingleCustom']);

        // Add the custom post type to the config
        // So that when the post is saved, the CMS knows what permalink structure to use
        $custom_types = $this->Config->get('cms.custom_posts');
        $custom_types[$type] = str_replace(['(', ':', ')'], '', $route);

        $this->Config->set('cms.custom_posts', $custom_types);

        // Add the menu to the sidebar
        $this->Filters->on('adminSidebar', function($sidebar) use ($title, $slug, $icon)
        {
            $sidebar['content']['children'] = Arr::insertAt($sidebar['content']['children'] ,
                ["$slug" =>
                    [
                        'link'     => '/admin/' . $slug . '/',
                        'text'     => Humanizer::pluralize(ucfirst($title)),
                        'icon'     => $icon,
                        'children' => [],
                    ],
                ],
            2);

            return $sidebar;
        });

        if ($isPage)
        {
            // Filter the page title
            $this->Filters->on('adminPageTitle', function($_title) use ($title, $isPage)
            {
                return Humanizer::pluralize(ucfirst($title)) . ' | Kanso';
            });
            // Add the custom post type to the model
            $this->Filters->on('adminCustomPostType', function() use ($isPage, $slug)
            {
                return $slug;
            });
            // Filter the request name
            $this->Filters->on('adminRequestName', function($requestName) use ($slug, $isPage)
            {
                return $slug;
            });
        }

        // Add the custom post type to the dropdown in
        // The admin panel
        $this->Filters->on('adminPostTypes', function($types) use ($title, $slug)
        {
            $types[$title] = $slug;

            return $types;
        });
    }

    /**
     * Adds a custom page to the Admin Panel.
     *
     * @param string      $title     The page title
     * @param string      $slug      The page slug
     * @param string      $icon      The icon in the sidebar to use
     * @param string      $model     The model to use for loading
     * @param string      $view      Absolute file path to include for page content
     * @param string|null $parent    Parent page slug (optional) (default null)
     * @param bool        $adminOnly Allow only administrators to use this page
     * @param array|null  $styles    Any custom styles to add into the page <head> (optional) (default null)
     * @param array|null  $scripts   Anything to go before the closing <body> tag (optional) (default null)
     */
    public function addPage(string $title, string $slug, string $icon, string $model, string $view, string $parent = null, bool $adminOnly = false, array $styles = null, array $scripts = null)
    {
        if ($this->Application->isCommandLine())
        {
            return false;
        }

        if (!$this->Gatekeeper->isLoggedIn() || !$this->Gatekeeper->getUser())
        {
            return false;
        }

        if ($this->Gatekeeper->getUser()->role !== 'administrator' && $adminOnly === true)
        {
            return false;
        }

        if ($parent)
        {
            $slug = $parent . '/' . $slug;
        }

        // Add the route only if the current user is logged as admin
        $this->Router->get("/admin/{$slug}/", '\kanso\cms\admin\controllers\Dashboard@blankPage', $model);
        $this->Router->get("/admin/{$slug}/(:all)", '\kanso\cms\admin\controllers\Dashboard@blankPage', $model);
        $this->Router->post("/admin/{$slug}/", '\kanso\cms\admin\controllers\Dashboard@blankPage', $model);
        $this->Router->post("/admin/{$slug}/(:all)", '\kanso\cms\admin\controllers\Dashboard@blankPage', $model);

        // If this is a child menu item is this page being requested ?
        if ($parent)
        {
            $requestSlug = explode('/', $this->Request->environment()->REQUEST_PATH);
            array_shift($requestSlug);
            $requestSlug = implode('/', $requestSlug);
            $isPage      = $slug === $requestSlug;
        }
        else
        {
            // Is this page being requested ?
            $requestSlug = Str::getAfterLastChar($this->Request->environment()->REQUEST_PATH, '/');
            $isPage      = $slug === $requestSlug;
        }

        // Add the menu to the sidebar
        $this->Filters->on('adminSidebar', function($sidebar) use ($title, $slug, $icon, $parent)
        {
            if ($parent)
            {
                foreach ($sidebar as $name => $item)
                {
                    if ($name === $parent)
                    {
                        $sidebar[$name]['children'][$slug] =
                        [
                            'link'     => '/admin/' . $slug . '/',
                            'text'     => $title,
                            'icon'     => $icon,
                        ];
                    }
                }

                return $sidebar;
            }

            return Arr::insertAt($sidebar,
                ["$slug" =>
                    [
                        'link'     => '/admin/' . $slug . '/',
                        'text'     => $title,
                        'icon'     => $icon,
                        'children' => [],
                    ],
                ],
            8);
        });

        // Filter the request name
        $this->Filters->on('adminRequestName', function($requestName) use ($slug, $isPage)
        {
            if ($isPage)
            {
                return $slug;
            }

            return $requestName;
        });

        // Filter the title
        $this->Filters->on('adminPageTitle', function($_title) use ($title, $isPage)
        {
            if ($isPage)
            {
                return ucfirst($title) . ' | Kanso';
            }

            return $_title;
        });

        // Filter the admin page to include
        $this->Filters->on('adminPageTemplate', function($requestName) use ($isPage, $view)
        {
            if ($isPage)
            {
                return $view;
            }

            return $requestName;
        });

        // Add stylesheets and JS scripts to admin panel
        if ($styles && $isPage)
        {
            $this->Filters->on('adminHeaderScripts', function($CSS) use ($styles)
            {
                $CSS = array_merge($CSS, $styles);

                return $CSS;
            });
        }

        // Add stylesheets and JS scripts to admin panel
        if ($scripts && $isPage)
        {
            $this->Filters->on('adminHeaderScripts', function($JS) use ($scripts)
            {
                $JS[] = array_merge($JS, $scripts);

                return $JS;
            });
        }
    }
}
