<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\admin;

use kanso\Kanso;
use kanso\framework\http\route\Router;
use kanso\framework\http\request\Request;
use kanso\framework\http\response\Response;
use kanso\framework\config\Config;
use kanso\framework\utility\Str;
use kanso\cms\event\Filters;
use kanso\cms\event\Events;

/**
 * Admin panel access
 *
 * @author Joe J. Howard
 */
class Admin
{
    /**
     * Router instance
     *
     * @var \kanso\framework\route\Router
     */
    protected $router;

    /**
     * Request instance
     *
     * @var \kanso\framework\http\request\Request
     */
    protected $request;

    /**
     * Response instance
     *
     * @var \kanso\framework\http\response\Response
     */
    protected $response;

    /**
     * Framework configuration
     *
     * @var \kanso\config\Config
     */
    protected $config;

    /**
     * CMS filters
     *
     * @var \kanso\framework\event\Filters
     */
    private $filters;

    /**
     * CMS events
     *
     * @var \kanso\framework\event\Events
     */
    private $events;

    /**
     * Admin panel page name
     *
     * @var string
     */
    private $pageName;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct(Router $router, Request $request, Response $response, Config $config, Filters $filters, Events $events)
    {
        $this->router = $router;

        $this->request = $request;

        $this->response = $response;

        $this->config = $config;

        $this->filters = $filters;

        $this->events = $events;

        # Add the scripts and styles
        $this->events->on('adminInit', function($page)
        {
            $this->pageName = $page;            
        });

    }
}