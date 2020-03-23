<?php

namespace kanso\framework\http\response;

class Status {

    /**
     * HTTP response code messages.
     *
     * @var array
     */
    protected $messages =
    [
        // Informational responses
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        // Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        // Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        // Client errors
        404 => 'error on Wikipedia',
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
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        // Server errors
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',

        // Unofficial codes
        103 => 'Checkpoint',
        103 => 'Early Hints',
        420 => 'Method Failure',
        420 => 'Enhance Your Calm',
        450 => 'Blocked by Windows Parental Controls',
        498 => 'Invalid Token',
        499 => 'Token Required',
        509 => 'Bandwidth Limit Exceeded',
        530 => 'Site is frozen',
        598 => 'Network read timeout error',
        599 => 'Network connect timeout error',
        440 => 'Login Time-out',
        449 => 'Retry With',
        451 => 'Redirect',
        444 => 'No Response',
        495 => 'SSL Certificate Error',
        496 => 'SSL Certificate Required',
        497 => 'HTTP Request Sent to HTTPS Port',
        499 => 'Client Closed Request',
        520 => 'Unknown Error',
        521 => 'Web Server Is Down',
        522 => 'Connection Timed Out',
        523 => 'Origin Is Unreachable',
        524 => 'A Timeout Occurred',
        525 => 'SSL Handshake Failed',
        526 => 'Invalid SSL Certificate',
        527 => 'Railgun Error',
    ];

    /**
     * Generic HTTP response descriptions.
     *
     * @var array
     */
    protected $descriptions =
    [
        // Informational responses
        100 => 'The server has received the request headers and the client should proceed to send the request body.',
        101 => 'The server has agreed to switch protocols.',
        102 => 'The server has received and is processing the request, but no response is available yet.',

        // Success
        200 => '',
        201 => 'The request has been fulfilled, resulting in the creation of a new resource.',
        202 => 'The request has been accepted for processing, but the processing has not been completed.',
        203 => 'The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is returning a modified version of the origin\'s response.',
        204 => 'The server successfully processed the request and is not returning any content.',
        205 => 'The server successfully processed the request, but is not returning any content.',
        206 => 'The server is delivering only part of the resource (byte serving) due to a range header sent by the client.',
        207 => 'Multi-Status',
        208 => 'The members of a DAV binding have already been enumerated in a preceding part of the (multistatus) response, and are not being included again.',
        226 => 'IM Used',

        // Client errors
        404 => 'The resource you requested could not be found. It may have been moved or deleted.',
        400 => 'The server cannot or will not process the request due to an apparent client error (e.g., malformed request syntax, size too large, invalid request message framing, or deceptive request routing).',
        401 => 'You are not authorized to access the requested resource.',
        402 => 'Payment Required',
        403 => 'You don\'t have permission to access the requested resource.',
        404 => 'The resource you requested could not be found. It may have been moved or deleted.',
        405 => 'A request method is not supported for the requested resource.',
        406 => 'The requested resource is capable of generating only content not acceptable according to the Accept headers sent in the request.',
        407 => 'The client must first authenticate itself with the proxy.',
        408 => 'The server timed out waiting for the request.',
        409 => 'The request could not be processed because of conflict in the current state of the resource, such as an edit conflict between multiple simultaneous updates.',
        410 => 'The resource requested is no longer available and will not be available again.',
        411 => 'The request did not specify the length of its content, which is required by the requested resource.',
        412 => 'The server does not meet one of the preconditions that the requester put on the request header fields.',
        413 => 'The request is larger than the server is willing or able to process.',
        414 => 'The URI provided was too long for the server to process.',
        415 => 'The request entity has a media type which the server or resource does not support.',
        416 => 'The client has asked for a portion of the file (byte serving), but the server cannot supply that portion.',
        417 => 'The server cannot meet the requirements of the Expect request-header field.',
        418 => 'Short and stout.',
        421 => 'The request was directed at a server that is not able to produce a response.',
        422 => 'The request was well-formed but was unable to be followed due to semantic errors.',
        423 => 'The resource that is being accessed is locked.',
        424 => 'The request failed because it depended on another request and that request failed.',
        426 => 'The client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field.',
        428 => 'The origin server requires the request to be conditional.',
        429 => 'The user has sent too many requests in a given amount of time.',
        431 => 'The server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large.',
        451 => 'A server operator has received a legal demand to deny access to a resource or to a set of resources that includes the requested resource.',
        498 => 'The provided CSRF token was invalid.',

        // Server errors
        500 => 'Aw, snap! An error has occurred while processing your request.',
        501 => 'The server either does not recognize the request method, or it lacks the ability to fulfil the request.',
        502 => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.',
        503 => 'The server cannot handle the request (because it is overloaded or down for maintenance).',
        504 => 'The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.',
        505 => 'The server does not support the HTTP protocol version used in the request.',
        506 => 'Transparent content negotiation for the request results in a circular reference.',
        507 => 'The server is unable to store the representation needed to complete the request.',
        508 => 'The server detected an infinite loop while processing the request.',
        510 => 'Further extensions to the request are required for the server to fulfil it.',
        511 => 'The client needs to authenticate to gain network access.',
    ];

    /**
     * The HTTP response code.
     *
     * @var int
     */
    protected $code = 200;

    /**
     * Constructor.
     */
    public function __construct(int $code = 200)
    {
        $this->code = $code;
    }

    /**
     * Set the code.
     *
     * @param int $code The response code to set
     */
    public function set(int $code): void
    {
        $this->code = $code;
    }

    /**
     * Get the code.
     *
     * @return int
     */
    public function get(): int
    {
        return $this->code;
    }

    /**
     * Get the message for the current code.
     *
     * @param  int|null    $code HTTP code (optional) (default null)
     * @return string|null
     */
    public function message(int $code = null)
    {
        $code = !$code ? $this->code : $code;

        if (isset($this->messages[$code]))
        {
            return $this->messages[$code];
        }

        return null;
    }

    /**
     * Get the description for the current code.
     *
     * @param  int|null    $code HTTP code (optional) (default null)
     * @return string|null
     */
    public function description(int $code = null)
    {
        $code = !$code ? $this->code : $code;

        if (isset($this->descriptions[$code]))
        {
            return $this->descriptions[$code];
        }

        return null;
    }

    /**
     * Is the response not modified.
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isNotModified(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code === 304;
    }

    /**
     * Is the response empty.
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isEmpty(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code === 204;
    }

    /**
     * Is this an informational response.
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isInformational(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code >= 100 && $code < 200;
    }

    /**
     * Is the response ok 200?
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isOk(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code === 200;
    }

    /**
     * Is the response successful ?
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isSuccessful(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code >= 200 && $code < 300;
    }

    /**
     * Is the response a redirect ?
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isRedirect(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return in_array($code, [301, 302, 303, 307]);
    }

    /**
     * Is the response forbidden ?
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isForbidden(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code === 403;
    }

    /**
     * Is the response 404 not found ?
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isNotFound(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code === 404;
    }

    /**
     * Is this a client error ?
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isClientError(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code >= 400 && $code < 500;
    }

    /**
     * Is this a server error ?
     *
     * @param  int|null $code HTTP code (optional) (default null)
     * @return bool
     */
    public function isServerError(int $code = null): bool
    {
        $code = !$code ? $this->code : $code;

        return $code >= 500 && $code < 600;
    }
}
