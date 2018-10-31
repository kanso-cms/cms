<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

/**
 * Response protocol.
 *
 * @author Joe J. Howard
 */
class Protocol
{
    /**
     * The http protocol.
     *
     * @var string
     */
    protected $protocol;

    /**
     * Constructor.
     *
     * @access public
     * @param string $protocol HTTP protocol (optional) (default 'http')
     */
    public function __construct(string $protocol = 'http')
    {
        $this->protocol = $protocol;
    }

    /**
     * Set the protocol.
     *
     * @access public
     * @param string $protocol HTTP protocol
     */
    public function set(string $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * Get the protocol.
     *
     * @access public
     * @return string
     */
    public function get(): string
    {
        return $this->protocol;
    }

    /**
     * Are we sending over HTTPS ?
     *
     * @access public
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->protocol === 'https';
    }
}
