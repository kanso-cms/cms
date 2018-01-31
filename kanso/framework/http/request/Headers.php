<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\request;

use kanso\framework\common\MagicArrayAccessTrait;

/**
 * Request headers class
 *
 * @author Joe J. Howard
 */
class Headers 
{
    use MagicArrayAccessTrait;

    /**
     * Special-case HTTP headers that are otherwise unidentifiable as HTTP headers.
     * Typically, HTTP headers in the $_SERVER array will be prefixed with
     * `HTTP_` or `X_`. These are not so we list them here for later reference.
     *
     * @var array
     */
    private $special =
    [
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE',
        'X-PJAX'
    ];

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
     * Reload the headers
     *
     * @access public
     * @param  array  $server Optional server overrides (optional) (default [])
     */
    public function reload(array $server = [])
    {
        $this->data = $this->extract($server);
    }

    /**
     * Returns a fresh copy of the headers
     *
     * @access private
     * @param  array  $server Optional server overrides (optional) (default [])
     * @return array
     */
    private function extract($server): array
    {
        $results = [];

        $server = empty($server) ? $_SERVER : $server;

        # Loop through the $_SERVER superglobal and save result consistently
        foreach ($server as $key => $value)
        {
            $key = strtoupper($key);
            
            if (strpos($key, 'X_') === 0 || strpos($key, 'HTTP_') === 0 || [$key, $this->special])
            {
                if ($key === 'HTTP_CONTENT_LENGTH')
                {
                    continue;
                }
                
                $results[$key] = $value;
            }
        }
        return $results;
    }   
}
