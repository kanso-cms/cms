<?php

namespace Kanso\Http;

/**
 * Response
 *
 * This class a simple abstraction over top an HTTP response. This
 * provides methods to set the HTTP status, the HTTP headers,
 * and the HTTP body.
 *
 * The Response object provides helper methods, that can be used interact 
 * with HTTP response properties i.e - The response that gets sent to client
 * The default response object will return a 200 OK HTTP response with the text/html content type.
 *
 * Note that this class is repsonsible for outputting all content and headers to client
 * from Kanso
 *
 * Some of the method are will return the Response object, so that methods are chainable
 * e.g $Kanso->Response->setBody('content')->setStatus(200)->addHeaders('text/html', 'charset=utf-8')->sendHeaders();
 *
 */
class Response {

    /**
     * @var array HTTP response codes and messages
     */
    protected static $messages = [
        100 => 'Continue',
        101 => 'Switching Protocols',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    ];
    
    /**
     * @var int HTTP status code
     */
    protected $status  = 200;

    /**
     * @var array Associative array of headers
     */
    protected $headers = [];

    /**
     * @var string HTTP response body
     */
    protected $body    = '';

    /**
     * @var int Length of HTTP response body
     */
    protected $length = 0;

    /**
     * Constructor
     *
     * If the headers are already sent, the consructor will load them into
     * the current headers array. Otherwise it defaults to text/html
     *
     * The only time this would happen is if PHP error reporting is on
     * and there is an error that needs to be displayed to client before
     * this class was constructed.
     *
     */
    public function __construct()
    {
        if (headers_sent()) {
            $headers = headers_list();
            foreach ($headers as $header) {
                $header = explode(":", $header);
                $this->headers[trim($header[0])] = trim($header[1]);
            }
        }
        else {
            $this->headers['Content-Type'] = 'text/html; charset=utf-8';
        }
    }

    /**
     * Get HTTP response body
     *
     * @return string    The HTTP response body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set HTTP response body
     *
     * @param  string   $content    Content to replace to the current HTTP response body
     * @return mixed                Kanso\Http\Response
     */
    public function setBody($content)
    {
        $this->body   = $content;
        $this->length = strlen($this->body);
        return $this;
    }

    /**
     * Append content to the HTTP response body
     *
     * @param  string   $content    Content to append to the current HTTP response body
     * @return mixed                Kanso\Http\Response
     */
    public function appendBody($content)
    {
        $this->body   = $this->body.$content;
        $this->length = strlen($this->body);
        return $this;
    }

    /**
     * Set the HTTP response code
     *
     * @param  int   $status    The status code to send to the client
     * @return mixed            Kanso\Http\Response
     */
    public function setStatus($status)
    {
        if (array_key_exists($status, self::$messages))
        {
            $this->status = (int)$status;
            return $this;
        }
        return null;
    }

    /**
     * Get HTTP response code
     *
     * @return int
     */
    public function getstatus()
    {
        return $this->status;
    }

