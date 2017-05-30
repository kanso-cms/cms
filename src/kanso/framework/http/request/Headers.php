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
    private $special = [
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'PHP_AUTH_USER',
        'PHP_AUTH_PW',
        'PHP_AUTH_DIGEST',
        'AUTH_TYPE'
    ];

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
     * Reload the headers
     *
     * @access public
     * @return array
     */
    public function reload()
    {
        $this->data = $this->extract();
    }

    /**
     * Returns a fresh copy of the headers
     *
     * @access private
     * @return array
     */
    private function extract(): array
    {
        $results = [];

        # Loop through the $_SERVER superglobal and save result consistently
        foreach ($_SERVER as $key => $value)
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
