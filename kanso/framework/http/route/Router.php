<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\route;

use kanso\framework\http\request\Request;
use kanso\framework\onion\Onion;
use kanso\framework\http\response\exceptions\MethodNotAllowedException;
use kanso\framework\http\response\exceptions\NotFoundException;
use kanso\framework\utility\Str;

/**
 * Application router
 *
 * @author Joe J. Howard
 */
class Router
{
    /**
     * Array of route strings
     *
     * @var array
     */
    private $routes = [];

    /**
     * Array of route request methods
     *
     * @var array
     */
    private $methods = [];

    /**
     * Array of route callbacks
     *
     * @var array
     */
    private $callbacks = [];

    /**
     * Array of route callback args
     *
     * @var array
     */
    private $callbackArgs = [];

    /**
     * Kanso Onion instance
     *
     * @var \kanso\framework\onion\Onion
     */
    private $onion;

    /**
     * Kanso Request instance
     *
     * @var \kanso\framework\http\request\Request
     */
    private $request;

    /**
     * Throw not found error if route not matched
     *
     * @var bool
     */
    private $throwNotFound;

    /**
     * Array of available regex patterns
     *
     * @var array
     */
    private $patterns = [
        ':any'      => '[^/]+',
        ':num'      => '[0-9]+',
        ':all'      => '.*',
        ':year'     => '\d{4}',
        ':month'    => '0[1-9]|1[012]',
        ':day'      => '0[1-9]|[12]\d|3[01]',
        ':hour'     => '0?[0-5]\d',
        ':minute'   => '0?[0-5]\d',
        ':second'   => '0?[0-5]\d',
        ':postname' => '[a-z0-9 -]+',
        ':category' => '.*',
        ':author'   => '[a-z0-9 -]+',
    ];

    /**
     * Constructor
     *
     * @access public
     * @param  \kanso\framework\http\request\Request $request       Request instance
     * @param  \kanso\framework\onion\Onion          $onion         Onion instance
     * @param  bool                                  $throwNotFound Throw not found error if route not matched (optional) (default true)
     */
    public function __construct(Request $request, Onion $onion, bool $throwNotFound = true)
    {
        $this->onion = $onion;

        $this->request = $request;

        $this->throwNotFound = $throwNotFound;
    }

    /**
     * Get current routes
     *
     */
    public function getRoutes(): array
    {
        $routes = [];

        foreach ($this->routes as $i => $uri)
        {
            $routes[] =
            [
                'uri'      => $uri,
                'method'   => $this->methods[$i],
                'callback' => $this->callbacks[$i],
                'args'     => $this->callbackArgs[$i],
            ];
        }

        return $routes;
    }

    /**
     * Add a http POST route
     *
     * @access public
     * @param  string $uri      URI to apply
     * @param  mixed  $callback Callback to apply
     * @param  mixed  $args     Args to add (optional) (default null)
     * @return \kanso\framework\http\route\Router
     */
    public function post(string $uri, $callback, $args = null): Router
    {
        return $this->map(Request::METHOD_POST, $uri, $callback, $args);
    }

    /**
     * Add a http GET route
     *
     * @access public
     * @param  string $uri      URI to apply
     * @param  mixed  $callback Callback to apply
     * @param  mixed  $args     Args to add (optional) (default null)
     * @return \kanso\framework\http\route\Router
     */
    public function get(string $uri, $callback, $args = null): Router
    {
        $this->head($uri, $callback, $args);
        
        return $this->map(Request::METHOD_GET, $uri, $callback, $args);
    }

    /**
     * Add a http HEAD route
     *
     * @access public
     * @param  string $uri      URI to apply
     * @param  mixed  $callback Callback to apply
     * @param  mixed  $args     Args to add (optional) (default null)
     * @return \kanso\framework\http\route\Router
     */
    public function head(string $uri, $callback, $args = null): Router
    {
        return $this->map(Request::METHOD_HEAD, $uri, $callback, $args);
    }

    /**
     * Add a http PUT route
     *
     * @access public
     * @param  string $uri      URI to apply
     * @param  mixed  $callback Callback to apply
     * @param  mixed  $args     Args to add (optional) (default null)
     * @return \kanso\framework\http\route\Router
     */
    public function put(string $uri, $callback, $args = null): Router
    {
        return $this->map(Request::METHOD_PUT, $uri, $callback, $args);
    }

