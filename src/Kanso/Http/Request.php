<?php

namespace Kanso\Http;

/**
 * Kanso HTTP Request
 *
 * This class provides a human-friendly interface of helper functions
 * to help validate/hande HTTP requests to the server
 *
 * This can be used for validating ajax requests, GET requests, 
 * POST requests or validating file requests.
 *
 * @property array \Kanso\Environment::extract()  $Environment
 * @property array \Kanso\Http\Header::extract()  $Headers
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
     * Constructor
     *
     */
    public function __construct()
    {
        
    }

    /**
     * Get HTTP method
     * @return string
     */
    public function getMethod()
    {
        return \Kanso\Kanso::getInstance()->Environment()['REQUEST_METHOD'];
    }

    /**
     * Is this a GET request?
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    /**
     * Is this a POST request?
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    /**
     * Is this a PUT request?
     * @return bool
     */
    public function isPut()
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Is this a PATCH request?
     * @return bool
     */
    public function isPatch()
    {
        return $this->getMethod() === self::METHOD_PATCH;
    }

    /**
     * Is this a DELETE request?
     * @return bool
     */
    public function isDelete()
    {
        return $this->getMethod() === self::METHOD_DELETE;
    }

    /**
     * Is this a HEAD request?
     * @return bool
     */
    public function isHead()
    {
        return $this->getMethod() === self::METHOD_HEAD;
    }

    /**
     * Is this an OPTIONS request?
     * @return bool
     */
    public function isOptions()
    {
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

    /**
     * Is this an Ajax request?
     * @return bool
     */
    public function isAjax()
    {
        if (!$this->isPost()) return false;
        $headers = \Kanso\Kanso::getInstance()->Headers();
        if (isset($headers['REQUESTED_WITH']) && $headers['REQUESTED_WITH'] === 'XMLHttpRequest' ) return true;
        if (isset($headers['HTTP_REQUESTED_WITH']) && $headers['HTTP_REQUESTED_WITH'] === 'XMLHttpRequest' ) return true;
        if (isset($headers['HTTP_X_REQUESTED_WITH']) && $headers['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ) return true;
        if (isset($headers['X_REQUESTED_WITH']) && $headers['X_REQUESTED_WITH'] === 'XMLHttpRequest' ) return true;
        return false;
    }

    /**
     * Is this a GET request for file?
     * @return bool
     */
    public function isFileGet()
    {
        if ($this->mimeType()) return true;
        return false;
    }

    /**
     * Fetch GET and POST request data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, false is returned,
     * unless there is a default value specified.
     *
     * @param  string            $key (optional)
     * @return array|mixed|false
     */
    public function fetch($key = null)
    {
        $env = \Kanso\Kanso::getInstance()->Environment();
        if (!$this->isGet()) {
            if ($key) {
                if (isset($_POST[$key])) return $_POST[$key];
                return false;
            }
            return array_merge($_POST, $_FILES);
        }
        else {
            $GETinfo  = array_merge(parse_url(rtrim($env['HTTP_HOST'].$env['REQUEST_URI'], '/')), pathinfo(rtrim($env['HTTP_HOST'].$env['REQUEST_URI'], '/')) );
            $GETinfo['page'] = 0;
            preg_match_all("/page\/(\d+)/", $env['REQUEST_URI'], $page);
            if (isset($page[1][0]) && !empty($page[1][0])) $GETinfo['page'] = (int) $page[1][0];
            if ($GETinfo['page'] === 1) $GETinfo['page'] = 0;
            if ($key) {
                if (isset($GETinfo[$key])) return $GETinfo[$key];
                return false;
            }
            return $GETinfo;
        }

        return false;
    }

    /**
     * Fetch and parse url queries
     *
     * This method fetches and parses url queries
     * eg example.com?foo=bar -> ['foo' => 'bar'];
     *
     * @return array
     */
    public function queries($_key = false)
    {
        $result   = [];
        $queryStr = $this->fetch('query');
        if (!empty($queryStr)) {
            $querySets = explode('&', $queryStr);
            if (!empty($querySets)) {
                foreach ($querySets as $querySet) {
                    if (\Kanso\Utility\Str::contains($querySet, '=')) {
                        $querySet = explode('=', $querySet);
                        $key      = urldecode($querySet[0]);
                        $value    = urldecode($querySet[1]);
                        if (empty($value)) $value = false;
                        $result[$key] = $value;   
                    }
                }
            }
        }
        if ($_key) {
            if (isset($result[$_key])) return $result[$_key];
            return null;
        }
        return $result;
    }

    /**
     * Get MIME Type (type/subtype within Content Type header)
     *
     * @return string|false
     */
    public function mimeType()
    {
        if (!headers_sent()) {
            $pathinfo = $this->fetch();
            if (isset($pathinfo['path'])) {
                return \Kanso\Utility\Mime::fromExt(\Kanso\Utility\Str::getAfterLastChar($pathinfo['path'], '.'));
            }
        }
        return false;
    }

}
