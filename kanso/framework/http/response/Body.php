<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\http\response;

/**
 * Http response body.
 *
 * @author Joe J. Howard
 */
class Body
{
    /**
     * The HTTP response body.
     *
     * @var string
     */
    protected $str = '';

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Get the body.
     *
     * @return string
     */
    public function get(): string
    {
        return $this->str;
    }

    /**
     * Set the body.
     *
     * @param string $str Output to set
     */
    public function set(string $str): void
    {
        $this->str = $str;
    }

    /**
     * Append output to the body.
     *
     * @param string $str Output to append
     */
    public function append(string $str): void
    {
       $this->str .= $str;
    }

    /**
     * Clear the body.
     */
    public function clear(): void
    {
        $this->str = '';
    }

    /**
     * Get the body length.
     */
    public function length()
    {
        return strlen($this->str);
    }
}
