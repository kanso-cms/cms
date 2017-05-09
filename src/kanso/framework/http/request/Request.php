<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\request;

use kanso\framework\http\request\Environment;
use kanso\framework\http\request\Headers;
use kanso\framework\utility\Mime;
use kanso\framework\utility\Str;

/**
 * Request manager class
 *
 * @author Joe J. Howard
 */
class Request
{
    /**
     * Request method constants
     *
     * @var string 
     */
    const METHOD_HEAD     = 'HEAD';
    const METHOD_GET      = 'GET';
    const METHOD_POST     = 'POST';
    const METHOD_PUT      = 'PUT';
    const METHOD_PATCH    = 'PATCH';
    const METHOD_DELETE   = 'DELETE';
    const METHOD_OPTIONS  = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';

    /**
     * Request headers
     *
     * @var \kanso\framework\http\request\Headers
     */
    private $headers;

    /**
     * Http Environment 
     *
     * @var \kanso\framework\http\request\Environment
     */
    private $environment;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct(Environment $environment, Headers $headers)
    {
        $this->environment = $environment;

        $this->headers = $headers;
    }

    /**
     * Trimmed request path
     *
     * @access public
     * @return string
     */
    public function path(): string
    {
        return parse_url(trim($this->environment->REQUEST_URI, '/'), PHP_URL_PATH);
    }

    /**
     * Environment access
     *
     * @access public
     * @return \kanso\framework\http\request\Environment
     */
    public function environment(): Environment
    {
        return $this->environment;
    }

    /**
     * Headers access
     *
     * @access public
     * @return \kanso\framework\http\request\Headers
     */
    public function headers(): Headers
    {
        return $this->headers;
    }

    /**
     * Returns the HTTP request method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($this->environment->REQUEST_METHOD);
    }

    /**
     * Is this a secure request ?
     *
     * @access public
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->environment->HTTP_HOST === 'https';
    }

    /**
     * Is this a GET request?
     *
     * @access public
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    /**
     * Is this a POST request?
     *
     * @access public
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    /**
     * Is this a PUT request?
     *
     * @access public
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Is this a PATCH request?
     *
     * @access public
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->getMethod() === self::METHOD_PATCH;
    }

    /**
     * Is this a DELETE request?
     *
     * @access public
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->getMethod() === self::METHOD_DELETE;
    }

    /**
     * Is this a HEAD request?
     *
     * @access public
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->getMethod() === self::METHOD_HEAD;
    }

    /**
     * Is this an OPTIONS request?
     *
     * @access public
     * @return bool
     */
    public function isOptions(): bool
    {
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

    /**
     * Is this an Ajax request?
     *
     * @access public
     * @return bool
     */
    public function isAjax(): bool
    {
        if (!$this->isPost())
        {
            return false;
        }

        $headers = $this->headers->asArray();

        if (isset($headers['REQUESTED_WITH']) && $headers['REQUESTED_WITH'] === 'XMLHttpRequest')
        {
            return true;
        } 
        else if (isset($headers['HTTP_REQUESTED_WITH']) &&  $headers['HTTP_REQUESTED_WITH'] === 'XMLHttpRequest')
        {
            return true;
        }
        else if (isset($headers['HTTP_X_REQUESTED_WITH']) &&  $headers['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
        {
            return true;
        }
        else if (isset($headers['X_REQUESTED_WITH']) &&  $headers['X_REQUESTED_WITH'] === 'XMLHttpRequest')
        {
            return true;
        }

        return false;
    }

    /**
     * Is this a GET request for file ?
     *
     * @access public
     * @return bool
     */
    public function isFileGet(): bool
    {
        if ($this->isGet())
        {
            if ($this->mimeType())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch GET and POST request data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, false is returned.
     *
     * @access public
     * @param  string $key (optional) (default null)
     * @return mixed
     */
    public function fetch(string $key = null)
    {
        $env = $this->environment->asArray();

        $data = parse_url(rtrim($env['HTTP_HOST'].$env['REQUEST_URI'], '/'));

        $data['page'] = 0;

        preg_match_all("/page\/(\d+)/", $env['REQUEST_URI'], $page);

        if (isset($page[1][0]) && !empty($page[1][0]))
        {
            $data['page'] = intval($page[1][0]);
        }
        
        if ($data['page'] === 1)
        {
            $data['page'] = 0;
        }

        if (!$this->isGet())
        {
            foreach ($_POST as $k => $v)
            {
                $data[$k] = $v;
            }
        }

        if ($key)
        {
            if (isset($data[$key]))
            {
                return $data[$key];
            }
            
            return false;
        }
        
        return $data;
    }

    /**
     * Fetch and parse url queries
     *
     * This method fetches and parses url queries
     * eg example.com?foo=bar -> ['foo' => 'bar'];
     *
     * @access public
     * @param  string $key (optional) (default null)
     * @return mixed
     */
    public function queries(string $_key = null)
    {
        $result   = [];

        $queryStr = $this->fetch('query');

        if (!empty($queryStr))
        {
            $querySets = explode('&', $queryStr);
            
            if (!empty($querySets))
            {
                foreach ($querySets as $querySet)
                {
                    if (Str::contains($querySet, '='))
                    {
                        $querySet = explode('=', $querySet);
                        $key      = urldecode($querySet[0]);
                        $value    = urldecode($querySet[1]);
                        
                        if (empty($value))
                        {
                            $value = null;
                        }
                        
                        $result[$key] = $value;   
                    }
                }
            }
        }
        if ($_key)
        {
            if (isset($result[$_key]))
            {
                return $result[$_key];
            }
            
            return null;
        }

        return $result;
    }

    /**
     * Get MIME Type (type/subtype within Content Type header)
     *
     * @access public
     * @return string|false
     */
    public function mimeType()
    {
        if (!headers_sent())
        {
            $pathinfo = $this->fetch();
            
            if (isset($pathinfo['path']))
            {
                return Mime::fromExt(Str::getAfterLastChar($pathinfo['path'], '.'));
            }
        }

        return false;
    }

}