    /**
     * Add a http PATCH route
     *
     * @access public
     * @param  string $uri      URI to apply
     * @param  mixed  $callback Callback to apply
     * @param  mixed  $args     Args to add (optional) (default null)
     * @return \kanso\framework\http\route\Router
     */
    public function patch(string $uri, $callback, $args = null): Router
    {
        return $this->map(Request::METHOD_PATCH, $uri, $callback, $args);
    }

    /**
     * Add a http DELETE route
     *
     * @access public
     * @param  string $uri      URI to apply
     * @param  mixed  $callback Callback to apply
     * @param  mixed  $args     Args to add (optional) (default null)
     * @return \kanso\framework\http\route\Router
     */
    public function delete(string $uri, $callback, $args = null): Router
    {
        return $this->map(Request::METHOD_DELETE, $uri, $callback, $args);
    }

    /**
     * Add a http OPTIONS route
     *
     * @access public
     * @param  string $uri      URI to apply
     * @param  mixed  $callback Callback to apply
     * @param  mixed  $args     Args to add (optional) (default null)
     * @return \kanso\framework\http\route\Router
     */
    public function options(string $uri, $callback, $args = null): Router
    {
        return $this->map(Request::METHOD_OPTIONS, $uri, $callback, $args);
    }

    /**
     * Map the route to internal arrays
     *
     * @access private
     * @param  string $method   http request method
     * @param  string $uri      URI to apply
     * @param  mixed  $callback Callback to apply
     * @param  mixed  $args     Args to add (optional) (default null)
     * @return \kanso\framework\http\route\Router
     */
    private function map(string $method, string $uri, $callback, $args = null): Router 
    {
        array_push($this->routes, trim($uri, '/'));
        
        array_push($this->methods, $method);
        
        array_push($this->callbacks, $callback);
        
        array_push($this->callbackArgs, $args);

        return $this;
    }

    /**
     * Loop the routes/methods to match request
     *
     * @access public
     */
    public function dispatch()
    {
        $requestMethod = $this->request->getMethod();

        $requestPath = Str::queryFilterUri($this->request->environment()->REQUEST_URI);

        $searches = array_keys($this->patterns);
        
        $replaces = array_values($this->patterns);

        $matched = false;

        $callbacks = [];

        $allowedMethod = '';

        # check if route is defined without regex
        if (in_array($requestPath, $this->routes))
        {
            $route_pos = array_keys($this->routes, $requestPath);
            
            foreach ($route_pos as $route)
            {
                # Found route
                $matched = true;

                $allowedMethod = $this->methods[$route];

                if ($this->methods[$route] == $requestMethod)
                {
                    # Push the callback into the stack
                    $callbacks[] = [$this->callbacks[$route], $this->callbackArgs[$route]];
                }
            }
        } 
        else {
            
            # check if defined with regex
            $pos = 0;

            foreach ($this->routes as $route)
            {
                if (strpos($route, ':') !== false)
                {
                    $route = str_replace($searches, $replaces, $route);
                }

                if (preg_match('#^' . $route . '$#', $requestPath, $matches))
                {
                    # Found route
                    $matched = true;

                    $allowedMethod = $this->methods[$pos];
                    
                    if ($this->methods[$pos] == $requestMethod)
                    {
                        # Push the callback into the stack
                        $callbacks[] = [$this->callbacks[$pos], $this->callbackArgs[$pos]];
                    }
                }
                
                $pos++;
            }
        }

        # We found a matching route but it does not allow the request method so we'll throw a 405 exception
        if ($matched && empty($callbacks))
        {
            throw new MethodNotAllowedException([$allowedMethod], 'Page requested over "'.$requestMethod.'". Only "'.$allowedMethod.'" is accepted.');
        }

        # No routes matched so we'll throw a 404 exception
        else if (!$matched)
        {
            if ($this->throwNotFound === true)
            {
                throw new NotFoundException($requestMethod . ': ' . $requestPath);
            }
        }

        # Loop the callbacks and add layer to onion
        else 
        {
            foreach ($callbacks as $callback)
            {
                $this->onion->addLayer($callback[0], $callback[1]);
            }
        }
    }
}
