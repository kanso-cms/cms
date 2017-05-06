<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\Framework\Http\Response;

/**
 * Http response body
 *
 * @author Joe J. Howard
 */
class Body
{
    /**
     * The HTTP response body
     *
     * @var string
     */
    protected $str = '';

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    { 
    }

    /**
     * Get the body
     *
     * @access public
     * @return string
     */
    public function get(): string
    {
        return $this->str;
    }

    /**
     * Set the body
     *
     * @access public
     * @param  string $str Output to set
     */
    public function set(string $str)
    {
        $this->str = $str;
    }

    /**
     * Append output to the body
     *
     * @access public
     * @param  string $str Output to append
     */
    public function append(string $str)
    {
       $this->str .= $str;
    }

    /**
     * Clear the body 
     *
     * @access public
     */
    public function clear()
    {
        $this->str = '';
    }

    /**
     * Get the body length
     *
     * @access public
     */
    public function length()
    {
        return strlen($this->str);
    }
}
