<?php

namespace Kanso\Framework\Http\Response;


class Status {

    /**
     * HTTP response code messages
     *
     * @var array
     */
    protected $messages = 
    [
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
     * The HTTP response code
     *
     * @var int
     */
    protected $code = 200;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
    }
    
    /**
     * Set the code
     *
     * @access public
     * @param  int    $code The response code to set
     */
    public function set(int $code)
    {
        $this->code = $code;
    }

    /**
     * Get the code
     *
     * @access public
     * @return int
     */
    public function get(): int
    {
        return $this->code;
    }

    /**
     * Get the message for the current code
     *
     * @access public
     * @return string|null
     */
    public function message()
    {
        if (isset($this->messages[$this->code]))
        {
            return $this->messages[$this->code];
        }

        return null;
    }

    /**
     * Is the response empty
     *
     * @access public
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->code === 204;
    }

    /**
     * Is this an informational response
     *
     * @access public
     * @return bool
     */
    public function isInformational(): bool
    {
        return $this->code >= 100 && $this->code < 200;
    }
    
    /**
     * Is the response ok 200?
     *
     * @access public
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->code === 200;
    }

    /**
     * Is the response successful ?
     *
     * @access public
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->code >= 200 && $this->code < 300;
    }
    
    /**
     * Is the response a redirect ?
     *
     * @access public
     * @return bool
     */
    public function isRedirect(): bool
    {
        return in_array($this->code, array(301, 302, 303, 307));
    }

    /**
     * Is the response forbidden ?
     *
     * @access public
     * @return bool
     */
    public function isForbidden(): bool
    {
        return $this->code === 403;
    }

    /**
     * Is the response 404 not found ?
     *
     * @access public
     * @return bool
     */
    public function isNotFound(): bool
    {
        return $this->code === 404;
    }

    /**
     * Is this a client error ?
     *
     * @access public
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->code >= 400 && $this->code < 500;
    }
    
    /**
     * Is this a server error ?
     *
     * @access public
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->code >= 500 && $this->code < 600;
    }
}
