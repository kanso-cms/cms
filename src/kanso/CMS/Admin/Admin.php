<?php

namespace Kanso\CMS\Admin;

/**
 * Admin class
 *
 * The admin class is used for public access to extend
 * the admin panel.
 */
class Admin
{

    /**
     * @var string
     */ 
    private $pageName;

    /**
     * @var boolean
     */ 
    private $isPost = false;

    /**
     * @var array
     */ 
    private $pageVars = [];

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        # Is this a POST request?
        $this->isPost = \Kanso\Kanso::getInstance()->Request->isPost() && $this->validatePOST();
    }

    /**
     * Create a new admin page
     *
     * @param string     $pageTitle     The name of the page on the sidebar
     * @param string     $icon          The icon of the sidebar menu item
     * @param string     $slug          The url slug for the page
     * @param string     $model         A string representation of a class model and function e.g 
     *                                  "\Namespace\Models\Admin@myPage"
     * @param array      $styles        Array of stylesheets to add to the admin panel's head (optional)
     * @param array      $styles        Array of scripts to add to the admin panel's body (optional)
     *
     */
    public function page($pageTitle, $icon, $slug, $model, $styles = [], $scripts = [])
    {
       
        # Filter the slug
        $slug = \Kanso\Utility\Str::slug($slug);

        # Filter the slug
        $menuName = \Kanso\Utility\Str::slug($pageTitle);

        # Add the sidebar menu
        $sbItem = [
            'link'     => "/admin/$slug/",
            'text'     => $pageTitle,
            'icon'     => $icon,
            'children' => [],
        ];
        \Kanso\Kanso::getInstance()->Filters->on('adminSidebarLinks', function($links) use($menuName, $sbItem) {
            $links[$menuName] = $sbItem;
            return $links;
        });
        
        # Add the scripts and styles
        \Kanso\Kanso::getInstance()->Events->on('adminInit', function($page) use($styles, $scripts) {
            if ($this->pageName === $page) {
                $this->addStyles($styles);
                $this->addScripts($scripts);
            }
        });

        # Filter the page title
        \Kanso\Kanso::getInstance()->Filters->on('adminPageTitle', function($title) use($pageTitle, $menuName) {
            
            if ($this->pageName === $menuName) {
                return "$pageTitle | Kanso";
            }
            else if ($this->pageName === 'custom') {
                $slug = trim(\Kanso\Kanso::getInstance()->Environment['REQUEST_URI'], '/');
                $slug = \Kanso\Utility\Str::getAfterLastChar($slug, '/');
                if ($slug === $menuName) {
                     return ucfirst($menuName)." | Kanso";
                }
            }
            return $title;
        });

        # Add the routes
        \Kanso\Kanso::getInstance()->get("/admin/$slug/",  '\Kanso\Admin\Controllers\Custom@dispatch', $model);
        \Kanso\Kanso::getInstance()->get("/admin/$slug/(:all)",  '\Kanso\Admin\Controllers\Custom@dispatch', $model); 
        \Kanso\Kanso::getInstance()->post("/admin/$slug/", '\Kanso\Admin\Controllers\Custom@dispatch', $model); 
        \Kanso\Kanso::getInstance()->post("/admin/$slug/(:all)", '\Kanso\Admin\Controllers\Custom@dispatch', $model); 
    }


    /********************************************************************************
    * ADD A NEW POST TYPE
    *******************************************************************************/

    /**
     * Create a new post type
     *
     * @param string     $name     The display name for the post type
     * @param string     $name     The value that will go into the database for the type column
     * @param string     $route    A valid Kanso route string eg:  '(:year)/(:month)/(:postname)/', 
     *
     */
    public function newPostType($name, $value, $icon, $route)
    {

        # Sanitize the value
        $value = \Kanso\Utility\Str::slug($value);

        # Add the route
        \Kanso\Kanso::getInstance()->Events->on('preDispatch', function($slug) use($value, $route) {

            \Kanso\Kanso::getInstance()->get($route, '\Kanso\Kanso::loadTemplate', 'single-'.$value);

        });

        # Filter add the post type
        \Kanso\Kanso::getInstance()->Filters->on('adminPostTypes', function($types) use($name, $value) {

            $types[$name] = $value;

            return $types;

        });

        # Add the sidebar link
        $this->page($name, $icon, $value, '\Kanso\Admin\Models\CustomPosts');       

        # Add the post types to Kanso's settings
        $permalink = str_replace([':', '(', ')'], '', $route);
        $permalink = trim($permalink, '/');

        # Settings don't get saved
        if (!isset(\Kanso\Kanso::getInstance()->Config['KANSO_CUSTOM_POSTS'])) {
            $customPosts = [$value => $permalink];
            \Kanso\Kanso::getInstance()->Settings->KANSO_CUSTOM_POSTS = $customPosts;
        }
        else {
            $customPosts = \Kanso\Kanso::getInstance()->Config['KANSO_CUSTOM_POSTS'];
            $customPosts[$value] = $permalink;
            \Kanso\Kanso::getInstance()->Settings->KANSO_CUSTOM_POSTS = $customPosts;
        }
    }

    /**
     * Add stylessheets to the admin panel on a custom page
     *
     * @param array      $_styles      Array of URLs to stylesheets
     *
     */
    private function addStyles($_styles)
    {
        if (!empty($_styles)) {
            \Kanso\Kanso::getInstance()->Filters->on('adminHeaderScripts', function($styles) {
                return array_merge($styles, $_styles);
            });
        }
    }

    /**
     * Add scripts to the admin panel on a custom page
     *
     * @param array      $_scripts      Array of URLs to scripts or any other HTML
     *
     */
    private function addScripts($_scripts)
    {
        if (!empty($_scripts)) {
            \Kanso\Kanso::getInstance()->Filters->on('adminFooterScripts', function($scripts) {
                return array_merge($scripts, $_scripts);
            });
        }
    }
}