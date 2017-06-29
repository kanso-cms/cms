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
     */
    public function __construct()
    {
        $this->data = $this->extract();
    }

    /**
     * Reload the environment properties
     *
     * @access public
     * @return array
     */
    public function reload()
    {
        $this->data = $this->extract();
    }
   
    /**
     * Returns a fresh copy of the environment properties
     *
     * @access private
     * @return array
     */
    private function extract(): array
    {
        # Array of config variables
        $env = [];
 
        # The HTTP request method
        $env['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];

        # Script Name
        $scriptName         = isset($_SERVER['SCRIPT_NAME']) && !empty($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
        $env['SCRIPT_NAME'] = trim($scriptName, '/'); 

        # Name of server host that is running the script
        $env['SERVER_NAME'] = $_SERVER['SERVER_NAME'];

        # Number of server port that is running the script
        $env['SERVER_PORT'] = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;

        # Is the application running under HTTPS or HTTP protocol?
        if ( (isset($_SERVER['HTTPS']) && $env['SERVER_PORT'] === 443) && ($_SERVER['HTTPS'] === 1 || $_SERVER['HTTPS'] === 'on')) {
            $env['HTTP_PROTOCOL'] = 'https';
        }
        else {
            $env['HTTP_PROTOCOL'] = 'http';
        }

        # Document root
        $env['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

        # Http host
        $env['HTTP_HOST'] = $env['HTTP_PROTOCOL'].'://'.$_SERVER['HTTP_HOST'];

        # domain name
        $env['DOMAIN_NAME'] = str_replace('www.', '', str_replace($env['HTTP_PROTOCOL'].'://', '', $env['HTTP_HOST']));

        # Request uri
        $env['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

        # Request full URL
        $env['REQUEST_URL'] = $env['HTTP_HOST'].$env['REQUEST_URI'];

        # Query string (without leading "?")
        $queryString         = isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $env['QUERY_STRING'] = (strpos($queryString, '?') !== false) ? substr($queryString, strrpos($queryString, '?') + 1) : '';

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

        $env['REMOTE_ADDR'] = $ipaddress;

        return $env;
    }
}
