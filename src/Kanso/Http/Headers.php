<?php

namespace Kanso\Http;

/**
 * HTTP Headers
 *
 * This class is used to extract the request headers sent from HTTP the client.
 *
 * When extracted, the class will parse all HTTP request headers and store them 
 * in Kanso. These are later used for HTTP response.
 */
class Headers
{

    /**
     * @var    array    Associative array of Header variables
     */
    protected static $properties = [];

    /**
     * Special-case HTTP headers that are otherwise unidentifiable as HTTP headers.
     * Typically, HTTP headers in the $_SERVER array will be prefixed with
     * `HTTP_` or `X_`. These are not so we list them here for later reference.
     *
     * @var array
     */
    protected static $special = [
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE'
    ];

    /**
     * Extract HTTP headers from an array of data (e.g. $_SERVER)
     * 
     * @return array
     */
    public static function extract()
    {

        # If the headers have already been extracted 
        # return the current headers - don't re-extract  
        if (!empty(self::$properties)) return self::$properties;

        $data    = $_SERVER;
        $results = [];

        # Loop through the $_SERVER superglobal and save result consistently
        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || [$key, static::$special]) {
                if ($key === 'HTTP_CONTENT_LENGTH') {
                    continue;
                }
                $results[$key] = $value;
            }
        }

        # Save extracted properties
        self::$properties = $results;

        # Return the headers
        return $results;
    }

}