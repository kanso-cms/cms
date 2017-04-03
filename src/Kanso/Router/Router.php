<?php

namespace Kanso\Router;

/**
 * Router
 *
 * This class gets the current request and loops through routes and calls
 * the appropriate callback. If a route isnt found, an error callback (which
 * must be provided) is called.
 *
 * Note that the router is capable of calling objects, with a parameter as 
 * callback both statically and through object initialization.
 * However, it's best to use a static class as callback
 * or callback as string, so the router's callbacks list doesn't get bloated.
 *
 * The router can be accessed 3 ways
 * $Kanso->Router
 * $Kanso->Router()
 * \Kanso\Router\Router
 * $Kanso->get()
 *
 * @example $Kanso->get('/routeurl/', '\Namesapce\Class::staticMethod', 'parameter');
 * @example $Kanso->get('/routeurl/', '\Namesapce\Class@publicMethod', 'parameter');
 * @example \Kanso\Router\Router::call('GET', '/routeurl/', '\Namesapce\Class@publicMethod', 'parameter');
 * @example \Kanso\Router\Router::call('GET', '/routeurl/', '\Namesapce\Class@publicMethod', 'parameter');
 * @example $Kanso->Router->call('GET', '/routeurl/', '\Namesapce\Class@publicMethod', 'parameter');
 * @example \Kanso\Router\Router::call('GET', '/routeurl/', $callback, 'parameter');
 * @example \Kanso\Router\Router::call('GET', /', function() { echo 'I <3 GET commands!'; });
 * @example \Kanso\Router\Router::error(function() {echo '404 :: Not Found';});
 * 
 * The router can also use a number of regex methods and wildcards :
 * (:all) (:any) (:year) (:month) (:day) (:hour) (:minute) (:postname) (:category) (:author)
 *
 */
class Router
{
    /**
     * @var boolean Should the router stop when a match is found
     */
    public static $halts = true;

    /**
     * @var array Array of routes (URIs)
     */
    public static $routes = [];

    /**
     * @var array Array of request methods
     */
    public static $methods = [];

    /**
     * @var mixed Array of callbacks
     */
    public static $callbacks = [];

    /**
     * @var mixed Array of callback arguements
     */
    public static $callbackArgs = [];

    /**
     * @var mixed Array of error callback and arguements
     */
    public static $error_callback;

    /**
     * @var array Array of regex keys and values to be used
     */
    public static $patterns = [
        ':any'      => '[^/]+',
        ':num'      => '[0-9]+',
        ':all'      => '.*',
        ':year'     => '\d{4}',
        ':month'    => '0?[1-9]|1[012]',
        ':day'      => '0[1-9]|[12]\d|3[01]',
        ':hour'     => '0?[1-9]|1[012]',
        ':minute'   => '[0-5]?\d',
        ':second'   => '[0-5]?\d',
        ':postname' => '[a-z0-9 -]+',
        ':category' => '[a-z0-9 -]+',
        ':author'   => '[a-z0-9 -]+',
    ];

    /**
     * Define a route w/ method, callback and args
     *
     * @param   string $method     The type of request method
     * @param   string $uri        The route uri
     * @param   mixed  $callback   Callable route function
     * @param   array  $args       Array of arguements to be applied to callback (optional)
     */
    public static function call($method, $uri, $callback, $args = null) 
    {
        array_push(self::$routes, trim($uri, '/'));
        array_push(self::$methods, strtoupper($method));
        array_push(self::$callbacks, $callback);
        array_push(self::$callbackArgs, $args);
    }

    /**
     * Define a callback with args if a route is not found
     *
     * @param   mixed  $callback   Callable route function
     * @param   array  $args       Array of arguments to be applied to callback (optional)
     */
    public static function error($callback, $args = null)
    {
        self::$error_callback = [$callback, $args];
    }
    
    /**
     * Tell the router to halt on match or continue
     * @param   boolean  $flag   Callable route function
     */
    public static function haltOnMatch($flag = true)
    {
        self::$halts = $flag;
    }

    /**
     * Loop the routes/methods to match request
     */
    public static function dispatch()
    {
        $uri    = parse_url(trim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];  

        $searches = array_keys(static::$patterns);
        $replaces = array_values(static::$patterns);

        $found_route = false;

        # check if route is defined without regex
        if (in_array($uri, self::$routes)) {
            $route_pos = array_keys(self::$routes, $uri);
            foreach ($route_pos as $route) {

                if (self::$methods[$route] == $method) {
                    
                    # Found route
                    $found_route = true;
                    
                    # Apply the callback
                    \Kanso\Utility\Callback::apply(self::$callbacks[$route], self::$callbackArgs[$route]);
                    
                    # Halt on match ?
                    if (self::$halts) return;
                }
            }
        } 
        else {
            # check if defined with regex
            $pos = 0;
            foreach (self::$routes as $route) {

                if (strpos($route, ':') !== false) {
                    $route = str_replace($searches, $replaces, $route);
                }

                if (preg_match('#^' . $route . '$#', $uri, $matched)) {
                    
                    if (self::$methods[$pos] == $method) {
                        
                        # Found route
                        $found_route = true;

                        # remove $matched[0] as [1] is the first parameter.
                        array_shift($matched);

                        # Apply the callback
                        \Kanso\Utility\Callback::apply(self::$callbacks[$pos],  self::$callbackArgs[$pos]);
                        
                        # Halt on match ?
                        if (self::$halts) return;

                    }
                }
            $pos++;
            }
        }

        # run the error callback if the route was not found
        if ($found_route === false) {
            \Kanso\Kanso::getInstance()->notFound();
        }
    }
    
}