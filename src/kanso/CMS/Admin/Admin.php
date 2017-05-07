<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Admin;

use Kanso\Kanso;
use Kanso\Framework\Http\Route\Router;
use Kanso\Framework\Http\Request\Request;
use Kanso\Framework\Http\Response\Response;
use Kanso\Framework\Config\Config;
use Kanso\Framework\Utility\Str;
use Kanso\CMS\Event\Filters;
use Kanso\CMS\Event\Events;

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
     * @var \Kanso\Framework\Route\Router
     */
    protected $router;

    /**
     * Request instance
     *
     * @var \Kanso\Framework\Http\Request\Request
     */
    protected $request;

    /**
     * Response instance
     *
     * @var \Kanso\Framework\Http\Response\Response
     */
    protected $response;

    /**
     * Framework configuration
     *
     * @var \Kanso\Config\Config
     */
    protected $config;

    /**
     * CMS filters
     *
     * @var \Kanso\Framework\Event\Filters
     */
    private $filters;

    /**
     * CMS events
     *
     * @var \Kanso\Framework\Event\Events
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