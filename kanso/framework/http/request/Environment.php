<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\request;

use kanso\framework\common\MagicArrayAccessTrait;

/**
 * Environment aware class
 *
 * @author Joe J. Howard
 */
class Environment 
{
    use MagicArrayAccessTrait;

    /**
     * Constructor. Loads the properties internally
     *
     * @access public
     * @param  array  $server Optional server overrides (optional) (default [])
     */
    public function __construct(array $server = [])
    {
        $this->data = $this->extract($server);
    }

    /**
     * Reload the environment properties
     *
     * @access public
     * @param  array  $server Optional server overrides (optional) (default [])
     */
    public function reload(array $server = [])
    {
        $this->data = $this->extract($server);
    }
   
    /**
     * Returns a fresh copy of the environment properties
     *
     * @access private
     * @return array
     */
    private function extract(array $server): array
    {
        $server = empty($server) ? $_SERVER : $server;

        # Array of config variables
        $env = [];
 
        # The HTTP request method
        $env['REQUEST_METHOD'] = !isset($server['REQUEST_METHOD']) ? 'CLI' : $server['REQUEST_METHOD'];

        # Script Name
        $scriptName  = isset($server['SCRIPT_NAME']) && !empty($server['SCRIPT_NAME']) ? $server['SCRIPT_NAME'] : substr($server['PHP_SELF'], strrpos($server['PHP_SELF'], '/') + 1);
        $scriptName  = explode('/', trim($scriptName, '/'));
        $env['SCRIPT_NAME'] = array_pop($scriptName);

        # Name of server host that is running the script
        $env['SERVER_NAME'] = $server['SERVER_NAME'];

        # Number of server port that is running the script
        $env['SERVER_PORT'] = isset($server['SERVER_PORT']) ? intval($server['SERVER_PORT']) : 80;

        # Is the application running under HTTPS or HTTP protocol?
        if ( (isset($server['HTTPS']) && $env['SERVER_PORT'] === 443) && ($server['HTTPS'] === 1 || $server['HTTPS'] === 'on') )
        {
            $env['HTTP_PROTOCOL'] = 'https';
        }
        else
        {
            $env['HTTP_PROTOCOL'] = 'http';
        }

        # Document root
        $env['DOCUMENT_ROOT'] = $server['DOCUMENT_ROOT'];

        # Http host
        $env['HTTP_HOST'] = $env['HTTP_PROTOCOL'].'://'.str_replace(['http://', 'https://'], ['', ''], $server['HTTP_HOST']);

        # domain name
        $env['DOMAIN_NAME'] = str_replace('www.', '', str_replace($env['HTTP_PROTOCOL'].'://', '', $env['HTTP_HOST']));

        # Request uri
        $env['REQUEST_URI'] = $server['REQUEST_URI'];

        # Request full URL
        $env['REQUEST_URL'] = $env['HTTP_HOST'].$env['REQUEST_URI'];

        # Query string (without leading "?")
        $queryString         = isset($server['REQUEST_URI']) && !empty($server['REQUEST_URI']) ? $server['REQUEST_URI'] : '';
        $env['QUERY_STRING'] = (strpos($queryString, '?') !== false) ? substr($queryString, strrpos($queryString, '?') + 1) : '';

        # Save the clients IP address
        if (isset($server['HTTP_CLIENT_IP']))
        {
            $ipaddress = $server['HTTP_CLIENT_IP'];
        }
        else if (isset($server['HTTP_X_FORWARDED_FOR']))
        {
            $ipaddress = $server['HTTP_X_FORWARDED_FOR'];
        }
        else if (isset($server['HTTP_X_FORWARDED']))
        {
            $ipaddress = $server['HTTP_X_FORWARDED'];
        }
        else if (isset($server['HTTP_FORWARDED_FOR']))
        {
            $ipaddress = $server['HTTP_FORWARDED_FOR'];
        }
        else if (isset($server['HTTP_FORWARDED']))
        {
            $ipaddress = $server['HTTP_FORWARDED'];
        }
        else if (isset($server['REMOTE_ADDR']))
        {
            $ipaddress = $server['REMOTE_ADDR'];
        }
        else
        {
            $ipaddress = 'UNKNOWN';
        }

        $env['REMOTE_ADDR'] = $ipaddress;

        # Save the browser user agent
        $env['HTTP_USER_AGENT'] = isset($server['HTTP_USER_AGENT']) ? $server['HTTP_USER_AGENT'] : 'UNKNOWN';
        
        return $env;
    }
}
