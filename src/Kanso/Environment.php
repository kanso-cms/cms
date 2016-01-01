<?php

namespace Kanso;

/**
 * Environment
 *
 * This class creates and returns a key/value array of common
 * environment variables for the current HTTP request.
 *
 * This is a singleton class; derived environment variables will
 * be common across multiple Kanso applications.
 *
 * This provides Kanso with a consistent set of "Globoal" 
 * variables that are used throughout the application.
 *
 * Kanso's Environment is used throughout the application
 * instead of PHP's superglobals like $_SERVER.
 *
 */
class Environment 
{

    /**
     * @var    array    Associative array of environment variables
     */
    protected static $properties = [];

    /**
     * Exctact the current Enviroment
     *
     * @return array    Associative array of environment variables
     */
    public static function extract()
    {
        # If the Environment has already been extracted 
        # return the current Environment - don't re-extract  
        if (!empty(self::$properties)) return self::$properties;
        
        # Array of config variables
        $env = [];
 
        # The HTTP request method
        $env['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];

        # Server params
        $scriptName = $_SERVER['SCRIPT_NAME']; // <-- "/foo/index.php"
        $requestUri = $_SERVER['REQUEST_URI']; // <-- "/foo/bar?test=abc" or "/foo/index.php/bar?test=abc"
        $queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''; // <-- "test=abc" or ""

        # Physical path
        if (strpos($requestUri, $scriptName) !== false) {
            $physicalPath = $scriptName; // <-- Without rewriting
        } else {
            $physicalPath = str_replace('\\', '', dirname($scriptName)); // <-- With rewriting
        }
        $env['SCRIPT_NAME'] = rtrim($physicalPath, '/'); // <-- Remove trailing slashes

        # Virtual path
        $env['PATH_INFO'] = $requestUri;
        if (substr($requestUri, 0, strlen($physicalPath)) == $physicalPath) {
            $env['PATH_INFO'] = substr($requestUri, strlen($physicalPath)); // <-- Remove physical path
        }
        $env['PATH_INFO']         = str_replace('?' . $queryString, '', $env['PATH_INFO']); // <-- Remove query string
        $env['PATH_INFO']         = '/' . ltrim($env['PATH_INFO'], '/'); // <-- Ensure leading slash

        # Query string (without leading "?")
        $env['QUERY_STRING']       = $queryString;

        # Name of server host that is running the script
        $env['SERVER_NAME']        = $_SERVER['SERVER_NAME'];

        # Number of server port that is running the script
        $env['SERVER_PORT']        = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;

        # Is the application running under HTTPS or HTTP protocol?
        $env['HTTP_PROTOCOL']      = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';

        # Server root
        $env['DOCUMENT_ROOT']      = $_SERVER['DOCUMENT_ROOT'];

        # Http host
        $env['HTTP_HOST']          = $env['HTTP_PROTOCOL'].'://'.$_SERVER['HTTP_HOST'];

        # Request uri
        $env['REQUEST_URI']        = $_SERVER['REQUEST_URI'];

        # Request URL
        $env['REQUEST_URL']        = $env['HTTP_HOST'].$env['REQUEST_URI'];

        # Kanso directory
        $env['KANSO_DIR']          = __DIR__;

        # Kanso theme directory
        $env["KANSO_THEME_DIR"]    = $env['KANSO_DIR'].DIRECTORY_SEPARATOR.'Themes';

        # Kanso uploads directory
        $env['KANSO_UPLOADS_DIR']  = $env['KANSO_DIR'].DIRECTORY_SEPARATOR.'Uploads';

        # Kanso admin directory
        $env['KANSO_ADMIN_DIR']    = $env['KANSO_DIR'].DIRECTORY_SEPARATOR.'Admin';

        # Kanso admin uri
        $env['KANSO_ADMIN_URI']    = $env['HTTP_HOST'].DIRECTORY_SEPARATOR.'admin';

        # Kanso website name
        $env['KANSO_WEBSITE_NAME'] = str_replace('www.', '', str_replace($env['HTTP_PROTOCOL'].'://', '', $env['HTTP_HOST']));

        # Kanso's image uploads url
        $env['KANSO_IMGS_URL']     = str_replace($env['DOCUMENT_ROOT'], $env['HTTP_HOST'], $env['KANSO_UPLOADS_DIR']).DIRECTORY_SEPARATOR.'Images/';

        # Save the clients IP address
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        $env['CLIENT_IP_ADDRESS'] = $ipaddress;

        # Save extracted properties
        self::$properties = $env;

        # Return the environment
        return $env;
    }
}