    /**
     * Append headers to the HTTP response headers
     *
     * @param  string   $name       Name of headers to be set
     * @param  string   $value      Value of headers to be set
     * @return mixed                Kanso\Http\Response
     */
    public function addheaders($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Replace HTTP response headers
     *
     * @param  array   $headers    Associative array of name/value headers
     * @return mixed               Kanso\Http\Response
     */
    public function setheaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get HTTP response headers
     * @return array
     */
    public function getheaders()
    {
        return $this->headers;
    }

    /**
     * Redirect to a new url
     *
     * This method prepares this response to return an HTTP Redirect response
     * to the HTTP client.
     *
     * @param  string    $url       The redirect destination
     * @param  int       $status    The redirect HTTP status code
     * @return mixed                Kanso\Http\Response
     */
    public function redirect ($url, $status = 302)
    {
        $this->setStatus($status);
        $this->setheaders(['Location', $url]);
        $this->sendheaders();
        header('location: '.$url);
        return $this;
    }

    /**
     * Finalize response headers
     *
     * This prepares the response and returns an array
     * of [status, headers, body]. This array is passed to Kanso's runner
     *
     * @return array[int status, array headers, string body]
     */
    public function finalize()
    {
        $Kanso = \Kanso\Kanso::getInstance();
        if ($Kanso->Config()['KANSO_USE_CDN'] && !$Kanso->is_admin) {
            $cdnFilter    = new \Kanso\CDN\CdnFilter($Kanso->Environment['HTTP_HOST'], $Kanso->Config['KASNO_CDN_URL'], $this->body);
            $this->body   = $cdnFilter->filter();
            $this->length = strlen($this->body);
        }

        $this->addheaders('Content-length', $this->length);
        if (in_array($this->status, [204, 304])) {
            $this->headers->remove('Content-Type');
            $this->headers->remove('Content-Length');
            $this->setBody('');
        }
        return [$this->status, $this->headers, $this->body];
    }

    /**
     * Send the response headers
     *
     * This method sends the response headers to
     * to the HTTP client.
     *
     * @return mixed    Kanso\Http\Response
     */
    public function sendheaders()
    {   
        if (!headers_sent())
        {
            if (isset($_SERVER['SERVER_PROTOCOL']))
                $protocol = $_SERVER['SERVER_PROTOCOL'];
            else
                $protocol = 'HTTP/1.1';

            header($protocol.' '.$this->status.' '.self::getMessageForCode($this->status));

            foreach ($this->headers as $name => $value)
            {
                header($name.':'.$value, true);
            }

            header("Content-length: $this->length");
        }
        return $this;
    }

    /**
     * Send the response body
     *
     * This method sends the response body to
     * to the HTTP client.
     *
     * This needs to be called after the headers are sent
     *
     * @return mixed    Kanso\Http\Response
     */
    public function sendBody() 
    {
        echo $this->body;
        return $this;
    }

    /**
     * Get message for HTTP status code
     *
     * @param  int         $status
     * @return string|null
     */
    public static function getMessageForCode($status)
    {
        if (isset(self::$messages[$status])) {
            return self::$messages[$status];
        } else {
            return null;
        }
    }

    /**
     * Redirect
     *
     * This method prepares this response to return an HTTP Redirect response
     * to the HTTP client.
     *
     * @param string $url    The redirect destination
     * @param int    $status The redirect HTTP status code
     */
    public function redirect ($url, $status = 302)
    {
        $this->setStatus($status);
        $this->headers->set('Location', $url);
    }
    /**
     * Helpers: Empty?
     * @return bool
     */
    public function isEmpty()
    {
        return in_array($this->status, array(201, 204, 304));
    }
    /**
     * Helpers: Informational?
     * @return bool
     */
    public function isInformational()
    {
        return $this->status >= 100 && $this->status < 200;
    }
    /**
     * Helpers: OK?
     * @return bool
     */
    public function isOk()
    {
        return $this->status === 200;
    }
    /**
     * Helpers: Successful?
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->status >= 200 && $this->status < 300;
    }
    /**
     * Helpers: Redirect?
     * @return bool
     */
    public function isRedirect()
    {
        return in_array($this->status, array(301, 302, 303, 307));
    }
    /**
     * Helpers: Redirection?
     * @return bool
     */
    public function isRedirection()
    {
        return $this->status >= 300 && $this->status < 400;
    }
    /**
     * Helpers: Forbidden?
     * @return bool
     */
    public function isForbidden()
    {
        return $this->status === 403;
    }
    /**
     * Helpers: Not Found?
     * @return bool
     */
    public function isNotFound()
    {
        return $this->status === 404;
    }
    /**
     * Helpers: Client error?
     * @return bool
     */
    public function isClientError()
    {
        return $this->status >= 400 && $this->status < 500;
    }
    /**
     * Helpers: Server Error?
     * @return bool
     */
    public function isServerError()
    {
        return $this->status >= 500 && $this->status < 600;
    }
    /**
     * DEPRECATION WARNING! ArrayAccess interface will be removed from \Slim\Http\Response.
     * Iterate `headers` or `cookies` properties directly.
     */
    /**
     * Array Access: Offset Exists
     */
    public function offsetExists($offset)
    {
        return isset($this->headers[$offset]);
    }
    /**
     * Array Access: Offset Get
     */
    public function offsetGet($offset)
    {
        return $this->headers[$offset];
    }
    /**
     * Array Access: Offset Set
     */
    public function offsetSet($offset, $value)
    {
        $this->headers[$offset] = $value;
    }
    /**
     * Array Access: Offset Unset
     */
    public function offsetUnset($offset)
    {
        unset($this->headers[$offset]);
    }


    /**
     * Helper function for zip downloads
     *
     * This method prepares this response headers to return
     * to the HTTP client for a zip file download
     *
     * @param string    $file    Absolute path to file for download
     */
    public function headers_download_zip($file) 
    {
        if (is_file($file) && !headers_sent()) {
            $fileName = substr($file, strrpos($file, '/') + 1);
            $this->setheaders(
                [
                    'Pragma'                    => 'public',
                    'Expires'                   => '0',
                    'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
                    'Cache-Control'             => 'public',
                    'Content-Description'       => 'File Transfer',
                    'Content-type'              => 'application/octet-stream',
                    'Content-Disposition'       => 'attachment; filename="'.$fileName.'"',
                    'Content-Transfer-Encoding' => 'binary',
                    'Content-Length'            => filesize($file),
                ]
            );
            ob_end_flush();
            @readfile($file);
        }
    }

    /**
     * Helper function for file downloads
     *
     * This method prepares this response headers to return
     * to the HTTP client for a file file download
     *
     * @param string    $file    Absolute path to file for download
     */
    public function headers_download_file($file) 
    {
        if (is_file($file) && !headers_sent()) {
            $this->setheaders(
                [
                    'Content-Description'        => 'File Transfer',
                    'Content-Type'               => 'application/octet-stream',
                    'Content-Disposition'        => 'attachment; filename='.basename($file),
                    'Content-Transfer-Encoding ' => 'binary',
                    'Expires'                    => '0',
                    'Cache-Control'              => 'must-revalidate, post-check=0, pre-check=0',
                    'Pragma'                     => 'public',
                    'Content-Length'             => filesize($file),
                ]
            );
        }
    }

}